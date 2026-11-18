<?php
$erro = $erro ?? null;
$email = $email ?? '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e3a5f">
    <title>Entrar · <?= e(APP_NOME) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%231e3a5f'/%3E%3Cpath d='M46 20 36 44l-6-10-10-6 26-8Z' fill='%23ffffff'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= e(APP_VERSAO) ?>">
</head>
<body>
<div class="login-pagina">
    <section class="login-hero" aria-label="Apresentação do sistema">
        <a class="marca" href="<?= e(url('login')) ?>">
            <span class="marca__icone"><?= icone('plane', 23) ?></span>
            <span>
                <span class="marca__nome">Horizonte</span>
                <span class="marca__slogan">Viagens</span>
            </span>
        </a>

        <div class="login-hero__conteudo">
            <span class="login-hero__tag"><?= icone('sparkles', 14) ?> Gestão sem complicação</span>
            <h1>O mundo inteiro,<br><em>em um só lugar.</em></h1>
            <p>Centralize clientes, pacotes, reservas e pagamentos. Tenha uma visão completa da operação e proporcione experiências memoráveis aos seus viajantes.</p>

            <div class="login-hero__recursos">
                <span class="login-hero__recurso"><?= icone('clientes', 15) ?> Clientes</span>
                <span class="login-hero__recurso"><?= icone('pacotes', 15) ?> Pacotes</span>
                <span class="login-hero__recurso"><?= icone('reservas', 15) ?> Reservas</span>
                <span class="login-hero__recurso"><?= icone('relatorios', 15) ?> Relatórios</span>
            </div>
        </div>

        <div class="login-hero__rodape">
            <span>Sistema interno da agência</span>
            <span>Acesso seguro · <?= date('Y') ?></span>
        </div>
    </section>

    <main class="login-painel">
        <div class="login-card">
            <a class="marca login-card__marca-mobile" href="<?= e(url('login')) ?>">
                <span class="marca__icone"><?= icone('plane', 23) ?></span>
                <span>
                    <span class="marca__nome">Horizonte</span>
                    <span class="marca__slogan">Viagens</span>
                </span>
            </a>

            <div class="login-card__cabecalho">
                <h2>Bem-vindo(a) de volta</h2>
                <p>Entre com suas credenciais para acessar o painel da agência.</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="login-erro mb-2" role="alert">
                    <?= icone('alert-circle', 18) ?>
                    <span><?= e($erro) ?></span>
                </div>
            <?php endif; ?>

            <form class="login-form" method="post" action="<?= e(url('login')) ?>" data-carregando>
                <?= csrf_campo() ?>
                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="grupo-icone">
                        <?= icone('mail', 17) ?>
                        <input class="controle" type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="seuemail@agencia.com.br" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <div class="grupo-senha">
                        <input class="controle" type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
                        <button type="button" class="botao-senha" data-alternar-senha aria-label="Mostrar senha">
                            <?= icone('eye', 18) ?>
                        </button>
                    </div>
                </div>

                <button type="submit" class="botao botao--primario botao--largo mt-1">
                    Entrar no sistema <?= icone('arrow-right', 17) ?>
                </button>
            </form>

            <div class="login-demo">
                <strong><?= icone('info', 14) ?> Acesso de demonstração</strong>
                <div class="login-demo__linha">
                    Administrador: <code>admin@horizonteviagens.com.br</code> / <code>admin123</code>
                    <button type="button" class="botao botao--fantasma botao--pequeno" data-preencher data-email="admin@horizonteviagens.com.br" data-senha="admin123">Usar</button>
                </div>
                <div class="login-demo__linha">
                    Agente: <code>rafael@horizonteviagens.com.br</code> / <code>agente123</code>
                    <button type="button" class="botao botao--fantasma botao--pequeno" data-preencher data-email="rafael@horizonteviagens.com.br" data-senha="agente123">Usar</button>
                </div>
            </div>

            <div class="login-card__rodape">
                © <?= date('Y') ?> Horizonte Viagens · Todos os direitos reservados
            </div>
        </div>
    </main>
</div>
<script src="<?= e(asset('js/app.js')) ?>?v=<?= e(APP_VERSAO) ?>" defer></script>
</body>
</html>
