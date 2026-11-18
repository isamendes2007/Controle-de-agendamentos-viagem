<?php
/**
 * Cadastro e gestão de pacotes.
 */

declare(strict_types=1);

class PacotesController extends Controller
{
    private PacoteService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PacoteService();
    }

    public function index(): void
    {
        $filtros = [
            'busca' => trim((string) get('busca')),
            'situacao' => (string) get('situacao', 'ativos'),
            'destino_id' => (int) get('destino_id', 0),
            'por_pagina' => 10,
            'pagina' => (int) get('pagina', 1),
        ];
        if (!in_array($filtros['situacao'], ['ativos', 'inativos', 'todos'], true)) {
            $filtros['situacao'] = 'ativos';
        }

        $resultado = $this->service->listar($filtros);
        $destinos = $this->banco->buscarTodos(
            'SELECT id, nome, cidade FROM destinos WHERE ativo = 1 ORDER BY nome ASC'
        );

        $this->visao('pacotes/index', [
            'tituloPagina' => 'Pacotes',
            'subtituloPagina' => 'Catálogo de pacotes da agência.',
            'pacotes' => $resultado['registros'],
            'busca' => $resultado['busca'],
            'situacao' => $resultado['situacao'],
            'paginacao' => $resultado,
            'parametrosFiltro' => [
                'busca' => $resultado['busca'],
                'situacao' => $resultado['situacao'],
                'destino_id' => $filtros['destino_id'],
            ],
            'categorias' => $this->service->categorias(),
            'destinos' => $destinos,
            'destinoId' => $filtros['destino_id'],
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
        $this->sucesso('Pacote cadastrado com sucesso.', 'pacotes/' . $id);
    }

    public function mostrar(string $id): void
    {
        $pacoteId = $this->idDaRota($id);
        $pacote = $this->service->buscarPorId($pacoteId);
        $this->visao('pacotes/mostrar', [
            'tituloPagina' => $pacote['nome'],
            'subtituloPagina' => 'Ficha do pacote e viagens programadas.',
            'pacote' => $pacote,
            'viagens' => $this->service->viagens($pacoteId),
            'indicadores' => $this->service->indicadores($pacoteId),
        ]);
    }

    public function editar(string $id): void
    {
        $pacoteId = $this->idDaRota($id);
        $pacote = $this->service->buscarPorId($pacoteId);
        $this->exibirFormulario($pacote, [], $pacote);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $pacoteId = $this->idDaRota($id);
        $pacote = $this->service->buscarPorId($pacoteId);

        [$dados, $erros] = $this->service->validar($_POST, $pacoteId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($pacote, $erros, array_merge($pacote, $_POST));
            return;
        }

        $this->service->atualizar($pacoteId, $dados);
        $this->sucesso('Dados do pacote atualizados com sucesso.', 'pacotes/' . $pacoteId);
    }

    public function alternar(string $id): void
    {
        exigir_csrf();
        $pacoteId = $this->idDaRota($id);
        $novoStatus = $this->service->alternar($pacoteId);

        flash(
            'sucesso',
            $novoStatus === 1
                ? 'Pacote reativado com sucesso.'
                : 'Pacote desativado. O histórico permanece preservado.'
        );
        redirecionar('pacotes/' . $pacoteId);
    }

    private function exibirFormulario(?array $pacote, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $pacote ?? [
            'nome' => '', 'destino_id' => '', 'categoria' => 'cultural', 'descricao' => '', 'preco_base' => '',
        ];

        $this->visao('pacotes/formulario', [
            'tituloPagina' => $pacote === null ? 'Novo pacote' : 'Editar pacote',
            'subtituloPagina' => $pacote === null
                ? 'Cadastre um novo pacote de viagem.'
                : 'Atualize os dados do pacote.',
            'pacote' => $pacote,
            'valores' => $valores,
            'erros' => $erros,
            'categorias' => $this->service->categorias(),
            'destinos' => $this->banco->buscarTodos('SELECT id, nome, cidade, pais, ativo FROM destinos ORDER BY ativo DESC, nome ASC'),
        ]);
    }
}
