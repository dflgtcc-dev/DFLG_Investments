<?php

namespace app\models;

class UsuarioAdministrador
{
    public const NIVEL_CHEFE = 1;
    public const NIVEL_COMUM = 2;

    private int $usuarioId;
    private string $criadoEm;
    private int $nivelPermissao;

    public function __construct(int $usuarioId, string $criadoEm = '', int $nivelPermissao = self::NIVEL_COMUM)
    {
        $this->usuarioId = $usuarioId;
        $this->criadoEm = $criadoEm;
        $this->nivelPermissao = $nivelPermissao;
    }

    public function getNivelPermissao(): int
    {
        return $this->nivelPermissao;
    }

    public function eChefe(): bool
    {
        return $this->nivelPermissao === self::NIVEL_CHEFE;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getCriadoEm(): string
    {
        return $this->criadoEm;
    }

    public static function arrayParaObjeto(array $usuarioAdministrador): self
    {
        return new self(
            $usuarioAdministrador['usuario_id'],
            $usuarioAdministrador['criado_em'],
            (int) ($usuarioAdministrador['nivel_permissao'] ?? self::NIVEL_COMUM)
        );
    }
}