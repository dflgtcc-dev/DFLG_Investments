<?php

namespace app\database;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Mantém o banco de dados em dia com o código.
 *
 * O ConnectionFactory chama migrate() a cada conexão. Tudo aqui é idempotente: cada passo confere
 * o que já existe e só altera o que falta, então rodar de novo em um banco atualizado não muda nada.
 *
 *  1. Banco vazio (sem a tabela usuarios): cria tudo a partir de scripts/script.sql.
 *  2. Banco de uma versão anterior: as migrações abaixo acrescentam o que foi criado depois.
 *
 * Ao incluir uma coluna ou tabela nova, faça nos dois lugares:
 *  - scripts/script.sql, para os bancos novos;
 *  - um método migrarXxx() aqui, registrado em MIGRACOES, para os bancos que já existem.
 */
class DatabaseInitializer
{
    private const SCRIPT = __DIR__ . '/scripts/script.sql';

    // a ordem importa: uma migração pode depender das anteriores
    private const MIGRACOES = [
        'migrarUltimoAcesso',
        'migrarColunasParcelamento',
        'migrarColunaTema',
        'migrarColunaTelefone',
        'migrarNivelPermissao',
        'migrarAporteMeta',
        'migrarLimiteCategoria',
    ];

    public function migrate(PDO $connection): void
    {
        $this->instalarSeBancoVazio($connection);

        foreach (self::MIGRACOES as $migracao) {
            try {
                $this->$migracao($connection);
            } catch (Throwable $e) {
                // o detalhe técnico vai só para o log (RNF 04); quem chamou decide o que mostrar
                error_log("Falha na migração {$migracao}: " . $e->getMessage());

                throw $e;
            }
        }
    }

    // banco recém-criado e ainda sem tabelas: monta o esquema completo. Só roda quando a tabela usuarios
    // não existe, porque o script traz dados iniciais (administrador e categorias) que não devem se repetir.
    private function instalarSeBancoVazio(PDO $connection): void
    {
        if ($this->tabelaExiste($connection, 'usuarios')) {
            return;
        }

        if (!is_file(self::SCRIPT)) {
            throw new RuntimeException('Arquivo do esquema do banco não encontrado: ' . self::SCRIPT);
        }

        $connection->exec(file_get_contents(self::SCRIPT));
    }

    // usuario_comum.ultimo_acesso (antes chamada data_ultimo_xp): base da sequência de dias de acesso
    private function migrarUltimoAcesso(PDO $connection): void
    {
        $colunas = $this->getColunas($connection, 'usuario_comum');

        if (in_array('ultimo_acesso', $colunas, true)) {
            return;
        }

        if (in_array('data_ultimo_xp', $colunas, true)) {
            $connection->exec("ALTER TABLE usuario_comum CHANGE COLUMN data_ultimo_xp ultimo_acesso DATE NULL");
        } else {
            $connection->exec("ALTER TABLE usuario_comum ADD COLUMN ultimo_acesso DATE NULL AFTER maior_streak");
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

    // RN 01 e RN 09: nível 1 = administrador chefe, nível 2 = administrador comum
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

    // RF 12: limite mensal de gastos de cada usuário por categoria.
    // Substitui a antiga categorias.limite_orcamento, que continua na tabela mas já não é usada.
    private function migrarLimiteCategoria(PDO $connection): void
    {
        if ($this->tabelaExiste($connection, 'limites_categoria')) {
            return;
        }

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

    private function tabelaExiste(PDO $connection, string $tabela): bool
    {
        $stmt = $connection->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabela"
        );
        $stmt->execute(['tabela' => $tabela]);

        return (int) $stmt->fetchColumn() > 0;
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
