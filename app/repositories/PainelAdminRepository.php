<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class PainelAdminRepository
{
    public const POR_PAGINA = 10;

    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    // lista os usuários de um perfil, com busca, filtro de status e paginação
    public function listar(string $perfil, string $busca, string $status, int $pagina): array
    {
        $where = "WHERE u.perfil = :perfil";
        $parametros = [':perfil' => $perfil];

        if ($busca !== '') {
            $where .= " AND (u.nome LIKE :busca1 OR u.nickname LIKE :busca2 OR u.email LIKE :busca3)";
            // % e _ digitados pelo administrador valem como texto, não como curinga
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $parametros[':busca1'] = $like;
            $parametros[':busca2'] = $like;
            $parametros[':busca3'] = $like;
        }

        if ($status !== '') {
            $where .= " AND u.status = :status";
            $parametros[':status'] = $status;
        }

        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM usuarios u $where");
        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = min(max(1, $pagina), $paginas);

        $sql = "SELECT u.id, u.nome, u.nickname, u.email, u.telefone, u.status, u.perfil, u.criado_em, " .
            "uc.nivel, uc.xp_total, uc.ultimo_acesso, a.nivel_permissao " .
            "FROM usuarios u " .
            "LEFT JOIN usuario_comum uc ON uc.usuario_id = u.id " .
            "LEFT JOIN administradores a ON a.usuario_id = u.id " .
            "$where ORDER BY u.criado_em DESC, u.id DESC LIMIT :limite OFFSET :deslocamento";
        $stmt = $this->connection->prepare($sql);
        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor);
        }
        $stmt->bindValue(':limite', self::POR_PAGINA, PDO::PARAM_INT);
        $stmt->bindValue(':deslocamento', ($pagina - 1) * self::POR_PAGINA, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'linhas' => $stmt->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
        ];
    }

    // total de contas por perfil e situação
    public function contarPorPerfilEStatus(): array
    {
        $stmt = $this->connection->query("SELECT perfil, status, COUNT(*) AS total FROM usuarios GROUP BY perfil, status");

        return $stmt->fetchAll();
    }

    // usuários comuns cadastrados nos últimos 7 e 30 dias
    public function contarNovosCadastros(): array
    {
        $sql = "SELECT " .
            "COALESCE(SUM(criado_em >= NOW() - INTERVAL 7 DAY), 0) AS ultimos7, " .
            "COALESCE(SUM(criado_em >= NOW() - INTERVAL 30 DAY), 0) AS ultimos30 " .
            "FROM usuarios WHERE perfil = 'comum'";

        return $this->connection->query($sql)->fetch();
    }

    // usuários comuns ativos separados pelo tempo desde o último acesso
    public function contarEngajamento(string $limiteSete, string $limiteTrinta): array
    {
        $sql = "SELECT " .
            "COALESCE(SUM(uc.ultimo_acesso >= :sete1), 0) AS ativos, " .
            "COALESCE(SUM(uc.ultimo_acesso < :sete2 AND uc.ultimo_acesso >= :trinta1), 0) AS em_risco, " .
            "COALESCE(SUM(uc.ultimo_acesso IS NULL OR uc.ultimo_acesso < :trinta2), 0) AS inativos, " .
            "COUNT(*) AS total " .
            "FROM usuarios u JOIN usuario_comum uc ON uc.usuario_id = u.id " .
            "WHERE u.status = 'ativo'";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':sete1', $limiteSete);
        $stmt->bindValue(':sete2', $limiteSete);
        $stmt->bindValue(':trinta1', $limiteTrinta);
        $stmt->bindValue(':trinta2', $limiteTrinta);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $stmt = $this->connection->prepare("UPDATE usuarios SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}