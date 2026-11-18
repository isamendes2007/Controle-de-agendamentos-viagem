<?php
/**
 * Horizonte Viagens — Serviço de domínio para pacotes.
 */

declare(strict_types=1);

class PacoteService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $situacao = (string) ($filtros['situacao'] ?? 'ativos');
        $destinoId = (int) ($filtros['destino_id'] ?? 0);
        if (!in_array($situacao, ['ativos', 'inativos', 'todos'], true)) {
            $situacao = 'ativos';
        }

        $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 10));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(p.nome LIKE :busca OR p.descricao LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($situacao === 'ativos') {
            $condicoes[] = 'p.ativo = 1';
        } elseif ($situacao === 'inativos') {
            $condicoes[] = 'p.ativo = 0';
        }
        if ($destinoId > 0) {
            $condicoes[] = 'p.destino_id = :destino_id';
            $parametros['destino_id'] = $destinoId;
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM pacotes p {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT p.*, d.nome AS destino_nome, d.nome AS destino, d.cidade, d.pais,
                    (SELECT COUNT(*) FROM viagens v
                        WHERE v.pacote_id = p.id AND v.status <> 'cancelada' AND v.data_partida >= CURRENT_DATE) AS viagens_futuras,
                    (SELECT MIN(v.data_partida) FROM viagens v
                        WHERE v.pacote_id = p.id AND v.status <> 'cancelada' AND v.data_partida >= CURRENT_DATE) AS proxima_partida
             FROM pacotes p
             INNER JOIN destinos d ON d.id = p.destino_id
             {$onde}
             ORDER BY p.nome ASC
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
            'situacao' => $situacao,
            'destino_id' => $destinoId,
        ];
    }

    public function buscarPorId(int $id): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT p.*, d.nome AS destino, d.nome AS destino_nome, d.cidade, d.estado, d.pais
             FROM pacotes p
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE p.id = :id LIMIT 1",
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Pacote não encontrado.');
        }
        return $registro;
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'nome' => trim((string) ($dadosBrutos['nome'] ?? '')),
            'destino_id' => (int) ($dadosBrutos['destino_id'] ?? 0),
            'categoria' => trim((string) ($dadosBrutos['categoria'] ?? 'cultural')),
            'descricao' => trim((string) ($dadosBrutos['descricao'] ?? '')),
            'preco_base' => $this->paraDecimal($dadosBrutos['preco_base'] ?? 0),
        ];

        $erros = [];

        if (mb_strlen($dados['nome']) < 2) {
            $erros['nome'] = 'Informe o nome do pacote.';
        }
        if ($dados['destino_id'] <= 0) {
            $erros['destino_id'] = 'Selecione um destino.';
        }
        if (!in_array($dados['categoria'], ['praia', 'aventura', 'cultural', 'gastronomia', 'ecoturismo', 'luxo', 'religioso', 'negocios'], true)) {
            $erros['categoria'] = 'Selecione uma categoria válida.';
        }
        if ($dados['preco_base'] < 0) {
            $erros['preco_base'] = 'Informe um preço base válido.';
        }

        return [$dados, $erros];
    }

    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO pacotes (destino_id, nome, categoria, descricao, preco_base, ativo)
             VALUES (:destino_id, :nome, :categoria, :descricao, :preco_base, 1)',
            $dadosValidados
        );
        return $this->banco->ultimoId();
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $this->banco->executar(
            'UPDATE pacotes SET
                destino_id = :destino_id, nome = :nome, categoria = :categoria,
                descricao = :descricao, preco_base = :preco_base
             WHERE id = :id',
            $dadosValidados
        );
    }

    public function alternar(int $id): int
    {
        $registro = $this->buscarPorId($id);
        $novoStatus = (int) $registro['ativo'] === 1 ? 0 : 1;
        $this->banco->executar(
            'UPDATE pacotes SET ativo = :ativo WHERE id = :id',
            ['ativo' => $novoStatus, 'id' => $id]
        );
        return $novoStatus;
    }

    /** Partidas do pacote, com ocupação e receita prevista. */
    public function viagens(int $pacoteId): array
    {
        return $this->banco->buscarTodos(
            "SELECT v.*,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r
                              WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS viajantes,
                    COALESCE((SELECT SUM(r.valor_total) FROM reservas r
                              WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS receita
             FROM viagens v
             WHERE v.pacote_id = :pacote
             ORDER BY v.data_partida ASC",
            ['pacote' => $pacoteId]
        );
    }

    /** Indicadores acumulados do pacote (reservas não canceladas). */
    public function indicadores(int $pacoteId): array
    {
        $registro = $this->banco->buscarUm(
            "SELECT COUNT(r.id) AS reservas,
                    COALESCE(SUM(r.qtd_viajantes), 0) AS viajantes,
                    COALESCE(SUM(r.valor_total), 0) AS receita
             FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             WHERE v.pacote_id = :pacote AND r.status <> 'cancelada'",
            ['pacote' => $pacoteId]
        );

        return [
            'reservas' => (int) ($registro['reservas'] ?? 0),
            'viajantes' => (int) ($registro['viajantes'] ?? 0),
            'receita' => (float) ($registro['receita'] ?? 0),
        ];
    }

    public function categorias(): array
    {
        return [
            'praia' => 'Praia',
            'aventura' => 'Aventura',
            'cultural' => 'Cultural',
            'gastronomia' => 'Gastronomia',
            'ecoturismo' => 'Ecoturismo',
            'luxo' => 'Luxo',
            'religioso' => 'Religioso',
            'negocios' => 'Negócios',
        ];
    }
}
