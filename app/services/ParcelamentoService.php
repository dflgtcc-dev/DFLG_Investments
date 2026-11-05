<?php

namespace app\services;

use app\models\Parcelamento;
use app\repositories\ParcelamentoRepository;

class ParcelamentoService
{
    // limites (o banco guarda DECIMAL(10,2), descrição VARCHAR(255) e tag VARCHAR(50))
    public const MAX_PARCELAS = 420;
    public const MAX_VALOR = 99999999.99;
    public const MAX_DESCRICAO = 200;
    public const MAX_TAG = 50;
    public const DATA_MINIMA = '2000-01-01';
    public const DATA_MAXIMA = '2099-12-31';

    private ParcelamentoRepository $repository;
    private CategoriaService $categoriaService;
    private array $erros = [];

    public function __construct()
    {
        $this->repository = new ParcelamentoRepository();
        $this->categoriaService = new CategoriaService();
    }

    public function getParcelamentos(int $usuarioId): array
    {
        return $this->repository->getParcelamentosPorUsuario($usuarioId);
    }

    public function getParcelamento(int $id, int $usuarioId)
    {
        return $this->repository->getParcelamento($id, $usuarioId);
    }

    public function saveParcelamento(Parcelamento $parcelamento): bool
    {
        if (!$this->validar($parcelamento)) {
            return false;
        }

        if (!$this->repository->saveParcelamento($parcelamento)) {
            $this->erros['parcelamento'] = "Não foi possível salvar o parcelamento. Tente novamente.";

            return false;
        }

        return true;
    }

    public function updateParcelamento(Parcelamento $parcelamento): bool
    {
        $this->erros = [];
        $gravado = $this->repository->getParcelamento($parcelamento->getId(), $parcelamento->getUsuarioId());

        if (!$gravado) {
            $this->erros['parcelamento'] = "Parcelamento não encontrado";

            return false;
        }

        // a data da 1ª parcela e o que já foi lançado vêm do banco, nunca da edição
        $parcelamento = new Parcelamento(
            $parcelamento->getId(),
            $parcelamento->getUsuarioId(),
            $parcelamento->getCategoriaId(),
            $parcelamento->getDescricao(),
            $parcelamento->getValorParcela(),
            $parcelamento->getQuantidadeParcelas(),
            $gravado->getParcelasPagas(),
            $gravado->getDataPrimeiraParcela(),
            null,
            $parcelamento->getTag(),
            $gravado->getValorPago()
        );

        if (!$this->validar($parcelamento)) {
            return false;
        }

        // não dá para ter menos parcelas do que as já pagas (ex.: "11 pagas de 5")
        if ($parcelamento->getQuantidadeParcelas() < $gravado->getParcelasPagas()) {
            $this->erros['quantidadeParcelas'] = "A quantidade de parcelas não pode ser menor que as já pagas ("
                . $gravado->getParcelasPagas() . ")";

            return false;
        }

        if (!$this->repository->updateParcelamento($parcelamento)) {
            $this->erros['parcelamento'] = "Não foi possível salvar o parcelamento. Tente novamente.";

            return false;
        }

        return true;
    }

    public function deleteParcelamento(int $id, int $usuarioId): bool
    {
        $this->erros = [];

        if (!$this->repository->deleteParcelamento($id, $usuarioId)) {
            $this->erros['parcelamento'] = "Não foi possível excluir o parcelamento.";

            return false;
        }

        return true;
    }

    // lança a próxima parcela da sequência. Parcela vencida entra na data do vencimento;
    // parcela antecipada (vence depois de hoje) entra com a data de hoje. Nunca fica registro no futuro.
    public function lancarParcela(int $id, int $usuarioId): bool
    {
        $this->erros = [];
        $parcelamento = $this->repository->getParcelamento($id, $usuarioId);

        if (!$parcelamento) {
            $this->erros['parcelamento'] = "Parcelamento não encontrado";

            return false;
        }

        if ($parcelamento->eQuitado()) {
            $this->erros['parcelamento'] = "Todas as parcelas deste parcelamento já foram lançadas.";

            return false;
        }

        $numero = $parcelamento->getNumeroProximaParcela();
        $hoje = date('Y-m-d');
        $dataRegistro = min($parcelamento->getDataVencimento($numero), $hoje);

        if (!$this->repository->lancarParcela($parcelamento, $numero, $dataRegistro)) {
            $atual = $this->repository->getParcelamento($id, $usuarioId);

            // a chave única recusou: outra requisição lançou essa parcela antes (ex.: clique duplo)
            $this->erros['parcelamento'] = ($atual && $atual->getParcelasPagas() >= $numero)
                ? "A parcela {$numero} já foi lançada."
                : "Não foi possível lançar a parcela. Tente novamente.";

            return false;
        }

        return true;
    }

