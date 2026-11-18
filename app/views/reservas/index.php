<?php
/**
 * Listagem de reservas.
 *
 * Recebe: $reservas, $busca, $status, $paginacao, $parametrosFiltro,
 * $statusOpcoes e $resumo.
 */
$acoesPagina = '<a class="botao botao--primario" href="' . e(url('reservas/nova')) . '">' . icone('plus', 16) . ' Nova reserva</a>';
?>
<div class="grade grade--4 mb-2">
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Reservas no filtro</span><span class="indicador__icone"><?= icone('reserva', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($resumo['total'] ?? 0) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Pendentes</span><span class="indicador__icone"><?= icone('relogio', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($resumo['pendentes'] ?? 0) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Confirmadas</span><span class="indicador__icone"><?= icone('check-circle', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($resumo['confirmadas'] ?? 0) ?></div>
    </article>
    <article class="indicador">
        <div class="indicador__topo"><span class="indicador__rotulo">Canceladas</span><span class="indicador__icone"><?= icone('x', 17) ?></span></div>
        <div class="indicador__valor"><?= numero($resumo['canceladas'] ?? 0) ?></div>
    </article>
</div>

<form method="get" action="<?= e(base_url('index.php')) ?>" class="painel-filtros">
    <input type="hidden" name="r" value="reservas">
    <div class="campo campo--busca">
        <label for="busca">Buscar reserva</label>
        <div class="grupo-icone">
            <?= icone('search', 16) ?>
            <input class="controle" type="search" id="busca" name="busca" value="<?= e($busca) ?>" placeholder="Código, cliente, CPF ou e-mail">
        </div>
    </div>
    <div class="campo" style="flex:0 1 200px;">
        <label for="status">Status</label>
        <select class="controle" id="status" name="status">
            <option value="">Todos</option>
            <?php foreach ($statusOpcoes as $chave => $rotulo): ?>
                <option value="<?= e($chave) ?>" <?= $status === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="painel-filtros__acoes">
        <button type="submit" class="botao botao--secundario"><?= icone('filtro', 15) ?> Filtrar</button>
        <a class="botao botao--fantasma" href="<?= e(url('reservas')) ?>">Limpar</a>
    </div>
</form>

<section class="card">
    <div class="card__cabecalho">
        <div>
            <h2>Reservas</h2>
            <p><?= numero($paginacao['total']) ?> reserva(s) encontrada(s).</p>
        </div>
    </div>

    <?php if ($reservas === []): ?>
        <div class="estado-vazio">
            <div class="estado-vazio__icone"><?= icone('reserva', 26) ?></div>
            <h3>Nenhuma reserva encontrada</h3>
            <p>Registre a primeira venda da agência para acompanhar pagamentos e embarques.</p>
            <a class="botao botao--primario" href="<?= e(url('reservas/nova')) ?>"><?= icone('plus', 16) ?> Nova reserva</a>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Reserva</th>
                        <th>Cliente</th>
                        <th>Viagem</th>
                        <th class="num">Viajantes</th>
                        <th class="num">Total</th>
                        <th class="num">Saldo</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td>
                                <span class="codigo"><?= e($reserva['codigo']) ?></span>
                                <small class="texto-muted" style="display:block;"><?= data_br($reserva['criado_em']) ?></small>
                            </td>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong><?= e($reserva['cliente']) ?></strong>
                                    <small><?= e(formatar_telefone($reserva['telefone'] ?? '')) ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="celula-principal__texto">
                                    <strong><?= e($reserva['pacote']) ?></strong>
                                    <small><?= e($reserva['destino']) ?> · <?= data_br($reserva['data_partida'] ?? '') ?></small>
                                </div>
                            </td>
                            <td class="num"><?= numero($reserva['qtd_viajantes']) ?></td>
                            <td class="num valor"><?= e(moeda($reserva['valor_total'])) ?></td>
                            <td class="num">
                                <?php $emAberto = (float) ($reserva['em_aberto'] ?? 0); ?>
                                <span class="<?= $emAberto > 0 ? 'valor valor--aberto' : 'valor valor--positivo' ?>">
                                    <?= $emAberto > 0 ? e(moeda($emAberto)) : 'Quitado' ?>
                                </span>
                            </td>
                            <td><?= selo_status((string) $reserva['status']) ?></td>
                            <td>
                                <div class="acoes-tabela">
                                    <a class="acao-icone" href="<?= e(url('reservas/' . $reserva['id'])) ?>" title="Ver reserva" aria-label="Ver reserva"><?= icone('visualizar', 16) ?></a>
                                    <a class="acao-icone" href="<?= e(url('reservas/' . $reserva['id'] . '/editar')) ?>" title="Editar" aria-label="Editar"><?= icone('editar', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginacao_html($paginacao, 'reservas', $parametrosFiltro) ?>
    <?php endif; ?>
</section>
