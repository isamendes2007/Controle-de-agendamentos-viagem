<?php
/**
 * Horizonte Viagens — inicialização única da aplicação.
 *
 * Carrega as constantes de configuração, o autoload das classes internas,
 * as funções auxiliares de interface e as funções de autenticação, e abre
 * a sessão. É o único ponto de inicialização usado pelo front controller
 * (index.php) e pelo instalador.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

/**
 * Autoload das classes internas (Database, Controller, serviços e controladores).
 * Não há namespaces: cada classe é um arquivo em app/, app/Controllers/ ou app/services/.
 */
spl_autoload_register(static function (string $classe): void {
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $classe)) {
        return;
    }

    foreach ([APP_PATH, APP_PATH . '/Controllers', APP_PATH . '/services'] as $pasta) {
        $arquivo = $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});

require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/Database.php';
require_once APP_PATH . '/auth.php';

// URL base da instalação (funciona na raiz do domínio ou em subpastas).
if (!defined('BASE_URL')) {
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    define('BASE_URL', $base === '.' ? '' : $base);
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('horizonte_sessao');
    session_start();
}
