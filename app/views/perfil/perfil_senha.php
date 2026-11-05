<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha • DFLG Investments</title>

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
            <h2 class="mb-0">Alterar Senha</h2>
            <a href="<?= URL_BASE ?>/perfil" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="row">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">

                        <form action="<?= URL_BASE ?>/perfil/senha" method="post">

                            <div class="mb-3">
                                <label for="senhaAtual" class="form-label">Senha atual</label>
                                <input type="password" class="form-control" id="senhaAtual" name="senhaAtual"
                                    autocomplete="current-password" required>
                                <?php if (isset($erros['senhaAtual'])): ?>
                                    <div class="text-danger small"><?= htmlspecialchars($erros['senhaAtual']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="novaSenha" class="form-label">Nova senha</label>
                                <input type="password" class="form-control" id="novaSenha" name="novaSenha" minlength="8" maxlength="72"
                                    autocomplete="new-password" required>
                                <?php if (isset($erros['novaSenha'])): ?>
                                    <div class="text-danger small"><?= htmlspecialchars($erros['novaSenha']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="confirmarSenha" class="form-label">Confirmar nova senha</label>
                                <input type="password" class="form-control" id="confirmarSenha" name="confirmarSenha" maxlength="72"
                                    autocomplete="new-password" required>
                                <?php if (isset($erros['confirmarSenha'])): ?>
                                    <div class="text-danger small"><?= htmlspecialchars($erros['confirmarSenha']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-circle"></i> Alterar senha
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>