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
    <title>Comparador de cenários • DFLG Investments</title>

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
            <h2 class="mb-0">Comparador de cenários</h2>
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

                        <form method="post" action="<?= URL_BASE ?>/calculadoras/comparador-cenarios/calcular">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="card h-100 border">
                                        <div class="card-body">
                                            <h3 class="h6 mb-3">Cenário 1</h3>
                                            <div class="row g-3">

                                            <div class="col-md-12">
                                                <label for="valorInicial1" class="form-label">Valor inicial (R$)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="valorInicial1" name="valorInicial1"
                                                    value="<?= htmlspecialchars($dados['valorInicial1'] ?? '') ?>" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="aporteMensal1" class="form-label">Aporte mensal (R$)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="aporteMensal1" name="aporteMensal1"
                                                    value="<?= htmlspecialchars($dados['aporteMensal1'] ?? '') ?>" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="taxa1" class="form-label">Taxa de rendimento (%)</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" class="form-control" id="taxa1" name="taxa1"
                                                        value="<?= htmlspecialchars($dados['taxa1'] ?? '') ?>" required>
                                                    <select class="form-select" name="unidadeTaxa1" aria-label="Unidade da taxa">
                                                        <option value="anual" <?= ($dados['unidadeTaxa1'] ?? 'anual') === 'anual' ? 'selected' : '' ?>>ao ano</option>
                                                        <option value="mensal" <?= ($dados['unidadeTaxa1'] ?? '') === 'mensal' ? 'selected' : '' ?>>ao mês</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="prazoMeses1" class="form-label">Prazo (meses)</label>
                                                <input type="number" step="1" min="1" max="1200" class="form-control" id="prazoMeses1" name="prazoMeses1"
                                                    value="<?= htmlspecialchars($dados['prazoMeses1'] ?? '') ?>" required>
                                            </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 border">
                                        <div class="card-body">
                                            <h3 class="h6 mb-3">Cenário 2</h3>
                                            <div class="row g-3">

                                            <div class="col-md-12">
                                                <label for="valorInicial2" class="form-label">Valor inicial (R$)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="valorInicial2" name="valorInicial2"
                                                    value="<?= htmlspecialchars($dados['valorInicial2'] ?? '') ?>" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="aporteMensal2" class="form-label">Aporte mensal (R$)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="aporteMensal2" name="aporteMensal2"
                                                    value="<?= htmlspecialchars($dados['aporteMensal2'] ?? '') ?>" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="taxa2" class="form-label">Taxa de rendimento (%)</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" class="form-control" id="taxa2" name="taxa2"
                                                        value="<?= htmlspecialchars($dados['taxa2'] ?? '') ?>" required>
                                                    <select class="form-select" name="unidadeTaxa2" aria-label="Unidade da taxa">
                                                        <option value="anual" <?= ($dados['unidadeTaxa2'] ?? 'anual') === 'anual' ? 'selected' : '' ?>>ao ano</option>
                                                        <option value="mensal" <?= ($dados['unidadeTaxa2'] ?? '') === 'mensal' ? 'selected' : '' ?>>ao mês</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="prazoMeses2" class="form-label">Prazo (meses)</label>
                                                <input type="number" step="1" min="1" max="1200" class="form-control" id="prazoMeses2" name="prazoMeses2"
                                                    value="<?= htmlspecialchars($dados['prazoMeses2'] ?? '') ?>" required>
                                            </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                        </form>

                        <?php if (!empty($resultado)): ?>
                            <?php if ($resultado['melhor'] === 0): ?>
                                <div class="alert alert-secondary mt-4 mb-0">Os dois cenários terminam com o mesmo valor.</div>
                            <?php else: ?>
                                <div class="alert alert-success mt-4 mb-0">
                                    O cenário <?= $resultado['melhor'] ?> termina com
                                    <strong>R$ <?= number_format($resultado['diferenca'], 2, ',', '.') ?></strong> a mais.
                                </div>
                            <?php endif; ?>

                            <?php if ($resultado['prazosDiferentes']): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    Os cenários têm prazos diferentes (<?= $resultado['cenario1']['prazoMeses'] ?> e
                                    <?= $resultado['cenario2']['prazoMeses'] ?> meses): a comparação junta períodos distintos.
                                </div>
                            <?php endif; ?>

                            <div class="row g-3 mt-2">
                                <?php foreach ([1, 2] as $numero): ?>
                                    <?php $cenario = $resultado['cenario' . $numero]; ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 bg-light border-0">
                                            <div class="card-body">
                                                <h3 class="h6">
                                                    Cenário <?= $numero ?>
                                                    <?php if ($resultado['melhor'] === $numero): ?>
                                                        <span class="badge bg-success ms-1">Maior montante</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <p class="text-muted small">
                                                    Taxa equivalente: <?= number_format($cenario['taxaMensal'], 4, ',', '.') ?>% ao mês
                                                    e <?= number_format($cenario['taxaAnual'], 2, ',', '.') ?>% ao ano
                                                </p>
                                                <small class="text-muted">Total investido</small>
                                                <p class="mb-2">R$ <?= number_format($cenario['totalInvestido'], 2, ',', '.') ?></p>
                                                <small class="text-muted">Rendimento</small>
                                                <p class="mb-2 text-success">R$ <?= number_format($cenario['rendimento'], 2, ',', '.') ?></p>
                                                <small class="text-muted">Montante final</small>
                                                <h2 class="h5 mt-1 mb-0">R$ <?= number_format($cenario['montanteFinal'], 2, ',', '.') ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <aside class="col-lg-4">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <h2 class="h5">Como funciona</h2>
                        <p class="text-muted small">Calcula dois investimentos com juros compostos e aportes no fim de cada mês, lado a lado. O melhor cenário é o que termina com o maior montante final.</p>
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