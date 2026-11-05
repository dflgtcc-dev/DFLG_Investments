<?php

namespace app\controllers;

use app\core\Controller;
use app\services\DashboardService;
use app\services\GamificacaoService;
use app\services\LimiteCategoriaService;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;
    private GamificacaoService $gamificacaoService;
    private LimiteCategoriaService $limiteCategoriaService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
        $this->gamificacaoService = new GamificacaoService();
        $this->limiteCategoriaService = new LimiteCategoriaService();
    }

    public function index()
    {
        
        $this->autenticacaoRequired();

        $usuario = $_SESSION['usuario_logado'];

        // RF 09 / RN 03: registra o acesso do dia e atualiza a sequência (streak)
        $this->gamificacaoService->verificarEAtualizarStreak($usuario->getId());

        $data = [];
        $data['usuario'] = $usuario;
        $data['gamificacao'] = $this->gamificacaoService->getGamificacao($usuario->getId());
        $data['progressoNivel'] = $data['gamificacao']
            ? $this->gamificacaoService->getProgressoNivel($data['gamificacao']->getXpTotal())
            : null;

        // RF 07: visão do mês escolhido (?mes=AAAA-MM); sem escolha (ou com valor estranho na URL), o mês atual
        $mes = $_GET['mes'] ?? null;
        $data['painel'] = $this->dashboardService->getPainel($usuario->getId(), is_string($mes) ? $mes : null);

        // RN 12: o aviso de limite excedido é do mês atual, então só aparece quando o usuário está nele
        $data['categoriasExcedidas'] = $data['painel']['ehMesAtual']
            ? $this->limiteCategoriaService->getCategoriasExcedidas($usuario->getId())
            : [];

        $this->view('dashboard/dashboard', $data);
    }
}