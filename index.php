<?php
/**
 * Horizonte Viagens — front controller único.
 *
 * Resolve a rota informada em ?r=, aplica as regras de acesso e CSRF e
 * despacha para o controlador correspondente. Funciona igual no servidor
 * embutido do PHP, no Apache e em hospedagem compartilhada.
 */

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$rota = trim((string) ($_GET['r'] ?? 'dashboard'), '/');
if ($rota === '') {
    $rota = 'dashboard';
}

$partes = explode('/', $rota);
$modulo = (string) $partes[0];
$verbo = metodo();

$controladores = [
    'dashboard'     => 'DashboardController',
    'clientes'      => 'ClientesController',
    'destinos'      => 'DestinosController',
    'pacotes'       => 'PacotesController',
    'viagens'       => 'ViagensController',
    'agendamentos'  => 'AgendamentosController',
    'reservas'      => 'ReservasController',
    'pagamentos'    => 'PagamentosController',
    'relatorios'    => 'RelatoriosController',
    'usuarios'      => 'UsuariosController',
    'perfil'        => 'PerfilController',
    'login'         => 'AuthController',
    'sair'          => 'AuthController',
];

if (!isset($controladores[$modulo])) {
    http_response_code(404);
    require VIEW_PATH . '/erros/404.php';
    exit;
}

$classe = $controladores[$modulo];
$acao = null;
$parametros = [];
$acesso = in_array($modulo, ['login', 'sair'], true) ? 'publica' : 'autenticada';

if ($modulo === 'login') {
    $acao = $verbo === 'POST' ? 'entrar' : 'formulario';
} elseif ($modulo === 'sair') {
    $acao = 'sair';
} elseif ($modulo === 'dashboard') {
    $acao = 'index';
} elseif ($modulo === 'perfil') {
    $acao = $verbo === 'POST' ? 'salvar' : 'index';
} elseif ($modulo === 'relatorios') {
    $acao = match ($partes[1] ?? '') {
        '', 'index'     => 'index',
        'imprimir'      => 'imprimir',
        'exportar'      => 'exportar',
        default         => null,
    };
} elseif ($modulo === 'usuarios') {
    $acesso = 'admin';
}

if ($acao === null) {
    $segundo = (string) ($partes[1] ?? '');

    if ($segundo === '') {
        $acao = 'index';
    } elseif ($segundo === 'novo') {
        $acao = $verbo === 'POST' ? 'criar' : 'novo';
    } elseif ($segundo === 'nova') {
        $acao = $verbo === 'POST' ? 'criar' : 'nova';
    } elseif (ctype_digit($segundo)) {
        $parametros[] = $segundo;
        $terceiro = (string) ($partes[2] ?? '');

        if ($terceiro === '') {
            $acao = $modulo === 'usuarios' ? 'editar' : 'mostrar';
        } elseif ($terceiro === 'editar') {
            $acao = $verbo === 'POST' ? 'atualizar' : 'editar';
        } else {
            $acao = match ($terceiro) {
                'status'             => 'atualizarStatus',
                'alternar'           => 'alternar',
                'baixar'             => 'baixar',
                'estornar'           => 'estornar',
                'excluir'            => 'excluir',
                'converter',
                'converter-reserva',
                'converterReserva'   => 'converterReserva',
                default              => null,
            };
        }
    }
}

if ($acao === null) {
    http_response_code(404);
    require VIEW_PATH . '/erros/404.php';
    exit;
}

if ($acesso !== 'publica') {
    exigir_login();
}
if ($acesso === 'admin') {
    exigir_administrador();
}

// Toda escrita passa pela validação do token da sessão.
if ($verbo === 'POST' && !csrf_valido()) {
    http_response_code(419);
    require VIEW_PATH . '/erros/419.php';
    exit;
}

try {
    if (!class_exists($classe)) {
        throw new RuntimeException('Controlador não encontrado: ' . $classe);
    }

    $controlador = new $classe();
    $controlador->{$acao}(...$parametros);
} catch (DomainException $erro) {
    error_log('[Horizonte Viagens] ' . $erro->getMessage());

    if (str_contains(mb_strtolower($erro->getMessage()), 'não encontrad')) {
        http_response_code(404);
        require VIEW_PATH . '/erros/404.php';
        exit;
    }

    http_response_code(422);
    $detalheErro = APP_DEBUG ? $erro->getMessage() : null;
    require VIEW_PATH . '/erros/500.php';
} catch (PDOException $erro) {
    error_log('[Horizonte Viagens] ' . $erro->getMessage());

    $mensagem = mb_strtolower($erro->getMessage());
    if (str_contains($mensagem, 'connect') || str_contains($mensagem, 'conectar') || str_contains($mensagem, 'unable to open database')) {
        http_response_code(500);
        $detalheTecnico = APP_DEBUG ? $erro->getMessage() : null;
        require VIEW_PATH . '/erros/banco.php';
        exit;
    }

    http_response_code(500);
    $detalheErro = APP_DEBUG ? $erro->getMessage() : null;
    require VIEW_PATH . '/erros/500.php';
} catch (Throwable $erro) {
    error_log('[Horizonte Viagens] ' . $erro->getMessage() . ' em ' . $erro->getFile() . ':' . $erro->getLine());

    http_response_code(500);
    $detalheErro = APP_DEBUG
        ? $erro->getMessage() . ' (' . basename($erro->getFile()) . ':' . $erro->getLine() . ')'
        : null;
    require VIEW_PATH . '/erros/500.php';
}
