<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $aviso
 * @var mixed $chave
 * @var mixed $descricao
 * @var mixed $minhaPosicao
 * @var mixed $periodo
 * @var mixed $periodos
 * @var mixed $usuarioId
 * @var mixed $usuarios
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <?php
    $podio = array_slice($usuarios, 0, 3);
    $demais = array_slice($usuarios, 3);

    // ordem visual do pódio: 2º, 1º e 3º
    $ordemPodio = [];

    foreach ([1, 0, 2] as $indice) {
        if (isset($podio[$indice])) {
            $ordemPodio[] = $podio[$indice];
        }
    }

    $rotuloXp = $periodo === 'geral' ? 'XP total' : 'XP no período';
    ?>

    <div class="container pb-5">

        <div class="mb-4">
            <h2 class="mb-0"><i class="bi bi-trophy me-2"></i>Ranking</h2>
            <p class="text-muted mb-0"><?= $descricao ?></p>
        </div>

        <ul class="nav nav-pills mb-4">
            <?php foreach ($periodos as $chave => $nome): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $periodo === $chave ? 'active' : '' ?>"
                        href="<?= URL_BASE ?>/ranking?periodo=<?= $chave ?>"><?= $nome ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($usuarios)): ?>
            <div class="alert alert-info">
                <?= $periodo === 'geral' ? 'Nenhum usuário aparece no ranking ainda.' : 'Ninguém pontuou neste período ainda.' ?>
            </div>
        <?php else: ?>

            <!-- Pódio -->
            <div class="podio mb-4">
                <?php foreach ($ordemPodio as $item): ?>
                    <div class="podio-lugar podio-<?= $item['posicao'] ?>">
                        <i class="bi <?= $item['posicao'] === 1 ? 'bi-trophy-fill' : 'bi-award-fill' ?> podio-medalha"></i>
                        <div class="fw-bold"><?= $item['posicao'] ?>º lugar</div>
                        <div class="podio-nick">
                            @<?= htmlspecialchars($item['nickname']) ?>
                            <?php if ($item['id'] === $usuarioId): ?>
                                <span class="badge bg-primary">Você</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">Nível <?= $item['nivel'] ?></div>
                        <div class="small"><?= number_format($item['xp'], 0, ',', '.') ?> XP</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Demais posições -->
            <?php if (!empty($demais)): ?>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posição</th>
                                    <th>Usuário</th>
                                    <th class="text-end">Nível</th>
                                    <th class="text-end"><?= $rotuloXp ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($demais as $item): ?>
                                    <tr>
                                        <td><?= $item['posicao'] ?>º</td>
                                        <td>
                                            @<?= htmlspecialchars($item['nickname']) ?>
                                            <?php if ($item['id'] === $usuarioId): ?>
                                                <span class="badge bg-primary">Você</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= $item['nivel'] ?></td>
                                        <td class="text-end"><?= number_format($item['xp'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <p class="text-muted small mt-3 mb-0">
                Ordem: nível atual e, depois, <?= $periodo === 'geral' ? 'XP total' : 'XP ganho no período' ?>.
                Em caso de empate, vale a maior sequência de acesso e, depois, o cadastro mais antigo.
            </p>

        <?php endif; ?>

        <!-- Situação de quem está vendo o ranking -->
        <?php if ($minhaPosicao): ?>
            <div class="card border-primary shadow-sm mt-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-person-circle me-1"></i>
                        <strong>Sua posição: <?= $minhaPosicao['posicao'] ?>º</strong>
                    </div>
                    <div class="text-end">
                        <span class="me-3">Nível <?= $minhaPosicao['nivel'] ?></span>
                        <span><?= number_format($minhaPosicao['xp'], 0, ',', '.') ?> XP</span>
                    </div>
                </div>
            </div>
        <?php elseif ($aviso === 'oculto'): ?>
            <div class="alert alert-warning mt-4 mb-0">
                <i class="bi bi-eye-slash me-1"></i>
                Você está oculto no ranking. Para aparecer, ative "Aparecer no ranking" nas
                <a href="<?= URL_BASE ?>/perfil" class="alert-link">preferências do perfil</a>.
            </div>
        <?php elseif ($aviso === 'sem_xp'): ?>
            <div class="alert alert-secondary mt-4 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Você ainda não pontuou neste período, por isso não aparece no ranking.
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>