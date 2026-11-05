<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\HistoricoXp;
use PDO;

class HistoricoXpRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function registrar(HistoricoXp $historicoXp): bool
    {
        $sql = "INSERT INTO historico_xp (usuario_id, acao, xp, data_concessao) " .
            "VALUES (:usuarioId, :acao, :xp, :dataConcessao)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $historicoXp->getUsuarioId());
        $stmt->bindValue(':acao', $historicoXp->getAcao());
        $stmt->bindValue(':xp', $historicoXp->getXp());
        $stmt->bindValue(':dataConcessao', $historicoXp->getDataConcessao());

        return $stmt->execute();
    }

    // com data: verifica se a ação já deu XP naquele dia; sem data: se já deu XP alguma vez
    public function existeConcessao(int $usuarioId, string $acao, ?string $data = null): bool
    {
        $sql = "SELECT COUNT(*) FROM historico_xp WHERE usuario_id = :usuarioId AND acao = :acao";

        if ($data) {
            $sql .= " AND data_concessao = :data";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':acao', $acao);

        if ($data) {
            $stmt->bindValue(':data', $data);
        }

        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}