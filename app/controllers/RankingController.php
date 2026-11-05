<?php

namespace app\controllers;

use app\core\Controller;
use app\services\RankingService;

class RankingController extends Controller
{
    private RankingService $service;

    public function __construct()
    {
        $this->service = new RankingService();
    }

    // RF 10: visualiza os rankings semanal, mensal e geral
    public function index()
    {
        $this->autenticacaoRequired();

        $periodo = $_GET['periodo'] ?? 'semanal';

        // usa o semanal quando vier um período inválido
        if (!array_key_exists($periodo, RankingService::PERIODOS)) {
            $periodo = 'semanal';
        }

        $data = $this->service->getRanking($periodo, $this->getUsuarioLogadoId());
        $data['periodos'] = RankingService::PERIODOS;
        $data['usuarioId'] = $this->getUsuarioLogadoId();

        $this->view('ranking/ranking_list', $data);
    }
}