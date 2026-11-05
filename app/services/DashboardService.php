<?php

namespace app\services;

use app\repositories\ParcelamentoRepository;
use app\repositories\TransacaoRepository;
use DateTimeImmutable;

// RF 07: monta os dados do dashboard (visão geral de um mês)
class DashboardService
{
    private const MESES_CURTOS = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    // cores das fatias do gráfico de categorias; a última é a das "Outras categorias"
    private const CORES_CATEGORIAS = ['#6366f1', '#f59e0b', '#14b8a6', '#ec4899', '#8b5cf6', '#0ea5e9', '#94a3b8'];

    private const CATEGORIAS_NO_GRAFICO = 6;
    private const MESES_NA_EVOLUCAO = 6;
    private const METAS_NO_PAINEL = 4;
    private const ITENS_NA_LISTA = 5;

    // a estimativa de fechamento só aparece depois do dia 7: nos primeiros dias a média diária engana
    private const DIAS_MINIMOS_PARA_ESTIMAR = 8;

    private TransacaoRepository $transacaoRepository;
    private ParcelamentoRepository $parcelamentoRepository;
    private LimiteCategoriaService $limiteCategoriaService;
    private MetaService $metaService;

    public function __construct()
    {
        $this->transacaoRepository = new TransacaoRepository();
        $this->parcelamentoRepository = new ParcelamentoRepository();
        $this->limiteCategoriaService = new LimiteCategoriaService();
        $this->metaService = new MetaService();
    }

    // $mesSolicitado no formato AAAA-MM; mês inválido ou fora do histórico do usuário cai no mês atual
    public function getPainel(int $usuarioId, ?string $mesSolicitado): array
    {
        $hoje = new DateTimeImmutable('today');
        $mesAtual = $hoje->format('Y-m');

        $primeiraData = $this->transacaoRepository->getPrimeiraData($usuarioId);
        $mesMinimo = $primeiraData ? min(substr($primeiraData, 0, 7), $mesAtual) : $mesAtual;

        $mes = $this->validarMes($mesSolicitado, $mesMinimo, $mesAtual);
        $ehMesAtual = $mes === $mesAtual;

        $inicioMes = new DateTimeImmutable($mes . '-01');
        $fimMes = $inicioMes->modify('last day of this month');
        $inicio = $inicioMes->format('Y-m-d');
        $fim = $fimMes->format('Y-m-d');

        $nomeMes = RelatorioService::MESES[(int) $inicioMes->format('n')];

        // totais do mês e saldo (o saldo do mês atual é o de hoje; o dos meses anteriores é o do último dia)
        $totais = $this->transacaoRepository->getTotaisPeriodo($usuarioId, $inicio, $fim);
        $receitas = $totais['receitas'];
        $despesas = $totais['despesas'];

        $limiteDoSaldo = $ehMesAtual ? $hoje->modify('+1 day') : $inicioMes->modify('first day of next month');
        $saldo = $this->transacaoRepository->getSaldoAnteriorA($usuarioId, $limiteDoSaldo->format('Y-m-d'));

        // comparação: o mês atual é comparado com os mesmos dias do mês anterior; os meses fechados, com o mês inteiro anterior
        $inicioAnterior = $inicioMes->modify('-1 month');
        $fimAnteriorMes = $inicioAnterior->modify('last day of this month');
        $nomeMesAnterior = RelatorioService::MESES[(int) $inicioAnterior->format('n')];

        if ($ehMesAtual) {
            $fimAnterior = $inicioAnterior->modify('+' . ((int) $hoje->format('j') - 1) . ' days');
            $fimAnterior = $fimAnterior > $fimAnteriorMes ? $fimAnteriorMes : $fimAnterior;
            $rotuloComparacao = 'vs. dias 1 a ' . $fimAnterior->format('j') . ' de ' . $nomeMesAnterior;
        } else {
            $fimAnterior = $fimAnteriorMes;
            $rotuloComparacao = 'vs. ' . $nomeMesAnterior;
        }

        $totaisAnterior = $this->transacaoRepository->getTotaisPeriodo(
            $usuarioId,
            $inicioAnterior->format('Y-m-d'),
            $fimAnterior->format('Y-m-d')
        );

        $categorias = $this->getCategoriasDoGrafico($usuarioId, $inicio, $fim, $despesas);
        $evolucao = $this->getEvolucao($usuarioId, $inicioMes, $fimMes);

        $limites = array_values(array_filter(
            $this->limiteCategoriaService->getCategoriasComLimite($usuarioId, $mes),
            fn ($categoria) => $categoria['limite'] !== null
        ));
        usort($limites, fn ($a, $b) => [$b['excedido'], $b['percentual']] <=> [$a['excedido'], $a['percentual']]);

        $metas = $this->getMetas($usuarioId);

        return [
            'mes' => $mes,
            'nomeMes' => $nomeMes . ' de ' . $inicioMes->format('Y'),
            'ehMesAtual' => $ehMesAtual,
            'mesAnterior' => $mes > $mesMinimo ? $inicioAnterior->format('Y-m') : null,
            'mesProximo' => $mes < $mesAtual ? $inicioMes->modify('+1 month')->format('Y-m') : null,

            'saldo' => round($saldo, 2),
            'rotuloSaldo' => $ehMesAtual ? 'Saldo atual' : 'Saldo no fim de ' . $nomeMes,
            'receitas' => round($receitas, 2),
            'despesas' => round($despesas, 2),
            'resultado' => round($receitas - $despesas, 2),
            'variacaoReceitas' => $this->variacao($receitas, $totaisAnterior['receitas']),
            'variacaoDespesas' => $this->variacao($despesas, $totaisAnterior['despesas']),
            'rotuloComparacao' => $rotuloComparacao,

            'categorias' => $categorias,
            'limites' => $limites,
            'metas' => $metas['lista'],
            'totalMetas' => $metas['total'],
            'metasConcluidas' => $metas['concluidas'],
            'parcelas' => $this->getParcelas($usuarioId, $inicio, $fim, $ehMesAtual, $hoje),
            'ultimos' => $this->transacaoRepository->getUltimasTransacoes($usuarioId, $inicio, $fim, self::ITENS_NA_LISTA),
            'ritmo' => $this->getRitmo($usuarioId, $inicio, $fim, $despesas, $ehMesAtual, $hoje, $fimMes),

            'graficos' => [
                'categorias' => [
                    'rotulos' => array_column($categorias, 'nome'),
                    'valores' => array_column($categorias, 'valor'),
                    'cores' => array_column($categorias, 'cor'),
                    'total' => round($despesas, 2),
                ],
                'evolucao' => $evolucao,
            ],
        ];
    }

