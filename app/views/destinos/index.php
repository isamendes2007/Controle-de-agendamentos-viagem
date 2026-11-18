<?php
/**
 * Listagem de destinos.
 *
 * Recebe: $destinos, $busca, $situacao, $paginacao, $parametrosFiltro.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('destinos/novo')) . '">' . icone('plus', 16) . ' Novo destino</a>';
?>
<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="destinos">
    <div class="campo campo--busca">
        <label for="busca">Buscar destino</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Nome, cidade, país ou continente">
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
        <a class="botao botao--fantasma" href="<?= e(url('destinos')) ?>">Limpar</a>
    </div>
</form>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Destinos operados</h2>
            <p><?= numero($paginacao['total']) ?> destino(s) encontrado(s).</p>
        </div>
    </div>

    <?php if ($destinos === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('destino', 26) ?></div>
            <h3>Nenhum destino encontrado</h3>
            <p>Cadastre o primeiro destino para começar a criar pacotes.</p>
            <a class="botao botao--primario" href="<?= e(url('destinos/novo')) ?>"><?= icone('plus', 16) ?> Novo destino</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Destino</th>
                        <th>Localização</th>
                        <th class="num">Pacotes ativos</th>
                        <th class="num">Viagens futuras</th>
                        <th>Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinos as $destino): ?>
                        <tr>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong><?= e($destino['nome']) ?></strong>
                                    <small><?= e(limitar_texto($destino['descricao'] ?? '', 60)) ?></small>
                                </div>
                            </td>
                            <td>
                                <?= e($destino['cidade']) ?>
                                <?php if (!empty($destino['estado'])): ?><span class="texto-muted">/<?= e($destino['estado']) ?></span><?php endif; ?>
                                <small class="texto-muted" style="display:block;"><?= e($destino['pais']) ?> · <?= e($destino['continente']) ?></small>
                            </td>
                            <td class="num"><?= numero($destino['total_pacotes'] ?? 0) ?></td>
                            <td class="num"><?= numero($destino['viagens_futuras'] ?? 0) ?></td>
                            <td><?= (int) $destino['ativo'] === 1 ? '<span class="selo selo--ativo">Ativo</span>' : '<span class="selo selo--inativo">Inativo</span>' ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('destinos/' . $destino['id'])) ?>" title="Ver destino" aria-label="Ver destino"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('destinos/' . $destino['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'destinos', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
