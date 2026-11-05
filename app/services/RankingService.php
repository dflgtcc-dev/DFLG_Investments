<?php

namespace app\services;

use app\repositories\RankingRepository;
use DateTimeImmutable;

// RF 10: monta os rankings semanal, mensal e geral
class RankingService
{
    public const PERIODOS = ['semanal' => 'Semanal', 'mensal' => 'Mensal', 'geral' => 'Geral'];

    // quantos usuários aparecem em cada ranking
    private const LIMITE = 20;

    private RankingRepository $repository;
    private GamificacaoService $gamificacaoService;

    public function __construct()
    {
        $this->repository = new RankingRepository();
        $this->gamificacaoService = new GamificacaoService();
    }

    // usuários na ordem do ranking (com a posição de cada um), o texto do período
    // e a situação de quem está olhando o ranking
    public function getRanking(string $periodo, int $usuarioId): array
    {
        $hoje = new DateTimeImmutable('today');

        if ($periodo === 'semanal') {
            // semana do calendário: de segunda a domingo
            $inicio = $hoje->modify('monday this week');
            $fim = $inicio->modify('+6 days');

            $usuarios = $this->repository->getRankingPorPeriodo($inicio->format('Y-m-d'), $fim->format('Y-m-d'), self::LIMITE);
            $posicao = $this->repository->getPosicaoPorPeriodo($usuarioId, $inicio->format('Y-m-d'), $fim->format('Y-m-d'));
            $descricao = 'Semana de ' . $inicio->format('d/m') . ' a ' . $fim->format('d/m/Y');
        } elseif ($periodo === 'mensal') {
            // mês do calendário: do dia 1 ao último dia
            $inicio = $hoje->modify('first day of this month');
            $fim = $hoje->modify('last day of this month');

            $usuarios = $this->repository->getRankingPorPeriodo($inicio->format('Y-m-d'), $fim->format('Y-m-d'), self::LIMITE);
            $posicao = $this->repository->getPosicaoPorPeriodo($usuarioId, $inicio->format('Y-m-d'), $fim->format('Y-m-d'));
            $descricao = RelatorioService::MESES[(int) $inicio->format('n')] . ' de ' . $inicio->format('Y');
        } else {
            $usuarios = $this->repository->getRankingGeral(self::LIMITE);
            $posicao = $this->repository->getPosicaoGeral($usuarioId);
            $descricao = 'Todo o período';
        }

        $ranking = [];

        foreach ($usuarios as $indice => $usuario) {
            $ranking[] = [
                'posicao' => $indice + 1,
                'id' => (int) $usuario['id'],
                'nickname' => $usuario['nickname'],
                'nivel' => (int) $usuario['nivel'],
                'xp' => (int) $usuario['xp'],
            ];
        }

        // quem está entre os primeiros já aparece na lista; quem está fora vê a própria posição à parte
        $minhaPosicao = null;
        $aviso = null;

        if ($posicao !== null) {
            if ($posicao['posicao'] > self::LIMITE) {
                $minhaPosicao = $posicao;
            }
        } else {
            $gamificacao = $this->gamificacaoService->getGamificacao($usuarioId);

            // administrador não tem gamificação e não participa do ranking
            if ($gamificacao) {
                $aviso = $gamificacao->getVisibilidadeRanking() ? 'sem_xp' : 'oculto';
            }
        }

        return [
            'periodo' => $periodo,
            'descricao' => $descricao,
            'usuarios' => $ranking,
            'minhaPosicao' => $minhaPosicao,
            'aviso' => $aviso,
        ];
    }
}