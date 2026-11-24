// "Olho mágico": coloca um botão de mostrar/ocultar senha em todo campo type="password".
// Basta incluir este arquivo na página; não é preciso alterar o HTML dos campos.
// Usa os Bootstrap Icons (bi-eye / bi-eye-slash), que já são carregados nas telas.
(function () {
    function criarBotao(campo) {
        var botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'btn btn-outline-secondary btn-olho-senha';
        botao.setAttribute('aria-label', 'Mostrar senha');
        botao.setAttribute('aria-pressed', 'false');
        botao.title = 'Mostrar senha';
        botao.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';

        botao.addEventListener('click', function () {
            var mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';

            botao.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
            botao.title = mostrar ? 'Ocultar senha' : 'Mostrar senha';
            botao.firstChild.className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';

            campo.focus();
        });

        return botao;
    }

    function adicionar(campo) {
        if (campo.dataset.olhoSenha === '1') {
            return;
        }
        campo.dataset.olhoSenha = '1';

        var pai = campo.parentElement;

        // campo que já está em um .input-group (login e cadastro): só acrescenta o botão no fim
        if (pai && pai.classList.contains('input-group')) {
            pai.appendChild(criarBotao(campo));
            return;
        }

        // campo solto (troca de senha, formulário do administrador): envolve em um .input-group
        var grupo = document.createElement('div');
        grupo.className = 'input-group';
        pai.insertBefore(grupo, campo);
        grupo.appendChild(campo);
        grupo.appendChild(criarBotao(campo));
    }

    function iniciar() {
        document.querySelectorAll('input[type="password"]').forEach(adicionar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
