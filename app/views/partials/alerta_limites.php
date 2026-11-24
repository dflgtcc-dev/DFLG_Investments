<?php
/**
 * RF 12 / RN 12: aviso das categorias que passaram do limite mensal.
 * Variável disponibilizada pelo controller (via Controller::view/extract).
 * @var mixed $categoriasExcedidas
 *
 * O aviso tem um "X" para fechar. O fechamento fica guardado no navegador (localStorage) e vale
 * para este usuário, neste mês e para este mesmo conjunto de categorias excedidas: se outra
 * categoria passar do limite, o aviso volta a aparecer.
 */
?>
<?php if (!empty($categoriasExcedidas)): ?>
    <?php
    $idsExcedidos = array_map(fn ($categoria) => (int) $categoria['id'], $categoriasExcedidas);
    sort($idsExcedidos);
    $assinaturaAlerta = $_SESSION['usuario_logado']->getId() . ':' . date('Y-m') . ':' . implode(',', $idsExcedidos);
    ?>
    <div class="alert alert-danger alert-dismissible" role="alert" id="alertaLimites"
        data-assinatura="<?= htmlspecialchars($assinaturaAlerta) ?>">
        <button type="button" class="btn-close" aria-label="Fechar aviso" title="Fechar aviso"></button>
        <div class="fw-bold mb-1">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Limite mensal excedido
        </div>
        <ul class="mb-2 ps-4">
            <?php foreach ($categoriasExcedidas as $categoria): ?>
                <li>
                    <strong><?= htmlspecialchars($categoria['nome']) ?></strong>:
                    gastou R$ <?= number_format($categoria['gasto'], 2, ',', '.') ?>
                    de R$ <?= number_format($categoria['limite'], 2, ',', '.') ?>
                    (R$ <?= number_format($categoria['excesso'], 2, ',', '.') ?> acima do limite)
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?= URL_BASE ?>/categorias" class="alert-link">Ver os limites das categorias</a>
    </div>
    <script>
        // roda logo depois do aviso, antes de a página aparecer, para ele não "piscar" quando já foi fechado
        (function () {
            var CHAVE = 'dflg-alerta-limites-fechado';
            var aviso = document.getElementById('alertaLimites');
            var assinatura = aviso.getAttribute('data-assinatura');

            try {
                if (localStorage.getItem(CHAVE) === assinatura) {
                    aviso.remove();
                    return;
                }
            } catch (e) { /* navegador sem localStorage: o aviso aparece normalmente */ }

            aviso.querySelector('.btn-close').addEventListener('click', function () {
                aviso.remove();
                try {
                    localStorage.setItem(CHAVE, assinatura);
                } catch (e) { /* sem localStorage: fecha só nesta página */ }
            });
        })();
    </script>
<?php endif; ?>
