<?php
/**
 * Listagem de usuários (somente administrador).
 *
 * Recebe: $usuarios e $perfilOpcoes.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('usuarios/novo')) . '">' . icone('plus', 16) . ' Novo usuário</a>';
?>
<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Equipe da agência</h2>
            <p><?= numero(count($usuarios)) ?> usuário(s) cadastrado(s).</p>
        </div>
    </div>

    <?php if ($usuarios === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('lock', 26) ?></div>
            <h3>Nenhum usuário cadastrado</h3>
            <p>Cadastre o primeiro acesso da equipe.</p>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Perfil</th>
                        <th>Telefone</th>
                        <th class="num">Reservas</th>
                        <th>Último acesso</th>
                        <th>Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuarioListado): ?>
                        <tr>
                            <td>
                                <div class="celula-principal">
                                    <span class="avatar avatar--usuario"><?= e(iniciais($usuarioListado['nome'])) ?></span>
                                    <div class="celula-principal__texto">
                                        <strong><?= e($usuarioListado['nome']) ?></strong>
                                        <small><?= e($usuarioListado['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($perfilOpcoes[$usuarioListado['perfil']] ?? rotulo_perfil($usuarioListado['perfil'])) ?></td>
                            <td><?= e(formatar_telefone($usuarioListado['telefone'] ?? '')) ?></td>
                            <td class="num"><?= numero($usuarioListado['total_reservas'] ?? 0) ?></td>
                            <td><?= $usuarioListado['ultimo_acesso'] ? data_hora_br($usuarioListado['ultimo_acesso']) : 'Nunca' ?></td>
                            <td><?= (int) $usuarioListado['ativo'] === 1 ? '<span class="selo selo--ativo">Ativo</span>' : '<span class="selo selo--inativo">Inativo</span>' ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('usuarios/' . $usuarioListado['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                    <?php if ((int) $usuarioListado['id'] !== (int) (usuario_atual()['id'] ?? 0)): ?>
                                        <form method="post" action="<?= e(url('usuarios/' . $usuarioListado['id'] . '/alternar')) ?>" data-confirmar="<?= (int) $usuarioListado['ativo'] === 1 ? 'Desativar este usuário?' : 'Reativar este usuário?' ?>">
                                            <?= csrf_campo() ?>
                                            <button type="submit" class="acao-icone" title="Ativar/desativar" aria-label="Ativar ou desativar">
                                                <?= icone((int) $usuarioListado['ativo'] === 1 ? 'eye-off' : 'refresh', 16) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
