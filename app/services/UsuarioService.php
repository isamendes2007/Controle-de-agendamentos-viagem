<?php
/**
 * Horizonte Viagens — Serviço de domínio para usuários.
 */

declare(strict_types=1);

class UsuarioService extends BaseService
{
    public function listar(): array
    {
        return $this->banco->buscarTodos(
            'SELECT u.*,
                    COALESCE((SELECT COUNT(*) FROM reservas r WHERE r.usuario_id = u.id), 0) AS total_reservas
             FROM usuarios u
             ORDER BY u.nome ASC'
        );
    }

    public function buscarPorId(int $id): array
    {
        $registro = $this->banco->buscarUm(
            'SELECT id, nome, email, perfil, telefone, ativo, ultimo_acesso, criado_em FROM usuarios WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($registro === null) {
            throw new DomainException('Usuário não encontrado.');
        }
        return $registro;
    }

    public function buscarPorEmail(string $email): ?array
    {
        return $this->banco->buscarUm(
            'SELECT * FROM usuarios WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
    }

    public function validar(array $dadosBrutos, ?int $idAtual = null): array
    {
        $dados = [
            'nome' => trim((string) ($dadosBrutos['nome'] ?? '')),
            'email' => mb_strtolower(trim((string) ($dadosBrutos['email'] ?? ''))),
            'perfil' => trim((string) ($dadosBrutos['perfil'] ?? 'agente')),
            'telefone' => formatar_telefone((string) ($dadosBrutos['telefone'] ?? '')),
            'senha' => (string) ($dadosBrutos['senha'] ?? ''),
            'ativo' => (int) ($dadosBrutos['ativo'] ?? 1),
        ];

        $erros = [];

        if (mb_strlen($dados['nome']) < 3) {
            $erros['nome'] = 'Informe o nome completo.';
        }
        if ($dados['email'] === '' || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        } else {
            $existente = $this->banco->buscarUm(
                'SELECT id FROM usuarios WHERE email = :email' . ($idAtual !== null ? ' AND id <> :id' : ''),
                array_merge(['email' => $dados['email']], $idAtual !== null ? ['id' => $idAtual] : [])
            );
            if ($existente !== null) {
                $erros['email'] = 'Este e-mail já está em uso por outro usuário.';
            }
        }
        if (!in_array($dados['perfil'], ['administrador', 'agente'], true)) {
            $erros['perfil'] = 'Selecione um perfil válido.';
        }
        if ($idAtual === null && mb_strlen($dados['senha']) < 6) {
            $erros['senha'] = 'A senha deve ter pelo menos 6 caracteres.';
        } elseif ($idAtual !== null && $dados['senha'] !== '' && mb_strlen($dados['senha']) < 6) {
            $erros['senha'] = 'A nova senha deve ter pelo menos 6 caracteres.';
        }
        $digitosTelefone = somente_digitos($dados['telefone']);
        if ($digitosTelefone !== '' && (strlen($digitosTelefone) < 10 || strlen($digitosTelefone) > 11)) {
            $erros['telefone'] = 'Informe um telefone com DDD.';
        }

        return [$dados, $erros];
    }

    public function criar(array $dadosValidados): int
    {
        $this->banco->executar(
            'INSERT INTO usuarios (nome, email, senha, perfil, telefone, ativo)
             VALUES (:nome, :email, :senha, :perfil, :telefone, :ativo)',
            [
                'nome' => $dadosValidados['nome'],
                'email' => $dadosValidados['email'],
                'senha' => password_hash($dadosValidados['senha'], PASSWORD_DEFAULT),
                'perfil' => $dadosValidados['perfil'],
                'telefone' => $dadosValidados['telefone'],
                'ativo' => $dadosValidados['ativo'],
            ]
        );
        return $this->banco->ultimoId();
    }

    public function atualizar(int $id, array $dadosValidados): void
    {
        $dadosValidados['id'] = $id;
        $campos = 'nome = :nome, email = :email, perfil = :perfil, telefone = :telefone, ativo = :ativo';
        $parametros = [
            'nome' => $dadosValidados['nome'],
            'email' => $dadosValidados['email'],
            'perfil' => $dadosValidados['perfil'],
            'telefone' => $dadosValidados['telefone'],
            'ativo' => $dadosValidados['ativo'],
            'id' => $id,
        ];
        if ($dadosValidados['senha'] !== '') {
            $campos .= ', senha = :senha';
            $parametros['senha'] = password_hash($dadosValidados['senha'], PASSWORD_DEFAULT);
        }
        $this->banco->executar("UPDATE usuarios SET {$campos} WHERE id = :id", $parametros);
    }

    public function alternar(int $id, int $usuarioLogadoId): void
    {
        if ($id === $usuarioLogadoId) {
            throw new DomainException('Você não pode alterar o status da própria conta.');
        }
        $registro = $this->buscarPorId($id);
        if ($registro['perfil'] === 'administrador') {
            $ativos = (int) ($this->banco->buscarUm(
                "SELECT COUNT(*) AS total FROM usuarios WHERE perfil = 'administrador' AND ativo = 1 AND id <> :id",
                ['id' => $id]
            )['total'] ?? 0);
            if ((int) $registro['ativo'] === 1 && $ativos === 0) {
                throw new DomainException('Não é possível desativar o último administrador ativo.');
            }
        }
        $novoStatus = (int) $registro['ativo'] === 1 ? 0 : 1;
        $this->banco->executar(
            'UPDATE usuarios SET ativo = :ativo WHERE id = :id',
            ['ativo' => $novoStatus, 'id' => $id]
        );
    }

    public function contarAdministradoresAtivos(): int
    {
        return (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM usuarios WHERE perfil = 'administrador' AND ativo = 1"
        )['total'] ?? 0);
    }

    public function atualizarUltimoAcesso(int $id): void
    {
        $this->banco->executar(
            'UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = :id',
            ['id' => $id]
        );
    }

    public function perfilOpcoes(): array
    {
        return [
            'administrador' => 'Administrador',
            'agente' => 'Agente de viagens',
        ];
    }
}
