<?php
$editando = $usuarioEditado !== null;
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('usuarios')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$proprio = $editando && (int) $usuarioEditado['id'] === (int) (usuario_atual()['id'] ?? 0);
?>
<form method="post" action="<?= e($editando ? url('usuarios/' . $usuarioEditado['id'] . '/editar') : url('usuarios/novo')) ?>" class="formulario" data-carregando>
    <?= csrf_campo() ?>

    <div class="grade grade--detalhe">
        <div class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados do usuário</h2>
                        <p>Informações de identificação e contato da equipe.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('usuarios', 13) ?> Equipe</span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['nome']) ? 'controle--erro' : '' ?>" type="text" id="nome" name="nome" value="<?= e($valores['nome'] ?? '') ?>" maxlength="100" placeholder="Ex.: Marina Costa" required autofocus>
                            <?php if (isset($erros['nome'])): ?><span class="campo__erro"><?= e($erros['nome']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo campo--largo">
                            <label for="email">E-mail de acesso <span class="obrigatorio">*</span></label>
                            <input class="controle <?= isset($erros['email']) ? 'controle--erro' : '' ?>" type="email" id="email" name="email" value="<?= e($valores['email'] ?? '') ?>" maxlength="150" placeholder="nome@horizonteviagens.com.br" autocomplete="off" required>
                            <?php if (isset($erros['email'])): ?><span class="campo__erro"><?= e($erros['email']) ?></span><?php else: ?><span class="campo__ajuda">Este e-mail será usado para entrar no sistema.</span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input class="controle <?= isset($erros['telefone']) ? 'controle--erro' : '' ?>" type="text" id="telefone" name="telefone" value="<?= e($valores['telefone'] ?? '') ?>" placeholder="(11) 90000-0000" data-mascara="telefone" inputmode="tel">
                            <?php if (isset($erros['telefone'])): ?><span class="campo__erro"><?= e($erros['telefone']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="perfil">Perfil de acesso <span class="obrigatorio">*</span></label>
                            <select class="controle <?= isset($erros['perfil']) ? 'controle--erro' : '' ?>" id="perfil" name="perfil" required>
                                <option value="agente" <?= ($valores['perfil'] ?? 'agente') === 'agente' ? 'selected' : '' ?>>Agente de viagens</option>
                                <option value="administrador" <?= ($valores['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                            </select>
                            <?php if (isset($erros['perfil'])): ?><span class="campo__erro"><?= e($erros['perfil']) ?></span><?php endif; ?>
                        </div>

                        <div class="campo">
                            <label for="ativo">Situação do acesso <span class="obrigatorio">*</span></label>
                            <select class="controle" id="ativo" name="ativo" required>
                                <option value="1" <?= (int) ($valores['ativo'] ?? 1) === 1 ? 'selected' : '' ?>>Ativo</option>
                                <option value="0" <?= (int) ($valores['ativo'] ?? 1) === 0 ? 'selected' : '' ?>>Inativo</option>
                            </select>
                            <?php if ($proprio): ?><span class="campo__ajuda">Você não pode desativar a própria conta.</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card__cabecalho">
                    <div>
                        <h2><?= $editando ? 'Redefinir senha' : 'Senha de acesso' ?></h2>
                        <p><?= $editando ? 'Deixe em branco para manter a senha atual.' : 'Crie uma senha inicial segura para o novo usuário.' ?></p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="campo">
                        <label for="senha"><?= $editando ? 'Nova senha' : 'Senha' ?> <?= $editando ? '' : '<span class="obrigatorio">*</span>' ?></label>
                        <div class="grupo-senha">
                            <input class="controle <?= isset($erros['senha']) ? 'controle--erro' : '' ?>" type="password" id="senha" name="senha" placeholder="<?= $editando ? 'Manter senha atual' : 'Mínimo de 6 caracteres' ?>" autocomplete="new-password" <?= $editando ? '' : 'required' ?>>
                            <button type="button" class="botao-senha" data-alternar-senha aria-label="Mostrar senha"><?= icone('eye', 18) ?></button>
                        </div>
                        <?php if (isset($erros['senha'])): ?><span class="campo__erro"><?= e($erros['senha']) ?></span><?php else: ?><span class="campo__ajuda">Use pelo menos 6 caracteres e evite dados óbvios.</span><?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grade" style="align-content:start;">
            <section class="card">
                <div class="card__corpo">
                    <div class="caixa-info">
                        <?= icone('lock', 18) ?>
                        <span>Administradores gerenciam usuários, exclusões de viagens e configurações sensíveis. Agentes operam clientes, reservas e pagamentos.</span>
                    </div>
                    <div class="divisor"></div>
                    <ul class="lista-marcadores">
                        <li><?= icone('check', 15) ?> Cada pessoa deve ter seu próprio acesso.</li>
                        <li><?= icone('check', 15) ?> O sistema mantém ao menos um administrador ativo.</li>
                        <li><?= icone('check', 15) ?> Usuários inativos não conseguem entrar.</li>
                    </ul>
                </div>
            </section>

            <section class="card">
                <div class="card__corpo">
                    <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                        <button type="submit" class="botao botao--primario botao--largo"><?= icone('check', 17) ?> <?= $editando ? 'Salvar alterações' : 'Criar usuário' ?></button>
                        <a class="botao botao--fantasma botao--largo" href="<?= e(url('usuarios')) ?>">Cancelar</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</form>
