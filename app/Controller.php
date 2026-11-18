<?php
/**
 * Horizonte Viagens — controlador base.
 *
 * Concentra a renderização das visões, o acesso ao banco, o controle de
 * mensagens e as verificações comuns a todos os módulos.
 */

declare(strict_types=1);

abstract class Controller
{
    protected Database $banco;

    /** Cache dos indicadores exibidos no menu lateral. */
    private static ?array $navegacao = null;

    public function __construct()
    {
        $this->banco = banco();
    }

    /**
     * Renderiza uma visão dentro de um layout.
     *
     * @param array<string, mixed> $dados
     */
    protected function visao(string $arquivo, array $dados = [], string $layout = 'app'): void
    {
        $caminho = VIEW_PATH . '/' . $arquivo . '.php';

        if (!is_file($caminho)) {
            throw new RuntimeException('Visão não encontrada: ' . $arquivo);
        }

        $tituloPagina = (string) ($dados['tituloPagina'] ?? APP_NOME);
        $subtituloPagina = (string) ($dados['subtituloPagina'] ?? '');
        $acoesPagina = '';

        extract($dados, EXTR_SKIP);

        ob_start();
        require $caminho;
        $conteudo = ob_get_clean();

        if ($layout === '') {
            echo $conteudo;
            return;
        }

        $arquivoLayout = VIEW_PATH . '/layouts/' . $layout . '.php';
        if (!is_file($arquivoLayout)) {
            throw new RuntimeException('Layout não encontrado: ' . $layout);
        }

        $navegacao = $this->dadosNavegacao();
        $navPendentes = $navegacao['pendentes'];
        $navAtrasados = $navegacao['atrasados'];

        require $arquivoLayout;
    }

    /**
     * Renderiza uma página completa sem passar pelo layout principal
     * (login, páginas de impressão e páginas de erro).
     *
     * @param array<string, mixed> $dados
     */
    protected function pagina(string $layout, array $dados = []): void
    {
        $arquivoLayout = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!is_file($arquivoLayout)) {
            throw new RuntimeException('Layout não encontrado: ' . $layout);
        }

        extract($dados, EXTR_SKIP);
        require $arquivoLayout;
    }

    /** Usuário autenticado na sessão. */
    protected function usuario(): array
    {
        return usuario_atual() ?? [];
    }

    /** Restringe a ação ao perfil administrador. */
    protected function somenteAdministrador(): void
    {
        if (!usuario_administrador()) {
            flash('erro', 'Esta área é exclusiva para administradores.');
            redirecionar('dashboard');
        }
    }

    /** Exige requisição POST. */
    protected function exigirPost(): void
    {
        if (metodo() !== 'POST') {
            flash('erro', 'Ação inválida. Utilize o formulário do sistema.');
            redirecionar('dashboard');
        }
    }

    /** Converte o identificador recebido na rota em inteiro positivo. */
    protected function idDaRota(string $id): int
    {
        $id = (int) $id;

        if ($id <= 0) {
            http_response_code(404);
            require VIEW_PATH . '/erros/404.php';
            exit;
        }

        return $id;
    }

    /** Redireciona com mensagem de sucesso. */
    protected function sucesso(string $mensagem, string $rota, array $parametros = []): void
    {
        flash('sucesso', $mensagem);
        redirecionar($rota, $parametros);
    }

    /** Redireciona com mensagem de erro. */
    protected function erro(string $mensagem, string $rota, array $parametros = []): void
    {
        flash('erro', $mensagem);
        redirecionar($rota, $parametros);
    }

    /**
     * Contadores exibidos no menu (agendamentos pendentes e cobranças atrasadas).
     *
     * @return array{pendentes: int, atrasados: int}
     */
    private function dadosNavegacao(): array
    {
        if (self::$navegacao !== null) {
            return self::$navegacao;
        }

        $pendentes = 0;
        $atrasados = 0;

        if (usuario_logado()) {
            try {
                $pendentes = (int) ($this->banco->buscarUm(
                    "SELECT COUNT(*) AS total FROM agendamentos WHERE status = 'solicitado'"
                )['total'] ?? 0);

                $atrasados = (int) ($this->banco->buscarUm(
                    "SELECT COUNT(*) AS total FROM pagamentos p
                     INNER JOIN reservas r ON r.id = p.reserva_id
                     WHERE p.status = 'atrasado' AND r.status <> 'cancelada'"
                )['total'] ?? 0);
            } catch (Throwable $erro) {
                error_log('[Horizonte Viagens] Falha ao carregar contadores do menu: ' . $erro->getMessage());
            }
        }

        self::$navegacao = ['pendentes' => $pendentes, 'atrasados' => $atrasados];

        return self::$navegacao;
    }
}
