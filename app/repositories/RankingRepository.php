<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use DateTimeImmutable;
use PDO;

class RankingRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    // RF 10: ranking geral, pelo nível atual e depois pelo XP total
    public function getRankingGeral(int $limite): array
    {
        return $this->getRanking($this->baseGeral(), [], $limite);
    }

    // RF 10: ranking de um período (semanal ou mensal), pelo nível atual e depois pelo XP ganho no período
    public function getRankingPorPeriodo(string $inicio, string $fim, int $limite): array
    {
        return $this->getRanking($this->basePeriodo(), [':inicio' => $inicio, ':fim' => $fim], $limite);
    }

    // RF 10: posição do usuário no ranking geral (null se ele não participa)
    public function getPosicaoGeral(int $usuarioId): ?array
    {
        return $this->getPosicao($this->baseGeral(), [], $usuarioId);
    }

    // RF 10: posição do usuário no ranking de um período (null se ele não participa)
    public function getPosicaoPorPeriodo(int $usuarioId, string $inicio, string $fim): ?array
    {
        return $this->getPosicao($this->basePeriodo(), [':inicio' => $inicio, ':fim' => $fim], $usuarioId);
    }

    // quem participa do ranking geral: usuários comuns que aparecem no ranking
    private function baseGeral(): string
    {
        return "SELECT u.id, u.nickname, c.nivel, c.xp_total AS xp, " . $this->streakEfetivo() . " AS streak_atual, u.criado_em " .
            "FROM usuario_comum c INNER JOIN usuarios u ON u.id = c.usuario_id " .
            "WHERE c.visibilidade_ranking = 1 AND u.status = 'ativo'";
    }

    // quem participa do ranking de um período: quem ganhou XP nele, com a soma desse XP
    private function basePeriodo(): string
    {
        return "SELECT u.id, u.nickname, c.nivel, SUM(h.xp) AS xp, " . $this->streakEfetivo() . " AS streak_atual, u.criado_em " .
            "FROM historico_xp h " .
            "INNER JOIN usuario_comum c ON c.usuario_id = h.usuario_id " .
            "INNER JOIN usuarios u ON u.id = h.usuario_id " .
            "WHERE c.visibilidade_ranking = 1 AND u.status = 'ativo' AND h.data_concessao BETWEEN :inicio AND :fim " .
            "GROUP BY u.id, u.nickname, u.criado_em, c.nivel, c.streak_atual, c.ultimo_acesso";
    }

    // RN 03: a sequência só vale se o último acesso foi hoje ou ontem; antes disso ela já foi quebrada (vale 0)
    // a data vem do PHP, como no cálculo da sequência no GamificacaoService, e não de um dado digitado pelo usuário
    private function streakEfetivo(): string
    {
        $ontem = (new DateTimeImmutable('-1 day'))->format('Y-m-d');

        return "CASE WHEN c.ultimo_acesso >= '" . $ontem . "' THEN c.streak_atual ELSE 0 END";
    }

    // RN 10: ordena pelo nível e depois pelo XP; no empate vale a maior sequência de acesso (a efetiva, RN 03) e, depois, o cadastro mais antigo
    private function getRanking(string $base, array $parametros, int $limite): array
    {
        $sql = "SELECT r.id, r.nickname, r.nivel, r.xp FROM (" . $base . ") r " .
            "ORDER BY r.nivel DESC, r.xp DESC, r.streak_atual DESC, r.criado_em ASC, r.id ASC " .
            "LIMIT :limite";
        $stmt = $this->connection->prepare($sql);

        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor);
        }

        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // posição do usuário: 1 mais a quantidade de participantes que estão à frente dele, pela mesma ordem do ranking
    private function getPosicao(string $base, array $parametros, int $usuarioId): ?array
    {
        $sql = "SELECT r.id, r.nickname, r.nivel, r.xp, r.streak_atual, r.criado_em FROM (" . $base . ") r " .
            "WHERE r.id = :usuarioId";
        $stmt = $this->connection->prepare($sql);

        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor);
        }

        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch();

        // não participa do ranking (oculto, sem XP no período ou administrador)
        if (!$usuario) {
            return null;
        }

        $sql = "SELECT COUNT(*) FROM (" . $base . ") r " .
            "WHERE r.nivel > :nivel " .
            "OR (r.nivel = :nivel2 AND r.xp > :xp) " .
            "OR (r.nivel = :nivel3 AND r.xp = :xp2 AND r.streak_atual > :streak) " .
            "OR (r.nivel = :nivel4 AND r.xp = :xp3 AND r.streak_atual = :streak2 AND r.criado_em < :criadoEm) " .
            "OR (r.nivel = :nivel5 AND r.xp = :xp4 AND r.streak_atual = :streak3 AND r.criado_em = :criadoEm2 AND r.id < :id)";
        $stmt = $this->connection->prepare($sql);

        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor);
        }

        foreach ([':nivel', ':nivel2', ':nivel3', ':nivel4', ':nivel5'] as $nome) {
            $stmt->bindValue($nome, (int) $usuario['nivel'], PDO::PARAM_INT);
        }

        foreach ([':xp', ':xp2', ':xp3', ':xp4'] as $nome) {
            $stmt->bindValue($nome, (int) $usuario['xp'], PDO::PARAM_INT);
        }

        foreach ([':streak', ':streak2', ':streak3'] as $nome) {
            $stmt->bindValue($nome, (int) $usuario['streak_atual'], PDO::PARAM_INT);
        }

        $stmt->bindValue(':criadoEm', $usuario['criado_em']);
        $stmt->bindValue(':criadoEm2', $usuario['criado_em']);
        $stmt->bindValue(':id', (int) $usuario['id'], PDO::PARAM_INT);
        $stmt->execute();

        return [
            'posicao' => (int) $stmt->fetchColumn() + 1,
            'nivel' => (int) $usuario['nivel'],
            'xp' => (int) $usuario['xp'],
        ];
    }
}