<?php
/**
 * Horizonte Viagens — Serviço de domínio para destinos.
 */

declare(strict_types=1);

class DestinoService extends BaseService
{
    public function listar(array $filtros = []): array
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $situacao = (string) ($filtros['situacao'] ?? 'ativos');
        if (!in_array($situacao, ['ativos', 'inativos', 'todos'], true)) {
            $situacao = 'ativos';
        }

        $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 10));
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));

        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(d.nome LIKE :busca OR d.cidade LIKE :busca OR d.pais LIKE :busca OR d.continente LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($situacao === 'ativos') {
            $condicoes[] = 'd.ativo = 1';
        } elseif ($situacao === 'inativos') {
            $condicoes[] = 'd.ativo = 0';
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM destinos d {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT d.*,
                    COUNT(p.id) AS total_pacotes,
                    (SELECT COUNT(*) FROM viagens v
                        INNER JOIN pacotes pac ON pac.id = v.pacote_id
                        WHERE pac.destino_id = d.id AND v.status <> 'cancelada'
                          AND v.data_partida >= CURRENT_DATE) AS viagens_futuras
             FROM destinos d
             LEFT JOIN pacotes p ON p.destino_id = d.id AND p.ativo = 1
             {$onde}
             GROUP BY d.id
             ORDER BY d.nome ASC
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
        ];
    }

    public function buscarPorId(int $id): array
    {
        $registro = $this->banco->buscarUm(
            'SELECT * FROM destinos WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Destino não encontrado.');
        }
        return $registro;
    }

    /** Pacotes vinculados ao destino, com a próxima partida e o menor preço futuro. */
    public function pacotes(int $destinoId): array
    {
        return $this->banco->buscarTodos(
            "SELECT p.*, d.nome AS destino,
                    (SELECT MIN(v.data_partida) FROM viagens v
                        WHERE v.pacote_id = p.id AND v.status <> 'cancelada'
                          AND v.data_partida >= CURRENT_DATE) AS proxima_partida,
                    COALESCE((SELECT MIN(v.preco) FROM viagens v
                        WHERE v.pacote_id = p.id AND v.status <> 'cancelada'
                          AND v.data_partida >= CURRENT_DATE), p.preco_base) AS proximo_preco
             FROM pacotes p
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE p.destino_id = :destino
             ORDER BY p.nome ASC",
            ['destino' => $destinoId]
        );
    }

    /** Próximas partidas do destino, com ocupação. */
    public function proximasViagens(int $destinoId, int $limite = 5): array
    {
        return $this->banco->buscarTodos(
            "SELECT v.*, p.nome AS pacote,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r
                              WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS viajantes
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             WHERE p.destino_id = :destino AND v.status <> 'cancelada'
               AND v.data_partida >= CURRENT_DATE
             ORDER BY v.data_partida ASC
             {$this->limiteOffset($limite, 0)}",
            ['destino' => $destinoId]
        );
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'nome' => trim((string) ($dadosBrutos['nome'] ?? '')),
            'cidade' => trim((string) ($dadosBrutos['cidade'] ?? '')),
            'estado' => trim((string) ($dadosBrutos['estado'] ?? '')),
            'pais' => trim((string) ($dadosBrutos['pais'] ?? '')),
            'continente' => trim((string) ($dadosBrutos['continente'] ?? '')),
            'descricao' => trim((string) ($dadosBrutos['descricao'] ?? '')),
        ];

        $erros = [];

        if (mb_strlen($dados['nome']) < 2) {
            $erros['nome'] = 'Informe o nome do destino.';
        }
        if (mb_strlen($dados['cidade']) < 2) {
            $erros['cidade'] = 'Informe a cidade.';
        }
        if ($dados['pais'] === '') {
            $erros['pais'] = 'Informe o país.';
        }
        if ($dados['continente'] === '') {
            $erros['continente'] = 'Informe o continente.';
        }

        return [$dados, $erros];
    }

    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO destinos (nome, cidade, estado, pais, continente, descricao, ativo)
             VALUES (:nome, :cidade, :estado, :pais, :continente, :descricao, 1)',
            $dadosValidados
        );
        return $this->banco->ultimoId();
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $this->banco->executar(
            'UPDATE destinos SET
                nome = :nome, cidade = :cidade, estado = :estado, pais = :pais,
                continente = :continente, descricao = :descricao
             WHERE id = :id',
            $dadosValidados
        );
    }

    public function alternar(int $id): int
    {
        $registro = $this->buscarPorId($id);
        $novoStatus = (int) $registro['ativo'] === 1 ? 0 : 1;
        $this->banco->executar(
            'UPDATE destinos SET ativo = :ativo WHERE id = :id',
            ['ativo' => $novoStatus, 'id' => $id]
        );
        return $novoStatus;
    }
}
