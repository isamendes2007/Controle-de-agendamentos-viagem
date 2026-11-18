<?php
/**
 * Horizonte Viagens — Serviço de domínio para pagamentos.
 *
 * Centraliza regras de negócio: validação de saldo devedor, baixa,
 * estorno, cancelamento e atualização automática de atrasados.
 */

declare(strict_types=1);

class PagamentoService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $status = (string) ($filtros['status'] ?? '');
        $venceDe = $this->paraIso((string) ($filtros['vence_de'] ?? ''));
        $venceAte = $this->paraIso((string) ($filtros['vence_ate'] ?? ''));
        $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 10));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(r.codigo LIKE :busca OR c.nome LIKE :busca OR p.referencia LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($status !== '' && in_array($status, ['pendente', 'pago', 'atrasado', 'estornado', 'cancelado'], true)) {
            $condicoes[] = 'p.status = :status';
            $parametros['status'] = $status;
        }
        if ($venceDe !== null) {
            $condicoes[] = 'p.data_vencimento >= :vence_de';
            $parametros['vence_de'] = $venceDe;
        }
        if ($venceAte !== null) {
            $condicoes[] = 'p.data_vencimento <= :vence_ate';
            $parametros['vence_ate'] = $venceAte;
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN clientes c ON c.id = r.cliente_id
             {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT p.*,
                    r.codigo AS reserva_codigo,
                    c.nome AS cliente_nome,
                    v.data_partida,
                    d.nome AS destino_nome,
                    u.nome AS usuario_nome
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             {$onde}
             ORDER BY p.data_vencimento ASC, p.criado_em DESC
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
            "SELECT p.*,
                    r.codigo AS reserva_codigo,
                    c.nome AS cliente_nome,
                    v.data_partida,
                    d.nome AS destino_nome,
                    u.nome AS usuario_nome
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Pagamento não encontrado.');
        }
        return $registro;
    }

    public function saldoDevedor(int $reservaId): float
    {
        $reserva = $this->banco->buscarUm(
            'SELECT valor_total FROM reservas WHERE id = :id LIMIT 1',
            ['id' => $reservaId]
        );
        if ($reserva === null) {
            return 0.0;
        }

        $pago = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE reserva_id = :id AND status = 'pago'",
            ['id' => $reservaId]
        )['total'] ?? 0);

        return round(max(0, (float) $reserva['valor_total'] - $pago), 2);
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'reserva_id' => (int) ($dadosBrutos['reserva_id'] ?? 0),
            'valor' => $this->paraDecimal($dadosBrutos['valor'] ?? 0),
            'forma_pagamento' => trim((string) ($dadosBrutos['forma_pagamento'] ?? 'pix')),
            'status' => trim((string) ($dadosBrutos['status'] ?? 'pendente')),
            'data_vencimento' => $this->paraIso((string) ($dadosBrutos['data_vencimento'] ?? '')),
            'data_pagamento' => $this->paraIso((string) ($dadosBrutos['data_pagamento'] ?? '')),
            'referencia' => trim((string) ($dadosBrutos['referencia'] ?? '')),
            'observacoes' => trim((string) ($dadosBrutos['observacoes'] ?? '')),
        ];

        $erros = [];

        if ($dados['reserva_id'] <= 0) {
            $erros['reserva_id'] = 'Selecione uma reserva.';
        }
        if ($dados['valor'] <= 0) {
            $erros['valor'] = 'Informe um valor maior que zero.';
        }
        if (!in_array($dados['forma_pagamento'], ['pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia', 'dinheiro'], true)) {
            $erros['forma_pagamento'] = 'Selecione uma forma de pagamento válida.';
        }
        if (!in_array($dados['status'], ['pendente', 'pago', 'atrasado', 'estornado', 'cancelado'], true)) {
            $erros['status'] = 'Selecione um status válido.';
        }
        if ($dados['data_vencimento'] === null) {
            $erros['data_vencimento'] = 'Informe a data de vencimento.';
        }

        // Validação de saldo devedor (apenas para criação)
        if ($idAtual === null && $dados['reserva_id'] > 0 && $dados['valor'] > 0) {
            $saldo = $this->saldoDevedor($dados['reserva_id']);
            if ($dados['valor'] > $saldo + 0.001) {
                $erros['valor'] = sprintf(
                    'O valor informado ultrapassa o saldo devedor de %s.',
                    moeda($saldo)
                );
            }
        }

        return [$dados, $erros];
    }

    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO pagamentos
                (reserva_id, usuario_id, valor, forma_pagamento, status, data_vencimento, data_pagamento, referencia, observacoes)
             VALUES
                (:reserva_id, :usuario_id, :valor, :forma_pagamento, :status, :data_vencimento, :data_pagamento, :referencia, :observacoes)',
            array_merge($dadosValidados, [
                'usuario_id' => $_SESSION['usuario']['id'] ?? null,
            ])
        );
        $id = $this->banco->ultimoId();
        $reservaId = $dadosValidados['reserva_id'];
        if ($dadosValidados['status'] === 'pago') {
            $this->banco->executar(
                'UPDATE pagamentos SET data_pagamento = CURRENT_DATE WHERE reserva_id = :id AND status = :status AND data_pagamento IS NULL',
                ['id' => $reservaId, 'status' => 'pago']
            );
        }
        $this->sincronizarStatusReserva($reservaId);
        return (int) $id;
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $this->banco->executar(
            'UPDATE pagamentos SET
                reserva_id = :reserva_id, valor = :valor, forma_pagamento = :forma_pagamento,
                status = :status, data_vencimento = :data_vencimento, data_pagamento = :data_pagamento,
                referencia = :referencia, observacoes = :observacoes
             WHERE id = :id',
            $dadosValidados
        );
    }

    /** Sincroniza o status da reserva quando o pagamento é quitado. */
    private function sincronizarStatusReserva(int $reservaId): void
    {
        $reserva = $this->banco->buscarUm('SELECT status, valor_total FROM reservas WHERE id = :id', ['id' => $reservaId]);
        if ($reserva === null || in_array($reserva['status'], ['cancelada', 'concluida'], true)) {
            return;
        }
        $totalPago = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE reserva_id = :id AND status = 'pago'",
            ['id' => $reservaId]
        )['total'] ?? 0);
        if ($totalPago + 0.001 >= (float) $reserva['valor_total'] && $reserva['status'] === 'pendente') {
            $this->banco->executar('UPDATE reservas SET status = :status WHERE id = :id', ['status' => 'confirmada', 'id' => $reservaId]);
        }
    }

    public function baixar(int $id, int $usuarioId): void
    {
        $pagamento = $this->buscarPorId($id);
        if ($pagamento['status'] === 'pago') {
            throw new DomainException('Este pagamento já está baixado.');
        }

        $this->banco->emTransacao(function (Database $db) use ($id, $usuarioId, $pagamento): void {
            $db->executar(
                'UPDATE pagamentos SET status = :status, data_pagamento = CURRENT_DATE, usuario_id = :usuario_id WHERE id = :id',
                ['status' => 'pago', 'usuario_id' => $usuarioId, 'id' => $id]
            );
            $reservaId = (int) $pagamento['reserva_id'];
            $this->sincronizarStatusReserva($reservaId);
        });
    }

    public function estornar(int $id): void
    {
        $pagamento = $this->buscarPorId($id);
        if ($pagamento['status'] !== 'pago') {
            throw new DomainException('Apenas pagamentos pagos podem ser estornados.');
        }

        $this->banco->executar(
            'UPDATE pagamentos SET status = :status, data_pagamento = NULL WHERE id = :id',
            ['status' => 'estornado', 'id' => $id]
        );
    }

    public function cancelar(int $id): void
    {
        $pagamento = $this->buscarPorId($id);
        if (!in_array($pagamento['status'], ['pendente', 'atrasado'], true)) {
            throw new DomainException('Apenas pagamentos pendentes ou atrasados podem ser cancelados.');
        }

        $this->banco->executar(
            'UPDATE pagamentos SET status = :status WHERE id = :id',
            ['status' => 'cancelado', 'id' => $id]
        );
    }

    public function excluir(int $id): void
    {
        $pagamento = $this->buscarPorId($id);
        if (!in_array($pagamento['status'], ['pendente', 'atrasado', 'cancelado', 'estornado'], true)) {
            throw new DomainException('Este pagamento não pode ser excluído.');
        }
        $this->banco->executar('DELETE FROM pagamentos WHERE id = :id', ['id' => $id]);
    }

    /** Marca pagamentos pendentes vencidos como atrasados. */
    public function atualizarAtrasados(): int
    {
        return $this->banco->executar(
            "UPDATE pagamentos
             SET status = 'atrasado'
             WHERE status = 'pendente' AND data_vencimento < CURRENT_DATE"
        );
    }

    public function statusOpcoes(): array
    {
        return [
            'pendente' => 'Pendente',
            'pago' => 'Pago',
            'atrasado' => 'Atrasado',
            'estornado' => 'Estornado',
            'cancelado' => 'Cancelado',
        ];
    }

    public function formaPagamentoOpcoes(): array
    {
        return [
            'pix' => 'PIX',
            'cartao_credito' => 'Cartão de crédito',
            'cartao_debito' => 'Cartão de débito',
            'boleto' => 'Boleto',
            'transferencia' => 'Transferência bancária',
            'dinheiro' => 'Dinheiro',
        ];
    }
}
