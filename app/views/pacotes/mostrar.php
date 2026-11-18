<?php
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('pacotes')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('pacotes/' . $pacote['id'] . '/editar')) . '">' . icone('editar', 16) . ' Editar</a>';
$acoesPagina .= '<a class="botao botao--primario" href="' . e(url('viagens/nova', ['pacote_id' => $pacote['id']])) . '">' . icone('plus', 16) . ' Nova partida</a>';
?>
<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="linha-flex linha-flex--entre">
                    <div>
                        <span class="etiqueta etiqueta--coral"><?= e(rotulo_categoria($pacote['categoria'])) ?></span>
                        <h2 class="mt-1" style="font-family:var(--fonte-titulo); font-size:1.55rem;"><?= e($pacote['nome']) ?></h2>
                        <p class="texto-suave mb-0"><?= icone('map', 15) ?> <?= e($pacote['destino']) ?> · <?= e($pacote['cidade']) ?><?= $pacote['estado'] ? '/' . e($pacote['estado']) : '' ?> · <?= e($pacote['pais']) ?></p>
                    </div>
                    <div class="texto-direita">
                        <span class="status <?= (int) $pacote['ativo'] === 1 ? 'status--pago' : 'status--neutro' ?>"><?= (int) $pacote['ativo'] === 1 ? 'Pacote ativo' : 'Pacote inativo' ?></span>
                        <div class="mt-1" style="font-size:1.25rem; font-weight:700; color:var(--teal);"><?= e(moeda($pacote['preco_base'])) ?></div>
                        <small class="texto-muted">preço base por pessoa</small>
                    </div>
                </div>
                <div class="divisor"></div>
                <p style="color:var(--texto-suave); line-height:1.8; margin-bottom:0;"><?= nl2br(e($pacote['descricao'] ?: 'Nenhuma descrição cadastrada para este pacote.')) ?></p>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Partidas programadas</h2>
                    <p>Datas, ocupação e desempenho de cada viagem.</p>
                </div>
                <a class="card__link" href="<?= e(url('viagens', ['busca' => $pacote['nome']])) ?>">Ver agenda <?= icone('arrow-right', 14) ?></a>
            </div>
            <?php if ($viagens === []): ?>
                <div class="estado-vazio">
                    <div class="estado-vazio__icone"><?= icone('calendar', 26) ?></div>
                    <h3>Nenhuma partida programada</h3>
                    <p>Defina as datas e abra as vendas deste pacote.</p>
                    <a class="botao botao--primario botao--pequeno" href="<?= e(url('viagens/nova', ['pacote_id' => $pacote['id']])) ?>"><?= icone('plus', 15) ?> Programar partida</a>
                </div>
            <?php else: ?>
                <div class="tabela-wrapper">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Partida</th>
                                <th>Período</th>
                                <th>Preço</th>
                                <th>Ocupação</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($viagens as $viagem): ?>
                                <?php $ocupacaoViagem = percentual((float) $viagem['viajantes'], (float) $viagem['vagas_totais']); ?>
                                <tr>
                                    <td>
                                        <div class="celula-principal__texto">
                                            <strong><?= data_br($viagem['data_partida']) ?></strong>
                                            <small><?= data_br($viagem['data_retorno']) ?></small>
                                        </div>
                                    </td>
                                    <td><?= (int) ((strtotime($viagem['data_retorno']) - strtotime($viagem['data_partida'])) / 86400) + 1 ?> dias</td>
                                    <td class="valor"><?= e(moeda($viagem['preco'])) ?></td>
                                    <td>
                                        <div class="barra-linear__topo" style="margin-bottom:4px;"><span><?= numero($viagem['viajantes']) ?>/<?= numero($viagem['vagas_totais']) ?></span><strong><?= $ocupacaoViagem ?>%</strong></div>
                                        <div class="barra-linear__trilho"><div class="barra-linear__preenchimento" style="width:<?= $ocupacaoViagem ?>%"></div></div>
                                    </td>
                                    <td><?= selo_status($viagem['status']) ?></td>
                                    <td class="texto-direita"><a class="acao-icone" href="<?= e(url('viagens/' . $viagem['id'])) ?>" title="Ver viagem"><?= icone('visualizar', 16) ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Desempenho do pacote</h2>
                    <p>Números acumulados.</p>
                </div>
            </div>
            <div class="card__corpo">
                <div class="financeiro-resumo">
                    <div class="financeiro-linha"><span>Reservas</span><strong><?= numero($indicadores['reservas']) ?></strong></div>
                    <div class="financeiro-linha"><span>Viajantes</span><strong><?= numero($indicadores['viajantes']) ?></strong></div>
                    <div class="financeiro-linha"><span>Receita contratada</span><strong><?= e(moeda($indicadores['receita'])) ?></strong></div>
                    <div class="financeiro-linha"><span>Partidas totais</span><strong><?= numero(count($viagens)) ?></strong></div>
                </div>
                <div class="divisor"></div>
                <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                    <a class="botao botao--secundario botao--largo" href="<?= e(url('pacotes/' . $pacote['id'] . '/editar')) ?>"><?= icone('editar', 16) ?> Editar pacote</a>
                    <form method="post" action="<?= e(url('pacotes/' . $pacote['id'] . '/alternar')) ?>" data-confirmar="<?= (int) $pacote['ativo'] === 1 ? 'Deseja desativar este pacote? As partidas existentes serão preservadas.' : 'Deseja reativar este pacote?' ?>">
                        <?= csrf_campo() ?>
                        <button type="submit" class="botao <?= (int) $pacote['ativo'] === 1 ? 'botao--perigo' : 'botao--secundario' ?> botao--largo">
                            <?= icone((int) $pacote['ativo'] === 1 ? 'eye-off' : 'refresh', 16) ?>
                            <?= (int) $pacote['ativo'] === 1 ? 'Desativar pacote' : 'Reativar pacote' ?>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__corpo">
                <div class="caixa-info">
                    <?= icone('info', 18) ?>
                    <span>As partidas usam o preço base como referência, mas podem ter valores promocionais ou sazonais.</span>
                </div>
            </div>
        </section>
    </aside>
</div>