    private function validarMes(?string $mes, string $minimo, string $maximo): string
    {
        if ($mes === null || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return $maximo;
        }

        if ($mes < $minimo || $mes > $maximo) {
            return $maximo;
        }

        return $mes;
    }

    // variação percentual; sem base de comparação (período anterior zerado) devolve null
    private function variacao(float $atual, float $anterior): ?int
    {
        if ($anterior <= 0) {
            return null;
        }

        return (int) round(($atual - $anterior) / $anterior * 100);
    }

    // as maiores categorias de despesa; as demais viram "Outras categorias"
    private function getCategoriasDoGrafico(int $usuarioId, string $inicio, string $fim, float $totalDespesas): array
    {
        $linhas = $this->transacaoRepository->getDespesasPorCategoria($usuarioId, $inicio, $fim);

        $categorias = [];
        $outras = 0.0;

        foreach ($linhas as $indice => $linha) {
            if ($indice < self::CATEGORIAS_NO_GRAFICO) {
                $categorias[] = [
                    'nome' => $linha['nome'],
                    'icone' => $linha['icone'] ?: 'bi-tag',
                    'valor' => round((float) $linha['total'], 2),
                ];
            } else {
                $outras += (float) $linha['total'];
            }
        }

        if ($outras > 0) {
            $categorias[] = ['nome' => 'Outras categorias', 'icone' => 'bi-three-dots', 'valor' => round($outras, 2)];
        }

        $quantidadeDeCores = count(self::CORES_CATEGORIAS);

        foreach ($categorias as $indice => $categoria) {
            $ehOutras = $outras > 0 && $indice === count($categorias) - 1;

            // as "Outras categorias" usam sempre a última cor (cinza)
            $categorias[$indice]['cor'] = $ehOutras
                ? self::CORES_CATEGORIAS[$quantidadeDeCores - 1]
                : self::CORES_CATEGORIAS[$indice % ($quantidadeDeCores - 1)];
            $categorias[$indice]['percentual'] = $totalDespesas > 0 ? round($categoria['valor'] / $totalDespesas * 100, 1) : 0;
        }

        return $categorias;
    }

    // receitas, despesas e resultado dos últimos meses, terminando no mês escolhido (meses sem registro valem zero)
    private function getEvolucao(int $usuarioId, DateTimeImmutable $inicioMes, DateTimeImmutable $fimMes): array
    {
        $inicioEvolucao = $inicioMes->modify('-' . (self::MESES_NA_EVOLUCAO - 1) . ' months');

        $porMes = [];

        foreach ($this->transacaoRepository->getEvolucaoPorIntervalo($usuarioId, $inicioEvolucao->format('Y-m-d'), $fimMes->format('Y-m-d')) as $linha) {
            $porMes[$linha['mes']] = $linha;
        }

        $evolucao = ['rotulos' => [], 'receitas' => [], 'despesas' => [], 'resultado' => []];

        for ($i = 0; $i < self::MESES_NA_EVOLUCAO; $i++) {
            $mes = $inicioEvolucao->modify("+{$i} months");
            $chave = $mes->format('Y-m');

            $receitas = (float) ($porMes[$chave]['receitas'] ?? 0);
            $despesas = (float) ($porMes[$chave]['despesas'] ?? 0);

            $evolucao['rotulos'][] = self::MESES_CURTOS[(int) $mes->format('n')] . '/' . $mes->format('y');
            $evolucao['receitas'][] = round($receitas, 2);
            $evolucao['despesas'][] = round($despesas, 2);
            $evolucao['resultado'][] = round($receitas - $despesas, 2);
        }

        return $evolucao;
    }

