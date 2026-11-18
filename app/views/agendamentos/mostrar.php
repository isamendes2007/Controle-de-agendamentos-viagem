<?php
$acoesPagina = '';
if ($agendamento['status'] === 'solicitado') {
    $acoesPagina .= '<form method="post" action="' . e(url('agendamentos/' . $agendamento['id'] . '/status')) . '" style="display:inline;" onsubmit="return confirm(\'Aprovar este agendamento?\')">' . csrf_campo() . '<input type="hidden" name="status" value="aprovado"><button type="submit" class="botao botao--secundario">' . icone('check', 16) . ' Aprovar</button></form> ';
    $acoesPagina .= '<form method="post" action="' . e(url('agendamentos/' . $agendamento['id'] . '/status')) . '" style="display:inline;" onsubmit="return confirm(\'Rejeitar este agendamento?\')">' . csrf_campo() . '<input type="hidden" name="status" value="rejeitado"><button type="submit" class="botao botao--perigo botao--pequeno">' . icone('x', 16) . ' Rejeitar</button></form> ';
}
if ($agendamento['status'] === 'aprovado') {
    $acoesPagina .= '<form method="post" action="' . e(url('agendamentos/' . $agendamento['id'] . '/converter-reserva')) . '" style="display:inline;">' . csrf_campo() . '<button type="submit" class="botao botao--primario">' . icone('arrow-right', 16) . ' Converter em reserva</button></form> ';
}
$acoesPagina .= '<a class="botao botao--fantasma" href="' . e(url('agendamentos')) . '">' . icone('arrow-left', 16) . ' Voltar</a>';
?>
<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Agendamento <?= e($agendamento['codigo']) ?></h2>
            <p>Solicitação de viagem para <?= e($agendamento['cliente_nome'] ?? $agendamento['cliente'] ?? '') ?>.</p>
        </div>
        <div class="texto-direita">
            <?= selo_status((string) $agendamento['status']) ?>
        </div>
    </div>
    <div class="card__corpo">
        <div class="grade grade--2">
            <div>
                <h3>Cliente</h3>
                <p><strong><?= e($agendamento['cliente_nome'] ?? $agendamento['cliente'] ?? '') ?></strong></p>
                <p><?= e($agendamento['cpf'] ?? '') ?> · <?= e($agendamento['email'] ?? '') ?></p>
                <p><?= e($agendamento['telefone'] ?? '') ?></p>
            </div>
            <div>
                <h3>Viagem</h3>
                <p><strong><?= e($agendamento['pacote_nome'] ?? $agendamento['pacote'] ?? '') ?></strong></p>
                <p><?= e($agendamento['destino_nome'] ?? $agendamento['destino'] ?? '') ?></p>
                <p><?= data_br($agendamento['data_partida']) ?> a <?= data_br($agendamento['data_retorno']) ?></p>
            </div>
            <div>
                <h3>Valores</h3>
                <p>Valor unitário: <strong><?= e(moeda($agendamento['valor_unitario'])) ?></strong></p>
                <p>Viajantes: <strong><?= numero($agendamento['qtd_viajantes']) ?></strong></p>
                <p>Total: <strong><?= e(moeda($agendamento['valor_total'])) ?></strong></p>
            </div>
            <div>
                <h3>Status e datas</h3>
                <p>Status: <strong><?= e(status_rotulo($agendamento['status'])) ?></strong></p>
                <p>Solicitado em: <strong><?= data_hora_br($agendamento['criado_em']) ?></strong></p>
                <p>Atualizado em: <strong><?= data_hora_br($agendamento['atualizado_em']) ?></strong></p>
                <?php if (!empty($agendamento['usuario_nome'])): ?>
                    <p>Atendido por: <strong><?= e($agendamento['usuario_nome']) ?></strong></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($agendamento['observacoes'])): ?>
            <div class="mt-2">
                <h3>Observações</h3>
                <p><?= e($agendamento['observacoes']) ?></p>
            </div>
        <?php endif; ?>
    </div>
    <div class="card__rodape">
        <?= $acoesPagina ?>
    </div>
</section>
