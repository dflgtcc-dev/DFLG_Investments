<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Parcelamento;
use app\services\CategoriaService;
use app\services\LimiteCategoriaService;
use app\services\ParcelamentoService;

class ParcelaController extends Controller
{
    private ParcelamentoService $service;
    private CategoriaService $categoriaService;
    private LimiteCategoriaService $limiteCategoriaService;

    public function __construct()
    {
        $this->service = new ParcelamentoService();
        $this->categoriaService = new CategoriaService();
        $this->limiteCategoriaService = new LimiteCategoriaService();
    }

    public function listarTodos()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['lista'] = $this->service->getParcelamentos($this->getUsuarioLogadoId());
        $data['categoriasExcedidas'] = $this->limiteCategoriaService->getCategoriasExcedidas($this->getUsuarioLogadoId());
        $data['aviso'] = $this->pegarAviso();

        $this->view('parcelamentos/parcelamentos_list', $data);
    }

    public function criar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

        $this->view('parcelamentos/parcelamentos_create', $data);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $descricao = $this->textoPost('descricao');
        $tag = $this->textoPost('tag');
        $valorParcela = $this->escalarPost('valorParcela');
        $quantidadeParcelas = $this->escalarPost('quantidadeParcelas');
        $dataPrimeiraParcela = $this->escalarPost('dataPrimeiraParcela');
        $categoriaId = $this->escalarPost('categoriaId');

        $validador->obrigatorio('descricao', $descricao);
        $validador->obrigatorio('valorParcela', $valorParcela);
        $validador->obrigatorio('quantidadeParcelas', $quantidadeParcelas);
        $validador->obrigatorio('dataPrimeiraParcela', $dataPrimeiraParcela);

        $erros = $validador->getErros() + $this->errosDeFormato($valorParcela, $quantidadeParcelas, $categoriaId);

        if (!empty($erros)) {

            $data['parcelamento'] = $_POST;
            $data['erros'] = $erros;
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

            $this->view('parcelamentos/parcelamentos_create', $data);

            return;
        }

        $parcelamento = new Parcelamento(
            0,
            $this->getUsuarioLogadoId(),
            $categoriaId !== '' ? (int) $categoriaId : null,
            $descricao,
            $this->paraValor($valorParcela),
            (int) $quantidadeParcelas,
            0,
            $dataPrimeiraParcela,
            null,
            $tag !== '' ? $tag : null
        );

        if ($this->service->saveParcelamento($parcelamento)) {
            $this->avisar('success', 'Parcelamento cadastrado. Use "Lançar parcela" a cada parcela paga.');
            $this->redirect(URL_BASE . '/parcelamentos');
        } else {

            $data['parcelamento'] = $_POST;
            $data['erros'] = $this->service->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

            $this->view('parcelamentos/parcelamentos_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/parcelamentos');
        }

        $parcelamento = $this->service->getParcelamento((int) $_GET['id'], $this->getUsuarioLogadoId());

        if (!$parcelamento) {
            $this->redirect(URL_BASE . '/parcelamentos');
        }

        $data = [];
        $data['parcelamento'] = $parcelamento;
        $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

        $this->view('parcelamentos/parcelamentos_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $id = (int) $this->escalarPost('id');
        $descricao = $this->textoPost('descricao');
        $tag = $this->textoPost('tag');
        $valorParcela = $this->escalarPost('valorParcela');
        $quantidadeParcelas = $this->escalarPost('quantidadeParcelas');
        $categoriaId = $this->escalarPost('categoriaId');

        // a data da 1ª parcela e as parcelas pagas vêm do banco: a data não pode ser editada
        $gravado = $this->service->getParcelamento($id, $this->getUsuarioLogadoId());

        if (!$gravado) {
            $this->redirect(URL_BASE . '/parcelamentos');
        }

        $validador->obrigatorio('descricao', $descricao);
        $validador->obrigatorio('valorParcela', $valorParcela);
        $validador->obrigatorio('quantidadeParcelas', $quantidadeParcelas);

        $erros = $validador->getErros() + $this->errosDeFormato($valorParcela, $quantidadeParcelas, $categoriaId);

        $parcelamento = new Parcelamento(
            $id,
            $this->getUsuarioLogadoId(),
            $categoriaId !== '' && ctype_digit($categoriaId) ? (int) $categoriaId : null,
            $descricao,
            $this->paraValor($valorParcela),
            ctype_digit($quantidadeParcelas) ? (int) $quantidadeParcelas : 0,
            $gravado->getParcelasPagas(),
            $gravado->getDataPrimeiraParcela(),
            null,
            $tag !== '' ? $tag : null,
            $gravado->getValorPago()
        );

        if (!empty($erros)) {

            $data['parcelamento'] = $parcelamento;
            $data['erros'] = $erros;
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

            $this->view('parcelamentos/parcelamentos_edit', $data);

            return;
        }

        if ($this->service->updateParcelamento($parcelamento)) {
            $this->avisar('success', 'Parcelamento atualizado.');
            $this->redirect(URL_BASE . '/parcelamentos');
        } else {

            $data['parcelamento'] = $parcelamento;
            $data['erros'] = $this->service->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento('despesa');

            $this->view('parcelamentos/parcelamentos_edit', $data);
        }
    }

    public function excluir()
    {
        $this->autenticacaoRequired();

        $id = (int) $this->escalarPost('id');

        if ($id > 0) {
            if ($this->service->deleteParcelamento($id, $this->getUsuarioLogadoId())) {
                $this->avisar('success', 'Parcelamento excluído. As parcelas já lançadas continuam nas suas transações.');
            } else {
                $this->avisar('danger', $this->primeiroErro());
            }
        }

        $this->redirect(URL_BASE . '/parcelamentos');
    }

    // botão "Lançar parcela": lança a próxima parcela da sequência
    public function lancar()
    {
        $this->autenticacaoRequired();

        $id = (int) $this->escalarPost('id');
        $parcelamento = $this->service->getParcelamento($id, $this->getUsuarioLogadoId());
        $numero = $parcelamento ? $parcelamento->getNumeroProximaParcela() : null;

        if ($this->service->lancarParcela($id, $this->getUsuarioLogadoId())) {
            $this->avisar('success', "Parcela {$numero}/{$parcelamento->getQuantidadeParcelas()} de \"" .
                html_entity_decode($parcelamento->getDescricao(), ENT_QUOTES) . "\" lançada.");
        } else {
            $this->avisar('danger', $this->primeiroErro());
        }

        $this->redirect(URL_BASE . '/parcelamentos');
    }

    // corrige um lançamento feito por engano: remove só a última parcela lançada
    public function desfazer()
    {
        $this->autenticacaoRequired();

        $id = (int) $this->escalarPost('id');
        $parcelamento = $this->service->getParcelamento($id, $this->getUsuarioLogadoId());

        if ($this->service->desfazerUltimaParcela($id, $this->getUsuarioLogadoId())) {
            $this->avisar('success', "Parcela {$parcelamento->getParcelasPagas()}/{$parcelamento->getQuantidadeParcelas()} desfeita.");
        } else {
            $this->avisar('danger', $this->primeiroErro());
        }

        $this->redirect(URL_BASE . '/parcelamentos');
    }

    // aceita só texto simples (um array no POST, como descricao[]=x, vira vazio)
    private function escalarPost(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    private function textoPost(string $campo): string
    {
        // mesmo tratamento do resto do sistema: o texto é gravado já escapado
        return filter_var($this->escalarPost($campo), FILTER_SANITIZE_SPECIAL_CHARS);
    }

    // o campo é type="number" (manda ponto), mas aceita vírgula se vier digitado de outro jeito
    private function paraValor(string $valor): float
    {
        if (str_contains($valor, ',') && !str_contains($valor, '.')) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? round((float) $valor, 2) : 0.0;
    }

    private function errosDeFormato(string $valorParcela, string $quantidadeParcelas, string $categoriaId): array
    {
        $erros = [];

        if ($valorParcela !== '' && $this->paraValor($valorParcela) <= 0) {
            $erros['valorParcela'] = "Informe um valor maior que zero";
        }

        if ($quantidadeParcelas !== '' && !ctype_digit($quantidadeParcelas)) {
            $erros['quantidadeParcelas'] = "Informe um número inteiro de parcelas";
        }

        if ($categoriaId !== '' && !ctype_digit($categoriaId)) {
            $erros['categoriaId'] = "Categoria inválida";
        }

        return $erros;
    }

    private function primeiroErro(): string
    {
        $erros = $this->service->getErros();

        return $erros ? reset($erros) : 'Não foi possível concluir a ação.';
    }

    private function avisar(string $tipo, string $texto): void
    {
        $_SESSION['aviso_parcelamentos'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    // aviso deixado pela ação anterior; aparece uma só vez
    private function pegarAviso(): ?array
    {
        $aviso = $_SESSION['aviso_parcelamentos'] ?? null;
        unset($_SESSION['aviso_parcelamentos']);

        return $aviso;
    }
}
