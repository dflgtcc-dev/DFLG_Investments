<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $dados
 * @var mixed $erros
 * @var mixed $resultado
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempo para atingir uma meta • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main class="container pb-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 cabecalho-pagina">
            <h2 class="mb-0">Tempo para atingir uma meta</h2>
            <a href="<?= URL_BASE ?>/calculadoras" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Calculadoras
            </a>
        </div>

        <div class="row g-4 align-items-start">

            <section class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">

                        <?php if (!empty($erros)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($erros as $erro): ?>
                                    <div><?= htmlspecialchars($erro) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= URL_BASE ?>/calculadoras/tempo-meta/calcular">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="valorAlvo" class="form-label">Valor que quero juntar (R$)</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="valorAlvo" name="valorAlvo"
                                        value="<?= htmlspecialchars($dados['valorAlvo'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="valorInicial" class="form-label">Valor que já tenho (R$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="valorInicial" name="valorInicial"
                                        value="<?= htmlspecialchars($dados['valorInicial'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="aporteMensal" class="form-label">Aporte mensal (R$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="aporteMensal" name="aporteMensal"
                                        value="<?= htmlspecialchars($dados['aporteMensal'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="taxa" class="form-label">Taxa de rendimento (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control" id="taxa" name="taxa"
                                            value="<?= htmlspecialchars($dados['taxa'] ?? '') ?>" required>
                                        <select class="form-select" name="unidadeTaxa" aria-label="Unidade da taxa">
                                            <option value="anual" <?= ($dados['unidadeTaxa'] ?? 'anual') === 'anual' ? 'selected' : '' ?>>ao ano</option>
                                            <option value="mensal" <?= ($dados['unidadeTaxa'] ?? '') === 'mensal' ? 'selected' : '' ?>>ao mês</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                        </form>

                        <?php if (!empty($resultado)): ?>
                            <?php if ($resultado['metaJaAtingida']): ?>
                                <div class="alert alert-success mt-4 mb-0">
                                    O valor que você já tem é igual ou maior que a meta. Ela já foi atingida.
                                </div>
                            <?php endif; ?>
                            <div class="alert alert-info mt-4 mb-0">
                                Taxa equivalente: <strong><?= number_format($resultado['taxaMensal'], 4, ',', '.') ?>% ao mês</strong>
                                e <strong><?= number_format($resultado['taxaAnual'], 2, ',', '.') ?>% ao ano</strong>.
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Tempo necessário</small>
                                            <h2 class="h4 mt-2 text-primary">
                                                <?= $resultado['anos'] ?> <?= $resultado['anos'] === 1 ? 'ano' : 'anos' ?>
                                                e <?= $resultado['mesesRestantes'] ?> <?= $resultado['mesesRestantes'] === 1 ? 'mês' : 'meses' ?>
                                            </h2>
                                            <small class="text-muted">(<?= $resultado['meses'] ?> <?= $resultado['meses'] === 1 ? 'mês' : 'meses' ?> no total)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Total investido</small>
                                            <h2 class="h5 mt-2">R$ <?= number_format($resultado['totalInvestido'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Rendimento</small>
                                            <h2 class="h5 mt-2 text-success">R$ <?= number_format($resultado['rendimento'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Valor alcançado</small>
                                            <h2 class="h5 mt-2">R$ <?= number_format($resultado['montanteFinal'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <aside class="col-lg-4">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <h2 class="h5">Como funciona</h2>
                        <p class="text-muted small">Calcula em quanto tempo você chega ao valor desejado, com o valor que já tem, os aportes mensais e o rendimento. O resultado é arredondado para cima, em meses inteiros. Os aportes entram no fim de cada mês.</p>
                        <p class="text-muted small mb-0">Projeção matemática com os dados que você informa. Não é recomendação de investimento.</p>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>