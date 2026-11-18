<?php
/**
 * Instalador do Horizonte Viagens.
 *
 * Execute este arquivo pelo navegador para configurar a conexão com o MySQL,
 * criar a estrutura do banco e importar os dados de demonstração.
 *
 * Recomendação: remova este arquivo do servidor após concluir a instalação.
 */

declare(strict_types=1);

$baseUrl = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/instalador.php'))), '/');
if ($baseUrl === '.' || $baseUrl === '/') {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

require __DIR__ . '/config/app.php';
require __DIR__ . '/app/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('horizonte_instalador');
    session_start();
}

$arquivoConfigLocal = CONFIG_PATH . '/database.local.php';
$configLocal = null;
if (is_file($arquivoConfigLocal)) {
    $conteudoLocal = require $arquivoConfigLocal;
    if (is_array($conteudoLocal)) {
        $configLocal = $conteudoLocal;
    }
}

$diagnostico = isset($_GET['diagnostico']);
$driverDetectado = 'sqlite';
if (is_array($configLocal)) {
    // Arquivos antigos do instalador não tinham a chave "driver": eram MySQL.
    $driverDetectado = isset($configLocal['driver']) ? (string) $configLocal['driver'] : 'mysql';
} elseif (getenv('DB_DRIVER')) {
    $driverDetectado = (string) getenv('DB_DRIVER');
}

$valores = [
    'driver' => $driverDetectado,
    'sqlite_path' => (string) ($configLocal['sqlite_path'] ?? (getenv('DB_SQLITE_PATH') ?: RAIZ . '/storage/banco.sqlite')),
    'host' => (string) ($configLocal['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1')),
    'port' => (string) ($configLocal['port'] ?? (getenv('DB_PORT') ?: '3306')),
    'name' => (string) ($configLocal['name'] ?? (getenv('DB_NAME') ?: 'agencia_viagem')),
    'user' => (string) ($configLocal['user'] ?? (getenv('DB_USER') ?: 'root')),
    'pass' => (string) ($configLocal['pass'] ?? (getenv('DB_PASS') ?: '')),
];
$incluirDemo = true;
$recriar = false;
$sobrescrever = false;

$mensagemErro = null;
$mensagemSucesso = null;
$detalhesInstalacao = [];

