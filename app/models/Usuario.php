<?php

namespace app\models;

use DateTimeImmutable;

class Usuario
{
    private int $id;
    private string $nome;
    private string $nickname;
    private string $email;
    private string $senha;
    private string $dataNascimento;
    private ?string $foto;
    private string $status;
    private string $perfil;
    private string $tema = 'claro';
    private ?string $telefone;
    private DateTimeImmutable $criadoEm;

    public function __construct(
        int $id,
        string $nome,
        string $nickname,
        string $email,
        string $senha,
        string $dataNascimento,
        string $perfil = 'comum',
        string $status = 'ativo',
        ?string $foto = null,
        string $tema = 'claro',
        ?string $telefone = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->nickname = $nickname;
        $this->email = $email;
        $this->senha = $senha;
        $this->dataNascimento = $dataNascimento;
        $this->perfil = $perfil;
        $this->status = $status;
        $this->foto = $foto;
        $this->tema = $tema;
        $this->telefone = $telefone;
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function getDataNascimento(): string
    {
        return $this->dataNascimento;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPerfil(): string
    {
        return $this->perfil;
    }

    public function getTema(): string
    {
        return $this->tema;
    }

    // somente os digitos (DDD + numero), ou null quando nao informado
    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function getTelefoneFormatado(): string
    {
        return self::formatarTelefone($this->telefone);
    }

    // (45) 99999-9999 para 11 digitos, (45) 3523-1234 para 10; outro tamanho volta como veio
    public static function formatarTelefone(?string $telefone): string
    {
        $t = $telefone ?? '';

        if (strlen($t) === 11) {
            return '(' . substr($t, 0, 2) . ') ' . substr($t, 2, 5) . '-' . substr($t, 7);
        }

        if (strlen($t) === 10) {
            return '(' . substr($t, 0, 2) . ') ' . substr($t, 2, 4) . '-' . substr($t, 6);
        }

        return $t;
    }

    public function eAdmin(): bool
    {
        return $this->perfil === 'administrador';
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public static function arrayParaObjeto(array $usuario): self
    {
        return new self(
            $usuario['id'],
            $usuario['nome'],
            $usuario['nickname'],
            $usuario['email'],
            $usuario['senha'],
            $usuario['data_nascimento'],
            $usuario['perfil'],
            $usuario['status'],
            $usuario['foto'] ?? null,
            $usuario['tema'] ?? 'claro',
            $usuario['telefone'] ?? null
        );
    }
}