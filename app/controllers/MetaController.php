<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Meta;
use app\services\MetaService;

class MetaController extends Controller
{
    private MetaService $service;

    public function __construct()
    {
        $this->service = new MetaService();
    }

    public function listarTodas()
    {
        $this->autenticacaoRequired();

        $data = [];
        $data['lista'] = $this->service->getMetas($this->getUsuarioLogadoId());

        $this->view('metas/metas_list', $data);
    }

    public function criar()
    {
        $this->autenticacaoRequired();

        $this->view('metas/metas_create');
    }

    public function salvar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $nomeMeta = filter_input(INPUT_POST, 'nomeMeta', FILTER_SANITIZE_SPECIAL_CHARS);
        $tipoMeta = $_POST['tipoMeta'] ?? 'economizar';
        $valorAlvo = $_POST['valorAlvo'] ?? '';
        $valorAtual = $_POST['valorAtual'] ?? 0;
        $dataLimite = $_POST['dataLimite'] ?? '';

        $validador->obrigatorio('nomeMeta', $nomeMeta, 'Informe o nome da meta');
        $validador->obrigatorio('valorAlvo', $valorAlvo, 'Informe o valor alvo');
        $validador->obrigatorio('dataLimite', $dataLimite, 'Informe a data limite');

        if ($validador->temErros()) {

            $data['meta'] = $_POST;
            $data['erros'] = $validador->getErros();

            $this->view('metas/metas_create', $data);

            return;
        }

        $meta = new Meta(
            0,
            $this->getUsuarioLogadoId(),
            $nomeMeta,
            $tipoMeta,
            (float) $valorAlvo,
            (float) $valorAtual,
            $dataLimite
        );

        if ($this->service->saveMeta($meta)) {
            $this->redirect(URL_BASE . '/metas');
        } else {

            $data['meta'] = $_POST;
            $data['erros'] = $this->service->getErros();

            $this->view('metas/metas_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/metas');
        }

        $meta = $this->service->getMeta((int) $_GET['id'], $this->getUsuarioLogadoId());

        if (!$meta) {
            $this->redirect(URL_BASE . '/metas');
        }

        $data = [];
        $data['meta'] = $meta;

        $this->view('metas/metas_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $id = $_POST['id'] ?? 0;
        $nomeMeta = filter_input(INPUT_POST, 'nomeMeta', FILTER_SANITIZE_SPECIAL_CHARS);
        $tipoMeta = $_POST['tipoMeta'] ?? 'economizar';
        $valorAlvo = $_POST['valorAlvo'] ?? '';
        $dataLimite = $_POST['dataLimite'] ?? '';

        // o valor guardado não vem do formulário: ele muda pelos aportes (RF 14)
        $metaAtual = $this->service->getMeta((int) $id, $this->getUsuarioLogadoId());

        if (!$metaAtual) {
            $this->redirect(URL_BASE . '/metas');
        }

        $validador->obrigatorio('nomeMeta', $nomeMeta, 'Informe o nome da meta');
        $validador->obrigatorio('valorAlvo', $valorAlvo, 'Informe o valor alvo');
        $validador->obrigatorio('dataLimite', $dataLimite, 'Informe a data limite');

        $meta = new Meta(
            (int) $id,
            $this->getUsuarioLogadoId(),
            $nomeMeta,
            $tipoMeta,
            (float) $valorAlvo,
            $metaAtual->getValorAtual(),
            $dataLimite
        );

        if ($validador->temErros()) {

            $data['meta'] = $meta;
            $data['erros'] = $validador->getErros();

            $this->view('metas/metas_edit', $data);

            return;
        }

        if ($this->service->updateMeta($meta)) {
            $this->redirect(URL_BASE . '/metas');
        } else {

            $data['meta'] = $meta;
            $data['erros'] = $this->service->getErros();

            $this->view('metas/metas_edit', $data);
        }
    }

    public function excluir()
    {
        $this->autenticacaoRequired();

        if (isset($_POST['id'])) {
            $this->service->deleteMeta((int) $_POST['id'], $this->getUsuarioLogadoId());
        }

        $this->redirect(URL_BASE . '/metas');
    }

    // RF 14: tela para transferir saldo para a meta
    public function aportar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/metas');
        }

        $meta = $this->service->getMeta((int) $_GET['id'], $this->getUsuarioLogadoId());

        // meta inexistente ou já concluída não recebe aporte
        if (!$meta || $meta->eConcluida()) {
            $this->redirect(URL_BASE . '/metas');
        }

        $data = [];
        $data['meta'] = $meta;
        $data['saldo'] = $this->service->getSaldo($this->getUsuarioLogadoId());

        $this->view('metas/metas_aportar', $data);
    }

    public function salvarAporte()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $id = (int) ($_POST['id'] ?? 0);
        $valor = $_POST['valor'] ?? '';
        $usuarioId = $this->getUsuarioLogadoId();

        $validador->obrigatorio('valor', $valor, 'Informe o valor do aporte');

        if (!$this->service->getMeta($id, $usuarioId)) {
            $this->redirect(URL_BASE . '/metas');
        }

        if (!$validador->temErros() && $this->service->aportar($id, $usuarioId, (float) $valor)) {
            $this->redirect(URL_BASE . '/metas');
        }

        $data['meta'] = $this->service->getMeta($id, $usuarioId);
        $data['saldo'] = $this->service->getSaldo($usuarioId);
        $data['erros'] = $validador->temErros() ? $validador->getErros() : $this->service->getErros();

        $this->view('metas/metas_aportar', $data);
    }

    // RF 14: tela para devolver dinheiro da meta ao saldo
    public function resgatar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/metas');
        }

        $meta = $this->service->getMeta((int) $_GET['id'], $this->getUsuarioLogadoId());

        if (!$meta || $meta->getValorAtual() <= 0) {
            $this->redirect(URL_BASE . '/metas');
        }

        $data = [];
        $data['meta'] = $meta;
        $data['saldo'] = $this->service->getSaldo($this->getUsuarioLogadoId());

        $this->view('metas/metas_resgatar', $data);
    }

    public function salvarResgate()
    {
        $this->autenticacaoRequired();

        $data = [];
        $validador = new Validador();

        $id = (int) ($_POST['id'] ?? 0);
        $valor = $_POST['valor'] ?? '';
        $usuarioId = $this->getUsuarioLogadoId();

        $validador->obrigatorio('valor', is_string($valor) ? $valor : '', 'Informe o valor do resgate');

        if (!$this->service->getMeta($id, $usuarioId)) {
            $this->redirect(URL_BASE . '/metas');
        }

        if (!$validador->temErros() && $this->service->resgatar($id, $usuarioId, (float) $valor)) {
            $this->redirect(URL_BASE . '/metas');
        }

        $data['meta'] = $this->service->getMeta($id, $usuarioId);
        $data['saldo'] = $this->service->getSaldo($usuarioId);
        $data['erros'] = $validador->temErros() ? $validador->getErros() : $this->service->getErros();

        $this->view('metas/metas_resgatar', $data);
    }
}
