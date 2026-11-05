<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $categoriasExcedidas
 * @var mixed $gamificacao
 * @var mixed $painel
 * @var mixed $progressoNivel
 * @var mixed $usuario
 */

$brl = fn ($valor): string => 'R$ ' . number_format($valor, 2, ',', '.');

// selo de variação em relação ao período anterior (receita subir é bom; despesa subir é ruim)
$selo = function (?int $variacao, bool $altaEBoa): string {
    if ($variacao === null) {
        return '<span class="dash-var dash-var-neutra">sem comparação</span>';
    }

    if ($variacao === 0) {
        return '<span class="dash-var dash-var-neutra"><i class="bi bi-dash"></i> igual</span>';
    }

    $subiu = $variacao > 0;
    $boa = $subiu === $altaEBoa;

    return '<span class="dash-var ' . ($boa ? 'dash-var-boa' : 'dash-var-ruim') . '">' .
        '<i class="bi ' . ($subiu ? 'bi-arrow-up-right' : 'bi-arrow-down-right') . '"></i> ' . abs($variacao) . '%</span>';
};

$primeiroNome = explode(' ', trim($usuario->getNome()))[0];
$resultadoPositivo = $painel['resultado'] >= 0;
$temEvolucao = array_sum($painel['graficos']['evolucao']['receitas']) + array_sum($painel['graficos']['evolucao']['despesas']) > 0;
$guardado = $painel['receitas'] > 0 ? (int) round($painel['resultado'] / $painel['receitas'] * 100) : null;
$ritmo = $painel['ritmo'];
$parcelas = $painel['parcelas'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard • DFLG Investments</title>

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

        <!-- TOPO: saldo, resumo do mês, nível e atalhos -->
        <section class="dash-hero shadow-sm h-100 mb-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="dash-ola">Olá, <?= htmlspecialchars($primeiroNome) ?></div>
                    <div class="dash-sub">@<?= htmlspecialchars($usuario->getNickname()) ?> · sua visão financeira</div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if (!$painel['ehMesAtual']): ?>
                        <a href="<?= URL_BASE ?>/dashboard" class="btn btn-sm btn-outline-light rounded-pill">Voltar para hoje</a>
                    <?php endif; ?>

                    <div class="dash-mes">
                        <?php if ($painel['mesAnterior']): ?>
                            <a href="<?= URL_BASE ?>/dashboard?mes=<?= $painel['mesAnterior'] ?>" class="dash-mes-botao" aria-label="Mês anterior">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="dash-mes-botao desativado"><i class="bi bi-chevron-left"></i></span>
                        <?php endif; ?>

                        <span class="dash-mes-nome"><?= htmlspecialchars($painel['nomeMes']) ?></span>

                        <?php if ($painel['mesProximo']): ?>
                            <a href="<?= URL_BASE ?>/dashboard?mes=<?= $painel['mesProximo'] ?>" class="dash-mes-botao" aria-label="Próximo mês">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="dash-mes-botao desativado"><i class="bi bi-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4 align-items-stretch">

                <div class="col-lg-8">
                    <div class="dash-rotulo"><?= htmlspecialchars($painel['rotuloSaldo']) ?></div>
                    <div class="dash-saldo <?= $painel['saldo'] >= 0 ? 'dash-saldo-pos' : 'dash-saldo-neg' ?>">
                        <?= $brl($painel['saldo']) ?>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-md-4">
                            <div class="dash-kpi">
                                <div class="dash-kpi-rotulo"><i class="bi bi-arrow-up-circle me-1"></i>Receitas do mês</div>
                                <div class="dash-kpi-valor"><?= $brl($painel['receitas']) ?></div>
                                <?= $selo($painel['variacaoReceitas'], true) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dash-kpi">
                                <div class="dash-kpi-rotulo"><i class="bi bi-arrow-down-circle me-1"></i>Despesas do mês</div>
                                <div class="dash-kpi-valor"><?= $brl($painel['despesas']) ?></div>
                                <?= $selo($painel['variacaoDespesas'], false) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dash-kpi">
                                <div class="dash-kpi-rotulo"><i class="bi bi-calculator me-1"></i>Resultado do mês</div>
                                <div class="dash-kpi-valor <?= $resultadoPositivo ? 'dash-saldo-pos' : 'dash-saldo-neg' ?>">
                                    <?= $brl($painel['resultado']) ?>
                                </div>
                                <span class="dash-var dash-var-neutra">receitas − despesas</span>
                            </div>
                        </div>
                    </div>

                    <div class="dash-comparacao">Variações <?= htmlspecialchars($painel['rotuloComparacao']) ?>.</div>
                </div>

                <div class="col-lg-4">
                    <?php if ($gamificacao): ?>
                        <div class="dash-nivel mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><i class="bi bi-stars me-1 text-warning"></i>Nível <?= $gamificacao->getNivel() ?></span>
                                <span class="small"><i class="bi bi-fire text-warning"></i> <?= $gamificacao->getStreakAtual() ?> <?= $gamificacao->getStreakAtual() == 1 ? 'dia' : 'dias' ?></span>
                            </div>
                            <div class="progress dash-xp" role="progressbar" aria-label="Progresso até o próximo nível"
                                aria-valuenow="<?= $progressoNivel['percentual'] ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: <?= $progressoNivel['percentual'] ?>%"></div>
                            </div>
                            <div class="small mt-2" style="color: rgba(255, 255, 255, 0.65);">
                                <?= $progressoNivel['xpNoNivel'] ?> / <?= $progressoNivel['xpParaProximo'] ?> XP para o nível <?= $gamificacao->getNivel() + 1 ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="<?= URL_BASE ?>/transacoes/cadastrar" class="btn btn-dflg">
                            <i class="bi bi-plus-circle me-1"></i>Nova transação
                        </a>
                        <a href="<?= URL_BASE ?>/relatorios?tipo=mensal&ano=<?= substr($painel['mes'], 0, 4) ?>&mes=<?= (int) substr($painel['mes'], 5, 2) ?>"
                            class="btn btn-outline-light">
                            <i class="bi bi-file-earmark-text me-1"></i>Ver extrato do mês
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- GRÁFICOS -->
        <div class="row g-4 mb-4">

            <div class="col-lg-5">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-1">Para onde foi seu dinheiro</h5>
                        <p class="text-muted small">Despesas de <?= htmlspecialchars($painel['nomeMes']) ?> por categoria</p>

                        <?php if (empty($painel['categorias'])): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-pie-chart display-5 d-block mb-2"></i>
                                Nenhuma despesa neste mês.
                            </div>
                        <?php else: ?>
                            <div class="dash-chart dash-chart-rosca" data-grafico>
                                <canvas id="grafico-categorias" role="img" aria-label="Gráfico de despesas por categoria"></canvas>
                            </div>

                            <ul class="list-unstyled mt-3 mb-0">
                                <?php foreach ($painel['categorias'] as $categoria): ?>
                                    <li class="d-flex align-items-center gap-2 py-1">
                                        <span class="dash-cor" style="background: <?= htmlspecialchars($categoria['cor']) ?>;"></span>
                                        <i class="bi <?= htmlspecialchars($categoria['icone']) ?> text-muted"></i>
                                        <span class="flex-grow-1 text-truncate dash-min0"><?= htmlspecialchars($categoria['nome']) ?></span>
                                        <span class="small text-muted text-nowrap"><?= number_format($categoria['percentual'], 1, ',', '.') ?>%</span>
                                        <span class="fw-semibold text-nowrap"><?= $brl($categoria['valor']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-1">Receitas × despesas</h5>
                        <p class="text-muted small">Os últimos 6 meses até <?= htmlspecialchars($painel['nomeMes']) ?></p>

                        <?php if (!$temEvolucao): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-bar-chart display-5 d-block mb-2"></i>
                                Ainda não há movimentações nesse período.
                            </div>
                        <?php else: ?>
                            <div class="dash-chart" data-grafico>
                                <canvas id="grafico-evolucao" role="img" aria-label="Gráfico de receitas e despesas dos últimos 6 meses"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- RITMO DO MÊS -->
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted small mb-1"><i class="bi bi-speedometer2 me-1"></i>Gasto médio por dia</div>
                        <div class="dash-ritmo-valor"><?= $brl($ritmo['mediaDiaria']) ?></div>
                        <div class="text-muted small">
                            <?= $painel['ehMesAtual'] ? 'nos ' . $ritmo['diasDecorridos'] . ' dias do mês até hoje' : 'nos ' . $ritmo['diasDecorridos'] . ' dias do mês' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-muted small mb-1"><i class="bi bi-graph-down-arrow me-1"></i>Maior despesa do mês</div>
                        <?php if ($ritmo['maiorDespesa']): ?>
                            <div class="dash-ritmo-valor"><?= $brl($ritmo['maiorDespesa']->getValor()) ?></div>
                            <div class="text-muted small text-truncate">
                                <?= htmlspecialchars($ritmo['maiorDespesa']->getDescricao() ?: ($ritmo['maiorDespesa']->getNomeCategoria() ?? 'Sem descrição')) ?>
                                · <?= date('d/m', strtotime($ritmo['maiorDespesa']->getDataRegistro())) ?>
                            </div>
                        <?php else: ?>
                            <div class="dash-ritmo-valor">—</div>
                            <div class="text-muted small">sem despesas neste mês</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <?php if ($painel['ehMesAtual']): ?>
                            <div class="text-muted small mb-1"><i class="bi bi-calendar-check me-1"></i>Estimativa de despesas no mês</div>
                            <?php if ($ritmo['estimativa'] !== null): ?>
                                <div class="dash-ritmo-valor"><?= $brl($ritmo['estimativa']) ?></div>
                                <div class="text-muted small">se mantiver o ritmo atual (é só uma estimativa)</div>
                            <?php else: ?>
                                <div class="dash-ritmo-valor">—</div>
                                <div class="text-muted small">aparece a partir do dia <?= $ritmo['diasParaEstimar'] ?>, com mais dados</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-muted small mb-1"><i class="bi bi-piggy-bank me-1"></i>Quanto da receita sobrou</div>
                            <?php if ($guardado !== null): ?>
                                <div class="dash-ritmo-valor <?= $guardado >= 0 ? '' : 'text-danger' ?>"><?= $guardado ?>%</div>
                                <div class="text-muted small">resultado do mês sobre as receitas</div>
                            <?php else: ?>
                                <div class="dash-ritmo-valor">—</div>
                                <div class="text-muted small">sem receitas neste mês</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- LIMITES E METAS -->
        <div class="row g-4 mb-4">

            <div class="col-lg-6">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Limites do mês</h5>
                            <a href="<?= URL_BASE ?>/categorias" class="small">Gerenciar</a>
                        </div>

                        <?php if (empty($painel['limites'])): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-shield-check display-5 d-block mb-2"></i>
                                Você ainda não definiu limites.
                                <div><a href="<?= URL_BASE ?>/categorias">Definir limites por categoria</a></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($painel['limites'] as $limite): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="fw-semibold">
                                            <i class="bi <?= htmlspecialchars($limite['icone'] ?? 'bi-tag') ?> me-1"></i><?= htmlspecialchars($limite['nome']) ?>
                                            <?php if ($limite['excedido']): ?><span class="badge bg-danger ms-1">Excedido</span><?php endif; ?>
                                        </span>
                                        <span class="<?= $limite['excedido'] ? 'text-danger' : 'text-muted' ?>">
                                            <?= $brl($limite['gasto']) ?> de <?= $brl($limite['limite']) ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 8px;" role="progressbar" aria-valuenow="<?= $limite['percentual'] ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar <?= $limite['excedido'] ? 'bg-danger' : 'bg-success' ?>" style="width: <?= $limite['percentual'] ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Suas metas</h5>
                            <a href="<?= URL_BASE ?>/metas" class="small">
                                Ver todas (<?= $painel['totalMetas'] ?>)
                            </a>
                        </div>

                        <?php if (empty($painel['metas'])): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-bullseye display-5 d-block mb-2"></i>
                                Nenhuma meta cadastrada.
                                <div><a href="<?= URL_BASE ?>/metas/cadastrar">Criar minha primeira meta</a></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($painel['metas'] as $meta): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="fw-semibold">
                                            <?= htmlspecialchars($meta['nome']) ?>
                                            <?php if ($meta['concluida']): ?><span class="badge bg-success ms-1">Concluída</span><?php endif; ?>
                                        </span>
                                        <span class="text-muted"><?= number_format($meta['progresso'], 0) ?>%</span>
                                    </div>
                                    <div class="progress dash-barra-meta mt-1" style="height: 8px;" role="progressbar" aria-valuenow="<?= (int) $meta['progresso'] ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?= $meta['progresso'] ?>%"></div>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <?= $brl($meta['valorAtual']) ?> de <?= $brl($meta['valorAlvo']) ?>
                                        <?php if (!$meta['concluida']): ?>
                                            · faltam <?= $brl($meta['falta']) ?> · até <?= $meta['dataLimite'] ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- PARCELAS E ÚLTIMOS LANÇAMENTOS -->
        <div class="row g-4">

            <div class="col-lg-6">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0"><?= $painel['ehMesAtual'] ? 'A pagar neste mês' : 'Parcelas do mês' ?></h5>
                            <a href="<?= URL_BASE ?>/parcelamentos" class="small">Parcelamentos</a>
                        </div>

                        <?php if ($painel['ehMesAtual']): ?>
                            <div class="fs-4 fw-bold"><?= $brl($parcelas['totalAPagar']) ?></div>
                            <div class="text-muted small mb-3">
                                em parcelas não lançadas que vencem até o fim do mês
                                <?php if ($parcelas['totalPago'] > 0): ?>
                                    · já pagas no mês: <?= $brl($parcelas['totalPago']) ?>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="fs-4 fw-bold"><?= $brl($parcelas['totalPago']) ?></div>
                            <div class="text-muted small mb-3">em parcelas pagas neste mês</div>
                        <?php endif; ?>

                        <?php if (empty($parcelas['itens'])): ?>
                            <div class="text-muted small">
                                <i class="bi bi-check2-circle me-1"></i>
                                <?= $painel['ehMesAtual'] ? 'Nenhuma parcela a lançar neste mês.' : 'Nenhuma parcela neste mês.' ?>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($parcelas['itens'] as $parcela): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="text-truncate dash-min0">
                                            <span class="badge <?= !empty($parcela['atrasada']) ? 'text-bg-danger' : 'text-bg-secondary' ?> me-2"
                                                <?= !empty($parcela['atrasada']) ? 'title="Vencida e ainda não lançada"' : '' ?>><?= $parcela['data'] ?></span>
                                            <?= htmlspecialchars($parcela['descricao']) ?>
                                        </span>
                                        <span class="fw-semibold ms-2 text-nowrap"><?= $brl($parcela['valor']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dash-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Últimos lançamentos</h5>
                            <a href="<?= URL_BASE ?>/transacoes" class="small">Ver todos</a>
                        </div>

                        <?php if (empty($painel['ultimos'])): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-receipt display-5 d-block mb-2"></i>
                                Nenhum lançamento neste mês.
                                <div><a href="<?= URL_BASE ?>/transacoes/cadastrar">Registrar uma transação</a></div>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($painel['ultimos'] as $transacao): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="text-truncate dash-min0">
                                            <?php if ($transacao->eAporteMeta()): ?>
                                                <i class="bi bi-bullseye text-primary me-2"></i>
                                            <?php elseif ($transacao->eReceita()): ?>
                                                <i class="bi bi-arrow-up-circle-fill text-success me-2"></i>
                                            <?php else: ?>
                                                <i class="bi bi-arrow-down-circle-fill text-danger me-2"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($transacao->getDescricao() ?: ($transacao->getNomeCategoria() ?? 'Sem descrição')) ?>
                                            <span class="text-muted small d-block ms-4 text-truncate">
                                                <?= htmlspecialchars($transacao->getNomeCategoria() ?? 'Sem categoria') ?> · <?= date('d/m', strtotime($transacao->getDataRegistro())) ?>
                                            </span>
                                        </span>
                                        <span class="fw-semibold ms-2 text-nowrap <?= $transacao->eReceita() ? 'text-success' : 'text-danger' ?>">
                                            <?= $transacao->eReceita() ? '+' : '-' ?> <?= $brl($transacao->getValor()) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script type="application/json" id="dados-dashboard"><?= json_encode($painel['graficos'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js (versão fixa) e o script do dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script src="<?= URL_BASE ?>/assets/js/dashboard.js"></script>
</body>

</html>
