<?php
/**
 * Painel gerencial.
 *
 * Recebe: $indicadores, $receitaMeses, $reservasPorStatus, $proximasViagens,
 * $cobrancasAtrasadas, $ocupacao e $saudacao.
 */
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('relatorios')) . '">' . icone('relatorio', 16) . ' Relatórios</a>';
$acoesPagina .= '<a class="botao botao--primario" href="' . e(url('reservas/nova')) . '">' . icone('plus', 16) . ' Nova reserva</a>';

$totalReservasStatus = array_sum($reservasPorStatus);
$coresStatus = [
    'pendente'   => '#d7a13b',
    'confirmada' => '#278c67',
    'concluida'  => '#3b72b8',
    'cancelada'  => '#c94f4b',
];
$paradasRosca = [];
$acumulado = 0.0;
if ($totalReservasStatus > 0) {
    foreach ($reservasPorStatus as $status => $quantidade) {
        if ($quantidade <= 0) {
            continue;
        }
        $acumulado += ($quantidade / $totalReservasStatus) * 100;
        $paradasRosca[] = ($coresStatus[$status] ?? '#9aa8a4') . ' ' . number_format($acumulado, 2, '.', '') . '%';
    }
}
$fundoRosca = $paradasRosca === [] ? null : 'conic-gradient(' . implode(', ', $paradasRosca) . ')';
$variacao = (float) ($indicadores['variacao_receita'] ?? 0);
?>
<section class="hero-painel">
    <div class="hero-painel__texto">
        <span class="hero-painel__saudacao"><?= e($saudacao) ?>, <?= e(explode(' ', trim((string) (usuario_atual()['nome'] ?? 'usuário')))[0] ?? 'usuário') ?></span>
        <h1>Resumo da operação</h1>
        <p>Receita recebida no mês: <strong><?= e(moeda($indicadores['receita_mes'] ?? 0)) ?></strong> · <?= e($indicadores['reservas_aguardando'] ?? 0) ?> reserva(s) aguardando confirmação.</p>
    </div>
    <div class="hero-painel__acoes">
        <a class="botao botao--vidro" href="<?= e(url('viagens/nova')) ?>"><?= icone('plus', 15) ?> Programar viagem</a>
        <a class="botao botao--vidro" href="<?= e(url('clientes/novo')) ?>"><?= icone('usuarios', 15) ?> Novo cliente</a>
    </div>
</section>

<div class="grade grade--4 mb-2">
    <article class="indicador">
        <div class="indicador__topo">
            <span class="indicador__rotulo">Receita no mês</span>
            <span class="indicador__icone"><?= icone('dinheiro', 18) ?></span>
        </div>
        <div class="indicador__valor"><?= e(moeda($indicadores['receita_mes'] ?? 0)) ?></div>
        <div class="indicador__rodape">
            <span class="variacao <?= $variacao >= 0 ? 'variacao--positiva' : 'variacao--negativa' ?>">
                <?= e(($variacao >= 0 ? '+' : '') . number_format($variacao, 1, ',', '.') . '%') ?>
            </span>
            em relação ao mês anterior
        </div>
    </article>

    <article class="indicador">
        <div class="indicador__topo">
            <span class="indicador__rotulo">Reservas no mês</span>
            <span class="indicador__icone"><?= icone('reserva', 18) ?></span>
        </div>
        <div class="indicador__valor"><?= numero($indicadores['reservas_mes'] ?? 0) ?></div>
        <div class="indicador__rodape"><?= numero($indicadores['reservas_aguardando'] ?? 0) ?> aguardando confirmação</div>
    </article>

    <article class="indicador">
        <div class="indicador__topo">
            <span class="indicador__rotulo">Clientes ativos</span>
            <span class="indicador__icone"><?= icone('usuarios', 18) ?></span>
        </div>
        <div class="indicador__valor"><?= numero($indicadores['clientes_ativos'] ?? 0) ?></div>
        <div class="indicador__rodape">de <?= numero($indicadores['clientes_total'] ?? 0) ?> cadastrados</div>
    </article>

    <article class="indicador">
        <div class="indicador__topo">
            <span class="indicador__rotulo">A receber</span>
            <span class="indicador__icone"><?= icone('relogio', 18) ?></span>
        </div>
        <div class="indicador__valor"><?= e(moeda($indicadores['a_receber'] ?? 0)) ?></div>
        <div class="indicador__rodape"><?= e(moeda($indicadores['em_atraso'] ?? 0)) ?> em atraso</div>
    </article>
</div>

