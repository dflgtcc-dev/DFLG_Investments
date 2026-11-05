<?php

namespace app\database;

use PDO;

class DatabaseInitializer
{

    public function init(PDO $connection)
    {
        $connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $connection->exec("USE " . DB_NAME);

        $scriptPath = __DIR__ . '/scripts/script.sql';

        if (file_exists($scriptPath)) {
            $sql = file_get_contents($scriptPath);

            try {
                $connection->exec($sql);
                $this->migrarColunasAntigas($connection);
                $this->migrarColunasParcelamento($connection);
                $this->migrarColunaTema($connection);
                $this->migrarAporteMeta($connection);
                $this->migrarLimiteCategoria($connection);
            } catch (\Exception $e) {
                error_log("Erro ao inicializar banco de dados: " . $e->getMessage());
            }
        }
    }

    public function migrate(PDO $connection): void
    {
        $this->migrarColunasAntigas($connection);
        $this->migrarColunasParcelamento($connection);
        $this->migrarColunaTema($connection);
        $this->migrarColunaTelefone($connection);
        $this->migrarNivelPermissao($connection);
        $this->migrarAporteMeta($connection);
        $this->migrarLimiteCategoria($connection);
    }

    private function migrarColunasAntigas(PDO $connection): void
    {
        $colunas = $connection->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuario_comum'"
        )->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('data_ultimo_xp', $colunas, true) && !in_array('ultimo_acesso', $colunas, true)) {
            $connection->exec(
                "ALTER TABLE usuario_comum CHANGE COLUMN data_ultimo_xp ultimo_acesso DATE NULL"
            );
        } elseif (!in_array('ultimo_acesso', $colunas, true)) {
            $connection->exec(
                "ALTER TABLE usuario_comum ADD COLUMN ultimo_acesso DATE NULL AFTER maior_streak"
            );
        }
    }

    private function migrarColunasParcelamento(PDO $connection): void
    {
        $parcelamentoColunas = $this->getColunas($connection, 'parcelamentos');
        $registroColunas = $this->getColunas($connection, 'registro_financeiro');

        if (!in_array('tag', $parcelamentoColunas, true)) {
            $connection->exec("ALTER TABLE parcelamentos ADD COLUMN tag VARCHAR(50) NULL");
        }
        if (!in_array('valor_parcela', $parcelamentoColunas, true)) {
            $connection->exec("ALTER TABLE parcelamentos ADD COLUMN valor_parcela DECIMAL(10,2) NULL");
        }
        if (!in_array('parcelamento_id', $registroColunas, true)) {
            $connection->exec("ALTER TABLE registro_financeiro ADD COLUMN parcelamento_id INT NULL");
        }
        if (!in_array('numero_parcela', $registroColunas, true)) {
            $this->migrarLancamentoManualDeParcelas($connection);
        }
    }

    // a opção "lançar parcelas futuras" saiu: cada parcela passa a ser lançada pelo botão "Lançar parcela".
    // Roda uma vez, nos bancos antigos. A coluna lancar_futuros, se existir, fica sem uso.
    private function migrarLancamentoManualDeParcelas(PDO $connection): void
    {
        $connection->exec("ALTER TABLE registro_financeiro ADD COLUMN numero_parcela INT NULL AFTER parcelamento_id");

        // parcelas futuras geradas pela opção antiga eram só previsão, não pagamento
        $apagar = $connection->prepare(
            "DELETE FROM registro_financeiro WHERE parcelamento_id IS NOT NULL AND data_registro > :hoje"
        );
        $apagar->execute(['hoje' => date('Y-m-d')]);

        // registros de parcelamentos que já não existem viram despesas comuns
        $connection->exec(
            "UPDATE registro_financeiro SET parcelamento_id = NULL " .
            "WHERE parcelamento_id IS NOT NULL AND parcelamento_id NOT IN (SELECT id FROM parcelamentos)"
        );

        // numera as parcelas que ficaram (1, 2, 3...) pela ordem de data
        $registros = $connection->query(
            "SELECT id, parcelamento_id FROM registro_financeiro WHERE parcelamento_id IS NOT NULL " .
            "ORDER BY parcelamento_id, data_registro, id"
        )->fetchAll(PDO::FETCH_ASSOC);

        $numerar = $connection->prepare("UPDATE registro_financeiro SET numero_parcela = :numero WHERE id = :id");
        $parcelamentoAtual = null;
        $numero = 0;

        foreach ($registros as $registro) {
            if ($registro['parcelamento_id'] !== $parcelamentoAtual) {
                $parcelamentoAtual = $registro['parcelamento_id'];
                $numero = 0;
            }
            $numerar->execute(['numero' => ++$numero, 'id' => $registro['id']]);
        }

        // impede lançar a mesma parcela duas vezes
        $connection->exec(
            "ALTER TABLE registro_financeiro ADD UNIQUE KEY uk_registro_parcela (parcelamento_id, numero_parcela)"
        );
    }

    // RNF 07: tema (claro ou escuro) escolhido pelo usuário
    private function migrarColunaTema(PDO $connection): void
    {
        $usuarioColunas = $this->getColunas($connection, 'usuarios');

        if (!in_array('tema', $usuarioColunas, true)) {
            $connection->exec(
                "ALTER TABLE usuarios ADD COLUMN tema ENUM('claro', 'escuro') NOT NULL DEFAULT 'claro' AFTER perfil"
            );
        }
    }

    private function migrarColunaTelefone(PDO $connection): void
    {
        $usuarioColunas = $this->getColunas($connection, 'usuarios');
 
        if (!in_array('telefone', $usuarioColunas, true)) {
            $connection->exec("ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(11) NULL AFTER email");
        }
    }

     // RN 01 e RN 09: nivel 1 = administrador chefe, nivel 2 = administrador comum
    private function migrarNivelPermissao(PDO $connection): void
    {
        $colunas = $this->getColunas($connection, 'administradores');
 
        if (!in_array('nivel_permissao', $colunas, true)) {
            $connection->exec("ALTER TABLE administradores ADD COLUMN nivel_permissao INT NOT NULL DEFAULT 2 AFTER usuario_id");
 
            // o administrador mais antigo (a conta criada pelo script do banco) passa a ser o chefe
            $connection->exec(
                "UPDATE administradores SET nivel_permissao = 1 " .
                "WHERE usuario_id = (SELECT menor_id FROM (SELECT MIN(usuario_id) AS menor_id FROM administradores) AS t)"
            );
        }
    }

    // RF 14: aporte e resgate são registros ligados à meta (categorias "Metas" e "Resgate de metas")
    private function migrarAporteMeta(PDO $connection): void
    {
        $registroColunas = $this->getColunas($connection, 'registro_financeiro');

        // ao excluir a meta, as despesas dos aportes também saem (o dinheiro volta para o saldo)
        if (!in_array('meta_id', $registroColunas, true)) {
            $connection->exec(
                "ALTER TABLE registro_financeiro ADD COLUMN meta_id INT NULL, " .
                "ADD CONSTRAINT fk_registro_meta FOREIGN KEY (meta_id) REFERENCES metas(id) ON DELETE CASCADE"
            );
        }

        $existe = $connection->query(
            "SELECT COUNT(*) FROM categorias WHERE nome = 'Metas' AND tipo = 'despesa'"
        )->fetchColumn();

        if (!$existe) {
            $connection->exec("INSERT INTO categorias (nome, icone, tipo) VALUES ('Metas', 'bi-bullseye', 'despesa')");
        }

        // o resgate (dinheiro da meta de volta ao saldo) entra como receita nesta categoria
        $existe = $connection->query(
            "SELECT COUNT(*) FROM categorias WHERE nome = 'Resgate de metas' AND tipo = 'receita'"
        )->fetchColumn();

        if (!$existe) {
            $connection->exec("INSERT INTO categorias (nome, icone, tipo) VALUES ('Resgate de metas', 'bi-box-arrow-in-down', 'receita')");
        }
    }

    // RF 12: limite mensal de gastos de cada usuário por categoria
    private function migrarLimiteCategoria(PDO $connection): void
    {
        $existe = $connection->query(
            "SELECT COUNT(*) FROM information_schema.TABLES " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'limites_categoria'"
        )->fetchColumn();

        if (!$existe) {
            $connection->exec(
                "CREATE TABLE limites_categoria (" .
                "id INT AUTO_INCREMENT PRIMARY KEY, " .
                "usuario_id INT NOT NULL, " .
                "categoria_id INT NOT NULL, " .
                "valor_limite DECIMAL(10,2) NOT NULL, " .
                "UNIQUE KEY uk_limite_usuario_categoria (usuario_id, categoria_id), " .
                "FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE, " .
                "FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }
    }

    private function getColunas(PDO $connection, string $tabela): array
    {
        $stmt = $connection->prepare(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabela"
        );
        $stmt->execute(['tabela' => $tabela]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}