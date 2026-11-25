<?php
/**
 * Lembrete de parcelas: avisa das parcelas ainda não lançadas que vencem nos próximos dias
 * (ParcelamentoService::DIAS_LEMBRETE) ou que já venceram.
 * Variável disponibilizada pelo controller (via Controller::view/extract).
 * @var mixed $lembretesParcelas
 *
 * O aviso tem um "X" para fechar. O fechamento fica guardado no navegador (localStorage) e vale
 * só por hoje, para este usuário e para este mesmo conjunto de parcelas: no dia seguinte, ou se
 * outra parcela entrar na janela de vencimento, o aviso volta. Ao lançar a parcela, ele some.
 */
?>
<?php if (!empty($lembretesParcelas)): ?>
    <?php
    $temAtraso = false;
    $chavesParcelas = [];
    foreach ($lembretesParcelas as $lembrete) {
        $temAtraso = $temAtraso || $lembrete['dias'] < 0;
        $chavesParcelas[] = $lembrete['parcelamentoId'] . '-' . $lembrete['numero'];
    }
    $assinaturaParcelas = $_SESSION['usuario_logado']->getId() . ':' . date('Y-m-d') . ':' . implode(',', $chavesParcelas);
    ?>
    <div class="alert alert-<?= $temAtraso ? 'danger' : 'warning' ?> alert-dismissible" role="alert" id="alertaParcelas"
        data-assinatura="<?= htmlspecialchars($assinaturaParcelas) ?>">
        <button type="button" class="btn-close" aria-label="Fechar aviso" title="Fechar aviso"></button>
        <div class="fw-bold mb-1">
            <i class="bi bi-bell-fill me-2"></i><?= $temAtraso ? 'Parcelas vencidas ou perto de vencer' : 'Parcelas perto de vencer' ?>
        </div>
        <ul class="mb-2 ps-4">
            <?php foreach ($lembretesParcelas as $lembrete): ?>
                <?php
                $dias = $lembrete['dias'];
                $data = date('d/m', strtotime($lembrete['vencimento']));
                if ($dias < 0) {
                    $quando = 'venceu em ' . $data . ' (há ' . abs($dias) . ($dias === -1 ? ' dia' : ' dias') . ')';
                } elseif ($dias === 0) {
                    $quando = 'vence hoje (' . $data . ')';
                } elseif ($dias === 1) {
                    $quando = 'vence amanhã (' . $data . ')';
                } else {
                    $quando = 'vence em ' . $dias . ' dias (' . $data . ')';
                }
                ?>
                <li>
                    <strong><?= htmlspecialchars($lembrete['descricao']) ?></strong>:
                    R$ <?= number_format($lembrete['valor'], 2, ',', '.') ?>,
                    <?= $quando ?>
                    <?php if ($lembrete['atrasadas'] > 1): ?>
                        <span class="badge text-bg-danger ms-1"><?= (int) $lembrete['atrasadas'] ?> parcelas em atraso</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?= URL_BASE ?>/parcelamentos" class="alert-link">Ir para os parcelamentos</a>
        <span class="small d-block mt-1">Depois de pagar, use <strong>Lançar parcela</strong> para registrar.</span>
    </div>
    <script>
        // roda logo depois do aviso, antes de a página aparecer, para ele não "piscar" quando já foi fechado
        (function () {
            var CHAVE = 'dflg-alerta-parcelas-fechado';
            var aviso = document.getElementById('alertaParcelas');
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
