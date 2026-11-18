<?php
$editando = $pacote !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('pacotes/' . $pacote['id']) : url('pacotes')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$valorPreco = isset($valores['preco_base']) && $valores['preco_base'] !== ''
    ? number_format((float) $valores['preco_base'], 2, ',', '.')
    : '';
?>
<form method="post" action="<?= e($editando ? url('pacotes/' . $pacote['id'] . '/editar') : url('pacotes/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados do pacote</h2>
                        <p>Informações comerciais que aparecerão no catálogo.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('pacotes', 13) ?> Experiência</span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="nome">Nome do pacote <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['nome']) ? 'controle--erro' : '' ?>" type="text" id="nome" name="nome" value="<?= e($valores['nome'] ?? '') ?>" maxlength="140" placeholder="Ex.: Rio Essencial" required>
                            <?php if (isset($erros['nome'])): ?><span class="campo__erro"><?= e($erros['nome']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="destino_id">Destino <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['destino_id']) ? 'controle--erro' : '' ?>" id="destino_id" name="destino_id" required>
                                <option value="">Selecione o destino</option>
                                <?php foreach ($destinos as $destinoOpcao): ?>
                                    <option value="<?= (int) $destinoOpcao['id'] ?>" <?= (int) ($valores['destino_id'] ?? 0) === (int) $destinoOpcao['id'] ? 'selected' : '' ?>>
                                        <?= e($destinoOpcao['nome']) ?> · <?= e($destinoOpcao['cidade']) ?><?= (int) $destinoOpcao['ativo'] === 0 ? ' (inativo)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['destino_id'])): ?><span class="campo__erro"><?= e($erros['destino_id']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="categoria">Categoria <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['categoria']) ? 'controle--erro' : '' ?>" id="categoria" name="categoria" required>
                                <?php foreach (categorias_pacote() as $chaveCategoria => $rotuloCategoria): ?>
                                    <option value="<?= e($chaveCategoria) ?>" <?= ($valores['categoria'] ?? '') === $chaveCategoria ? 'selected' : '' ?>><?= e($rotuloCategoria) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['categoria'])): ?><span class="campo__erro"><?= e($erros['categoria']) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="preco_base">Preço base por pessoa <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['preco_base']) ? 'controle--erro' : '' ?>" type="text" id="preco_base" name="preco_base" value="<?= e($valorPreco) ?>" placeholder="0,00" data-moeda inputmode="decimal" required>
                            <?php if (isset($erros['preco_base'])): ?><span class="campo__erro"><?= e($erros['preco_base']) ?></span><?php else: ?><span class="campo__ajuda">Valor de referência. O preço final pode ser ajustado em cada partida.</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Descrição da experiência</h2>
                        <p>Apresente os diferenciais do pacote para a equipe e para o cliente.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="campo">
                        <label for="descricao">O que está incluído?</label>
                        <textarea class="controle" id="descricao" name="descricao" rows="6" maxlength="4000" placeholder="Hospedagem, passeios, transfers, refeições..."><?= e($valores['descricao'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__corpo">
                    <div class="caixa-info">
                        <?= icone('sparkles', 18) ?>
                        <span>Um bom pacote combina uma descrição clara, preço competitivo e partidas bem programadas.</span>
                    </div>
                    <div class="divisor"></div>
                    <ul class="lista-marcadores">
                        <li><?= icone('check', 15) ?> Cadastre o pacote primeiro e depois crie as partidas.</li>
                        <li><?= icone('check', 15) ?> Cada partida pode ter data, preço e vagas próprias.</li>
                        <li><?= icone('check', 15) ?> Pacotes inativos não aparecem para novas viagens.</li>
                    </ul>
                </div>
            </section>

            <section class="card">
                <div class="card__corpo">
                    <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                        <button type="submit" class="botao botao--primario botao--largo"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Cadastrar pacote' ?></button>
                        <a class="botao botao--fantasma botao--largo" href="<?= e($editando ? url('pacotes/' . $pacote['id']) : url('pacotes')) ?>">Cancelar</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>
