<?php
$tituloDocumento = 'Relatório gerencial de reservas';
$periodoDocumento = 'Período de ' . data_br($inicio) . ' a ' . data_br($fim);
if ($destinoId > 0) {
    foreach ($destinos as $destinoOpcao) {
        if ((int) $destinoOpcao['id'] === $destinoId) {
            $periodoDocumento .= ' · Destino: ' . $destinoOpcao['nome'];
            break;
        }
    }
}
?>
<section class="doc-secao">
    <h2>Resumo executivo</h2>
    <div class="doc-indicadores">
        <div class="doc-indicador">
            <span>Receita recebida</span>
            <strong><?= e(moeda($receita)) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Reservas no período</span>
            <strong><?= numero($resumo['total_reservas'] ?? 0) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Valor contratado</span>
            <strong><?= e(moeda($resumo['valor_contratado'] ?? 0)) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Ticket médio</span>
            <strong><?= e(moeda($resumo['ticket_medio'] ?? 0)) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Valores em aberto</span>
            <strong><?= e(moeda($emAberto)) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Viajantes</span>
            <strong><?= numero($resumo['viajantes'] ?? 0) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Clientes novos</span>
            <strong><?= numero($clientesNovos) ?></strong>
        </div>
        <div class="doc-indicador">
            <span>Ocupação das viagens</span>
            <strong><?= $ocupacao ?>%</strong>
        </div>
    </div>
</section>

<section class="doc-secao">
    <h2>Receita mensal recebida</h2>
    <?php if ($meses === []): ?>
        <p class="doc-vazio">Nenhum mês no período selecionado.</p>
    <?php else: ?>
        <table class="doc-tabela">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th class="num">Receita recebida</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meses as $mes): ?>
                    <tr>
                        <td><?= e($mes['rotulo']) ?></td>
                        <td class="num"><?= e(moeda($mes['total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="doc-secao">
    <h2>Reservas por status</h2>
    <table class="doc-tabela">
        <thead>
            <tr>
                <th>Status</th>
                <th class="num">Quantidade</th>
                <th class="num">Participação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statusReservas as $status => $quantidade): ?>
                <tr>
                    <td><?= e(status_rotulo($status)) ?></td>
                    <td class="num"><?= numero($quantidade) ?></td>
                    <td class="num"><?= $totalStatus > 0 ? number_format(($quantidade / $totalStatus) * 100, 1, ',', '.') . '%' : '0,0%' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="doc-secao">
    <h2>Top destinos</h2>
    <?php if ($topDestinos === []): ?>
        <p class="doc-vazio">Nenhum destino com reservas no período.</p>
    <?php else: ?>
        <table class="doc-tabela">
            <thead>
                <tr>
                    <th>Destino</th>
                    <th class="num">Reservas</th>
                    <th class="num">Viajantes</th>
                    <th class="num">Valor contratado</th>
                    <th class="num">Recebido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topDestinos as $destino): ?>
                    <tr>
                        <td><?= e($destino['destino']) ?> · <?= e($destino['cidade']) ?>/<?= e($destino['pais']) ?></td>
                        <td class="num"><?= numero($destino['reservas']) ?></td>
                        <td class="num"><?= numero($destino['viajantes']) ?></td>
                        <td class="num"><?= e(moeda($destino['valor'])) ?></td>
                        <td class="num"><?= e(moeda($destino['recebido'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="doc-secao">
    <h2>Formas de pagamento</h2>
    <?php if ($formasPagamento === []): ?>
        <p class="doc-vazio">Nenhum pagamento recebido no período.</p>
    <?php else: ?>
        <table class="doc-tabela">
            <thead>
                <tr>
                    <th>Forma de pagamento</th>
                    <th class="num">Lançamentos</th>
                    <th class="num">Total recebido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formasPagamento as $forma): ?>
                    <tr>
                        <td><?= e(forma_pagamento_rotulo($forma['forma_pagamento'])) ?></td>
                        <td class="num"><?= numero($forma['quantidade']) ?></td>
                        <td class="num"><?= e(moeda($forma['total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="doc-secao">
    <h2>Desempenho das viagens</h2>
    <?php if ($desempenhoViagens === []): ?>
        <p class="doc-vazio">Nenhuma partida programada no período.</p>
    <?php else: ?>
        <table class="doc-tabela">
            <thead>
                <tr>
                    <th>Destino / Pacote</th>
                    <th>Partida</th>
                    <th>Retorno</th>
                    <th class="num">Ocupação</th>
                    <th class="num">Receita prevista</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($desempenhoViagens as $viagem): ?>
                    <tr>
                        <td><?= e($viagem['destino']) ?><br><small><?= e($viagem['pacote']) ?></small></td>
                        <td><?= data_br($viagem['data_partida']) ?></td>
                        <td><?= data_br($viagem['data_retorno']) ?></td>
                        <td class="num"><?= numero($viagem['viajantes']) ?>/<?= numero($viagem['vagas_totais']) ?> (<?= percentual((float) $viagem['viajantes'], (float) $viagem['vagas_totais']) ?>%)</td>
                        <td class="num"><?= e(moeda($viagem['receita'])) ?></td>
                        <td><?= e(status_rotulo($viagem['status'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="doc-secao">
    <h2>Comparativo de receita</h2>
    <table class="doc-tabela">
        <thead>
            <tr>
                <th>Período</th>
                <th class="num">Receita recebida</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Período atual (<?= data_br($inicio) ?> a <?= data_br($fim) ?>)</td>
                <td class="num"><?= e(moeda($receita)) ?></td>
            </tr>
            <tr>
                <td>Período anterior (<?= data_br($periodoAnterior[0]) ?> a <?= data_br($periodoAnterior[1]) ?>)</td>
                <td class="num"><?= e(moeda($receitaAnterior)) ?></td>
            </tr>
            <tr>
                <td><strong>Variação</strong></td>
                <td class="num"><strong><?= $variacao === null ? 'Sem base comparável' : number_format($variacao, 1, ',', '.') . '%' ?></strong></td>
            </tr>
        </tbody>
    </table>
</section>
