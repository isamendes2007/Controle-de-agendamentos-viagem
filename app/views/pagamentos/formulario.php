<?php
$editando = $pagamento !== null;
$voltarPara = $editando ? url('reservas/' . $pagamento['reserva_id']) : url('pagamentos');
$acoesPagina = '<a class="botao botao--secundario" href="' . e($voltarPara) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$valorFormatado = isset($valores['valor']) && $valores['valor'] !== ''
    ? number_format((float) $valores['valor'], 2, ',', '.')
    : '';
$reservaSelecionada = (int) ($valores['reserva_id'] ?? 0);
?>
<form method="post" action="<?= e($editando ? url('pagamentos/' . $pagamento['id'] . '/editar') : url('pagamentos/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados do pagamento</h2>
                        <p>Vincule a cobrança à reserva correta e informe o valor.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('pagamentos', 13) ?> Financeiro</span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="reserva_id">Reserva <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['reserva_id']) ? 'controle--erro' : '' ?>" id="reserva_id" name="reserva_id" required data-select-reserva-pagamento>
                                <option value="">Selecione a reserva</option>
                                <?php foreach ($reservas as $reservaOpcao): ?>
                                    <?php
                                    $registradoOpcao = (float) $reservaOpcao['registrado'];
                                    if ($editando
                                        && (int) $pagamento['reserva_id'] === (int) $reservaOpcao['id']
                                        && !in_array($pagamento['status'], ['cancelado', 'estornado'], true)) {
                                        $registradoOpcao -= (float) $pagamento['valor'];
                                    }
                                    $saldoOpcao = max(0, (float) $reservaOpcao['valor_total'] - $registradoOpcao);
                                    ?>
                                    <option value="<?= (int) $reservaOpcao['id'] ?>"
                                            data-saldo="<?= $saldoOpcao ?>"
                                            <?= $reservaSelecionada === (int) $reservaOpcao['id'] ? 'selected' : '' ?>>
                                        <?= e($reservaOpcao['codigo']) ?> · <?= e($reservaOpcao['cliente']) ?> · <?= e($reservaOpcao['destino']) ?> · <?= e(moeda($reservaOpcao['valor_total'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['reserva_id'])): ?><span class="campo__erro"><?= e($erros['reserva_id']) ?></span><?php endif; ?>
                            <span class="campo__ajuda" data-saldo-pagamento>Selecione uma reserva para ver o saldo.</span>
                        </div>

                        <div class="campo">
                            <label for="valor">Valor (R$) <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['valor']) ? 'controle--erro' : '' ?>" type="text" id="valor" name="valor" value="<?= e($valorFormatado) ?>" placeholder="0,00" data-moeda inputmode="decimal" required>
                            <?php if (isset($erros['valor'])): ?><span class="campo__erro"><?= e($erros['valor']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="forma_pagamento">Forma de pagamento <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['forma_pagamento']) ? 'controle--erro' : '' ?>" id="forma_pagamento" name="forma_pagamento" required>
                                <?php foreach (opcoes_forma_pagamento() as $chaveForma => $rotuloForma): ?>
                                    <option value="<?= e($chaveForma) ?>" <?= ($valores['forma_pagamento'] ?? 'pix') === $chaveForma ? 'selected' : '' ?>><?= e($rotuloForma) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['forma_pagamento'])): ?><span class="campo__erro"><?= e($erros['forma_pagamento']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="status">Status da cobrança <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['status']) ? 'controle--erro' : '' ?>" id="status" name="status" required>
                                <?php foreach (opcoes_status_pagamento() as $chaveStatus => $rotuloStatus): ?>
                                    <option value="<?= e($chaveStatus) ?>" <?= ($valores['status'] ?? 'pendente') === $chaveStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['status'])): ?><span class="campo__erro"><?= e($erros['status']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="data_vencimento">Vencimento <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['data_vencimento']) ? 'controle--erro' : '' ?>" type="date" id="data_vencimento" name="data_vencimento" value="<?= e($valores['data_vencimento'] ?? '') ?>" required>
                            <?php if (isset($erros['data_vencimento'])): ?><span class="campo__erro"><?= e($erros['data_vencimento']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="data_pagamento">Data de recebimento</label>
                            <input class="controle <?= isset($erros['data_pagamento']) ? 'controle--erro' : '' ?>" type="date" id="data_pagamento" name="data_pagamento" value="<?= e($valores['data_pagamento'] ?? '') ?>">
                            <?php if (isset($erros['data_pagamento'])): ?><span class="campo__erro"><?= e($erros['data_pagamento']) ?></span><?php else: ?><span class="campo__ajuda">Preenchida automaticamente ao marcar como pago.</span><?php endif; ?>
                        </div>

                        <div class="campo campo--largo">
                            <label for="referencia">Referência / comprovante</label>
                            <input class="controle" type="text" id="referencia" name="referencia" value="<?= e($valores['referencia'] ?? '') ?>" maxlength="100" placeholder="Ex.: PIX-88291, TED-1092, parcela 2/3">
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Observações</h2>
                        <p>Anotações internas sobre negociação e recebimento.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="campo">
                        <label for="observacoes">Anotações do pagamento</label>
                        <textarea class="controle" id="observacoes" name="observacoes" rows="4" maxlength="3000" placeholder="Ex.: sinal combinado, parcelamento autorizado, comprovante enviado por e-mail..."><?= e($valores['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__corpo">
                    <div class="caixa-info">
                        <?= icone('info', 18) ?>
                        <span>O valor não pode ultrapassar o saldo disponível da reserva. Cobranças pendentes vencidas são marcadas automaticamente como atrasadas.</span>
                    </div>
                    <div class="divisor"></div>
                    <ul class="lista-marcadores">
                        <li><?= icone('check', 15) ?> Use o status <strong>Pago</strong> para lançar o recebimento.</li>
                        <li><?= icone('check', 15) ?> Estornos e cancelamentos preservam o histórico.</li>
                        <li><?= icone('check', 15) ?> O recebimento total confirma a reserva automaticamente.</li>
                    </ul>
                </div>
            </section>

            <section class="card">
                <div class="card__corpo">
                    <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                        <button type="submit" class="botao botao--primario botao--largo"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Registrar pagamento' ?></button>
                        <a class="botao botao--fantasma botao--largo" href="<?= e($voltarPara) ?>">Cancelar</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>
