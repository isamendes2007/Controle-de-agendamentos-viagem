<?php
/**
 * Horizonte Viagens — Serviço de domínio para viagens.
 */

declare(strict_types=1);

class ViagemService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $status = (string) ($filtros['status'] ?? '');
        $destinoId = (int) ($filtros['destino_id'] ?? 0);
        $de = trim((string) ($filtros['de'] ?? ''));
        $ate = trim((string) ($filtros['ate'] ?? ''));
        $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 10));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(p.nome LIKE :busca OR d.nome LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($status !== '' && in_array($status, ['planejada', 'confirmada', 'em_andamento', 'concluida', 'cancelada'], true)) {
            $condicoes[] = 'v.status = :status';
            $parametros['status'] = $status;
        }
        if ($destinoId > 0) {
            $condicoes[] = 'd.id = :destino_id';
            $parametros['destino_id'] = $destinoId;
        }
        if ($de !== '') {
            $condicoes[] = 'v.data_partida >= :de';
            $parametros['de'] = $de;
        }
        if ($ate !== '') {
            $condicoes[] = 'v.data_partida <= :ate';
            $parametros['ate'] = $ate;
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT v.*,
                    p.nome AS pacote_nome,
                    d.nome AS destino_nome,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS ocupadas,
                    COALESCE((SELECT SUM(r.valor_total) FROM reservas r WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS receita_prevista,
                    v.vagas_totais
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             {$onde}
             ORDER BY v.data_partida ASC
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
            "SELECT v.*, p.nome AS pacote_nome, p.categoria, p.preco_base AS pacote_preco_base,
                    d.nome AS destino_nome, d.cidade, d.estado, d.pais, d.continente
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE v.id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Viagem não encontrada.');
        }
        return $registro;
    }

    /** Indicadores rápidos do topo da listagem. */
    public function resumo(): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT
                SUM(CASE WHEN status IN ('planejada', 'confirmada') AND data_partida >= CURRENT_DATE THEN 1 ELSE 0 END) AS futuras,
                SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) AS em_andamento,
                SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) AS concluidas
             FROM viagens"
        );

        return [
            'futuras' => (int) ($registro['futuras'] ?? 0),
            'em_andamento' => (int) ($registro['em_andamento'] ?? 0),
            'concluidas' => (int) ($registro['concluidas'] ?? 0),
        ];
    }

    /** Indicadores financeiros e de ocupação de uma viagem. */
    public function totais(int $viagemId): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT
                (SELECT COALESCE(SUM(qtd_viajantes), 0) FROM reservas
                    WHERE viagem_id = :id AND status <> 'cancelada') AS viajantes,
                (SELECT COALESCE(SUM(valor_total), 0) FROM reservas
                    WHERE viagem_id = :id AND status <> 'cancelada') AS receita_prevista,
                (SELECT COALESCE(SUM(p.valor), 0) FROM pagamentos p
                    INNER JOIN reservas r ON r.id = p.reserva_id
                    WHERE r.viagem_id = :id AND r.status <> 'cancelada' AND p.status = 'pago') AS recebido",
            ['id' => $viagemId]
        );

        $receitaPrevista = (float) ($registro['receita_prevista'] ?? 0);
        $recebido = (float) ($registro['recebido'] ?? 0);

        return [
            'viajantes' => (int) ($registro['viajantes'] ?? 0),
            'receita_prevista' => $receitaPrevista,
            'recebido' => $recebido,
            'em_aberto' => $this->saldoEmAberto($receitaPrevista, $recebido),
        ];
    }

    public function reservasComDetalhes(int $viagemId): array
    {
        $registros = $this->banco->buscarTodos(
            "SELECT r.*, c.nome AS cliente_nome, c.cpf, c.email, c.telefone,
                    COALESCE((SELECT SUM(pg.valor) FROM pagamentos pg WHERE pg.reserva_id = r.id AND pg.status = 'pago'), 0) AS total_pago
             FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             WHERE r.viagem_id = :id
             ORDER BY r.criado_em DESC",
            ['id' => $viagemId]
        );

        foreach ($registros as $indice => $registro) {
            $registros[$indice]['em_aberto'] = $this->saldoEmAberto($registro['valor_total'], $registro['total_pago']);
        }

        return $registros;
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'pacote_id' => (int) ($dadosBrutos['pacote_id'] ?? 0),
            'data_partida' => $this->paraIso((string) ($dadosBrutos['data_partida'] ?? '')),
            'data_retorno' => $this->paraIso((string) ($dadosBrutos['data_retorno'] ?? '')),
            'vagas_totais' => (int) ($dadosBrutos['vagas_totais'] ?? 0),
            'preco' => $this->paraDecimal($dadosBrutos['preco'] ?? 0),
            'status' => trim((string) ($dadosBrutos['status'] ?? 'planejada')),
            'observacoes' => trim((string) ($dadosBrutos['observacoes'] ?? '')),
        ];

        $erros = [];

        if ($dados['pacote_id'] <= 0) {
            $erros['pacote_id'] = 'Selecione um pacote.';
        }
        if ($dados['data_partida'] === null) {
            $erros['data_partida'] = 'Informe a data de partida.';
        }
        if ($dados['data_retorno'] === null) {
            $erros['data_retorno'] = 'Informe a data de retorno.';
        }
        if ($dados['data_partida'] !== null && $dados['data_retorno'] !== null && $dados['data_retorno'] < $dados['data_partida']) {
            $erros['data_retorno'] = 'A data de retorno não pode ser anterior à data de partida.';
        }
        if ($dados['vagas_totais'] <= 0) {
            $erros['vagas_totais'] = 'Informe um número válido de vagas.';
        }
        if ($dados['preco'] < 0) {
            $erros['preco'] = 'Informe um preço válido.';
        }
        if (!in_array($dados['status'], ['planejada', 'confirmada', 'em_andamento', 'concluida', 'cancelada'], true)) {
            $erros['status'] = 'Selecione um status válido.';
        }

        return [$dados, $erros];
    }

    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO viagens (pacote_id, data_partida, data_retorno, vagas_totais, preco, status, observacoes)
             VALUES (:pacote_id, :data_partida, :data_retorno, :vagas_totais, :preco, :status, :observacoes)',
            $dadosValidados
        );
        return $this->banco->ultimoId();
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $this->banco->executar(
            'UPDATE viagens SET
                pacote_id = :pacote_id, data_partida = :data_partida, data_retorno = :data_retorno,
                vagas_totais = :vagas_totais, preco = :preco, status = :status, observacoes = :observacoes
             WHERE id = :id',
            $dadosValidados
        );
    }

    public function atualizarStatus(int $id, string $status): void
    {
        if (!in_array($status, ['planejada', 'confirmada', 'em_andamento', 'concluida', 'cancelada'], true)) {
            throw new InvalidArgumentException('Status de viagem inválido.');
        }
        $this->banco->executar(
            'UPDATE viagens SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }

    public function excluir(int $id): void
    {
        $temReservas = (int) ($this->banco->buscarUm(
            'SELECT COUNT(*) AS total FROM reservas WHERE viagem_id = :id',
            ['id' => $id]
        )['total'] ?? 0);
        if ($temReservas > 0) {
            throw new DomainException('Esta viagem possui reservas e não pode ser excluída.');
        }
        $this->banco->executar('DELETE FROM viagens WHERE id = :id', ['id' => $id]);
    }

    public function statusOpcoes(): array
    {
        return [
            'planejada' => 'Planejada',
            'confirmada' => 'Confirmada',
            'em_andamento' => 'Em andamento',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];
    }
}
