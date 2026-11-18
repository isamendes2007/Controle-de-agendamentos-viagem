<?php
/**
 * Gestão de agendamentos de viagens.
 *
 * Permite criar solicitações de agendamento (por cliente) e que o gestor
 * acompanhe, aprove ou rejeite cada solicitação.
 */

declare(strict_types=1);

class AgendamentosController extends Controller
{
    private AgendamentoService $service;
    private ReservaService $reservaService;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AgendamentoService();
        $this->reservaService = new ReservaService();
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

        // Contagem por status para cards do topo
        $porStatus = $this->service->contagemPorStatus();
        $contagem = [];
        foreach ($porStatus as $item) {
            $contagem[$item['status']] = (int) $item['quantidade'];
        }

        $this->visao('agendamentos/index', [
            'tituloPagina' => 'Agendamentos',
            'subtituloPagina' => 'Solicitações de viagem dos clientes e acompanhamento do gestor.',
            'agendamentos' => $resultado['registros'],
            'busca' => $resultado['busca'],
            'status' => $resultado['status'],
            'paginacao' => $resultado,
            'parametrosFiltro' => ['busca' => $resultado['busca'], 'status' => $resultado['status']],
            'statusOpcoes' => $this->service->statusOpcoes(),
            'contagem' => $contagem,
            'pendentes' => $contagem['solicitado'] ?? 0,
        ]);
    }

    public function novo(): void
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
        $this->sucesso('Agendamento de viagem criado com sucesso.', 'agendamentos/' . $id);
    }

    public function mostrar(string $id): void
    {
        $agendamentoId = $this->idDaRota($id);
        $agendamento = $this->service->buscarPorId($agendamentoId);

        $this->visao('agendamentos/mostrar', [
            'tituloPagina' => 'Agendamento ' . $agendamento['codigo'],
            'subtituloPagina' => 'Acompanhamento da solicitação de viagem.',
            'agendamento' => $agendamento,
            'statusOpcoes' => $this->service->statusOpcoes(),
        ]);
    }

    public function editar(string $id): void
    {
        $agendamentoId = $this->idDaRota($id);
        $agendamento = $this->service->buscarPorId($agendamentoId);
        $this->exibirFormulario($agendamento, [], $agendamento);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $agendamentoId = $this->idDaRota($id);
        $agendamento = $this->service->buscarPorId($agendamentoId);

        [$dados, $erros] = $this->service->validar($_POST, $agendamentoId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($agendamento, $erros, array_merge($agendamento, $_POST));
            return;
        }

        $this->service->atualizar($agendamentoId, $dados);
        $this->sucesso('Agendamento atualizado com sucesso.', 'agendamentos/' . $agendamentoId);
    }

    public function atualizarStatus(string $id): void
    {
        exigir_csrf();
        $agendamentoId = $this->idDaRota($id);
        $status = (string) post('status');

        try {
            $this->service->atualizarStatus($agendamentoId, $status);
            flash('sucesso', 'Status do agendamento atualizado com sucesso.');
        } catch (InvalidArgumentException $e) {
            flash('erro', $e->getMessage());
        }

        redirecionar('agendamentos/' . $agendamentoId);
    }

    public function excluir(string $id): void
    {
        exigir_csrf();
        $agendamentoId = $this->idDaRota($id);

        try {
            $this->service->excluir($agendamentoId);
            flash('sucesso', 'Agendamento excluído com sucesso.');
            redirecionar('agendamentos');
        } catch (Throwable $e) {
            flash('erro', 'Não foi possível excluir este agendamento.');
            redirecionar('agendamentos/' . $agendamentoId);
        }
    }

    /** Converte um agendamento aprovado em uma reserva. */
    public function converterReserva(string $id): void
    {
        exigir_csrf();
        $agendamentoId = $this->idDaRota($id);
        $agendamento = $this->service->buscarPorId($agendamentoId);

        if ($agendamento['status'] !== 'aprovado') {
            flash('erro', 'Somente agendamentos aprovados podem ser convertidos em reserva.');
            redirecionar('agendamentos/' . $agendamentoId);
        }

        // Verifica se já existe reserva para este agendamento (via código ou cliente+viagem)
        $existe = $this->banco->buscarUm(
            'SELECT id FROM reservas WHERE cliente_id = :cliente_id AND viagem_id = :viagem_id AND status <> \'cancelada\' LIMIT 1',
            ['cliente_id' => $agendamento['cliente_id'], 'viagem_id' => $agendamento['viagem_id']]
        );
        if ($existe !== null) {
            flash('atencao', 'Já existe uma reserva ativa para este cliente nesta viagem.');
            redirecionar('agendamentos/' . $agendamentoId);
        }

        try {
            $reservaId = $this->reservaService->criar([
                'cliente_id' => (int) $agendamento['cliente_id'],
                'viagem_id' => (int) $agendamento['viagem_id'],
                'qtd_viajantes' => (int) $agendamento['qtd_viajantes'],
                'valor_unitario' => (float) $agendamento['valor_unitario'],
                'desconto' => 0.0,
                'status' => 'confirmada',
                'observacoes' => trim('Convertido do agendamento ' . $agendamento['codigo'] . '. ' . ($agendamento['observacoes'] ?? '')),
            ]);

            $this->service->atualizarStatus($agendamentoId, 'aprovado');
            flash('sucesso', 'Reserva criada com sucesso a partir do agendamento.');
            redirecionar('reservas/' . $reservaId);
        } catch (Throwable $e) {
            flash('erro', 'Não foi possível criar a reserva: ' . $e->getMessage());
            redirecionar('agendamentos/' . $agendamentoId);
        }
    }

    private function exibirFormulario(?array $agendamento, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $agendamento ?? [
            'cliente_id' => '', 'viagem_id' => '', 'qtd_viajantes' => 1,
            'valor_unitario' => '', 'status' => 'solicitado', 'observacoes' => '',
        ];

        $clientes = $this->banco->buscarTodos('SELECT id, nome, cpf FROM clientes WHERE ativo = 1 ORDER BY nome ASC');
        $viagens = $this->banco->buscarTodos(
            "SELECT v.id, p.nome AS pacote_nome, d.nome AS destino_nome, v.data_partida, v.vagas_totais, v.preco,
                    COALESCE((SELECT SUM(r.qtd_viajantes) FROM reservas r WHERE r.viagem_id = v.id AND r.status <> 'cancelada'), 0) AS ocupadas
             FROM viagens v
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE v.status IN ('planejada', 'confirmada')
             ORDER BY v.data_partida ASC"
        );

        $this->visao('agendamentos/formulario', [
            'tituloPagina' => $agendamento === null ? 'Novo agendamento' : 'Editar agendamento',
            'subtituloPagina' => $agendamento === null
                ? 'Solicite uma nova viagem para um cliente.'
                : 'Atualize os dados do agendamento.',
            'agendamento' => $agendamento,
            'valores' => $valores,
            'erros' => $erros,
            'clientes' => $clientes,
            'viagens' => $viagens,
            'statusOpcoes' => $this->service->statusOpcoes(),
        ]);
    }
}
