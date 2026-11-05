<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use Exception;
use PDO;

// RF 12: limites mensais de gastos por categoria
class LimiteCategoriaRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    // categorias de despesa com o limite do usuário (se ele definiu) e o gasto do período, sem contar os futuros
    public function getCategoriasComGasto(int $usuarioId, string $inicio, string $fim, string $categoriaAporte): array
    {
        $sql = "SELECT c.id, c.nome, c.icone, l.valor_limite, COALESCE(SUM(r.valor), 0) AS gasto " .
            "FROM categorias c " .
            "LEFT JOIN limites_categoria l ON l.categoria_id = c.id AND l.usuario_id = :usuarioLimite " .
            "LEFT JOIN registro_financeiro r ON r.categoria_id = c.id AND r.usuario_id = :usuarioGasto " .
            "AND r.tipo = 'despesa' AND r.data_registro BETWEEN :inicio AND :fim " .
            "AND r.data_registro <= CURRENT_DATE " .
            "WHERE c.tipo = 'despesa' AND c.nome <> :aporte " .
            "GROUP BY c.id, c.nome, c.icone, l.valor_limite ORDER BY c.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioLimite', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':usuarioGasto', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->bindValue(':aporte', $categoriaAporte);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // grava todos os limites de uma vez: valor nulo remove o limite da categoria
    public function salvarLimites(int $usuarioId, array $limites): bool
    {
        try {
            $this->connection->beginTransaction();

            foreach ($limites as $categoriaId => $valor) {
                if ($valor === null) {
                    $sql = "DELETE FROM limites_categoria WHERE usuario_id = :usuarioId AND categoria_id = :categoriaId";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
                    $stmt->bindValue(':categoriaId', $categoriaId, PDO::PARAM_INT);
                    $stmt->execute();

                    continue;
                }

                $valorTexto = number_format($valor, 2, '.', '');

                $sql = "INSERT INTO limites_categoria (usuario_id, categoria_id, valor_limite) " .
                    "VALUES (:usuarioId, :categoriaId, :valor) " .
                    "ON DUPLICATE KEY UPDATE valor_limite = :valorNovo";
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
                $stmt->bindValue(':categoriaId', $categoriaId, PDO::PARAM_INT);
                $stmt->bindValue(':valor', $valorTexto);
                $stmt->bindValue(':valorNovo', $valorTexto);
                $stmt->execute();
            }

            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao salvar limites de categoria: " . $e->getMessage());

            return false;
        }
    }
}
