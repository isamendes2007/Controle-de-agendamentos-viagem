<?php
/**
 * Meu perfil.
 *
 * Recebe: $usuario, $valores, $erros e $estatisticas.
 */
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('dashboard')) . '">' . icone('chevron-left', 16) . ' Voltar ao painel</a>';
$erro = static fn (string $campo): ?string => $erros[$campo] ?? null;
?>
<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <form method="post" action="<?= e(url('perfil')) ?>" class="formulario" data-carregando>
            <?= csrf_campo() ?>

            <section class="card mb-2">
                <div class="card__cabecalho">
                    <div>
                        <h2>Dados pessoais</h2>
                        <p>Informações usadas no seu acesso ao sistema.</p>
                    </div>
                    <span class="etiqueta etiqueta--teal"><?= icone('usuarios', 13) ?> <?= e(rotulo_perfil($usuario['perfil'] ?? 'agente')) ?></span>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo campo--largo">
                            <label for="nome">Nome completo <span class="obrigatorio">*</span></label>
                            <input class="controle <?= $erro('nome') ? 'controle--erro' : '' ?>" type="text" id="nome" name="nome" value="<?= e($valores['nome'] ?? '') ?>" maxlength="100" required>
                            <?php if ($erro('nome')): ?><span class="campo__erro"><?= e($erro('nome')) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="email">E-mail <span class="obrigatorio">*</span></label>
                            <input class="controle <?= $erro('email') ? 'controle--erro' : '' ?>" type="email" id="email" name="email" value="<?= e($valores['email'] ?? '') ?>" maxlength="150" required>
                            <?php if ($erro('email')): ?><span class="campo__erro"><?= e($erro('email')) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input class="controle <?= $erro('telefone') ? 'controle--erro' : '' ?>" type="text" id="telefone" name="telefone" value="<?= e($valores['telefone'] ?? '') ?>" placeholder="(11) 90000-0000" data-mascara="telefone" inputmode="tel">
                            <?php if ($erro('telefone')): ?><span class="campo__erro"><?= e($erro('telefone')) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card mb-2">
                <div class="card__cabecalho">
                    <div>
                        <h2>Alterar senha</h2>
                        <p>Preencha apenas se quiser trocar a senha de acesso.</p>
                    </div>
                </div>
                <div class="card__corpo">
                    <div class="grade-campos">
                        <div class="campo">
                            <label for="senha_atual">Senha atual</label>
                            <div class="grupo-senha">
                                <input class="controle <?= $erro('senha_atual') ? 'controle--erro' : '' ?>" type="password" id="senha_atual" name="senha_atual" autocomplete="current-password">
                                <button type="button" class="botao-senha" data-alternar-senha aria-label="Mostrar senha"><?= icone('eye', 18) ?></button>
                            </div>
                            <?php if ($erro('senha_atual')): ?><span class="campo__erro"><?= e($erro('senha_atual')) ?></span><?php endif; ?>
                        </div>
                        <div class="campo">
                            <label for="senha">Nova senha</label>
                            <div class="grupo-senha">
                                <input class="controle <?= $erro('senha') ? 'controle--erro' : '' ?>" type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" autocomplete="new-password">
                                <button type="button" class="botao-senha" data-alternar-senha aria-label="Mostrar senha"><?= icone('eye', 18) ?></button>
                            </div>
                            <?php if ($erro('senha')): ?><span class="campo__erro"><?= e($erro('senha')) ?></span><?php else: ?><span class="campo__ajuda">Deixe em branco para manter a senha atual.</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <div class="formulario__rodape">
                <a class="botao botao--fantasma" href="<?= e(url('dashboard')) ?>">Cancelar</a>
                <button type="submit" class="botao botao--primario"><?= icone('check', 17) ?> Salvar dados</button>
            </div>
        </form>
    </div>

    <aside class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="perfil-resumo">
                    <span class="avatar avatar--grande"><?= e(iniciais($usuario['nome'] ?? '')) ?></span>
                    <div class="perfil-resumo__dados">
                        <h2><?= e($usuario['nome'] ?? '') ?></h2>
                        <span class="texto-muted"><?= e($usuario['email'] ?? '') ?></span>
                    </div>
                </div>
                <div class="divisor"></div>
                <div class="financeiro-resumo">
                    <div class="financeiro-linha"><span>Reservas registradas</span><strong><?= numero($estatisticas['reservas'] ?? 0) ?></strong></div>
                    <div class="financeiro-linha"><span>Pagamentos lançados</span><strong><?= numero($estatisticas['pagamentos'] ?? 0) ?></strong></div>
                    <div class="financeiro-linha financeiro-linha--destaque"><span>Total recebido</span><strong><?= e(moeda($estatisticas['recebido'] ?? 0)) ?></strong></div>
                </div>
                <div class="divisor"></div>
                <div class="dados-lista">
                    <div class="dado">
                        <span class="dado__icone"><?= icone('lock', 16) ?></span>
                        <div class="dado__texto"><span>Perfil de acesso</span><strong><?= e(rotulo_perfil($usuario['perfil'] ?? 'agente')) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('calendario', 16) ?></span>
                        <div class="dado__texto"><span>Conta criada em</span><strong><?= data_br($usuario['criado_em'] ?? null) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('relogio', 16) ?></span>
                        <div class="dado__texto"><span>Último acesso</span><strong><?= $usuario['ultimo_acesso'] ? data_hora_br($usuario['ultimo_acesso']) : '—' ?></strong></div>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</div>
