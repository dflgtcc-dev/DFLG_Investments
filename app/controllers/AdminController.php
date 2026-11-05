<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\UploadImagem;
use app\helpers\Validador;
use app\models\Usuario;
use app\services\AdminService;

class AdminController extends Controller
{
    private AdminService $adminService;

    public function __construct()
    {
        $this->adminService = new AdminService();
    }

    // RF 02: indicadores agregados de uso da plataforma
    public function painel()
    {
        $this->adminRequired();

        $data = [];
        $data['indicadores'] = $this->adminService->getIndicadores();
        $data['ehChefe'] = $this->adminService->eChefe($this->getUsuarioLogadoId());

        $this->view('admin/painel', $data);
    }

    // RF 02: lista de contas, com busca, filtro de status e paginação
    public function usuarios()
    {
        $this->adminRequired();

        $operadorId = $this->getUsuarioLogadoId();
        $filtros = $this->lerFiltros($_GET);

        $data = $this->adminService->listar($operadorId, $filtros);
        $filtros['tipo'] = $data['perfil'];
        $filtros['pagina'] = $data['pagina'];

        $data['filtros'] = $filtros;
        $data['ehChefe'] = $this->adminService->eChefe($operadorId);
        $data['aviso'] = $this->pegarAviso();

        $this->view('admin/usuarios', $data);
    }

    // RF 01 e RN 01: formulário para o chefe criar um administrador
    public function novo()
    {
        $this->adminRequired();
        $this->somenteChefe();

        $this->view('admin/usuario_form', ['modo' => 'criar', 'valores' => []]);
    }

    public function criar()
    {
        $this->adminRequired();
        $this->somenteChefe();

        $nome = $this->texto('nome');
        $nickname = $this->texto('nickname');
        $email = $this->texto('email');
        $senha = $_POST['senha'] ?? '';
        $dataNascimento = $this->texto('dataNascimento');
        $telefone = $this->telefone();

        $validador = new Validador();
        $validador->obrigatorio('nome', $nome);
        $validador->obrigatorio('nickname', $nickname);
        $validador->obrigatorio('email', $email);
        $validador->obrigatorio('senha', is_string($senha) ? $senha : '');
        $validador->obrigatorio('dataNascimento', $dataNascimento);

        $valores = [
            'nome' => $nome, 'nickname' => $nickname, 'email' => $email,
            'telefone' => Usuario::formatarTelefone($telefone), 'dataNascimento' => $dataNascimento,
        ];

        if ($validador->temErros()) {
            $this->view('admin/usuario_form', ['modo' => 'criar', 'valores' => $valores, 'erros' => $validador->getErros()]);

            return;
        }

        $administrador = new Usuario(0, $nome, $nickname, $email, (string) $senha, $dataNascimento, 'administrador', 'ativo', null, 'claro', $telefone);

        if (!$this->adminService->criarAdministrador($this->getUsuarioLogadoId(), $administrador)) {
            $this->view('admin/usuario_form', ['modo' => 'criar', 'valores' => $valores, 'erros' => $this->adminService->getErros()]);

            return;
        }

        $this->avisar('success', 'Administrador criado com sucesso.');
        $this->redirect(URL_BASE . '/admin/usuarios?tipo=administrador');
    }

    // RF 02: o administrador edita os dados da conta (menos a gamificação)
    public function editar()
    {
        $this->adminRequired();

        $alvo = $this->adminService->getAlvo($this->getUsuarioLogadoId(), $this->idDe($_GET));

        if (!$alvo) {
            $this->avisar('danger', 'Você não tem permissão para alterar esta conta, ou ela não existe mais.');
            $this->redirect($this->urlLista([]));
        }

        $this->view('admin/usuario_form', [
            'modo' => 'editar',
            'alvo' => $alvo,
            'valores' => $this->valoresDe($alvo),
        ]);
    }

