<?php

namespace app\services;

use app\models\Usuario;
use app\models\UsuarioAdministrador;
use app\repositories\PainelAdminRepository;
use app\repositories\UsuarioAdministradorRepository;
use app\repositories\UsuarioRepository;

class AdminService
{
    private UsuarioRepository $usuarioRepository;
    private UsuarioAdministradorRepository $administradorRepository;
    private PainelAdminRepository $painelRepository;
    private UsuarioService $usuarioService;
    private array $erros = [];

    public function __construct()
    {
        $this->usuarioRepository = new UsuarioRepository();
        $this->administradorRepository = new UsuarioAdministradorRepository();
        $this->painelRepository = new PainelAdminRepository();
        $this->usuarioService = new UsuarioService();
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    // nível de permissão do administrador, ou null quando a conta não é de administrador
    public function getNivel(int $usuarioId): ?int
    {
        $administrador = $this->administradorRepository->getPorUsuarioId($usuarioId);

        return $administrador ? $administrador->getNivelPermissao() : null;
    }

    // RN 01 e RN 09: o administrador chefe tem nível 1
    public function eChefe(int $usuarioId): bool
    {
        return $this->getNivel($usuarioId) === UsuarioAdministrador::NIVEL_CHEFE;
    }

    /**
     * Regra central de permissão (RF 01, RF 02, RN 01, RN 09):
     * - ninguém age sobre a própria conta pelo painel nem sobre o administrador chefe;
     * - o chefe age sobre administradores e usuários comuns;
     * - o administrador comum age somente sobre usuários comuns.
     */
    private function permitido(int $operadorId, ?Usuario $alvo): bool
    {
        if ($alvo === null || $alvo->getId() === $operadorId) {
            return false;
        }

        if ($this->getNivel($operadorId) === null || $this->eChefe($alvo->getId())) {
            return false;
        }

        if ($this->eChefe($operadorId)) {
            return true;
        }

        return $alvo->getPerfil() === 'comum';
    }

    // devolve o usuário apenas se o operador puder gerenciá-lo
    public function getAlvo(int $operadorId, int $alvoId): ?Usuario
    {
        $alvo = $this->usuarioRepository->getUsuarioById($alvoId);

        if (!$alvo || !$this->permitido($operadorId, $alvo)) {
            return null;
        }

        return $alvo;
    }

    public function listar(int $operadorId, array $filtros): array
    {
        $ehChefe = $this->eChefe($operadorId);

        // o administrador comum só enxerga usuários comuns
        $perfil = $ehChefe ? $filtros['tipo'] : 'comum';

        $resultado = $this->painelRepository->listar($perfil, $filtros['busca'], $filtros['status'], $filtros['pagina']);

        foreach ($resultado['linhas'] as &$linha) {
            $alvo = $this->usuarioRepository->getUsuarioById((int) $linha['id']);

            $linha['eChefe'] = (int) ($linha['nivel_permissao'] ?? 0) === UsuarioAdministrador::NIVEL_CHEFE;
            $linha['eVoce'] = (int) $linha['id'] === $operadorId;
            $linha['podeGerenciar'] = $this->permitido($operadorId, $alvo ?: null);
            // excluir conta é exclusivo do administrador chefe
            $linha['podeExcluir'] = $ehChefe && $linha['podeGerenciar'];
        }
        unset($linha);

        $resultado['perfil'] = $perfil;

        return $resultado;
    }

    public function alterarStatus(int $operadorId, int $alvoId, string $status): bool
    {
        $alvo = $this->getAlvo($operadorId, $alvoId);

        if (!$alvo || !in_array($status, ['ativo', 'bloqueado'], true)) {
            return false;
        }

        return $this->painelRepository->atualizarStatus($alvoId, $status);
    }

    // devolve o usuário excluído (para apagar a foto) ou null quando não for permitido
    public function excluir(int $operadorId, int $alvoId): ?Usuario
    {
        if (!$this->eChefe($operadorId)) {
            return null;
        }

        $alvo = $this->getAlvo($operadorId, $alvoId);

        if (!$alvo || !$this->usuarioRepository->deleteUsuario($alvoId)) {
            return null;
        }

        return $alvo;
    }

    public function atualizar(int $operadorId, Usuario $dados, bool $removerFoto): bool
    {
        $this->erros = [];

        $alvo = $this->getAlvo($operadorId, $dados->getId());

        if (!$alvo) {
            $this->erros['permissao'] = 'Você não tem permissão para alterar esta conta.';

            return false;
        }

        if (!$this->usuarioService->updateUsuario($dados)) {
            $this->erros = $this->usuarioService->getErros();

            return false;
        }

        if ($removerFoto && $alvo->getFoto()) {
            $this->usuarioService->updateFoto($alvo->getId(), null);
        }

        return true;
    }

    // RF 02: o administrador define uma nova senha para a conta que ele pode gerenciar (recuperação de acesso)
    public function redefinirSenha(int $operadorId, int $alvoId, string $novaSenha, string $confirmacao): bool
    {
        $this->erros = [];

        if (!$this->getAlvo($operadorId, $alvoId)) {
            $this->erros['permissao'] = 'Você não tem permissão para alterar esta conta.';

            return false;
        }

        if (!$this->usuarioService->redefinirSenha($alvoId, $novaSenha, $confirmacao)) {
            $this->erros = $this->usuarioService->getErros();

            return false;
        }

        return true;
    }

    // RF 01 e RN 01: só o chefe cria administradores, sempre como conta nova
    public function criarAdministrador(int $operadorId, Usuario $dados): bool
    {
        $this->erros = [];

        if (!$this->eChefe($operadorId)) {
            $this->erros['permissao'] = 'Somente o administrador chefe pode criar administradores.';

            return false;
        }

        if (!$this->usuarioService->cadastrarAdministrador($dados)) {
            $this->erros = $this->usuarioService->getErros();

            return false;
        }

        return true;
    }

    public function getIndicadores(): array
    {
        $comuns = ['total' => 0, 'ativos' => 0, 'bloqueados' => 0];
        $administradores = 0;

        foreach ($this->painelRepository->contarPorPerfilEStatus() as $linha) {
            if ($linha['perfil'] === 'comum') {
                $comuns['total'] += (int) $linha['total'];
                $comuns[$linha['status'] === 'ativo' ? 'ativos' : 'bloqueados'] += (int) $linha['total'];
            } else {
                $administradores += (int) $linha['total'];
            }
        }

        $novos = $this->painelRepository->contarNovosCadastros();

        // engajamento: usuários comuns ativos por tempo desde o último acesso (até 7, de 8 a 30, mais de 30 dias)
        $limiteSete = date('Y-m-d', strtotime('-7 days'));
        $limiteTrinta = date('Y-m-d', strtotime('-30 days'));
        $faixas = $this->painelRepository->contarEngajamento($limiteSete, $limiteTrinta);
        $total = (int) $faixas['total'];

        $engajamento = ['total' => $total];
        foreach (['ativos', 'em_risco', 'inativos'] as $faixa) {
            $quantidade = (int) $faixas[$faixa];
            $engajamento[$faixa] = [
                'quantidade' => $quantidade,
                'percentual' => $total > 0 ? round($quantidade * 100 / $total, 1) : 0,
            ];
        }

        return [
            'comuns' => $comuns,
            'administradores' => $administradores,
            'novos7' => (int) $novos['ultimos7'],
            'novos30' => (int) $novos['ultimos30'],
            'engajamento' => $engajamento,
        ];
    }
}