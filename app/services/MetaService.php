<?php

namespace app\services;

use app\models\Meta;
use app\repositories\MetaRepository;
use DateTimeImmutable;

class MetaService
{
    private MetaRepository $repository;
    private TransacaoService $transacaoService;
    private CategoriaService $categoriaService;
    private array $erros = [];

    public function __construct()
    {
        $this->repository = new MetaRepository();
        $this->transacaoService = new TransacaoService();
        $this->categoriaService = new CategoriaService();
    }

    public function getMetas(int $usuarioId): array
    {
        return $this->repository->getMetasPorUsuario($usuarioId);
    }

    public function getMeta(int $id, int $usuarioId)
    {
        return $this->repository->getMeta($id, $usuarioId);
    }

    public function saveMeta(Meta $meta): bool
    {
        if (!$this->validar($meta)) {
            return false;
        }

        return $this->repository->saveMeta($meta);
    }

    public function updateMeta(Meta $meta): bool
    {
        $metaGravada = $this->repository->getMeta($meta->getId(), $meta->getUsuarioId());

        if (!$metaGravada) {
            $this->erros['meta'] = "Meta não encontrada";

            return false;
        }

        // o valor guardado vale o do banco (muda só pelos aportes), nunca o que vier na edição
        $meta = new Meta(
            $meta->getId(),
            $meta->getUsuarioId(),
            $meta->getNomeMeta(),
            $meta->getTipoMeta(),
            $meta->getValorAlvo(),
            $metaGravada->getValorAtual(),
            $meta->getDataLimite()
        );

        if (!$this->validar($meta)) {
            return false;
        }

        if (!$this->repository->updateMeta($meta)) {
            // um aporte gravado entre a leitura e o UPDATE pode ter passado do novo valor alvo
            $this->erros['valorAlvo'] = "O valor alvo não pode ser menor que o valor já guardado na meta";

            return false;
        }

        return true;
    }

    // ao excluir a meta, todo o valor guardado volta para o saldo
    public function deleteMeta(int $id, int $usuarioId): bool
    {
        $categoria = $this->categoriaService->getCategoriaPorNome('Outras receitas', 'receita');

        return $this->repository->deleteMeta($id, $usuarioId, $categoria ? $categoria->getId() : null);
    }

    // RF 14: tira dinheiro da meta e devolve para o saldo
    public function resgatar(int $metaId, int $usuarioId, float $valor): bool
    {
        $this->erros = [];

        $meta = $this->repository->getMeta($metaId, $usuarioId);

        if (!$meta) {
            $this->erros['meta'] = "Meta não encontrada";

            return false;
        }

        $valor = round($valor, 2);
        $guardado = round($meta->getValorAtual(), 2);

        if ($valor <= 0) {
            $this->erros['valor'] = "O valor deve ser maior que zero";
        } elseif ($valor > $guardado) {
            $this->erros['valor'] = "O valor não pode passar do que está guardado na meta (R$ " . number_format($guardado, 2, ',', '.') . ")";
        }

        if (!empty($this->erros)) {
            return false;
        }

        $categoria = $this->categoriaService->getCategoriaPorNome(CategoriaService::CATEGORIA_RESGATE_META, 'receita');

        if (!$categoria) {
            $this->erros['meta'] = "Categoria \"" . CategoriaService::CATEGORIA_RESGATE_META . "\" não encontrada";

            return false;
        }

        $descricao = "Resgate da meta: " . $meta->getNomeMeta();

        if (!$this->repository->registrarResgate($metaId, $usuarioId, $valor, $categoria->getId(), $descricao)) {
            $this->erros['valor'] = "Não foi possível registrar o resgate";

            return false;
        }

        return true;
    }

    public function getSaldo(int $usuarioId): float
    {
        return $this->transacaoService->getSaldo($usuarioId);
    }

    // RF 14 (UC-14 inclui UC-07.5): transfere saldo para a meta
    public function aportar(int $metaId, int $usuarioId, float $valor): bool
    {
        $this->erros = [];

        $meta = $this->repository->getMeta($metaId, $usuarioId);

        if (!$meta) {
            $this->erros['meta'] = "Meta não encontrada";

            return false;
        }

        if ($meta->eConcluida()) {
            $this->erros['valor'] = "Esta meta já foi concluída";

            return false;
        }

        $valor = round($valor, 2);
        $faltante = round($meta->getValorAlvo() - $meta->getValorAtual(), 2);
        $saldo = $this->transacaoService->getSaldo($usuarioId);

        if ($valor <= 0) {
            $this->erros['valor'] = "O valor deve ser maior que zero";
        } elseif ($valor > $faltante) {
            $this->erros['valor'] = "O valor não pode passar do que falta para concluir a meta (R$ " . number_format($faltante, 2, ',', '.') . ")";
        } elseif ($valor > $saldo) {
            $this->erros['valor'] = "Saldo insuficiente (saldo disponível: R$ " . number_format($saldo, 2, ',', '.') . ")";
        }

        if (!empty($this->erros)) {
            return false;
        }

        $categoria = $this->categoriaService->getCategoriaPorNome(CategoriaService::CATEGORIA_APORTE_META, 'despesa');

        if (!$categoria) {
            $this->erros['meta'] = "Categoria \"" . CategoriaService::CATEGORIA_APORTE_META . "\" não encontrada";

            return false;
        }

        $descricao = "Aporte na meta: " . $meta->getNomeMeta();

        if (!$this->repository->registrarAporte($metaId, $usuarioId, $valor, $categoria->getId(), $descricao)) {
            $this->erros['valor'] = "Não foi possível registrar o aporte";

            return false;
        }

        return true;
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    private function validar(Meta $meta): bool
    {
        $this->erros = [];

        if ($meta->getValorAlvo() <= 0) {
            $this->erros['valorAlvo'] = "O valor alvo deve ser maior que zero";
        }

        if ($meta->getValorAtual() < 0) {
            $this->erros['valorAtual'] = "O valor atual não pode ser negativo";
        }

        // o valor alvo nunca fica abaixo do que já está na meta (valor já guardado + aportes),
        // senão a meta ficaria com mais de 100% e o "falta" negativo
        if ($meta->getValorAlvo() > 0 && round($meta->getValorAlvo(), 2) < round($meta->getValorAtual(), 2)) {
            $valorAtualTexto = "R$ " . number_format($meta->getValorAtual(), 2, ',', '.');

            if ($meta->getId() > 0) {
                $this->erros['valorAlvo'] = "O valor alvo não pode ser menor que o valor já guardado na meta ({$valorAtualTexto}, somando o valor inicial e os aportes)";
            } else {
                $this->erros['valorAtual'] = "O valor já guardado não pode ser maior que o valor alvo";
            }
        }

        $hoje = new DateTimeImmutable('today');
        $dataLimite = DateTimeImmutable::createFromFormat('Y-m-d', $meta->getDataLimite());

        if (!$dataLimite) {
            $this->erros['dataLimite'] = "Informe uma data limite válida";
        } elseif ($dataLimite->setTime(0, 0) <= $hoje) {
            $this->erros['dataLimite'] = "A data limite deve ser posterior à data de hoje";
        }

        return empty($this->erros);
    }
}