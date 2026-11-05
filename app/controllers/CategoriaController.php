<?php

namespace app\controllers;

use app\core\Controller;
use app\services\LimiteCategoriaService;
use app\services\RelatorioService;

class CategoriaController extends Controller
{
    private LimiteCategoriaService $service;

    public function __construct()
    {
        $this->service = new LimiteCategoriaService();
    }

    // RF 12: lista as categorias de despesa com o gasto do mês e o limite de cada uma
    public function listarTodas()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['categorias'] = $this->service->getCategoriasComLimite($this->getUsuarioLogadoId());
        $data['mes'] = $this->getMesAtual();
        $data['salvo'] = isset($_GET['salvo']);

        $this->view('categorias/categorias_list', $data);
    }

    public function salvarLimites()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();

        $valores = $_POST['limite'] ?? [];

        if (!is_array($valores)) {
            $valores = [];
        }

        if ($this->service->salvarLimites($usuarioId, $valores)) {
            $this->redirect(URL_BASE . '/categorias?salvo=1');
        }

        $data = [];
        $data['categorias'] = $this->service->getCategoriasComLimite($usuarioId);
        $data['mes'] = $this->getMesAtual();
        $data['salvo'] = false;
        $data['erros'] = $this->service->getErros();
        $data['valores'] = $valores;

        $this->view('categorias/categorias_list', $data);
    }

    private function getMesAtual(): string
    {
        return RelatorioService::MESES[(int) date('n')] . ' de ' . date('Y');
    }
}
