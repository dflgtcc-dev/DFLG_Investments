<?php

namespace app\models;

class HistoricoXp
{
    private int $id;
    private int $usuarioId;
    private string $acao;
    private int $xp;
    private string $dataConcessao;

    public function __construct(
        int $id,
        int $usuarioId,
        string $acao,
        int $xp,
        string $dataConcessao
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->acao = $acao;
        $this->xp = $xp;
        $this->dataConcessao = $dataConcessao;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getAcao(): string
    {
        return $this->acao;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function getDataConcessao(): string
    {
        return $this->dataConcessao;
    }

    public static function arrayParaObjeto(array $historicoXp): self
    {
        return new self(
            $historicoXp['id'],
            $historicoXp['usuario_id'],
            $historicoXp['acao'],
            (int) $historicoXp['xp'],
            $historicoXp['data_concessao']
        );
    }
}