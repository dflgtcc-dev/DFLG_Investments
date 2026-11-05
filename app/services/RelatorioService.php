<?php

namespace app\services;

use app\repositories\TransacaoRepository;

// RF 11: monta os extratos financeiros mensal e anual
class RelatorioService
{
    public const MESES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
        7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    private TransacaoRepository $repository;

    public function __construct()
    {
        $this->repository = new TransacaoRepository();
    }

    // anos que o usuário pode consultar: do ano do primeiro registro até o ano atual (mais recente primeiro)
    public function getAnosDisponiveis(int $usuarioId): array
    {
        $anoAtual = (int) date('Y');
        $primeiroAno = $this->repository->getPrimeiroAno($usuarioId) ?? $anoAtual;

        return range($anoAtual, min($primeiroAno, $anoAtual));
    }

    // extrato de um mês: saldo inicial, cada lançamento com o saldo depois dele, totais e saldo final
    public function getExtratoMensal(int $usuarioId, int $ano, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $saldoInicial = $this->repository->getSaldoAnteriorA($usuarioId, $inicio);
        $saldo = $saldoInicial;
        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        $lancamentos = [];

        foreach ($this->repository->getTransacoesPorPeriodo($usuarioId, $inicio, $fim) as $transacao) {
            if ($transacao->eReceita()) {
                $totalReceitas += $transacao->getValor();
                $saldo += $transacao->getValor();
            } else {
                $totalDespesas += $transacao->getValor();
                $saldo -= $transacao->getValor();
            }

            $lancamentos[] = ['transacao' => $transacao, 'saldo' => round($saldo, 2)];
        }

        return [
            'titulo' => self::MESES[$mes] . ' de ' . $ano,
            'saldoInicial' => round($saldoInicial, 2),
            'totalReceitas' => round($totalReceitas, 2),
            'totalDespesas' => round($totalDespesas, 2),
            'saldoFinal' => round($saldo, 2),
            'lancamentos' => $lancamentos,
        ];
    }

    // extrato de um ano: uma linha por mês (até o mês atual, se for o ano corrente) com o saldo acumulado
    public function getExtratoAnual(int $usuarioId, int $ano): array
    {
        $anoAtual = (int) date('Y');
        $ultimoMes = $ano < $anoAtual ? 12 : ($ano === $anoAtual ? (int) date('n') : 0);

        $saldoInicial = $this->repository->getSaldoAnteriorA($usuarioId, sprintf('%04d-01-01', $ano));
        $saldo = $saldoInicial;
        $totalReceitas = 0.0;
        $totalDespesas = 0.0;

        $resumo = [];

        foreach ($this->repository->getResumoPorMes($usuarioId, $ano) as $linha) {
            $resumo[(int) $linha['mes']] = $linha;
        }

        $meses = [];

        for ($mes = 1; $mes <= $ultimoMes; $mes++) {
            $receitas = (float) ($resumo[$mes]['receitas'] ?? 0);
            $despesas = (float) ($resumo[$mes]['despesas'] ?? 0);
            $saldo += $receitas - $despesas;
            $totalReceitas += $receitas;
            $totalDespesas += $despesas;

            $meses[] = [
                'mes' => self::MESES[$mes],
                'receitas' => round($receitas, 2),
                'despesas' => round($despesas, 2),
                'resultado' => round($receitas - $despesas, 2),
                'saldoAcumulado' => round($saldo, 2),
            ];
        }

        return [
            'titulo' => (string) $ano,
            'saldoInicial' => round($saldoInicial, 2),
            'totalReceitas' => round($totalReceitas, 2),
            'totalDespesas' => round($totalDespesas, 2),
            'saldoFinal' => round($saldo, 2),
            'meses' => $meses,
        ];
    }
}
