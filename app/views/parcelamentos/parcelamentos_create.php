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
    <title>Novo Parcelamento • DFLG Investments</title>

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
            <h2 class="mb-0">Novo Parcelamento</h2>
            <a href="<?= URL_BASE ?>/parcelamentos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (isset($erros['parcelamento'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erros['parcelamento']) ?></div>
                <?php endif; ?>

                <form action="<?= URL_BASE ?>/parcelamentos/salvar" method="post">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" maxlength="200"
                                value="<?= htmlspecialchars($parcelamento['descricao'] ?? '') ?>">
                            <?php if (isset($erros['descricao'])): ?>
                                <div class="text-danger small"><?= $erros['descricao'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="tag" class="form-label">Tag da compra</label>
                            <input type="text" class="form-control" id="tag" name="tag" maxlength="50"
                                value="<?= htmlspecialchars($parcelamento['tag'] ?? '') ?>">
                            <?php if (isset($erros['tag'])): ?>
                                <div class="text-danger small"><?= $erros['tag'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="categoriaId" class="form-label">Categoria</label>
                            <select class="form-select" id="categoriaId" name="categoriaId">
                                <option value="">Sem categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria->getId() ?>"
                                        <?= (isset($parcelamento['categoriaId']) && $parcelamento['categoriaId'] == $categoria->getId()) ? 'selected' : '' ?>>
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
                                value="<?= htmlspecialchars($parcelamento['valorParcela'] ?? '') ?>">
                            <?php if (isset($erros['valorParcela'])): ?>
                                <div class="text-danger small"><?= $erros['valorParcela'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="quantidadeParcelas" class="form-label">Qtd. de parcelas</label>
                            <input type="number" min="2" step="1" class="form-control" id="quantidadeParcelas" name="quantidadeParcelas" max="420"
                                value="<?= htmlspecialchars($parcelamento['quantidadeParcelas'] ?? '') ?>">
                            <?php if (isset($erros['quantidadeParcelas'])): ?>
                                <div class="text-danger small"><?= $erros['quantidadeParcelas'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="dataPrimeiraParcela" class="form-label">Data da 1ª parcela</label>
                            <input type="date" class="form-control" id="dataPrimeiraParcela" name="dataPrimeiraParcela"
                                value="<?= htmlspecialchars($parcelamento['dataPrimeiraParcela'] ?? date('Y-m-d')) ?>">
                            <?php if (isset($erros['dataPrimeiraParcela'])): ?>
                                <div class="text-danger small"><?= $erros['dataPrimeiraParcela'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Pode ser uma data passada, de hoje ou futura.</div>
                        </div>

                    </div>

                    <div class="alert alert-info small mt-4 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Nenhuma parcela é lançada no cadastro. Na lista de parcelamentos, use <strong>Lançar parcela</strong>
                        a cada parcela paga: a parcela vencida entra na data do vencimento, e a antecipada entra com a data de hoje.
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