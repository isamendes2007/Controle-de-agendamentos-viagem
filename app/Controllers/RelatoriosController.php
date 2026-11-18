<?php
/**
 * Relatórios gerenciais e exportações.
 */

declare(strict_types=1);

class RelatoriosController extends Controller
{
    private RelatorioService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new RelatorioService();
    }

    public function index(): void
    {
        $dataInicial = (string) get('inicio', date('Y-m-01'));
        $dataFinal = (string) get('fim', date('Y-m-d'));
        $destino = (string) get('destino_id', '');
        $destinoId = (int) $destino;

        $destinos = $this->banco->buscarTodos('SELECT id, nome, cidade, pais FROM destinos WHERE ativo = 1 ORDER BY nome ASC');

        $indicadores = $this->service->indicadores($dataInicial, $dataFinal, $destino !== '' ? $destino : null);
        $receitaMensal = $this->service->receitaMensal($dataInicial, $dataFinal, $destino !== '' ? $destino : null);
        $topDestinos = $this->service->topDestinos($dataInicial, $dataFinal);
        $desempenho = $this->service->desempenhoViagens($dataInicial, $dataFinal);
        $reservasStatus = $this->service->reservasPorStatus($dataInicial, $dataFinal);
        $ocupacao = $this->service->ocupacaoViagens($dataInicial, $dataFinal);
        $formasPagamento = $this->service->formasPagamento($dataInicial, $dataFinal);

        // Mapeia nomes esperados pela view de relatórios
        $totalStatus = array_sum(array_column($reservasStatus, 'quantidade'));
        $statusReservasMap = [];
        foreach ($reservasStatus as $item) {
            $statusReservasMap[$item['status']] = (int) $item['quantidade'];
        }

        // Cores da rosca de reservas por status (view index)
        $coresStatusRosca = [
            'pendente' => '#d7a13b',
            'confirmada' => '#278c67',
            'concluida' => '#3b72b8',
            'cancelada' => '#c94f4b',
        ];
        $paradasRosca = [];
        $acumulado = 0.0;
        if ($totalStatus > 0) {
            foreach ($statusReservasMap as $status => $quantidade) {
                if ($quantidade <= 0) {
                    continue;
                }
                $acumulado += ($quantidade / $totalStatus) * 100;
                $paradasRosca[] = ($coresStatusRosca[$status] ?? '#9aa8a4') . ' ' . round($acumulado, 2) . '%';
            }
        }

        $topDestinosMapeados = [];
        foreach ($topDestinos as $d) {
            $topDestinosMapeados[] = [
                'destino' => $d['destino'],
                'cidade' => $d['cidade'] ?? '',
                'pais' => $d['pais'] ?? '',
                'reservas' => (int) $d['reservas'],
                'viajantes' => (int) $d['viajantes'],
                'valor' => (float) $d['valor_contratado'],
                'recebido' => (float) $d['recebido'],
            ];
        }

        $desempenhoMapeado = [];
        foreach ($desempenho as $v) {
            $desempenhoMapeado[] = [
                'id' => (int) $v['id'],
                'destino' => $v['destino_nome'],
                'pacote' => $v['pacote_nome'],
                'data_partida' => $v['data_partida'],
                'data_retorno' => $v['data_retorno'],
                'viajantes' => (int) $v['ocupadas'],
                'vagas_totais' => (int) $v['vagas_totais'],
                'receita' => (float) $v['receita_prevista'],
                'status' => $v['status'],
            ];
        }

        $formasMapeadas = [];
        foreach ($formasPagamento as $f) {
            $formasMapeadas[] = [
                'forma_pagamento' => $f['forma'],
                'quantidade' => (int) $f['lancamentos'],
                'total' => (float) $f['total_recebido'],
            ];
        }

        // Meses: mapeia para rotulo/total
        $mesesMapeados = [];
        foreach ($receitaMensal as $m) {
            $mesNum = (int) substr($m['mes'], 5, 2);
            $mesesMapeados[] = [
                'rotulo' => mb_substr(meses_pt()[$mesNum] ?? '', 0, 3) . '/' . substr($m['mes'], 2, 2),
                'total' => (float) $m['receita'],
            ];
        }

        $diasIntervalo = (strtotime($dataFinal) - strtotime($dataInicial)) / 86400;
        $periodoAnterior = [
            date('Y-m-d', strtotime($dataInicial . ' - ' . ($diasIntervalo + 1) . ' days')),
            date('Y-m-d', strtotime($dataInicial . ' - 1 day')),
        ];

        $indicadoresAnterior = $this->service->indicadores($periodoAnterior[0], $periodoAnterior[1], $destino !== '' ? $destino : null);

        $this->visao('relatorios/index', [
            'tituloPagina' => 'Relatórios',
            'subtituloPagina' => 'Indicadores estratégicos para decisões mais seguras.',
            'inicio' => $dataInicial,
            'fim' => $dataFinal,
            'destinoId' => $destinoId,
            'destinos' => $destinos,
            'receita' => (float) $indicadores['receita_recebida'],
            'variacao' => (float) $indicadores['variacao'],
            'periodoAnterior' => $periodoAnterior,
            'totalStatus' => $totalStatus,
            'statusReservas' => $statusReservasMap,
            'topDestinos' => $topDestinosMapeados,
            'desempenhoViagens' => $desempenhoMapeado,
            'formasPagamento' => $formasMapeadas,
            'ocupacao' => (float) $ocupacao['percentual'],
            'viajantesOcupacao' => (int) $ocupacao['viajantes'],
            'vagasOcupacao' => (int) $ocupacao['vagas'],
            'emAberto' => (float) $indicadores['em_aberto'],
            'clientesNovos' => (int) $indicadores['clientes_novos'],
            'receitaAnterior' => (float) $indicadoresAnterior['receita_recebida'],
            'meses' => $mesesMapeados,
            'maxReceita' => max(array_merge([0.01], array_column($mesesMapeados, 'total'))),
            'maxForma' => max(array_merge([0.01], array_column($formasMapeadas, 'total'))),
            'resumo' => [
                'total_reservas' => (int) $indicadores['reservas_periodo'],
                'reservas_ativas' => (int) $indicadores['reservas_ativas'],
                'viajantes' => (int) $indicadores['viajantes'],
                'ticket_medio' => (float) $indicadores['ticket_medio'],
                'valor_contratado' => (float) $indicadores['valor_contratado'],
            ],
            'fundoRosca' => $paradasRosca === [] ? '#edf0ec' : 'conic-gradient(' . implode(', ', $paradasRosca) . ')',
            'indicadores' => $indicadores,
        ]);
    }

    public function exportar(): void
    {
        $dataInicial = (string) get('inicio', date('Y-m-01'));
        $dataFinal = (string) get('fim', date('Y-m-d'));
        $destino = (string) get('destino', '');

        $csv = $this->service->exportarCSV($dataInicial, $dataFinal, $destino !== '' ? $destino : null);

        $nomeArquivo = 'relatorio_reservas_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        echo "\xEF\xBB\xBF" . $csv;
        exit;
    }

    public function imprimir(): void
    {
        $dataInicial = (string) get('inicio', date('Y-m-01'));
        $dataFinal = (string) get('fim', date('Y-m-d'));
        $destino = (string) get('destino', '');
        $destinoId = (int) $destino;

        $destinos = $this->banco->buscarTodos('SELECT id, nome, cidade, pais FROM destinos WHERE ativo = 1 ORDER BY nome ASC');

        $indicadores = $this->service->indicadores($dataInicial, $dataFinal, $destino !== '' ? $destino : null);
        $receitaMensal = $this->service->receitaMensal($dataInicial, $dataFinal, $destino !== '' ? $destino : null);
        $topDestinos = $this->service->topDestinos($dataInicial, $dataFinal);
        $desempenho = $this->service->desempenhoViagens($dataInicial, $dataFinal);
        $reservasStatus = $this->service->reservasPorStatus($dataInicial, $dataFinal);
        $ocupacao = $this->service->ocupacaoViagens($dataInicial, $dataFinal);
        $formasPagamento = $this->service->formasPagamento($dataInicial, $dataFinal);

        // Mapeia nomes esperados pela view de impressão
        $totalStatus = array_sum(array_column($reservasStatus, 'quantidade'));
        $statusReservasMap = [];
        foreach ($reservasStatus as $item) {
            $statusReservasMap[$item['status']] = (int) $item['quantidade'];
        }

        // Top destinos: mapeia chaves para nomes esperados pela view
        $topDestinosMapeados = [];
        foreach ($topDestinos as $d) {
            $topDestinosMapeados[] = [
                'destino' => $d['destino'],
                'cidade' => $d['cidade'] ?? '',
                'pais' => $d['pais'] ?? '',
                'reservas' => (int) $d['reservas'],
                'viajantes' => (int) $d['viajantes'],
                'valor' => (float) $d['valor_contratado'],
                'recebido' => (float) $d['recebido'],
            ];
        }

        // Desempenho: mapeia chaves
        $desempenhoMapeado = [];
        foreach ($desempenho as $v) {
            $desempenhoMapeado[] = [
                'id' => (int) $v['id'],
                'destino' => $v['destino_nome'],
                'pacote' => $v['pacote_nome'],
                'data_partida' => $v['data_partida'],
                'data_retorno' => $v['data_retorno'],
                'viajantes' => (int) $v['ocupadas'],
                'vagas_totais' => (int) $v['vagas_totais'],
                'receita' => (float) $v['receita_prevista'],
                'status' => $v['status'],
            ];
        }

        // Formas de pagamento: mapeia chaves
        $formasMapeadas = [];
        foreach ($formasPagamento as $f) {
            $formasMapeadas[] = [
                'forma_pagamento' => $f['forma'],
                'quantidade' => (int) $f['lancamentos'],
                'total' => (float) $f['total_recebido'],
            ];
        }

        // Meses: mapeia para rotulo/total
        $mesesMapeados = [];
        foreach ($receitaMensal as $m) {
            $mesNum = (int) substr($m['mes'], 5, 2);
            $mesesMapeados[] = [
                'rotulo' => mb_substr(meses_pt()[$mesNum] ?? '', 0, 3) . '/' . substr($m['mes'], 2, 2),
                'total' => (float) $m['receita'],
            ];
        }

        $diasIntervalo = (strtotime($dataFinal) - strtotime($dataInicial)) / 86400;
        $periodoAnterior = [
            date('Y-m-d', strtotime($dataInicial . ' - ' . ($diasIntervalo + 1) . ' days')),
            date('Y-m-d', strtotime($dataInicial . ' - 1 day')),
        ];

        $indicadoresAnterior = $this->service->indicadores($periodoAnterior[0], $periodoAnterior[1], $destino !== '' ? $destino : null);

        $this->visao('relatorios/imprimir', [
            'inicio' => $dataInicial,
            'fim' => $dataFinal,
            'destinoId' => $destinoId,
            'destinos' => $destinos,
            'receita' => (float) $indicadores['receita_recebida'],
            'resumo' => [
                'total_reservas' => (int) $indicadores['reservas_periodo'],
                'reservas_ativas' => (int) $indicadores['reservas_ativas'],
                'valor_contratado' => (float) $indicadores['valor_contratado'],
                'ticket_medio' => (float) $indicadores['ticket_medio'],
                'viajantes' => (int) $indicadores['viajantes'],
            ],
            'emAberto' => (float) $indicadores['em_aberto'],
            'clientesNovos' => (int) $indicadores['clientes_novos'],
            'ocupacao' => (float) $ocupacao['percentual'],
            'meses' => $mesesMapeados,
            'statusReservas' => $statusReservasMap,
            'totalStatus' => $totalStatus,
            'topDestinos' => $topDestinosMapeados,
            'formasPagamento' => $formasMapeadas,
            'desempenhoViagens' => $desempenhoMapeado,
            'periodoAnterior' => $periodoAnterior,
            'receitaAnterior' => (float) $indicadoresAnterior['receita_recebida'],
            'variacao' => (float) $indicadores['variacao'],
            'emitidoEm' => date('d/m/Y \à\s H:i'),
        ], 'impressao');
    }
}
