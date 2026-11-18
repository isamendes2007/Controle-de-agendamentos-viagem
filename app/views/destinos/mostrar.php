<?php
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('destinos')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('destinos/' . $destino['id'] . '/editar')) . '">' . icone('editar', 16) . ' Editar</a>';
$acoesPagina .= '<a class="botao botao--primario" href="' . e(url('pacotes/novo', ['destino_id' => $destino['id']])) . '">' . icone('plus', 16) . ' Novo pacote</a>';
?>
<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="linha-flex linha-flex--entre">
                    <div class="perfil-resumo">
                        <span class="avatar avatar--grande" style="background:linear-gradient(145deg,#d07656,#6f4b3d);"><?= icone('map', 27) ?></span>
                        <div class="perfil-resumo__dados">
                            <h2><?= e($destino['nome']) ?></h2>
                            <p class="texto-suave mb-0"><?= e($destino['cidade']) ?><?= $destino['estado'] ? ' · ' . e($destino['estado']) : '' ?> · <?= e($destino['pais']) ?></p>
                        </div>
                    </div>
                    <span class="status <?= (int) $destino['ativo'] === 1 ? 'status--pago' : 'status--neutro' ?>"><?= (int) $destino['ativo'] === 1 ? 'Destino ativo' : 'Destino inativo' ?></span>
                </div>
                <div class="divisor"></div>
                <p style="color:var(--texto-suave); line-height:1.75; margin-bottom:0;"><?= nl2br(e($destino['descricao'] ?: 'Nenhuma descrição cadastrada para este destino.')) ?></p>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Pacotes neste destino</h2>
                    <p><?= numero(count($pacotes)) ?> experiência(s) cadastrada(s).</p>
                </div>
                <a class="card__link" href="<?= e(url('pacotes', ['destino_id' => $destino['id']])) ?>">Ver no catálogo <?= icone('arrow-right', 14) ?></a>
            </div>
            <?php if ($pacotes === []): ?>
                <div class="estado-vazio">
                    <div class="estado-vazio__icone"><?= icone('pacotes', 26) ?></div>
                    <h3>Nenhum pacote ainda</h3>
                    <p>Crie uma experiência para este destino.</p>
                    <a class="botao botao--primario botao--pequeno" href="<?= e(url('pacotes/novo', ['destino_id' => $destino['id']])) ?>"><?= icone('plus', 15) ?> Criar pacote</a>
                </div>
            <?php else: ?>
                <div class="tabela-wrapper">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Pacote</th>
                                <th>Categoria</th>
                                <th>Próxima partida</th>
                                <th>A partir de</th>
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
                                            <small><?= e(limitar_texto($pacote['descricao'], 58)) ?></small>
                                        </div>
                                    </td>
                                    <td><span class="etiqueta etiqueta--teal"><?= e(rotulo_categoria($pacote['categoria'])) ?></span></td>
                                    <td><?= data_br($pacote['proxima_partida']) ?></td>
                                    <td class="valor"><?= e(moeda($pacote['proximo_preco'])) ?></td>
                                    <td><span class="status <?= (int) $pacote['ativo'] === 1 ? 'status--pago' : 'status--neutro' ?>"><?= (int) $pacote['ativo'] === 1 ? 'Ativo' : 'Inativo' ?></span></td>
                                    <td class="texto-direita"><a class="acao-icone" href="<?= e(url('pacotes/' . $pacote['id'])) ?>" title="Ver pacote"><?= icone('visualizar', 16) ?></a></td>
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
                    <h2>Próximas partidas</h2>
                    <p>Viagens confirmadas ou planejadas.</p>
                </div>
            </div>
            <div class="card__corpo">
                <?php if ($proximasViagens === []): ?>
                    <div class="caixa-info">
                        <?= icone('calendar', 18) ?>
                        <span>Nenhuma partida futura programada para este destino.</span>
                    </div>
                <?php else: ?>
                    <div class="lista-viagens">
                        <?php foreach ($proximasViagens as $viagem): ?>
                            <?php $ocupacaoViagem = percentual((float) $viagem['viajantes'], (float) $viagem['vagas_totais']); ?>
                            <a class="viagem-linha" href="<?= e(url('viagens/' . $viagem['id'])) ?>">
                                <span class="viagem-linha__data">
                                    <span class="viagem-linha__dia"><?= date('d', strtotime($viagem['data_partida'])) ?></span>
                                    <span class="viagem-linha__mes"><?= mes_abreviado((int) date('n', strtotime($viagem['data_partida']))) ?></span>
                                </span>
                                <span class="viagem-linha__texto">
                                    <strong><?= e($viagem['pacote']) ?></strong>
                                    <span><?= data_br($viagem['data_partida']) ?> a <?= data_br($viagem['data_retorno']) ?></span>
                                </span>
                                <span class="viagem-linha__ocupacao">
                                    <span class="barra-linear__trilho"><span class="barra-linear__preenchimento" style="display:block;width:<?= $ocupacaoViagem ?>%;height:100%"></span></span>
                                    <small><?= $ocupacaoViagem ?>%</small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card__corpo">
                <div class="dados-lista">
                    <div class="dado">
                        <span class="dado__icone"><?= icone('globe', 16) ?></span>
                        <div class="dado__texto"><span>Continente</span><strong><?= e($destino['continente']) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('flag', 16) ?></span>
                        <div class="dado__texto"><span>País</span><strong><?= e($destino['pais']) ?></strong></div>
                    </div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('calendar', 16) ?></span>
                        <div class="dado__texto"><span>Cadastrado em</span><strong><?= e(data_br($destino['criado_em'])) ?></strong></div>
                    </div>
                </div>
                <div class="divisor"></div>
                <form method="post" action="<?= e(url('destinos/' . $destino['id'] . '/alternar')) ?>" data-confirmar="<?= (int) $destino['ativo'] === 1 ? 'Deseja desativar este destino? Os pacotes e o histórico serão preservados.' : 'Deseja reativar este destino?' ?>">
                    <?= csrf_campo() ?>
                    <button type="submit" class="botao <?= (int) $destino['ativo'] === 1 ? 'botao--perigo' : 'botao--secundario' ?> botao--largo">
                        <?= icone((int) $destino['ativo'] === 1 ? 'eye-off' : 'refresh', 16) ?>
                        <?= (int) $destino['ativo'] === 1 ? 'Desativar destino' : 'Reativar destino' ?>
                    </button>
                </form>
            </div>
        </section>
    </aside>
</div>
