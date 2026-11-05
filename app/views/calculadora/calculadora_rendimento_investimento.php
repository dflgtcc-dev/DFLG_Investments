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
    <title>Quanto meu investimento rendeu • DFLG Investments</title>

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
            <h2 class="mb-0">Quanto meu investimento rendeu</h2>
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

                        <form method="post" action="<?= URL_BASE ?>/calculadoras/rendimento-investimento/calcular">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="valorInicial" class="form-label">Valor inicial (R$)</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="valorInicial" name="valorInicial"
                                        value="<?= htmlspecialchars($dados['valorInicial'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="valorFinal" class="form-label">Valor final (R$)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="valorFinal" name="valorFinal"
                                        value="<?= htmlspecialchars($dados['valorFinal'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="prazoMeses" class="form-label">Período (meses)</label>
                                    <input type="number" step="1" min="1" max="1200" class="form-control" id="prazoMeses" name="prazoMeses"
                                        value="<?= htmlspecialchars($dados['prazoMeses'] ?? '') ?>" required>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                        </form>

                        <?php if (!empty($resultado)): ?>
                            <div class="row g-3 mt-4">
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Rendimento</small>
                                            <h2 class="h5 mt-2 <?= $resultado['rendimento'] >= 0 ? 'text-success' : 'text-danger' ?>">R$ <?= number_format($resultado['rendimento'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Rentabilidade total</small>
                                            <h2 class="h5 mt-2 <?= $resultado['rentabilidadeTotal'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($resultado['rentabilidadeTotal'], 2, ',', '.') ?>%</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Taxa média ao mês</small>
                                            <h2 class="h5 mt-2"><?= number_format($resultado['taxaMensal'], 2, ',', '.') ?>%</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Taxa média ao ano</small>
                                            <h2 class="h5 mt-2"><?= number_format($resultado['taxaAnual'], 2, ',', '.') ?>%</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mt-3 mb-0">Considera que não houve aportes nem resgates durante o período.</p>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <aside class="col-lg-4">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <h2 class="h5">Como funciona</h2>
                        <p class="text-muted small">Compara o valor inicial com o valor final e calcula a rentabilidade total e a taxa média, ao mês e ao ano, que leva um ao outro. Se o valor final for menor que o inicial, a rentabilidade aparece negativa.</p>
                        <p class="text-muted small mb-0">Cálculo matemático com os dados que você informa. Não é recomendação de investimento.</p>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>