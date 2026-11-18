<?php
/**
 * Página de erro 404.
 */
http_response_code(404);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página não encontrada · <?= e(APP_NOME) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: "Segoe UI", Arial, sans-serif; color: #18353d; background: radial-gradient(circle at 15% 15%, #e6f4f2, transparent 32%), #f4efe5; }
        .caixa { width: min(100%, 560px); background: #fff; border: 1px solid #e1e5e1; border-radius: 22px; padding: 44px; text-align: center; box-shadow: 0 20px 60px rgba(8, 42, 51, .08); }
        .numero { font-family: Georgia, serif; font-size: 5rem; line-height: 1; color: #0f7c82; margin-bottom: 8px; }
        h1 { font-family: Georgia, serif; font-size: 1.8rem; margin: 0 0 10px; }
        p { color: #647b83; line-height: 1.65; margin: 0 auto 24px; max-width: 390px; font-size: .9rem; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 18px; border-radius: 10px; background: #0f7c82; color: #fff; text-decoration: none; font-size: .84rem; font-weight: 600; }
        a:hover { background: #0b6e74; }
        .marca { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; font-family: Georgia, serif; font-weight: 700; color: #082a33; }
        .marca span:first-child { width: 34px; height: 34px; border-radius: 10px; background: #ee7c5c; display: grid; place-items: center; }
    </style>
</head>
<body>
    <div class="caixa">
        <div class="marca"><span>✦</span><span>Horizonte Viagens</span></div>
        <div class="numero">404</div>
        <h1>Destino não encontrado</h1>
        <p>O endereço que você tentou acessar não existe ou foi movido. Volte ao início para continuar navegando.</p>
        <a href="<?= e(usuario_logado() ? url('dashboard') : url('login')) ?>"><?= usuario_logado() ? 'Voltar ao painel' : 'Ir para o login' ?></a>
    </div>
</body>
</html>
