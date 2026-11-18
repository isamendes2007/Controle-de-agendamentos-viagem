<?php
/**
 * Gestão de viagens (partidas programadas).
 */

declare(strict_types=1);

class ViagensController extends Controller
{
    private ViagemService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ViagemService();
    }

    public function index(): void
    {
        $filtros = [
            'busca' => trim((string) get('busca')),
            'status' => (string) get('status', ''),
            'destino_id' => (int) get('destino_id', 0),
            'de' => trim((string) get('de', '')),
            'ate' => trim((string) get('ate', '')),
            'por_pagina' => 10,
            'pagina' => (int) get('pagina', 1),
        ];

        $resultado = $this->service->listar($filtros);
        $destinos = $this->banco->buscarTodos('SELECT id, nome FROM destinos WHERE ativo = 1 ORDER BY nome ASC');

        // Nomes e indicadores esperados pela view.
        $viagens = [];
        foreach ($resultado['registros'] as $registro) {
            $viagens[] = array_merge($registro, [
                'viajantes' => (int) $registro['ocupadas'],
                'pacote' => $registro['pacote_nome'],
                'destino' => $registro['destino_nome'],
                'receita_prevista' => (float) $registro['receita_prevista'],
            ]);
        }

        $this->visao('viagens/index', [
            'tituloPagina' => 'Viagens',
            'subtituloPagina' => 'Programação e acompanhamento das partidas.',
            'viagens' => $viagens,
            'busca' => $resultado['busca'],
            'status' => $resultado['status'],
            'paginacao' => $resultado,
            'parametrosFiltro' => [
                'busca' => $resultado['busca'],
                'status' => $resultado['status'],
                'destino_id' => $filtros['destino_id'],
                'de' => $filtros['de'],
                'ate' => $filtros['ate'],
            ],
            'statusOpcoes' => $this->service->statusOpcoes(),
            'destinos' => $destinos,
            'destinoId' => $filtros['destino_id'],
            'de' => $filtros['de'],
            'ate' => $filtros['ate'],
            'resumo' => $this->service->resumo(),
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
        $this->sucesso('Viagem programada com sucesso.', 'viagens/' . $id);
    }

    public function mostrar(string $id): void
    {
        $viagemId = $this->idDaRota($id);
        $viagem = $this->service->buscarPorId($viagemId);
        $reservas = $this->service->reservasComDetalhes($viagemId);
        $totais = $this->service->totais($viagemId);

        // Nomes esperados pela view.
        $viagem = array_merge($viagem, [
            'pacote' => $viagem['pacote_nome'],
            'destino' => $viagem['destino_nome'],
        ]);

        // Reservas com o nome do cliente no formato esperado pela view.
        $reservas = array_map(
            static fn (array $reserva): array => array_merge($reserva, ['cliente' => $reserva['cliente_nome']]),
            $reservas
        );

        $ocupacao = percentual((float) $totais['viajantes'], (float) $viagem['vagas_totais']);

        $this->visao('viagens/mostrar', [
            'tituloPagina' => $viagem['pacote'],
            'subtituloPagina' => 'Detalhes da partida e reservas vinculadas.',
            'viagem' => $viagem,
            'reservas' => $reservas,
            'totais' => $totais,
            'ocupacao' => $ocupacao,
            'statusOpcoes' => $this->service->statusOpcoes(),
        ]);
    }

    public function editar(string $id): void
    {
        $viagemId = $this->idDaRota($id);
        $viagem = $this->service->buscarPorId($viagemId);
        $this->exibirFormulario($viagem, [], $viagem);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $viagemId = $this->idDaRota($id);
        $viagem = $this->service->buscarPorId($viagemId);

        [$dados, $erros] = $this->service->validar($_POST, $viagemId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($viagem, $erros, array_merge($viagem, $_POST));
            return;
        }

        $this->service->atualizar($viagemId, $dados);
        $this->sucesso('Dados da viagem atualizados com sucesso.', 'viagens/' . $viagemId);
    }

    public function atualizarStatus(string $id): void
    {
        exigir_csrf();
        $viagemId = $this->idDaRota($id);
        $status = (string) post('status');

        try {
            $this->service->atualizarStatus($viagemId, $status);
            flash('sucesso', 'Status da viagem atualizado com sucesso.');
        } catch (InvalidArgumentException $e) {
            flash('erro', $e->getMessage());
        }

        redirecionar('viagens/' . $viagemId);
    }

    public function excluir(string $id): void
    {
        exigir_csrf();
        $viagemId = $this->idDaRota($id);

        try {
            $this->service->excluir($viagemId);
            flash('sucesso', 'Viagem excluída com sucesso.');
            redirecionar('viagens');
        } catch (DomainException $e) {
            flash('atencao', $e->getMessage());
            redirecionar('viagens/' . $viagemId);
        }
    }

    private function exibirFormulario(?array $viagem, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $viagem ?? [
            'pacote_id' => '', 'data_partida' => '', 'data_retorno' => '',
            'vagas_totais' => '', 'preco' => '', 'status' => 'planejada', 'observacoes' => '',
        ];

        $vagasOcupadas = 0;
        if ($viagem !== null) {
            $vagasOcupadas = (int) ($this->service->totais((int) $viagem['id'])['viajantes'] ?? 0);
        }

        $this->visao('viagens/formulario', [
            'tituloPagina' => $viagem === null ? 'Nova viagem' : 'Editar viagem',
            'subtituloPagina' => $viagem === null
                ? 'Programe uma nova partida a partir de um pacote.'
                : 'Atualize os dados da viagem.',
            'viagem' => $viagem,
            'valores' => $valores,
            'erros' => $erros,
            'vagasOcupadas' => $vagasOcupadas,
            'pacotes' => $this->banco->buscarTodos(
                'SELECT p.id, p.nome, p.preco_base, p.ativo, d.nome AS destino
                 FROM pacotes p
                 INNER JOIN destinos d ON d.id = p.destino_id
                 WHERE p.ativo = 1
                 ORDER BY p.nome ASC'
            ),
            'statusOpcoes' => $this->service->statusOpcoes(),
        ]);
    }
}
