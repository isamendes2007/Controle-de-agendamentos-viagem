<?php
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('agendamentos/novo')) . '">' . icone('plus', 17) . ' Novo agendamento</a>';
$pendentes = $pendentes ?? 0;
if ($pendentes > 0) {
    $acoesPagina .= ' <a class="botao botao--secundario" href="' . e(url('agendamentos?status=solicitado')) . '">' . icone('alert-triangle', 16) . ' ' . $pendentes . ' pendente(s)</a>';
}
?>
<div class="linha-flex" style="gap:9px; margin-bottom:18px;">
    <span class="etiqueta etiqueta--teal"><?= icone('calendar', 13) ?> <?= numero($resumo['total'] ?? $paginacao['total']) ?> agendamento(s)</span>
    <span class="etiqueta"><?= icone('clock', 13) ?> <?= numero($contagem['solicitado'] ?? 0) ?> solicitado(s)</span>
    <span class="etiqueta"><?= icone('check', 13) ?> <?= numero($contagem['aprovado'] ?? 0) ?> aprovado(s)</span>
    <span class="etiqueta"><?= icone('alert-triangle', 13) ?> <?= numero($contagem['rejeitado'] ?? 0) ?> rejeitado(s)</span>
</div>

<?php if ($agendamentos === []): ?>
    <div class="estado-vazio">
        <div class="estado-vazio__icone"><?= icone('calendar', 27) ?></div>
        <h3>Nenhum agendamento encontrado</h3>
        <p>Solicite o primeiro agendamento de viagem para um cliente.</p>
        <a class="botao botao--primario" href="<?= e(url('agendamentos/novo')) ?>"><?= icone('plus', 16) ?> Novo agendamento</a>
    </div>
<?php else: ?>
    <div class="tabela-wrapper">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Viagem</th>
                    <th>Partida</th>
                    <th>Viajantes</th>
                    <th>Valor total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $agendamento): ?>
                    <tr>
                        <td>
                            <div class="celula-principal">
                                <span class="codigo"><?= e($agendamento['codigo']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="celula-principal__texto">
                                <strong><?= e($agendamento['cliente_nome'] ?? $agendamento['cliente'] ?? '') ?></strong>
                                <small><?= e($agendamento['cpf'] ?? '') ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="celula-principal__texto">
                                <strong><?= e($agendamento['pacote_nome'] ?? $agendamento['pacote'] ?? '') ?></strong>
                                <small><?= e($agendamento['destino_nome'] ?? $agendamento['destino'] ?? '') ?></small>
                            </div>
                        </td>
                        <td><?= data_br($agendamento['data_partida']) ?></td>
                        <td class="num"><?= numero($agendamento['qtd_viajantes']) ?></td>
                        <td class="num valor"><?= e(moeda($agendamento['valor_total'])) ?></td>
                        <td><?= selo_status($agendamento['status']) ?></td>
                        <td class="texto-direita">
                            <a class="acao-icone" href="<?= e(url('agendamentos/' . $agendamento['id'])) ?>" title="Ver agendamento" aria-label="Ver agendamento"><?= icone('visualizar', 16) ?></a>
                            <?php if ($agendamento['status'] === 'solicitado'): ?>
                                <form method="post" action="<?= e(url('agendamentos/' . $agendamento['id'] . '/status')) ?>" style="display:inline;" onsubmit="return confirm('Aprovar este agendamento?')">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="status" value="aprovado">
                                    <button type="submit" class="acao-icone" title="Aprovar" aria-label="Aprovar" style="color:var(--sucesso);"><?= icone('check', 16) ?></button>
                                </form>
                                <form method="post" action="<?= e(url('agendamentos/' . $agendamento['id'] . '/status')) ?>" style="display:inline;" onsubmit="return confirm('Rejeitar este agendamento?')">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="status" value="rejeitado">
                                    <button type="submit" class="acao-icone acao-icone--perigo" title="Rejeitar" aria-label="Rejeitar"><?= icone('x', 16) ?></button>
                                </form>
                            <?php endif; ?>
                            <a class="acao-icone" href="<?= e(url('agendamentos/' . $agendamento['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginacao" style="margin-top:16px;">
        <?= paginacao_html($paginacao, 'agendamentos', ['busca' => $busca, 'status' => $status]) ?>
    </div>
<?php endif; ?>

<style>
    .num { text-align: right; }
</style>