    // metas ainda em andamento primeiro (a mais próxima do prazo na frente), depois as concluídas
    private function getMetas(int $usuarioId): array
    {
        $metas = $this->metaService->getMetas($usuarioId);

        usort($metas, function ($a, $b) {
            return [$a->eConcluida(), $a->getDataLimite()] <=> [$b->eConcluida(), $b->getDataLimite()];
        });

        $concluidas = 0;
        $lista = [];

        foreach ($metas as $meta) {
            if ($meta->eConcluida()) {
                $concluidas++;
            }

            if (count($lista) < self::METAS_NO_PAINEL) {
                $lista[] = [
                    'nome' => $meta->getNomeMeta(),
                    'progresso' => min(100, (float) $meta->getProgresso()),
                    'valorAtual' => $meta->getValorAtual(),
                    'valorAlvo' => $meta->getValorAlvo(),
                    'falta' => max(0, round($meta->getValorAlvo() - $meta->getValorAtual(), 2)),
                    'concluida' => $meta->eConcluida(),
                    'dataLimite' => date('d/m/Y', strtotime($meta->getDataLimite())),
                ];
            }
        }

        return ['lista' => $lista, 'total' => count($metas), 'concluidas' => $concluidas];
    }

    // parcelas do mês. Pago = parcelas lançadas com data no mês.
    // No mês atual, a pagar = parcelas ainda não lançadas que vencem até o fim do mês (inclui as vencidas sem lançar).
    private function getParcelas(int $usuarioId, string $inicio, string $fim, bool $ehMesAtual, DateTimeImmutable $hoje): array
    {
        $dataHoje = $hoje->format('Y-m-d');

        $totalPago = 0.0;
        $pagas = [];

        foreach ($this->transacaoRepository->getParcelasDoPeriodo($usuarioId, $inicio, $fim) as $parcela) {
            $totalPago += (float) $parcela['valor'];
            $pagas[] = [
                'data' => date('d/m', strtotime($parcela['data_registro'])),
                'valor' => (float) $parcela['valor'],
                'descricao' => $parcela['descricao'] ?: 'Parcela',
                'categoria' => $parcela['nome_categoria'],
                'atrasada' => false,
            ];
        }

        if (!$ehMesAtual) {
            return [
                'itens' => array_slice($pagas, 0, self::ITENS_NA_LISTA),
                'totalAPagar' => 0.0,
                'totalPago' => round($totalPago, 2),
            ];
        }

        $totalAPagar = 0.0;
        $pendentes = [];

        foreach ($this->parcelamentoRepository->getParcelamentosPorUsuario($usuarioId) as $parcelamento) {
            for ($numero = $parcelamento->getParcelasPagas() + 1; $numero <= $parcelamento->getQuantidadeParcelas(); $numero++) {
                $vencimento = $parcelamento->getDataVencimento($numero);

                if ($vencimento > $fim) {
                    break;
                }

                $totalAPagar += $parcelamento->getValorParcela();
                $pendentes[] = [
                    'vencimento' => $vencimento,
                    'data' => date('d/m', strtotime($vencimento)),
                    'valor' => $parcelamento->getValorParcela(),
                    'descricao' => $parcelamento->getDescricaoParcela($numero),
                    'categoria' => $parcelamento->getNomeCategoria(),
                    'atrasada' => $vencimento < $dataHoje,
                ];
            }
        }

        usort($pendentes, fn($a, $b) => strcmp($a['vencimento'], $b['vencimento']));

        return [
            'itens' => array_slice($pendentes, 0, self::ITENS_NA_LISTA),
            'totalAPagar' => round($totalAPagar, 2),
            'totalPago' => round($totalPago, 2),
        ];
    }

    // média gasta por dia, maior despesa e estimativa de fechamento do mês (só no mês atual)
    private function getRitmo(int $usuarioId, string $inicio, string $fim, float $despesas, bool $ehMesAtual, DateTimeImmutable $hoje, DateTimeImmutable $fimMes): array
    {
        $diasNoMes = (int) $fimMes->format('j');
        $diasDecorridos = $ehMesAtual ? (int) $hoje->format('j') : $diasNoMes;
        $mediaDiaria = $diasDecorridos > 0 ? $despesas / $diasDecorridos : 0.0;

        $estimativa = null;

        if ($ehMesAtual && $diasDecorridos >= self::DIAS_MINIMOS_PARA_ESTIMAR) {
            $estimativa = round($mediaDiaria * $diasNoMes, 2);
        }

        return [
            'mediaDiaria' => round($mediaDiaria, 2),
            'diasDecorridos' => $diasDecorridos,
            'maiorDespesa' => $this->transacaoRepository->getMaiorDespesa($usuarioId, $inicio, $fim),
            'estimativa' => $estimativa,
            'diasParaEstimar' => self::DIAS_MINIMOS_PARA_ESTIMAR,
        ];
    }
}
