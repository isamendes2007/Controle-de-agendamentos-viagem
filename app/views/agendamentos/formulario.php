<?php
$editando = $agendamento !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('agendamentos/' . $agendamento['id']) : url('agendamentos')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
?>
<form method="post" action="<?= e($editando ? url('agendamentos/' . $agendamento['id'] . '/editar') : url('agendamentos/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <div class="campo campo--largo">
                <label for="cliente_id">Cliente <span class="obrigatorio">*</span></label>
                <select class="controle <?= isset($erros['cliente_id']) ? 'controle--erro' : '' ?>" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione o cliente</option>
                    <?php foreach ($clientes as $clienteOpcao): ?>
                        <option value="<?= (int) $clienteOpcao['id'] ?>" <?= ($valores['cliente_id'] ?? 0) === (int) $clienteOpcao['id'] ? 'selected' : '' ?>>
                            <?= e($clienteOpcao['nome']) ?> · <?= e(formatar_cpf($clienteOpcao['cpf'])) ?>
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
                        $disponiveis = max(0, (int) $viagemOpcao['vagas_totais'] - (int) $viagemOpcao['ocupadas']);
                        ?>
                        <option value="<?= (int) $viagemOpcao['id'] ?>"
                                data-preco="<?= (float) $viagemOpcao['preco'] ?>"
                                data-vagas="<?= (int) $viagemOpcao['vagas_totais'] ?>"
                                data-ocupadas="<?= (int) $viagemOpcao['ocupadas'] ?>"
                                data-disponiveis="<?= $disponiveis ?>"
                                <?= ($valores['viagem_id'] ?? 0) === (int) $viagemOpcao['id'] ? 'selected' : '' ?>>
                            <?= e($viagemOpcao['destino_nome'] ?? $viagemOpcao['destino'] ?? '') ?> · <?= e($viagemOpcao['pacote_nome'] ?? $viagemOpcao['pacote'] ?? '') ?> · <?= data_br($viagemOpcao['data_partida']) ?> · <?= e(moeda($viagemOpcao['preco'])) ?> · <?= $disponiveis ?> vaga(s)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($erros['viagem_id'])): ?><span class="campo__erro"><?= e($erros['viagem_id']) ?></span><?php endif; ?>
                <span class="campo__ajuda">Verifique a disponibilidade de vagas antes de solicitar.</span>
            </div>

            <div class="campo">
                <label for="qtd_viajantes">Quantidade de viajantes <span class="obrigatorio">*</span></label>
                <input class="controle <?= isset($erros['qtd_viajantes']) ? 'controle--erro' : '' ?>" type="number" id="qtd_viajantes" name="qtd_viajantes" value="<?= e($valores['qtd_viajantes'] ?? 1) ?>" min="1" step="1" required data-campo-qtd>
                <?php if (isset($erros['qtd_viajantes'])): ?><span class="campo__erro"><?= e($erros['qtd_viajantes']) ?></span><?php endif; ?>
            </div>

            <div class="campo">
                <label for="valor_unitario">Valor unitário (R$) <span class="obrigatorio">*</span></label>
                <input class="controle <?= isset($erros['valor_unitario']) ? 'controle--erro' : '' ?>" type="text" id="valor_unitario" name="valor_unitario" value="<?= e($valores['valor_unitario'] ?? '') ?>" placeholder="0,00" data-moeda inputmode="decimal" required>
                <?php if (isset($erros['valor_unitario'])): ?><span class="campo__erro"><?= e($erros['valor_unitario']) ?></span><?php endif; ?>
            </div>

            <div class="campo">
                <label for="status">Status <span class="obrigatorio">*</span></label>
                <select class="controle <?= isset($erros['status']) ? 'controle--erro' : '' ?>" id="status" name="status" required>
                    <?php foreach ($statusOpcoes as $chave => $rotulo): ?>
                        <option value="<?= e($chave) ?>" <?= ($valores['status'] ?? 'solicitado') === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($erros['status'])): ?><span class="campo__erro"><?= e($erros['status']) ?></span><?php endif; ?>
                <span class="campo__ajuda">Solicitado = aguardando decisão do gestor. Aprovado = pode ser convertido em reserva.</span>
            </div>

            <div class="campo campo--largo">
                <label for="observacoes">Observações</label>
                <textarea class="controle" id="observacoes" name="observacoes" maxlength="3000" rows="3" placeholder="Ex.: preferência de assento, restrição alimentar, data flexível..."><?= e($valores['observacoes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="formulario__rodape">
        <a class="botao botao--fantasma" href="<?= e(url('agendamentos')) ?>"><?= icone('arrow-left', 16) ?> Voltar</a>
        <button type="submit" class="botao botao--primario botao--largo"><?= icone('save', 17) ?> <?= $editando ? 'Salvar alterações' : 'Solicitar agendamento' ?></button>
    </div>
</form>

<script src="<?= e(asset('js/app.js')) ?>?v=<?= e(APP_VERSAO) ?>" defer></script>
