<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $calculadoras
 * @var mixed $slug
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadoras • DFLG Investments</title>

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

        <h2 class="mb-1">Calculadoras</h2>
        <p class="text-muted">Simule o crescimento do seu patrimônio e planeje seus objetivos.</p>

        <div class="row g-3 mt-2">
            <?php foreach ($calculadoras as $slug => $calculadora): ?>
                <div class="col-md-4">
                    <a href="<?= URL_BASE ?>/calculadoras/<?= htmlspecialchars($slug) ?>" class="text-decoration-none">
                        <div class="card stat-card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="mb-1">
                                    <i class="bi <?= htmlspecialchars($calculadora['icone']) ?> me-2 text-primary"></i><?= htmlspecialchars($calculadora['nome']) ?>
                                </h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($calculadora['descricao']) ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-muted small mt-4">As calculadoras fazem projeções matemáticas com os dados que você informa. Não são recomendação de investimento.</p>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>