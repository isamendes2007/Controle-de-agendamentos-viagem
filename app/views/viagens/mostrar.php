<?php
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('viagens')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('viagens/' . $viagem['id'] . '/editar')) . '">' . icone('editar', 16) . ' Editar</a>';
if (!in_array($viagem['status'], ['cancelada', 'concluida'], true)) {
    $acoesPagina .= '<a class="botao botao--primario" href="' . e(url('reservas/nova', ['viagem_id' => $viagem['id']])) . '">' . icone('plus', 16) . ' Nova reserva</a>';
}
$restantes = max(0, (int) $viagem['vagas_totais'] - (int) $totais['viajantes']);
?>
<?php if ($viagem['status'] === 'cancelada'): ?>
    <div class="caixa-info caixa-info--perigo mb-2">
        <?= icone('alert-circle', 18) ?>
        <span>Esta viagem está cancelada. As reservas vinculadas a ela também foram canceladas. As cobranças pendentes foram anuladas.</span>
    </div>
<?php elseif ($viagem['status'] === 'em_andamento'): ?>
    <div class="caixa-info caixa-info--amarelo mb-2">
        <?= icone('clock', 18) ?>
        <span>Esta viagem está em andamento. Acompanhe os viajantes e os pagamentos até o retorno.</span>
    </div>
<?php endif; ?>

