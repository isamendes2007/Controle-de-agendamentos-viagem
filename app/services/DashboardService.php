<?php
/**
 * Horizonte Viagens — Serviço de domínio para o painel gerencial.
 */

declare(strict_types=1);

class DashboardService extends BaseService
{
    public function indicadores(): array
    {
        $hoje = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        $receitaMes = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE status = 'pago' AND data_pagamento >= :inicio",
            ['inicio' => $inicioMes]
        )['total'] ?? 0);

        $receitaMesAnterior = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE status = 'pago' AND data_pagamento >= :inicio_anterior
             AND data_pagamento < :inicio",
            [
                'inicio_anterior' => date('Y-m-d', strtotime($inicioMes . ' -1 month')),
                'inicio' => $inicioMes,
            ]
        )['total'] ?? 0);

        $variacaoReceita = $receitaMesAnterior > 0
            ? round((($receitaMes - $receitaMesAnterior) / $receitaMesAnterior) * 100, 1)
            : 0;

        $reservasMes = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas WHERE criado_em >= :inicio",
            ['inicio' => $inicioMes]
        )['total'] ?? 0);

        $reservasAguardando = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas WHERE status = 'pendente' AND criado_em >= :inicio",
            ['inicio' => $inicioMes]
        )['total'] ?? 0);

        $clientesAtivos = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM clientes WHERE ativo = 1"
        )['total'] ?? 0);

        $clientesTotal = (int) ($this->banco->buscarUm(
            'SELECT COUNT(*) AS total FROM clientes'
        )['total'] ?? 0);

        $viagensOperacao = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM viagens WHERE status IN ('confirmada', 'em_andamento')"
        )['total'] ?? 0);

        $aReceber = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(CASE WHEN status IN ('pendente', 'atrasado') THEN valor ELSE 0 END), 0) AS total FROM pagamentos"
        )['total'] ?? 0);

        $emAtraso = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos WHERE status = 'atrasado'"
        )['total'] ?? 0);

        $totalPagamentos = (int) ($this->banco->buscarUm('SELECT COUNT(*) AS total FROM pagamentos')['total'] ?? 0);

        $variacaoReceitaLabel = $variacaoReceita >= 0 ? '+' . $variacaoReceita . '%' : $variacaoReceita . '%';

        return [
            'receita_mes' => $receitaMes,
            'receita_mes_anterior' => $receitaMesAnterior,
            'variacao_receita' => $variacaoReceita,
            'variacao_receita_label' => $variacaoReceitaLabel,
            'reservas_mes' => $reservasMes,
            'reservas_aguardando' => $reservasAguardando,
            'clientes_ativos' => $clientesAtivos,
            'clientes_total' => $clientesTotal,
            'viagens_operacao' => $viagensOperacao,
            'a_receber' => $aReceber,
            'em_atraso' => $emAtraso,
            'total_pagamentos' => $totalPagamentos,
        ];
    }

    public function receitaUltimosSeisMeses(): array
    {
        return $this->banco->buscarTodos(
            "SELECT SUBSTR(data_pagamento, 1, 7) AS mes,
                    COALESCE(SUM(valor), 0) AS receita
             FROM pagamentos
             WHERE status = 'pago' AND data_pagamento >= :inicio
             GROUP BY SUBSTR(data_pagamento, 1, 7)
             ORDER BY mes ASC",
            ['inicio' => date('Y-m-d', strtotime('-5 months', strtotime(date('Y-m-01'))))]
        );
    }

    public function reservasPorStatus(): array
    {
        return $this->banco->buscarTodos(
            "SELECT status, COUNT(*) AS quantidade FROM reservas GROUP BY status"
        );
    }

    public function proximasViagens(int $limite = 5): array
    {
        return $this->banco->buscarTodos(
            "SELECT v.*, p.nome AS pacote_nome, d.nome AS destino_nome,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS ocupadas
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE v.status IN ('planejada', 'confirmada') AND v.data_partida >= CURRENT_DATE
             ORDER BY v.data_partida ASC
             {$this->limiteOffset($limite, 0)}",
            []
        );
    }

    public function cobrançasAtrasadas(int $limite = 5): array
    {
        return $this->banco->buscarTodos(
            "SELECT p.*, r.codigo AS reserva_codigo, c.nome AS cliente_nome,
                    v.data_partida, d.nome AS destino_nome
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE p.status = 'atrasado' AND r.status <> 'cancelada'
             ORDER BY p.data_vencimento ASC
             {$this->limiteOffset($limite, 0)}",
            []
        );
    }

    public function contagemAtrasados(): int
    {
        return (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             WHERE p.status = 'atrasado' AND r.status <> 'cancelada'"
        )['total'] ?? 0);
    }

    public function ocupacaoGeral(): array
    {
        $viajantes = (int) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(r.qtd_viajantes), 0) AS total FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             WHERE r.status <> 'cancelada' AND v.status IN ('confirmada', 'em_andamento')"
        )['total'] ?? 0);

        $vagas = (int) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(vagas_totais), 0) AS total FROM viagens
             WHERE status IN ('confirmada', 'em_andamento')"
        )['total'] ?? 0);

        return [
            'viajantes' => $viajantes,
            'vagas' => $vagas,
            'percentual' => $vagas > 0 ? round(($viajantes / $vagas) * 100, 1) : 0,
        ];
    }
}
