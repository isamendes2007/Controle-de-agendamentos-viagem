<?php
/**
 * Configuração de acesso ao banco de dados.
 *
 * O sistema funciona com dois drivers:
 *
 * 1. SQLite (padrão) — zero configuração. O banco é um arquivo em
 *    storage/banco.sqlite, criado automaticamente na primeira execução.
 *    Ideal para desenvolvimento local, demonstração e hospedagem simples.
 *
 * 2. MySQL / MariaDB — indicado para hospedagem compartilhada ou servidor
 *    dedicado. Configure pelas variáveis de ambiente DB_DRIVER=mysql,
 *    DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS, ou crie o arquivo
 *    config/database.local.php retornando:
 *
 *    return [
 *        'driver' => 'mysql',
 *        'host' => '127.0.0.1',
 *        'port' => '3306',
 *        'name' => 'agencia_viagem',
 *        'user' => 'root',
 *        'pass' => '',
 *    ];
 */

declare(strict_types=1);

$raizProjeto = dirname(__DIR__);

$configuracaoBanco = [
    'driver' => 'sqlite',
    'sqlite_path' => $raizProjeto . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'banco.sqlite',
    // Cria estrutura e dados de demonstração quando o arquivo SQLite não existe.
    'auto_instalar' => true,
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'agencia_viagem',
    'user' => 'root',
    'pass' => '',
];

$arquivoLocal = __DIR__ . DIRECTORY_SEPARATOR . 'database.local.php';
$configuracaoLocal = is_file($arquivoLocal) ? require $arquivoLocal : null;

if (is_array($configuracaoLocal)) {
    // Arquivos antigos (gerados por versões anteriores do instalador) só
    // traziam host/porta/usuário. Nesse caso o driver é MySQL.
    if (!isset($configuracaoLocal['driver'])) {
        $configuracaoBanco['driver'] = 'mysql';
    }
    $configuracaoBanco = array_merge($configuracaoBanco, $configuracaoLocal);
}

// Variáveis de ambiente têm prioridade sobre o arquivo local. Isso permite
// publicar em serviços de hospedagem sem gravar credenciais no código.
$variaveisAmbiente = [
    'driver' => 'DB_DRIVER',
    'sqlite_path' => 'DB_SQLITE_PATH',
    'host' => 'DB_HOST',
    'port' => 'DB_PORT',
    'name' => 'DB_NAME',
    'user' => 'DB_USER',
    'pass' => 'DB_PASS',
];

foreach ($variaveisAmbiente as $chave => $variavel) {
    $valor = getenv($variavel);
    if ($valor !== false && $valor !== '') {
        $configuracaoBanco[$chave] = $valor;
    }
}

$driver = strtolower((string) $configuracaoBanco['driver']);
if (!in_array($driver, ['sqlite', 'mysql'], true)) {
    throw new RuntimeException(
        'Driver de banco de dados inválido: "' . $driver . '". Use "sqlite" ou "mysql".'
    );
}

define('DB_DRIVER', $driver);
define('DB_SQLITE_PATH', (string) $configuracaoBanco['sqlite_path']);
define('DB_AUTO_INSTALAR', (bool) $configuracaoBanco['auto_instalar']);
define('DB_HOST', (string) $configuracaoBanco['host']);
define('DB_PORT', (string) $configuracaoBanco['port']);
define('DB_NAME', (string) $configuracaoBanco['name']);
define('DB_USER', (string) $configuracaoBanco['user']);
define('DB_PASS', (string) $configuracaoBanco['pass']);