<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="linha-flex linha-flex--entre">
                    <div class="perfil-resumo">
                        <span class="avatar avatar--grande" style="background:linear-gradient(145deg,var(--marinho-2),var(--teal));"><?= icone('plane', 26) ?></span>
                        <div class="perfil-resumo__dados">
                            <h2><?= e($viagem['destino']) ?></h2>
                            <p class="texto-suave mb-0"><a href="<?= e(url('pacotes/' . $viagem['pacote_id'])) ?>"><?= e($viagem['pacote']) ?></a> · <?= e($viagem['cidade']) ?><?= $viagem['estado'] ? '/' . e($viagem['estado']) : '' ?></p>
                            <div class="linha-flex mt-1" style="gap:7px;">
                                <span class="etiqueta"><?= icone('calendar', 13) ?> <?= data_br($viagem['data_partida']) ?> a <?= data_br($viagem['data_retorno']) ?></span>
                                <?= selo_status($viagem['status']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="texto-direita">
                        <div style="font-size:1.3rem;font-weight:700;color:var(--teal);"><?= e(moeda($viagem['preco'])) ?></div>
                        <small class="texto-muted">por pessoa</small>
                    </div>
                </div>
                <?php if (!empty($viagem['observacoes'])): ?>
                    <div class="divisor"></div>
                    <p class="mb-0" style="color:var(--texto-suave);"><?= nl2br(e($viagem['observacoes'])) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Reservas desta viagem</h2>
                    <p><?= numero(count($reservas)) ?> reserva(s) registrada(s).</p>
                </div>
                <?php if (!in_array($viagem['status'], ['cancelada', 'concluida'], true)): ?>
                    <a class="card__link" href="<?= e(url('reservas/nova', ['viagem_id' => $viagem['id']])) ?>"><?= icone('plus', 14) ?> Adicionar reserva</a>
                <?php endif; ?>
            </div>
            <?php if ($reservas === []): ?>
                <div class="estado-vazio">
                    <div class="estado-vazio__icone"><?= icone('reservas', 26) ?></div>
                    <h3>Nenhuma reserva ainda</h3>
                    <p>Abra as vendas registrando a primeira reserva para esta partida.</p>
                    <?php if (!in_array($viagem['status'], ['cancelada', 'concluida'], true)): ?>
                        <a class="botao botao--primario botao--pequeno" href="<?= e(url('reservas/nova', ['viagem_id' => $viagem['id']])) ?>"><?= icone('plus', 15) ?> Nova reserva</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="tabela-wrapper">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Viajantes</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <div class="celula-principal">
                                            <span class="avatar avatar--usuario"><?= e(iniciais($reserva['cliente'])) ?></span>
                                            <div class="celula-principal__texto">
                                                <strong><?= e($reserva['cliente']) ?></strong>
                                                <small class="codigo"><?= e($reserva['codigo']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= numero($reserva['qtd_viajantes']) ?></td>
                                    <td class="valor"><?= e(moeda($reserva['valor_total'])) ?></td>
                                    <td class="valor valor--positivo"><?= e(moeda($reserva['total_pago'])) ?></td>
                                    <td class="<?= (float) $reserva['em_aberto'] > 0 ? 'valor valor--aberto' : 'valor valor--positivo' ?>"><?= (float) $reserva['em_aberto'] > 0 ? e(moeda($reserva['em_aberto'])) : 'Quitado' ?></td>
                                    <td><?= selo_status($reserva['status']) ?></td>
                                    <td class="texto-direita"><a class="acao-icone" href="<?= e(url('reservas/' . $reserva['id'])) ?>" title="Ver reserva"><?= icone('visualizar', 16) ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tabela-total">
                    <div class="tabela-total__item">Receita prevista <strong><?= e(moeda($totais['receita_prevista'])) ?></strong></div>
                    <div class="tabela-total__item">Recebido <strong style="color:var(--verde)"><?= e(moeda($totais['recebido'])) ?></strong></div>
                    <div class="tabela-total__item">Em aberto <strong style="color:var(--amarelo)"><?= e(moeda($totais['em_aberto'])) ?></strong></div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Ocupação</h2>
                    <p>Disponibilidade atual da partida.</p>
                </div>
            </div>
            <div class="card__corpo">
                <div class="linha-flex linha-flex--entre mb-1">
                    <span class="texto-suave" style="font-size:.76rem;"><?= numero($totais['viajantes']) ?> de <?= numero($viagem['vagas_totais']) ?> vagas</span>
                    <strong style="color:var(--teal);"><?= $ocupacao ?>%</strong>
                </div>
                <div class="barra-linear__trilho" style="height:9px;">
                    <div class="barra-linear__preenchimento <?= $ocupacao >= 90 ? 'barra-linear__preenchimento--coral' : '' ?>" style="width:<?= $ocupacao ?>%"></div>
                </div>
                <div class="linha-flex linha-flex--entre mt-1">
                    <span class="dica-atalho"><?= numero($restantes) ?> vaga(s) restante(s)</span>
                    <span class="dica-atalho"><?= numero($viagem['vagas_totais']) ?> no total</span>
                </div>
                <div class="divisor"></div>
                <div class="financeiro-resumo">
                    <div class="financeiro-linha"><span>Receita prevista</span><strong><?= e(moeda($totais['receita_prevista'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Recebido</span><strong style="color:var(--verde)"><?= e(moeda($totais['recebido'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Em aberto</span><strong style="color:var(--amarelo)"><?= e(moeda($totais['em_aberto'])) ?></strong></div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Status da viagem</h2>
                    <p>Atualize a etapa operacional.</p>
                </div>
            </div>
            <div class="card__corpo">
                <form method="post" action="<?= e(url('viagens/' . $viagem['id'] . '/status')) ?>" class="formulario" data-carregando>
                    <?= csrf_campo() ?>
                    <div class="campo">
                        <label for="status">Novo status</label>
                        <select class="controle" id="status" name="status">
                            <?php foreach (opcoes_status_viagem() as $chaveStatus => $rotuloStatus): ?>
                                <option value="<?= e($chaveStatus) ?>" <?= $viagem['status'] === $chaveStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="botao botao--secundario botao--largo"><?= icone('refresh', 16) ?> Atualizar status</button>
                </form>
                <div class="divisor"></div>
                <div class="dados-lista">
                    <div class="dado">
                        <span class="dado__icone"><?= icone('calendar', 16) ?></span>
                        <div class="dado__texto"><span>Partida</span><strong><?= data_br($viagem['data_partida']) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('calendar', 16) ?></span>
                        <div class="dado__texto"><span>Retorno</span><strong><?= data_br($viagem['data_retorno']) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('pacotes', 16) ?></span>
                        <div class="dado__texto"><span>Pacote</span><a href="<?= e(url('pacotes/' . $viagem['pacote_id'])) ?>"><?= e($viagem['pacote']) ?></a></div>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($reservas === []): ?>
            <section class="card">
                <div class="card__corpo">
                    <form method="post" action="<?= e(url('viagens/' . $viagem['id'] . '/excluir')) ?>" data-confirmar="Deseja excluir definitivamente esta viagem? Esta ação não pode ser desfeita.">
                        <?= csrf_campo() ?>
                        <button type="submit" class="botao botao--perigo botao--largo"><?= icone('excluir', 16) ?> Excluir viagem</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </aside>
</div>
