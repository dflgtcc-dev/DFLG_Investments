<?php
/**
 * Variáveis disponibilizadas pelo controller (via Controller::view/extract).
 * @var array $linhas
 * @var int $total
 * @var int $pagina
 * @var int $paginas
 * @var array $filtros
 * @var bool $ehChefe
 * @var array|null $aviso
 */

// endereço da lista com os filtros atuais (e uma página específica, quando informada)
$urlLista = function (array $mudancas = []) use ($filtros) {
    $parametros = array_merge($filtros, $mudancas);
    $padroes = ['busca' => '', 'status' => '', 'tipo' => 'comum', 'pagina' => 1];

    foreach ($padroes as $campo => $padrao) {
        if ($parametros[$campo] === $padrao) {
            unset($parametros[$campo]);
        }
    }

    return URL_BASE . '/admin/usuarios' . ($parametros ? '?' . http_build_query($parametros) : '');
};

// campos escondidos que levam os filtros de volta depois de uma ação
$retorno = function () use ($filtros) {
    foreach ($filtros as $campo => $valor) {
        echo '<input type="hidden" name="' . htmlspecialchars($campo) . '" value="' . htmlspecialchars((string) $valor) . '">';
    }
};

$ehListaDeAdmins = $filtros['tipo'] === 'administrador';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários • DFLG Investments</title>

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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h2 class="mb-0"><?= $ehListaDeAdmins ? 'Administradores' : 'Usuários comuns' ?></h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= URL_BASE ?>/admin" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Painel
                </a>
                <?php if ($ehChefe): ?>
                    <a href="<?= URL_BASE ?>/admin/usuarios/novo" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Novo administrador
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($aviso): ?>
            <div class="alert alert-<?= htmlspecialchars($aviso['tipo']) ?>" role="alert">
                <?= htmlspecialchars($aviso['texto']) ?>
            </div>
        <?php endif; ?>

        <?php if ($ehChefe): ?>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?= $ehListaDeAdmins ? '' : 'active' ?>" href="<?= $urlLista(['tipo' => 'comum', 'pagina' => 1]) ?>">Usuários comuns</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $ehListaDeAdmins ? 'active' : '' ?>" href="<?= $urlLista(['tipo' => 'administrador', 'pagina' => 1]) ?>">Administradores</a>
                </li>
            </ul>
        <?php endif; ?>

        <form method="get" action="<?= URL_BASE ?>/admin/usuarios" class="row g-2 mb-3">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($filtros['tipo']) ?>">
            <div class="col-md-6">
                <input type="search" class="form-control" name="busca" maxlength="100"
                    placeholder="Buscar por nome, nickname ou e-mail" value="<?= htmlspecialchars($filtros['busca']) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status" aria-label="Situação da conta">
                    <option value="" <?= $filtros['status'] === '' ? 'selected' : '' ?>>Todas as situações</option>
                    <option value="ativo" <?= $filtros['status'] === 'ativo' ? 'selected' : '' ?>>Ativas</option>
                    <option value="bloqueado" <?= $filtros['status'] === 'bloqueado' ? 'selected' : '' ?>>Bloqueadas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                <a href="<?= $urlLista(['busca' => '', 'status' => '', 'pagina' => 1]) ?>" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabela-cards">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <?php if (!$ehListaDeAdmins): ?>
                                <th>Nível</th>
                                <th>Último acesso</th>
                            <?php else: ?>
                                <th>Permissão</th>
                            <?php endif; ?>
                            <th>Situação</th>
                            <th>Cadastro</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$linhas): ?>
                            <tr>
                                <td colspan="8" class="text-center text-body-secondary py-4">Nenhuma conta encontrada.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($linhas as $linha): ?>
                            <tr>
                                <td class="td-titulo">
                                    <div class="fw-semibold"><?= htmlspecialchars($linha['nome']) ?></div>
                                    <div class="text-body-secondary small">@<?= htmlspecialchars($linha['nickname']) ?></div>
                                </td>
                                <td data-label="E-mail" class="text-break"><?= htmlspecialchars($linha['email']) ?></td>
                                <td data-label="Telefone" class="text-nowrap"><?= $linha['telefone'] ? htmlspecialchars(\app\models\Usuario::formatarTelefone($linha['telefone'])) : '—' ?></td>
                                <?php if (!$ehListaDeAdmins): ?>
                                    <td data-label="Nível"><?= $linha['nivel'] !== null ? (int) $linha['nivel'] : '—' ?></td>
                                    <td data-label="Último acesso"><?= $linha['ultimo_acesso'] ? date('d/m/Y', strtotime($linha['ultimo_acesso'])) : 'Nunca' ?></td>
                                <?php else: ?>
                                    <td data-label="Permissão"><?= $linha['eChefe'] ? 'Chefe' : 'Comum' ?></td>
                                <?php endif; ?>
                                <td data-label="Situação">
                                    <?php if ($linha['status'] === 'ativo'): ?>
                                        <span class="badge text-bg-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Bloqueada</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Cadastro"><?= date('d/m/Y', strtotime($linha['criado_em'])) ?></td>
                                <td class="text-end text-nowrap td-acoes">
                                    <?php if ($linha['eChefe'] || $linha['eVoce']): ?>
                                        <span class="text-body-secondary small">
                                            <i class="bi bi-shield-lock"></i> <?= $linha['eVoce'] ? 'Sua conta' : 'Conta protegida' ?>
                                        </span>
                                    <?php elseif ($linha['podeGerenciar']): ?>
                                        <a href="<?= URL_BASE ?>/admin/usuarios/editar?id=<?= (int) $linha['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="post" action="<?= URL_BASE ?>/admin/usuarios/<?= $linha['status'] === 'ativo' ? 'bloquear' : 'desbloquear' ?>" class="d-inline">
                                            <input type="hidden" name="id" value="<?= (int) $linha['id'] ?>">
                                            <?php $retorno(); ?>
                                            <?php if ($linha['status'] === 'ativo'): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Bloquear">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Desbloquear">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <?php if ($linha['podeExcluir']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir"
                                                data-bs-toggle="modal" data-bs-target="#modalExcluir"
                                                data-id="<?= (int) $linha['id'] ?>"
                                                data-nome="<?= htmlspecialchars($linha['nome']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <span class="text-body-secondary small"><?= $total ?> conta(s) • página <?= $pagina ?> de <?= $paginas ?></span>

            <?php if ($paginas > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $urlLista(['pagina' => $pagina - 1]) ?>">Anterior</a>
                        </li>
                        <?php for ($i = 1; $i <= $paginas; $i++): ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $urlLista(['pagina' => $i]) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $urlLista(['pagina' => $pagina + 1]) ?>">Próxima</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

    </div>

    <?php if ($ehChefe): ?>
        <!-- RN 14: a exclusão é irreversível e exige confirmação explícita -->
        <div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirTitulo" aria-hidden="true">
            <div class="modal-dialog">
                <form method="post" action="<?= URL_BASE ?>/admin/usuarios/excluir" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalExcluirTitulo">Excluir conta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p>Você vai excluir a conta de <strong id="modalExcluirNome"></strong>.</p>
                        <p class="text-danger small mb-3">
                            Esta ação é irreversível e remove permanentemente todos os dados da conta,
                            incluindo registros financeiros, metas e parcelamentos.
                        </p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmarExclusao" name="confirmarExclusao" value="1" required>
                            <label class="form-check-label" for="confirmarExclusao">Entendo que não é possível desfazer</label>
                        </div>
                        <input type="hidden" name="id" id="modalExcluirId" value="">
                        <?php $retorno(); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Excluir definitivamente</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($ehChefe): ?>
        <script>
            // preenche o modal de exclusão com a conta escolhida e desmarca a confirmação
            document.getElementById('modalExcluir').addEventListener('show.bs.modal', function (evento) {
                var botao = evento.relatedTarget;
                document.getElementById('modalExcluirId').value = botao.getAttribute('data-id');
                document.getElementById('modalExcluirNome').textContent = botao.getAttribute('data-nome');
                document.getElementById('confirmarExclusao').checked = false;
            });
        </script>
    <?php endif; ?>
</body>

</html>