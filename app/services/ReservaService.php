<?php
/**
 * Horizonte Viagens — Serviço de domínio para reservas.
 *
 * Centraliza regras de negócio: cálculo de total, validação de vagas,
 * geração de código, sincronização de status e cancelamento.
 */

declare(strict_types=1);

class ReservaService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $status = (string) ($filtros['status'] ?? '');
        $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 10));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(r.codigo LIKE :busca OR c.nome LIKE :busca OR c.cpf LIKE :busca OR c.email LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($status !== '' && in_array($status, ['pendente', 'confirmada', 'concluida', 'cancelada'], true)) {
            $condicoes[] = 'r.status = :status';
            $parametros['status'] = $status;
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT r.*,
                    c.nome AS cliente_nome, c.cpf, c.email, c.telefone,
                    v.data_partida, v.data_retorno, v.status AS status_viagem,
                    p.nome AS pacote_nome, d.nome AS destino_nome,
                    COALESCE((SELECT SUM(pg.valor) FROM pagamentos pg WHERE pg.reserva_id = r.id AND pg.status = 'pago'), 0) AS total_pago
             FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             {$onde}
             ORDER BY r.criado_em DESC
             {$this->limiteOffset($porPagina, $offset)}",
            $parametros
        );

        foreach ($registros as $indice => $registro) {
            $registros[$indice]['em_aberto'] = $this->saldoEmAberto($registro['valor_total'], $registro['total_pago']);
        }

        return [
            'registros' => $registros,
            'total' => $total,
            'por_pagina' => $porPagina,
            'pagina' => $pagina,
            'total_paginas' => $totalPaginas,
            'offset' => $offset,
            'inicio' => $total === 0 ? 0 : $offset + 1,
            'fim' => min($total, $pagina * $porPagina),
            'busca' => $busca,
            'status' => $status,
        ];
    }

    public function buscarPorId(int $id): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT r.*,
                    c.nome AS cliente_nome, c.cpf, c.email, c.telefone,
                    v.data_partida, v.data_retorno, v.status AS status_viagem,
                    p.nome AS pacote_nome, d.nome AS destino_nome, d.cidade, d.estado, d.pais,
                    u.nome AS usuario_nome,
                    COALESCE((SELECT SUM(pg.valor) FROM pagamentos pg WHERE pg.reserva_id = r.id AND pg.status = 'pago'), 0) AS total_pago
             FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             LEFT JOIN usuarios u ON u.id = r.usuario_id
             WHERE r.id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Reserva não encontrada.');
        }

        $registro['em_aberto'] = $this->saldoEmAberto($registro['valor_total'], $registro['total_pago']);

        return $registro;
    }

    public function pagamentos(int $reservaId): array
    {
        return $this->banco->buscarTodos(
            "SELECT p.*, u.nome AS usuario_nome, u.nome AS registrado_por
             FROM pagamentos p
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.reserva_id = :id
             ORDER BY p.data_vencimento ASC, p.criado_em DESC",
            ['id' => $reservaId]
        );
    }

    public function resumoFinanceiro(int $reservaId): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT
                r.valor_total,
                COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) AS total_pago,
                COALESCE(SUM(CASE WHEN p.status = 'atrasado' THEN p.valor ELSE 0 END), 0) AS atrasado
             FROM reservas r
             LEFT JOIN pagamentos p ON p.reserva_id = r.id
             WHERE r.id = :id
             GROUP BY r.id, r.valor_total",
            ['id' => $reservaId]
        );

        if ($registro === null) {
            return ['valor_total' => 0, 'total_pago' => 0, 'em_aberto' => 0, 'atrasado' => 0];
        }

        $registro['em_aberto'] = $this->saldoEmAberto($registro['valor_total'], $registro['total_pago']);
        return $registro;
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'cliente_id' => (int) ($dadosBrutos['cliente_id'] ?? 0),
            'viagem_id' => (int) ($dadosBrutos['viagem_id'] ?? 0),
            'qtd_viajantes' => (int) ($dadosBrutos['qtd_viajantes'] ?? 1),
            'valor_unitario' => $this->paraDecimal($dadosBrutos['valor_unitario'] ?? 0),
            'desconto' => $this->paraDecimal($dadosBrutos['desconto'] ?? 0),
            'status' => trim((string) ($dadosBrutos['status'] ?? 'pendente')),
            'observacoes' => trim((string) ($dadosBrutos['observacoes'] ?? '')),
        ];

        $erros = [];

        if ($dados['cliente_id'] <= 0) {
            $erros['cliente_id'] = 'Selecione um cliente.';
        }
        if ($dados['viagem_id'] <= 0) {
            $erros['viagem_id'] = 'Selecione uma viagem.';
        }
        if ($dados['qtd_viajantes'] <= 0) {
            $erros['qtd_viajantes'] = 'Informe a quantidade de viajantes.';
        }

        // Se o valor unitário não foi informado, busca o preço da viagem.
        if ($dados['valor_unitario'] <= 0 && $dados['viagem_id'] > 0) {
            $viagem = $this->banco->buscarUm(
                'SELECT preco FROM viagens WHERE id = :id',
                ['id' => $dados['viagem_id']]
            );
            if ($viagem !== null) {
                $dados['valor_unitario'] = (float) $viagem['preco'];
            }
        }

        if ($dados['valor_unitario'] < 0) {
            $erros['valor_unitario'] = 'Informe um valor unitário válido.';
        }
        if ($dados['desconto'] < 0) {
            $erros['desconto'] = 'Informe um desconto válido.';
        }
        if (!in_array($dados['status'], ['pendente', 'confirmada', 'concluida', 'cancelada'], true)) {
            $erros['status'] = 'Selecione um status válido.';
        }

        // Validação de vagas (apenas para criação)
        if ($idAtual === null && $dados['viagem_id'] > 0 && $dados['qtd_viajantes'] > 0) {
            $viagem = $this->banco->buscarUm(
                'SELECT vagas_totais FROM viagens WHERE id = :id',
                ['id' => $dados['viagem_id']]
            );
            if ($viagem !== null) {
                $ocupadas = (int) ($this->banco->buscarUm(
                    'SELECT COALESCE(SUM(qtd_viajantes), 0) AS total FROM reservas WHERE viagem_id = :id AND status <> \'cancelada\'',
                    ['id' => $dados['viagem_id']]
                )['total'] ?? 0);
                if (($ocupadas + $dados['qtd_viajantes']) > (int) $viagem['vagas_totais']) {
                    $erros['qtd_viajantes'] = 'Não há vagas suficientes para esta viagem.';
                }
            }
        }

        return [$dados, $erros];
    }

    /** Calcula o valor total da reserva. */
    public function calcularTotal(float $valorUnitario, int $qtdViajantes, float $desconto): float
    {
        return round(max(0, ($valorUnitario * $qtdViajantes) - $desconto), 2);
    }

    public function criar(array $dadosValidados): int
    {
        $valorTotal = $this->calcularTotal(
            $dadosValidados['valor_unitario'],
            $dadosValidados['qtd_viajantes'],
            $dadosValidados['desconto']
        );

        $codigo = $this->gerarCodigo();

        $this->banco->executar(
            'INSERT INTO reservas
                (codigo, cliente_id, viagem_id, qtd_viajantes, valor_unitario, desconto, valor_total, status, observacoes, usuario_id)
             VALUES
                (:codigo, :cliente_id, :viagem_id, :qtd_viajantes, :valor_unitario, :desconto, :valor_total, :status, :observacoes, :usuario_id)',
            array_merge($dadosValidados, [
                'codigo' => $codigo,
                'valor_total' => $valorTotal,
                'usuario_id' => $_SESSION['usuario']['id'] ?? null,
            ])
        );
        return (int) $this->banco->ultimoId();
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $reserva = $this->buscarPorId($id);
        $valorTotal = $this->calcularTotal(
            $dadosValidados['valor_unitario'],
            $dadosValidados['qtd_viajantes'],
            $dadosValidados['desconto']
        );

        $dadosValidados['id'] = $id;
        $dadosValidados['valor_total'] = $valorTotal;

        $this->banco->executar(
            'UPDATE reservas SET
                cliente_id = :cliente_id, viagem_id = :viagem_id, qtd_viajantes = :qtd_viajantes,
                valor_unitario = :valor_unitario, desconto = :desconto, valor_total = :valor_total,
                status = :status, observacoes = :observacoes
             WHERE id = :id',
            $dadosValidados
        );
    }

    public function atualizarStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pendente', 'confirmada', 'concluida', 'cancelada'], true)) {
            throw new InvalidArgumentException('Status de reserva inválido.');
        }

        $reserva = $this->buscarPorId($id);

        if ($status === 'cancelada' && $reserva['status'] !== 'cancelada') {
            $this->cancelarPagamentosAbertos($id);
        }

        $this->banco->executar(
            'UPDATE reservas SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );

        if ($status === 'concluida') {
            $this->banco->executar(
                "UPDATE reservas SET status = 'concluida' WHERE viagem_id = :viagemId AND status = 'confirmada'",
                ['viagemId' => $reserva['viagem_id']]
            );
        }
    }

    /** Cancela pagamentos pendentes/atrasados de uma reserva. */
    private function cancelarPagamentosAbertos(int $reservaId): void
    {
        $this->banco->executar(
            "UPDATE pagamentos
             SET status = 'cancelado'
             WHERE reserva_id = :id AND status IN ('pendente', 'atrasado')",
            ['id' => $reservaId]
        );
    }

    /** Sincroniza o status da reserva para confirmada quando quitada. */
    public function sincronizarStatus(int $reservaId): void
    {
        $reserva = $this->buscarPorId($reservaId);
        if (in_array($reserva['status'], ['cancelada', 'concluida'], true)) {
            return;
        }
        $resumo = $this->resumoFinanceiro($reservaId);
        if ((float) $resumo['total_pago'] + 0.001 >= (float) $reserva['valor_total'] && $reserva['status'] === 'pendente') {
            $this->banco->executar(
                'UPDATE reservas SET status = :status WHERE id = :id',
                ['status' => 'confirmada', 'id' => $reservaId]
            );
        }
    }

    /** Verifica se a reserva pode ser concluída (sem pagamentos em aberto). */
    public function podeConcluir(int $reservaId): bool
    {
        $resumo = $this->resumoFinanceiro($reservaId);
        return (float) $resumo['em_aberto'] <= 0;
    }

    private function gerarCodigo(): string
    {
        do {
            $codigo = 'HV-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $existe = $this->banco->buscarUm('SELECT id FROM reservas WHERE codigo = :codigo', ['codigo' => $codigo]);
        } while ($existe !== null);
        return $codigo;
    }

    public function statusOpcoes(): array
    {
        return [
            'pendente' => 'Pendente',
            'confirmada' => 'Confirmada',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];
    }
}
