<?php
/**
 * Autenticação: entrada e saída do sistema.
 */

declare(strict_types=1);

class AuthController extends Controller
{
    public function formulario(): void
    {
        if (usuario_logado()) {
            redirecionar('dashboard');
        }

        $this->pagina('auth', [
            'erro'  => null,
            'email' => (string) get('email'),
        ]);
    }

    public function entrar(): void
    {
        $this->exigirPost();

        if (usuario_logado()) {
            redirecionar('dashboard');
        }

        // Limite simples de tentativas por sessão (5 em 10 minutos).
        $agora = time();
        $tentativas = array_values(array_filter(
            (array) ($_SESSION['tentativas_login'] ?? []),
            static fn ($instante): bool => (int) $instante > $agora - 600
        ));

        if (count($tentativas) >= 5) {
            flash('erro', 'Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.');
            redirecionar('login');
        }

        $email = mb_strtolower((string) post('email'));
        $senha = (string) post('senha');

        if ($email === '' || $senha === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('erro', 'Informe um e-mail válido e a senha de acesso.');
            redirecionar('login');
        }

        $usuario = (new AuthService())->autenticar($email, $senha);

        if ($usuario === null) {
            $tentativas[] = $agora;
            $_SESSION['tentativas_login'] = $tentativas;
            flash('erro', 'E-mail ou senha inválidos. Confira os dados e tente novamente.');
            redirecionar('login');
        }

        unset($_SESSION['tentativas_login'], $_SESSION['atrasos_verificados']);
        flash('sucesso', 'Bem-vindo(a), ' . $usuario['nome'] . '!');
        redirecionar('dashboard');
    }

    public function sair(): void
    {
        $this->exigirPost();

        encerrar_sessao();
        session_start();
        session_regenerate_id(true);

        flash('info', 'Sessão encerrada com sucesso.');
        redirecionar('login');
    }
}