    public function atualizar()
    {
        $this->adminRequired();

        $operadorId = $this->getUsuarioLogadoId();
        $alvo = $this->adminService->getAlvo($operadorId, $this->idDe($_POST));

        if (!$alvo) {
            $this->avisar('danger', 'Você não tem permissão para alterar esta conta, ou ela não existe mais.');
            $this->redirect($this->urlLista([]));
        }

        $nome = $this->texto('nome');
        $nickname = $this->texto('nickname');
        $email = $this->texto('email');
        $dataNascimento = $this->texto('dataNascimento');
        $telefone = $this->telefone();
        $removerFoto = isset($_POST['removerFoto']);

        $validador = new Validador();
        $validador->obrigatorio('nome', $nome);
        $validador->obrigatorio('nickname', $nickname);
        $validador->obrigatorio('email', $email);
        $validador->obrigatorio('dataNascimento', $dataNascimento);

        $dados = new Usuario(
            $alvo->getId(),
            $nome,
            $nickname,
            $email,
            $alvo->getSenha(),
            $dataNascimento,
            $alvo->getPerfil(),
            $alvo->getStatus(),
            $alvo->getFoto(),
            $alvo->getTema(),
            $telefone
        );
        $valores = $this->valoresDe($dados);

        if ($validador->temErros()) {
            $this->view('admin/usuario_form', ['modo' => 'editar', 'alvo' => $alvo, 'valores' => $valores, 'erros' => $validador->getErros()]);

            return;
        }

        if (!$this->adminService->atualizar($operadorId, $dados, $removerFoto)) {
            $this->view('admin/usuario_form', ['modo' => 'editar', 'alvo' => $alvo, 'valores' => $valores, 'erros' => $this->adminService->getErros()]);

            return;
        }

        if ($removerFoto && $alvo->getFoto()) {
            (new UploadImagem(__DIR__ . '/../../public/assets/uploads/perfil'))->remover($alvo->getFoto());
        }

        $this->avisar('success', 'Dados da conta atualizados.');
        $this->redirect($this->urlLista($this->lerFiltros($_POST)));
    }

    // RF 02: define uma nova senha para a conta (o administrador informa a senha à pessoa)
    public function redefinirSenha()
    {
        $this->adminRequired();

        $operadorId = $this->getUsuarioLogadoId();
        $alvo = $this->adminService->getAlvo($operadorId, $this->idDe($_POST));

        if (!$alvo) {
            $this->avisar('danger', 'Você não tem permissão para alterar esta conta, ou ela não existe mais.');
            $this->redirect($this->urlLista([]));
        }

        $novaSenha = (string) ($_POST['novaSenha'] ?? '');
        $confirmacao = (string) ($_POST['confirmarSenha'] ?? '');

        if (!$this->adminService->redefinirSenha($operadorId, $alvo->getId(), $novaSenha, $confirmacao)) {
            $this->view('admin/usuario_form', [
                'modo' => 'editar',
                'alvo' => $alvo,
                'valores' => $this->valoresDe($alvo),
                'erros' => $this->adminService->getErros(),
            ]);

            return;
        }

        $this->avisar('success', 'Senha redefinida. Informe a nova senha à pessoa; ela pode trocá-la em Perfil.');
        $this->redirect($this->urlLista($this->lerFiltros($_POST)));
    }

    public function bloquear()
    {
        $this->mudarStatus('bloqueado', 'Conta bloqueada.');
    }

    public function desbloquear()
    {
        $this->mudarStatus('ativo', 'Conta desbloqueada.');
    }

