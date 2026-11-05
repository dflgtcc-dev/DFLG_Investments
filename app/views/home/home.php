<?php
/**
 * Home page pública (Figura 8). Estática: não consulta o banco e não gera XP.
 * Os números do cartão de exemplo são fictícios.
 */

$recursos = [
    ['bi-arrow-left-right', 'Receitas e despesas', 'Registre seus lançamentos e veja para onde vai o seu dinheiro.'],
    ['bi-pie-chart', 'Painel com gráficos', 'Saldo, categorias e a evolução dos últimos 6 meses em um só lugar.'],
    ['bi-bullseye', 'Metas e limites', 'Guarde para os seus sonhos e defina limites mensais por categoria.'],
    ['bi-calculator', '10 calculadoras', 'Juros, aposentadoria, reserva de emergência e muito mais.'],
    ['bi-file-earmark-pdf', 'Extratos em PDF', 'Gere o extrato das suas movimentações quando precisar.'],
    ['bi-trophy', 'XP, níveis e ranking', 'Ganhe XP pelo hábito de usar o sistema e suba no ranking.'],
];

$duvidas = [
    ['Preciso conectar minha conta bancária?', 'Não. Você registra receitas, despesas e metas manualmente. O DFLG não se conecta a bancos e também não faz pagamentos nem transferências: ele serve para organizar e acompanhar o seu dinheiro.'],
    ['Como eu ganho XP?', 'Pelo hábito de usar o sistema: 5 XP no primeiro acesso de cada dia, 10 XP por dia ao usar as calculadoras, 20 XP ao completar o perfil, 15 XP ao abrir os extratos pela primeira vez e 10 XP ao personalizar as preferências. Manter a sequência de acessos rende bônus extra aos 7, 30, 100 e 1.000 dias.'],
    ['Registrar mais gastos me dá mais XP?', 'Não. O XP premia só ações que o próprio sistema consegue verificar, como acessar todos os dias e explorar as ferramentas. Os valores que você registra nunca rendem pontos, então não há ganho em lançar dados falsos.'],
    ['O que acontece se eu ficar um dia sem acessar?', 'A sua sequência de acessos recomeça do primeiro dia. O XP e o nível que você já conquistou continuam com você.'],
    ['Posso excluir minha conta e os meus dados?', 'Sim, pelo seu perfil. A exclusão é definitiva: apaga todos os seus dados, como lançamentos, metas e parcelamentos, e pede a sua confirmação antes de concluir.'],
];
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DFLG Investments • Dream. Fund. Learn. Grow.</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Fonte da home (Inter): mais leve e neutra para a leitura -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= URL_BASE ?>/assets/css/home.css" rel="stylesheet">
</head>

<body class="dflg-pagina home-pagina">

    <nav class="container d-flex align-items-center justify-content-between py-4">
        <a href="<?= URL_BASE ?>/" class="home-marca">
            <img src="<?= URL_BASE ?>/assets/img/logo-dflg-transparent.png" alt="DFLG"> Investments
        </a>
        <div class="d-flex align-items-center gap-2">
            <!-- no celular só cabe um botão: fica o Entrar (o cadastro continua no "Começar agora" da página) -->
            <a href="<?= URL_BASE ?>/login" class="btn btn-contorno">Entrar</a>
            <a href="<?= URL_BASE ?>/cadastrar" class="btn btn-claro d-none d-sm-inline-block">Cadastrar</a>
        </div>
    </nav>

    <header class="home-hero">
    <div class="container">
        <div class="home-hero-conteudo">
            <span class="home-selo">
                 Dream. Fund. Learn. Grow.
            </span>

            <h1>
                Transforme sonhos em
                <span class="h-destaque">realidade financeira</span>
            </h1>

            <p class="lead h-suave">
                Organize receitas, despesas e metas em um só lugar e ganhe XP a cada hábito saudável que você constrói.
            </p>

            <div>
                <a href="<?= URL_BASE ?>/cadastrar" class="btn btn-dflg btn-lg px-4">
                    Começar agora
                </a>
            </div>
        </div>
    </div>
    </header>

    <main class="container pb-0">
        <section class="home-secao">
            <div class="home-secao-cab">
                <h2>Tudo o que você precisa</h2>
                <p class="h-suave">Simples de usar, do primeiro lançamento à meta alcançada.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($recursos as [$icone, $titulo, $texto]): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="home-recurso">
                            <div class="home-recurso-icone"><i class="bi <?= $icone ?>"></i></div>
                            <h5><?= $titulo ?></h5>
                            <p class="h-suave mb-0"><?= $texto ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="home-secao home-faq">
            <div class="home-secao-cab">
                <h2>Dúvidas? A gente responde</h2>
                <p class="h-suave">As perguntas mais frequentes sobre o DFLG.</p>
            </div>

            <div class="accordion" id="faq">
                <?php foreach ($duvidas as $i => [$pergunta, $resposta]): ?>
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= $i ?>" aria-expanded="false" aria-controls="faq-<?= $i ?>">
                                <?= $pergunta ?>
                            </button>
                        </h3>
                        <div id="faq-<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faq">
                            <div class="accordion-body"><?= $resposta ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="home-cta">
            <h2>Sua jornada financeira começa aqui</h2>
            <p>Organize seu dinheiro, conquiste seus objetivos e construa um futuro financeiro melhor.</p>
            <a href="<?= URL_BASE ?>/cadastrar" class="btn btn-dflg btn-lg px-4">Começar agora</a>
            <div class="home-cta-selo">
                <span class="home-selo">Aprenda • Planeje • Conquiste • Evolua</span>
            </div>
        </div>
    </main>


    <footer class="home-rodape">
    <div class="container">
        <div class="home-rodape-info">
            <span>© <?= date('Y') ?> DFLG Investments</span>

            <div class="home-rodape-links">
                <span>
                    Atendimento:
                    <a href="mailto:dflgtcc@gmail.com">dflgtcc@gmail.com</a>
                </span>
            </div>
        </div>
    </div>  
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>