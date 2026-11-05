<?php

namespace app\models;

class Meta
{
    private int $id;
    private int $usuarioId;
    private string $nomeMeta;
    private string $tipoMeta;
    private float $valorAlvo;
    private float $valorAtual;
    private string $dataLimite;

    public function __construct(
        int $id,
        int $usuarioId,
        string $nomeMeta,
        string $tipoMeta,
        float $valorAlvo,
        float $valorAtual = 0.0,
        string $dataLimite = ''
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->nomeMeta = $nomeMeta;
        $this->tipoMeta = $tipoMeta;
        $this->valorAlvo = $valorAlvo;
        $this->valorAtual = $valorAtual;
        $this->dataLimite = $dataLimite;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getNomeMeta(): string
    {
        return $this->nomeMeta;
    }

    public function getTipoMeta(): string
    {
        return $this->tipoMeta;
    }

    public function getValorAlvo(): float
    {
        return $this->valorAlvo;
    }

    public function getValorAtual(): float
    {
        return $this->valorAtual;
    }

    public function getDataLimite(): string
    {
        return $this->dataLimite;
    }

    // percentual já conquistado da meta, usado nas barras de progresso das views
    public function getProgresso(): float
    {
        if ($this->valorAlvo <= 0) {
            return 0.0;
        }

        $progresso = ($this->valorAtual / $this->valorAlvo) * 100;

        return $progresso > 100 ? 100.0 : round($progresso, 2);
    }

    public function eConcluida(): bool
    {
        return $this->valorAtual >= $this->valorAlvo;
    }

    public static function arrayParaObjeto(array $meta): self
    {
        return new self(
            $meta['id'],
            $meta['usuario_id'],
            $meta['nome_meta'],
            $meta['tipo_meta'],
            (float) $meta['valor_alvo'],
            (float) $meta['valor_atual'],
            $meta['data_limite']
        );
    }
}
