<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Transacao;
use app\services\CategoriaService;
use app\services\LimiteCategoriaService;
use app\services\TransacaoService;

class TransacaoController extends Controller
{
    private TransacaoService $service;
    private CategoriaService $categoriaService;
    private LimiteCategoriaService $limiteCategoriaService;

    public function __construct()
    {
        $this->service = new TransacaoService();
        $this->categoriaService = new CategoriaService();
        $this->limiteCategoriaService = new LimiteCategoriaService();
    }

    public function listarTodas()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->getUsuarioLogadoId();

        $leitura = $this->service->normalizarFiltros($_GET);
        $filtros = $leitura['filtros'];

        $data = [];
        $data['lista'] = $this->service->getTransacoes($usuarioId, $filtros);
        $data['resumoLista'] = $this->service->resumirLista($data['lista']);
        $data['categorias'] = $this->categoriaService->getCategorias();
        $data['filtros'] = $filtros;
        $data['errosFiltro'] = $leitura['erros'];
        $data['totalReceitas'] = $this->service->getTotalReceitas($usuarioId);
        $data['totalDespesas'] = $this->service->getTotalDespesas($usuarioId);
        $data['saldo'] = $this->service->getSaldo($usuarioId);
        $data['categoriasExcedidas'] = $this->limiteCategoriaService->getCategoriasExcedidas($usuarioId);

        $this->view('transacoes/transacoes_list', $data);
    }

    public function criar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

        $this->view('transacoes/transacoes_create', $data);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $tipo = $_POST['tipo'] ?? 'despesa';
        $valor = $_POST['valor'] ?? '';
        $categoriaId = $_POST['categoriaId'] ?? '';
        $dataRegistro = $_POST['dataRegistro'] ?? '';
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

        $validador->obrigatorio('valor', $valor);
        $validador->obrigatorio('categoriaId', $categoriaId);
        $validador->obrigatorio('dataRegistro', $dataRegistro);

        if ($validador->temErros()) {

            $data['transacao'] = $_POST;
            $data['erros'] = $validador->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

            $this->view('transacoes/transacoes_create', $data);

            return;
        }

        $transacao = new Transacao(
            0,
            $this->getUsuarioLogadoId(),
            (int) $categoriaId,
            $tipo,
            (float) $valor,
            $dataRegistro,
            $descricao
        );

        if ($this->service->saveTransacao($transacao)) {
            $this->redirect(URL_BASE . '/transacoes');
        } else {

            $data['transacao'] = $_POST;
            $data['erros'] = $this->service->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

            $this->view('transacoes/transacoes_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/transacoes');
        }

        $transacao = $this->service->getTransacao((int) $_GET['id'], $this->getUsuarioLogadoId());

        // RF 14: o aporte de meta não é editado por aqui
        // RF 13: a parcela é alterada pela tela de parcelamentos
        if (!$transacao || $transacao->eAporteMeta() || $transacao->eParcela()) {
            $this->redirect(URL_BASE . '/transacoes');
        }

        $data = [];
        $data['transacao'] = $transacao;
        $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

        $this->view('transacoes/transacoes_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $id = $_POST['id'] ?? 0;
        $tipo = $_POST['tipo'] ?? 'despesa';
        $valor = $_POST['valor'] ?? '';
        $categoriaId = $_POST['categoriaId'] ?? '';
        $dataRegistro = $_POST['dataRegistro'] ?? '';
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

        $validador->obrigatorio('valor', $valor);
        $validador->obrigatorio('categoriaId', $categoriaId);
        $validador->obrigatorio('dataRegistro', $dataRegistro);

        $transacao = new Transacao(
            (int) $id,
            $this->getUsuarioLogadoId(),
            $categoriaId !== '' ? (int) $categoriaId : null,
            $tipo,
            (float) $valor,
            $dataRegistro,
            $descricao
        );

        if ($validador->temErros()) {

            $data['transacao'] = $transacao;
            $data['erros'] = $validador->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

            $this->view('transacoes/transacoes_edit', $data);

            return;
        }

        if ($this->service->updateTransacao($transacao)) {
            $this->redirect(URL_BASE . '/transacoes');
        } else {

            $data['transacao'] = $transacao;
            $data['erros'] = $this->service->getErros();
            $data['categorias'] = $this->categoriaService->getCategoriasParaLancamento();

            $this->view('transacoes/transacoes_edit', $data);
        }
    }

    public function excluir()
    {
        $this->autenticacaoRequired();

        if (isset($_POST['id'])) {
            $this->service->deleteTransacao((int) $_POST['id'], $this->getUsuarioLogadoId());
        }

        $this->redirect(URL_BASE . '/transacoes');
    }
}