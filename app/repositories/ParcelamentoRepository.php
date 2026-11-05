<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Parcelamento;
use PDO;

class ParcelamentoRepository
{
    private PDO $connection;

    // parcelas pagas = parcelas lançadas (cada uma é um registro com parcelamento_id e numero_parcela)
    private const SELECT_BASE = "SELECT p.*, c.nome AS nome_categoria, " .
        "COALESCE(p.valor_parcela, p.valor_total / NULLIF(p.quantidade_parcelas, 0)) AS valor_parcela, " .
        "COUNT(r.id) AS parcelas_pagas, COALESCE(SUM(r.valor), 0) AS valor_pago FROM parcelamentos p " .
        "LEFT JOIN categorias c ON c.id = p.categoria_id " .
        "LEFT JOIN registro_financeiro r ON r.parcelamento_id = p.id ";

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function getParcelamentosPorUsuario(int $usuarioId): array
    {
        $sql = self::SELECT_BASE .
            "WHERE p.usuario_id = :usuarioId GROUP BY p.id ORDER BY p.data_primeira_parcela DESC, p.id DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $parcelamentos = [];

        foreach ($stmt->fetchAll() as $parcelamento) {
            $parcelamentos[] = Parcelamento::arrayParaObjeto($parcelamento);
        }

        return $parcelamentos;
    }

    // sempre filtra pelo dono, pra um usuário não acessar o parcelamento de outro
    public function getParcelamento(int $id, int $usuarioId)
    {
        $sql = self::SELECT_BASE . "WHERE p.id = :id AND p.usuario_id = :usuarioId GROUP BY p.id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $parcelamento = $stmt->fetch();

        if ($parcelamento == null) {
            return false;
        }

        return Parcelamento::arrayParaObjeto($parcelamento);
    }

    // o cadastro não lança nenhuma parcela: elas entram pelo botão "Lançar parcela"
    public function saveParcelamento(Parcelamento $parcelamento): bool
    {
        try {
            $sql = "INSERT INTO parcelamentos " .
                "(usuario_id, categoria_id, descricao, valor_total, valor_parcela, quantidade_parcelas, parcelas_pagas, data_primeira_parcela, tag) " .
                "VALUES (:usuarioId, :categoriaId, :descricao, :valorTotal, :valorParcela, :quantidadeParcelas, 0, :dataPrimeiraParcela, :tag)";
            $stmt = $this->connection->prepare($sql);
            $this->bindParcelamento($stmt, $parcelamento);
            $stmt->bindValue(':dataPrimeiraParcela', $parcelamento->getDataPrimeiraParcela());

            return $stmt->execute();
        } catch (\Throwable $erro) {
            error_log("Erro ao salvar parcelamento: " . $erro->getMessage());

            return false;
        }
    }

    // a data da 1ª parcela não é alterada. Categoria e descrição valem também para as parcelas já lançadas;
    // o valor novo vale só para as próximas (as lançadas guardam o valor da época)
    public function updateParcelamento(Parcelamento $parcelamento): bool
    {
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE parcelamentos SET categoria_id = :categoriaId, descricao = :descricao, valor_total = :valorTotal, " .
                "valor_parcela = :valorParcela, quantidade_parcelas = :quantidadeParcelas, tag = :tag " .
                "WHERE id = :id AND usuario_id = :usuarioId";

            $stmt = $this->connection->prepare($sql);
            $this->bindParcelamento($stmt, $parcelamento);
            $stmt->bindValue(':id', $parcelamento->getId());
            $stmt->execute();

            $lancadas = $this->connection->prepare(
                "SELECT id, numero_parcela FROM registro_financeiro WHERE parcelamento_id = :id AND usuario_id = :usuarioId"
            );
            $lancadas->execute(['id' => $parcelamento->getId(), 'usuarioId' => $parcelamento->getUsuarioId()]);

            $atualizar = $this->connection->prepare(
                "UPDATE registro_financeiro SET categoria_id = :categoriaId, descricao = :descricao WHERE id = :id"
            );

            foreach ($lancadas->fetchAll() as $registro) {
                $atualizar->execute([
                    'categoriaId' => $parcelamento->getCategoriaId(),
                    'descricao' => $parcelamento->getDescricaoParcela((int) $registro['numero_parcela']),
                    'id' => $registro['id'],
                ]);
            }

            $this->connection->commit();

            return true;
        } catch (\Throwable $erro) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao atualizar parcelamento: " . $erro->getMessage());

