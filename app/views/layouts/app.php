<?php
/**
 * Layout principal da aplicação.
 *
 * Recebe: $tituloPagina, $subtituloPagina, $acoesPagina, $conteudo,
 * $navPendentes (agendamentos aguardando) e $navAtrasados (cobranças vencidas).
 */
$rotaAtual = trim((string) ($_GET['r'] ?? 'dashboard'), '/');
$moduloAtual = explode('/', $rotaAtual)[0] ?: 'dashboard';
$menuAtivo = static fn (string ...$modulos): string => in_array($moduloAtual, $modulos, true) ? 'is-active' : '';
$mensagens = flash_obter();
$nomeUsuario = (string) (usuario_atual()['nome'] ?? 'Usuário');
$primeiroNome = explode(' ', trim($nomeUsuario))[0] ?: 'Usuário';
$perfilUsuario = (string) (usuario_atual()['perfil'] ?? 'agente');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e3a5f">
    <title><?= e($tituloPagina) ?> · <?= e(APP_NOME) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%231e3a5f'/%3E%3Cpath d='M46 20 36 44l-6-10-10-6 26-8Z' fill='%23ffffff'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= e(APP_VERSAO) ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="marca" href="<?= e(url('dashboard')) ?>" aria-label="Ir para o painel">
            <span class="marca__icone"><?= icone('plane', 22) ?></span>
            <span>
                <span class="marca__nome">Horizonte</span>
                <span class="marca__slogan">Viagens</span>
            </span>
        </a>

        <nav class="nav-principal" aria-label="Menu principal">
            <div class="nav-grupo">
                <span class="nav-rotulo">Visão geral</span>
                <a class="nav-link <?= $menuAtivo('dashboard') ?>" href="<?= e(url('dashboard')) ?>">
                    <?= icone('painel', 18) ?> <span>Painel</span>
                </a>
            </div>

            <div class="nav-grupo">
                <span class="nav-rotulo">Operação</span>
                <a class="nav-link <?= $menuAtivo('clientes') ?>" href="<?= e(url('clientes')) ?>">
                    <?= icone('usuarios', 18) ?> <span>Clientes</span>
                </a>
                <a class="nav-link <?= $menuAtivo('destinos') ?>" href="<?= e(url('destinos')) ?>">
                    <?= icone('destino', 18) ?> <span>Destinos</span>
                </a>
                <a class="nav-link <?= $menuAtivo('pacotes') ?>" href="<?= e(url('pacotes')) ?>">
                    <?= icone('pacote', 18) ?> <span>Pacotes</span>
                </a>
                <a class="nav-link <?= $menuAtivo('viagens') ?>" href="<?= e(url('viagens')) ?>">
                    <?= icone('viagens', 18) ?> <span>Viagens</span>
                </a>
                <a class="nav-link <?= $menuAtivo('agendamentos') ?>" href="<?= e(url('agendamentos')) ?>">
                    <?= icone('agendamentos', 18) ?> <span>Agendamentos</span>
                    <?php if (($navPendentes ?? 0) > 0): ?>
                        <span class="nav-badge"><?= (int) $navPendentes ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link <?= $menuAtivo('reservas') ?>" href="<?= e(url('reservas')) ?>">
                    <?= icone('reserva', 18) ?> <span>Reservas</span>
                </a>
            </div>

            <div class="nav-grupo">
                <span class="nav-rotulo">Financeiro</span>
                <a class="nav-link <?= $menuAtivo('pagamentos') ?>" href="<?= e(url('pagamentos')) ?>">
                    <?= icone('pagamento', 18) ?> <span>Pagamentos</span>
                    <?php if (($navAtrasados ?? 0) > 0): ?>
                        <span class="nav-badge"><?= (int) $navAtrasados ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link <?= $menuAtivo('relatorios') ?>" href="<?= e(url('relatorios')) ?>">
                    <?= icone('relatorio', 18) ?> <span>Relatórios</span>
                </a>
            </div>

            <?php if ($perfilUsuario === 'administrador'): ?>
                <div class="nav-grupo">
                    <span class="nav-rotulo">Administração</span>
                    <a class="nav-link <?= $menuAtivo('usuarios') ?>" href="<?= e(url('usuarios')) ?>">
                        <?= icone('lock', 18) ?> <span>Usuários</span>
                    </a>
                </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar__rodape">
            <a class="usuario-mini" href="<?= e(url('perfil')) ?>">
                <span class="avatar"><?= e(iniciais($nomeUsuario)) ?></span>
                <span class="usuario-mini__dados">
                    <span class="usuario-mini__nome"><?= e($primeiroNome) ?></span>
                    <span class="usuario-mini__cargo"><?= e(rotulo_perfil($perfilUsuario)) ?></span>
                </span>
            </a>
            <form method="post" action="<?= e(url('sair')) ?>">
                <?= csrf_campo() ?>
                <button type="submit" class="sidebar__sair"><?= icone('sair', 16) ?> Sair do sistema</button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" data-fechar-menu></div>

    <div class="app-main">
        <header class="topbar">
            <button type="button" class="topbar__menu" data-abrir-menu aria-label="Abrir menu">
                <?= icone('menu', 20) ?>
            </button>

            <div class="topbar__contexto">
                <strong><?= e(APP_NOME) ?></strong>
                <span class="topbar__separador">/</span>
                <span><?= e($tituloPagina) ?></span>
            </div>

            <div class="topbar__acoes">
                <span class="topbar__data"><?= icone('calendario', 15) ?> <?= e(date('d/m/Y')) ?></span>

                <?php if (($navAtrasados ?? 0) > 0): ?>
                    <a class="topbar__icone" href="<?= e(url('pagamentos', ['status' => 'atrasado'])) ?>" title="Cobranças em atraso">
                        <?= icone('alerta', 17) ?>
                        <span class="topbar__alerta"><?= (int) $navAtrasados ?></span>
                    </a>
                <?php endif; ?>

                <a class="usuario-mini" href="<?= e(url('perfil')) ?>" title="Meu perfil">
                    <span class="avatar"><?= e(iniciais($nomeUsuario)) ?></span>
                    <span class="usuario-mini__dados">
                        <span class="usuario-mini__nome"><?= e($primeiroNome) ?></span>
                        <span class="usuario-mini__cargo"><?= e(rotulo_perfil($perfilUsuario)) ?></span>
                    </span>
                </a>
            </div>
        </header>

        <main class="conteudo">
            <?php foreach ($mensagens as $mensagem): ?>
                <?php $tipoMensagem = in_array($mensagem['tipo'], ['sucesso', 'erro', 'atencao', 'info'], true) ? $mensagem['tipo'] : 'info'; ?>
                <div class="flash flash--<?= e($tipoMensagem) ?>" role="status">
                    <span class="flash__icone"><?= icone($tipoMensagem === 'sucesso' ? 'check-circle' : ($tipoMensagem === 'erro' ? 'alert-circle' : 'info'), 18) ?></span>
                    <span class="flash__texto"><?= e($mensagem['mensagem']) ?></span>
                    <button type="button" class="flash__fechar" data-fechar-alerta aria-label="Fechar mensagem"><?= icone('fechar', 15) ?></button>
                </div>
            <?php endforeach; ?>

            <div class="cabecalho-pagina">
                <div class="cabecalho-pagina__texto">
                    <h1><?= e($tituloPagina) ?></h1>
                    <?php if ($subtituloPagina !== ''): ?><p><?= e($subtituloPagina) ?></p><?php endif; ?>
                </div>
                <?php if (trim((string) $acoesPagina) !== ''): ?>
                    <div class="cabecalho-pagina__acoes"><?= $acoesPagina ?></div>
                <?php endif; ?>
            </div>

            <?= $conteudo ?>
        </main>

        <footer class="rodape-sistema">
            <span><?= e(APP_NOME) ?> · Sistema de gestão · v<?= e(APP_VERSAO) ?></span>
            <span>Horizonte Viagens Ltda. — CNPJ 00.000.000/0001-00</span>
        </footer>
    </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>?v=<?= e(APP_VERSAO) ?>" defer></script>
</body>
</html>
