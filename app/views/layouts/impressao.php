<?php
$tituloDocumento = $tituloDocumento ?? 'Relatório gerencial';
$emitidoEm = $emitidoEm ?? date('d/m/Y \à\s H:i');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloDocumento) ?> · <?= e(APP_NOME) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= e(APP_VERSAO) ?>">
    <style>
        :root { --doc-linha: var(--cor-borda); }
        body.pagina-impressao { background: var(--cor-fundo); color: var(--cor-texto); font-family: var(--fonte-interface); }
        .doc-barra { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 24px; background: var(--marca); color: #fff; }
        .doc-barra__acoes { display: flex; gap: 8px; flex-wrap: wrap; }
        .doc-barra .botao { text-decoration: none; }
        .doc-folha { width: min(100% - 32px, 980px); margin: 24px auto 40px; background: var(--cor-superficie); border-radius: var(--raio-card); box-shadow: var(--cor-sombra-media); padding: 42px 46px 36px; }
        .doc-topo { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; border-bottom: 3px solid var(--marca); padding-bottom: 18px; margin-bottom: 26px; }
        .doc-marca { display: flex; align-items: center; gap: 12px; }
        .doc-marca__icone { width: 46px; height: 46px; border-radius: 13px; display: grid; place-items: center; background: var(--marca); color: #fff; }
        .doc-marca__nome { display: block; font-family: var(--fonte-titulo); font-size: 1.30rem; font-weight: 700; color: var(--marca); line-height: 1.1; }
        .doc-marca__slogan { display: block; font-size: .62rem; text-transform: uppercase; letter-spacing: .18em; color: var(--cor-muted); margin-top: 2px; }
        .doc-topo__meta { text-align: right; font-size: .68rem; color: var(--cor-muted); line-height: 1.7; }
        .doc-topo h1 { font-family: var(--fonte-titulo); font-size: 1.45rem; margin: 0 0 4px; color: var(--cor-texto); }
        .doc-topo__periodo { font-size: .72rem; color: var(--cor-texto-suave); }
        .doc-secao { margin-bottom: 26px; break-inside: avoid; }
        .doc-secao h2 { font-family: var(--fonte-titulo); font-size: 1rem; color: var(--marca); margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid var(--cor-borda); }
        .doc-indicadores { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .doc-indicador { border: 1px solid var(--cor-borda); border-radius: var(--raio-card); padding: 12px 13px; background: var(--cor-superficie-alt); }
        .doc-indicador span { display: block; font-size: .62rem; text-transform: uppercase; letter-spacing: .08em; color: var(--cor-muted); margin-bottom: 4px; }
        .doc-indicador strong { font-size: 1rem; color: var(--marca); }
        .doc-tabela { width: 100%; border-collapse: collapse; font-size: .70rem; }
        .doc-tabela th { text-align: left; padding: 7px 8px; background: var(--cor-superficie-alt); color: var(--cor-texto-suave); font-size: .60rem; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid var(--cor-borda); }
        .doc-tabela td { padding: 7px 8px; border-bottom: 1px solid #eef1f5; vertical-align: top; }
        .doc-tabela tr:last-child td { border-bottom: 0; }
        .doc-tabela .num { text-align: right; white-space: nowrap; }
        .doc-rodape { margin-top: 30px; padding-top: 12px; border-top: 1px solid var(--cor-borda); display: flex; justify-content: space-between; gap: 20px; font-size: .60rem; color: var(--cor-muted); }
        .doc-vazio { font-size: .72rem; color: var(--cor-muted); padding: 8px 0; }
        @media (max-width: 720px) {
            .doc-folha { padding: 26px 22px; }
            .doc-topo { flex-direction: column; }
            .doc-topo__meta { text-align: left; }
            .doc-indicadores { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media print {
            @page { size: A4; margin: 14mm; }
            body.pagina-impressao { background: #fff; }
            .doc-barra { display: none !important; }
            .doc-folha { width: auto; margin: 0; padding: 0; box-shadow: none; border-radius: 0; }
            .doc-secao { break-inside: avoid; }
            .doc-tabela { font-size: .62rem; }
        }
    </style>
</head>
<body class="pagina-impressao">
    <div class="doc-barra">
        <strong><?= e($tituloDocumento) ?></strong>
        <div class="doc-barra__acoes">
            <a class="botao botao--vidro botao--pequeno" href="<?= e(url('relatorios')) ?>"><?= icone('chevron-left', 15) ?> Voltar ao relatório</a>
            <button type="button" class="botao botao--secundario botao--pequeno" data-imprimir><?= icone('imprimir', 15) ?> Imprimir</button>
        </div>
    </div>

    <main class="doc-folha">
        <header class="doc-topo">
            <div class="doc-marca">
                <span class="doc-marca__icone"><?= icone('plane', 24) ?></span>
                <span>
                    <span class="doc-marca__nome">Horizonte Viagens</span>
                    <span class="doc-marca__slogan">Gestão de viagens</span>
                </span>
            </div>
            <div class="doc-topo__meta">
                <h1><?= e($tituloDocumento) ?></h1>
                <?php if (!empty($periodoDocumento)): ?>
                    <div class="doc-topo__periodo"><?= e($periodoDocumento) ?></div>
                <?php endif; ?>
                <div>Emitido em <?= e($emitidoEm) ?></div>
            </div>
        </header>

        <?= $conteudo ?>

        <footer class="doc-rodape">
            <span><?= e(APP_NOME) ?> · Sistema de gestão de agência de viagens</span>
            <span>Documento gerado automaticamente pelo sistema</span>
        </footer>
    </main>

    <script src="<?= e(asset('js/app.js')) ?>?v=<?= e(APP_VERSAO) ?>" defer></script>
</body>
</html>
