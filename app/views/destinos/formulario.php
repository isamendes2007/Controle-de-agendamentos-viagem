<?php
$editando = $destino !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('destinos/' . $destino['id']) : url('destinos')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$continentes = ['América do Sul', 'América Central', 'América do Norte', 'Europa', 'África', 'Ásia', 'Oceania'];
?>
<form method="post" action="<?= e($editando ? url('destinos/' . $destino['id'] . '/editar') : url('destinos/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Informações do destino</h2>
                <p>Dados que serão exibidos no catálogo de pacotes.</p>
            </div>
            <span class="etiqueta etiqueta--teal"><?= icone('destinos', 13) ?> Localização</span>
        </div>
        <div class="card__corpo">
            <div class="grade-campos">
                <div class="campo campo--largo">
                    <label for="nome">Nome do destino <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['nome']) ? 'controle--erro' : '' ?>" type="text" id="nome" name="nome" value="<?= e($valores['nome'] ?? '') ?>" maxlength="120" placeholder="Ex.: Rio de Janeiro" required>
                    <?php if (isset($erros['nome'])): ?><span class="campo__erro"><?= e($erros['nome']) ?></span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="cidade">Cidade <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['cidade']) ? 'controle--erro' : '' ?>" type="text" id="cidade" name="cidade" value="<?= e($valores['cidade'] ?? '') ?>" maxlength="100" placeholder="Ex.: Rio de Janeiro" required>
                    <?php if (isset($erros['cidade'])): ?><span class="campo__erro"><?= e($erros['cidade']) ?></span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="estado">Estado / província</label>
                    <input class="controle" type="text" id="estado" name="estado" value="<?= e($valores['estado'] ?? '') ?>" maxlength="80" placeholder="Ex.: RJ">
                </div>
                <div class="campo">
                    <label for="pais">País <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['pais']) ? 'controle--erro' : '' ?>" type="text" id="pais" name="pais" value="<?= e($valores['pais'] ?? '') ?>" maxlength="80" placeholder="Ex.: Brasil" required>
                    <?php if (isset($erros['pais'])): ?><span class="campo__erro"><?= e($erros['pais']) ?></span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="continente">Continente <span class="obrigatorio">*</span></label>
                    <select class="controle <?= isset($erros['continente']) ? 'controle--erro' : '' ?>" id="continente" name="continente" required>
                        <option value="">Selecione</option>
                        <?php foreach ($continentes as $continente): ?>
                            <option value="<?= e($continente) ?>" <?= ($valores['continente'] ?? '') === $continente ? 'selected' : '' ?>><?= e($continente) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($erros['continente'])): ?><span class="campo__erro"><?= e($erros['continente']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Descrição</h2>
                <p>Destaque os atrativos e o perfil ideal para este destino.</p>
            </div>
        </div>
        <div class="card__corpo">
            <div class="campo">
                <label for="descricao">Sobre o destino</label>
                <textarea class="controle" id="descricao" name="descricao" maxlength="3000" rows="5" placeholder="Praias, cultura, gastronomia, melhores épocas para viajar..."><?= e($valores['descricao'] ?? '') ?></textarea>
                <span class="campo__ajuda">Este texto será exibido na página do destino e nos materiais internos.</span>
            </div>
        </div>
    </section>

    <div class="formulario__rodape">
        <a class="botao botao--fantasma" href="<?= e($editando ? url('destinos/' . $destino['id']) : url('destinos')) ?>">Cancelar</a>
        <button type="submit" class="botao botao--primario"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Cadastrar destino' ?></button>
    </div>
</form>
