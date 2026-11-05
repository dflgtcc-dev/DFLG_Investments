<?php

/**
 * Dados de demonstração para a apresentação do projeto (uso só por linha de comando).
 *
 *   php app/database/scripts/dados_demonstracao.php
 *
 * Cria (ou recria do zero) a conta de demonstração com seis meses de receitas e despesas, metas com aportes e
 * resgate, parcelamentos já lançados, limites por categoria e XP/sequência de acesso. A conta fica a 5 XP de
 * subir de nível: no primeiro login do dia o usuário ganha XP e sobe para o nível 3, o que mostra as notificações.
 *
 * Rodar de novo apaga só a conta de demonstração e a recria; as demais contas não são tocadas.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../core/Autoload.php';

use app\database\ConnectionFactory;

const DEMO_EMAIL = 'demo@dflg.com';
const DEMO_NICKNAME = 'demo';
const DEMO_SENHA = 'Demo@2026';

$pdo = ConnectionFactory::getConnection();
$hoje = new DateTimeImmutable('today');

// data de um dia do mês (m meses atrás), sem passar do fim do mês nem de hoje
$data = function (int $mesesAtras, int $dia) use ($hoje): string {
    $primeiro = $hoje->modify('first day of this month')->modify("-{$mesesAtras} months");
    $dia = min($dia, (int) $primeiro->format('t'));
    $d = $primeiro->setDate((int) $primeiro->format('Y'), (int) $primeiro->format('n'), $dia);

    return ($d > $hoje ? $hoje : $d)->format('Y-m-d');
};

$categoria = function (string $nome) use ($pdo): int {
    $stmt = $pdo->prepare('SELECT id FROM categorias WHERE nome = ?');
    $stmt->execute([$nome]);
    $id = $stmt->fetchColumn();

    if ($id === false) {
        throw new RuntimeException("Categoria '{$nome}' não encontrada. Abra o sistema uma vez para o banco ser preparado.");
    }

    return (int) $id;
};

$pdo->beginTransaction();

try {
    // recria a conta de demonstração (as tabelas filhas saem junto, por ON DELETE CASCADE)
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE email = ? OR nickname = ?');
    $stmt->execute([DEMO_EMAIL, DEMO_NICKNAME]);

    $pdo->prepare(
        "INSERT INTO usuarios (nome, nickname, email, telefone, senha, data_nascimento, perfil, tema)
         VALUES (?, ?, ?, ?, ?, ?, 'comum', 'claro')"
    )->execute([
        'Usuário Demonstração', DEMO_NICKNAME, DEMO_EMAIL, '11999998888',
        password_hash(DEMO_SENHA, PASSWORD_BCRYPT), '2005-03-15',
    ]);
    $uid = (int) $pdo->lastInsertId();

    $cat = [];
    foreach (['Salário', 'Freelance', 'Rendimentos', 'Resgate de metas', 'Alimentação', 'Moradia', 'Transporte',
        'Saúde', 'Educação', 'Lazer', 'Compras', 'Contas e assinaturas', 'Metas'] as $nome) {
        $cat[$nome] = $categoria($nome);
    }

    $reg = $pdo->prepare(
        'INSERT INTO registro_financeiro (usuario_id, categoria_id, tipo, valor, data_registro, descricao, parcelamento_id, numero_parcela, meta_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $lancar = function (string $tipo, string $categoriaNome, float $valor, string $dia, string $descricao, ?int $parcelamento = null, ?int $parcela = null, ?int $meta = null) use ($reg, $uid, $cat): void {
        $reg->execute([$uid, $cat[$categoriaNome], $tipo, $valor, $dia, $descricao, $parcelamento, $parcela, $meta]);
    };

    // seis meses de movimentação (valores fixos, para a demonstração ser sempre igual)
    $despesasFixas = [
        ['Moradia', 1100.00, 10, 'Aluguel'],
        ['Contas e assinaturas', 180.00, 12, 'Internet e celular'],
        ['Contas e assinaturas', 55.90, 15, 'Streaming'],
        ['Transporte', 220.00, 8, 'Passe mensal'],
    ];
    $variaveis = [
        // [mesesAtras => [[categoria, valor, dia, descrição], ...]]
        5 => [['Alimentação', 640.00, 6, 'Mercado'], ['Lazer', 120.00, 20, 'Cinema e lanche'], ['Saúde', 90.00, 18, 'Farmácia']],
        4 => [['Alimentação', 710.50, 7, 'Mercado'], ['Lazer', 210.00, 22, 'Show'], ['Compras', 260.00, 14, 'Roupas']],
        3 => [['Alimentação', 680.00, 5, 'Mercado'], ['Lazer', 150.00, 19, 'Restaurante'], ['Educação', 120.00, 9, 'Livros']],
        2 => [['Alimentação', 735.30, 6, 'Mercado'], ['Lazer', 180.00, 21, 'Parque e lanche'], ['Saúde', 150.00, 11, 'Consulta']],
        1 => [['Alimentação', 702.00, 7, 'Mercado'], ['Lazer', 240.00, 18, 'Viagem de fim de semana'], ['Compras', 190.00, 25, 'Presente']],
        0 => [['Alimentação', 410.00, 2, 'Mercado'], ['Lazer', 340.00, 3, 'Festa e lanche'], ['Transporte', 60.00, 3, 'Aplicativo de transporte']],
    ];

    for ($m = 5; $m >= 0; $m--) {
        $lancar('receita', 'Salário', 3800.00, $data($m, 5), 'Salário');

        if (in_array($m, [4, 2, 0], true)) {
            $lancar('receita', 'Freelance', 650.00, $data($m, 17), 'Projeto freelance');
        }
        if ($m <= 3) {
            $lancar('receita', 'Rendimentos', 38.40 + (3 - $m) * 4.2, $data($m, 28), 'Rendimento da poupança');
        }

        foreach ($despesasFixas as [$c, $valor, $dia, $descricao]) {
            // no mês atual só entra o que já venceu
            if ($m === 0 && $dia > (int) $hoje->format('j')) {
                continue;
            }
            $lancar('despesa', $c, $valor, $data($m, $dia), $descricao);
        }
        foreach ($variaveis[$m] as [$c, $valor, $dia, $descricao]) {
            $lancar('despesa', $c, $valor, $data($m, $dia), $descricao);
        }
    }

    // metas: aporte = despesa em "Metas" e resgate = receita em "Resgate de metas", ambos ligados à meta
    $novaMeta = $pdo->prepare(
        'INSERT INTO metas (usuario_id, nome_meta, tipo_meta, valor_alvo, valor_atual, data_limite) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $metas = [
        ['Reserva de emergência', 'economizar', 10000.00, $hoje->modify('+10 months')->format('Y-m-d'),
            [[4, 12, 1500.00], [3, 12, 1500.00], [2, 12, 1000.00], [1, 12, -500.00]]],
        ['Viagem de formatura', 'comprar', 4000.00, $hoje->modify('+6 months')->format('Y-m-d'),
            [[2, 20, 800.00], [1, 20, 700.00]]],
        ['Notebook novo', 'comprar', 5000.00, $hoje->modify('+2 months')->format('Y-m-d'),
            [[5, 15, 2000.00], [4, 15, 1500.00], [3, 15, 1500.00]]],
    ];
    foreach ($metas as [$nome, $tipo, $alvo, $limite, $movimentos]) {
        $atual = array_sum(array_column($movimentos, 2));
        $novaMeta->execute([$uid, $nome, $tipo, $alvo, $atual, $limite]);
        $metaId = (int) $pdo->lastInsertId();

        foreach ($movimentos as [$mes, $dia, $valor]) {
            if ($valor >= 0) {
                $lancar('despesa', 'Metas', $valor, $data($mes, $dia), "Aporte na meta: {$nome}", null, null, $metaId);
            } else {
                $lancar('receita', 'Resgate de metas', -$valor, $data($mes, $dia), "Resgate da meta: {$nome}", null, null, $metaId);
            }
        }
    }

    // parcelamentos com parte das parcelas já lançadas
    $novoParcelamento = $pdo->prepare(
        'INSERT INTO parcelamentos (usuario_id, categoria_id, descricao, valor_total, quantidade_parcelas, parcelas_pagas, data_primeira_parcela, tag, valor_parcela)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $parcelamentos = [
        ['Fone de ouvido', 'Compras', 600.00, 6, 3, 3, 'eletrônicos'],
        ['Curso de inglês', 'Educação', 1200.00, 6, 2, 1, 'estudos'],
    ];
    foreach ($parcelamentos as [$descricao, $c, $total, $qtd, $pagas, $mesesAtras, $tag]) {
        $valorParcela = round($total / $qtd, 2);
        $primeira = $data($mesesAtras, 10);
        $novoParcelamento->execute([$uid, $cat[$c], $descricao, $total, $qtd, $pagas, $primeira, $tag, $valorParcela]);
        $parcelamentoId = (int) $pdo->lastInsertId();

        for ($n = 1; $n <= $pagas; $n++) {
            $dia = (new DateTimeImmutable($primeira))->modify('+' . ($n - 1) . ' months')->format('Y-m-d');
            $lancar('despesa', $c, $valorParcela, $dia > $hoje->format('Y-m-d') ? $hoje->format('Y-m-d') : $dia, "{$descricao} ({$n}/{$qtd})", $parcelamentoId, $n);
        }
    }

    // limites mensais por categoria (o de Lazer já está estourado no mês atual, para aparecer o aviso do RF 12)
    $limite = $pdo->prepare('INSERT INTO limites_categoria (usuario_id, categoria_id, valor_limite) VALUES (?, ?, ?)');
    foreach (['Alimentação' => 900.00, 'Lazer' => 300.00, 'Transporte' => 350.00] as $nome => $valor) {
        $limite->execute([$uid, $cat[$nome], $valor]);
    }

    // gamificação: 295 XP (nível 2). O acesso de hoje soma 5 XP e leva ao nível 3 (300 XP) com o aviso na tela
    $historico = $pdo->prepare('INSERT INTO historico_xp (usuario_id, acao, xp, data_concessao) VALUES (?, ?, ?, ?)');
    $xpTotal = 0;
    $conceder = function (string $acao, int $xp, string $dia) use ($historico, $uid, &$xpTotal): void {
        $historico->execute([$uid, $acao, $xp, $dia]);
        $xpTotal += $xp;
    };
    $conceder('perfil_completo', 20, $data(5, 1));
    $conceder('preferencias', 10, $data(5, 1));
    $conceder('relatorios', 15, $data(3, 2));
    $conceder('simulador', 10, $data(2, 2));
    $conceder('streak_7', 25, $hoje->modify('-6 days')->format('Y-m-d'));
    for ($i = 1; $i <= 43; $i++) {
        $conceder('acesso_diario', 5, $hoje->modify("-{$i} days")->format('Y-m-d'));
    }

    // última visita ontem: o login de hoje continua a sequência (13 dias) e concede o XP diário
    $pdo->prepare(
        'INSERT INTO usuario_comum (usuario_id, xp_total, nivel, streak_atual, maior_streak, ultimo_acesso, visibilidade_ranking)
         VALUES (?, ?, 2, 12, 12, ?, 1)'
    )->execute([$uid, $xpTotal, $hoje->modify('-1 day')->format('Y-m-d')]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Não foi possível criar os dados de demonstração: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo "Conta de demonstração criada.\n";
echo '  E-mail: ' . DEMO_EMAIL . "\n";
echo '  Senha:  ' . DEMO_SENHA . "\n";
echo "  XP: {$xpTotal} (nível 2). O primeiro login de hoje mostra +5 XP e a subida para o nível 3.\n";
