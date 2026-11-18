<?php
/**
 * Horizonte Viagens — Classe base para serviços de domínio.
 *
 * Fornece acesso centralizado ao banco e utilidades compartilhadas por todos
 * os serviços de negócio.
 */

declare(strict_types=1);

abstract class BaseService
{
    protected Database $banco;

    public function __construct()
    {
        $this->banco = banco();
    }

    /** Retorna o rótulo amigável de um status de reserva. */
    protected function rotuloStatusReserva(string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'confirmada' => 'Confirmada',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /** Retorna o rótulo amigável de um status de pagamento. */
    protected function rotuloStatusPagamento(string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'pago' => 'Pago',
            'atrasado' => 'Atrasado',
            'estornado' => 'Estornado',
            'cancelado' => 'Cancelado',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /** Retorna o rótulo amigável de um status de viagem. */
    protected function rotuloStatusViagem(string $status): string
    {
        return match ($status) {
            'planejada' => 'Planejada',
            'confirmada' => 'Confirmada',
            'em_andamento' => 'Em andamento',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /** Converte um valor monetário do formato brasileiro para decimal. */
    protected function paraDecimal(mixed $valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }
        $texto = trim((string) $valor);
        if ($texto === '') {
            return 0.0;
        }
        $texto = preg_replace('/[^\d,.\-]/', '', $texto);
        if (str_contains($texto, ',')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif (substr_count($texto, '.') > 1) {
            $texto = str_replace('.', '', $texto);
        }
        return round((float) $texto, 2);
    }

    /** Converte uma data dd/mm/aaaa para ISO (aaaa-mm-dd). */
    protected function paraIso(?string $data): ?string
    {
        if ($data === null || trim($data) === '') {
            return null;
        }
        $data = trim($data);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data;
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $partes)) {
            return $partes[3] . '-' . $partes[2] . '-' . $partes[1];
        }
        return null;
    }

    /** Monta a cláusula LIMIT ... OFFSET ... com valores inteiros seguros. */
    protected function limiteOffset(int $porPagina, int $offset): string
    {
        return 'LIMIT ' . max(1, $porPagina) . ' OFFSET ' . max(0, $offset);
    }

    /**
     * Saldo em aberto de uma reserva: valor contratado menos o que já foi pago.
     *
     * É a definição usada em todas as telas e no relatório, para que uma
     * reserva parcialmente paga nunca apareça como quitada.
     */
    protected function saldoEmAberto(float|int|string|null $valorTotal, float|int|string|null $totalPago): float
    {
        return max(0.0, round((float) $valorTotal - (float) $totalPago, 2));
    }
}
