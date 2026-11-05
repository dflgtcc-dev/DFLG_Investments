<?php

namespace app\services;

use app\models\Transacao;
use app\repositories\TransacaoRepository;

class TransacaoService
{
    private TransacaoRepository $repository;
    private CategoriaService $categoriaService;
    private array $erros = [];

    public function __construct()
    {
        $this->repository = new TransacaoRepository();
        $this->categoriaService = new CategoriaService();
    }

    public function getTransacoes(int $usuarioId, array $filtros = []): array
    {
        return $this->repository->getTransacoesPorUsuario($usuarioId, $filtros);
    }

    public const MAX_BUSCA = 100;

    // lê os filtros da URL (texto de fora, nunca confiável): o que for inválido é descartado e vira um aviso
    // devolve ['filtros' => [...], 'erros' => [...]]
    public function normalizarFiltros(array $entrada): array
    {
        $texto = function (string $campo) use ($entrada): string {
            $valor = $entrada[$campo] ?? '';

            return is_string($valor) ? trim($valor) : '';
        };

        $erros = [];
        $filtros = [
            'tipo' => null, 'categoriaId' => null, 'origem' => null, 'ordem' => null,
            'dataInicio' => null, 'dataFim' => null, 'valorMin' => null, 'valorMax' => null, 'busca' => null,
        ];

        $tipo = $texto('tipo');
        $filtros['tipo'] = in_array($tipo, ['receita', 'despesa'], true) ? $tipo : null;

        $categoriaId = $texto('categoriaId');
        $filtros['categoriaId'] = ctype_digit($categoriaId) && (int) $categoriaId > 0 ? (int) $categoriaId : null;

        $origem = $texto('origem');
        $filtros['origem'] = in_array($origem, ['manual', 'parcela', 'meta'], true) ? $origem : null;

        $ordem = $texto('ordem');
        $filtros['ordem'] = in_array($ordem, ['antigas', 'maior', 'menor'], true) ? $ordem : null;

        foreach (['dataInicio' => 'inicial', 'dataFim' => 'final'] as $campo => $nome) {
            $data = $texto($campo);

            if ($data === '') {
                continue;
            }

            $valida = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);

            if (!$valida || $valida->format('Y-m-d') !== $data) {
                $erros[$campo] = "A data {$nome} é inválida e foi ignorada";
            } else {
                $filtros[$campo] = $data;
            }
        }

        if ($filtros['dataInicio'] && $filtros['dataFim'] && $filtros['dataInicio'] > $filtros['dataFim']) {
            $erros['dataInicio'] = "A data inicial não pode ser depois da data final; o período foi ignorado";
            $filtros['dataInicio'] = $filtros['dataFim'] = null;
        }

        foreach (['valorMin' => 'mínimo', 'valorMax' => 'máximo'] as $campo => $nome) {
            $valor = str_replace(',', '.', $texto($campo));

            if ($valor === '') {
                continue;
            }

            if (!is_numeric($valor) || (float) $valor < 0 || (float) $valor > 99999999.99) {
                $erros[$campo] = "O valor {$nome} é inválido e foi ignorado";
            } else {
                $filtros[$campo] = round((float) $valor, 2);
            }
        }

        if ($filtros['valorMin'] !== null && $filtros['valorMax'] !== null && $filtros['valorMin'] > $filtros['valorMax']) {
            $erros['valorMin'] = "O valor mínimo não pode ser maior que o máximo; a faixa de valor foi ignorada";
            $filtros['valorMin'] = $filtros['valorMax'] = null;
        }

        $busca = mb_substr($texto('busca'), 0, self::MAX_BUSCA);
        // a descrição é gravada com os caracteres especiais escapados, então a busca passa pelo mesmo tratamento
        $filtros['busca'] = $busca !== '' ? filter_var($busca, FILTER_SANITIZE_SPECIAL_CHARS) : null;

