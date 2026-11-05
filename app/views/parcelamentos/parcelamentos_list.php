<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var array|null $aviso
 * @var mixed $categoriasExcedidas
 * @var mixed $lista
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parcelamentos • DFLG Investments</title>

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

        <?php require __DIR__ . '/../partials/alerta_limites.php'; ?>

        <?php if (!empty($aviso)): ?>
            <div class="alert alert-<?= htmlspecialchars($aviso['tipo']) ?>" role="alert">
                <?= htmlspecialchars($aviso['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 cabecalho-pagina">
            <h2 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Meus Parcelamentos</h2>
            <a href="<?= URL_BASE ?>/parcelamentos/cadastrar" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Parcelamento
            </a>
        </div>

        <?php if (empty($lista)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Você ainda não cadastrou nenhum parcelamento.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle tabela-cards">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Descrição</th>
                                    <th class="px-3 py-3 text-end">Valores</th>
                                    <th class="px-3 py-3">Progresso</th>
                                    <th class="px-3 py-3 text-end text-nowrap">Falta pagar</th>
                                    <th class="px-4 py-3 text-end" aria-label="Ações"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $parcelamento):
                                    $total = $parcelamento->getQuantidadeParcelas();
                                    $pagas = $parcelamento->getParcelasPagas();
                                    $atrasadas = $parcelamento->getParcelasAtrasadas();
                                ?>
                                    <tr>
                                        <!-- descrição, categoria e próximo vencimento -->
                                        <td class="px-4 py-3 td-titulo" style="min-width: 220px;">
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($parcelamento->getDescricao()) ?>
                                                <?php if ($parcelamento->getTag()): ?>
                                                    <span class="badge bg-secondary fw-normal ms-1"><?= htmlspecialchars($parcelamento->getTag()) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($parcelamento->getNomeCategoria() ?? 'Sem categoria') ?>
                                            </small>
                                            <small class="d-block mt-1">
                                                <?php if ($parcelamento->eQuitado()): ?>
                                                    <span class="badge bg-success">Quitado</span>
                                                <?php elseif ($atrasadas > 0): ?>
                                                    <span class="badge bg-danger">
                                                        <?= $atrasadas === 1 ? '1 parcela vencida sem lançar' : $atrasadas . ' parcelas vencidas sem lançar' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        Parcela <?= $parcelamento->getNumeroProximaParcela() ?> vence em
                                                        <?= date('d/m/Y', strtotime($parcelamento->getDataProximaParcela())) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </td>

                                        <!-- parcela e valor total -->
                                        <td class="px-3 py-3 text-end text-nowrap " data-label="Valores">
                                            <div>
                                                <div><?= $total ?>x R$ <?= number_format($parcelamento->getValorParcela(), 2, ',', '.') ?></div>
                                                <small class="text-muted" title="Já pago + o que falta no valor atual da parcela">
                                                    Total R$ <?= number_format($parcelamento->getValorTotal(), 2, ',', '.') ?>
                                                </small>
                                            </div>
                                        </td>

                                        <!-- progresso, com lançar e desfazer logo abaixo -->
                                        <td class="px-3 py-3 td-bloco" data-label="Progresso" style="min-width: 210px;">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-muted"><?= $pagas ?> de <?= $total ?> pagas</span>
                                                <span class="text-muted"><?= number_format($parcelamento->getProgresso(), 0) ?>%</span>
                                            </div>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar <?= $parcelamento->eQuitado() ? 'bg-success' : 'bg-primary' ?>" role="progressbar"
                                                    style="width: <?= $parcelamento->getProgresso() ?>%"></div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if (!$parcelamento->eQuitado()):
                                                    $numero = $parcelamento->getNumeroProximaParcela();
                                                    $vencimento = $parcelamento->getDataProximaParcela();
                                                    $mensagem = $vencimento > date('Y-m-d')
                                                        ? "A parcela {$numero}/{$total} vence em " . date('d/m/Y', strtotime($vencimento)) . ". Lançar agora como paga hoje?"
                                                        : "Lançar a parcela {$numero}/{$total} (vencimento em " . date('d/m/Y', strtotime($vencimento)) . ")?";
                                                ?>
                                                    <form method="POST" action="<?= URL_BASE ?>/parcelamentos/lancar" class="m-0"
                                                        onsubmit="this.querySelector('button').disabled = true; return true;">
                                                        <input type="hidden" name="id" value="<?= $parcelamento->getId() ?>">
                                                        <button type="submit" class="btn btn-sm <?= $atrasadas > 0 ? 'btn-danger' : 'btn-success' ?> text-nowrap"
                                                            onclick="return confirm(<?= htmlspecialchars(json_encode($mensagem), ENT_QUOTES) ?>)">
                                                            <i class="bi bi-check2-circle"></i> Lançar <?= $numero ?>/<?= $total ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($pagas > 0): ?>
                                                    <form method="POST" action="<?= URL_BASE ?>/parcelamentos/desfazer" class="m-0"
                                                        onsubmit="return confirm('Desfazer a parcela <?= $pagas ?>/<?= $total ?>? Ela sai das suas transações.')">
                                                        <input type="hidden" name="id" value="<?= $parcelamento->getId() ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap" title="Desfazer a última parcela lançada">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Desfazer
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3 text-end fw-bold text-nowrap" data-label="Falta pagar">
                                            R$ <?= number_format($parcelamento->getValorRestante(), 2, ',', '.') ?>
                                        </td>

                                        <!-- editar e excluir só com ícone -->
                                        <td class="px-4 py-3 text-end text-nowrap td-acoes">
                                            <a href="<?= URL_BASE ?>/parcelamentos/editar?id=<?= $parcelamento->getId() ?>"
                                                class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="<?= URL_BASE ?>/parcelamentos/excluir" class="d-inline"
                                                onsubmit="return confirm('Excluir este parcelamento? As parcelas já lançadas continuam nas suas transações como despesas comuns.')">
                                                <input type="hidden" name="id" value="<?= $parcelamento->getId() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir" aria-label="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
