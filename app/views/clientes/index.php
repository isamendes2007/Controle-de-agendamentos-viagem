<?php
/**
 * Listagem de clientes.
 *
 * Recebe: $clientes, $busca, $situacao, $paginacao, $parametrosFiltro.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('clientes/novo')) . '">' . icone('plus', 16) . ' Novo cliente</a>';
?>
<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="clientes">
    <div class="campo campo--busca">
        <label for="busca">Buscar cliente</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Nome, CPF, e-mail, telefone ou cidade">
        </div>
    </div>
    <div class="campo" style="flex:0 1 200px;">
        <label for="situacao">Situação</label>
        <select class="controle" id="situacao" name="situacao">
            <option value="ativos" <?= $situacao === 'ativos' ? 'selected' : '' ?>>Ativos</option>
            <option value="inativos" <?= $situacao === 'inativos' ? 'selected' : '' ?>>Inativos</option>
            <option value="todos" <?= $situacao === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Filtrar</button>
        <a class="botao botao--fantasma" href="<?= e(url('clientes')) ?>">Limpar</a>
    </div>
</form>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Clientes cadastrados</h2>
            <p><?= numero($paginacao['total']) ?> cliente(s) encontrado(s).</p>
        </div>
    </div>

    <?php if ($clientes === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('usuarios', 26) ?></div>
            <h3>Nenhum cliente encontrado</h3>
            <p>Ajuste a busca ou cadastre o primeiro viajante da agência.</p>
            <a class="botao botao--primario" href="<?= e(url('clientes/novo')) ?>"><?= icone('plus', 16) ?> Novo cliente</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contato</th>
                        <th>Cidade</th>
                        <th class="num">Reservas</th>
                        <th>Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td>
                                <div class="celula-principal">
                                    <span class="avatar avatar--usuario"><?= e(iniciais($cliente['nome'])) ?></span>
                                    <div class="celula-principal__texto">
                                        <strong><?= e($cliente['nome']) ?></strong>
                                        <small><?= e(formatar_cpf($cliente['cpf'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong style="font-weight:500;"><?= e($cliente['email']) ?></strong>
                                    <small><?= e(formatar_telefone($cliente['telefone'])) ?></small>
                                </div>
                            </td>
                            <td>
                                <?= e($cliente['cidade'] ?: '—') ?>
                                <?php if (!empty($cliente['estado'])): ?><span class="texto-muted">/<?= e($cliente['estado']) ?></span><?php endif; ?>
                            </td>
                            <td class="num"><?= numero($cliente['total_reservas'] ?? 0) ?></td>
                            <td><?= (int) $cliente['ativo'] === 1 ? '<span class="selo selo--ativo">Ativo</span>' : '<span class="selo selo--inativo">Inativo</span>' ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('clientes/' . $cliente['id'])) ?>" title="Ver ficha" aria-label="Ver ficha"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('clientes/' . $cliente['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'clientes', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
