<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var array $indicadores
 * @var bool $ehChefe
 */
$engajamento = $indicadores['engajamento'];
$faixas = [
    'ativos' => ['titulo' => 'Ativos', 'detalhe' => 'acessaram nos últimos 7 dias', 'cor' => 'success'],
    'em_risco' => ['titulo' => 'Em risco', 'detalhe' => 'de 8 a 30 dias sem acessar', 'cor' => 'warning'],
    'inativos' => ['titulo' => 'Inativos', 'detalhe' => 'mais de 30 dias sem acessar, ou nunca acessaram', 'cor' => 'secondary'],
];
$aviso = $_SESSION['aviso_admin'] ?? null;
unset($_SESSION['aviso_admin']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo • DFLG Investments</title>

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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h2 class="mb-0">Painel Administrativo</h2>
                <span class="text-body-secondary small">
                    <?= $ehChefe ? 'Administrador chefe' : 'Administrador' ?>
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= URL_BASE ?>/admin/usuarios" class="btn btn-primary">
                    <i class="bi bi-people"></i> Gerenciar usuários
                </a>
                <?php if ($ehChefe): ?>
                    <a href="<?= URL_BASE ?>/admin/usuarios/novo" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Novo administrador
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($aviso): ?>
            <div class="alert alert-<?= htmlspecialchars($aviso['tipo']) ?>" role="alert">
                <?= htmlspecialchars($aviso['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary small">Usuários comuns</div>
                        <div class="fs-2 fw-bold"><?= $indicadores['comuns']['total'] ?></div>
                        <div class="small">
                            <?= $indicadores['comuns']['ativos'] ?> ativos • <?= $indicadores['comuns']['bloqueados'] ?> bloqueados
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary small">Administradores</div>
                        <div class="fs-2 fw-bold"><?= $indicadores['administradores'] ?></div>
                        <div class="small">incluindo o administrador chefe</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary small">Novos cadastros (7 dias)</div>
                        <div class="fs-2 fw-bold"><?= $indicadores['novos7'] ?></div>
                        <div class="small">usuários comuns</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary small">Novos cadastros (30 dias)</div>
                        <div class="fs-2 fw-bold"><?= $indicadores['novos30'] ?></div>
                        <div class="small">usuários comuns</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-1">Engajamento</h5>
                <p class="text-body-secondary small mb-4">
                    Considera os <?= $engajamento['total'] ?> usuários comuns ativos, separados pelo tempo desde o último acesso.
                </p>

                <?php foreach ($faixas as $chave => $faixa): ?>
                    <?php $dados = $engajamento[$chave]; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold"><?= $faixa['titulo'] ?>
                                <span class="text-body-secondary fw-normal small">• <?= $faixa['detalhe'] ?></span>
                            </span>
                            <span><?= $dados['quantidade'] ?> (<?= number_format($dados['percentual'], 1, ',', '.') ?>%)</span>
                        </div>
                        <div class="progress" role="progressbar" aria-label="<?= $faixa['titulo'] ?>"
                            aria-valuenow="<?= $dados['percentual'] ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-<?= $faixa['cor'] ?>" style="width: <?= $dados['percentual'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>