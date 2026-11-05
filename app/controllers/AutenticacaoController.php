<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Usuario;
use app\services\AutenticacaoService;
use app\services\UsuarioService;

class AutenticacaoController extends Controller
{

    private AutenticacaoService $autenticacaoService;
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->autenticacaoService = new AutenticacaoService();
        $this->usuarioService = new UsuarioService();
    }

    public function login()
    {
        $dados = [];

        // aviso deixado quando a conta foi bloqueada ou excluída com a sessão aberta; aparece uma só vez
        if (isset($_SESSION['aviso_login'])) {
            $dados['erros'] = $_SESSION['aviso_login'];
            unset($_SESSION['aviso_login']);
        }

        $this->view('autenticacao/login', $dados);
    }

    public function logar()
    {
        $dados = [];

        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $resultado = $this->autenticacaoService->logar($email, $senha);

        if ($resultado) {
            // administrador entra direto no painel administrativo
            $this->redirect(URL_BASE . ($_SESSION['usuario_logado']->eAdmin() ? '/admin' : '/dashboard'));
        } else {

            $dados['erros'] = "E-mail ou senha inválidos";

            $this->view('autenticacao/login', $dados);
        }
    }

    public function logout()
    {
        $this->autenticacaoService->logout();
        $this->redirect(URL_BASE . '/');
    }

    public function cadastrar()
    {
        $this->view('autenticacao/cadastro');
    }

    public function salvar()
    {
        $data = [];
        $validador = new Validador();

        $nome = trim($_POST['nome'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $dataNascimento = $_POST['dataNascimento'];

        // telefone é opcional: guarda só os dígitos, ou null quando vazio
        $telefoneInformado = $_POST['telefone'] ?? '';
        $telefone = preg_replace('/\D/', '', is_string($telefoneInformado) ? $telefoneInformado : '');
        $telefone = $telefone === '' ? null : $telefone;

        $validador->obrigatorio('nome', $nome);
        $validador->obrigatorio('nickname', $nickname);
        $validador->obrigatorio('email', $email);
        $validador->obrigatorio('senha', $senha);
        $validador->obrigatorio('dataNascimento', $dataNascimento);

        if ($validador->temErros()) {

            $data['usuario'] = $_POST;
            $data['erros'] = $validador->getErros();

            $this->view('autenticacao/cadastro', $data);

            return;
        }

        // cadastro público sempre cria usuário comum, nunca administrador
        $usuario = new Usuario(0, $nome, $nickname, $email, $senha, $dataNascimento, 'comum', 'ativo', null, 'claro', $telefone);

        if ($this->usuarioService->cadastrar($usuario)) {
            $this->redirect(URL_BASE . '/login');
        } else {

            $data['usuario'] = $_POST;
            $data['erros'] = $this->usuarioService->getErros();

            $this->view('autenticacao/cadastro', $data);
        }
    }
}