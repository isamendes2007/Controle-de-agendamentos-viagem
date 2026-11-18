<?php
/**
 * Horizonte Viagens — Serviço de domínio para agendamentos de viagens.
 *
 * Um agendamento é uma solicitação de viagem feita por (ou para) um cliente.
 * O gestor acompanha, aprova ou rejeita cada solicitação. Quando aprovado,
 * o agendamento pode ser convertido em uma reserva.
 */

declare(strict_types=1);

class AgendamentoService extends BaseService
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
            $condicoes[] = '(a.codigo LIKE :busca OR c.nome LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($status !== '' && in_array($status, ['solicitado', 'aprovado', 'rejeitado', 'cancelado'], true)) {
            $condicoes[] = 'a.status = :status';
            $parametros['status'] = $status;
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM agendamentos a
             INNER JOIN clientes c ON c.id = a.cliente_id
             {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT a.*,
                    c.nome AS cliente_nome, c.cpf, c.email, c.telefone,
                    v.data_partida, v.data_retorno,
                    p.nome AS pacote_nome, d.nome AS destino_nome,
                    u.nome AS usuario_nome
             FROM agendamentos a
             INNER JOIN clientes c ON c.id = a.cliente_id
             INNER JOIN viagens v ON v.id = a.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             {$onde}
             ORDER BY a.criado_em DESC
             {$this->limiteOffset($porPagina, $offset)}",
            $parametros
        );

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
            "SELECT a.*,
                    c.nome AS cliente_nome, c.cpf, c.email, c.telefone,
                    v.data_partida, v.data_retorno, v.vagas_totais, v.preco,
                    p.nome AS pacote_nome, d.nome AS destino_nome, d.cidade, d.estado, d.pais,
                    u.nome AS usuario_nome
             FROM agendamentos a
             INNER JOIN clientes c ON c.id = a.cliente_id
             INNER JOIN viagens v ON v.id = a.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             WHERE a.id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Agendamento não encontrado.');
        }
        return $registro;
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'cliente_id' => (int) ($dadosBrutos['cliente_id'] ?? 0),
            'viagem_id' => (int) ($dadosBrutos['viagem_id'] ?? 0),
            'qtd_viajantes' => (int) ($dadosBrutos['qtd_viajantes'] ?? 1),
            'valor_unitario' => $this->paraDecimal($dadosBrutos['valor_unitario'] ?? 0),
            'status' => trim((string) ($dadosBrutos['status'] ?? 'solicitado')),
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
        if ($dados['valor_unitario'] < 0) {
            $erros['valor_unitario'] = 'Informe um valor unitário válido.';
        }
        if (!in_array($dados['status'], ['solicitado', 'aprovado', 'rejeitado', 'cancelado'], true)) {
            $erros['status'] = 'Selecione um status válido.';
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

        // Validação de vagas disponíveis (apenas para criação)
        if ($idAtual === null && $dados['viagem_id'] > 0 && $dados['qtd_viajantes'] > 0) {
            $viagem = $this->banco->buscarUm(
                'SELECT vagas_totais FROM viagens WHERE id = :id',
                ['id' => $dados['viagem_id']]
            );
            if ($viagem !== null) {
                $ocupadas = (int) ($this->banco->buscarUm(
                    "SELECT COALESCE(SUM(r.qtd_viajantes), 0) AS total FROM reservas r
                     WHERE r.viagem_id = :id AND r.status <> 'cancelada'
                     UNION ALL
                     SELECT COALESCE(SUM(a.qtd_viajantes), 0) FROM agendamentos a
                     WHERE a.viagem_id = :id AND a.status = 'aprovado'",
                    ['id' => $dados['viagem_id']]
                )['total'] ?? 0);
                if (($ocupadas + $dados['qtd_viajantes']) > (int) $viagem['vagas_totais']) {
                    $erros['qtd_viajantes'] = 'Não há vagas suficientes para esta viagem.';
                }
            }
        }

        return [$dados, $erros];
    }

    /** Calcula o valor total do agendamento. */
    public function calcularTotal(float $valorUnitario, int $qtdViajantes): float
    {
        return round(max(0, $valorUnitario * $qtdViajantes), 2);
    }

    public function criar(array $dadosValidados): int
    {
        $valorTotal = $this->calcularTotal(
            $dadosValidados['valor_unitario'],
            $dadosValidados['qtd_viajantes']
        );

        $codigo = $this->gerarCodigo();

        $this->banco->executar(
            'INSERT INTO agendamentos
                (codigo, cliente_id, viagem_id, qtd_viajantes, valor_unitario, valor_total, status, observacoes, usuario_id)
             VALUES
                (:codigo, :cliente_id, :viagem_id, :qtd_viajantes, :valor_unitario, :valor_total, :status, :observacoes, :usuario_id)',
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
        $dadosValidados['id'] = $id;
        $valorTotal = $this->calcularTotal(
            $dadosValidados['valor_unitario'],
            $dadosValidados['qtd_viajantes']
        );
        $dadosValidados['valor_total'] = $valorTotal;

        $this->banco->executar(
            'UPDATE agendamentos SET
                cliente_id = :cliente_id, viagem_id = :viagem_id, qtd_viajantes = :qtd_viajantes,
                valor_unitario = :valor_unitario, valor_total = :valor_total,
                status = :status, observacoes = :observacoes
             WHERE id = :id',
            $dadosValidados
        );
    }

    public function atualizarStatus(int $id, string $status): void
    {
        if (!in_array($status, ['solicitado', 'aprovado', 'rejeitado', 'cancelado'], true)) {
            throw new InvalidArgumentException('Status de agendamento inválido.');
        }
        $this->banco->executar(
            'UPDATE agendamentos SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }

    public function excluir(int $id): void
    {
        $this->banco->executar('DELETE FROM agendamentos WHERE id = :id', ['id' => $id]);
    }

    /** Conta agendamentos por status para o dashboard. */
    public function contagemPorStatus(): array
    {
        return $this->banco->buscarTodos(
            "SELECT status, COUNT(*) AS quantidade FROM agendamentos GROUP BY status"
        );
    }

    /** Agendamentos pendentes (aguardando decisão do gestor). */
    public function pendentes(int $limite = 5): array
    {
        return $this->banco->buscarTodos(
            "SELECT a.*, c.nome AS cliente_nome, v.data_partida,
                    p.nome AS pacote_nome, d.nome AS destino_nome
             FROM agendamentos a
             INNER JOIN clientes c ON c.id = a.cliente_id
             INNER JOIN viagens v ON v.id = a.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE a.status = 'solicitado'
             ORDER BY a.criado_em ASC
             LIMIT :limite",
            ['limite' => $limite],
            ['limite' => PDO::PARAM_INT]
        );
    }

    private function gerarCodigo(): string
    {
        do {
            $codigo = 'AG-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $existe = $this->banco->buscarUm('SELECT id FROM agendamentos WHERE codigo = :codigo', ['codigo' => $codigo]);
        } while ($existe !== null);
        return $codigo;
    }

    public function statusOpcoes(): array
    {
        return [
            'solicitado' => 'Solicitado',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'cancelado' => 'Cancelado',
        ];
    }
}
