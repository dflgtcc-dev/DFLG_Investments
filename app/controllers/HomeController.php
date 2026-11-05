<?php

namespace app\controllers;

use app\core\Controller;

// Home page (Figura 8): página pública e estática.
class HomeController extends Controller
{
    public function index()
    {
        $this->redirecionarSeLogado();

        $this->view('home/home');
    }

    // quem já está logado vai direto para o seu painel (administrador para /admin, usuário comum para /dashboard)
    private function redirecionarSeLogado(): void
    {
        if (!isset($_SESSION['usuario_logado'])) {
            return;
        }

        $this->redirect(URL_BASE . ($_SESSION['usuario_logado']->eAdmin() ? '/admin' : '/dashboard'));
    }
}