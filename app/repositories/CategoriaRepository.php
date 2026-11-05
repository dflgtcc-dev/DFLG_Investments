<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Categoria;
use PDO;

class CategoriaRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function getCategorias(?string $tipo = null): array
    {
        $sql = "SELECT * FROM categorias";

        if ($tipo !== null) {
            $sql .= " WHERE tipo = :tipo";
        }

        $sql .= " ORDER BY tipo ASC, nome ASC";

        $stmt = $this->connection->prepare($sql);

        if ($tipo !== null) {
            $stmt->bindValue(':tipo', $tipo);
        }

        $stmt->execute();

        $categorias = [];

        foreach ($stmt->fetchAll() as $categoria) {
            $categorias[] = Categoria::arrayParaObjeto($categoria);
        }

        return $categorias;
    }

    // usado para achar a categoria "Metas" dos aportes
    public function getCategoriaPorNome(string $nome, string $tipo)
    {
        $sql = "SELECT * FROM categorias WHERE nome = :nome AND tipo = :tipo LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();

        $categoria = $stmt->fetch();

        if ($categoria == null) {
            return false;
        }

        return Categoria::arrayParaObjeto($categoria);
    }

    public function getCategoria(int $id)
    {
        $sql = "SELECT * FROM categorias WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $categoria = $stmt->fetch();

        if ($categoria == null) {
            return false;
        }

        return Categoria::arrayParaObjeto($categoria);
    }
}