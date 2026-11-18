<?php
/**
 * Gestão de reservas de viagem.
 */

declare(strict_types=1);

class ReservasController extends Controller
{
    private ReservaService $service;
    private PagamentoService $pagamentoService;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ReservaService();
        $this->pagamentoService = new PagamentoService();
    }

    public function index(): void
    {
        $filtros = [
            'busca' => trim((string) get('busca')),
            'status' => (string) get('status', ''),
            'por_pagina' => 10,
            'pagina' => (int) get('pagina', 1),
        ];

        $resultado = $this->service->listar($filtros);

        // Resumo rápido para cards do topo
        $totalReservas = (int) $resultado['total'];
        $reservasPendentes = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas WHERE status = 'pendente'"
        )['total'] ?? 0);
        $reservasConfirmadas = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas WHERE status = 'confirmada'"
        )['total'] ?? 0);
        $reservasCanceladas = (int) ($this->banco->buscarUm(
            "SELECT COUNT(*) AS total FROM reservas WHERE status = 'cancelada'"
        )['total'] ?? 0);

        $resumo = [
            'total' => $totalReservas,
            'pendentes' => $reservasPendentes,
            'confirmadas' => $reservasConfirmadas,
            'canceladas' => $reservasCanceladas,
        ];

        // Mapeia campos para nomes esperados pela view
        $reservasMapeadas = [];
        foreach ($resultado['registros'] as $r) {
            $reservasMapeadas[] = array_merge($r, [
                'cliente' => $r['cliente_nome'] ?? '',
                'destino' => $r['destino_nome'] ?? '',
                'pacote' => $r['pacote_nome'] ?? '',
            ]);
        }

        $this->visao('reservas/index', [
            'tituloPagina' => 'Reservas',
            'subtituloPagina' => 'Acompanhamento de reservas e status de pagamento.',
            'reservas' => $reservasMapeadas,
            'busca' => $resultado['busca'],
            'status' => $resultado['status'],
            'paginacao' => $resultado,
            'parametrosFiltro' => ['busca' => $resultado['busca'], 'status' => $resultado['status']],
            'statusOpcoes' => $this->service->statusOpcoes(),
            'resumo' => $resumo,
        ]);
    }

    public function nova(): void
    {
        $this->exibirFormulario(null, []);
    }

    public function criar(): void
    {
        exigir_csrf();
        [$dados, $erros] = $this->service->validar($_POST);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario(null, $erros, $_POST);
            return;
        }

        $id = $this->service->criar($dados);
        $this->sucesso('Reserva criada com sucesso.', 'reservas/' . $id);
    }

    public function mostrar(string $id): void
    {
        $reservaId = $this->idDaRota($id);
        $reserva = $this->service->buscarPorId($reservaId);
        $pagamentos = $this->service->pagamentos($reservaId);
        $resumo = $this->service->resumoFinanceiro($reservaId);

        // Mapeia campos para nomes esperados pela view
        $reservaParaView = array_merge($reserva, [
            'cliente' => $reserva['cliente_nome'] ?? '',
            'destino' => $reserva['destino_nome'] ?? '',
            'pacote' => $reserva['pacote_nome'] ?? '',
            'pacote_id' => $reserva['pacote_id'] ?? null,
            'registrado_por' => $reserva['usuario_nome'] ?? '-',
        ]);

        $valorTotal = (float) $resumo['valor_total'];
        $totalPago = (float) $resumo['total_pago'];
        $percentualPago = $valorTotal > 0 ? round(($totalPago / $valorTotal) * 100, 1) : 0;
        $saldo = (float) $resumo['em_aberto'];

        $this->visao('reservas/mostrar', [
            'tituloPagina' => 'Reserva ' . $reserva['codigo'],
            'subtituloPagina' => 'Detalhes, pagamentos e status da reserva.',
            'reserva' => $reservaParaView,
            'pagamentos' => $pagamentos,
            'resumo' => $resumo,
            'statusOpcoes' => $this->service->statusOpcoes(),
            'podeConcluir' => $this->service->podeConcluir($reservaId),
            'percentualPago' => $percentualPago,
            'saldo' => $saldo,
        ]);
    }

    public function editar(string $id): void
    {
        $reservaId = $this->idDaRota($id);
        $reserva = $this->service->buscarPorId($reservaId);
        $this->exibirFormulario($reserva, [], $reserva);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $reservaId = $this->idDaRota($id);
        $reserva = $this->service->buscarPorId($reservaId);

        [$dados, $erros] = $this->service->validar($_POST, $reservaId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($reserva, $erros, array_merge($reserva, $_POST));
            return;
        }

        $this->service->atualizar($reservaId, $dados);
        $this->sucesso('Reserva atualizada com sucesso.', 'reservas/' . $reservaId);
    }

    public function atualizarStatus(string $id): void
    {
        exigir_csrf();
        $reservaId = $this->idDaRota($id);
        $status = (string) post('status');

        try {
            $this->service->atualizarStatus($reservaId, $status);
            flash('sucesso', 'Status da reserva atualizado com sucesso.');
        } catch (InvalidArgumentException $e) {
            flash('erro', $e->getMessage());
        }

        redirecionar('reservas/' . $reservaId);
    }

    private function exibirFormulario(?array $reserva, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $reserva ?? [
            'cliente_id' => '', 'viagem_id' => '', 'qtd_viajantes' => 1,
            'valor_unitario' => '', 'desconto' => 0, 'status' => 'pendente', 'observacoes' => '',
        ];

        $clientes = $this->banco->buscarTodos('SELECT id, nome, cpf, ativo FROM clientes WHERE ativo = 1 ORDER BY nome ASC');
        $viagens = $this->banco->buscarTodos(
            "SELECT v.id, p.nome AS pacote, d.nome AS destino, v.data_partida, v.vagas_totais, v.preco,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS ocupadas
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE v.status IN ('planejada', 'confirmada')
             ORDER BY v.data_partida ASC"
        );

        $this->visao('reservas/formulario', [
            'tituloPagina' => $reserva === null ? 'Nova reserva' : 'Editar reserva',
            'subtituloPagina' => $reserva === null
                ? 'Cadastre uma nova reserva para um cliente.'
                : 'Atualize os dados da reserva.',
            'reserva' => $reserva,
            'valores' => $valores,
            'erros' => $erros,
            'clientes' => $clientes,
            'viagens' => $viagens,
            'statusOpcoes' => $this->service->statusOpcoes(),
        ]);
    }
}
