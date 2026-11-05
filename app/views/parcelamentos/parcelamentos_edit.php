<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $categorias
 * @var mixed $erros
 * @var mixed $parcelamento
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Parcelamento • DFLG Investments</title>

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
            <h2 class="mb-0">Editar Parcelamento</h2>
            <a href="<?= URL_BASE ?>/parcelamentos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (isset($erros['parcelamento'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erros['parcelamento']) ?></div>
                <?php endif; ?>

                <form action="<?= URL_BASE ?>/parcelamentos/atualizar" method="post">

                    <input type="hidden" name="id" value="<?= $parcelamento->getId() ?>">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" maxlength="200"
                                value="<?= htmlspecialchars($parcelamento->getDescricao()) ?>">
                            <?php if (isset($erros['descricao'])): ?>
                                <div class="text-danger small"><?= $erros['descricao'] ?></div>
                            <?php endif; ?>
                            <?php if (isset($erros['descricao'])): ?>
                                <div class="text-danger small"><?= $erros['descricao'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="tag" class="form-label">Tag da compra</label>
                            <input type="text" class="form-control" id="tag" name="tag" maxlength="50"
                                value="<?= htmlspecialchars($parcelamento->getTag() ?? '') ?>">
                            <?php if (isset($erros['tag'])): ?>
                                <div class="text-danger small"><?= $erros['tag'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="categoriaId" class="form-label">Categoria</label>
                            <select class="form-select" id="categoriaId" name="categoriaId">
                                <option value="">Sem categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria->getId() ?>" <?= $parcelamento->getCategoriaId() == $categoria->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['categoriaId'])): ?>
                                <div class="text-danger small"><?= $erros['categoriaId'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="valorParcela" class="form-label">Valor da parcela (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="valorParcela" name="valorParcela" max="99999999.99"
                                value="<?= $parcelamento->getValorParcela() ?>">
                            <?php if (isset($erros['valorParcela'])): ?>
                                <div class="text-danger small"><?= $erros['valorParcela'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="quantidadeParcelas" class="form-label">Qtd. de parcelas</label>
                            <input type="number" min="<?= max(2, $parcelamento->getParcelasPagas()) ?>" step="1" class="form-control" id="quantidadeParcelas" name="quantidadeParcelas" max="420"
                                value="<?= $parcelamento->getQuantidadeParcelas() ?>">
                            <?php if (isset($erros['quantidadeParcelas'])): ?>
                                <div class="text-danger small"><?= $erros['quantidadeParcelas'] ?></div>
                            <?php endif; ?>
                            <?php if ($parcelamento->getParcelasPagas() > 0): ?>
                                <div class="form-text">Já pagas: <?= $parcelamento->getParcelasPagas() ?>. Não pode ficar abaixo disso.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="dataPrimeiraParcela" class="form-label">Data da 1ª parcela</label>
                            <input type="date" class="form-control" id="dataPrimeiraParcela"
                                value="<?= htmlspecialchars($parcelamento->getDataPrimeiraParcela()) ?>" disabled>
                            <div class="form-text">Não pode ser alterada depois do cadastro.</div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-text">
                                Categoria, descrição e tag valem também para as parcelas já lançadas.
                                O valor novo vale só para as próximas parcelas (as lançadas mantêm o valor que foi pago).
                            </div>
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