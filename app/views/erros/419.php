<?php
/**
 * Página exibida quando o token CSRF do formulário expirou.
 */
http_response_code(419);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sessão expirada · <?= e(APP_NOME) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: "Segoe UI", Arial, sans-serif; color: #18353d; background: radial-gradient(circle at 15% 15%, #e6f4f2, transparent 32%), #f4efe5; }
        .caixa { width: min(100%, 540px); background: #fff; border: 1px solid #e1e5e1; border-radius: 22px; padding: 42px; text-align: center; box-shadow: 0 20px 60px rgba(8, 42, 51, .08); }
        .numero { font-family: Georgia, serif; font-size: 4rem; line-height: 1; color: #0f7c82; margin-bottom: 10px; }
        h1 { font-family: Georgia, serif; font-size: 1.7rem; margin: 0 0 10px; }
        p { color: #647b83; line-height: 1.65; margin: 0 auto 24px; max-width: 380px; font-size: .9rem; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 18px; border-radius: 10px; background: #0f7c82; color: #fff; text-decoration: none; font-size: .84rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="caixa">
        <div class="numero">419</div>
        <h1>Sessão expirada</h1>
        <p>O formulário ficou aberto por muito tempo ou a sessão foi encerrada. Volte à página anterior, recarregue o formulário e envie novamente.</p>
        <a href="<?= e(usuario_logado() ? url('dashboard') : url('login')) ?>">Voltar ao sistema</a>
    </div>
</body>
</html>
