<?php
/**
 * Horizonte Viagens — Serviço de domínio para clientes.
 *
 * Centraliza toda a lógica de negócio de clientes: listagem, busca,
 * criação, atualização, ativação/desativação e consultas relacionadas.
 */

declare(strict_types=1);

class ClienteService extends BaseService
{
    /** Lista clientes com filtros de busca e situação, com paginação. */
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
            $condicoes[] = '(c.nome LIKE :busca OR c.email LIKE :busca OR c.cpf LIKE :busca OR c.telefone LIKE :busca)';
            $parametros['busca'] = '%' . $busca . '%';
        }
        if ($situacao === 'ativos') {
            $condicoes[] = 'c.ativo = 1';
        } elseif ($situacao === 'inativos') {
            $condicoes[] = 'c.ativo = 0';
        }

        $onde = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM clientes c {$onde}",
            $parametros
        )['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $registros = $this->banco->buscarTodos(
            "SELECT c.*,
                    COUNT(r.id) AS total_reservas,
                    COALESCE(SUM(CASE WHEN r.status <> 'cancelada' THEN r.valor_total ELSE 0 END), 0) AS valor_reservas
             FROM clientes c
             LEFT JOIN reservas r ON r.cliente_id = c.id
             {$onde}
             GROUP BY c.id
             ORDER BY c.nome ASC
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

    /** Busca um cliente pelo ID ou lança exceção se não encontrado. */
    public function buscarPorId(int $id): array
    {
        $registro = $this->banco->buscarUm(
            'SELECT * FROM clientes WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Cliente não encontrado.');
        }
        return $registro;
    }

    /** Valida e normaliza os dados de um cliente para criação ou edição. */
    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'nome' => trim((string) ($dadosBrutos['nome'] ?? '')),
            'cpf' => formatar_cpf((string) ($dadosBrutos['cpf'] ?? '')),
            'email' => mb_strtolower(trim((string) ($dadosBrutos['email'] ?? ''))),
            'telefone' => formatar_telefone((string) ($dadosBrutos['telefone'] ?? '')),
            'data_nascimento' => $this->paraIso((string) ($dadosBrutos['data_nascimento'] ?? '')),
            'cep' => somente_digitos((string) ($dadosBrutos['cep'] ?? '')),
            'endereco' => trim((string) ($dadosBrutos['endereco'] ?? '')),
            'numero' => trim((string) ($dadosBrutos['numero'] ?? '')),
            'complemento' => trim((string) ($dadosBrutos['complemento'] ?? '')),
            'bairro' => trim((string) ($dadosBrutos['bairro'] ?? '')),
            'cidade' => trim((string) ($dadosBrutos['cidade'] ?? '')),
            'estado' => mb_strtoupper(trim((string) ($dadosBrutos['estado'] ?? ''))),
            'observacoes' => trim((string) ($dadosBrutos['observacoes'] ?? '')),
        ];

        $erros = [];

        if (mb_strlen($dados['nome']) < 3) {
            $erros['nome'] = 'Informe o nome completo do cliente.';
        }
        if (!cpf_valido($dados['cpf'])) {
            $erros['cpf'] = 'Informe um CPF válido.';
        } else {
            $cpfDigitos = somente_digitos($dados['cpf']);
            $existente = $this->banco->buscarUm(
                "SELECT id FROM clientes WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf" .
                ($idAtual !== null ? ' AND id <> :id' : ''),
                array_merge(['cpf' => $cpfDigitos], $idAtual !== null ? ['id' => $idAtual] : [])
            );
            if ($existente !== null) {
                $erros['cpf'] = 'Já existe um cliente cadastrado com este CPF.';
            }
        }
        if ($dados['email'] === '' || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        } elseif ($idAtual !== null) {
            $existenteEmail = $this->banco->buscarUm(
                'SELECT id FROM usuarios WHERE email = :email AND id <> :id',
                ['email' => $dados['email'], 'id' => $idAtual]
            );
            if ($existenteEmail !== null) {
                $erros['email'] = 'Este e-mail já está em uso por outro usuário.';
            }
        } else {
            $existenteEmail = $this->banco->buscarUm(
                'SELECT id FROM clientes WHERE email = :email',
                ['email' => $dados['email']]
            );
            if ($existenteEmail !== null) {
                $erros['email'] = 'Já existe um cliente cadastrado com este e-mail.';
            }
        }
        $digitosTelefone = somente_digitos($dados['telefone']);
        if (strlen($digitosTelefone) < 10 || strlen($digitosTelefone) > 11) {
            $erros['telefone'] = 'Informe um telefone com DDD.';
        }
        if ($dados['data_nascimento'] !== null && $dados['data_nascimento'] > date('Y-m-d')) {
            $erros['data_nascimento'] = 'A data de nascimento não pode estar no futuro.';
        }
        if ($dados['cep'] !== '' && strlen($dados['cep']) !== 8) {
            $erros['cep'] = 'O CEP deve ter 8 dígitos.';
        }
        if ($dados['estado'] !== '' && !preg_match('/^[A-Z]{2}$/', $dados['estado'])) {
            $erros['estado'] = 'Use a sigla do estado com 2 letras.';
        }

        return [$dados, $erros];
    }

    /** Cria um novo cliente. Retorna o ID gerado. */
    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO clientes
                (nome, cpf, email, telefone, data_nascimento, cep, endereco, numero, complemento, bairro, cidade, estado, observacoes, ativo)
             VALUES
                (:nome, :cpf, :email, :telefone, :data_nascimento, :cep, :endereco, :numero, :complemento, :bairro, :cidade, :estado, :observacoes, 1)',
            $dadosValidados
        );
        return $this->banco->ultimoId();
    }

    /** Atualiza um cliente existente. */
    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $this->banco->executar(
            'UPDATE clientes SET
                nome = :nome, cpf = :cpf, email = :email, telefone = :telefone,
                data_nascimento = :data_nascimento, cep = :cep, endereco = :endereco,
                numero = :numero, complemento = :complemento, bairro = :bairro,
                cidade = :cidade, estado = :estado, observacoes = :observacoes
             WHERE id = :id',
            $dadosValidados
        );
    }

    /** Alterna o status ativo/inativo de um cliente. */
    public function alternar(int $id): int
    {
        $registro = $this->buscarPorId($id);
        $novoStatus = (int) $registro['ativo'] === 1 ? 0 : 1;
        $this->banco->executar(
            'UPDATE clientes SET ativo = :ativo WHERE id = :id',
            ['ativo' => $novoStatus, 'id' => $id]
        );
        return $novoStatus;
    }

    /** Resumo de reservas de um cliente. */
    public function resumoReservas(int $clienteId): array
    {
        return $this->banco->buscarUm(
            "SELECT COUNT(*) AS total_reservas,
                    COALESCE(SUM(CASE WHEN status <> 'cancelada' THEN valor_total ELSE 0 END), 0) AS valor_total,
                    COALESCE(SUM(CASE WHEN status = 'confirmada' THEN 1 ELSE 0 END), 0) AS confirmadas
             FROM reservas WHERE cliente_id = :id",
            ['id' => $clienteId]
        ) ?? ['total_reservas' => 0, 'valor_total' => 0, 'confirmadas' => 0];
    }

    /** Reservas de um cliente com detalhes da viagem. */
    public function reservasComDetalhes(int $clienteId): array
    {
        $registros = $this->banco->buscarTodos(
            "SELECT r.*, v.data_partida, v.data_retorno, v.status AS status_viagem,
                    p.nome AS pacote, d.nome AS destino, d.cidade, d.estado, d.pais,
                    COALESCE((SELECT SUM(pg.valor) FROM pagamentos pg WHERE pg.reserva_id = r.id AND pg.status = 'pago'), 0) AS total_pago
             FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE r.cliente_id = :id
             ORDER BY v.data_partida DESC",
            ['id' => $clienteId]
        );

        foreach ($registros as $indice => $registro) {
            $registros[$indice]['em_aberto'] = $this->saldoEmAberto($registro['valor_total'], $registro['total_pago']);
        }

        return $registros;
    }
}
