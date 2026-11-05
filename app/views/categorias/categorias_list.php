<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $categorias
 * @var mixed $erros
 * @var mixed $mes
 * @var mixed $salvo
 * @var mixed $valores
 */
$erros = $erros ?? [];
$valores = $valores ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias • DFLG Investments</title>

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

        <div class="mb-4">
            <h2 class="mb-0"><i class="bi bi-tags me-2"></i>Categorias</h2>
            <p class="text-muted mb-0">Limite mensal de gastos · <?= htmlspecialchars($mes) ?></p>
        </div>

        <?php if ($salvo): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Limites salvos.
            </div>
        <?php endif; ?>

        <?php if (isset($erros['geral'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erros['geral']) ?></div>
        <?php endif; ?>

        <form action="<?= URL_BASE ?>/categorias/limites" method="post">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle tabela-cards">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Categoria</th>
                                    <th class="px-4 py-3 text-end">Gasto no mês</th>
                                    <th class="px-4 py-3" style="min-width: 190px;">Limite mensal (R$)</th>
                                    <th class="px-4 py-3" style="min-width: 200px;">Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $categoria): ?>
                                    <?php
                                    $id = $categoria['id'];
                                    $digitado = array_key_exists($id, $valores)
                                        ? $valores[$id]
                                        : ($categoria['limite'] !== null ? number_format($categoria['limite'], 2, '.', '') : '');
                                    ?>
                                    <tr>
                                        <td class="px-4 py-3 td-titulo fw-semibold">
                                            <i class="bi <?= htmlspecialchars($categoria['icone'] ?? 'bi-tag') ?> me-2"></i>
                                            <?= htmlspecialchars($categoria['nome']) ?>
                                        </td>
                                        <td class="px-4 py-3 text-end text-nowrap" data-label="Gasto no mês">R$ <?= number_format($categoria['gasto'], 2, ',', '.') ?></td>
                                        <td class="px-4 py-3 td-bloco" data-label="Limite mensal (R$)">
                                            <input type="number" step="0.01" min="0.01" inputmode="decimal"
                                                class="form-control <?= isset($erros[$id]) ? 'is-invalid' : '' ?>"
                                                name="limite[<?= $id ?>]" placeholder="Sem limite"
                                                value="<?= htmlspecialchars((string) $digitado) ?>">
                                            <?php if (isset($erros[$id])): ?>
                                                <div class="text-danger small"><?= htmlspecialchars($erros[$id]) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 td-bloco" data-label="Situação">
                                            <?php if ($categoria['limite'] === null): ?>
                                                <span class="text-muted small">Sem limite</span>
                                            <?php else: ?>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar <?= $categoria['excedido'] ? 'bg-danger' : 'bg-success' ?>"
                                                        style="width: <?= $categoria['percentual'] ?>%"></div>
                                                </div>
                                                <?php if ($categoria['excedido']): ?>
                                                    <span class="badge bg-danger mt-1">Excedido</span>
                                                    <span class="small text-danger">
                                                        R$ <?= number_format($categoria['gasto'] - $categoria['limite'], 2, ',', '.') ?> acima
                                                    </span>
                                                <?php else: ?>
                                                    <span class="small text-muted">
                                                        <?= $categoria['percentual'] ?>% de R$ <?= number_format($categoria['limite'], 2, ',', '.') ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="text-muted small mb-0">
                    Deixe o campo vazio para não ter limite. Conta só despesas com data até hoje.
                    Quando uma categoria passa do limite, o aviso aparece no Dashboard, em Finanças e em Parcelamentos.
                </p>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Salvar limites
                </button>
            </div>
        </form>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
