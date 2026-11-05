<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 */
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Fonte (Inter): mais leve e neutra para a leitura -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= URL_BASE ?>/assets/css/home.css" rel="stylesheet">
    <!-- tema claro/escuro (no head para não piscar) -->
    <script src="<?= URL_BASE ?>/assets/js/tema.js"></script>
</head>

<body class="dflg-pagina auth-pagina">

    <div class="auth-topo">
        <a href="<?= URL_BASE ?>/" class="btn-icone" title="Voltar para a home" aria-label="Voltar para a home">
            <i class="bi bi-house-door-fill"></i>
        </a>
        <button type="button" class="btn-icone tema-botao" data-tema-botao title="Alternar tema" aria-label="Alternar entre tema claro e escuro">
            <i class="bi bi-moon-stars-fill"></i><i class="bi bi-sun-fill"></i>
        </button>
    </div>

    <div class="card auth-card" style="max-width: 440px;">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?= URL_BASE ?>/assets/img/logo-dflg-transparent.png" alt="DFLG" style="width: 64px;">
                <h3 class="card-title mt-2">DFLG Investments</h3>
                <span class="home-selo">Dream. Fund. Learn. Grow.</span>
            </div>

            <?php if (isset($erros)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <?= is_array($erros) ? implode('<br>', $erros) : $erros ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= URL_BASE ?>/logar" method="post">
                <div class="mb-2">
                    <label for="email" class="form-label small">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="senha" class="form-label small">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-dflg">
                        Entrar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <p class="text-center small h-suave mt-3 mb-0">
                Não tem conta? <a href="<?= URL_BASE ?>/cadastrar">Cadastre-se</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>