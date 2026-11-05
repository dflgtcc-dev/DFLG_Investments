<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $categorias
 * @var mixed $erros
 * @var mixed $transacao
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Registro • DFLG Investments</title>

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
            <h2 class="mb-0">Novo Registro Financeiro</h2>
            <a href="<?= URL_BASE ?>/transacoes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= URL_BASE ?>/transacoes/salvar" method="post">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="despesa" <?= (isset($transacao['tipo']) && $transacao['tipo'] == 'despesa') ? 'selected' : '' ?>>Despesa</option>
                                <option value="receita" <?= (isset($transacao['tipo']) && $transacao['tipo'] == 'receita') ? 'selected' : '' ?>>Receita</option>
                            </select>
                            <?php if (isset($erros['tipo'])): ?>
                                <div class="text-danger small"><?= $erros['tipo'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="valor" class="form-label">Valor (R$)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="valor" name="valor"
                                value="<?= htmlspecialchars($transacao['valor'] ?? '') ?>">
                            <?php if (isset($erros['valor'])): ?>
                                <div class="text-danger small"><?= $erros['valor'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">O valor precisa ser maior que zero.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="dataRegistro" class="form-label">Data</label>
                            <input type="date" class="form-control" id="dataRegistro" name="dataRegistro"
                                value="<?= htmlspecialchars($transacao['dataRegistro'] ?? date('Y-m-d')) ?>">
                            <?php if (isset($erros['dataRegistro'])): ?>
                                <div class="text-danger small"><?= $erros['dataRegistro'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="categoriaId" class="form-label">Categoria</label>
                            <select class="form-select" id="categoriaId" name="categoriaId">
                                <option value="">Selecione...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria->getId() ?>" data-tipo="<?= $categoria->getTipo() ?>"
                                        <?= (isset($transacao['categoriaId']) && $transacao['categoriaId'] == $categoria->getId()) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['categoriaId'])): ?>
                                <div class="text-danger small"><?= $erros['categoriaId'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao"
                                value="<?= htmlspecialchars($transacao['descricao'] ?? '') ?>">
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
    <script>
        const tipoSelect = document.getElementById('tipo');
        const categoriaSelect = document.getElementById('categoriaId');
        const categoriaOptions = Array.from(categoriaSelect.options).filter(o => o.value !== '');

        function filtrarCategorias() {
            const tipo = tipoSelect.value;
            const valorAtual = categoriaSelect.value;
            let manteveSelecao = false;

            categoriaOptions.forEach(option => {
                const visivel = option.dataset.tipo === tipo;
                option.hidden = !visivel;
                if (visivel && option.value === valorAtual) {
                    manteveSelecao = true;
                }
            });

            if (!manteveSelecao) {
                categoriaSelect.value = '';
            }
        }

        tipoSelect.addEventListener('change', filtrarCategorias);
        filtrarCategorias();
    </script>
</body>

</html>
