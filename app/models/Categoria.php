<?php

namespace app\models;

class Categoria
{
    private int $id;
    private string $nome;
    private ?string $icone;
    private string $tipo;
    private ?float $limiteOrcamento;

    public function __construct(
        int $id,
        string $nome,
        ?string $icone = null,
        string $tipo = 'despesa',
        ?float $limiteOrcamento = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->icone = $icone;
        $this->tipo = $tipo;
        $this->limiteOrcamento = $limiteOrcamento;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public static function arrayParaObjeto(array $categoria): self //transforma o array em objeto categoria
    {
        return new self(
            $categoria['id'],
            $categoria['nome'],
            $categoria['icone'],
            $categoria['tipo'],
            $categoria['limite_orcamento'] !== null ? (float) $categoria['limite_orcamento'] : null
        );
    }
}
