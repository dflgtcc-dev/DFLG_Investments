<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\UsuarioAdministrador;
use PDO;

class UsuarioAdministradorRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function criarParaUsuario(int $usuarioId, int $nivelPermissao = UsuarioAdministrador::NIVEL_COMUM): bool
    {
        $sql = "INSERT INTO administradores (usuario_id, nivel_permissao) VALUES (:usuarioId, :nivel)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':nivel', $nivelPermissao);

        return $stmt->execute();
    }

    public function getPorUsuarioId(int $usuarioId)
    {
        $sql = "SELECT * FROM administradores WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();
        $usuarioAdministrador = $stmt->fetch();

        if ($usuarioAdministrador == null) {
            return false;
        }

        return UsuarioAdministrador::arrayParaObjeto($usuarioAdministrador);
    }
}