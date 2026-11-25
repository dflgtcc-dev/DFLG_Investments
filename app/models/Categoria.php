<?php

namespace app\models;

class Categoria
{
    private int $id;
    private string $nome;
    private string $tipo;

    public function __construct(
        int $id,
        string $nome,
        string $tipo = 'despesa'
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->tipo = $tipo;
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
            $categoria['tipo']
        );
    }
}
