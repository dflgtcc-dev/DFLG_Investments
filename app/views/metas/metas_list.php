<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $lista
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas • DFLG Investments</title>

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
            <h2 class="mb-0"><i class="bi bi-bullseye me-2"></i>Minhas Metas</h2>
            <a href="<?= URL_BASE ?>/metas/cadastrar" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Meta
            </a>
        </div>

        <?php if (empty($lista)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Você ainda não cadastrou nenhuma meta. Que tal começar agora?
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle tabela-cards">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Meta</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Progresso</th>
                                    <th class="px-4 py-3">Data limite</th>
                                    <th class="px-4 py-3 text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $meta): ?>
                                    <tr>
                                        <td class="px-4 py-3 td-titulo fw-semibold">
                                            <?= htmlspecialchars($meta->getNomeMeta()) ?>
                                            <?php if ($meta->eConcluida()): ?>
                                                <span class="badge bg-success ms-1">Concluída</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3" data-label="Tipo">
                                            <span class="badge bg-secondary text-capitalize"><?= htmlspecialchars($meta->getTipoMeta()) ?></span>
                                        </td>
                                        <td class="px-4 py-3 td-bloco" data-label="Progresso" style="min-width: 220px;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $meta->getProgresso() ?>%"></div>
                                            </div>
                                            <small class="text-muted">
                                                R$ <?= number_format($meta->getValorAtual(), 2, ',', '.') ?>
                                                de R$ <?= number_format($meta->getValorAlvo(), 2, ',', '.') ?>
                                                (<?= $meta->getProgresso() ?>%)
                                            </small>
                                        </td>
                                        <td class="px-4 py-3" data-label="Data limite">
                                            <?= date('d/m/Y', strtotime($meta->getDataLimite())) ?>
                                        </td>
                                        <td class="px-4 py-3 text-end text-nowrap td-acoes">
                                            <?php if (!$meta->eConcluida()): ?>
                                                <a href="<?= URL_BASE ?>/metas/aportar?id=<?= $meta->getId() ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-piggy-bank"></i> Aportar
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($meta->getValorAtual() > 0): ?>
                                                <a href="<?= URL_BASE ?>/metas/resgatar?id=<?= $meta->getId() ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-box-arrow-in-down"></i> Resgatar
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= URL_BASE ?>/metas/editar?id=<?= $meta->getId() ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form method="POST" action="<?= URL_BASE ?>/metas/excluir" class="d-inline"
                                                onsubmit="return confirm('Deseja realmente excluir esta meta? Todo o valor guardado volta para o seu saldo.')">
                                                <input type="hidden" name="id" value="<?= $meta->getId() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>