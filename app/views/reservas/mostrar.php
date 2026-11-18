<?php
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('reservas')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('reservas/' . $reserva['id'] . '/editar')) . '">' . icone('editar', 16) . ' Editar</a>';
if (!in_array($reserva['status'], ['cancelada', 'concluida'], true)) {
    $acoesPagina .= '<a class="botao botao--primario" href="' . e(url('pagamentos/novo', ['reserva_id' => $reserva['id']])) . '">' . icone('plus', 16) . ' Registrar pagamento</a>';
}
$quitado = (float) $resumo['em_aberto'] <= 0.001 && (float) $reserva['valor_total'] > 0;
$classeFinanceira = $reserva['status'] === 'cancelada'
    ? 'selo-financeiro--cancelado'
    : ($quitado ? 'selo-financeiro--quitado' : ((float) $resumo['total_pago'] > 0 ? 'selo-financeiro--parcial' : 'selo-financeiro--aberto'));
$textoFinanceiro = $reserva['status'] === 'cancelada'
    ? 'Reserva cancelada'
    : ($quitado ? 'Quitado' : ((float) $resumo['total_pago'] > 0 ? 'Pagamento parcial' : 'Em aberto'));
?>
<?php if ($reserva['status'] === 'cancelada'): ?>
    <div class="caixa-info caixa-info--perigo mb-2">
        <?= icone('alert-circle', 18) ?>
        <span>Esta reserva está cancelada. Os pagamentos pendentes foram anulados e o histórico financeiro foi preservado.</span>
    </div>
<?php elseif ($reserva['status'] === 'concluida'): ?>
    <div class="caixa-info mb-2">
        <?= icone('check-circle', 18) ?>
        <span>Reserva concluída. A viagem já foi realizada e o financeiro está encerrado.</span>
    </div>
<?php elseif ((float) $resumo['atrasado'] > 0): ?>
    <div class="caixa-info caixa-info--amarelo mb-2">
        <?= icone('alert-triangle', 18) ?>
        <span>Existe cobrança em atraso nesta reserva. Regularize o recebimento ou registre a negociação com o cliente.</span>
    </div>
<?php endif; ?>

