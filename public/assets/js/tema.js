// Tema (claro/escuro) do login e do cadastro: não há usuário logado, então a escolha fica no navegador.
// O script é carregado no <head> para o tema valer antes da página aparecer (sem piscar).
(function () {
    var CHAVE = 'dflg-tema';

    function aplicar(tema) {
        document.documentElement.setAttribute('data-bs-theme', tema);
    }

    var salvo = null;
    try {
        salvo = localStorage.getItem(CHAVE);
    } catch (e) { /* navegador sem localStorage: segue com o padrão */ }

    // padrão escuro, como no dashboard
    aplicar(salvo === 'light' ? 'light' : 'dark');

    document.addEventListener('click', function (evento) {
        if (!evento.target.closest('[data-tema-botao]')) {
            return;
        }

        var novo = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        aplicar(novo);

        try {
            localStorage.setItem(CHAVE, novo);
        } catch (e) { /* sem localStorage: vale só nesta página */ }
    });
})();