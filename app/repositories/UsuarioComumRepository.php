<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\UsuarioComum;
use PDO;

class UsuarioComumRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function criarParaUsuario(int $usuarioId): bool
    {
        $sql = "INSERT INTO usuario_comum (usuario_id) VALUES (:usuarioId)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);

        return $stmt->execute();
    }

    public function getPorUsuarioId(int $usuarioId)
    {
        $sql = "SELECT * FROM usuario_comum WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();
        $usuarioComum = $stmt->fetch();

        if ($usuarioComum == null) {
            return false;
        }

        return UsuarioComum::arrayParaObjeto($usuarioComum);
    }

    public function atualizarXp(UsuarioComum $usuarioComum): bool
    {
        $sql = "UPDATE usuario_comum SET xp_total = :xpTotal, nivel = :nivel WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':xpTotal', $usuarioComum->getXpTotal());
        $stmt->bindValue(':nivel', $usuarioComum->getNivel());
        $stmt->bindValue(':usuarioId', $usuarioComum->getUsuarioId());

        return $stmt->execute();
    }

    public function atualizarStreak(UsuarioComum $usuarioComum): bool
    {
        $sql = "UPDATE usuario_comum SET streak_atual = :streakAtual, maior_streak = :maiorStreak, " .
            "ultimo_acesso = :ultimoAcesso WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':streakAtual', $usuarioComum->getStreakAtual());
        $stmt->bindValue(':maiorStreak', $usuarioComum->getMaiorStreak());
        $stmt->bindValue(':ultimoAcesso', $usuarioComum->getUltimoAcesso());
        $stmt->bindValue(':usuarioId', $usuarioComum->getUsuarioId());

        return $stmt->execute();
    }

    public function atualizarVisibilidade(UsuarioComum $usuarioComum): bool
    {
        $sql = "UPDATE usuario_comum SET visibilidade_ranking = :visibilidadeRanking WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':visibilidadeRanking', $usuarioComum->getVisibilidadeRanking(), PDO::PARAM_BOOL);
        $stmt->bindValue(':usuarioId', $usuarioComum->getUsuarioId());

        return $stmt->execute();
    }

    // a conexão é única (ConnectionFactory), então a transação vale para todos os repositories
    public function iniciarTransacao(): void
    {
        $this->connection->beginTransaction();
    }

    public function confirmarTransacao(): void
    {
        $this->connection->commit();
    }

    public function desfazerTransacao(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}