    // RF 02 e RN 14: somente o chefe exclui contas, e a exclusão exige confirmação explícita
    public function excluir()
    {
        $this->adminRequired();

        $operadorId = $this->getUsuarioLogadoId();
        $filtros = $this->lerFiltros($_POST);

        if (!$this->adminService->eChefe($operadorId)) {
            $this->avisar('danger', 'Somente o administrador chefe pode excluir contas.');
            $this->redirect($this->urlLista($filtros));
        }

        if (($_POST['confirmarExclusao'] ?? '') !== '1') {
            $this->avisar('danger', 'Marque a confirmação para excluir a conta.');
            $this->redirect($this->urlLista($filtros));
        }

        $excluido = $this->adminService->excluir($operadorId, $this->idDe($_POST));

        if (!$excluido) {
            $this->avisar('danger', 'Não foi possível excluir esta conta.');
            $this->redirect($this->urlLista($filtros));
        }

        if ($excluido->getFoto()) {
            (new UploadImagem(__DIR__ . '/../../public/assets/uploads/perfil'))->remover($excluido->getFoto());
        }

        $this->avisar('success', 'Conta excluída definitivamente.');
        $this->redirect($this->urlLista($filtros));
    }

    private function mudarStatus(string $status, string $mensagem)
    {
        $this->adminRequired();

        if ($this->adminService->alterarStatus($this->getUsuarioLogadoId(), $this->idDe($_POST), $status)) {
            $this->avisar('success', $mensagem);
        } else {
            $this->avisar('danger', 'Você não tem permissão para alterar esta conta, ou ela não existe mais.');
        }

        $this->redirect($this->urlLista($this->lerFiltros($_POST)));
    }

    private function somenteChefe()
    {
        if (!$this->adminService->eChefe($this->getUsuarioLogadoId())) {
            $this->avisar('danger', 'Somente o administrador chefe pode criar administradores.');
            $this->redirect(URL_BASE . '/admin');
        }
    }

    // aceita apenas os filtros conhecidos, com valores válidos
    private function lerFiltros(array $origem): array
    {
        $busca = $origem['busca'] ?? '';
        $busca = is_string($busca) ? mb_substr(trim($busca), 0, 100) : '';

        $status = $origem['status'] ?? '';
        $status = in_array($status, ['ativo', 'bloqueado'], true) ? $status : '';

        $tipo = ($origem['tipo'] ?? '') === 'administrador' ? 'administrador' : 'comum';

        return [
            'busca' => $busca,
            'status' => $status,
            'tipo' => $tipo,
            'pagina' => max(1, (int) ($origem['pagina'] ?? 1)),
        ];
    }

    private function urlLista(array $filtros): string
    {
        $filtros = $this->lerFiltros($filtros);
        $parametros = [];

        foreach (['busca' => '', 'status' => '', 'tipo' => 'comum', 'pagina' => 1] as $campo => $padrao) {
            if ($filtros[$campo] !== $padrao) {
                $parametros[$campo] = $filtros[$campo];
            }
        }

        return URL_BASE . '/admin/usuarios' . ($parametros ? '?' . http_build_query($parametros) : '');
    }

    private function idDe(array $origem): int
    {
        $id = $origem['id'] ?? '';

        return is_string($id) && ctype_digit($id) ? (int) $id : 0;
    }

    private function texto(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    // telefone é opcional: guarda só os dígitos, ou null quando vazio
    private function telefone(): ?string
    {
        $informado = $_POST['telefone'] ?? '';
        $digitos = preg_replace('/\D/', '', is_string($informado) ? $informado : '');

        return $digitos === '' ? null : $digitos;
    }

    private function valoresDe(Usuario $usuario): array
    {
        return [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'nickname' => $usuario->getNickname(),
            'email' => $usuario->getEmail(),
            'telefone' => $usuario->getTelefoneFormatado(),
            'dataNascimento' => $usuario->getDataNascimento(),
            'foto' => $usuario->getFoto(),
        ];
    }

    private function avisar(string $tipo, string $texto): void
    {
        $_SESSION['aviso_admin'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    // aviso deixado pela ação anterior; aparece uma só vez
    private function pegarAviso(): ?array
    {
        $aviso = $_SESSION['aviso_admin'] ?? null;
        unset($_SESSION['aviso_admin']);

        return $aviso;
    }
}