<?php
$editando = $cliente !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e($editando ? url('clientes/' . $cliente['id']) : url('clientes')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$ufs = estados_brasil();
?>
<?php if ($editando && (int) $cliente['ativo'] === 0): ?>
    <div class="caixa-info caixa-info--amarelo mb-2">
        <?= icone('alert-triangle', 18) ?>
        <span>Este cliente está inativo. Os dados continuam disponíveis para consulta e histórico, mas ele não aparece em novas reservas.</span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($editando ? url('clientes/' . $cliente['id'] . '/editar') : url('clientes/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Dados pessoais</h2>
                <p>Informações de identificação do viajante.</p>
            </div>
            <span class="etiqueta etiqueta--teal"><?= icone('clientes', 13) ?> Cadastro</span>
        </div>
        <div class="card__corpo">
            <div class="grade-campos">
                <div class="campo campo--largo">
                    <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['nome']) ? 'controle--erro' : '' ?>" type="text" id="nome" name="nome" value="<?= e($valores['nome'] ?? '') ?>" maxlength="120" placeholder="Ex.: Maria da Silva" required>
                    <?php if (isset($erros['nome'])): ?><span class="campo__erro"><?= e($erros['nome']) ?></span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="cpf">CPF <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['cpf']) ? 'controle--erro' : '' ?>" type="text" id="cpf" name="cpf" value="<?= e($valores['cpf'] ?? '') ?>" placeholder="000.000.000-00" data-mascara="cpf" inputmode="numeric" required>
                    <?php if (isset($erros['cpf'])): ?><span class="campo__erro"><?= e($erros['cpf']) ?></span><?php else: ?><span class="campo__ajuda">O CPF é validado automaticamente.</span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="data_nascimento">Data de nascimento</label>
                    <input class="controle <?= isset($erros['data_nascimento']) ? 'controle--erro' : '' ?>" type="date" id="data_nascimento" name="data_nascimento" value="<?= e($valores['data_nascimento'] ?? '') ?>" max="<?= date('Y-m-d') ?>">
                    <?php if (isset($erros['data_nascimento'])): ?><span class="campo__erro"><?= e($erros['data_nascimento']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Contato</h2>
                <p>Como a agência pode falar com este cliente.</p>
            </div>
        </div>
        <div class="card__corpo">
            <div class="grade-campos">
                <div class="campo">
                    <label for="email">E-mail <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['email']) ? 'controle--erro' : '' ?>" type="email" id="email" name="email" value="<?= e($valores['email'] ?? '') ?>" maxlength="150" placeholder="cliente@email.com" required>
                    <?php if (isset($erros['email'])): ?><span class="campo__erro"><?= e($erros['email']) ?></span><?php endif; ?>
                </div>
                <div class="campo">
                    <label for="telefone">Telefone / WhatsApp <span class="obrigatorio">*</span></label>
                    <input class="controle <?= isset($erros['telefone']) ? 'controle--erro' : '' ?>" type="tel" id="telefone" name="telefone" value="<?= e($valores['telefone'] ?? '') ?>" placeholder="(11) 99999-9999" data-mascara="telefone" inputmode="tel" required>
                    <?php if (isset($erros['telefone'])): ?><span class="campo__erro"><?= e($erros['telefone']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Endereço</h2>
                <p>Dados opcionais para correspondências e documentos.</p>
            </div>
        </div>
        <div class="card__corpo">
            <div class="grade-campos grade-campos--3">
                <div class="campo">
                    <label for="cep">CEP</label>
                    <input class="controle <?= isset($erros['cep']) ? 'controle--erro' : '' ?>" type="text" id="cep" name="cep" value="<?= e($valores['cep'] ?? '') ?>" placeholder="00000-000" data-mascara="cep" inputmode="numeric">
                    <?php if (isset($erros['cep'])): ?><span class="campo__erro"><?= e($erros['cep']) ?></span><?php endif; ?>
                </div>
                <div class="campo" style="grid-column: span 2;">
                    <label for="endereco">Logradouro</label>
                    <input class="controle" type="text" id="endereco" name="endereco" value="<?= e($valores['endereco'] ?? '') ?>" maxlength="180" placeholder="Rua, avenida, alameda...">
                </div>
                <div class="campo">
                    <label for="numero">Número</label>
                    <input class="controle" type="text" id="numero" name="numero" value="<?= e($valores['numero'] ?? '') ?>" maxlength="20" placeholder="123">
                </div>
                <div class="campo">
                    <label for="complemento">Complemento</label>
                    <input class="controle" type="text" id="complemento" name="complemento" value="<?= e($valores['complemento'] ?? '') ?>" maxlength="80" placeholder="Apto, bloco...">
                </div>
                <div class="campo">
                    <label for="bairro">Bairro</label>
                    <input class="controle" type="text" id="bairro" name="bairro" value="<?= e($valores['bairro'] ?? '') ?>" maxlength="100" placeholder="Centro">
                </div>
                <div class="campo">
                    <label for="cidade">Cidade</label>
                    <input class="controle" type="text" id="cidade" name="cidade" value="<?= e($valores['cidade'] ?? '') ?>" maxlength="100" placeholder="São Paulo">
                </div>
                <div class="campo">
                    <label for="estado">Estado</label>
                    <select class="controle <?= isset($erros['estado']) ? 'controle--erro' : '' ?>" id="estado" name="estado">
                        <option value="">Selecione</option>
                        <?php foreach ($ufs as $sigla => $nomeUf): ?>
                            <option value="<?= e($sigla) ?>" <?= ($valores['estado'] ?? '') === $sigla ? 'selected' : '' ?>><?= e($sigla) ?> — <?= e($nomeUf) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($erros['estado'])): ?><span class="campo__erro"><?= e($erros['estado']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Observações</h2>
                <p>Preferências, restrições ou informações relevantes sobre o cliente.</p>
            </div>
        </div>
        <div class="card__corpo">
            <div class="campo">
                <label for="observacoes">Anotações internas</label>
                <textarea class="controle" id="observacoes" name="observacoes" maxlength="3000" rows="4" placeholder="Ex.: prefere assentos na janela, restrição alimentar, contato preferencial..."><?= e($valores['observacoes'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div class="formulario__rodape">
        <a class="botao botao--fantasma" href="<?= e($editando ? url('clientes/' . $cliente['id']) : url('clientes')) ?>">Cancelar</a>
        <button type="submit" class="botao botao--primario"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Cadastrar cliente' ?></button>
    </div>
</form>
