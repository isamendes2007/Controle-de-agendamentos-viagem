<?php
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('viagens/nova')) . '">' . icone('plus', 17) . ' Programar viagem</a>';
?>
<form method="get" action="<?= e(url('viagens')) ?>" class="painel-filtros">
    <div class="campo campo--busca">
        <label for="busca">Buscar viagem</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Pacote, destino ou cidade">
        </div>
    </div>
    <div class="campo" style="flex:1 1 130px;">
        <label for="status">Status</label>
        <select class="controle" id="status" name="status">
            <option value="">Todos</option>
            <?php foreach (opcoes_status_viagem() as $chave => $rotulo): ?>
                <option value="<?= e($chave) ?>" <?= $status === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo" style="flex:1 1 160px;">
        <label for="destino_id">Destino</label>
        <select class="controle" id="destino_id" name="destino_id">
            <option value="">Todos</option>
            <?php foreach ($destinos as $destinoFiltro): ?>
                <option value="<?= (int) $destinoFiltro['id'] ?>" <?= $destinoId === (int) $destinoFiltro['id'] ? 'selected' : '' ?>><?= e($destinoFiltro['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo" style="flex:0 1 150px;">
        <label for="de">Partida de</label>
        <input class="controle" type="date" id="de" name="de" value="<?= e($de ?? '') ?>">
    </div>
    <div class="campo" style="flex:0 1 150px;">
        <label for="ate">Até</label>
        <input class="controle" type="date" id="ate" name="ate" value="<?= e($ate ?? '') ?>">
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Filtrar</button>
        <?php if ($busca !== '' || $status !== '' || $destinoId > 0 || $de || $ate): ?>
            <a class="botao botao--fantasma" href="<?= e(url('viagens')) ?>">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="linha-flex" style="gap:9px; margin-bottom:18px;">
    <span class="etiqueta etiqueta--teal"><?= icone('calendar', 13) ?> <?= numero($resumo['futuras']) ?> futura(s)</span>
    <span class="etiqueta etiqueta--amarelo"><?= icone('clock', 13) ?> <?= numero($resumo['em_andamento']) ?> em andamento</span>
    <span class="etiqueta"><?= icone('check', 13) ?> <?= numero($resumo['concluidas']) ?> concluída(s)</span>
</div>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Programação de viagens</h2>
            <p><?= numero($paginacao['total']) ?> partida(s) encontrada(s).</p>
        </div>
    </div>

    <?php if ($viagens === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('viagens', 27) ?></div>
            <h3>Nenhuma viagem encontrada</h3>
            <p>Programe a próxima partida a partir de um dos pacotes cadastrados.</p>
            <a class="botao botao--primario" href="<?= e(url('viagens/nova')) ?>"><?= icone('plus', 16) ?> Programar viagem</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Viagem</th>
                        <th>Período</th>
                        <th>Preço</th>
                        <th>Ocupação</th>
                        <th>Receita prevista</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($viagens as $viagem): ?>
                        <?php
                        $ocupacaoViagem = percentual((float) $viagem['viajantes'], (float) $viagem['vagas_totais']);
                        $restantes = max(0, (int) $viagem['vagas_totais'] - (int) $viagem['viajantes']);
                        ?>
                        <tr>
                            <td>
                                <div class="celula-principal">
                                    <span class="avatar avatar--usuario" style="background:linear-gradient(145deg,var(--marinho-2),var(--teal));"><?= icone('plane', 15) ?></span>
                                    <div class="celula-principal__texto">
                                        <strong><?= e($viagem['destino']) ?></strong>
                                        <small><?= e($viagem['pacote']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong style="font-weight:500;"><?= data_br($viagem['data_partida']) ?></strong>
                                    <small>até <?= data_br($viagem['data_retorno']) ?></small>
                                </div>
                            </td>
                            <td class="valor"><?= e(moeda($viagem['preco'])) ?></td>
                            <td style="min-width:135px;">
                                <div class="barra-linear__topo" style="margin-bottom:4px;">
                                    <span><?= numero($viagem['viajantes']) ?>/<?= numero($viagem['vagas_totais']) ?></span>
                                    <strong><?= $ocupacaoViagem ?>%</strong>
                                </div>
                                <div class="barra-linear__trilho"><div class="barra-linear__preenchimento <?= $restantes <= 3 ? 'barra-linear__preenchimento--amarelo' : '' ?>" style="width:<?= $ocupacaoViagem ?>%"></div></div>
                            </td>
                            <td class="valor"><?= e(moeda($viagem['receita_prevista'])) ?></td>
                            <td><?= selo_status($viagem['status']) ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('viagens/' . $viagem['id'])) ?>" title="Ver viagem" aria-label="Ver viagem"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('viagens/' . $viagem['id'] . '/editar')) ?>" title="Editar viagem" aria-label="Editar viagem"><?= icone('editar', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'viagens', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
