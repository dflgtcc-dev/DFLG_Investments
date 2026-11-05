<?php
/**
 * RF 12 / RN 12: aviso das categorias que passaram do limite mensal.
 * Variável disponibilizada pelo controller (via Controller::view/extract).
 * @var mixed $categoriasExcedidas
 */
?>
<?php if (!empty($categoriasExcedidas)): ?>
    <div class="alert alert-danger" role="alert">
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
<?php endif; ?>
