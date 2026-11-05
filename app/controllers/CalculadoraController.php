<?php

namespace app\controllers;

use app\core\Controller;
use app\services\CalculadoraService;
use app\services\GamificacaoService;

class CalculadoraController extends Controller
{
    private CalculadoraService $service;
    private GamificacaoService $gamificacaoService;

    public function __construct()
    {
        $this->service = new CalculadoraService();
        $this->gamificacaoService = new GamificacaoService();
    }

    public function index()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_list', [
            'calculadoras' => $this->service->getCalculadoras(),
        ]);
    }

    public function jurosCompostos()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_juros_compostos');
    }

    public function jurosCompostosCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_juros_compostos',
            'calcularJurosCompostos',
            ['valorInicial', 'aporteMensal', 'taxa', 'unidadeTaxa', 'prazoMeses']
        );
    }

    public function aporteMeta()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_aporte_meta');
    }

    public function aporteMetaCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_aporte_meta',
            'calcularAporteMeta',
            ['valorAlvo', 'valorInicial', 'taxa', 'unidadeTaxa', 'prazoMeses']
        );
    }

    public function tempoMeta()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_tempo_meta');
    }

    public function tempoMetaCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_tempo_meta',
            'calcularTempoMeta',
            ['valorAlvo', 'valorInicial', 'aporteMensal', 'taxa', 'unidadeTaxa']
        );
    }

    public function independenciaFinanceira()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_independencia_financeira');
    }

    public function independenciaFinanceiraCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_independencia_financeira',
            'calcularIndependenciaFinanceira',
            ['rendaMensal', 'patrimonioAtual', 'aporteMensal', 'taxa', 'unidadeTaxa']
        );
    }

    public function aposentadoria()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_aposentadoria');
    }

    public function aposentadoriaCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_aposentadoria',
            'calcularAposentadoria',
            ['idadeAtual', 'idadeAposentadoria', 'patrimonioAtual', 'aporteMensal', 'taxa', 'unidadeTaxa']
        );
    }

    public function reservaEmergencia()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_reserva_emergencia');
    }

    public function reservaEmergenciaCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_reserva_emergencia',
            'calcularReservaEmergencia',
            ['gastoMensal', 'mesesCobertura', 'valorGuardado', 'aporteMensal', 'taxa', 'unidadeTaxa']
        );
    }

    public function valorReal()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_valor_real');
    }

    public function valorRealCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_valor_real',
            'calcularValorReal',
            ['valor', 'prazoMeses', 'inflacao', 'unidadeInflacao']
        );
    }

    public function comparadorCenarios()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_comparador_cenarios');
    }

    public function comparadorCenariosCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_comparador_cenarios',
            'calcularComparadorCenarios',
            [
                'valorInicial1', 'aporteMensal1', 'taxa1', 'unidadeTaxa1', 'prazoMeses1',
                'valorInicial2', 'aporteMensal2', 'taxa2', 'unidadeTaxa2', 'prazoMeses2',
            ]
        );
    }

    public function rendimentoInvestimento()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_rendimento_investimento');
    }

    public function rendimentoInvestimentoCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_rendimento_investimento',
            'calcularRendimentoInvestimento',
            ['valorInicial', 'valorFinal', 'prazoMeses']
        );
    }

    public function jurosSimples()
    {
        $this->autenticacaoRequired();

        $this->view('calculadora/calculadora_juros_simples');
    }

    public function jurosSimplesCalcular()
    {
        $this->autenticacaoRequired();

        $this->calcular(
            'calculadora/calculadora_juros_simples',
            'calcularJurosSimples',
            ['capital', 'taxa', 'unidadeTaxa', 'prazoMeses']
        );
    }

    // lê os campos do formulário, faz o cálculo e mostra o resultado na mesma tela;
    // usar qualquer calculadora dá 10 XP por dia (RN 07 e Quadro 5)
    private function calcular(string $view, string $calculo, array $campos)
    {
        $dados = [];

        foreach ($campos as $campo) {
            $dados[$campo] = $_POST[$campo] ?? '';
        }

        $resultado = $this->service->$calculo($dados);

        if ($resultado === false) {

            $this->view($view, [
                'dados' => $dados,
                'erros' => $this->service->getErros(),
            ]);

            return;
        }

        $this->gamificacaoService->concederXp($this->getUsuarioLogadoId(), 'simulador');

        $this->view($view, [
            'dados' => $dados,
            'resultado' => $resultado,
        ]);
    }
}