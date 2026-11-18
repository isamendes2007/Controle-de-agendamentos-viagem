<?php
/**
 * Horizonte Viagens — Serviço de domínio para relatórios.
 */

declare(strict_types=1);

class RelatorioService extends BaseService
{
    public function indicadores(string $dataInicio, string $dataFim, ?string $destino = null): array
    {
        $params = ['inicio' => $dataInicio, 'fim' => $dataFim];
        $filtroDestino = '';
        if ($destino !== null) {
            $filtroDestino = 'AND d.id = :destino';
            $params['destino'] = $destino;
        }

        $receitaRecebida = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(p.valor), 0) AS total FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE p.status = 'pago' AND p.data_pagamento BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $diasPeriodo = (int) ((strtotime($dataFim) - strtotime($dataInicio)) / 86400) + 1;
        $fimAnterior = date('Y-m-d', strtotime($dataInicio . ' -1 day'));
        $inicioAnterior = date('Y-m-d', strtotime($fimAnterior . ' -' . max(0, $diasPeriodo - 1) . ' days'));

        $paramsAnterior = $params;
        $paramsAnterior['inicio'] = $inicioAnterior;
        $paramsAnterior['fim'] = $fimAnterior;

        $receitaAnterior = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(p.valor), 0) AS total FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE p.status = 'pago' AND p.data_pagamento BETWEEN :inicio AND :fim {$filtroDestino}",
            $paramsAnterior
        )['total'] ?? 0);

        $reservasPeriodo = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $reservasAtivas = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE r.status <> 'cancelada' AND r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $viajantesPeriodo = (int) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(r.qtd_viajantes), 0) AS total FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE r.status <> 'cancelada' AND r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $valorContratado = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(r.valor_total), 0) AS total FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $pagoNoPeriodo = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) AS total FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}",
            $params
        )['total'] ?? 0);

        $clientesNovos = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM clientes WHERE criado_em BETWEEN :inicio AND :fim",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        )['total'] ?? 0);

        $variacao = $receitaAnterior > 0 ? round((($receitaRecebida - $receitaAnterior) / $receitaAnterior) * 100, 1) : 0;

        return [
            'receita_recebida' => $receitaRecebida,
            'receita_anterior' => $receitaAnterior,
            'variacao' => $variacao,
            'reservas_periodo' => $reservasPeriodo,
            'reservas_ativas' => $reservasAtivas,
            'viajantes' => $viajantesPeriodo,
            'valor_contratado' => $valorContratado,
            'ticket_medio' => $reservasPeriodo > 0 ? round($valorContratado / $reservasPeriodo, 2) : 0,
            'em_aberto' => $this->saldoEmAberto($valorContratado, $pagoNoPeriodo),
            'clientes_novos' => $clientesNovos,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ];
    }

    public function receitaMensal(string $dataInicio, string $dataFim, ?string $destino = null): array
    {
        $params = ['inicio' => $dataInicio, 'fim' => $dataFim];
        $filtroDestino = '';
        if ($destino !== null) {
            $filtroDestino = 'AND d.id = :destino';
            $params['destino'] = $destino;
        }

        return $this->banco->buscarTodos(
            "SELECT SUBSTR(p.data_pagamento, 1, 7) AS mes,
                    COALESCE(SUM(p.valor), 0) AS receita
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             WHERE p.status = 'pago' AND p.data_pagamento BETWEEN :inicio AND :fim {$filtroDestino}
             GROUP BY SUBSTR(p.data_pagamento, 1, 7)
             ORDER BY mes ASC",
            $params
        );
    }

    public function topDestinos(string $dataInicio, string $dataFim): array
    {
        return $this->banco->buscarTodos(
            "SELECT d.nome AS destino, d.pais,
                    COUNT(r.id) AS reservas,
                    SUM(r.qtd_viajantes) AS viajantes,
                    COALESCE(SUM(r.valor_total), 0) AS valor_contratado,
                    COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) AS recebido
             FROM reservas r
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes pac ON pac.id = v.pacote_id
             INNER JOIN destinos d ON d.id = pac.destino_id
             LEFT JOIN pagamentos p ON p.reserva_id = r.id
             WHERE r.criado_em BETWEEN :inicio AND :fim
             GROUP BY d.id
             ORDER BY valor_contratado DESC
             LIMIT 10",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        );
    }

    public function desempenhoViagens(string $dataInicio, string $dataFim): array
    {
        return $this->banco->buscarTodos(
            "SELECT v.id, v.data_partida, v.data_retorno, v.status,
                    p.nome AS pacote_nome, d.nome AS destino_nome,
                    v.vagas_totais,
                    COALESCE(SUM(CASE WHEN r.status <> 'cancelada' THEN r.qtd_viajantes ELSE 0 END), 0) AS ocupadas,
                    COALESCE(SUM(CASE WHEN r.status <> 'cancelada' THEN r.valor_total ELSE 0 END), 0) AS receita_prevista
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             LEFT JOIN reservas r ON r.viagem_id = v.id
             WHERE v.data_partida BETWEEN :inicio AND :fim
             GROUP BY v.id
             ORDER BY v.data_partida ASC",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        );
    }

    public function reservasPorStatus(string $dataInicio, string $dataFim): array
    {
        return $this->banco->buscarTodos(
            "SELECT r.status AS status, COUNT(*) AS quantidade
             FROM reservas r
             WHERE r.criado_em BETWEEN :inicio AND :fim
             GROUP BY r.status",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        );
    }

    public function ocupacaoViagens(string $dataInicio, string $dataFim): array
    {
        $totalViajantes = (int) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(r.qtd_viajantes), 0) AS total FROM reservas r
             WHERE r.criado_em BETWEEN :inicio AND :fim AND r.status <> 'cancelada'",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        )['total'] ?? 0);

        $totalVagas = (int) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(v.vagas_totais), 0) AS total FROM viagens v
             WHERE v.data_partida BETWEEN :inicio AND :fim",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        )['total'] ?? 0);

        return [
            'viajantes' => $totalViajantes,
            'vagas' => $totalVagas,
            'percentual' => $totalVagas > 0 ? round(($totalViajantes / $totalVagas) * 100, 1) : 0,
        ];
    }

    public function formasPagamento(string $dataInicio, string $dataFim): array
    {
        return $this->banco->buscarTodos(
            "SELECT p.forma_pagamento AS forma,
                    COUNT(*) AS lancamentos,
                    COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) AS total_recebido
             FROM pagamentos p
             INNER JOIN reservas r ON r.id = p.reserva_id
             WHERE r.criado_em BETWEEN :inicio AND :fim
             GROUP BY p.forma_pagamento
             ORDER BY total_recebido DESC",
            ['inicio' => $dataInicio, 'fim' => $dataFim]
        );
    }

    public function exportarCSV(string $dataInicio, string $dataFim, ?string $destino = null): string
    {
        $params = ['inicio' => $dataInicio, 'fim' => $dataFim];
        $filtroDestino = '';
        if ($destino !== null) {
            $filtroDestino = 'AND d.id = :destino';
            $params['destino'] = $destino;
        }

        $linhas = $this->banco->buscarTodos(
            "SELECT r.codigo AS codigo, r.criado_em AS data_reserva,
                    c.nome AS cliente, c.cpf, c.telefone,
                    p.nome AS pacote, d.nome AS destino,
                    v.data_partida AS partida, v.data_retorno AS retorno,
                    r.qtd_viajantes AS viajantes,
                    r.valor_total AS valor_total,
                    COALESCE(SUM(CASE WHEN pg.status = 'pago' THEN pg.valor ELSE 0 END), 0) AS total_pago,
                    r.status AS status
             FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             LEFT JOIN pagamentos pg ON pg.reserva_id = r.id
             WHERE r.criado_em BETWEEN :inicio AND :fim {$filtroDestino}
             GROUP BY r.id
             ORDER BY r.criado_em DESC",
            $params
        );

        $cabecalho = ['Código', 'Data da reserva', 'Cliente', 'CPF', 'Telefone', 'Pacote', 'Destino', 'Partida', 'Retorno', 'Viajantes', 'Valor total', 'Total pago', 'Em aberto', 'Status'];
        $linhasCSV = [];
        $linhasCSV[] = implode(';', array_map([$this, 'campoCsv'], $cabecalho));

        foreach ($linhas as $linha) {
            $linha['em_aberto'] = $this->saldoEmAberto($linha['valor_total'], $linha['total_pago']);
            $linhasCSV[] = implode(';', array_map([$this, 'campoCsv'], [
                $linha['codigo'],
                data_br($linha['data_reserva']),
                $linha['cliente'],
                $linha['cpf'],
                $linha['telefone'],
                $linha['pacote'],
                $linha['destino'],
                data_br($linha['partida']),
                data_br($linha['retorno']),
                $linha['viajantes'],
                moeda($linha['valor_total']),
                moeda($linha['total_pago']),
                moeda($linha['em_aberto']),
                ucfirst(str_replace('_', ' ', $linha['status'])),
            ]));
        }

        return implode("\n", $linhasCSV);
    }

    /**
     * Formata um campo CSV: entre aspas apenas quando necessário.
     *
     * O separador é ponto e vírgula (padrão brasileiro), então valores como
     * "R$ 1.234,56" não precisam de aspas. Campos com ; " ou quebra de linha
     * são escapados conforme a RFC 4180.
     */
    private function campoCsv(mixed $valor): string
    {
        $texto = (string) $valor;

        if (strpbrk($texto, ";\"\r\n") === false) {
            return $texto;
        }

        return '"' . str_replace('"', '""', $texto) . '"';
    }
}
