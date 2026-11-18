<?php
/**
 * Gestão de pagamentos e cobranças.
 */

declare(strict_types=1);

class PagamentosController extends Controller
{
    private PagamentoService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PagamentoService();
    }

    public function index(): void
    {
        $filtros = [
            'busca' => trim((string) get('busca')),
            'status' => (string) get('status', ''),
            'vence_de' => trim((string) get('vence_de')),
            'vence_ate' => trim((string) get('vence_ate')),
            'por_pagina' => 10,
            'pagina' => (int) get('pagina', 1),
        ];

        $resultado = $this->service->listar($filtros);

        // Estatísticas rápidas para os cards do topo
        $recebidoMes = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE status = 'pago' AND data_pagamento >= :inicio_mes",
            ['inicio_mes' => date('Y-m-01')]
        )['total'] ?? 0);

        $totalRecebido = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos WHERE status = 'pago'"
        )['total'] ?? 0);

        $aReceber = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(CASE WHEN status IN ('pendente', 'atrasado') THEN valor ELSE 0 END), 0) AS total FROM pagamentos"
        )['total'] ?? 0);

        $emAtraso = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos WHERE status = 'atrasado'"
        )['total'] ?? 0);

        // Mapeia campos para nomes esperados pela view
        $pagamentosMapeados = [];
        foreach ($resultado['registros'] as $p) {
            $pagamentosMapeados[] = array_merge($p, [
                'cliente' => $p['cliente_nome'] ?? '',
                'destino' => $p['destino_nome'] ?? '',
                'pacote' => $p['destino_nome'] ?? '',
            ]);
        }

        $resumoView = [
            'recebido_mes' => $recebidoMes,
            'total_recebido' => $totalRecebido,
            'pendente' => $aReceber,
            'atrasado' => $emAtraso,
        ];

        $this->visao('pagamentos/index', [
            'tituloPagina' => 'Pagamentos',
            'subtituloPagina' => 'Recebimentos, parcelas e cobranças em atraso.',
            'pagamentos' => $pagamentosMapeados,
            'busca' => $resultado['busca'],
            'status' => $resultado['status'],
            'vence_de' => $filtros['vence_de'],
            'vence_ate' => $filtros['vence_ate'],
            'paginacao' => $resultado,
            'parametrosFiltro' => [
                'busca' => $resultado['busca'],
                'status' => $resultado['status'],
                'vence_de' => $filtros['vence_de'],
                'vence_ate' => $filtros['vence_ate'],
            ],
            'resumo' => $resumoView,
            'total_lancamentos' => $resultado['total'],
            'statusOpcoes' => $this->service->statusOpcoes(),
            'formaPagamentoOpcoes' => $this->service->formaPagamentoOpcoes(),
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

        try {
            $id = $this->service->criar($dados);
            $reservaId = (int) $dados['reserva_id'];
            $this->sucesso('Pagamento registrado com sucesso.', 'reservas/' . $reservaId);
        } catch (DomainException $e) {
            flash('erro', $e->getMessage());
            $this->exibirFormulario(null, ['valor' => $e->getMessage()], $_POST);
        }
    }

    public function editar(string $id): void
    {
        $pagamentoId = $this->idDaRota($id);
        $pagamento = $this->service->buscarPorId($pagamentoId);
        $this->exibirFormulario($pagamento, [], $pagamento);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $pagamentoId = $this->idDaRota($id);
        $pagamento = $this->service->buscarPorId($pagamentoId);

        [$dados, $erros] = $this->service->validar($_POST, $pagamentoId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($pagamento, $erros, array_merge($pagamento, $_POST));
            return;
        }

        $this->service->atualizar($pagamentoId, $dados);
        $this->sucesso('Pagamento atualizado com sucesso.', 'pagamentos/' . $pagamentoId . '/editar');
    }

    public function baixar(string $id): void
    {
        exigir_csrf();
        $pagamentoId = $this->idDaRota($id);
        $usuarioId = (int) ($this->usuario()['id'] ?? 0);

        try {
            $this->service->baixar($pagamentoId, $usuarioId);
            flash('sucesso', 'Pagamento baixado com sucesso.');
        } catch (DomainException $e) {
            flash('erro', $e->getMessage());
        }

        $pagamento = $this->service->buscarPorId($pagamentoId);
        redirecionar('reservas/' . $pagamento['reserva_id']);
    }

    public function estornar(string $id): void
    {
        exigir_csrf();
        $pagamentoId = $this->idDaRota($id);

        try {
            $this->service->estornar($pagamentoId);
            flash('sucesso', 'Pagamento estornado com sucesso.');
        } catch (DomainException $e) {
            flash('erro', $e->getMessage());
        }

        $pagamento = $this->service->buscarPorId($pagamentoId);
        redirecionar('reservas/' . $pagamento['reserva_id']);
    }

    public function excluir(string $id): void
    {
        exigir_csrf();
        $pagamentoId = $this->idDaRota($id);

        try {
            $this->service->excluir($pagamentoId);
            flash('sucesso', 'Pagamento excluído com sucesso.');
        } catch (DomainException $e) {
            flash('erro', $e->getMessage());
            $pagamento = $this->service->buscarPorId($pagamentoId);
            redirecionar('reservas/' . $pagamento['reserva_id']);
            return;
        }

        $pagamento = $this->service->buscarPorId($pagamentoId);
        redirecionar('reservas/' . $pagamento['reserva_id']);
    }

    private function exibirFormulario(?array $pagamento, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $pagamento ?? [
            'reserva_id' => '', 'valor' => '', 'forma_pagamento' => 'pix',
            'status' => 'pendente', 'data_vencimento' => '', 'data_pagamento' => '',
            'referencia' => '', 'observacoes' => '',
        ];

        $reservas = $this->banco->buscarTodos(
            "SELECT r.id, r.codigo, c.nome AS cliente, d.nome AS destino, r.valor_total,
                    COALESCE((SELECT SUM(pg.valor) FROM pagamentos pg WHERE pg.reserva_id = r.id AND pg.status = 'pago'), 0) AS registrado
             FROM reservas r
             INNER JOIN clientes c ON c.id = r.cliente_id
             INNER JOIN viagens v ON v.id = r.viagem_id
             INNER JOIN pacotes p ON p.id = v.pacote_id
             INNER JOIN destinos d ON d.id = p.destino_id
             WHERE r.status <> 'cancelada'
             ORDER BY r.criado_em DESC"
        );

        $saldoDevedor = null;
        if (!empty($valores['reserva_id'])) {
            $saldoDevedor = $this->service->saldoDevedor((int) $valores['reserva_id']);
        }

        $this->visao('pagamentos/formulario', [
            'tituloPagina' => $pagamento === null ? 'Novo pagamento' : 'Editar pagamento',
            'subtituloPagina' => $pagamento === null
                ? 'Registre um novo lançamento financeiro.'
                : 'Atualize os dados do pagamento.',
            'pagamento' => $pagamento,
            'valores' => $valores,
            'erros' => $erros,
            'reservas' => $reservas,
            'saldo_devedor' => $saldoDevedor,
            'statusOpcoes' => $this->service->statusOpcoes(),
            'formaPagamentoOpcoes' => $this->service->formaPagamentoOpcoes(),
        ]);
    }
}
