<?php
/**
 * Meu perfil: dados pessoais e troca de senha do usuário autenticado.
 */

declare(strict_types=1);

class PerfilController extends Controller
{
    private UsuarioService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UsuarioService();
    }

    public function index(): void
    {
        $usuario = $this->service->buscarPorId((int) ($this->usuario()['id'] ?? 0));

        $reservas = (int) ($this->banco->buscarUm(
            'SELECT COUNT(*) AS total FROM reservas WHERE usuario_id = :id',
            ['id' => $usuario['id']]
        )['total'] ?? 0);

        $pagamentos = (int) ($this->banco->buscarUm(
            'SELECT COUNT(*) AS total FROM pagamentos WHERE usuario_id = :id',
            ['id' => $usuario['id']]
        )['total'] ?? 0);

        $recebido = (float) ($this->banco->buscarUm(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM pagamentos
             WHERE usuario_id = :id AND status = 'pago'",
            ['id' => $usuario['id']]
        )['total'] ?? 0);

        $this->visao('perfil/index', [
            'tituloPagina'    => 'Meu perfil',
            'subtituloPagina' => 'Mantenha seus dados de acesso atualizados.',
            'usuario'         => $usuario,
            'valores'         => $usuario,
            'erros'           => [],
            'estatisticas'    => [
                'reservas'   => $reservas,
                'pagamentos' => $pagamentos,
                'recebido'   => $recebido,
            ],
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $id = (int) ($this->usuario()['id'] ?? 0);
        $atual = $this->service->buscarPorId($id);
        $registroSenha = $this->banco->buscarUm(
            'SELECT senha FROM usuarios WHERE id = :id',
            ['id' => $id]
        );
        $hashSenha = (string) ($registroSenha['senha'] ?? '');

        $dadosBrutos = $_POST;
        // O próprio usuário não altera perfil nem situação da conta.
        $dadosBrutos['perfil'] = $atual['perfil'];
        $dadosBrutos['ativo'] = (int) $atual['ativo'];

        [$dados, $erros] = $this->service->validar($dadosBrutos, $id);

        $novaSenha = (string) post('senha');
        $senhaAtual = (string) post('senha_atual');

        if ($novaSenha !== '' && ($hashSenha === '' || !password_verify($senhaAtual, $hashSenha))) {
            $erros['senha_atual'] = 'Informe a senha atual correta para definir uma nova senha.';
        }

        if ($erros !== []) {
            http_response_code(422);
            $this->visao('perfil/index', [
                'tituloPagina'    => 'Meu perfil',
                'subtituloPagina' => 'Mantenha seus dados de acesso atualizados.',
                'usuario'         => $atual,
                'valores'         => array_merge($atual, $_POST),
                'erros'           => $erros,
                'estatisticas'    => [
                    'reservas'   => 0,
                    'pagamentos' => 0,
                    'recebido'   => 0,
                ],
            ]);
            return;
        }

        $this->service->atualizar($id, $dados);

        $_SESSION['usuario']['nome'] = $dados['nome'];
        $_SESSION['usuario']['email'] = $dados['email'];

        $this->sucesso('Dados atualizados com sucesso.', 'perfil');
    }
}