            return false;
        }
    }

    // as parcelas já lançadas foram pagas: continuam nas transações como despesas comuns,
    // para não mudar o saldo e os relatórios dos meses que já passaram
    public function deleteParcelamento(int $id, int $usuarioId): bool
    {
        try {
            $this->connection->beginTransaction();

            $desvincular = $this->connection->prepare(
                "UPDATE registro_financeiro SET parcelamento_id = NULL, numero_parcela = NULL " .
                "WHERE parcelamento_id = :id AND usuario_id = :usuarioId"
            );
            $desvincular->execute(['id' => $id, 'usuarioId' => $usuarioId]);

            $stmt = $this->connection->prepare("DELETE FROM parcelamentos WHERE id = :id AND usuario_id = :usuarioId");
            $stmt->execute(['id' => $id, 'usuarioId' => $usuarioId]);

            $this->connection->commit();

            return $stmt->rowCount() === 1;
        } catch (\Throwable $erro) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao excluir parcelamento: " . $erro->getMessage());

            return false;
        }
    }

    // lança a parcela de número $numero. A chave única (parcelamento_id, numero_parcela)
    // impede lançar a mesma parcela duas vezes, mesmo com dois cliques ao mesmo tempo
    public function lancarParcela(Parcelamento $parcelamento, int $numero, string $dataRegistro): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO registro_financeiro " .
                "(usuario_id, categoria_id, tipo, valor, data_registro, descricao, parcelamento_id, numero_parcela) " .
                "VALUES (:usuarioId, :categoriaId, 'despesa', :valor, :dataRegistro, :descricao, :parcelamentoId, :numero)"
            );

            return $stmt->execute([
                'usuarioId' => $parcelamento->getUsuarioId(),
                'categoriaId' => $parcelamento->getCategoriaId(),
                'valor' => number_format($parcelamento->getValorParcela(), 2, '.', ''),
                'dataRegistro' => $dataRegistro,
                'descricao' => $parcelamento->getDescricaoParcela($numero),
                'parcelamentoId' => $parcelamento->getId(),
                'numero' => $numero,
            ]);
        } catch (\Throwable $erro) {
            error_log("Erro ao lançar parcela: " . $erro->getMessage());

            return false;
        }
    }

    // remove só a última parcela lançada (as parcelas ficam sempre em sequência, sem buracos)
    public function desfazerUltimaParcela(int $parcelamentoId, int $usuarioId, int $numero): bool
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM registro_financeiro WHERE parcelamento_id = :id AND usuario_id = :usuarioId AND numero_parcela = :numero"
        );
        $stmt->execute(['id' => $parcelamentoId, 'usuarioId' => $usuarioId, 'numero' => $numero]);

        return $stmt->rowCount() === 1;
    }

    private function bindParcelamento($stmt, Parcelamento $parcelamento): void
    {
        $stmt->bindValue(':usuarioId', $parcelamento->getUsuarioId());
        $stmt->bindValue(':categoriaId', $parcelamento->getCategoriaId());
        $stmt->bindValue(':descricao', $parcelamento->getDescricao());
        $stmt->bindValue(':valorTotal', $parcelamento->getValorTotal());
        $stmt->bindValue(':valorParcela', $parcelamento->getValorParcela());
        $stmt->bindValue(':quantidadeParcelas', $parcelamento->getQuantidadeParcelas());
        $stmt->bindValue(':tag', $parcelamento->getTag());
    }
}
