<?php
// RNF 07: aplica o tema escolhido pelo usuário (claro ou escuro) em todas as telas internas
$temaBootstrap = $_SESSION['usuario_logado']->getTema() === 'escuro' ? 'dark' : 'light';
$ehAdmin = $_SESSION['usuario_logado']->eAdmin();

// links principais: no computador ficam visíveis; no celular e tablet vão para dentro do menu sanduíche
$linksMenu = $ehAdmin
    ? ['/admin' => ['Painel', 'bi-speedometer2'], '/admin/usuarios' => ['Usuários', 'bi-people']]
    : [
        '/dashboard' => ['Dashboard', 'bi-speedometer2'],
        '/metas' => ['Metas', 'bi-bullseye'],
        '/transacoes' => ['Finanças', 'bi-wallet2'],
        '/parcelamentos' => ['Parcelamentos', 'bi-credit-card-2-front'],
        '/categorias' => ['Categorias', 'bi-tags'],
        '/calculadoras' => ['Calculadoras', 'bi-calculator'],
    ];

// marca o link da página atual (ex.: /metas/editar marca "Metas"; /admin/usuarios marca só "Usuários")
$caminhoAtual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$pastaBase = rtrim(parse_url(URL_BASE, PHP_URL_PATH) ?? '', '/');
if ($pastaBase !== '' && str_starts_with($caminhoAtual, $pastaBase)) {
    $caminhoAtual = substr($caminhoAtual, strlen($pastaBase)) ?: '/';
}
$linkAtivo = function (string $rota) use ($caminhoAtual, $linksMenu): bool {
    if ($caminhoAtual !== $rota && !str_starts_with($caminhoAtual, $rota . '/')) {
        return false;
    }
    // vence a rota mais específica (/admin/usuarios ganha de /admin)
    foreach (array_keys($linksMenu) as $outra) {
        if (strlen($outra) > strlen($rota) && ($caminhoAtual === $outra || str_starts_with($caminhoAtual, $outra . '/'))) {
            return false;
        }
    }

    return true;
};
?>
<script>
    document.documentElement.setAttribute('data-bs-theme', '<?= $temaBootstrap ?>');
</script>
<nav class="navbar navbar-dflg mb-4 sticky-top" data-bs-theme="dark">
    <div class="container">
        <a href="<?= URL_BASE ?><?= $ehAdmin ? '/admin' : '/dashboard' ?>" class="navbar-brand mb-0 h1 text-white text-decoration-none">
                <img src="<?= URL_BASE ?>/assets/img/logo-dflg-transparent.png" alt="DFLG" height="36" class="me-2">Investments
        </a>

        <!-- Links (sempre visíveis, à direita) + menu sanduíche -->
        <div class="d-flex align-items-center gap-2">
            <div class="nav-links d-none d-lg-flex">
                <?php foreach ($linksMenu as $rota => [$rotulo, $icone]): ?>
                    <a href="<?= URL_BASE . $rota ?>" class="nav-link-dflg<?= $linkAtivo($rota) ? ' ativo' : '' ?>"
                        <?= $linkAtivo($rota) ? 'aria-current="page"' : '' ?>><?= $rotulo ?></a>
                <?php endforeach; ?>
            </div>

            <!-- Menu sanduíche: Perfil, Ranking, Relatórios e Sair (no celular, também os links principais) -->
            <div class="dropdown">
            <button class="btn-sanduiche" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-dflg">
                <?php foreach ($linksMenu as $rota => [$rotulo, $icone]): ?>
                    <li class="d-lg-none">
                        <a class="dropdown-item<?= $linkAtivo($rota) ? ' ativo' : '' ?>" href="<?= URL_BASE . $rota ?>">
                            <i class="bi <?= $icone ?> me-2"></i><?= $rotulo ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="d-lg-none"><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= URL_BASE ?>/perfil"><i class="bi bi-person me-2"></i>Perfil</a></li>
                <?php if (!$ehAdmin): ?>
                    <li><a class="dropdown-item" href="<?= URL_BASE ?>/ranking"><i class="bi bi-trophy me-2"></i>Ranking</a></li>
                    <li><a class="dropdown-item" href="<?= URL_BASE ?>/relatorios"><i class="bi bi-file-earmark-text me-2"></i>Relatórios</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= URL_BASE ?>/logout"
                       onclick="return confirm('Deseja realmente sair da conta?');">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </a>
                </li>
            </ul>
            </div>
        </div>
    </div>
</nav>

<?php require __DIR__ . '/notificacao_xp.php'; ?>
