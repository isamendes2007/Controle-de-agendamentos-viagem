<?php
$editando = $reserva !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('reservas/' . $reserva['id']) : url('reservas')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$descontoValor = isset($valores['desconto']) && (float) $valores['desconto'] > 0
    ? number_format((float) $valores['desconto'], 2, ',', '.')
    : '';
$viagemSelecionada = (int) ($valores['viagem_id'] ?? 0);
$clienteSelecionado = (int) ($valores['cliente_id'] ?? 0);
$quantidadeAtual = max(1, (int) ($valores['qtd_viajantes'] ?? 1));
?>
<?php if ($clientes === []): ?>
    <div class="caixa-info caixa-info--amarelo mb-2">
        <?= icone('alert-triangle', 18) ?>
        <span>Nenhum cliente ativo cadastrado. <a href="<?= e(url('clientes/novo')) ?>">Cadastre um cliente</a> antes de criar a reserva.</span>
    </div>
<?php endif; ?>
<?php if ($viagens === []): ?>
    <div class="caixa-info caixa-info--amarelo mb-2">
        <?= icone('alert-triangle', 18) ?>
        <span>Nenhuma viagem disponível. <a href="<?= e(url('viagens/nova')) ?>">Programe uma viagem</a> para abrir novas reservas.</span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($editando ? url('reservas/' . $reserva['id'] . '/editar') : url('reservas/nova')) ?>" class="formulario" data-carregando data-formulario-reserva>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados da reserva</h2>
                        <p>Vincule o cliente à partida e defina as condições comerciais.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('reservas', 13) ?> Venda</span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="cliente_id">Cliente <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['cliente_id']) ? 'controle--erro' : '' ?>" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione o cliente</option>
                                <?php foreach ($clientes as $clienteOpcao): ?>
                                    <option value="<?= (int) $clienteOpcao['id'] ?>" <?= $clienteSelecionado === (int) $clienteOpcao['id'] ? 'selected' : '' ?>>
                                        <?= e($clienteOpcao['nome']) ?> · <?= e(formatar_cpf($clienteOpcao['cpf'])) ?><?= (int) $clienteOpcao['ativo'] === 0 ? ' (inativo)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['cliente_id'])): ?><span class="campo__erro"><?= e($erros['cliente_id']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo campo--largo">
                            <label for="viagem_id">Viagem <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['viagem_id']) ? 'controle--erro' : '' ?>" id="viagem_id" name="viagem_id" required data-select-viagem>
                                <option value="">Selecione a viagem</option>
                                <?php foreach ($viagens as $viagemOpcao): ?>
                                    <?php
                                    $ocupadasOpcao = (int) $viagemOpcao['ocupadas'];
                                    if ($editando && (int) $viagemOpcao['id'] === (int) $reserva['viagem_id']) {
                                        $ocupadasOpcao = max(0, $ocupadasOpcao - (int) $reserva['qtd_viajantes']);
                                    }
                                    $disponiveisOpcao = max(0, (int) $viagemOpcao['vagas_totais'] - $ocupadasOpcao);
                                    ?>
                                    <option value="<?= (int) $viagemOpcao['id'] ?>"
                                            data-preco="<?= (float) $viagemOpcao['preco'] ?>"
                                            data-vagas="<?= (int) $viagemOpcao['vagas_totais'] ?>"
                                            data-ocupadas="<?= $ocupadasOpcao ?>"
                                            <?= $viagemSelecionada === (int) $viagemOpcao['id'] ? 'selected' : '' ?>>
                                        <?= e($viagemOpcao['destino']) ?> · <?= e($viagemOpcao['pacote']) ?> · <?= data_br($viagemOpcao['data_partida']) ?> · <?= e(moeda($viagemOpcao['preco'])) ?> · <?= $disponiveisOpcao ?> vaga(s)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['viagem_id'])): ?><span class="campo__erro"><?= e($erros['viagem_id']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="qtd_viajantes">Quantidade de viajantes <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['qtd_viajantes']) ? 'controle--erro' : '' ?>" type="number" id="qtd_viajantes" name="qtd_viajantes" value="<?= e($quantidadeAtual) ?>" min="1" step="1" required data-campo-qtd>
                            <?php if (isset($erros['qtd_viajantes'])): ?><span class="campo__erro"><?= e($erros['qtd_viajantes']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="desconto">Desconto (R$)</label>
                            <input class="controle <?= isset($erros['desconto']) ? 'controle--erro' : '' ?>" type="text" id="desconto" name="desconto" value="<?= e($descontoValor) ?>" placeholder="0,00" data-moeda data-campo-desconto inputmode="decimal">
                            <?php if (isset($erros['desconto'])): ?><span class="campo__erro"><?= e($erros['desconto']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="status">Status inicial <span class="obrigatorio">*</span></label>
                            <select class="controle" id="status" name="status" required>
                                <?php foreach (opcoes_status_reserva() as $chaveStatus => $rotuloStatus): ?>
                                    <?php if (!$editando && in_array($chaveStatus, ['concluida', 'cancelada'], true)) continue; ?>
                                    <option value="<?= e($chaveStatus) ?>" <?= ($valores['status'] ?? 'pendente') === $chaveStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Observações</h2>
                        <p>Preferências, solicitações e combinados com o cliente.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="campo">
                        <label for="observacoes">Anotações da reserva</label>
                        <textarea class="controle" id="observacoes" name="observacoes" rows="4" maxlength="3000" placeholder="Ex.: quarto duplo, transfer privativo, restrição alimentar..."><?= e($valores['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Resumo da reserva</h2>
                        <p>O total é calculado automaticamente.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="calculadora-reserva">
                        <div class="calculadora-reserva__linha"><span>Valor por pessoa</span><strong data-resumo-unitario>R$ 0,00</strong></div>
                        <div class="calculadora-reserva__linha"><span>Subtotal</span><strong data-resumo-subtotal>R$ 0,00</strong></div>
                        <div class="calculadora-reserva__linha"><span>Desconto</span><strong>—</strong></div>
                        <div class="calculadora-reserva__total"><span>Total da reserva</span><span data-resumo-total>R$ 0,00</span></div>
                    </div>
                    <div class="linha-flex linha-flex--entre mt-2">
                        <span class="dica-atalho" data-resumo-vagas>Selecione uma viagem</span>
                    </div>
                    <div class="divisor"></div>
                    <div class="caixa-info">
                        <?= icone('info', 17) ?>
                        <span>Após criar a reserva, registre os pagamentos na tela de detalhes para acompanhar o saldo.</span>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__corpo">
                    <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                        <button type="submit" class="botao botao--primario botao--largo" <?= ($clientes === [] || $viagens === []) ? 'disabled' : '' ?>><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Criar reserva' ?></button>
                        <a class="botao botao--fantasma botao--largo" href="<?= e($editando ? url('reservas/' . $reserva['id']) : url('reservas')) ?>">Cancelar</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>
