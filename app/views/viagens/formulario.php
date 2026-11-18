<?php
$editando = $viagem !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('viagens/' . $viagem['id']) : url('viagens')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$valorPreco = isset($valores['preco']) && $valores['preco'] !== ''
    ? number_format((float) $valores['preco'], 2, ',', '.')
    : '';
?>
<form method="post" action="<?= e($editando ? url('viagens/' . $viagem['id'] . '/editar') : url('viagens/nova')) ?>" class="formulario" data-carregando data-formulario-viagem>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados da partida</h2>
                        <p>Defina quando, para onde e por quanto esta viagem será vendida.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('calendar', 13) ?> Operação</span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="pacote_id">Pacote <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['pacote_id']) ? 'controle--erro' : '' ?>" id="pacote_id" name="pacote_id" required>
                                <option value="">Selecione o pacote</option>
                                <?php foreach ($pacotes as $pacoteOpcao): ?>
                                    <option value="<?= (int) $pacoteOpcao['id'] ?>" data-preco="<?= (float) $pacoteOpcao['preco_base'] ?>" <?= (int) ($valores['pacote_id'] ?? 0) === (int) $pacoteOpcao['id'] ? 'selected' : '' ?>>
                                        <?= e($pacoteOpcao['nome']) ?> · <?= e($pacoteOpcao['destino']) ?><?= (int) $pacoteOpcao['ativo'] === 0 ? ' (inativo)' : '' ?> · <?= e(moeda($pacoteOpcao['preco_base'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['pacote_id'])): ?><span class="campo__erro"><?= e($erros['pacote_id']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="data_partida">Data de partida <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['data_partida']) ? 'controle--erro' : '' ?>" type="date" id="data_partida" name="data_partida" value="<?= e($valores['data_partida'] ?? '') ?>" required>
                            <?php if (isset($erros['data_partida'])): ?><span class="campo__erro"><?= e($erros['data_partida']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="data_retorno">Data de retorno <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['data_retorno']) ? 'controle--erro' : '' ?>" type="date" id="data_retorno" name="data_retorno" value="<?= e($valores['data_retorno'] ?? '') ?>" required>
                            <?php if (isset($erros['data_retorno'])): ?><span class="campo__erro"><?= e($erros['data_retorno']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="vagas_totais">Vagas totais <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['vagas_totais']) ? 'controle--erro' : '' ?>" type="number" id="vagas_totais" name="vagas_totais" value="<?= e($valores['vagas_totais'] ?? 20) ?>" min="<?= max(1, (int) $vagasOcupadas) ?>" step="1" required>
                            <?php if (isset($erros['vagas_totais'])): ?>
                                <span class="campo__erro"><?= e($erros['vagas_totais']) ?></span>
                            <?php elseif ($editando && $vagasOcupadas > 0): ?>
                                <span class="campo__ajuda"><?= numero($vagasOcupadas) ?> vaga(s) já ocupada(s) por reservas ativas.</span>
                            <?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="preco">Preço por pessoa <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['preco']) ? 'controle--erro' : '' ?>" type="text" id="preco" name="preco" value="<?= e($valorPreco) ?>" placeholder="0,00" data-moeda inputmode="decimal" required>
                            <?php if (isset($erros['preco'])): ?><span class="campo__erro"><?= e($erros['preco']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="status">Status da viagem <span class="obrigatorio">*</span></label>
                            <select class="controle" id="status" name="status" required>
                                <?php foreach (opcoes_status_viagem() as $chaveStatus => $rotuloStatus): ?>
                                    <option value="<?= e($chaveStatus) ?>" <?= ($valores['status'] ?? 'planejada') === $chaveStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Observações da operação</h2>
                        <p>Informações úteis para a equipe e para o guia.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="campo">
                        <label for="observacoes">Anotações internas</label>
                        <textarea class="controle" id="observacoes" name="observacoes" rows="5" maxlength="3000" placeholder="Informações sobre embarque, guia, grupo, transfers..."><?= e($valores['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__corpo">
                    <div class="caixa-info">
                        <?= icone('alert-circle', 18) ?>
                        <span>Ao alterar o status para <strong>cancelada</strong>, as reservas não canceladas desta viagem também serão canceladas e as cobranças pendentes serão anuladas.</span>
                    </div>
                    <div class="divisor"></div>
                    <ul class="lista-marcadores">
                        <li><?= icone('check', 15) ?> A capacidade não pode ser menor que as vagas ocupadas.</li>
                        <li><?= icone('check', 15) ?> Datas e preços podem ser ajustados antes da venda.</li>
                        <li><?= icone('check', 15) ?> Viagens concluídas são marcadas automaticamente nas reservas confirmadas.</li>
                    </ul>
                </div>
            </section>

            <section class="card">
                <div class="card__corpo">
                    <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                        <button type="submit" class="botao botao--primario botao--largo"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Programar viagem' ?></button>
                        <a class="botao botao--fantasma botao--largo" href="<?= e($editando ? url('viagens/' . $viagem['id']) : url('viagens')) ?>">Cancelar</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>