/** Divide um arquivo SQL em comandos, respeitando strings e comentários. */
function dividir_comandos_sql(string $sql): array
{
    $comandos = [];
    $buffer = '';
    $stringAberta = null;
    $tamanho = strlen($sql);

    for ($i = 0; $i < $tamanho; $i++) {
        $caractere = $sql[$i];

        if ($stringAberta !== null) {
            $buffer .= $caractere;
            if ($caractere === '\\' && $i + 1 < $tamanho) {
                $buffer .= $sql[++$i];
                continue;
            }
            if ($caractere === $stringAberta) {
                $stringAberta = null;
            }
            continue;
        }

        if ($caractere === "'" || $caractere === '"') {
            $stringAberta = $caractere;
            $buffer .= $caractere;
            continue;
        }

        if ($caractere === '-' && ($sql[$i + 1] ?? '') === '-') {
            while ($i < $tamanho && $sql[$i] !== "\n") {
                $i++;
            }
            $buffer .= "\n";
            continue;
        }

        if ($caractere === ';') {
            $comando = trim($buffer);
            if ($comando !== '') {
                $comandos[] = $comando;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $caractere;
    }

    $comando = trim($buffer);
    if ($comando !== '') {
        $comandos[] = $comando;
    }

    return $comandos;
}

/** Remove a senha de uma mensagem de erro antes de exibi-la. */
function limpar_mensagem_erro(string $mensagem, string $senha): string
{
    if ($senha !== '') {
        $mensagem = str_replace($senha, '***', $mensagem);
    }
    return $mensagem;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores = [
        'driver' => strtolower((string) post('driver', 'sqlite')),
        'sqlite_path' => trim((string) post('sqlite_path', RAIZ . '/storage/banco.sqlite')),
        'host' => trim((string) post('host', '127.0.0.1')),
        'port' => trim((string) post('port', '3306')),
        'name' => trim((string) post('name', 'agencia_viagem')),
        'user' => trim((string) post('user', 'root')),
        'pass' => (string) post('pass', ''),
    ];
    $incluirDemo = (string) post('incluir_demo', '0') === '1';
    $recriar = (string) post('recriar', '0') === '1';
    $sobrescrever = (string) post('sobrescrever', '0') === '1';
    $acao = (string) post('acao', 'instalar');

    if (!csrf_valido()) {
        $mensagemErro = 'A sessão do instalador expirou. Recarregue a página e tente novamente.';
    } elseif (!in_array($valores['driver'], ['sqlite', 'mysql'], true)) {
        $mensagemErro = 'Escolha um tipo de banco de dados válido.';
    } elseif ($valores['driver'] === 'sqlite' && !extension_loaded('pdo_sqlite')) {
        $mensagemErro = 'A extensão pdo_sqlite não está habilitada no PHP. Habilite-a ou escolha MySQL.';
    } elseif ($valores['driver'] === 'mysql' && ($valores['host'] === '' || $valores['name'] === '' || $valores['user'] === '')) {
        $mensagemErro = 'Preencha o host, o nome do banco e o usuário do MySQL.';
    } elseif ($valores['driver'] === 'mysql' && !preg_match('/^[A-Za-z0-9_]+$/', $valores['name'])) {
        $mensagemErro = 'O nome do banco deve conter apenas letras, números e sublinhado.';
    } elseif ($valores['driver'] === 'mysql' && (!ctype_digit($valores['port']) || (int) $valores['port'] < 1 || (int) $valores['port'] > 65535)) {
        $mensagemErro = 'Informe uma porta válida para o MySQL (por exemplo, 3306).';
    } elseif (is_file($arquivoConfigLocal) && $acao === 'instalar' && !$sobrescrever) {
        $mensagemErro = 'Já existe uma configuração local. Marque a opção para sobrescrever a configuração antes de reinstalar.';
    }

    if ($mensagemErro === null) {
        try {
            if ($valores['driver'] === 'sqlite') {
                $mensagemSucesso = instalarSqlite($valores, $acao, $incluirDemo, $recriar, $detalhesInstalacao);
            } else {
                $mensagemSucesso = instalarMysql($valores, $acao, $incluirDemo, $recriar, $detalhesInstalacao);
            }

            if ($acao === 'instalar') {
                $configLocal = $valores;
            }
        } catch (PDOException $erro) {
            $mensagemErro = 'Não foi possível conectar ou executar o SQL: ' . limpar_mensagem_erro($erro->getMessage(), $valores['pass']);
        } catch (Throwable $erro) {
            $mensagemErro = $erro->getMessage();
        }
    }
}

/**
 * Cria (ou testa) o banco SQLite e grava a configuração local.
 *
 * @param array<string, string> $valores
 * @param list<string> $detalhes
 */
function instalarSqlite(array $valores, string $acao, bool $incluirDemo, bool $recriar, array &$detalhes): string
{
    $caminho = $valores['sqlite_path'] !== '' ? $valores['sqlite_path'] : RAIZ . '/storage/banco.sqlite';
    $diretorio = dirname($caminho);

    if (!is_dir($diretorio) && !@mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
        throw new RuntimeException('Não foi possível criar a pasta ' . $diretorio . '. Verifique as permissões do servidor.');
    }

    $existe = is_file($caminho) && filesize($caminho) > 0;

    if ($acao === 'testar') {
        if ($existe) {
            $pdo = new PDO('sqlite:' . $caminho, null, null, opcoesPdo(true));
            $detalhes[] = 'Arquivo SQLite acessível (versão ' . $pdo->query('SELECT sqlite_version()')->fetchColumn() . ').';
        } elseif (!is_writable($diretorio)) {
            throw new RuntimeException('A pasta ' . $diretorio . ' não permite escrita.');
        } else {
            $detalhes[] = 'A pasta de dados permite escrita; o arquivo será criado na instalação.';
        }
        return 'Ambiente pronto para usar SQLite. Nenhuma configuração adicional é necessária.';
    }

    if ($existe && !$recriar) {
        throw new RuntimeException(
            'Já existe um banco SQLite em ' . $caminho . '. '
            . 'Marque a opção "Apagar e recriar" para substituí-lo.'
        );
    }

    if ($existe) {
        foreach (['', '-wal', '-shm'] as $sufixo) {
            if (is_file($caminho . $sufixo)) {
                @unlink($caminho . $sufixo);
            }
        }
        $detalhes[] = 'Banco SQLite anterior removido (opção de recriação marcada).';
    }

    $pdo = new PDO('sqlite:' . $caminho, null, null, opcoesPdo(true));
    $pdo->exec('PRAGMA foreign_keys = ON');

    $arquivoSchema = RAIZ . '/database/sqlite/schema.sql';
    if (!is_file($arquivoSchema)) {
        throw new RuntimeException('Arquivo database/sqlite/schema.sql não encontrado.');
    }
    $pdo->exec((string) file_get_contents($arquivoSchema));
    $detalhes[] = 'Estrutura criada no arquivo ' . $caminho . '.';

    if ($incluirDemo) {
        $arquivoSeed = RAIZ . '/database/sqlite/seed.sql';
        if (!is_file($arquivoSeed)) {
            throw new RuntimeException('Arquivo database/sqlite/seed.sql não encontrado.');
        }
        $pdo->exec((string) file_get_contents($arquivoSeed));
        $detalhes[] = 'Dados de demonstração importados.';
    } else {
        $detalhes[] = 'Dados de demonstração não importados (opção desmarcada).';
    }

    $configuracao = ['driver' => 'sqlite'];
    $padrao = str_replace('\\', '/', RAIZ . '/storage/banco.sqlite');
    if (str_replace('\\', '/', $caminho) !== $padrao) {
        $configuracao['sqlite_path'] = $caminho;
    }

    gravarConfiguracaoLocal($configuracao);
    $detalhes[] = 'Arquivo config/database.local.php criado com sucesso.';

    return 'Instalação concluída! O sistema já está pronto para uso.';
}

/**
 * Cria (ou testa) o banco MySQL e grava a configuração local.
 *
 * @param array<string, string> $valores
 * @param list<string> $detalhes
 */
function instalarMysql(array $valores, string $acao, bool $incluirDemo, bool $recriar, array &$detalhes): string
{
    $pdo = new PDO(
        'mysql:host=' . $valores['host'] . ';port=' . $valores['port'] . ';charset=utf8mb4',
        $valores['user'],
        $valores['pass'],
        opcoesPdo(false)
    );

    $versao = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    $detalhes[] = 'Conexão com o MySQL estabelecida (versão ' . $versao . ').';

    if ($acao === 'testar') {
        return 'Conexão testada com sucesso! O servidor MySQL respondeu normalmente.';
    }

    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `' . $valores['name'] . '` '
        . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    $pdo->exec('USE `' . $valores['name'] . '`');
    $detalhes[] = 'Banco de dados "' . $valores['name'] . '" pronto para uso.';

    if ($recriar) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['pagamentos', 'reservas', 'agendamentos', 'viagens', 'pacotes', 'destinos', 'clientes', 'usuarios'] as $tabela) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $tabela . '`');
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $detalhes[] = 'Tabelas anteriores removidas (opção de recriação marcada).';
    }

    $arquivoSchema = RAIZ . '/database/schema.sql';
    if (!is_file($arquivoSchema)) {
        throw new RuntimeException('Arquivo database/schema.sql não encontrado.');
    }

    $comandosSchema = dividir_comandos_sql((string) file_get_contents($arquivoSchema));
    foreach ($comandosSchema as $comando) {
        $pdo->exec($comando);
    }
    $detalhes[] = 'Estrutura criada com ' . count($comandosSchema) . ' comando(s) SQL.';

    $totalUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    if ($incluirDemo) {
        if ($totalUsuarios > 0) {
            $detalhes[] = 'Já existiam usuários cadastrados; os dados de demonstração não foram importados.';
        } else {
            $arquivoSeed = RAIZ . '/database/seed.sql';
            if (!is_file($arquivoSeed)) {
                throw new RuntimeException('Arquivo database/seed.sql não encontrado.');
            }
            $comandosSeed = dividir_comandos_sql((string) file_get_contents($arquivoSeed));
            foreach ($comandosSeed as $comando) {
                $pdo->exec($comando);
            }
            $detalhes[] = 'Dados de demonstração importados (' . count($comandosSeed) . ' comando(s) SQL).';
        }
    } else {
        $detalhes[] = 'Dados de demonstração não importados (opção desmarcada).';
    }

    gravarConfiguracaoLocal([
        'driver' => 'mysql',
        'host' => $valores['host'],
        'port' => $valores['port'],
        'name' => $valores['name'],
        'user' => $valores['user'],
        'pass' => $valores['pass'],
    ]);
    $detalhes[] = 'Arquivo config/database.local.php criado com sucesso.';

    return 'Instalação concluída! O sistema já está pronto para uso.';
}

