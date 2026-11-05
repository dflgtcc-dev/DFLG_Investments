<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var string $modo      'criar' (novo administrador) ou 'editar' (dados de uma conta)
 * @var array $valores
 * @var array $erros
 * @var \app\models\Usuario|null $alvo
 */
$criando = $modo === 'criar';
$erros = $erros ?? [];
$valor = fn(string $campo) => htmlspecialchars((string) ($valores[$campo] ?? ''));
$erro = function (string $campo) use ($erros) {
    if (isset($erros[$campo])) {
        echo '<div class="text-danger small">' . htmlspecialchars($erros[$campo]) . '</div>';
    }
};
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $criando ? 'Novo administrador' : 'Editar conta' ?> • DFLG Investments</title>

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
            <h2 class="mb-0"><?= $criando ? 'Novo administrador' : 'Editar conta' ?></h2>
            <a href="<?= URL_BASE ?>/admin/usuarios<?= !$criando && $alvo->getPerfil() === 'administrador' ? '?tipo=administrador' : '' ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (isset($erros['permissao']) || isset($erros['geral'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($erros['permissao'] ?? $erros['geral']) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (!$criando): ?>
                    <p class="text-body-secondary small">
                        Conta de <?= $alvo->getPerfil() === 'administrador' ? 'administrador' : 'usuário comum' ?>
                        • <?= $alvo->getStatus() === 'ativo' ? 'ativa' : 'bloqueada' ?>.
                        XP, nível e sequência de acesso não podem ser alterados.
                    </p>
                <?php else: ?>
                    <p class="text-body-secondary small">
                        A conta é criada como administrador comum. Informe a senha inicial à pessoa; ela pode trocá-la depois em Perfil.
                    </p>
                <?php endif; ?>

                <form action="<?= URL_BASE ?>/admin/usuarios/<?= $criando ? 'novo' : 'editar' ?>" method="post">
                    <?php if (!$criando): ?>
                        <input type="hidden" name="id" value="<?= (int) $alvo->getId() ?>">
                        <input type="hidden" name="tipo" value="<?= $alvo->getPerfil() === 'administrador' ? 'administrador' : 'comum' ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="100"
                                value="<?= $valor('nome') ?>" required>
                            <?php $erro('nome'); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="nickname" class="form-label">Nickname</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" maxlength="25"
                                value="<?= $valor('nickname') ?>" required>
                            <?php $erro('nickname'); ?>
                        </div>

                        <div class="col-md-8">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100"
                                value="<?= $valor('email') ?>" required>
                            <?php $erro('email'); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="dataNascimento" class="form-label">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento"
                                value="<?= $valor('dataNascimento') ?>" required>
                            <?php $erro('dataNascimento'); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="telefone" class="form-label">Telefone <span class="text-muted small">(opcional)</span></label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" maxlength="15"
                                placeholder="(45) 99999-9999" value="<?= $valor('telefone') ?>">
                            <?php $erro('telefone'); ?>
                        </div>

                        <?php if ($criando): ?>
                            <div class="col-md-4">
                                <label for="senha" class="form-label">Senha inicial</label>
                                <input type="password" class="form-control" id="senha" name="senha"
                                    autocomplete="new-password" minlength="8" maxlength="72" required>
                                <?php $erro('senha'); ?>
                            </div>
                        <?php elseif ($alvo->getFoto()): ?>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="removerFoto" name="removerFoto" value="1">
                                    <label class="form-check-label" for="removerFoto">Remover a foto de perfil desta conta</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> <?= $criando ? 'Criar administrador' : 'Salvar alterações' ?>
                        </button>
                    </div>
                </form>

                <?php if (!$criando): ?>
                    <hr class="my-4">

                    <h3 class="h5">Redefinir senha</h3>
                    <p class="text-body-secondary small">
                        Use quando a pessoa esquecer a senha. Defina uma nova senha e informe-a à pessoa; ela pode trocá-la depois em Perfil.
                    </p>

                    <form action="<?= URL_BASE ?>/admin/usuarios/senha" method="post"
                        onsubmit="return confirm('Redefinir a senha desta conta?');">
                        <input type="hidden" name="id" value="<?= (int) $alvo->getId() ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="novaSenha" class="form-label">Nova senha</label>
                                <input type="password" class="form-control" id="novaSenha" name="novaSenha"
                                    autocomplete="new-password" minlength="8" maxlength="72" required>
                                <div class="form-text">De 8 a 72 caracteres.</div>
                                <?php $erro('novaSenha'); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmarSenha" class="form-label">Confirmar nova senha</label>
                                <input type="password" class="form-control" id="confirmarSenha" name="confirmarSenha"
                                    autocomplete="new-password" minlength="8" maxlength="72" required>
                                <?php $erro('confirmarSenha'); ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-outline-danger px-4">
                                <i class="bi bi-key"></i> Redefinir senha
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

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