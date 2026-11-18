<?php
/**
 * Autenticação e controle de acesso.
 */

declare(strict_types=1);

/** Devolve o usuário autenticado na sessão, ou null. */
function usuario_atual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Verifica se há um usuário autenticado. */
function usuario_logado(): bool
{
    return isset($_SESSION['usuario']['id']);
}

/** Indica se o usuário atual é administrador. */
function usuario_administrador(): bool
{
    return (usuario_atual()['perfil'] ?? '') === 'administrador';
}

/** Exige autenticação para acessar a página. */
function exigir_login(): void
{
    if (!usuario_logado()) {
        flash('atencao', 'Faça login para acessar o sistema.');
        redirecionar('login');
    }

    // Marca uma única vez por sessão as cobranças vencidas como atrasadas.
    if (empty($_SESSION['atrasos_verificados'])) {
        $_SESSION['atrasos_verificados'] = true;
        try {
            (new PagamentoService())->atualizarAtrasados();
        } catch (Throwable $erro) {
            error_log('[Horizonte Viagens] Falha ao atualizar cobranças atrasadas: ' . $erro->getMessage());
        }
    }
}

/** Exige perfil de administrador. */
function exigir_administrador(): void
{
    exigir_login();
    if (!usuario_administrador()) {
        flash('erro', 'Você não tem permissão para acessar esta área.');
        redirecionar('dashboard');
    }
}

/** Encerra a sessão do usuário (a autenticação em si fica no AuthService). */
function encerrar_sessao(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
    }

    session_destroy();
}
