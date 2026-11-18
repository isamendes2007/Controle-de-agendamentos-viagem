<?php
/**
 * Relatório gerencial.
 *
 * Recebe os indicadores, séries e filtros montados por RelatoriosController.
 */
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('relatorios/exportar', ['inicio' => $inicio, 'fim' => $fim, 'destino' => $destinoId ?: ''])) . '">' . icone('download', 16) . ' Exportar CSV</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('relatorios/imprimir', ['inicio' => $inicio, 'fim' => $fim, 'destino' => $destinoId ?: ''])) . '">' . icone('imprimir', 16) . ' Imprimir</a>';
$registroStatus = static fn (string $status): int => (int) ($statusReservas[$status] ?? 0);
?>
<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="relatorios">
    <div class="campo" style="flex:0 1 170px;">
        <label for="inicio">Data inicial</label>
        <input class="controle" type="date" id="inicio" name="inicio" value="<?= e($inicio) ?>">
    </div>
    <div class="campo" style="flex:0 1 170px;">
        <label for="fim">Data final</label>
        <input class="controle" type="date" id="fim" name="fim" value="<?= e($fim) ?>">
    </div>
    <div class="campo" style="flex:0 1 220px;">
        <label for="destino_id">Destino</label>
        <select class="controle" id="destino_id" name="destino_id">
            <option value="">Todos os destinos</option>
            <?php foreach ($destinos as $destinoOpcao): ?>
                <option value="<?= (int) $destinoOpcao['id'] ?>" <?= $destinoId === (int) $destinoOpcao['id'] ? 'selected' : '' ?>><?= e($destinoOpcao['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Aplicar filtros</button>
        <a class="botao botao--fantasma" href="<?= e(url('relatorios')) ?>">Limpar</a>
    </div>
</form>

<div class="grade grade--4 mb-2">
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Receita recebida</span><span class="indicador__icone"><?= icone('dinheiro', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($receita)) ?></div>
        <div class="indicador__rodape">
            <span class="variacao <?= $variacao >= 0 ? 'variacao--positiva' : 'variacao--negativa' ?>"><?= e(number_format($variacao, 1, ',', '.')) ?>%</span>
            sobre o período anterior
        </div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Reservas no período</span><span class="indicador__icone"><?= icone('reserva', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($resumo['total_reservas'] ?? 0) ?></div>
        <div class="indicador__rodape"><?= numero($resumo['viajantes'] ?? 0) ?> viajantes</div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Ticket médio</span><span class="indicador__icone"><?= icone('tendencia', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($resumo['ticket_medio'] ?? 0)) ?></div>
        <div class="indicador__rodape">Contratado: <?= e(moeda($resumo['valor_contratado'] ?? 0)) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Valores em aberto</span><span class="indicador__icone"><?= icone('relogio', 17) ?></span></div>
        <div class="indicador__valor"><?= e(moeda($emAberto)) ?></div>
        <div class="indicador__rodape"><?= numero($clientesNovos) ?> cliente(s) novo(s)</div>
    </article>
</div>

<div class="grade grade--dash">
    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Receita mensal</h2><p>Recebimentos efetivados no período.</p></div>
        </div>
        <div class="card__corpo">
            <?php
            $serieMeses = [];
            foreach ($meses as $mes) {
                $serieMeses[(string) $mes['rotulo']] = (float) $mes['total'];
            }
            echo grafico_barras($serieMeses);
            ?>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Reservas por status</h2><p>Distribuição no período.</p></div>
        </div>
        <div class="card__corpo">
            <div class="linha-flex" style="gap:22px; align-items:center;">
                <div class="rosca" style="background:<?= e($fundoRosca) ?>">
                    <div class="rosca__centro">
                        <span class="rosca__valor"><?= numero($totalStatus) ?></span>
                        <span class="rosca__legenda">reservas</span>
                    </div>
                </div>
                <div class="legenda-lista">
                    <?php foreach (opcoes_status_reserva() as $chave => $rotulo): ?>
                        <div class="legenda-lista__item">
                            <span><?= e($rotulo) ?></span>
                            <span class="legenda-lista__valor"><?= numero($registroStatus($chave)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Top destinos</h2><p>Por valor contratado no período.</p></div>
        </div>
        <?php if ($topDestinos === []): ?>
            <div class="card__corpo"><p class="texto-muted">Nenhum destino com reservas no período.</p></div>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table class="tabela tabela--compacta">
                    <thead>
                        <tr><th>Destino</th><th class="num">Reservas</th><th class="num">Viajantes</th><th class="num">Contratado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topDestinos as $destino): ?>
                            <tr>
                                <td><?= e($destino['destino']) ?><small class="texto-muted" style="display:block;"><?= e($destino['cidade']) ?>/<?= e($destino['pais']) ?></small></td>
                                <td class="num"><?= numero($destino['reservas']) ?></td>
                                <td class="num"><?= numero($destino['viajantes']) ?></td>
                                <td class="num valor"><?= e(moeda($destino['valor'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Formas de pagamento</h2><p>Recebimentos do período.</p></div>
        </div>
        <?php if ($formasPagamento === []): ?>
            <div class="card__corpo"><p class="texto-muted">Nenhum pagamento recebido no período.</p></div>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table class="tabela tabela--compacta">
                    <thead>
                        <tr><th>Forma</th><th class="num">Lançamentos</th><th class="num">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formasPagamento as $forma): ?>
                            <tr>
                                <td><?= e(forma_pagamento_rotulo($forma['forma_pagamento'])) ?></td>
                                <td class="num"><?= numero($forma['quantidade']) ?></td>
                                <td class="num valor"><?= e(moeda($forma['total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Ocupação</h2><p>Viagens do período.</p></div>
        </div>
        <div class="card__corpo">
            <div class="barra-linear__topo mb-1">
                <span><?= numero($viajantesOcupacao) ?> viajantes de <?= numero($vagasOcupacao) ?> vagas</span>
                <strong><?= numero($ocupacao, 1) ?>%</strong>
            </div>
            <div class="barra-linear__trilho">
                <div class="barra-linear__preenchimento" style="width:<?= (float) $ocupacao ?>%"></div>
            </div>
            <div class="divisor"></div>
            <div class="financeiro-resumo">
                <div class="financeiro-linha"><span>Período atual</span><strong><?= data_br($inicio) ?> a <?= data_br($fim) ?></strong></div>
                <div class="financeiro-linha"><span>Período anterior</span><strong><?= data_br($periodoAnterior[0] ?? '') ?> a <?= data_br($periodoAnterior[1] ?? '') ?></strong></div>
                <div class="financeiro-linha financeiro-linha--destaque"><span>Receita anterior</span><strong><?= e(moeda($receitaAnterior)) ?></strong></div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div><h2>Desempenho das viagens</h2><p>Partidas e ocupação no período.</p></div>
        </div>
        <?php if ($desempenhoViagens === []): ?>
            <div class="card__corpo"><p class="texto-muted">Nenhuma partida programada no período.</p></div>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table class="tabela tabela--compacta">
                    <thead>
                        <tr><th>Pacote</th><th>Partida</th><th class="num">Ocupação</th><th class="num">Receita</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($desempenhoViagens as $viagemRelatorio): ?>
                            <tr>
                                <td>
                                    <strong style="font-weight:500;"><?= e($viagemRelatorio['pacote']) ?></strong>
                                    <small class="texto-muted" style="display:block;"><?= e($viagemRelatorio['destino']) ?></small>
                                </td>
                                <td><?= data_br($viagemRelatorio['data_partida']) ?></td>
                                <td class="num"><?= numero($viagemRelatorio['viajantes']) ?>/<?= numero($viagemRelatorio['vagas_totais']) ?></td>
                                <td class="num valor"><?= e(moeda($viagemRelatorio['receita'])) ?></td>
                                <td><?= selo_status((string) $viagemRelatorio['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
