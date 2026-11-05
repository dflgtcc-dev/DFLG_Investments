<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 * @var mixed $meta
 * @var mixed $saldo
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resgatar da Meta • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <div class="container pb-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 cabecalho-pagina">
            <h2 class="mb-0">Resgatar da Meta</h2>
            <a href="<?= URL_BASE ?>/metas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h5 class="mb-3"><?= htmlspecialchars($meta->getNomeMeta()) ?></h5>

                <div class="progress mb-2" role="progressbar" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: <?= min($meta->getProgresso(), 100) ?>%"></div>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-4">
                    <span>Guardado: R$ <?= number_format($meta->getValorAtual(), 2, ',', '.') ?></span>
                    <span>Alvo: R$ <?= number_format($meta->getValorAlvo(), 2, ',', '.') ?></span>
                </div>

                <p class="mb-3">
                    Saldo disponível: <strong>R$ <?= number_format($saldo, 2, ',', '.') ?></strong>
                </p>

                <?php if (isset($erros['meta'])): ?>
                    <div class="alert alert-danger"><?= $erros['meta'] ?></div>
                <?php endif; ?>

                <form action="<?= URL_BASE ?>/metas/resgatar/salvar" method="post">

                    <input type="hidden" name="id" value="<?= $meta->getId() ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="valor" class="form-label">Valor a resgatar (R$)</label>
                            <input type="number" step="0.01" min="0.01" max="<?= $meta->getValorAtual() ?>" class="form-control" id="valor" name="valor">
                            <?php if (isset($erros['valor'])): ?>
                                <div class="text-danger small"><?= $erros['valor'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                O valor sai da meta e volta para o seu saldo. É uma transferência: não conta como receita nos totais.
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-box-arrow-in-down"></i> Resgatar
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>