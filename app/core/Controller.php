<?php

namespace app\core;

use app\repositories\UsuarioRepository;

class Controller
{

    public function view(string $view, ?array $data = null)
    {

        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/$view.php";

        if (file_exists($path)) {
            require_once $path;
        } else {
            print 'A view solicitada não foi encontrada: ' . $view;
        }
    }

    public function redirect(string $url)
    {
        header('location: ' . $url);
        exit();
    }

    public function autenticacaoRequired()
    {
        if (!isset($_SESSION['usuario_logado'])) {
            $this->redirect(URL_BASE . '/login');
        }

        $this->validarContaAtiva();

        return true;
    }

    public function adminRequired()
    {
        if (!isset($_SESSION['usuario_logado'])) {
            $this->redirect(URL_BASE . '/login');
        }

        // usuário comum não acessa o painel administrativo
        if ($_SESSION['usuario_logado']->getPerfil() !== 'administrador') {
            $this->redirect(URL_BASE . '/dashboard');
        }

        $this->validarContaAtiva();

        return true;
    }

    // RF 02: conta bloqueada ou excluída com a sessão aberta perde o acesso no próximo clique
    private function validarContaAtiva(): void
    {
        $usuario = (new UsuarioRepository())->getUsuarioById($_SESSION['usuario_logado']->getId());

        if ($usuario && $usuario->getStatus() === 'ativo') {
            return;
        }

        $aviso = $usuario
            ? 'Sua conta foi bloqueada. Entre em contato com o administrador.'
            : 'Sua conta não está mais disponível.';

        // limpa os dados do usuário, mas mantém a sessão para levar o aviso até a tela de login
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['aviso_login'] = $aviso;

        $this->redirect(URL_BASE . '/login');
    }

    public function getUsuarioLogadoId(): int
    {
        return $_SESSION['usuario_logado']->getId();
    }
}