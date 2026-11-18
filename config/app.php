<?php
/**
 * Configurações gerais da aplicação.
 * Horizonte Viagens — Sistema de Gestão de Agência de Viagens
 */

declare(strict_types=1);

define('APP_NOME', 'Horizonte Viagens');
define('APP_DESCRICAO', 'Sistema de gestão de agência de viagens');
define('APP_VERSAO', '1.0.0');

define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('APP_TIMEZONE', 'America/Sao_Paulo');

define('RAIZ', dirname(__DIR__));
define('APP_PATH', RAIZ . DIRECTORY_SEPARATOR . 'app');
define('VIEW_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'views');
define('CONFIG_PATH', __DIR__);

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', RAIZ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'erros.log');
}

// Local de armazenamento de logs e arquivos temporários.
if (!is_dir(RAIZ . DIRECTORY_SEPARATOR . 'storage')) {
    @mkdir(RAIZ . DIRECTORY_SEPARATOR . 'storage', 0775, true);
}
