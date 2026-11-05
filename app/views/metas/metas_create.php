<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 * @var mixed $meta
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Meta • DFLG Investments</title>

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
            <h2 class="mb-0">Nova Meta</h2>
            <a href="<?= URL_BASE ?>/metas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= URL_BASE ?>/metas/salvar" method="post">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="nomeMeta" class="form-label">Nome da meta</label>
                            <input type="text" class="form-control" id="nomeMeta" name="nomeMeta"
                                value="<?= htmlspecialchars($meta['nomeMeta'] ?? '') ?>">
                            <?php if (isset($erros['nomeMeta'])): ?>
                                <div class="text-danger small"><?= $erros['nomeMeta'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoMeta" class="form-label">Tipo</label>
                            <select class="form-select" id="tipoMeta" name="tipoMeta">
                                <option value="economizar" <?= (isset($meta['tipoMeta']) && $meta['tipoMeta'] == 'economizar') ? 'selected' : '' ?>>Economizar</option>
                                <option value="comprar" <?= (isset($meta['tipoMeta']) && $meta['tipoMeta'] == 'comprar') ? 'selected' : '' ?>>Comprar algo</option>
                                <option value="investir" <?= (isset($meta['tipoMeta']) && $meta['tipoMeta'] == 'investir') ? 'selected' : '' ?>>Investir</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="valorAlvo" class="form-label">Valor alvo (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="valorAlvo" name="valorAlvo"
                                value="<?= htmlspecialchars($meta['valorAlvo'] ?? '') ?>">
                            <?php if (isset($erros['valorAlvo'])): ?>
                                <div class="text-danger small"><?= $erros['valorAlvo'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="valorAtual" class="form-label">Valor já guardado (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="valorAtual" name="valorAtual"
                                value="<?= htmlspecialchars($meta['valorAtual'] ?? '0') ?>">
                            <?php if (isset($erros['valorAtual'])): ?>
                                <div class="text-danger small"><?= $erros['valorAtual'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="dataLimite" class="form-label">Data limite</label>
                            <input type="date" class="form-control" id="dataLimite" name="dataLimite"
                                value="<?= htmlspecialchars($meta['dataLimite'] ?? '') ?>">
                            <?php if (isset($erros['dataLimite'])): ?>
                                <div class="text-danger small"><?= $erros['dataLimite'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">A data precisa ser posterior a hoje.</div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Salvar
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
