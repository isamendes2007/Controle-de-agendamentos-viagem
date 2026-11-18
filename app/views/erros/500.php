<?php
/**
 * Página de erro interno.
 */
$detalheErro = $detalheErro ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Algo deu errado · <?= e(APP_NOME) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: "Segoe UI", Arial, sans-serif; color: #18353d; background: radial-gradient(circle at 15% 15%, #fceee9, transparent 32%), #f4efe5; }
        .caixa { width: min(100%, 600px); background: #fff; border: 1px solid #e1e5e1; border-radius: 22px; padding: 42px; text-align: center; box-shadow: 0 20px 60px rgba(8, 42, 51, .08); }
        .icone { width: 62px; height: 62px; border-radius: 18px; display: grid; place-items: center; margin: 0 auto 18px; background: #fbe9e8; color: #c94f4b; font-size: 1.8rem; }
        h1 { font-family: Georgia, serif; font-size: 1.8rem; margin: 0 0 10px; }
        p { color: #647b83; line-height: 1.65; margin: 0 auto 24px; max-width: 410px; font-size: .9rem; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 18px; border-radius: 10px; background: #0f7c82; color: #fff; text-decoration: none; font-size: .84rem; font-weight: 600; }
        a:hover { background: #0b6e74; }
        pre { text-align: left; background: #f4f2ed; border: 1px solid #e4dfd4; border-radius: 10px; padding: 14px; color: #8f3b37; white-space: pre-wrap; word-break: break-word; font-size: .72rem; margin: 0 0 22px; }
    </style>
</head>
<body>
    <div class="caixa">
        <div class="icone">!</div>
        <h1>Algo deu errado</h1>
        <p>Não foi possível concluir esta operação. Nossa equipe registrou o ocorrido. Tente novamente em instantes.</p>
        <?php if (!empty($detalheErro)): ?>
            <pre><?= e($detalheErro) ?></pre>
        <?php endif; ?>
        <a href="<?= e(usuario_logado() ? url('dashboard') : url('login')) ?>">Voltar com segurança</a>
    </div>
</body>
</html>
