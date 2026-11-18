<?php
/**
 * Gestão de usuários do sistema (apenas administrador).
 */

declare(strict_types=1);

class UsuariosController extends Controller
{
    private UsuarioService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UsuarioService();
    }

    public function index(): void
    {
        $this->somenteAdministrador();
        $usuarios = $this->service->listar();
        $this->visao('usuarios/index', [
            'tituloPagina' => 'Usuários',
            'subtituloPagina' => 'Cadastro e controle de acesso da equipe.',
            'usuarios' => $usuarios,
            'perfilOpcoes' => $this->service->perfilOpcoes(),
        ]);
    }

    public function novo(): void
    {
        $this->somenteAdministrador();
        $this->exibirFormulario(null, []);
    }

    public function criar(): void
    {
        $this->somenteAdministrador();
        exigir_csrf();
        [$dados, $erros] = $this->service->validar($_POST);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario(null, $erros, $_POST);
            return;
        }

        $id = $this->service->criar($dados);
        $this->sucesso('Usuário cadastrado com sucesso.', 'usuarios/' . $id . '/editar');
    }

    public function editar(string $id): void
    {
        $this->somenteAdministrador();
        $usuarioId = $this->idDaRota($id);
        $usuario = $this->service->buscarPorId($usuarioId);
        $this->exibirFormulario($usuario, [], $usuario);
    }

    public function atualizar(string $id): void
    {
        $this->somenteAdministrador();
        exigir_csrf();
        $usuarioId = $this->idDaRota($id);
        $usuario = $this->service->buscarPorId($usuarioId);

        [$dados, $erros] = $this->service->validar($_POST, $usuarioId);

        if ($erros !== []) {
            http_response_code(422);
            $this->exibirFormulario($usuario, $erros, array_merge($usuario, $_POST));
            return;
        }

        $this->service->atualizar($usuarioId, $dados);
        $_SESSION['usuario']['perfil'] = $dados['perfil'];
        $this->sucesso('Usuário atualizado com sucesso.', 'usuarios/' . $usuarioId . '/editar');
    }

    public function alternar(string $id): void
    {
        $this->somenteAdministrador();
        exigir_csrf();
        $usuarioId = $this->idDaRota($id);
        $usuarioLogadoId = (int) ($this->usuario()['id'] ?? 0);

        try {
            $this->service->alternar($usuarioId, $usuarioLogadoId);
            flash('sucesso', 'Status do usuário atualizado com sucesso.');
        } catch (DomainException $e) {
            flash('erro', $e->getMessage());
        }

        redirecionar('usuarios/' . $usuarioId . '/editar');
    }

    private function exibirFormulario(?array $usuario, array $erros, ?array $dados = null): void
    {
        $valores = $dados ?? $usuario ?? [
            'nome' => '', 'email' => '', 'perfil' => 'agente', 'telefone' => '', 'senha' => '', 'ativo' => 1,
        ];

        $this->visao('usuarios/formulario', [
            'tituloPagina' => $usuario === null ? 'Novo usuário' : 'Editar usuário',
            'subtituloPagina' => $usuario === null
                ? 'Cadastre um novo usuário para a equipe.'
                : 'Atualize dados, perfil de acesso e senha.',
            'usuarioEditado' => $usuario,
            'valores' => $valores,
            'erros' => $erros,
            'perfilOpcoes' => $this->service->perfilOpcoes(),
        ]);
    }
}
