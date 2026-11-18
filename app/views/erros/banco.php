<?php
/**
 * Página exibida quando o banco de dados ainda não está configurado.
 */
$detalheTecnico = $detalheTecnico ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Banco de dados não conectado · <?= e(APP_NOME) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: "Segoe UI", Arial, sans-serif; color: #18353d; background: radial-gradient(circle at 18% 12%, #e6f4f2, transparent 32%), #f4efe5; }
        .caixa { width: min(100%, 650px); background: #fff; border: 1px solid #e1e5e1; border-radius: 22px; padding: 40px; box-shadow: 0 20px 60px rgba(8, 42, 51, .08); }
        .icone { width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center; background: #e5f4f3; color: #0f7c82; font-size: 1.7rem; margin-bottom: 18px; }
        h1 { font-family: Georgia, serif; font-size: 1.75rem; margin: 0 0 10px; }
        p { color: #647b83; line-height: 1.65; font-size: .9rem; margin: 0 0 18px; }
        ol { color: #4d666e; line-height: 1.8; font-size: .85rem; padding-left: 20px; margin: 0 0 22px; }
        code { background: #f2f4f1; border: 1px solid #e4e9e4; border-radius: 5px; padding: 1px 5px; font-size: .8rem; }
        .acoes { display: flex; gap: 10px; flex-wrap: wrap; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 17px; border-radius: 10px; background: #0f7c82; color: #fff; text-decoration: none; font-size: .84rem; font-weight: 600; }
        a.secundario { background: #fff; border: 1px solid #d5dcd9; color: #18353d; }
        pre { background: #fbf1ef; border: 1px solid #efd7d3; border-radius: 10px; padding: 12px; color: #9f3d39; white-space: pre-wrap; word-break: break-word; font-size: .71rem; margin: 18px 0 0; }
    </style>
</head>
<body>
    <div class="caixa">
        <div class="icone">⌁</div>
        <h1>Vamos conectar o banco de dados</h1>
        <p>O sistema ainda não conseguiu acessar o MySQL. Siga as etapas abaixo para concluir a instalação.</p>
        <ol>
            <li>Verifique se o MySQL está em execução.</li>
            <li>Abra o <strong>instalador</strong> e informe host, porta, usuário e senha.</li>
            <li>Depois do instalador, remova o arquivo <code>instalador.php</code> do servidor.</li>
        </ol>
        <div class="acoes">
            <a href="<?= e(rtrim(BASE_URL, '/') . '/instalador.php') ?>">Abrir instalador</a>
            <a class="secundario" href="<?= e(rtrim(BASE_URL, '/') . '/instalador.php?diagnostico=1') ?>">Ver diagnóstico</a>
        </div>
        <?php if (!empty($detalheTecnico)): ?>
            <pre><?= e($detalheTecnico) ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>
