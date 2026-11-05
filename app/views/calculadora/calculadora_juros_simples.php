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
    <title>Juros simples • DFLG Investments</title>

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
            <h2 class="mb-0">Juros simples</h2>
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

                        <form method="post" action="<?= URL_BASE ?>/calculadoras/juros-simples/calcular">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="capital" class="form-label">Capital (R$)</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="capital" name="capital"
                                        value="<?= htmlspecialchars($dados['capital'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="taxa" class="form-label">Taxa de juros (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control" id="taxa" name="taxa"
                                            value="<?= htmlspecialchars($dados['taxa'] ?? '') ?>" required>
                                        <select class="form-select" name="unidadeTaxa" aria-label="Unidade da taxa">
                                            <option value="anual" <?= ($dados['unidadeTaxa'] ?? 'anual') === 'anual' ? 'selected' : '' ?>>ao ano</option>
                                            <option value="mensal" <?= ($dados['unidadeTaxa'] ?? '') === 'mensal' ? 'selected' : '' ?>>ao mês</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="prazoMeses" class="form-label">Prazo (meses)</label>
                                    <input type="number" step="1" min="1" max="1200" class="form-control" id="prazoMeses" name="prazoMeses"
                                        value="<?= htmlspecialchars($dados['prazoMeses'] ?? '') ?>" required>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                        </form>

                        <?php if (!empty($resultado)): ?>
                            <div class="alert alert-info mt-4 mb-0">
                                Taxa usada: <strong><?= number_format($resultado['taxaMensal'], 4, ',', '.') ?>% ao mês</strong>
                                e <strong><?= number_format($resultado['taxaAnual'], 2, ',', '.') ?>% ao ano</strong>
                                (em juros simples, a taxa anual é dividida por 12).
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Juros</small>
                                            <h2 class="h5 mt-2 text-success">R$ <?= number_format($resultado['juros'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Montante final</small>
                                            <h2 class="h5 mt-2">R$ <?= number_format($resultado['montanteFinal'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 bg-light mt-3">
                                <div class="card-body">
                                    <small class="text-muted">Em juros compostos, com a mesma taxa</small>
                                    <h2 class="h5 mt-2">R$ <?= number_format($resultado['montanteComposto'], 2, ',', '.') ?></h2>
                                    <p class="text-muted small mb-0">
                                        <?php if (abs($resultado['diferencaComposto']) < 0.005): ?>
                                            Nesse prazo, os dois regimes dão o mesmo resultado.
                                        <?php elseif ($resultado['diferencaComposto'] > 0): ?>
                                            R$ <?= number_format($resultado['diferencaComposto'], 2, ',', '.') ?> a mais que nos juros simples.
                                        <?php else: ?>
                                            R$ <?= number_format(abs($resultado['diferencaComposto']), 2, ',', '.') ?> a menos que nos juros simples.
                                        <?php endif; ?>
                                    </p>
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
                        <p class="text-muted small">Nos juros simples, os juros incidem sempre sobre o capital inicial: juros = capital × taxa mensal × meses. A taxa anual é dividida por 12. Ao lado, o mesmo capital em juros compostos, para você ver a diferença.</p>
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