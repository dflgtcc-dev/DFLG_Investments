<?php
/**
 * Aviso de XP ganho e de subida de nível. O GamificacaoService deixa os avisos na sessão;
 * aqui eles são mostrados uma única vez (toast do Bootstrap) e apagados em seguida.
 */
$avisosXp = $_SESSION['notificacoes_xp'] ?? [];
unset($_SESSION['notificacoes_xp']);
?>
<?php if (!empty($avisosXp)): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php foreach ($avisosXp as $aviso): ?>
            <div class="toast toast-xp align-items-center border-0 <?= $aviso['nivel'] ? 'text-bg-success' : 'text-bg-primary' ?>"
                 role="status" aria-live="polite" aria-atomic="true" data-bs-delay="<?= $aviso['nivel'] ? 7000 : 5000 ?>">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php if ($aviso['nivel']): ?>
                            <div class="fw-bold"><i class="bi bi-trophy-fill me-1"></i>Você subiu para o nível <?= (int) $aviso['nivel'] ?>!</div>
                        <?php endif; ?>
                        <i class="bi bi-stars me-1"></i><strong>+<?= (int) $aviso['xp'] ?> XP</strong>
                        <span class="opacity-75">· <?= htmlspecialchars($aviso['rotulo']) ?></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toast-xp').forEach(function (el) {
                if (window.bootstrap) { new bootstrap.Toast(el).show(); }
            });
        });
    </script>
<?php endif; ?>
