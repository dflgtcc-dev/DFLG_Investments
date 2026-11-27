<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $erros
 * @var mixed $usuario
 */
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Fonte (Inter): mais leve e neutra para a leitura -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/home.css') ?>" rel="stylesheet">
    <!-- tema claro/escuro (no head para não piscar) -->
    <script src="<?= asset('js/tema.js') ?>"></script>
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

    <div class="card auth-card" style="max-width: 400px;">
        <div class="card-body">
            <div class="text-center mb-3">
                <img src="<?= URL_BASE ?>/assets/img/logo-dflg-transparent.png" alt="DFLG" style="width: 48px;">
                <h3 class="card-title mt-2">DFLG Investments</h3>
                <span class="home-selo">Dream. Fund. Learn. Grow.</span>
            </div>

            <?php if (isset($erros) && !empty($erros)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <?= implode('<br>', $erros) ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= URL_BASE ?>/cadastrar/salvar" method="post">
                <div class="input-group mb-2">
                    <span class="input-group-text text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required placeholder="Nome completo" aria-label="Nome completo">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text text-muted"><i class="bi bi-at"></i></span>
                    <input type="text" class="form-control" id="nickname" name="nickname" maxlength="25" value="<?= htmlspecialchars($usuario['nickname'] ?? '') ?>" required placeholder="Nickname" aria-label="Nickname">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required placeholder="E-mail" aria-label="E-mail">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text text-muted"><i class="bi bi-telephone"></i></span>
                    <input type="tel" class="form-control" id="telefone" name="telefone" maxlength="15" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" placeholder="Telefone (opcional)" aria-label="Telefone (opcional)">
                </div>

                <div class="input-group mb-2">
                    <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="senha" name="senha" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="Senha" aria-label="Senha">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text text-muted"><i class="bi bi-calendar3"></i></span>
                    <input type="<?= !empty($usuario['dataNascimento']) ? 'date' : 'text' ?>" class="form-control" id="dataNascimento" name="dataNascimento" value="<?= htmlspecialchars($usuario['dataNascimento'] ?? '') ?>" required placeholder="Data de nascimento" aria-label="Data de nascimento" title="Data de nascimento" onfocus="this.type='date'" onblur="if(!this.value)this.type='text'">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-dflg">
                        Cadastrar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <p class="text-center small h-suave mt-3 mb-0">
                Já tem conta? <a href="<?= URL_BASE ?>/login">Entrar</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/senha.js') ?>"></script>

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