<div class="grade grade--dash">
    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Receita recebida</h2>
                <p>Últimos seis meses.</p>
            </div>
        </div>
        <div class="card__corpo">
            <?= grafico_barras($receitaMeses) ?>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Reservas por status</h2>
                <p>Distribuição atual da carteira.</p>
            </div>
        </div>
        <div class="card__corpo">
            <?php if ($totalReservasStatus === 0): ?>
                <p class="texto-muted">Nenhuma reserva registrada.</p>
            <?php else: ?>
                <div class="linha-flex" style="gap:22px; align-items:center;">
                    <div class="rosca" style="<?= $fundoRosca !== null ? 'background:' . e($fundoRosca) : '' ?>">
                        <div class="rosca__centro">
                            <span class="rosca__valor"><?= numero($totalReservasStatus) ?></span>
                            <span class="rosca__legenda">reservas</span>
                        </div>
                    </div>
                    <div class="legenda-lista">
                        <?php foreach (opcoes_status_reserva() as $chave => $rotulo): ?>
                            <?php $quantidade = (int) ($reservasPorStatus[$chave] ?? 0); ?>
                            <div class="legenda-lista__item">
                                <span class="legenda-lista__cor" style="background:<?= e($coresStatus[$chave] ?? '#9aa8a4') ?>"></span>
                                <span><?= e($rotulo) ?></span>
                                <span class="legenda-lista__valor"><?= numero($quantidade) ?></span>
                                <span class="texto-muted">(<?= numero(percentual($quantidade, $totalReservasStatus, 1), 1) ?>%)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Próximas viagens</h2>
                <p>Partidas planejadas ou confirmadas.</p>
            </div>
            <a class="card__link" href="<?= e(url('viagens')) ?>">Ver todas</a>
        </div>
        <div class="card__corpo">
            <?php if ($proximasViagens === []): ?>
                <p class="texto-muted">Nenhuma partida programada.</p>
            <?php else: ?>
                <div class="lista-viagens">
                    <?php foreach ($proximasViagens as $viagem): ?>
                        <a class="viagem-linha" href="<?= e(url('viagens/' . $viagem['id'])) ?>">
                            <span class="viagem-linha__data">
                                <span class="viagem-linha__dia"><?= e(date('d', (int) strtotime((string) $viagem['data_partida']))) ?></span>
                                <span class="viagem-linha__mes"><?= e(date('M', (int) strtotime((string) $viagem['data_partida']))) ?></span>
                            </span>
                            <span class="viagem-linha__texto">
                                <strong><?= e($viagem['destino_nome']) ?></strong>
                                <span class="texto-muted"><?= e($viagem['pacote_nome']) ?></span>
                            </span>
                            <span class="viagem-linha__ocupacao">
                                <?= numero($viagem['ocupadas']) ?>/<?= numero($viagem['vagas_totais']) ?> viajantes
                            </span>
                            <?= selo_status((string) $viagem['status']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Cobranças em atraso</h2>
                <p>Pendências que exigem contato com o cliente.</p>
            </div>
            <a class="card__link" href="<?= e(url('pagamentos', ['status' => 'atrasado'])) ?>">Ver financeiro</a>
        </div>
        <div class="card__corpo">
            <?php if ($cobrancasAtrasadas === []): ?>
                <p class="texto-muted">Nenhuma cobrança vencida. Tudo em dia.</p>
            <?php else: ?>
                <div class="lista-atrasos">
                    <?php foreach ($cobrancasAtrasadas as $cobranca): ?>
                        <div class="atraso-item">
                            <span class="atraso-item__icone"><?= icone('alerta', 18) ?></span>
                            <span class="atraso-item__texto">
                                <strong><?= e($cobranca['cliente_nome']) ?></strong>
                                <span class="texto-muted"><?= e($cobranca['destino_nome']) ?> · venceu em <?= data_br($cobranca['data_vencimento']) ?></span>
                            </span>
                            <span class="atraso-item__valor"><?= e(moeda($cobranca['valor'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card__cabecalho">
            <div>
                <h2>Ocupação das viagens</h2>
                <p>Viagens confirmadas e em andamento.</p>
            </div>
        </div>
        <div class="card__corpo">
            <div class="barra-linear__topo mb-1">
                <span><?= numero($ocupacao['viajantes'] ?? 0) ?> viajantes de <?= numero($ocupacao['vagas'] ?? 0) ?> vagas</span>
                <strong><?= numero($ocupacao['percentual'] ?? 0, 1) ?>%</strong>
            </div>
            <div class="barra-linear__trilho">
                <div class="barra-linear__preenchimento" style="width:<?= (float) ($ocupacao['percentual'] ?? 0) ?>%"></div>
            </div>
            <div class="divisor"></div>
            <div class="financeiro-resumo">
                <div class="financeiro-linha"><span>Viagens em operação</span><strong><?= numero($indicadores['viagens_operacao'] ?? 0) ?></strong></div>
                <div class="financeiro-linha"><span>Lançamentos financeiros</span><strong><?= numero($indicadores['total_pagamentos'] ?? 0) ?></strong></div>
                <div class="financeiro-linha financeiro-linha--destaque"><span>Valor contratado no mês</span><strong><?= e(moeda($indicadores['receita_mes'] ?? 0)) ?></strong></div>
            </div>
        </div>
    </section>
</div>
