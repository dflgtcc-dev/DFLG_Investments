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
    <title>Valor real (inflação) • DFLG Investments</title>

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
            <h2 class="mb-0">Valor real (inflação)</h2>
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

                        <form method="post" action="<?= URL_BASE ?>/calculadoras/valor-real/calcular">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="valor" class="form-label">Valor (R$)</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="valor" name="valor"
                                        value="<?= htmlspecialchars($dados['valor'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="prazoMeses" class="form-label">Prazo (meses)</label>
                                    <input type="number" step="1" min="1" max="1200" class="form-control" id="prazoMeses" name="prazoMeses"
                                        value="<?= htmlspecialchars($dados['prazoMeses'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="inflacao" class="form-label">Inflação (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control" id="inflacao" name="inflacao"
                                            value="<?= htmlspecialchars($dados['inflacao'] ?? '') ?>" required>
                                        <select class="form-select" name="unidadeInflacao" aria-label="Unidade da taxa">
                                            <option value="anual" <?= ($dados['unidadeInflacao'] ?? 'anual') === 'anual' ? 'selected' : '' ?>>ao ano</option>
                                            <option value="mensal" <?= ($dados['unidadeInflacao'] ?? '') === 'mensal' ? 'selected' : '' ?>>ao mês</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                        </form>

                        <?php if (!empty($resultado)): ?>
                            <div class="alert alert-info mt-4 mb-0">
                                Inflação equivalente: <strong><?= number_format($resultado['taxaMensal'], 4, ',', '.') ?>% ao mês</strong>
                                e <strong><?= number_format($resultado['taxaAnual'], 2, ',', '.') ?>% ao ano</strong>.
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Para manter o poder de compra</small>
                                            <h2 class="h5 mt-2 text-primary">R$ <?= number_format($resultado['valorCorrigido'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Poder de compra do valor no futuro</small>
                                            <h2 class="h5 mt-2">R$ <?= number_format($resultado['poderDeCompra'], 2, ',', '.') ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body">
                                            <small class="text-muted">Perda de poder de compra</small>
                                            <h2 class="h5 mt-2 text-danger"><?= number_format($resultado['perdaPercentual'], 2, ',', '.') ?>%</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $valorInformado = (float) ($dados['valor'] ?? 0);
                            $prazoInformado = (int) ($dados['prazoMeses'] ?? 0);
                            ?>
                            <p class="text-muted small mt-3 mb-0">
                                Daqui a <?= $prazoInformado ?> <?= $prazoInformado === 1 ? 'mês' : 'meses' ?>, você precisará de
                                R$ <?= number_format($resultado['valorCorrigido'], 2, ',', '.') ?> para comprar o que
                                R$ <?= number_format($valorInformado, 2, ',', '.') ?> compram hoje. E R$ <?= number_format($valorInformado, 2, ',', '.') ?>
                                recebidos nessa data valerão R$ <?= number_format($resultado['poderDeCompra'], 2, ',', '.') ?> em poder de compra de hoje.
                            </p>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <aside class="col-lg-4">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <h2 class="h5">Como funciona</h2>
                        <p class="text-muted small">Mostra o efeito da inflação sobre o poder de compra: quanto será preciso no futuro para comprar o mesmo que o valor compra hoje, e quanto um valor recebido no futuro vale em poder de compra de hoje.</p>
                        <p class="text-muted small mb-0">Projeção matemática com a inflação que você informa. Não é recomendação de investimento.</p>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>