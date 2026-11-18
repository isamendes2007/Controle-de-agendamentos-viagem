<?php
/**
 * Horizonte Viagens — Serviço de autenticação.
 */

declare(strict_types=1);

class AuthService
{
    private UsuarioService $usuarios;

    public function __construct()
    {
        $this->usuarios = new UsuarioService();
    }

    /** Autentica um usuário por e-mail e senha. Retorna os dados do usuário ou null. */
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->usuarios->buscarPorEmail($email);
        if ($usuario === null) {
            return null;
        }
        if ((int) $usuario['ativo'] !== 1) {
            return null;
        }
        if (!password_verify($senha, $usuario['senha'])) {
            return null;
        }

        unset($usuario['senha']);

        $this->usuarios->atualizarUltimoAcesso((int) $usuario['id']);

        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil' => $usuario['perfil'],
            'ativo' => (int) $usuario['ativo'],
        ];
        session_regenerate_id(true);

        return $usuario;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }

    public function estaLogado(): bool
    {
        return !empty($_SESSION['usuario']['id'] ?? null);
    }

    public function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public function ehAdministrador(): bool
    {
        return ($this->usuario()['perfil'] ?? '') === 'administrador';
    }
}
