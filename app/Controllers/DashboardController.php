<?php
/**
 * Painel gerencial.
 */

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $service = new DashboardService();

        $indicadores = $service->indicadores();
        $reservasPorStatus = [];
        foreach ($service->reservasPorStatus() as $linha) {
            $reservasPorStatus[(string) $linha['status']] = (int) $linha['quantidade'];
        }

        $receitaMeses = [];
        foreach ($service->receitaUltimosSeisMeses() as $linha) {
            $receitaMeses[mes_abreviado((string) $linha['mes'])] = (float) $linha['receita'];
        }

        $statusApi = $this->banco->buscarTodos(
            'SELECT v.*, p.nome AS pacote_nome, d.nome AS destino_nome,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r
                              WHERE r.viagem_id = v.id AND r.status <> \'cancelada\'), 0) AS ocupadas
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE v.status IN (\'planejada\', \'confirmada\') AND v.data_partida >= CURRENT_DATE
             ORDER BY v.data_partida ASC
             LIMIT 5'
        );

        $cobrancasAtrasadas = $this->banco->buscarTodos(
            "SELECT p.*, r.codigo AS reserva_codigo, c.nome AS cliente_nome, d.nome AS destino_nome
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE p.status = 'atrasado' AND r.status <> 'cancelada'
             ORDER BY p.data_vencimento ASC
             LIMIT 5"
        );

        $this->visao('dashboard/index', [
            'tituloPagina'      => 'Painel',
            'subtituloPagina'   => 'Visão geral da operação da agência.',
            'indicadores'       => $indicadores,
            'receitaMeses'      => $receitaMeses,
            'reservasPorStatus' => $reservasPorStatus,
            'proximasViagens'   => $statusApi,
            'cobrancasAtrasadas' => $cobrancasAtrasadas,
            'ocupacao'          => $service->ocupacaoGeral(),
            'saudacao'          => saudacao(),
        ]);
    }
}