    public function desfazerUltimaParcela(int $id, int $usuarioId): bool
    {
        $this->erros = [];
        $parcelamento = $this->repository->getParcelamento($id, $usuarioId);

        if (!$parcelamento) {
            $this->erros['parcelamento'] = "Parcelamento não encontrado";

            return false;
        }

        if ($parcelamento->getParcelasPagas() === 0) {
            $this->erros['parcelamento'] = "Nenhuma parcela foi lançada ainda.";

            return false;
        }

        if (!$this->repository->desfazerUltimaParcela($id, $usuarioId, $parcelamento->getParcelasPagas())) {
            $this->erros['parcelamento'] = "Não foi possível desfazer a última parcela.";

            return false;
        }

        return true;
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    private function validar(Parcelamento $parcelamento): bool
    {
        $this->erros = [];

        $descricao = trim($parcelamento->getDescricao());
        if ($descricao === '') {
            $this->erros['descricao'] = "Informe a descrição";
        } elseif (mb_strlen($descricao) > self::MAX_DESCRICAO) {
            $this->erros['descricao'] = "A descrição pode ter no máximo " . self::MAX_DESCRICAO . " caracteres";
        }

        if ($parcelamento->getTag() !== null && mb_strlen($parcelamento->getTag()) > self::MAX_TAG) {
            $this->erros['tag'] = "A tag pode ter no máximo " . self::MAX_TAG . " caracteres";
        }

        $quantidade = $parcelamento->getQuantidadeParcelas();
        if ($quantidade <= 1) {
            $this->erros['quantidadeParcelas'] = "O parcelamento deve ter mais de uma parcela";
        } elseif ($quantidade > self::MAX_PARCELAS) {
            $this->erros['quantidadeParcelas'] = "O parcelamento pode ter no máximo " . self::MAX_PARCELAS . " parcelas";
        }

        $valor = $parcelamento->getValorParcela();
        if ($valor <= 0) {
            $this->erros['valorParcela'] = "O valor da parcela deve ser maior que zero";
        } elseif ($valor > self::MAX_VALOR) {
            $this->erros['valorParcela'] = "O valor da parcela é alto demais";
        } elseif (!isset($this->erros['quantidadeParcelas']) && $parcelamento->getValorTotal() > self::MAX_VALOR) {
            $this->erros['valorParcela'] = "O valor total (parcela × quantidade) não pode passar de R$ 99.999.999,99";
        }

        $data = $parcelamento->getDataPrimeiraParcela();
        $dataValida = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        if ($data === '') {
            $this->erros['dataPrimeiraParcela'] = "Informe a data da primeira parcela";
        } elseif (!$dataValida || $dataValida->format('Y-m-d') !== $data) {
            // recusa datas como 2026-02-30, que o PHP transformaria em 02/03
            $this->erros['dataPrimeiraParcela'] = "Informe uma data válida";
        } elseif ($data < self::DATA_MINIMA || $data > self::DATA_MAXIMA) {
            $this->erros['dataPrimeiraParcela'] = "A data deve estar entre 01/01/2000 e 31/12/2099";
        }

        if ($parcelamento->getCategoriaId() !== null) {
            $categoria = $this->categoriaService->getCategoria($parcelamento->getCategoriaId());

            if (!$categoria) {
                $this->erros['categoriaId'] = "Categoria não encontrada";
            } elseif ($categoria->getTipo() !== 'despesa') {
                $this->erros['categoriaId'] = "Escolha uma categoria de despesa";
            } elseif ($this->categoriaService->eCategoriaAporteMeta($categoria)) {
                // RF 14: a categoria Metas é gerada pelos aportes
                $this->erros['categoriaId'] = "A categoria Metas é usada apenas pelos aportes das metas";
            }
        }

        return empty($this->erros);
    }
}
