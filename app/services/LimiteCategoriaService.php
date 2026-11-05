<?php

namespace app\services;

use app\repositories\LimiteCategoriaRepository;

// RF 12: limite mensal de gastos por categoria e aviso das categorias que passaram dele (RN 12)
class LimiteCategoriaService
{
    // cabe em DECIMAL(10,2)
    private const LIMITE_MAXIMO = 99999999.99;

    private LimiteCategoriaRepository $repository;
    private array $erros = [];

    public function __construct()
    {
        $this->repository = new LimiteCategoriaRepository();
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    // categorias de despesa com o limite e o gasto do mês (só despesas com data até hoje)
    // $mes no formato AAAA-MM; sem ele, usa o mês atual
    public function getCategoriasComLimite(int $usuarioId, ?string $mes = null): array
    {
        $referencia = $mes !== null ? strtotime($mes . '-01') : time();

        $linhas = $this->repository->getCategoriasComGasto(
            $usuarioId,
            date('Y-m-01', $referencia),
            date('Y-m-t', $referencia),
            CategoriaService::CATEGORIA_APORTE_META
        );

        $categorias = [];

        foreach ($linhas as $linha) {
            $limite = $linha['valor_limite'] !== null ? (float) $linha['valor_limite'] : null;
            $gasto = round((float) $linha['gasto'], 2);

            $categorias[] = [
                'id' => (int) $linha['id'],
                'nome' => $linha['nome'],
                'icone' => $linha['icone'],
                'limite' => $limite,
                'gasto' => $gasto,
                // gastar exatamente o limite não excede
                'excedido' => $limite !== null && $gasto > $limite,
                'percentual' => $limite ? min(100, (int) round($gasto / $limite * 100)) : 0,
            ];
        }

        return $categorias;
    }

    // RN 12: categorias que passaram do limite no mês atual
    public function getCategoriasExcedidas(int $usuarioId): array
    {
        $excedidas = [];

        foreach ($this->getCategoriasComLimite($usuarioId) as $categoria) {
            if ($categoria['excedido']) {
                $categoria['excesso'] = round($categoria['gasto'] - $categoria['limite'], 2);
                $excedidas[] = $categoria;
            }
        }

        return $excedidas;
    }

    // recebe os valores digitados (id da categoria => texto); vazio remove o limite
    // nada é gravado se algum valor for inválido
    public function salvarLimites(int $usuarioId, array $valores): bool
    {
        $this->erros = [];

        $limites = [];

        foreach ($this->getCategoriasComLimite($usuarioId) as $categoria) {
            $id = $categoria['id'];

            // categoria que não veio no formulário não muda
            if (!array_key_exists($id, $valores)) {
                continue;
            }

            $texto = is_string($valores[$id]) ? trim($valores[$id]) : '';

            if ($texto === '') {
                $limites[$id] = null;

                continue;
            }

            if (!is_numeric($texto)) {
                $this->erros[$id] = "Informe um valor numérico";
            } elseif ((float) $texto <= 0) {
                $this->erros[$id] = "O limite deve ser maior que zero";
            } elseif ((float) $texto > self::LIMITE_MAXIMO) {
                $this->erros[$id] = "O limite é maior que o permitido";
            } else {
                $limites[$id] = round((float) $texto, 2);
            }
        }

        if (!empty($this->erros)) {
            return false;
        }

        if (!$this->repository->salvarLimites($usuarioId, $limites)) {
            $this->erros['geral'] = "Não foi possível salvar os limites";

            return false;
        }

        return true;
    }
}
