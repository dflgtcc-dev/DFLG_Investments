<?php

namespace app\models;

use DateTimeImmutable;

class UsuarioComum
{
    private int $usuarioId;
    private int $xpTotal;
    private int $nivel;
    private int $streakAtual;
    private int $maiorStreak;
    private bool $visibilidadeRanking;
    private ?string $ultimoAcesso;

    public function __construct(
        int $usuarioId,
        int $xpTotal = 0,
        int $nivel = 1,
        int $streakAtual = 0,
        int $maiorStreak = 0,
        ?string $ultimoAcesso = null,
        bool $visibilidadeRanking = true
    ) {
        $this->usuarioId = $usuarioId;
        $this->xpTotal = $xpTotal;
        $this->nivel = $nivel;
        $this->streakAtual = $streakAtual;
        $this->maiorStreak = $maiorStreak;
        $this->ultimoAcesso = $ultimoAcesso;
        $this->visibilidadeRanking = $visibilidadeRanking;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getXpTotal(): int
    {
        return $this->xpTotal;
    }

    public function setXpTotal(int $xpTotal): self
    {
        $this->xpTotal = $xpTotal;

        return $this;
    }

    public function getNivel(): int
    {
        return $this->nivel;
    }

    public function setNivel(int $nivel): self
    {
        $this->nivel = $nivel;

        return $this;
    }

    public function getStreakAtual(): int
    {
        return $this->streakAtual;
    }

    // RN 03: a sequencia so vale se o ultimo acesso foi hoje ou ontem; antes disso ela ja foi quebrada
    public function getStreakEfetivo(): int
    {
        $hoje = (new DateTimeImmutable())->format('Y-m-d');
        $ontem = (new DateTimeImmutable('-1 day'))->format('Y-m-d');

        if ($this->ultimoAcesso === $hoje || $this->ultimoAcesso === $ontem) {
            return $this->streakAtual;
        }

        return 0;
    }

    public function setStreakAtual(int $streakAtual): self
    {
        $this->streakAtual = $streakAtual;

        return $this;
    }

    public function getMaiorStreak(): int
    {
        return $this->maiorStreak;
    }

    public function setMaiorStreak(int $maiorStreak): self
    {
        $this->maiorStreak = $maiorStreak;

        return $this;
    }

    public function getVisibilidadeRanking(): bool
    {
        return $this->visibilidadeRanking;
    }

    public function setVisibilidadeRanking(bool $visibilidadeRanking): self
    {
        $this->visibilidadeRanking = $visibilidadeRanking;

        return $this;
    }

    public static function arrayParaObjeto(array $usuarioComum): self
    {
        return new self(
            $usuarioComum['usuario_id'],
            (int) $usuarioComum['xp_total'],
            (int) $usuarioComum['nivel'],
            (int) $usuarioComum['streak_atual'],
            (int) $usuarioComum['maior_streak'],
            $usuarioComum['ultimo_acesso'],
            (bool) $usuarioComum['visibilidade_ranking']
        );
    }

    public function getUltimoAcesso(): ?string
    {
        return $this->ultimoAcesso;
    }

    public function setUltimoAcesso(?string $ultimoAcesso): self
    {
        $this->ultimoAcesso = $ultimoAcesso;

        return $this;
    }

}