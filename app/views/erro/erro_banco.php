<?php
// RNF 04: tela exibida quando o banco de dados está indisponível ou retorna erro.
// Não consulta o banco, para funcionar justamente quando ele falha.
$temaBootstrap = (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']->getTema() === 'escuro') ? 'dark' : 'light';
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="<?= $temaBootstrap ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Não foi possível acessar os dados • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm text-center">
                    <div class="card-body p-4 p-md-5">

                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>

                        <h1 class="h4 mt-3">Não foi possível acessar o banco de dados</h1>

                        <p class="text-body-secondary mt-3">
                            O sistema está com dificuldade para acessar as informações neste momento.
                            Aguarde alguns instantes e tente novamente.
                        </p>
                        <p class="text-body-secondary small">
                            Se você estava salvando algo, confira se o registro foi gravado antes de repetir a operação.
                            Se o problema continuar, entre em contato com o administrador do sistema. 
                            Email: <a href="mailto:dflgtcc@gmail.com.br">dflgtcc@gmail.com.br</a>
                        </p>

                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-4">
                            <a href="javascript:history.back()" class="btn btn-primary">
                                <i class="bi bi-arrow-counterclockwise"></i> Voltar e tentar novamente
                            </a>
                            <a href="<?= URL_BASE ?>/" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Ir para o início
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>