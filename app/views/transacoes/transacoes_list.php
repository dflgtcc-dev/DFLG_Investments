<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var mixed $categorias
 * @var mixed $categoriasExcedidas
 * @var array $filtros
 * @var array $errosFiltro
 * @var array $resumoLista
 * @var mixed $lista
 * @var mixed $saldo
 * @var mixed $totalDespesas
 * @var mixed $totalReceitas
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transações • DFLG Investments</title>

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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 cabecalho-pagina">
            <h2 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Minhas Finanças</h2>
            <a href="<?= URL_BASE ?>/transacoes/cadastrar" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Registro
            </a>
        </div>

        <!-- RESUMO -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total de receitas</p>
                        <h4 class="mb-0 text-success">R$ <?= number_format($totalReceitas, 2, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total de despesas</p>
                        <h4 class="mb-0 text-danger">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Saldo</p>
                        <h4 class="mb-0 <?= $saldo >= 0 ? 'text-success' : 'text-danger' ?>">R$ <?= number_format($saldo, 2, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // ---- filtros: monta os atalhos de período, os "chips" dos filtros ativos e os links para remover cada um
        $hoje = new DateTimeImmutable('today');
        $moeda = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $dataBr = fn ($d) => date('d/m/Y', strtotime($d));
        $nomeCategoria = '';
        foreach ($categorias as $categoria) {
            if ($filtros['categoriaId'] == $categoria->getId()) {
                $nomeCategoria = $categoria->getNome();
            }
        }

        $link = function (array $remover = [], array $trocar = []) use ($filtros) {
            $parametros = array_merge(array_diff_key($filtros, array_flip($remover)), $trocar);
            $parametros = array_filter($parametros, fn ($v) => $v !== null && $v !== '');

            return URL_BASE . '/transacoes' . ($parametros ? '?' . http_build_query($parametros) : '');
        };

        $chips = [];
        if ($filtros['tipo']) {
            $chips[] = [$filtros['tipo'] === 'receita' ? 'Receitas' : 'Despesas', ['tipo']];
        }
        if ($filtros['categoriaId']) {
            $chips[] = ['Categoria: ' . ($nomeCategoria ?: '#' . $filtros['categoriaId']), ['categoriaId']];
        }
        if ($filtros['origem']) {
            $chips[] = ['Origem: ' . ['manual' => 'lançadas por mim', 'parcela' => 'parcelas', 'meta' => 'metas'][$filtros['origem']], ['origem']];
        }
        if ($filtros['dataInicio'] && $filtros['dataFim']) {
            $chips[] = [$dataBr($filtros['dataInicio']) . ' a ' . $dataBr($filtros['dataFim']), ['dataInicio', 'dataFim']];
        } elseif ($filtros['dataInicio']) {
            $chips[] = ['A partir de ' . $dataBr($filtros['dataInicio']), ['dataInicio']];
        } elseif ($filtros['dataFim']) {
            $chips[] = ['Até ' . $dataBr($filtros['dataFim']), ['dataFim']];
        }
        if ($filtros['valorMin'] !== null && $filtros['valorMax'] !== null) {
            $chips[] = [$moeda($filtros['valorMin']) . ' a ' . $moeda($filtros['valorMax']), ['valorMin', 'valorMax']];
        } elseif ($filtros['valorMin'] !== null) {
            $chips[] = ['Valor a partir de ' . $moeda($filtros['valorMin']), ['valorMin']];
        } elseif ($filtros['valorMax'] !== null) {
            $chips[] = ['Valor até ' . $moeda($filtros['valorMax']), ['valorMax']];
        }
        if ($filtros['busca']) {
            $chips[] = ['Busca: "' . html_entity_decode($filtros['busca'], ENT_QUOTES) . '"', ['busca']];
        }
        $ordemRotulos = ['antigas' => 'Mais antigas', 'maior' => 'Maior valor', 'menor' => 'Menor valor'];
        if ($filtros['ordem']) {
            $chips[] = ['Ordem: ' . strtolower($ordemRotulos[$filtros['ordem']]), ['ordem']];
        }
        $ativos = count($chips);

        $atalhos = [
            'Este mês' => [$hoje->modify('first day of this month'), $hoje],
            'Mês passado' => [$hoje->modify('first day of last month'), $hoje->modify('last day of last month')],
            'Últimos 30 dias' => [$hoje->modify('-29 days'), $hoje],
            'Este ano' => [$hoje->setDate((int) $hoje->format('Y'), 1, 1), $hoje],
        ];
        ?>

        <!-- Filtros -->
        <div class="card shadow-sm mb-3 filtros-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filtros
                        <?php if ($ativos): ?><span class="badge bg-primary ms-1"><?= $ativos ?></span><?php endif; ?>
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#painelFiltros" aria-expanded="<?= $ativos ? 'true' : 'false' ?>" aria-controls="painelFiltros">
                        <i class="bi bi-sliders"></i> <span>Mostrar</span>
                    </button>
                </div>

                <div id="painelFiltros" class="collapse d-md-block <?= $ativos || $errosFiltro ? 'show' : '' ?>">
                    <!-- atalhos de período -->
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="small text-muted align-self-center me-1">Período:</span>
                        <?php foreach ($atalhos as $rotulo => [$de, $ate]):
                            $marcado = $filtros['dataInicio'] === $de->format('Y-m-d') && $filtros['dataFim'] === $ate->format('Y-m-d'); ?>
                            <a class="btn btn-sm <?= $marcado ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill"
                                href="<?= htmlspecialchars($link([], ['dataInicio' => $de->format('Y-m-d'), 'dataFim' => $ate->format('Y-m-d')])) ?>"><?= $rotulo ?></a>
                        <?php endforeach; ?>
                    </div>

                    <form action="<?= URL_BASE ?>/transacoes" method="get" class="row g-3 mt-0">
                        <div class="col-6 col-md-3">
                            <label for="tipo" class="form-label small fw-bold">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="receita" <?= $filtros['tipo'] === 'receita' ? 'selected' : '' ?>>Receitas</option>
                                <option value="despesa" <?= $filtros['tipo'] === 'despesa' ? 'selected' : '' ?>>Despesas</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="categoriaId" class="form-label small fw-bold">Categoria</label>
                            <select class="form-select" id="categoriaId" name="categoriaId">
                                <option value="">Todas</option>
                                <?php foreach (['receita' => 'Receitas', 'despesa' => 'Despesas'] as $tipoCat => $grupo): ?>
                                    <optgroup label="<?= $grupo ?>">
                                        <?php foreach ($categorias as $categoria): if ($categoria->getTipo() !== $tipoCat) { continue; } ?>
                                            <option value="<?= $categoria->getId() ?>" <?= $filtros['categoriaId'] == $categoria->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria->getNome()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="origem" class="form-label small fw-bold">Origem</label>
                            <select class="form-select" id="origem" name="origem">
                                <option value="">Todas</option>
                                <option value="manual" <?= $filtros['origem'] === 'manual' ? 'selected' : '' ?>>Lançadas por mim</option>
                                <option value="parcela" <?= $filtros['origem'] === 'parcela' ? 'selected' : '' ?>>Parcelas</option>
                                <option value="meta" <?= $filtros['origem'] === 'meta' ? 'selected' : '' ?>>Metas (aportes e resgates)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="ordem" class="form-label small fw-bold">Ordenar por</label>
                            <select class="form-select" id="ordem" name="ordem">
                                <option value="">Mais recentes</option>
                                <?php foreach ($ordemRotulos as $valor => $rotulo): ?>
                                    <option value="<?= $valor ?>" <?= $filtros['ordem'] === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="dataInicio" class="form-label small fw-bold">De</label>
                            <input type="date" class="form-control" id="dataInicio" name="dataInicio"
                                value="<?= htmlspecialchars((string) $filtros['dataInicio']) ?>" min="2000-01-01" max="<?= $hoje->format('Y-m-d') ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="dataFim" class="form-label small fw-bold">Até</label>
                            <input type="date" class="form-control" id="dataFim" name="dataFim"
                                value="<?= htmlspecialchars((string) $filtros['dataFim']) ?>" min="2000-01-01" max="<?= $hoje->format('Y-m-d') ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="valorMin" class="form-label small fw-bold">Valor mínimo</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="valorMin" name="valorMin" placeholder="0,00"
                                    value="<?= $filtros['valorMin'] !== null ? htmlspecialchars((string) $filtros['valorMin']) : '' ?>">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="valorMax" class="form-label small fw-bold">Valor máximo</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="valorMax" name="valorMax" placeholder="sem limite"
                                    value="<?= $filtros['valorMax'] !== null ? htmlspecialchars((string) $filtros['valorMax']) : '' ?>">
                            </div>
                        </div>

                        <div class="col-md-9">
                            <label for="busca" class="form-label small fw-bold">Buscar na descrição ou categoria</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="busca" name="busca" maxlength="100" placeholder="Ex.: mercado, aluguel, salário"
                                    value="<?= htmlspecialchars(html_entity_decode((string) $filtros['busca'], ENT_QUOTES)) ?>">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="<?= URL_BASE ?>/transacoes" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php foreach ($errosFiltro as $erroFiltro): ?>
            <div class="alert alert-warning py-2 small" role="alert"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erroFiltro) ?></div>
        <?php endforeach; ?>

        <?php if ($chips): ?>
            <!-- filtros ativos: o x remove só aquele filtro -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($chips as [$rotulo, $remover]): ?>
                    <a href="<?= htmlspecialchars($link($remover)) ?>" class="badge rounded-pill text-bg-secondary text-decoration-none fw-normal py-2 px-3"
                        title="Remover este filtro">
                        <?= htmlspecialchars($rotulo) ?> <i class="bi bi-x-lg ms-1"></i>
                    </a>
                <?php endforeach; ?>
                <a href="<?= URL_BASE ?>/transacoes" class="small align-self-center ms-1">Limpar tudo</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($lista)): ?>
            <!-- resumo do que está na tela -->
            <div class="d-flex flex-wrap gap-3 align-items-center small text-muted mb-2">
                <span><strong class="text-body"><?= $resumoLista['quantidade'] ?></strong> <?= $resumoLista['quantidade'] === 1 ? 'registro' : 'registros' ?></span>
                <span>Receitas <strong class="text-success"><?= $moeda($resumoLista['receitas']) ?></strong></span>
                <span>Despesas <strong class="text-danger"><?= $moeda($resumoLista['despesas']) ?></strong></span>
                <span>Resultado <strong class="<?= $resumoLista['resultado'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= $moeda($resumoLista['resultado']) ?></strong></span>
                <?php if ($resumoLista['transferencias'] > 0): ?>
                    <span title="Aportes e resgates de meta só movem o saldo e não entram nas somas">
                        <i class="bi bi-info-circle"></i> <?= $resumoLista['transferencias'] ?> de metas fora das somas
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($lista)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <?php if ($chips): ?>
                    Nenhum registro encontrado com esses filtros. <a href="<?= URL_BASE ?>/transacoes" class="alert-link">Limpar filtros</a>
                <?php else: ?>
                    Nenhum registro financeiro encontrado.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle tabela-cards">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Data</th>
                                    <th class="px-4 py-3">Descrição</th>
                                    <th class="px-4 py-3">Categoria</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3 text-end">Valor</th>
                                    <th class="px-4 py-3 text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $transacao): ?>
                                    <tr>
                                        <td class="px-4 py-3 text-nowrap" data-label="Data"><?= date('d/m/Y', strtotime($transacao->getDataRegistro())) ?></td>
                                        <td class="px-4 py-3" data-label="Descrição"><?= htmlspecialchars($transacao->getDescricao() ?? '-') ?></td>
                                        <td class="px-4 py-3" data-label="Categoria"><?= htmlspecialchars($transacao->getNomeCategoria() ?? 'Sem categoria') ?></td>
                                        <td class="px-4 py-3 td-oculta-celular" data-label="Tipo">
                                            <span class="badge <?= $transacao->eReceita() ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $transacao->eReceita() ? 'Receita' : 'Despesa' ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-end fw-bold text-nowrap <?= $transacao->eReceita() ? 'text-success' : 'text-danger' ?>" data-label="Valor">
                                            <?= $transacao->eReceita() ? '+' : '-' ?> R$ <?= number_format($transacao->getValor(), 2, ',', '.') ?>
                                        </td>
                                        <td class="px-4 py-3 text-end text-nowrap td-acoes">
                                            <?php if ($transacao->eParcela()): ?>
                                                <a href="<?= URL_BASE ?>/parcelamentos" class="btn btn-sm btn-outline-secondary" title="Parcelas são alteradas na tela de parcelamentos">
                                                    <i class="bi bi-credit-card-2-front"></i> Ver parcelamento
                                                </a>
                                            <?php elseif ($transacao->eAporteMeta()): ?>
                                                <a href="<?= URL_BASE ?>/metas" class="btn btn-sm btn-outline-secondary" title="Aportes e resgates são feitos na tela de metas">
                                                    <i class="bi bi-bullseye"></i> Ver metas
                                                </a>
                                            <?php else: ?>
                                            <a href="<?= URL_BASE ?>/transacoes/editar?id=<?= $transacao->getId() ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <form method="POST" action="<?= URL_BASE ?>/transacoes/excluir" class="d-inline"
                                                onsubmit="return confirm('Deseja realmente excluir este registro?')">
                                                <input type="hidden" name="id" value="<?= $transacao->getId() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                            <?php endif; ?>
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
