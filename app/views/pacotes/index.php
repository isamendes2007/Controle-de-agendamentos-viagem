<?php
/**
 * Catálogo de pacotes.
 *
 * Recebe: $pacotes, $busca, $situacao, $destinos, $destinoId, $paginacao,
 * $parametrosFiltro e $categorias.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('pacotes/novo')) . '">' . icone('plus', 16) . ' Novo pacote</a>';
?>
<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="pacotes">
    <div class="campo campo--busca">
        <label for="busca">Buscar pacote</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Nome ou descrição">
        </div>
    </div>
    <div class="campo" style="flex:0 1 190px;">
        <label for="destino_id">Destino</label>
        <select class="controle" id="destino_id" name="destino_id">
            <option value="">Todos</option>
            <?php foreach ($destinos as $destinoFiltro): ?>
                <option value="<?= (int) $destinoFiltro['id'] ?>" <?= (int) $destinoId === (int) $destinoFiltro['id'] ? 'selected' : '' ?>><?= e($destinoFiltro['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo" style="flex:0 1 170px;">
        <label for="situacao">Situação</label>
        <select class="controle" id="situacao" name="situacao">
            <option value="ativos" <?= $situacao === 'ativos' ? 'selected' : '' ?>>Ativos</option>
            <option value="inativos" <?= $situacao === 'inativos' ? 'selected' : '' ?>>Inativos</option>
            <option value="todos" <?= $situacao === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Filtrar</button>
        <a class="botao botao--fantasma" href="<?= e(url('pacotes')) ?>">Limpar</a>
    </div>
</form>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Pacotes cadastrados</h2>
            <p><?= numero($paginacao['total']) ?> pacote(s) encontrado(s).</p>
        </div>
    </div>

    <?php if ($pacotes === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('pacote', 26) ?></div>
            <h3>Nenhum pacote encontrado</h3>
            <p>Cadastre um pacote vinculado a um destino para começar a vender.</p>
            <a class="botao botao--primario" href="<?= e(url('pacotes/novo')) ?>"><?= icone('plus', 16) ?> Novo pacote</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Pacote</th>
                        <th>Destino</th>
                        <th>Categoria</th>
                        <th class="num">Preço base</th>
                        <th class="num">Partidas futuras</th>
                        <th>Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacotes as $pacote): ?>
                        <tr>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong><?= e($pacote['nome']) ?></strong>
                                    <small><?= e(limitar_texto($pacote['descricao'] ?? '', 55)) ?></small>
                                </div>
                            </td>
                            <td>
                                <?= e($pacote['destino_nome'] ?? $pacote['destino'] ?? '—') ?>
                                <small class="texto-muted" style="display:block;"><?= e($pacote['cidade'] ?? '') ?><?= !empty($pacote['pais']) ? '/ ' . e($pacote['pais']) : '' ?></small>
                            </td>
                            <td><span class="etiqueta etiqueta--teal"><?= e(rotulo_categoria($pacote['categoria'] ?? '')) ?></span></td>
                            <td class="num valor"><?= e(moeda($pacote['preco_base'])) ?></td>
                            <td class="num">
                                <?= numero($pacote['viagens_futuras'] ?? 0) ?>
                                <?php if (!empty($pacote['proxima_partida'])): ?>
                                    <small class="texto-muted" style="display:block;"><?= data_br($pacote['proxima_partida']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $pacote['ativo'] === 1 ? '<span class="selo selo--ativo">Ativo</span>' : '<span class="selo selo--inativo">Inativo</span>' ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('pacotes/' . $pacote['id'])) ?>" title="Ver pacote" aria-label="Ver pacote"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('pacotes/' . $pacote['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'pacotes', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
