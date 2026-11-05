<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $ano
 * @var mixed $anos
 * @var mixed $extrato
 * @var mixed $mes
 * @var mixed $meses
 * @var mixed $numero
 * @var mixed $tipo
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios • DFLG Investments</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS próprio -->
    <link href="<?= URL_BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <?php
    $mensal = $tipo === 'mensal';
    $periodo = $mensal ? ['tipo' => 'mensal', 'ano' => $ano, 'mes' => $mes] : ['tipo' => 'anual', 'ano' => $ano];
    ?>

    <div class="container pb-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 cabecalho-pagina">
            <h2 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Relatórios</h2>
            <a href="<?= URL_BASE ?>/relatorios/pdf?<?= http_build_query($periodo) ?>" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
            </a>
        </div>

        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $mensal ? 'active' : '' ?>"
                    href="<?= URL_BASE ?>/relatorios?<?= http_build_query(['tipo' => 'mensal', 'ano' => $ano, 'mes' => $mes]) ?>">Extrato mensal</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= !$mensal ? 'active' : '' ?>"
                    href="<?= URL_BASE ?>/relatorios?<?= http_build_query(['tipo' => 'anual', 'ano' => $ano]) ?>">Extrato anual</a>
            </li>
        </ul>

        <!-- Período -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="<?= URL_BASE ?>/relatorios" method="get" class="row g-2 align-items-end">
                    <input type="hidden" name="tipo" value="<?= $tipo ?>">

                    <?php if ($mensal): ?>
                        <div class="col-md-4">
                            <label for="mes" class="form-label small fw-bold">Mês</label>
                            <select class="form-select" id="mes" name="mes">
                                <?php foreach ($meses as $numero => $nome): ?>
                                    <option value="<?= $numero ?>" <?= $mes == $numero ? 'selected' : '' ?>><?= $nome ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-4">
                        <label for="ano" class="form-label small fw-bold">Ano</label>
                        <select class="form-select" id="ano" name="ano">
                            <?php foreach ($anos as $opcao): ?>
                                <option value="<?= $opcao ?>" <?= $ano == $opcao ? 'selected' : '' ?>><?= $opcao ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search"></i> Gerar extrato
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <h5 class="mb-3">Extrato de <?= htmlspecialchars($extrato['titulo']) ?></h5>

        <!-- RESUMO -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1"><?= $mensal ? 'Saldo no início do mês' : 'Saldo no início do ano' ?></p>
                        <h5 class="mb-0 <?= $extrato['saldoInicial'] >= 0 ? '' : 'text-danger' ?>">R$ <?= number_format($extrato['saldoInicial'], 2, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Receitas</p>
                        <h5 class="mb-0 text-success">R$ <?= number_format($extrato['totalReceitas'], 2, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Despesas</p>
                        <h5 class="mb-0 text-danger">R$ <?= number_format($extrato['totalDespesas'], 2, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Saldo final</p>
                        <h5 class="mb-0 <?= $extrato['saldoFinal'] >= 0 ? 'text-success' : 'text-danger' ?>">R$ <?= number_format($extrato['saldoFinal'], 2, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($mensal): ?>

            <?php if (empty($extrato['lancamentos'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Nenhum lançamento neste mês.
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle tabela-cards">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Data</th>
                                        <th class="px-4 py-3">Categoria</th>
                                        <th class="px-4 py-3">Descrição</th>
                                        <th class="px-4 py-3 text-end">Valor</th>
                                        <th class="px-4 py-3 text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($extrato['lancamentos'] as $lancamento): ?>
                                        <?php $transacao = $lancamento['transacao']; ?>
                                        <tr>
                                            <td class="px-4 py-3 text-nowrap" data-label="Data"><?= date('d/m/Y', strtotime($transacao->getDataRegistro())) ?></td>
                                            <td class="px-4 py-3" data-label="Categoria"><?= htmlspecialchars($transacao->getNomeCategoria() ?? 'Sem categoria') ?></td>
                                            <td class="px-4 py-3" data-label="Descrição"><?= htmlspecialchars($transacao->getDescricao() ?: '-') ?></td>
                                            <td class="px-4 py-3 text-end fw-bold text-nowrap <?= $transacao->eReceita() ? 'text-success' : 'text-danger' ?>" data-label="Valor">
                                                <?= $transacao->eReceita() ? '+' : '-' ?> R$ <?= number_format($transacao->getValor(), 2, ',', '.') ?>
                                            </td>
                                            <td class="px-4 py-3 text-end text-nowrap <?= $lancamento['saldo'] >= 0 ? '' : 'text-danger' ?>" data-label="Saldo">
                                                R$ <?= number_format($lancamento['saldo'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <?php if (empty($extrato['meses'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Nenhum mês com registros neste ano.
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle tabela-cards">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Mês</th>
                                        <th class="px-4 py-3 text-end">Receitas</th>
                                        <th class="px-4 py-3 text-end">Despesas</th>
                                        <th class="px-4 py-3 text-end">Resultado do mês</th>
                                        <th class="px-4 py-3 text-end">Saldo acumulado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($extrato['meses'] as $linha): ?>
                                        <tr>
                                            <td class="px-4 py-3 td-titulo fw-semibold"><?= $linha['mes'] ?></td>
                                            <td class="px-4 py-3 text-end text-success text-nowrap" data-label="Receitas">R$ <?= number_format($linha['receitas'], 2, ',', '.') ?></td>
                                            <td class="px-4 py-3 text-end text-danger text-nowrap" data-label="Despesas">R$ <?= number_format($linha['despesas'], 2, ',', '.') ?></td>
                                            <td class="px-4 py-3 text-end text-nowrap <?= $linha['resultado'] >= 0 ? '' : 'text-danger' ?>" data-label="Resultado do mês">R$ <?= number_format($linha['resultado'], 2, ',', '.') ?></td>
                                            <td class="px-4 py-3 text-end fw-bold text-nowrap <?= $linha['saldoAcumulado'] >= 0 ? '' : 'text-danger' ?>" data-label="Saldo acumulado">R$ <?= number_format($linha['saldoAcumulado'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td class="px-4 py-3 td-titulo">Total do ano</td>
                                        <td class="px-4 py-3 text-end text-success text-nowrap" data-label="Receitas">R$ <?= number_format($extrato['totalReceitas'], 2, ',', '.') ?></td>
                                        <td class="px-4 py-3 text-end text-danger text-nowrap" data-label="Despesas">R$ <?= number_format($extrato['totalDespesas'], 2, ',', '.') ?></td>
                                        <td class="px-4 py-3 text-end text-nowrap" data-label="Resultado do ano">R$ <?= number_format($extrato['totalReceitas'] - $extrato['totalDespesas'], 2, ',', '.') ?></td>
                                        <td class="px-4 py-3 text-end text-nowrap" data-label="Saldo final">R$ <?= number_format($extrato['saldoFinal'], 2, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <p class="text-muted small mt-3 mb-0">Considera apenas registros com data até hoje.</p>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
