<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erro
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Perfil • DFLG Investments</title>

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
            <h2 class="mb-0">Exclusão de Perfil</h2>
            <a href="<?= URL_BASE ?>/perfil" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3>Deseja realmente excluir seu perfil?</h3>
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= URL_BASE ?>/perfil/excluir">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="excluirCheck" name="confirmarExclusao" required>
                        <label class="form-check-label" for="excluirCheck">
                            Estou ciente de que todos os meus dados serão excluídos e não poderão ser recuperados.
                        </label>
                    </div>

                    <button type="submit" id="btnExcluir" class="btn btn-secondary" disabled
                            onclick="return confirm('Deseja realmente excluir seu perfil?')">
                        <i class="bi bi-trash"></i> Excluir perfil
                    </button>
                </form>

            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // RN 14: o botão fica inativo (cinza) e só é liberado (vermelho) depois de marcar "Estou ciente"
        const excluirCheck = document.getElementById('excluirCheck');
        const btnExcluir = document.getElementById('btnExcluir');

        function atualizarBotaoExcluir() {
            btnExcluir.disabled = !excluirCheck.checked;
            btnExcluir.classList.toggle('btn-danger', excluirCheck.checked);
            btnExcluir.classList.toggle('btn-secondary', !excluirCheck.checked);
        }

        excluirCheck.addEventListener('change', atualizarBotaoExcluir);
        atualizarBotaoExcluir(); // também cobre o navegador que lembra o checkbox marcado ao recarregar
    </script>
</body>

</html>