<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="perfil-resumo">
                    <span class="avatar avatar--grande avatar--usuario"><?= e(iniciais($reserva['cliente'])) ?></span>
                    <div class="perfil-resumo__dados">
                        <h2><?= e($reserva['cliente']) ?></h2>
                        <p class="texto-suave mb-0">
                            <a href="<?= e(url('clientes/' . $reserva['cliente_id'])) ?>">Ver cadastro do cliente</a>
                            · <?= e(formatar_cpf($reserva['cpf'])) ?>
                        </p>
                        <div class="linha-flex mt-1" style="gap:7px;">
                            <span class="codigo"><?= e($reserva['codigo']) ?></span>
                            <?= selo_status($reserva['status']) ?>
                            <span class="selo-financeiro <?= e($classeFinanceira) ?>"><?= e($textoFinanceiro) ?></span>
                        </div>
                    </div>
                    <div class="texto-direita">
                        <div style="font-size:1.3rem;font-weight:700;color:var(--teal);"><?= e(moeda($reserva['valor_total'])) ?></div>
                        <small class="texto-muted">valor total da reserva</small>
                    </div>
                </div>

                <div class="divisor"></div>

                <div class="grade grade--2" style="gap:18px;">
                    <div class="dados-lista">
                        <div class="dado">
                            <span class="dado__icone"><?= icone('mail', 16) ?></span>
                            <div class="dado__texto">
                                <span>E-mail</span>
                                <a href="mailto:<?= e($reserva['email']) ?>"><?= e($reserva['email']) ?></a>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('phone', 16) ?></span>
                            <div class="dado__texto">
                                <span>Telefone</span>
                                <strong><?= e(formatar_telefone($reserva['telefone'])) ?></strong>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('destinos', 16) ?></span>
                            <div class="dado__texto">
                                <span>Destino</span>
                                <strong><?= e($reserva['destino']) ?> · <?= e($reserva['cidade']) ?><?= $reserva['estado'] ? '/' . e($reserva['estado']) : '' ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="dados-lista">
                        <div class="dado">
                            <span class="dado__icone"><?= icone('pacotes', 16) ?></span>
                            <div class="dado__texto">
                                <span>Pacote</span>
                                <a href="<?= e(url('pacotes/' . $reserva['pacote_id'])) ?>"><?= e($reserva['pacote']) ?></a>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('calendar', 16) ?></span>
                            <div class="dado__texto">
                                <span>Período da viagem</span>
                                <strong><?= data_br($reserva['data_partida']) ?> a <?= data_br($reserva['data_retorno']) ?></strong>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('clock', 16) ?></span>
                            <div class="dado__texto">
                                <span>Reserva criada em</span>
                                <strong><?= data_hora_br($reserva['criado_em']) ?> · por <?= e($reserva['agente'] ?? 'sistema') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($reserva['observacoes'])): ?>
                    <div class="divisor"></div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('info', 16) ?></span>
                        <div class="dado__texto">
                            <span>Observações da reserva</span>
                            <strong style="font-weight:500; color:var(--texto-suave);"><?= nl2br(e($reserva['observacoes'])) ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Pagamentos da reserva</h2>
                    <p><?= numero(count($pagamentos)) ?> lançamento(s) financeiro(s).</p>
                </div>
                <?php if (!in_array($reserva['status'], ['cancelada', 'concluida'], true)): ?>
                    <a class="card__link" href="<?= e(url('pagamentos/novo', ['reserva_id' => $reserva['id']])) ?>"><?= icone('plus', 14) ?> Novo pagamento</a>
                <?php endif; ?>
            </div>

            <?php if ($pagamentos === []): ?>
                <div class="estado-vazio">
                    <div class="estado-vazio__icone"><?= icone('pagamentos', 26) ?></div>
                    <h3>Nenhum pagamento registrado</h3>
                    <p>Registre o sinal ou a primeira parcela para acompanhar o saldo desta reserva.</p>
                    <?php if ($reserva['status'] !== 'cancelada'): ?>
                        <a class="botao botao--primario botao--pequeno" href="<?= e(url('pagamentos/novo', ['reserva_id' => $reserva['id']])) ?>"><?= icone('plus', 15) ?> Registrar pagamento</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="tabela-wrapper">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Forma / Referência</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Recebido em</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos as $pagamento): ?>
                                <tr>
                                    <td>
                                        <strong><?= data_br($pagamento['data_vencimento']) ?></strong>
                                        <?php if ($pagamento['status'] === 'atrasado'): ?>
                                            <small class="texto-muted" style="display:block;color:var(--vermelho);">Em atraso</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="celula-principal__texto">
                                            <strong><?= e(forma_pagamento_rotulo($pagamento['forma_pagamento'])) ?></strong>
                                            <small><?= e($pagamento['referencia'] ?: 'Sem referência') ?></small>
                                        </div>
                                    </td>
                                    <td class="valor"><?= e(moeda($pagamento['valor'])) ?></td>
                                    <td><?= selo_status($pagamento['status']) ?></td>
                                    <td>
                                        <div class="celula-principal__texto">
                                            <strong><?= data_br($pagamento['data_pagamento'], '—') ?></strong>
                                            <small><?= e($pagamento['registrado_por'] ? 'por ' . $pagamento['registrado_por'] : '') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="acoes-tabela">
                                            <?php if (in_array($pagamento['status'], ['pendente', 'atrasado'], true)): ?>
                                                <form method="post" action="<?= e(url('pagamentos/' . $pagamento['id'] . '/baixar')) ?>" class="form-inline" data-confirmar="Confirmar o recebimento de <?= e(moeda($pagamento['valor'])) ?>?">
                                                    <?= csrf_campo() ?>
                                                    <button type="submit" class="acao-icone" title="Marcar como recebido" aria-label="Marcar como recebido"><?= icone('check-circle', 16) ?></button>
                                                </form>
                                            <?php endif; ?>
                                            <a class="acao-icone" href="<?= e(url('pagamentos/' . $pagamento['id'] . '/editar')) ?>" title="Editar pagamento" aria-label="Editar pagamento"><?= icone('editar', 16) ?></a>
                                            <?php if ($pagamento['status'] === 'pago'): ?>
                                                <form method="post" action="<?= e(url('pagamentos/' . $pagamento['id'] . '/estornar')) ?>" class="form-inline" data-confirmar="Estornar este pagamento? O valor deixará de constar como recebido.">
                                                    <?= csrf_campo() ?>
                                                    <button type="submit" class="acao-icone acao-icone--perigo" title="Estornar pagamento" aria-label="Estornar pagamento"><?= icone('refresh', 16) ?></button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" action="<?= e(url('pagamentos/' . $pagamento['id'] . '/excluir')) ?>" class="form-inline" data-confirmar="Excluir definitivamente este lançamento de pagamento?">
                                                    <?= csrf_campo() ?>
                                                    <button type="submit" class="acao-icone acao-icone--perigo" title="Excluir pagamento" aria-label="Excluir pagamento"><?= icone('excluir', 16) ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tabela-total">
                    <div class="tabela-total__item">Total da reserva <strong><?= e(moeda($reserva['valor_total'])) ?></strong></div>
                    <div class="tabela-total__item">Total recebido <strong style="color:var(--verde)"><?= e(moeda($resumo['total_pago'])) ?></strong></div>
                    <div class="tabela-total__item">Em aberto <strong style="color:var(--amarelo)"><?= e(moeda($resumo['em_aberto'])) ?></strong></div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Resumo financeiro</h2>
                    <p>Valores e progresso de quitação.</p>
                </div>
            </div>
            <div class="card__corpo">
                <div class="barra-linear__topo">
                    <span>Quitação da reserva</span>
                    <strong><?= $percentualPago ?>%</strong>
                </div>
                <div class="barra-linear__trilho">
                    <div class="barra-linear__preenchimento <?= $percentualPago >= 100 ? '' : ($percentualPago > 0 ? 'barra-linear__preenchimento--amarelo' : 'barra-linear__preenchimento--coral') ?>" style="width: <?= $percentualPago ?>%"></div>
                </div>
                <div class="linha-flex linha-flex--entre mt-1">
                    <span class="dica-atalho"><?= e(moeda($resumo['total_pago'])) ?> recebido(s)</span>
                    <span class="dica-atalho"><?= e(moeda($reserva['valor_total'])) ?> total</span>
                </div>

                <div class="divisor"></div>

                <div class="financeiro-resumo">
                    <div class="financeiro-linha"><span>Valor por pessoa</span><strong><?= e(moeda($reserva['valor_unitario'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Viajantes</span><strong><?= numero($reserva['qtd_viajantes']) ?></strong></div>
                    <div class="financeiro-linha"><span>Subtotal</span><strong><?= e(moeda((float) $reserva['valor_unitario'] * (int) $reserva['qtd_viajantes'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Desconto</span><strong><?= e(moeda($reserva['desconto'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Total pago</span><strong style="color:var(--verde)"><?= e(moeda($resumo['total_pago'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Em atraso</span><strong style="color:var(--vermelho)"><?= e(moeda($resumo['atrasado'])) ?></strong></div>
                    <div class="financeiro-linha financeiro-linha--destaque"><span>Saldo devedor</span><strong><?= e(moeda($saldo)) ?></strong></div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Status da reserva</h2>
                    <p>Atualize a etapa comercial da venda.</p>
                </div>
            </div>
            <div class="card__corpo">
                <form method="post" action="<?= e(url('reservas/' . $reserva['id'] . '/status')) ?>" class="formulario" data-carregando>
                    <?= csrf_campo() ?>
                    <div class="campo">
                        <label for="status">Novo status</label>
                        <select class="controle" id="status" name="status">
                            <?php foreach (opcoes_status_reserva() as $chaveStatus => $rotuloStatus): ?>
                                <option value="<?= e($chaveStatus) ?>" <?= $reserva['status'] === $chaveStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="botao botao--secundario botao--largo"><?= icone('refresh', 16) ?> Atualizar status</button>
                </form>
                <div class="divisor"></div>
                <div class="caixa-info">
                    <?= icone('info', 17) ?>
                    <span>Para concluir a reserva, quite ou cancele todos os pagamentos em aberto. O cancelamento anula as cobranças pendentes.</span>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__corpo">
                <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                    <a class="botao botao--secundario botao--largo" href="<?= e(url('pagamentos', ['busca' => $reserva['codigo']])) ?>"><?= icone('pagamentos', 16) ?> Ver no financeiro</a>
                    <a class="botao botao--fantasma botao--largo" href="<?= e(url('reservas/' . $reserva['id'] . '/editar')) ?>"><?= icone('editar', 16) ?> Editar reserva</a>
                </div>
            </div>
        </section>
    </aside>
</div>
