<?php

namespace app\services;

use app\models\Categoria;
use app\repositories\CategoriaRepository;

class CategoriaService
{
    // RF 14: categoria das despesas geradas pelos aportes nas metas (não pode ser escolhida em lançamentos)
    public const CATEGORIA_APORTE_META = 'Metas';
    public const CATEGORIA_RESGATE_META = 'Resgate de metas';

    private CategoriaRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoriaRepository();
    }

    public function getCategorias(?string $tipo = null): array
    {
        return $this->repository->getCategorias($tipo);
    }

    // categorias que o usuário pode escolher ao lançar uma receita, despesa ou parcelamento
    public function getCategoriasParaLancamento(?string $tipo = null): array
    {
        $categorias = [];

        foreach ($this->getCategorias($tipo) as $categoria) {
            if (!$this->eCategoriaAporteMeta($categoria)) {
                $categorias[] = $categoria;
            }
        }

        return $categorias;
    }

    // RF 14: as categorias "Metas" (aporte) e "Resgate de metas" são usadas só pelas transferências das metas
    public function eCategoriaAporteMeta(Categoria $categoria): bool
    {
        return ($categoria->getTipo() === 'despesa' && $categoria->getNome() === self::CATEGORIA_APORTE_META)
            || ($categoria->getTipo() === 'receita' && $categoria->getNome() === self::CATEGORIA_RESGATE_META);
    }

    public function getCategoriaPorNome(string $nome, string $tipo)
    {
        return $this->repository->getCategoriaPorNome($nome, $tipo);
    }

    public function getCategoria(int $id)
    {
        return $this->repository->getCategoria($id);
    }
}