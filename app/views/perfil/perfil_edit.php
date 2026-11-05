<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 * @var mixed $usuario
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil • DFLG Investments</title>

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
            <h2 class="mb-0">Editar Perfil</h2>
            <a href="<?= URL_BASE ?>/perfil" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <form action="<?= URL_BASE ?>/perfil/atualizar" method="post" enctype="multipart/form-data">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="100"
                                value="<?= htmlspecialchars($usuario->getNome()) ?>">
                            <?php if (isset($erros['nome'])): ?>
                                <div class="text-danger small"><?= $erros['nome'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="nickname" class="form-label">Nickname</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" maxlength="25"
                                value="<?= htmlspecialchars($usuario->getNickname()) ?>">
                            <?php if (isset($erros['nickname'])): ?>
                                <div class="text-danger small"><?= $erros['nickname'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100"
                                value="<?= htmlspecialchars($usuario->getEmail()) ?>" <?= $usuario->eAdmin() ? '' : 'disabled' ?>>
                            <?php if (isset($erros['email'])): ?>
                                <div class="text-danger small"><?= $erros['email'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="dataNascimento" class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento"
                                value="<?= htmlspecialchars($usuario->getDataNascimento()) ?>" <?= $usuario->eAdmin() ? '' : 'disabled' ?>>
                            <?php if (isset($erros['dataNascimento'])): ?>
                                <div class="text-danger small"><?= $erros['dataNascimento'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="telefone" class="form-label">Telefone <span class="text-muted small">(opcional)</span></label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" maxlength="15"
                                placeholder="(45) 99999-9999" value="<?= htmlspecialchars($usuario->getTelefoneFormatado()) ?>">
                            <?php if (isset($erros['telefone'])): ?>
                                <div class="text-danger small"><?= $erros['telefone'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="foto" class="form-label">Foto de perfil</label>

                            <?php if ($usuario->getFoto()): ?>
                                <div class="mb-2">
                                    <img src="<?= URL_BASE ?>/assets/uploads/perfil/<?= htmlspecialchars($usuario->getFoto()) ?>"
                                        alt="Foto atual" class="rounded-circle object-fit-cover" width="80" height="80">
                                </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="foto" name="foto"
                                accept="image/jpeg,image/png,image/webp">
                            <?php if (isset($erros['foto'])): ?>
                                <div class="text-danger small"><?= $erros['foto'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">JPG, PNG ou WEBP, até 2 MB. Deixe em branco para manter a foto atual.</div>
                            <?php if ($usuario->getFoto()): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="removerFoto" name="removerFoto" value="1">
                                    <label class="form-check-label" for="removerFoto">Remover foto atual</label>
                                </div>
                            <?php endif; ?>
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
        // máscara do telefone: (45) 99999-9999 ou (45) 3523-1234; o servidor guarda só os dígitos
        document.getElementById('telefone').addEventListener('input', function () {
            var d = this.value.replace(/\D/g, '').slice(0, 11);
            if (d.length > 10) {
                d = d.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            } else if (d.length > 6) {
                d = d.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (d.length > 2) {
                d = d.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
            } else if (d.length > 0) {
                d = d.replace(/^(\d{0,2}).*/, '($1');
            }
            this.value = d;
        });
    </script>
</body>

</html>