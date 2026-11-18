<?php
$acoesPagina = '<a class="botao botao--secundario" href="' . e(url('clientes')) . '">' . icone('chevron-left', 16) . ' Voltar</a>';
$acoesPagina .= '<a class="botao botao--secundario" href="' . e(url('clientes/' . $cliente['id'] . '/editar')) . '">' . icone('editar', 16) . ' Editar</a>';
$acoesPagina .= '<a class="botao botao--primario" href="' . e(url('reservas/nova', ['cliente_id' => $cliente['id']])) . '">' . icone('plus', 16) . ' Nova reserva</a>';
?>
<div class="grade grade--detalhe">
    <div class="grade" style="align-content:start;">
        <section class="card">
            <div class="card__corpo">
                <div class="perfil-resumo">
                    <span class="avatar avatar--grande"><?= e(iniciais($cliente['nome'])) ?></span>
                    <div class="perfil-resumo__dados">
                        <h2><?= e($cliente['nome']) ?></h2>
                        <div class="linha-flex" style="gap:7px;">
                            <span class="etiqueta"><?= e(formatar_cpf($cliente['cpf'])) ?></span>
                            <span class="status <?= (int) $cliente['ativo'] === 1 ? 'status--pago' : 'status--neutro' ?>"><?= (int) $cliente['ativo'] === 1 ? 'Cliente ativo' : 'Cliente inativo' ?></span>
                        </div>
                    </div>
                </div>

                <div class="divisor"></div>

                <div class="grade grade--2" style="gap:18px;">
                    <div class="dados-lista">
                        <div class="dado">
                            <span class="dado__icone"><?= icone('mail', 16) ?></span>
                            <div class="dado__texto">
                                <span>E-mail</span>
                                <a href="mailto:<?= e($cliente['email']) ?>"><?= e($cliente['email']) ?></a>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('phone', 16) ?></span>
                            <div class="dado__texto">
                                <span>Telefone</span>
                                <strong><?= e(formatar_telefone($cliente['telefone'])) ?></strong>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('calendar', 16) ?></span>
                            <div class="dado__texto">
                                <span>Nascimento</span>
                                <strong><?= e(data_br($cliente['data_nascimento'])) ?><?= $idade !== null ? ' · ' . $idade . ' anos' : '' ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="dados-lista">
                        <div class="dado">
                            <span class="dado__icone"><?= icone('destinos', 16) ?></span>
                            <div class="dado__texto">
                                <span>Endereço</span>
                                <strong>
                                    <?php if ($cliente['endereco']): ?>
                                        <?= e($cliente['endereco']) ?><?= $cliente['numero'] ? ', ' . e($cliente['numero']) : '' ?><?= $cliente['complemento'] ? ' — ' . e($cliente['complemento']) : '' ?><br>
                                        <?= e($cliente['bairro'] ? $cliente['bairro'] . ' · ' : '') ?><?= e($cliente['cidade'] ?: '') ?><?= $cliente['estado'] ? '/' . e($cliente['estado']) : '' ?>
                                    <?php else: ?>
                                        Não informado
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('clock', 16) ?></span>
                            <div class="dado__texto">
                                <span>Cliente desde</span>
                                <strong><?= e(data_br($cliente['criado_em'])) ?></strong>
                            </div>
                        </div>
                        <div class="dado">
                            <span class="dado__icone"><?= icone('reservas', 16) ?></span>
                            <div class="dado__texto">
                                <span>Última atualização</span>
                                <strong><?= e(data_br($cliente['atualizado_em'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($cliente['observacoes'])): ?>
                    <div class="divisor"></div>
                    <div class="dado">
                        <span class="dado__icone"><?= icone('info', 16) ?></span>
                        <div class="dado__texto">
                            <span>Observações internas</span>
                            <strong style="font-weight:500; color:var(--texto-suave);"><?= nl2br(e($cliente['observacoes'])) ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card__cabecalho">
                <div>
                    <h2>Histórico de reservas</h2>
                    <p>Viagens vinculadas a este cliente.</p>
                </div>
            </div>

            <?php if ($reservas === []): ?>
                <div class="estado-vazio">
                    <div class="estado-vazio__icone"><?= icone('reservas', 26) ?></div>
                    <h3>Nenhuma reserva registrada</h3>
                    <p>Este cliente ainda não possui viagens na agência.</p>
                    <a class="botao botao--primario botao--pequeno" href="<?= e(url('reservas/nova', ['cliente_id' => $cliente['id']])) ?>"><?= icone('plus', 15) ?> Criar primeira reserva</a>
                </div>
            <?php else: ?>
                <div class="tabela-wrapper">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Reserva</th>
                                <th>Viagem</th>
                                <th>Viajantes</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <span class="codigo"><?= e($reserva['codigo']) ?></span>
                                        <small class="texto-muted" style="display:block;"><?= e(data_br($reserva['criado_em'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="celula-principal__texto">
                                            <strong><?= e($reserva['pacote']) ?></strong>
                                            <small><?= e($reserva['destino']) ?> · <?= data_br($reserva['data_partida']) ?></small>
                                        </div>
                                    </td>
                                    <td><?= numero($reserva['qtd_viajantes']) ?></td>
                                    <td class="valor"><?= e(moeda($reserva['valor_total'])) ?></td>
                                    <td class="<?= (float) $reserva['em_aberto'] > 0 ? 'valor valor--aberto' : 'valor valor--positivo' ?>">
                                        <?= (float) $reserva['em_aberto'] > 0 ? e(moeda($reserva['em_aberto'])) : 'Quitado' ?>
                                    </td>
                                    <td><?= selo_status($reserva['status']) ?></td>
                                    <td class="texto-direita">
                                        <a class="acao-icone" href="<?= e(url('reservas/' . $reserva['id'])) ?>" title="Ver reserva" aria-label="Ver reserva"><?= icone('visualizar', 16) ?></a>
                                    </td>
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
                    <h2>Resumo do cliente</h2>
                    <p>Indicadores consolidados.</p>
                </div>
            </div>
            <div class="card__corpo">
                <div class="financeiro-resumo">
                    <div class="financeiro-linha"><span>Reservas realizadas</span><strong><?= numero($resumo['total_reservas']) ?></strong></div>
                    <div class="financeiro-linha"><span>Reservas confirmadas</span><strong><?= numero($resumo['confirmadas']) ?></strong></div>
                    <div class="financeiro-linha"><span>Valor contratado</span><strong><?= e(moeda($resumo['valor_total'])) ?></strong></div>
                    <div class="financeiro-linha financeiro-linha--destaque"><span>Saldo em aberto</span><strong><?= e(moeda($emAberto)) ?></strong></div>
                </div>
                <div class="divisor"></div>
                <div class="formulario__rodape" style="justify-content:stretch; flex-direction:column; align-items:stretch;">
                    <a class="botao botao--secundario botao--largo" href="<?= e(url('clientes/' . $cliente['id'] . '/editar')) ?>"><?= icone('editar', 16) ?> Editar cadastro</a>
                    <form method="post" action="<?= e(url('clientes/' . $cliente['id'] . '/alternar')) ?>" data-confirmar="<?= (int) $cliente['ativo'] === 1 ? 'Deseja desativar este cliente? O histórico será preservado.' : 'Deseja reativar este cliente?' ?>">
                        <?= csrf_campo() ?>
                        <button type="submit" class="botao <?= (int) $cliente['ativo'] === 1 ? 'botao--perigo' : 'botao--secundario' ?> botao--largo">
                            <?= icone((int) $cliente['ativo'] === 1 ? 'eye-off' : 'refresh', 16) ?>
                            <?= (int) $cliente['ativo'] === 1 ? 'Desativar cliente' : 'Reativar cliente' ?>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__body card__corpo">
                <div class="caixa-info">
                    <?= icone('info', 18) ?>
                    <span>Para novas reservas, o cliente precisa estar ativo. Ao desativar, todo o histórico e os pagamentos são mantidos.</span>
                </div>
            </div>
        </section>
    </aside>
</div>
