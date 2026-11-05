<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Transacao;
use Exception;
use PDO;

class TransacaoRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    private const ORDENACAO = [
        'recentes' => 'r.data_registro DESC, r.id DESC',
        'antigas' => 'r.data_registro ASC, r.id ASC',
        'maior' => 'r.valor DESC, r.data_registro DESC, r.id DESC',
        'menor' => 'r.valor ASC, r.data_registro DESC, r.id DESC',
    ];

    // $filtros já vem validado pelo service: tipo, categoriaId, dataInicio, dataFim, valorMin, valorMax, busca, origem, ordem
    public function getTransacoesPorUsuario(int $usuarioId, array $filtros = []): array
    {
        $sql = "SELECT r.*, c.nome AS nome_categoria FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.data_registro <= CURRENT_DATE";
        $parametros = ['usuarioId' => $usuarioId];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND r.tipo = :tipo";
            $parametros['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['categoriaId'])) {
            $sql .= " AND r.categoria_id = :categoriaId";
            $parametros['categoriaId'] = (int) $filtros['categoriaId'];
        }

        if (!empty($filtros['dataInicio'])) {
            $sql .= " AND r.data_registro >= :dataInicio";
            $parametros['dataInicio'] = $filtros['dataInicio'];
        }

        if (!empty($filtros['dataFim'])) {
            $sql .= " AND r.data_registro <= :dataFim";
            $parametros['dataFim'] = $filtros['dataFim'];
        }

        if (isset($filtros['valorMin']) && $filtros['valorMin'] !== null) {
            $sql .= " AND r.valor >= :valorMin";
            $parametros['valorMin'] = number_format((float) $filtros['valorMin'], 2, '.', '');
        }

        if (isset($filtros['valorMax']) && $filtros['valorMax'] !== null) {
            $sql .= " AND r.valor <= :valorMax";
            $parametros['valorMax'] = number_format((float) $filtros['valorMax'], 2, '.', '');
        }

        if (!empty($filtros['busca'])) {
            // '!' como escape para % e _ digitados valerem como texto comum
            $termo = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filtros['busca']) . '%';
            $sql .= " AND (r.descricao LIKE :buscaDescricao ESCAPE '!' OR c.nome LIKE :buscaCategoria ESCAPE '!')";
            $parametros['buscaDescricao'] = $termo;
            $parametros['buscaCategoria'] = $termo;
        }

        $sql .= match ($filtros['origem'] ?? null) {
            'manual' => " AND r.parcelamento_id IS NULL AND r.meta_id IS NULL",
            'parcela' => " AND r.parcelamento_id IS NOT NULL",
            'meta' => " AND r.meta_id IS NOT NULL",
            default => '',
        };

        $sql .= " ORDER BY " . (self::ORDENACAO[$filtros['ordem'] ?? 'recentes'] ?? self::ORDENACAO['recentes']);

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($parametros);

        $transacoes = [];

        foreach ($stmt->fetchAll() as $transacao) {
            $transacoes[] = Transacao::arrayParaObjeto($transacao);
        }

        return $transacoes;
    }

    // sempre filtra pelo dono, pra um usuário não acessar a transação de outro
    public function getTransacao(int $id, int $usuarioId)
    {
        $sql = "SELECT r.*, c.nome AS nome_categoria FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.id = :id AND r.usuario_id = :usuarioId";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $transacao = $stmt->fetch();

        if ($transacao == null) {
            return false;
        }

        return Transacao::arrayParaObjeto($transacao);
    }

    public function saveTransacao(Transacao $transacao): bool
    {
        $sql = "INSERT INTO registro_financeiro (usuario_id, categoria_id, tipo, valor, data_registro, descricao) " .
            "VALUES (:usuarioId, :categoriaId, :tipo, :valor, :dataRegistro, :descricao)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $transacao->getUsuarioId());
        $stmt->bindValue(':categoriaId', $transacao->getCategoriaId());
        $stmt->bindValue(':tipo', $transacao->getTipo());
        $stmt->bindValue(':valor', $transacao->getValor());
        $stmt->bindValue(':dataRegistro', $transacao->getDataRegistro());
        $stmt->bindValue(':descricao', $transacao->getDescricao());

        return $stmt->execute();
    }

    public function updateTransacao(Transacao $transacao): bool
    {
        $sql = "UPDATE registro_financeiro SET categoria_id = :categoriaId, tipo = :tipo, valor = :valor, " .
            "data_registro = :dataRegistro, descricao = :descricao WHERE id = :id AND usuario_id = :usuarioId AND parcelamento_id IS NULL";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':categoriaId', $transacao->getCategoriaId());
        $stmt->bindValue(':tipo', $transacao->getTipo());
        $stmt->bindValue(':valor', $transacao->getValor());
        $stmt->bindValue(':dataRegistro', $transacao->getDataRegistro());
        $stmt->bindValue(':descricao', $transacao->getDescricao());
        $stmt->bindValue(':id', $transacao->getId());
        $stmt->bindValue(':usuarioId', $transacao->getUsuarioId());

        return $stmt->execute();
    }

    public function deleteTransacao(int $id, int $usuarioId): bool
    {
        try {
            $this->connection->beginTransaction();

            // aportes e resgates de meta não passam por aqui: o service recusa (ficam em Metas)
            $sql = "DELETE FROM registro_financeiro WHERE id = :id AND usuario_id = :usuarioId AND parcelamento_id IS NULL";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':usuarioId', $usuarioId);
            $stmt->execute();

            $this->connection->commit();

            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            error_log("Erro ao excluir transação: " . $e->getMessage());

            return false;
        }
    }

    // saldo de hoje: tudo o que entrou menos o que saiu, inclusive o que foi para as metas e voltou delas
    public function getSaldo(int $usuarioId): float
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0) " .
            "FROM registro_financeiro WHERE usuario_id = :usuarioId AND data_registro <= CURRENT_DATE";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    // total de receitas ou despesas, sem as transferências de e para as metas (elas só movem o saldo)
    public function getTotalPorTipo(int $usuarioId, string $tipo): float
    {
        $sql = "SELECT COALESCE(SUM(valor), 0) FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND tipo = :tipo AND meta_id IS NULL AND data_registro <= CURRENT_DATE";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function getEvolucaoMensal(int $usuarioId, int $meses = 6): array
    {
        $meses = max(1, min($meses, 24));
        $sql = "SELECT DATE_FORMAT(data_registro, '%Y-%m') AS mes, " .
            "SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas, " .
            "SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas " .
            "FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND meta_id IS NULL " .
            "AND data_registro >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL {$meses} MONTH), '%Y-%m-01') " .
            "AND data_registro <= CURRENT_DATE " .
            "GROUP BY DATE_FORMAT(data_registro, '%Y-%m') ORDER BY mes ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // RF 11: saldo (receitas - despesas) dos registros anteriores à data, sem contar os futuros
    public function getSaldoAnteriorA(int $usuarioId, string $data): float
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0) " .
            "FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND data_registro < :data AND data_registro <= CURRENT_DATE";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':data', $data);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    // RF 11: registros do período em ordem cronológica, sem contar os futuros
    public function getTransacoesPorPeriodo(int $usuarioId, string $inicio, string $fim): array
    {
        $sql = "SELECT r.*, c.nome AS nome_categoria FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.data_registro BETWEEN :inicio AND :fim " .
            "AND r.data_registro <= CURRENT_DATE " .
            "ORDER BY r.data_registro ASC, r.id ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        $transacoes = [];

        foreach ($stmt->fetchAll() as $transacao) {
            $transacoes[] = Transacao::arrayParaObjeto($transacao);
        }

        return $transacoes;
    }

    // RF 11: receitas e despesas de cada mês do ano, sem contar os futuros
    public function getResumoPorMes(int $usuarioId, int $ano): array
    {
        $sql = "SELECT MONTH(data_registro) AS mes, " .
            "SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas, " .
            "SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas " .
            "FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND data_registro BETWEEN :inicio AND :fim " .
            "AND data_registro <= CURRENT_DATE " .
            "GROUP BY MONTH(data_registro) ORDER BY mes ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', sprintf('%04d-01-01', $ano));
        $stmt->bindValue(':fim', sprintf('%04d-12-31', $ano));
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // RF 11: ano do primeiro registro do usuário (null se ainda não tem nenhum)
    public function getPrimeiroAno(int $usuarioId): ?int
    {
        $sql = "SELECT MIN(YEAR(data_registro)) FROM registro_financeiro WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $ano = $stmt->fetchColumn();

        return $ano ? (int) $ano : null;
    }

    // RF 07 (dashboard): data do primeiro registro do usuário (null se ele ainda não tem nenhum)
    public function getPrimeiraData(int $usuarioId): ?string
    {
        $sql = "SELECT MIN(data_registro) FROM registro_financeiro WHERE usuario_id = :usuarioId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchColumn();

        return $data ?: null;
    }

    // RF 07 (dashboard): receitas e despesas do período, sem contar os registros futuros
    public function getTotaisPeriodo(int $usuarioId, string $inicio, string $fim): array
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) AS receitas, " .
            "COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) AS despesas " .
            "FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND meta_id IS NULL AND data_registro BETWEEN :inicio AND :fim " .
            "AND data_registro <= CURRENT_DATE";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        $linha = $stmt->fetch();

        return ['receitas' => (float) $linha['receitas'], 'despesas' => (float) $linha['despesas']];
    }

    // RF 07 (dashboard): despesas do período somadas por categoria, da maior para a menor
    public function getDespesasPorCategoria(int $usuarioId, string $inicio, string $fim): array
    {
        $sql = "SELECT COALESCE(c.nome, 'Sem categoria') AS nome, c.icone, SUM(r.valor) AS total " .
            "FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.tipo = 'despesa' AND r.meta_id IS NULL " .
            "AND r.data_registro BETWEEN :inicio AND :fim AND r.data_registro <= CURRENT_DATE " .
            "GROUP BY r.categoria_id, c.nome, c.icone ORDER BY total DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // RF 07 (dashboard): receitas e despesas de cada mês do intervalo, sem contar os registros futuros
    public function getEvolucaoPorIntervalo(int $usuarioId, string $inicio, string $fim): array
    {
        $sql = "SELECT DATE_FORMAT(data_registro, '%Y-%m') AS mes, " .
            "SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas, " .
            "SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas " .
            "FROM registro_financeiro " .
            "WHERE usuario_id = :usuarioId AND meta_id IS NULL AND data_registro BETWEEN :inicio AND :fim " .
            "AND data_registro <= CURRENT_DATE " .
            "GROUP BY DATE_FORMAT(data_registro, '%Y-%m') ORDER BY mes ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // RF 07 (dashboard): os registros mais recentes do período, sem contar os futuros
    public function getUltimasTransacoes(int $usuarioId, string $inicio, string $fim, int $limite): array
    {
        $sql = "SELECT r.*, c.nome AS nome_categoria FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.data_registro BETWEEN :inicio AND :fim " .
            "AND r.data_registro <= CURRENT_DATE " .
            "ORDER BY r.data_registro DESC, r.id DESC LIMIT " . (int) $limite;
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        $transacoes = [];

        foreach ($stmt->fetchAll() as $transacao) {
            $transacoes[] = Transacao::arrayParaObjeto($transacao);
        }

        return $transacoes;
    }

    // RF 07 (dashboard): a maior despesa do período, sem contar as futuras
    public function getMaiorDespesa(int $usuarioId, string $inicio, string $fim): ?Transacao
    {
        $sql = "SELECT r.*, c.nome AS nome_categoria FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.tipo = 'despesa' AND r.meta_id IS NULL " .
            "AND r.data_registro BETWEEN :inicio AND :fim AND r.data_registro <= CURRENT_DATE " .
            "ORDER BY r.valor DESC, r.data_registro DESC, r.id DESC LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        $transacao = $stmt->fetch();

        return $transacao ? Transacao::arrayParaObjeto($transacao) : null;
    }

    // RF 07 (dashboard): parcelas do período, inclusive as que ainda vão vencer (por isso não filtra "até hoje")
    public function getParcelasDoPeriodo(int $usuarioId, string $inicio, string $fim): array
    {
        $sql = "SELECT r.data_registro, r.valor, r.descricao, c.nome AS nome_categoria " .
            "FROM registro_financeiro r " .
            "LEFT JOIN categorias c ON c.id = r.categoria_id " .
            "WHERE r.usuario_id = :usuarioId AND r.parcelamento_id IS NOT NULL " .
            "AND r.data_registro BETWEEN :inicio AND :fim " .
            "ORDER BY r.data_registro ASC, r.id ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}