<?php
/**
 * Cadastro e gestão de destinos.
 */

declare(strict_types=1);

class DestinosController extends Controller
{
    private DestinoService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DestinoService();
    }

    public function index(): void
    {
        $filtros = [
            'busca' => trim((string) get('busca')),
            'situacao' => (string) get('situacao', 'ativos'),
            'por_pagina' => 10,
            'pagina' => (int) get('pagina', 1),
        ];
        if (!in_array($filtros['situacao'], ['ativos', 'inativos', 'todos'], true)) {
            $filtros['situacao'] = 'ativos';
        }

        $resultado = $this->service->listar($filtros);

        $this->visao('destinos/index', [
            'tituloPagina' => 'Destinos',
            'subtituloPagina' => 'Cadastro dos destinos operados pela agência.',
            'destinos' => $resultado['registros'],
            'busca' => $resultado['busca'],
            'situacao' => $resultado['situacao'],
            'paginacao' => $resultado,
            'parametrosFiltro' => ['busca' => $resultado['busca'], 'situacao' => $resultado['situacao']],
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
        $this->sucesso('Destino cadastrado com sucesso.', 'destinos/' . $id);
    }

    public function mostrar(string $id): void
    {
        $destinoId = $this->idDaRota($id);
        $destino = $this->service->buscarPorId($destinoId);
        $this->visao('destinos/mostrar', [
            'tituloPagina' => $destino['nome'],
            'subtituloPagina' => 'Ficha do destino e pacotes vinculados.',
            'destino' => $destino,
            'pacotes' => $this->service->pacotes($destinoId),
            'proximasViagens' => $this->service->proximasViagens($destinoId),
        ]);
    }

    public function editar(string $id): void
    {
        $destinoId = $this->idDaRota($id);
        $destino = $this->service->buscarPorId($destinoId);
        $this->exibirFormulario($destino, [], $destino);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $destinoId = $this->idDaRota($id);
        $destino = $this->service->buscarPorId($destinoId);

        [$dados, $erros] = $this->service->validar($_POST, $destinoId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($destino, $erros, array_merge($destino, $_POST));
            return;
        }

        $this->service->atualizar($destinoId, $dados);
        $this->sucesso('Dados do destino atualizados com sucesso.', 'destinos/' . $destinoId);
    }

    public function alternar(string $id): void
    {
        exigir_csrf();
        $destinoId = $this->idDaRota($id);
        $novoStatus = $this->service->alternar($destinoId);

        flash(
            'sucesso',
            $novoStatus === 1
                ? 'Destino reativado com sucesso.'
                : 'Destino desativado. O histórico permanece preservado.'
        );
        redirecionar('destinos/' . $destinoId);
    }

    private function exibirFormulario(?array $destino, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $destino ?? [
            'nome' => '', 'cidade' => '', 'estado' => '', 'pais' => '', 'continente' => '', 'descricao' => '',
        ];

        $this->visao('destinos/formulario', [
            'tituloPagina' => $destino === null ? 'Novo destino' : 'Editar destino',
            'subtituloPagina' => $destino === null
                ? 'Cadastre um novo destino para começar a criar pacotes.'
                : 'Atualize os dados do destino.',
            'destino' => $destino,
            'valores' => $valores,
            'erros' => $erros,
        ]);
    }
}
