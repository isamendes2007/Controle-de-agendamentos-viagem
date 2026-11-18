<?php
/**
 * Roteador para o servidor embutido do PHP.
 * Uso: php -S 127.0.0.1:8000 router.php
 */

declare(strict_types=1);

$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$caminho = rawurldecode($caminho);

// Bloqueia travessia de diretórios, arquivos ocultos e pastas internas.
$bloqueado = preg_match('#(^|/)\.\.(/|$)#', $caminho) === 1
    || preg_match('#(^|/)\.(?!well-known(?:/|$))#', $caminho) === 1
    || preg_match('#^/(app|config|database|storage)(/|$)#i', $caminho) === 1;

if ($bloqueado) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<title>Página não encontrada · Horizonte Viagens</title></head>'
        . '<body style="font-family:system-ui,sans-serif;padding:48px;text-align:center;color:#233">'
        . '<h1 style="font-family:Georgia,serif">Página não encontrada</h1>'
        . '<p>O endereço informado não existe ou não está disponível.</p>'
        . '<p><a href="/dashboard">Voltar ao sistema</a></p></body></html>';
    return true;
}

$arquivo = __DIR__ . $caminho;

if ($caminho !== '/' && is_file($arquivo)) {
    return false;
}

require __DIR__ . '/index.php';