        return ['filtros' => $filtros, 'erros' => $erros];
    }

    // resumo da lista filtrada; aportes e resgates de meta só movem o saldo, então ficam fora das somas
    public function resumirLista(array $lista): array
    {
        $receitas = 0.0;
        $despesas = 0.0;
        $transferencias = 0;

        foreach ($lista as $transacao) {
            if ($transacao->eAporteMeta()) {
                $transferencias++;
            } elseif ($transacao->eReceita()) {
                $receitas += $transacao->getValor();
            } else {
                $despesas += $transacao->getValor();
            }
        }

        return [
            'quantidade' => count($lista),
            'receitas' => round($receitas, 2),
            'despesas' => round($despesas, 2),
            'resultado' => round($receitas - $despesas, 2),
            'transferencias' => $transferencias,
        ];
    }

    public function getTransacao(int $id, int $usuarioId)
    {
        return $this->repository->getTransacao($id, $usuarioId);
    }

    public function saveTransacao(Transacao $transacao): bool
    {
        if (!$this->validar($transacao)) {
            return false;
        }

        return $this->repository->saveTransacao($transacao);
    }

    public function updateTransacao(Transacao $transacao): bool
    {
        $existente = $this->repository->getTransacao($transacao->getId(), $transacao->getUsuarioId());

        if (!$existente) {
            $this->erros['transacao'] = "Transação não encontrada";

            return false;
        }

        // RF 14: aportes e resgates são movimentos das metas: só mudam pela tela de metas
        if ($existente->eAporteMeta()) {
            $this->erros['transacao'] = "Esta transferência de meta não pode ser editada";

            return false;
        }

        // RF 13: a parcela é controlada pelo parcelamento (lançar e desfazer ficam lá)
        if ($existente->eParcela()) {
            $this->erros['transacao'] = "Esta parcela é controlada pelo parcelamento e não pode ser editada aqui";

            return false;
        }

        if (!$this->validar($transacao)) {
            return false;
        }

        return $this->repository->updateTransacao($transacao);
    }

    public function deleteTransacao(int $id, int $usuarioId): bool
    {
        $this->erros = [];
        $existente = $this->repository->getTransacao($id, $usuarioId);

        // RF 13: para tirar uma parcela, use "Desfazer" na tela de parcelamentos
        if ($existente && $existente->eParcela()) {
            $this->erros['transacao'] = "Esta parcela é controlada pelo parcelamento. Use \"Desfazer\" na tela de parcelamentos.";

            return false;
        }

        if ($existente && $existente->eAporteMeta()) {
            $this->erros['transacao'] = "Esta transferência é de uma meta. Para tirar o dinheiro da meta, use \"Resgatar\" na tela de metas.";

            return false;
        }

        return $this->repository->deleteTransacao($id, $usuarioId);
    }

    public function getTotalReceitas(int $usuarioId): float
    {
        return $this->repository->getTotalPorTipo($usuarioId, 'receita');
    }

    public function getTotalDespesas(int $usuarioId): float
    {
        return $this->repository->getTotalPorTipo($usuarioId, 'despesa');
    }

    public function getSaldo(int $usuarioId): float
    {
        return $this->repository->getSaldo($usuarioId);
    }

    public function getEvolucaoMensal(int $usuarioId, int $meses = 6): array
    {
        return $this->repository->getEvolucaoMensal($usuarioId, $meses);
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    private function validar(Transacao $transacao): bool
    {
        $this->erros = [];

        if ($transacao->getValor() <= 0) {
            $this->erros['valor'] = "O valor deve ser maior que zero";
        }

        if (!in_array($transacao->getTipo(), ['receita', 'despesa'])) {
            $this->erros['tipo'] = "Selecione um tipo válido (receita ou despesa)";
        }

        $categoria = $transacao->getCategoriaId() ? $this->categoriaService->getCategoria($transacao->getCategoriaId()) : false;

        if (!$categoria) {
            $this->erros['categoriaId'] = "Selecione uma categoria válida";
        } elseif ($this->categoriaService->eCategoriaAporteMeta($categoria)) {
            // RF 14: a categoria Metas é gerada pelos aportes
            $this->erros['categoriaId'] = "A categoria Metas é usada apenas pelos aportes das metas";
        } elseif ($categoria->getTipo() !== $transacao->getTipo()) {
            $this->erros['categoriaId'] = "Essa categoria é de {$categoria->getTipo()}, não de {$transacao->getTipo()}";
        }

        if (!$transacao->getDataRegistro()) {
            $this->erros['dataRegistro'] = "Informe a data do registro";
        }

        return empty($this->erros);
    }
}