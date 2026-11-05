<?php

namespace app\models;

class Transacao
{
    private int $id;
    private int $usuarioId;
    private ?int $categoriaId;
    private string $tipo;
    private float $valor;
    private string $dataRegistro;
    private ?string $descricao;
    private ?string $nomeCategoria;
    private ?int $metaId;
    private ?int $parcelamentoId;

    public function __construct(
        int $id,
        int $usuarioId,
        ?int $categoriaId,
        string $tipo,
        float $valor,
        string $dataRegistro,
        ?string $descricao = null,
        ?string $nomeCategoria = null,
        ?int $metaId = null,
        ?int $parcelamentoId = null
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->categoriaId = $categoriaId;
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->dataRegistro = $dataRegistro;
        $this->descricao = $descricao;
        $this->nomeCategoria = $nomeCategoria;
        $this->metaId = $metaId;
        $this->parcelamentoId = $parcelamentoId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getCategoriaId(): ?int
    {
        return $this->categoriaId;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getDataRegistro(): string
    {
        return $this->dataRegistro;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getNomeCategoria(): ?string
    {
        return $this->nomeCategoria;
    }

    // RF 13: despesa lançada pelo botão "Lançar parcela"; só é alterada pela tela de parcelamentos
    public function eParcela(): bool
    {
        return $this->parcelamentoId !== null;
    }

    // RF 14: despesa gerada por um aporte em uma meta
    public function eAporteMeta(): bool
    {
        return $this->metaId !== null;
    }

    public function eReceita(): bool
    {
        return $this->tipo === 'receita';
    }

    public static function arrayParaObjeto(array $transacao): self
    {
        return new self(
            $transacao['id'],
            $transacao['usuario_id'],
            $transacao['categoria_id'] !== null ? (int) $transacao['categoria_id'] : null,
            $transacao['tipo'],
            (float) $transacao['valor'],
            $transacao['data_registro'],
            $transacao['descricao'],
            $transacao['nome_categoria'] ?? null,
            isset($transacao['meta_id']) ? (int) $transacao['meta_id'] : null,
            isset($transacao['parcelamento_id']) ? (int) $transacao['parcelamento_id'] : null
        );
    }
}