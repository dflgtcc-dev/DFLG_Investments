<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\UploadImagem;
use app\helpers\Validador;
use app\models\Usuario;
use app\services\GamificacaoService;
use app\services\UsuarioService;

class PerfilController extends Controller
{
    private UsuarioService $usuarioService;
    private GamificacaoService $gamificacaoService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->gamificacaoService = new GamificacaoService();
    }

    public function visualizar()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();

        $data = [];
        $data['usuario'] = $this->usuarioService->getUsuarioById($usuarioId);
        $data['gamificacao'] = $this->gamificacaoService->getGamificacao($usuarioId);
        $data['progresso'] = $data['gamificacao']
            ? $this->gamificacaoService->getProgressoNivel($data['gamificacao']->getXpTotal())
            : null;

        // aviso deixado por uma ação anterior (ex.: senha alterada); aparece uma só vez
        if (isset($_SESSION['aviso_perfil'])) {
            $data['sucesso'] = $_SESSION['aviso_perfil'];
            unset($_SESSION['aviso_perfil']);
        }

        $this->view('perfil/perfil_view', $data);
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['usuario'] = $this->usuarioService->getUsuarioById($this->getUsuarioLogadoId());

        $this->view('perfil/perfil_edit', $data);
    }

    public function senha()
    {
        $this->autenticacaoRequired();

        $this->view('perfil/perfil_senha');
    }

    public function alterarSenha()
    {
        $this->autenticacaoRequired();

        $senhaAtual = $_POST['senhaAtual'] ?? '';
        $novaSenha = $_POST['novaSenha'] ?? '';
        $confirmacao = $_POST['confirmarSenha'] ?? '';

        if (!is_string($senhaAtual) || !is_string($novaSenha) || !is_string($confirmacao)) {
            $this->redirect(URL_BASE . '/perfil/senha');

            return;
        }

        if ($this->usuarioService->alterarSenha($this->getUsuarioLogadoId(), $senhaAtual, $novaSenha, $confirmacao)) {
            // a sessão continua aberta, mas com um novo identificador
            session_regenerate_id(true);
            $_SESSION['aviso_perfil'] = 'Senha alterada com sucesso.';

            $this->redirect(URL_BASE . '/perfil');

            return;
        }

        $data = [];
        $data['erros'] = $this->usuarioService->getErros();

        $this->view('perfil/perfil_senha', $data);
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->somenteUsuarioComum();

        $data = [];
        $data['usuario'] = $this->usuarioService->getUsuarioById($this->getUsuarioLogadoId());

        $this->view('perfil/perfil_excluir', $data);
    }

    public function excluirConta()
    {
        $this->autenticacaoRequired();
        $this->somenteUsuarioComum();

        $usuarioId = $this->getUsuarioLogadoId();
        $usuario = $this->usuarioService->getUsuarioById($usuarioId);

        // RN 14: a exclusão exige confirmação explícita do usuário
        if (($_POST['confirmarExclusao'] ?? '') !== '1') {
            $data['usuario'] = $usuario;
            $data['erro'] = 'Marque a confirmação para excluir seu perfil.';

            $this->view('perfil/perfil_excluir', $data);

            return;
        }

        if (!$usuario || !$this->usuarioService->deleteUsuario($usuarioId)) {
            $data['usuario'] = $usuario;
            $data['erro'] = 'Não foi possível excluir seu perfil.';

            $this->view('perfil/perfil_excluir', $data);

            return;
        }

        if ($usuario->getFoto()) {
            $upload = new UploadImagem(__DIR__ . '/../../public/assets/uploads/perfil');
            $upload->remover($usuario->getFoto());
        }

        session_unset();
        session_destroy();

        $this->redirect(URL_BASE . '/login');
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();
        $upload = new UploadImagem(__DIR__ . '/../../public/assets/uploads/perfil');
        $usuarioAtual = $this->usuarioService->getUsuarioById($this->getUsuarioLogadoId());

        $nome = trim($_POST['nome'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        // RF 04: o usuário comum não altera e-mail nem data de nascimento (vale o que está no banco); o administrador altera
        $contaTravada = !$usuarioAtual->eAdmin();
        $email = $contaTravada ? $usuarioAtual->getEmail() : ($_POST['email'] ?? '');
        $dataNascimento = $contaTravada ? $usuarioAtual->getDataNascimento() : ($_POST['dataNascimento'] ?? '');

        // telefone é opcional: guarda só os dígitos, ou null quando vazio
        $telefoneInformado = $_POST['telefone'] ?? '';
        $telefone = preg_replace('/\D/', '', is_string($telefoneInformado) ? $telefoneInformado : '');
        $telefone = $telefone === '' ? null : $telefone;
        $foto = $_FILES['foto'] ?? null;
        $removerFoto = isset($_POST['removerFoto']);

        $validador->obrigatorio('nome', $nome);
        $validador->obrigatorio('nickname', $nickname);
        $validador->obrigatorio('email', $email);
        $validador->obrigatorio('dataNascimento', $dataNascimento);

        $erros = $validador->getErros();

        if ($upload->enviado($foto) && !$upload->validar($foto)) {
            $erros['foto'] = $upload->getErro();
        }

        $usuario = new Usuario(
            $usuarioAtual->getId(),
            (string) $nome,
            (string) $nickname,
            $email,
            $usuarioAtual->getSenha(),
            $dataNascimento,
            $usuarioAtual->getPerfil(),
            $usuarioAtual->getStatus(),
            $usuarioAtual->getFoto(),
            $usuarioAtual->getTema(),
            $telefone
        );

        if (!empty($erros)) {

            $data['usuario'] = $usuario;
            $data['erros'] = $erros;

            $this->view('perfil/perfil_edit', $data);

            return;
        }

        if (!$this->usuarioService->updateUsuario($usuario)) {

            $data['usuario'] = $usuario;
            $data['erros'] = $this->usuarioService->getErros();

            $this->view('perfil/perfil_edit', $data);

            return;
        }

        if ($upload->enviado($foto)) {
            $nomeFoto = $upload->salvar($foto, 'usuario_' . $usuarioAtual->getId());

            if (!$nomeFoto) {
                $this->atualizarSessao($usuarioAtual->getId());

                $data['usuario'] = $usuario;
                $data['erros'] = ['foto' => 'Seus dados foram salvos, mas a foto não: ' . $upload->getErro()];

                $this->view('perfil/perfil_edit', $data);

                return;
            }

            $this->usuarioService->updateFoto($usuarioAtual->getId(), $nomeFoto);
            $upload->remover($usuarioAtual->getFoto());
        } elseif ($removerFoto && $usuarioAtual->getFoto()) {
            // RF 04: o usuário pode remover a foto; se enviou uma nova na mesma edição, a nova prevalece
            $this->usuarioService->updateFoto($usuarioAtual->getId(), null);
            $upload->remover($usuarioAtual->getFoto());
        }

        $this->atualizarSessao($usuarioAtual->getId());

        // Quadro 5: perfil completo (foto e dados pessoais) dá XP uma única vez
        if ($_SESSION['usuario_logado']->getFoto()) {
            $this->gamificacaoService->concederXp($usuarioAtual->getId(), 'perfil_completo');
        }

        $this->redirect(URL_BASE . '/perfil');
    }

    public function atualizarPreferencias()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();
        $visibilidade = isset($_POST['visibilidadeRanking']);
        // RNF 07: o switch marcado é o modo escuro
        $tema = isset($_POST['modoEscuro']) ? 'escuro' : 'claro';

        $this->usuarioService->updateTema($usuarioId, $tema);

        // Quadro 5: salvar as preferências dá XP uma única vez (administrador não tem XP)
        $this->gamificacaoService->atualizarVisibilidade($usuarioId, $visibilidade);

        $this->atualizarSessao($usuarioId);

        $this->redirect(URL_BASE . '/perfil');
    }

    // RF 15: só o usuário comum exclui a própria conta; administrador volta ao perfil
    private function somenteUsuarioComum()
    {
        if ($_SESSION['usuario_logado']->getPerfil() === 'administrador') {
            $this->redirect(URL_BASE . '/perfil');
        }
    }

    // atualiza o usuário guardado na sessão com os dados novos do banco
    private function atualizarSessao(int $usuarioId)
    {
        $_SESSION['usuario_logado'] = $this->usuarioService->getUsuarioById($usuarioId);
    }
}