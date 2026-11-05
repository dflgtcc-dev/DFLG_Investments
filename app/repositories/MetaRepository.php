<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Meta;
use Exception;
use PDO;

class MetaRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function getMetasPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM metas WHERE usuario_id = :usuarioId ORDER BY data_limite ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $metas = [];

        foreach ($stmt->fetchAll() as $meta) {
            $metas[] = Meta::arrayParaObjeto($meta);
        }

        return $metas;
    }

    // sempre filtra pelo dono, pra um usuário não acessar a meta de outro
    public function getMeta(int $id, int $usuarioId)
    {
        $sql = "SELECT * FROM metas WHERE id = :id AND usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $meta = $stmt->fetch();

        if ($meta == null) {
            return false;
        }

        return Meta::arrayParaObjeto($meta);
    }

    public function saveMeta(Meta $meta): bool
    {
        $sql = "INSERT INTO metas (usuario_id, nome_meta, tipo_meta, valor_alvo, valor_atual, data_limite) " .
            "VALUES (:usuarioId, :nomeMeta, :tipoMeta, :valorAlvo, :valorAtual, :dataLimite)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $meta->getUsuarioId());
        $stmt->bindValue(':nomeMeta', $meta->getNomeMeta());
        $stmt->bindValue(':tipoMeta', $meta->getTipoMeta());
        $stmt->bindValue(':valorAlvo', $meta->getValorAlvo());
        $stmt->bindValue(':valorAtual', $meta->getValorAtual());
        $stmt->bindValue(':dataLimite', $meta->getDataLimite());

        return $stmt->execute();
    }

    public function updateMeta(Meta $meta): bool
    {
        // o valor guardado não é alterado aqui: ele muda pelos aportes (RF 14)
        $sql = "UPDATE metas SET nome_meta = :nomeMeta, tipo_meta = :tipoMeta, valor_alvo = :valorAlvo, " .
            "data_limite = :dataLimite WHERE id = :id AND usuario_id = :usuarioId AND :valorAlvoMinimo >= valor_atual";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':nomeMeta', $meta->getNomeMeta());
        $stmt->bindValue(':tipoMeta', $meta->getTipoMeta());
        $stmt->bindValue(':valorAlvo', $meta->getValorAlvo());
        $stmt->bindValue(':valorAlvoMinimo', $meta->getValorAlvo());
        $stmt->bindValue(':dataLimite', $meta->getDataLimite());
        $stmt->bindValue(':id', $meta->getId());
        $stmt->bindValue(':usuarioId', $meta->getUsuarioId());
        $stmt->execute();

        // 0 linhas: o valor alvo ficaria abaixo do já guardado (a meta existe, o service já conferiu).
        // Se nada mudou, o MySQL também devolve 0, então confere de novo antes de acusar erro.
        if ($stmt->rowCount() === 1) {
            return true;
        }

        $check = $this->connection->prepare("SELECT valor_atual FROM metas WHERE id = :id AND usuario_id = :usuarioId");
        $check->execute(['id' => $meta->getId(), 'usuarioId' => $meta->getUsuarioId()]);
        $valorAtual = $check->fetchColumn();

        return $valorAtual !== false && round((float) $valorAtual, 2) <= round($meta->getValorAlvo(), 2);
    }

    // RF 14 (UC-07.5): transfere saldo para a meta; a despesa e o novo valor da meta são gravados juntos
    public function registrarAporte(int $metaId, int $usuarioId, float $valor, int $categoriaId, string $descricao): bool
    {
        $valorTexto = number_format($valor, 2, '.', '');

        try {
            $this->connection->beginTransaction();

            // só soma se ainda couber no valor alvo
            $sql = "UPDATE metas SET valor_atual = valor_atual + :valor " .
                "WHERE id = :metaId AND usuario_id = :usuarioId AND valor_atual + :valorLimite <= valor_alvo";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':valor', $valorTexto);
            $stmt->bindValue(':valorLimite', $valorTexto);
            $stmt->bindValue(':metaId', $metaId);
            $stmt->bindValue(':usuarioId', $usuarioId);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                $this->connection->rollBack();

                return false;
            }

            $sql = "INSERT INTO registro_financeiro (usuario_id, categoria_id, tipo, valor, data_registro, descricao, meta_id) " .
                "VALUES (:usuarioId, :categoriaId, 'despesa', :valor, CURRENT_DATE, :descricao, :metaId)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':usuarioId', $usuarioId);
            $stmt->bindValue(':categoriaId', $categoriaId);
            $stmt->bindValue(':valor', $valorTexto);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':metaId', $metaId);
            $stmt->execute();

            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao registrar aporte na meta: " . $e->getMessage());

            return false;
        }
    }

    // RF 14: devolve dinheiro da meta para o saldo; a receita e o novo valor da meta são gravados juntos
    public function registrarResgate(int $metaId, int $usuarioId, float $valor, int $categoriaId, string $descricao): bool
    {
        $valorTexto = number_format($valor, 2, '.', '');

        try {
            $this->connection->beginTransaction();

            // só tira se a meta tiver esse valor guardado
            $stmt = $this->connection->prepare(
                "UPDATE metas SET valor_atual = valor_atual - :valor " .
                "WHERE id = :metaId AND usuario_id = :usuarioId AND valor_atual >= :valorMinimo"
            );
            $stmt->bindValue(':valor', $valorTexto);
            $stmt->bindValue(':valorMinimo', $valorTexto);
            $stmt->bindValue(':metaId', $metaId);
            $stmt->bindValue(':usuarioId', $usuarioId);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                $this->connection->rollBack();

                return false;
            }

            $stmt = $this->connection->prepare(
                "INSERT INTO registro_financeiro (usuario_id, categoria_id, tipo, valor, data_registro, descricao, meta_id) " .
                "VALUES (:usuarioId, :categoriaId, 'receita', :valor, CURRENT_DATE, :descricao, :metaId)"
            );
            $stmt->bindValue(':usuarioId', $usuarioId);
            $stmt->bindValue(':categoriaId', $categoriaId);
            $stmt->bindValue(':valor', $valorTexto);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':metaId', $metaId);
            $stmt->execute();

            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao registrar resgate da meta: " . $e->getMessage());

            return false;
        }
    }

    // todo o valor guardado volta para o saldo. Os aportes e resgates da meta saem junto; o que sobrar
    // (o valor inicial, que nunca saiu do saldo) entra como receita em "Outras receitas"
    public function deleteMeta(int $id, int $usuarioId, ?int $categoriaReceitaId): bool
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("SELECT nome_meta, valor_atual FROM metas WHERE id = :id AND usuario_id = :usuarioId");
            $stmt->execute(['id' => $id, 'usuarioId' => $usuarioId]);
            $meta = $stmt->fetch();

            if (!$meta) {
                $this->connection->rollBack();

                return false;
            }

            $stmt = $this->connection->prepare(
                "SELECT COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE -valor END), 0) " .
                "FROM registro_financeiro WHERE meta_id = :id AND usuario_id = :usuarioId"
            );
            $stmt->execute(['id' => $id, 'usuarioId' => $usuarioId]);
            $liquidoTransferido = (float) $stmt->fetchColumn();

            $stmt = $this->connection->prepare("DELETE FROM registro_financeiro WHERE meta_id = :id AND usuario_id = :usuarioId");
            $stmt->execute(['id' => $id, 'usuarioId' => $usuarioId]);

            $stmt = $this->connection->prepare("DELETE FROM metas WHERE id = :id AND usuario_id = :usuarioId");
            $stmt->execute(['id' => $id, 'usuarioId' => $usuarioId]);

            $inicial = round((float) $meta['valor_atual'] - $liquidoTransferido, 2);

            if ($inicial > 0 && $categoriaReceitaId !== null) {
                $stmt = $this->connection->prepare(
                    "INSERT INTO registro_financeiro (usuario_id, categoria_id, tipo, valor, data_registro, descricao) " .
                    "VALUES (:usuarioId, :categoriaId, 'receita', :valor, CURRENT_DATE, :descricao)"
                );
                $stmt->execute([
                    'usuarioId' => $usuarioId,
                    'categoriaId' => $categoriaReceitaId,
                    'valor' => number_format($inicial, 2, '.', ''),
                    'descricao' => 'Valor inicial da meta excluída: ' . $meta['nome_meta'],
                ]);
            }

            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao excluir meta: " . $e->getMessage());

            return false;
        }
    }
}
