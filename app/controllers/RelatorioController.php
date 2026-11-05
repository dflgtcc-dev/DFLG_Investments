<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\ExtratoPdf;
use app\services\GamificacaoService;
use app\services\RelatorioService;

class RelatorioController extends Controller
{
    private RelatorioService $service;
    private GamificacaoService $gamificacaoService;

    public function __construct()
    {
        $this->service = new RelatorioService();
        $this->gamificacaoService = new GamificacaoService();
    }

    // RF 11: visualiza o extrato mensal ou anual
    public function index()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();

        // Quadro 5: acessar os relatórios pela primeira vez dá XP (uma única vez)
        $this->gamificacaoService->concederXp($usuarioId, 'relatorios');

        $filtro = $this->lerFiltro($usuarioId);

        $data = $filtro;
        $data['meses'] = RelatorioService::MESES;
        $data['extrato'] = $this->getExtrato($usuarioId, $filtro);

        $this->view('relatorios/relatorios_extrato', $data);
    }

    // RF 11: baixa o mesmo extrato em PDF
    public function pdf()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();
        $filtro = $this->lerFiltro($usuarioId);
        $extrato = $this->getExtrato($usuarioId, $filtro);

        if ($filtro['tipo'] === 'anual') {
            $titulo = 'Extrato anual';
            $arquivo = sprintf('extrato-anual-%04d.pdf', $filtro['ano']);
        } else {
            $titulo = 'Extrato mensal';
            $arquivo = sprintf('extrato-mensal-%04d-%02d.pdf', $filtro['ano'], $filtro['mes']);
        }

        $pdf = new ExtratoPdf($titulo, $extrato['titulo'], $_SESSION['usuario_logado']->getNome());
        $pdf->gerar($filtro['tipo'], $extrato);
        $pdf->Output('D', $arquivo);
        exit();
    }

    // lê o tipo, o ano e o mês da URL e usa o mês atual quando vier algo inválido
    private function lerFiltro(int $usuarioId): array
    {
        $anos = $this->service->getAnosDisponiveis($usuarioId);

        $tipo = ($_GET['tipo'] ?? '') === 'anual' ? 'anual' : 'mensal';

        $ano = (int) ($_GET['ano'] ?? 0);

        if (!in_array($ano, $anos, true)) {
            $ano = (int) date('Y');
        }

        $mes = (int) ($_GET['mes'] ?? 0);

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }

        return ['tipo' => $tipo, 'ano' => $ano, 'mes' => $mes, 'anos' => $anos];
    }

    private function getExtrato(int $usuarioId, array $filtro): array
    {
        if ($filtro['tipo'] === 'anual') {
            return $this->service->getExtratoAnual($usuarioId, $filtro['ano']);
        }

        return $this->service->getExtratoMensal($usuarioId, $filtro['ano'], $filtro['mes']);
    }
}
