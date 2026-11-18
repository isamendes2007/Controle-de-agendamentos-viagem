<?php
/**
 * Cadastro e gestão de clientes.
 */

declare(strict_types=1);

class ClientesController extends Controller
{
    private ClienteService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClienteService();
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

        $this->visao('clientes/index', [
            'tituloPagina' => 'Clientes',
            'subtituloPagina' => 'Cadastro e histórico dos viajantes atendidos pela agência.',
            'clientes' => $resultado['registros'],
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
        $this->sucesso('Cliente cadastrado com sucesso.', 'clientes/' . $id);
    }

    public function mostrar(string $id): void
    {
        $clienteId = $this->idDaRota($id);
        $cliente = $this->service->buscarPorId($clienteId);

        $reservas = $this->service->reservasComDetalhes($clienteId);
        $resumo = $this->service->resumoReservas($clienteId);

        $emAberto = 0.0;
        foreach ($reservas as $reserva) {
            if (!in_array($reserva['status'], ['cancelada'], true)) {
                $emAberto += (float) $reserva['em_aberto'];
            }
        }

        $this->visao('clientes/mostrar', [
            'tituloPagina' => $cliente['nome'],
            'subtituloPagina' => 'Ficha completa do cliente e histórico de viagens.',
            'cliente' => $cliente,
            'reservas' => $reservas,
            'resumo' => $resumo,
            'emAberto' => $emAberto,
            'idade' => calcular_idade($cliente['data_nascimento']),
        ]);
    }

    public function editar(string $id): void
    {
        $clienteId = $this->idDaRota($id);
        $cliente = $this->service->buscarPorId($clienteId);
        $this->exibirFormulario($cliente, [], $cliente);
    }

    public function atualizar(string $id): void
    {
        exigir_csrf();
        $clienteId = $this->idDaRota($id);
        $cliente = $this->service->buscarPorId($clienteId);

        [$dados, $erros] = $this->service->validar($_POST, $clienteId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($cliente, $erros, array_merge($cliente, $_POST));
            return;
        }

        $this->service->atualizar($clienteId, $dados);
        $this->sucesso('Dados do cliente atualizados com sucesso.', 'clientes/' . $clienteId);
    }

    public function alternar(string $id): void
    {
        exigir_csrf();
        $clienteId = $this->idDaRota($id);
        $novoStatus = $this->service->alternar($clienteId);

        flash(
            'sucesso',
            $novoStatus === 1
                ? 'Cliente reativado com sucesso.'
                : 'Cliente desativado. O histórico permanece preservado.'
        );
        redirecionar('clientes/' . $clienteId);
    }

    private function exibirFormulario(?array $cliente, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $cliente ?? [
            'nome' => '', 'cpf' => '', 'email' => '', 'telefone' => '',
            'data_nascimento' => '', 'cep' => '', 'endereco' => '', 'numero' => '',
            'complemento' => '', 'bairro' => '', 'cidade' => '', 'estado' => '', 'observacoes' => '',
        ];

        $this->visao('clientes/formulario', [
            'tituloPagina' => $cliente === null ? 'Novo cliente' : 'Editar cliente',
            'subtituloPagina' => $cliente === null
                ? 'Cadastre um novo viajante para começar a criar reservas.'
                : 'Atualize os dados cadastrais e de contato.',
            'cliente' => $cliente,
            'valores' => $valores,
            'erros' => $erros,
        ]);
    }
}