/** Opções de conexão PDO usadas pelo instalador. */
function opcoesPdo(bool $sqlite): array
{
    return [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => !$sqlite,
        PDO::ATTR_TIMEOUT => 8,
    ];
}

/** Grava config/database.local.php com as credenciais escolhidas. */
function gravarConfiguracaoLocal(array $configuracao): void
{
    $conteudo = "<?php\n"
        . "/**\n"
        . " * Configuração local gerada pelo instalador.\n"
        . " * Este arquivo contém credenciais e não deve ser versionado.\n"
        . " */\n\n"
        . "return " . var_export($configuracao, true) . ";\n";

    if (file_put_contents(CONFIG_PATH . '/database.local.php', $conteudo, LOCK_EX) === false) {
        throw new RuntimeException('Não foi possível gravar config/database.local.php. Verifique as permissões da pasta config.');
    }
}

$pdoDisponivel = extension_loaded('pdo_mysql');
$sqliteDisponivel = extension_loaded('pdo_sqlite');
$configGravavel = is_dir(CONFIG_PATH) && is_writable(CONFIG_PATH);
$storageGravavel = is_dir(RAIZ . '/storage') && is_writable(RAIZ . '/storage');
$instalado = is_file($arquivoConfigLocal);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e3a5f">
    <title>Instalação · <?= e(APP_NOME) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%231e3a5f'/%3E%3Cpath d='M46 20 36 44l-6-10-10-6 26-8Z' fill='%23ffffff'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= e(APP_VERSAO) ?>">
    <style>
        .instalador__grade[hidden], .caixa-info[hidden] { display: none !important; }
        .instalador { width: min(100% - 32px, 780px); margin: 40px auto 60px; }
        .instalador__topo { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
        .instalador__topo .marca { padding: 0; color: var(--cor-texto); text-decoration: none; }
        .instalador__topo .marca__nome { color: var(--cor-texto); }
        .instalador__topo .marca__slogan { color: var(--cor-muted); }
        .instalador__topo .marca__icone { background: var(--marca); color: #fff; }
        .instalador__selo { font-size: .66rem; text-transform: uppercase; letter-spacing: .12em; color: var(--cor-muted); border: 1px solid var(--cor-borda); border-radius: 20px; padding: 5px 11px; background: var(--cor-superficie); }
        .instalador__cartao { background: var(--cor-superficie); border: 1px solid var(--cor-borda); border-radius: var(--raio-card); box-shadow: var(--cor-sombra); padding: 28px; margin-bottom: 18px; }
        .instalador__cartao h1 { font-family: var(--fonte-titulo); font-size: 1.7rem; margin-bottom: 6px; color: var(--cor-texto); }
        .instalador__cartao > p { color: var(--cor-texto-suave); font-size: .84rem; margin-bottom: 20px; }
        .instalador__grade { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .instalador__grade .campo--largo { grid-column: 1 / -1; }
        .instalador__checks { display: flex; flex-direction: column; gap: 9px; margin: 16px 0 20px; }
        .instalador__checks label { display: flex; align-items: flex-start; gap: 9px; font-size: .78rem; color: var(--cor-texto-suave); }
        .instalador__checks input { margin-top: 3px; }
        .instalador__acoes { display: flex; gap: 9px; flex-wrap: wrap; align-items: center; }
        .instalador__lista { margin: 0; padding-left: 20px; color: var(--cor-texto-suave); font-size: .8rem; line-height: 1.8; }
        .instalador__resultado { border-radius: var(--raio-medio); padding: 14px 16px; margin-bottom: 16px; font-size: .8rem; display: flex; gap: 10px; align-items: flex-start; }
        .instalador__resultado--ok { background: var(--sucesso-suave); color: #1c6e50; }
        .instalador__resultado--erro { background: var(--perigo-suave); color: #9f3d39; }
        .instalador__resultado ul { margin: 6px 0 0; padding-left: 18px; }
        .instalador__diag { width: 100%; border-collapse: collapse; font-size: .78rem; }
        .instalador__diag th, .instalador__diag td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--cor-borda); }
        .instalador__diag th { color: var(--cor-muted); font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; }
        .instalador__diag .ok { color: var(--sucesso); font-weight: 600; }
        .instalador__diag .falha { color: var(--perigo); font-weight: 600; }
        .instalador__credenciais { font-size: .78rem; color: var(--cor-texto-suave); line-height: 1.9; }
        .instalador__credenciais code { background: var(--cor-superficie-alt); border: 1px solid var(--cor-borda); border-radius: 5px; padding: 1px 5px; }
        .instalador__nota { font-size: .7rem; color: var(--cor-muted); margin-top: 14px; }
        @media (max-width: 620px) {
            .instalador__grade { grid-template-columns: 1fr; }
            .instalador__cartao { padding: 22px 18px; }
        }
    </style>
</head>
<body>
<div class="instalador">
    <div class="instalador__topo">
        <a class="marca" href="<?= e(($baseUrl ?: '') . '/') ?>">
            <span class="marca__icone"><?= icone('plane', 23) ?></span>
            <span>
                <span class="marca__nome">Horizonte</span>
                <span class="marca__slogan">Viagens</span>
            </span>
        </a>
        <span class="instalador__selo">Instalador · versão <?= e(APP_VERSAO) ?></span>
    </div>

    <?php if ($mensagemErro !== null): ?>
        <div class="instalador__resultado instalador__resultado--erro" role="alert">
            <span><?= icone('alert-circle', 19) ?></span>
            <div>
                <strong>Não foi possível concluir.</strong>
                <div><?= e($mensagemErro) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($mensagemSucesso !== null): ?>
        <div class="instalador__resultado instalador__resultado--ok" role="status">
            <span><?= icone('check-circle', 19) ?></span>
            <div>
                <strong><?= e($mensagemSucesso) ?></strong>
                <?php if ($detalhesInstalacao !== []): ?>
                    <ul>
                        <?php foreach ($detalhesInstalacao as $detalhe): ?>
                            <li><?= e($detalhe) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($diagnostico): ?>
        <section class="instalador__cartao">
            <h1>Diagnóstico do ambiente</h1>
            <p>Verifique os requisitos antes de instalar.</p>
            <table class="instalador__diag">
                <thead>
                    <tr><th>Item</th><th>Situação</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Versão do PHP</td>
                        <td class="<?= version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok' : 'falha' ?>"><?= e(PHP_VERSION) ?><?= version_compare(PHP_VERSION, '8.0.0', '>=') ? ' (compatível)' : ' (requer 8.0+)' ?></td>
                    </tr>
                    <tr>
                        <td>Extensão PDO MySQL</td>
                        <td class="<?= $pdoDisponivel ? 'ok' : 'falha' ?>"><?= $pdoDisponivel ? 'Instalada' : 'Ausente — habilite pdo_mysql no php.ini' ?></td>
                    </tr>
                    <tr>
                        <td>Extensão PDO SQLite</td>
                        <td class="<?= $sqliteDisponivel ? 'ok' : 'falha' ?>"><?= $sqliteDisponivel ? 'Instalada' : 'Ausente — habilite pdo_sqlite no php.ini' ?></td>
                    </tr>
                    <tr>
                        <td>Permissão de escrita em /config</td>
                        <td class="<?= $configGravavel ? 'ok' : 'falha' ?>"><?= $configGravavel ? 'Liberada' : 'Sem permissão' ?></td>
                    </tr>
                    <tr>
                        <td>Permissão de escrita em /storage</td>
                        <td class="<?= $storageGravavel ? 'ok' : 'falha' ?>"><?= $storageGravavel ? 'Liberada' : 'Sem permissão' ?></td>
                    </tr>
                    <tr>
                        <td>Estrutura do banco (MySQL)</td>
                        <td class="<?= is_file(__DIR__ . '/database/schema.sql') ? 'ok' : 'falha' ?>"><?= is_file(__DIR__ . '/database/schema.sql') ? 'Encontrada' : 'Arquivo ausente' ?></td>
                    </tr>
                    <tr>
                        <td>Estrutura do banco (SQLite)</td>
                        <td class="<?= is_file(__DIR__ . '/database/sqlite/schema.sql') ? 'ok' : 'falha' ?>"><?= is_file(__DIR__ . '/database/sqlite/schema.sql') ? 'Encontrada' : 'Arquivo ausente' ?></td>
                    </tr>
                    <tr>
                        <td>Dados de demonstração (MySQL)</td>
                        <td class="<?= is_file(__DIR__ . '/database/seed.sql') ? 'ok' : 'falha' ?>"><?= is_file(__DIR__ . '/database/seed.sql') ? 'Encontrado' : 'Arquivo ausente' ?></td>
                    </tr>
                    <tr>
                        <td>Dados de demonstração (SQLite)</td>
                        <td class="<?= is_file(__DIR__ . '/database/sqlite/seed.sql') ? 'ok' : 'falha' ?>"><?= is_file(__DIR__ . '/database/sqlite/seed.sql') ? 'Encontrado' : 'Arquivo ausente' ?></td>
                    </tr>
                    <tr>
                        <td>Configuração local</td>
                        <td class="<?= $instalado ? 'ok' : '' ?>"><?= $instalado ? 'Já existe (config/database.local.php)' : 'Ainda não criada' ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="instalador__acoes mt-2">
                <a class="botao botao--secundario" href="<?= e($baseUrl . '/instalador.php') ?>"><?= icone('chevron-left', 15) ?> Voltar ao instalador</a>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($mensagemSucesso !== null && str_contains($mensagemSucesso, 'Instalação concluída')): ?>
        <section class="instalador__cartao">
            <h1>Tudo pronto!</h1>
            <p>Acesse o sistema com um dos usuários de demonstração ou com o usuário criado por você.</p>
            <div class="instalador__credenciais">
                <div>Administrador: <code>admin@horizonteviagens.com.br</code> / <code>admin123</code></div>
                <div>Agente: <code>rafael@horizonteviagens.com.br</code> / <code>agente123</code></div>
            </div>
            <div class="instalador__acoes mt-2">
                <a class="botao botao--primario" href="<?= e(($baseUrl ?: '') . '/login') ?>"><?= icone('arrow-right', 16) ?> Entrar no sistema</a>
                <a class="botao botao--secundario" href="<?= e($baseUrl . '/instalador.php?diagnostico=1') ?>"><?= icone('info', 15) ?> Ver diagnóstico</a>
            </div>
            <p class="instalador__nota">Por segurança, remova o arquivo <code>instalador.php</code> do servidor após concluir a instalação.</p>
        </section>
    <?php endif; ?>

    <?php if (!$diagnostico): ?>
        <section class="instalador__cartao">
            <h1>Configurar o banco de dados</h1>
            <p>Escolha onde os dados serão guardados. O SQLite dispensa servidor e é ideal para uso local; o MySQL é indicado para hospedagem.</p>

            <?php if ($instalado): ?>
                <div class="caixa-info caixa-info--amarelo mb-2">
                    <?= icone('alert-triangle', 18) ?>
                    <span>O sistema já possui uma configuração local. Para reinstalar, marque a opção de sobrescrever a configuração.</span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e($baseUrl . '/instalador.php') ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="sqlite_path" value="<?= e($valores['sqlite_path']) ?>">

                <div class="instalador__grade" style="margin-bottom:16px;">
                    <div class="campo campo--largo">
                        <label for="driver">Tipo de banco de dados</label>
                        <select class="controle" id="driver" name="driver" data-escolher-driver>
                            <option value="sqlite" <?= $valores['driver'] === 'sqlite' ? 'selected' : '' ?>>SQLite — arquivo local (recomendado)</option>
                            <option value="mysql" <?= $valores['driver'] === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB — servidor</option>
                        </select>
                        <span class="campo__ajuda">A escolha é gravada em <code>config/database.local.php</code>.</span>
                    </div>
                </div>

                <div class="caixa-info caixa-info--teal mb-2" data-driver="sqlite">
                    <?= icone('info', 18) ?>
                    <span>O banco será criado em <code><?= e($valores['sqlite_path']) ?></code>. A pasta precisa permitir escrita. Nenhum servidor de banco de dados é necessário.</span>
                </div>

                <div class="instalador__grade" data-driver="mysql">
                    <div class="campo">
                        <label for="host">Host <span class="obrigatorio">*</span></label>
                        <input class="controle" type="text" id="host" name="host" value="<?= e($valores['host']) ?>" placeholder="127.0.0.1" required>
                    </div>
                    <div class="campo">
                        <label for="port">Porta <span class="obrigatorio">*</span></label>
                        <input class="controle" type="number" id="port" name="port" value="<?= e($valores['port']) ?>" min="1" max="65535" placeholder="3306" required>
                    </div>
                    <div class="campo campo--largo">
                        <label for="name">Nome do banco de dados <span class="obrigatorio">*</span></label>
                        <input class="controle" type="text" id="name" name="name" value="<?= e($valores['name']) ?>" placeholder="agencia_viagem" pattern="[A-Za-z0-9_]+" required>
                        <span class="campo__ajuda">Será criado caso ainda não exista. Use apenas letras, números e sublinhado.</span>
                    </div>
                    <div class="campo">
                        <label for="user">Usuário do MySQL <span class="obrigatorio">*</span></label>
                        <input class="controle" type="text" id="user" name="user" value="<?= e($valores['user']) ?>" placeholder="root" autocomplete="off" required>
                    </div>
                    <div class="campo">
                        <label for="pass">Senha do MySQL</label>
                        <div class="grupo-senha">
                            <input class="controle" type="password" id="pass" name="pass" value="<?= e($valores['pass']) ?>" placeholder="Deixe em branco se não houver senha" autocomplete="off">
                            <button type="button" class="botao-senha" data-alternar-senha aria-label="Mostrar senha"><?= icone('eye', 18) ?></button>
                        </div>
                        <span class="campo__ajuda">A senha é gravada apenas no arquivo local de configuração.</span>
                    </div>
                </div>

                <div class="instalador__checks">
                    <label>
                        <input type="checkbox" name="incluir_demo" value="1" <?= $incluirDemo ? 'checked' : '' ?>>
                        <span>Importar dados de demonstração (clientes, destinos, pacotes, viagens, reservas e pagamentos).</span>
                    </label>
                    <label>
                        <input type="checkbox" name="recriar" value="1" <?= $recriar ? 'checked' : '' ?>>
                        <span><strong>Apagar e recriar o banco.</strong> Esta opção remove todos os dados existentes no banco selecionado.</span>
                    </label>
                    <?php if ($instalado): ?>
                        <label>
                            <input type="checkbox" name="sobrescrever" value="1" <?= $sobrescrever ? 'checked' : '' ?>>
                            <span>Confirmo que desejo sobrescrever o arquivo <code>config/database.local.php</code> existente.</span>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="instalador__acoes">
                    <button type="submit" name="acao" value="instalar" class="botao botao--primario"><?= icone('download', 16) ?> Instalar sistema</button>
                    <button type="submit" name="acao" value="testar" class="botao botao--secundario"><?= icone('check-circle', 16) ?> Testar conexão</button>
                    <a class="botao botao--fantasma" href="<?= e($baseUrl . '/instalador.php?diagnostico=1') ?>"><?= icone('info', 15) ?> Diagnóstico</a>
                </div>
            </form>
        </section>

        <section class="instalador__cartao">
            <h2 style="font-family:Georgia,serif;font-size:1.1rem;margin-bottom:10px;">Antes de instalar</h2>
            <ol class="instalador__lista">
                <li><strong>SQLite:</strong> não há nada para configurar — a pasta <code>storage</code> precisa apenas permitir escrita.</li>
                <li><strong>MySQL:</strong> inicie o serviço (XAMPP, WAMP, Laragon ou servidor próprio) e confirme a porta, normalmente 3306.</li>
                <li>Para MySQL, use um usuário com permissão para criar bancos e tabelas.</li>
                <li>Após concluir, remova o arquivo <code>instalador.php</code> do servidor.</li>
            </ol>
            <p class="instalador__nota">Compatível com PHP 8.0+, SQLite 3.31+ e MySQL 8.0+ / MariaDB 10.4+. Em hospedagem, também é possível configurar por variáveis de ambiente (DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASS).</p>
        </section>
    <?php endif; ?>
</div>
<script>
(function () {
    var seletor = document.querySelector('[data-escolher-driver]');
    if (!seletor) { return; }

    function atualizar() {
        var escolhido = seletor.value;
        document.querySelectorAll('[data-driver]').forEach(function (bloco) {
            var ativo = bloco.getAttribute('data-driver') === escolhido;
            bloco.hidden = !ativo;
            bloco.querySelectorAll('input, select').forEach(function (campo) {
                campo.disabled = !ativo;
            });
        });
    }

    seletor.addEventListener('change', atualizar);
    atualizar();
})();
</script>
<script src="<?= e(asset('js/app.js')) ?>?v=<?= e(APP_VERSAO) ?>" defer></script>
</body>
</html>
