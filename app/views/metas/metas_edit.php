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
    <title>Editar Meta • DFLG Investments</title>

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
            <h2 class="mb-0">Editar Meta</h2>
            <a href="<?= URL_BASE ?>/metas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= URL_BASE ?>/metas/atualizar" method="post">

                    <input type="hidden" name="id" value="<?= $meta->getId() ?>">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="nomeMeta" class="form-label">Nome da meta</label>
                            <input type="text" class="form-control" id="nomeMeta" name="nomeMeta"
                                value="<?= htmlspecialchars($meta->getNomeMeta()) ?>">
                            <?php if (isset($erros['nomeMeta'])): ?>
                                <div class="text-danger small"><?= $erros['nomeMeta'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="tipoMeta" class="form-label">Tipo</label>
                            <select class="form-select" id="tipoMeta" name="tipoMeta">
                                <option value="economizar" <?= $meta->getTipoMeta() == 'economizar' ? 'selected' : '' ?>>Economizar</option>
                                <option value="comprar" <?= $meta->getTipoMeta() == 'comprar' ? 'selected' : '' ?>>Comprar algo</option>
                                <option value="investir" <?= $meta->getTipoMeta() == 'investir' ? 'selected' : '' ?>>Investir</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="valorAlvo" class="form-label">Valor alvo (R$)</label>
                            <input type="number" step="0.01" min="<?= max(0.01, round($meta->getValorAtual(), 2)) ?>" class="form-control" id="valorAlvo" name="valorAlvo"
                                value="<?= $meta->getValorAlvo() ?>">
                            <?php if (isset($erros['valorAlvo'])): ?>
                                <div class="text-danger small"><?= $erros['valorAlvo'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                Já guardado: R$ <?= number_format($meta->getValorAtual(), 2, ',', '.') ?> (muda pelos aportes). O valor alvo não pode ficar abaixo disso.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="dataLimite" class="form-label">Data limite</label>
                            <input type="date" class="form-control" id="dataLimite" name="dataLimite"
                                value="<?= htmlspecialchars($meta->getDataLimite()) ?>">
                            <?php if (isset($erros['dataLimite'])): ?>
                                <div class="text-danger small"><?= $erros['dataLimite'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">A data precisa ser posterior a hoje.</div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Atualizar
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