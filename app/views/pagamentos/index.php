<?php
/**
 * Listagem de pagamentos.
 *
 * Recebe: $pagamentos, $busca, $status, $vence_de, $vence_ate, $paginacao,
 * $parametrosFiltro, $resumo, $total_lancamentos, $statusOpcoes e
 * $formaPagamentoOpcoes.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('pagamentos/novo')) . '">' . icone('plus', 16) . ' Novo pagamento</a>';
?>
<div class="grade grade--4 mb-2">
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Recebido no mês</span><span class="indicador__icone"><?= icone('dinheiro', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($resumo['recebido_mes'] ?? 0)) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">A receber</span><span class="indicador__icone"><?= icone('relogio', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($resumo['pendente'] ?? 0)) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Em atraso</span><span class="indicador__icone"><?= icone('alerta', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($resumo['atrasado'] ?? 0)) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Lançamentos</span><span class="indicador__icone"><?= icone('pagamento', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($total_lancamentos ?? 0) ?></div>
        <div class="indicador__rodape">Total recebido: <?= e(moeda($resumo['total_recebido'] ?? 0)) ?></div>
    </article>
</div>

<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="pagamentos">
    <div class="campo campo--busca">
        <label for="busca">Buscar lançamento</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Reserva, cliente ou referência">
        </div>
    </div>
    <div class="campo" style="flex:0 1 180px;">
        <label for="status">Status</label>
        <select class="controle" id="status" name="status">
            <option value="">Todos</option>
            <?php foreach ($statusOpcoes as $chave => $rotulo): ?>
                <option value="<?= e($chave) ?>" <?= $status === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo" style="flex:0 1 150px;">
        <label for="vence_de">Vence de</label>
        <input class="controle" type="date" id="vence_de" name="vence_de" value="<?= e($vence_de) ?>">
    </div>
    <div class="campo" style="flex:0 1 150px;">
        <label for="vence_ate">Até</label>
        <input class="controle" type="date" id="vence_ate" name="vence_ate" value="<?= e($vence_ate) ?>">
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Filtrar</button>
        <a class="botao botao--fantasma" href="<?= e(url('pagamentos')) ?>">Limpar</a>
    </div>
</form>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Cobranças e recebimentos</h2>
            <p><?= numero($paginacao['total']) ?> lançamento(s) encontrado(s).</p>
        </div>
    </div>

    <?php if ($pagamentos === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('pagamento', 26) ?></div>
            <h3>Nenhum lançamento encontrado</h3>
            <p>Ajuste os filtros ou registre um pagamento vinculado a uma reserva.</p>
            <a class="botao botao--primario" href="<?= e(url('pagamentos/novo')) ?>"><?= icone('plus', 16) ?> Novo pagamento</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Cliente</th>
                        <th>Reserva / destino</th>
                        <th class="num">Valor</th>
                        <th>Forma</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagamentos as $pagamento): ?>
                        <tr>
                            <td>
                                <strong style="font-weight:500;"><?= data_br($pagamento['data_vencimento']) ?></strong>
                                <?php if (!empty($pagamento['data_pagamento'])): ?>
                                    <small class="texto-muted" style="display:block;">pago em <?= data_br($pagamento['data_pagamento']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($pagamento['cliente']) ?></td>
                            <td>
                                <span class="codigo"><?= e($pagamento['reserva_codigo']) ?></span>
                                <small class="texto-muted" style="display:block;"><?= e($pagamento['destino']) ?></small>
                            </td>
                            <td class="num valor"><?= e(moeda($pagamento['valor'])) ?></td>
                            <td><?= e(forma_pagamento_rotulo($pagamento['forma_pagamento'])) ?></td>
                            <td><?= selo_status((string) $pagamento['status']) ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('reservas/' . $pagamento['reserva_id'])) ?>" title="Abrir reserva" aria-label="Abrir reserva"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('pagamentos/' . $pagamento['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                    <?php if (in_array($pagamento['status'], ['pendente', 'atrasado'], true)): ?>
                                        <form method="post" action="<?= e(url('pagamentos/' . $pagamento['id'] . '/baixar')) ?>" data-confirmar="Confirmar a baixa deste pagamento?">
                                            <?= csrf_campo() ?>
                                            <button type="submit" class="acao-icone" title="Dar baixa" aria-label="Dar baixa"><?= icone('check', 16) ?></button>
                                        </form>
                                    <?php elseif ($pagamento['status'] === 'pago'): ?>
                                        <form method="post" action="<?= e(url('pagamentos/' . $pagamento['id'] . '/estornar')) ?>" data-confirmar="Estornar este pagamento?">
                                            <?= csrf_campo() ?>
                                            <button type="submit" class="acao-icone acao-icone--perigo" title="Estornar" aria-label="Estornar"><?= icone('refresh', 16) ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'pagamentos', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
