<?php

namespace app\services;

use app\models\Usuario;
use app\models\UsuarioAdministrador;
use app\repositories\UsuarioAdministradorRepository;
use app\repositories\UsuarioRepository;
use app\repositories\UsuarioComumRepository;
use DateTimeImmutable;

class UsuarioService
{
    private const IDADE_MINIMA = 16;
    private const LIMITE_NOME = 100;
    private const LIMITE_NICKNAME = 25;
    private const LIMITE_EMAIL = 100;
    // bcrypt ignora tudo depois de 72 bytes, por isso esse é o máximo
    public const SENHA_MINIMO = 8;
    public const SENHA_MAXIMO_BYTES = 72;

    private UsuarioRepository $repository;
    private UsuarioComumRepository $usuarioComumRepository;
    private UsuarioAdministradorRepository $administradorRepository;
    private array $erros = [];

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
        $this->usuarioComumRepository = new UsuarioComumRepository();
        $this->administradorRepository = new UsuarioAdministradorRepository();
    }

    public function cadastrar(Usuario $usuario): bool
    {
        if (!$this->validar($usuario)) {
            return false;
        }

        if (!$this->repository->saveUsuario($usuario)) {
            return false;
        }

        $usuarioCriado = $this->repository->getUsuarioByEmail($usuario->getEmail());
        $this->usuarioComumRepository->criarParaUsuario($usuarioCriado->getId());

        return true;
    }

    // RF 01: cria uma conta de administrador comum (nivel 2), sem os dados de gamificação do usuário comum
    public function cadastrarAdministrador(Usuario $usuario): bool
    {
        if (!$this->validar($usuario)) {
            return false;
        }

        if (!$this->repository->saveUsuario($usuario)) {
            $this->erros['geral'] = 'Não foi possível criar o administrador.';

            return false;
        }

        $criado = $this->repository->getUsuarioByEmail($usuario->getEmail());

        if (!$criado || !$this->administradorRepository->criarParaUsuario($criado->getId(), UsuarioAdministrador::NIVEL_COMUM)) {
            // desfaz a criação da conta para não sobrar um administrador sem registro de permissão
            if ($criado) {
                $this->repository->deleteUsuario($criado->getId());
            }

            $this->erros['geral'] = 'Não foi possível criar o administrador.';

            return false;
        }

        return true;
    }

    public function getUsuarioById(int $id)
    {
        return $this->repository->getUsuarioById($id);
    }

    public function updateUsuario(Usuario $usuario): bool
    {
        $atual = $this->repository->getUsuarioById($usuario->getId());

        if (!$this->validar($usuario, $atual ?: null)) {
            return false;
        }

        return $this->repository->updateUsuario($usuario);
    }

    public function updateFoto(int $id, ?string $foto): bool
    {
        return $this->repository->updateFoto($id, $foto);
    }

    public function updateTema(int $id, string $tema): bool
    {
        return $this->repository->updateTema($id, $tema);
    }

    public function alterarSenha(int $id, string $senhaAtual, string $novaSenha, string $confirmacao): bool
    {
        $this->erros = [];

        $usuario = $this->repository->getUsuarioById($id);

        if (!$usuario || !password_verify($senhaAtual, $usuario->getSenha())) {
            $this->erros['senhaAtual'] = "A senha atual está incorreta";
        }

        if ($novaSenha === '') {
            $this->erros['novaSenha'] = "Informe a nova senha";
        } elseif ($erroSenha = $this->erroDeSenha($novaSenha)) {
            $this->erros['novaSenha'] = $erroSenha;
        } elseif ($novaSenha === $senhaAtual) {
            $this->erros['novaSenha'] = "A nova senha deve ser diferente da atual";
        }

        if ($novaSenha !== $confirmacao) {
            $this->erros['confirmarSenha'] = "A confirmação não confere com a nova senha";
        }

        if (!empty($this->erros)) {
            return false;
        }

        return $this->repository->updateSenha($id, password_hash($novaSenha, PASSWORD_BCRYPT));
    }

    // o administrador define uma nova senha para a conta (sem pedir a atual), como recuperação de acesso
    public function redefinirSenha(int $id, string $novaSenha, string $confirmacao): bool
    {
        $this->erros = [];

        if ($novaSenha === '') {
            $this->erros['novaSenha'] = "Informe a nova senha";
        } elseif ($erroSenha = $this->erroDeSenha($novaSenha)) {
            $this->erros['novaSenha'] = $erroSenha;
        }

        if ($novaSenha !== $confirmacao) {
            $this->erros['confirmarSenha'] = "A confirmação não confere com a nova senha";
        }

        if (!empty($this->erros)) {
            return false;
        }

        return $this->repository->updateSenha($id, password_hash($novaSenha, PASSWORD_BCRYPT));
    }

    public function deleteUsuario(int $id): bool
    {
        return $this->repository->deleteUsuario($id);
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    // $atual e o cadastro antes da edicao: e-mail e data de nascimento so sao validados quando mudam,
    // para que dados antigos nao impecam a edicao dos demais campos
    private function erroDeSenha(string $senha): ?string
    {
        if (mb_strlen($senha) < self::SENHA_MINIMO) {
            return "A senha deve ter no mínimo " . self::SENHA_MINIMO . " caracteres";
        }

        if (strlen($senha) > self::SENHA_MAXIMO_BYTES) {
            return "A senha deve ter no máximo " . self::SENHA_MAXIMO_BYTES . " caracteres";
        }

        return null;
    }

    private function validar(Usuario $usuario, ?Usuario $atual = null): bool
    {
        $this->erros = [];

        // a senha só é validada no cadastro; a troca passa por alterarSenha()
        if ($atual === null && ($erroSenha = $this->erroDeSenha($usuario->getSenha()))) {
            $this->erros['senha'] = $erroSenha;
        }

        $emailMudou = $atual === null || $atual->getEmail() !== $usuario->getEmail();
        $nascimentoMudou = $atual === null || $atual->getDataNascimento() !== $usuario->getDataNascimento();

        if (mb_strlen($usuario->getNome()) > self::LIMITE_NOME) {
            $this->erros['nome'] = "O nome deve ter no máximo " . self::LIMITE_NOME . " caracteres";
        }

        if (mb_strlen($usuario->getNickname()) > self::LIMITE_NICKNAME) {
            $this->erros['nickname'] = "O nickname deve ter no máximo " . self::LIMITE_NICKNAME . " caracteres";
        }

        if ($emailMudou) {
            if (mb_strlen($usuario->getEmail()) > self::LIMITE_EMAIL) {
                $this->erros['email'] = "O e-mail deve ter no máximo " . self::LIMITE_EMAIL . " caracteres";
            } elseif (!filter_var($usuario->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $this->erros['email'] = "Informe um e-mail válido";
            }
        }

        // telefone é opcional; quando informado, DDD + 9 dígitos (celular) ou DDD + 8 dígitos (fixo)
        $telefone = $usuario->getTelefone();

        if ($telefone !== null && !preg_match('/^[1-9]{2}(9\d{8}|[2-5]\d{7})$/', $telefone)) {
            $this->erros['telefone'] = "Informe um telefone válido com DDD, por exemplo (45) 99999-9999";
        }

        if ($nascimentoMudou) {
            $nascimento = DateTimeImmutable::createFromFormat('Y-m-d', $usuario->getDataNascimento());

            if (!$nascimento) {
                $this->erros['dataNascimento'] = "Informe uma data de nascimento válida";
            } elseif ($nascimento > new DateTimeImmutable('today')) {
                $this->erros['dataNascimento'] = "A data de nascimento não pode ser no futuro";
            } elseif ($nascimento->diff(new DateTimeImmutable('today'))->y < self::IDADE_MINIMA) {
                $this->erros['dataNascimento'] = "É preciso ter pelo menos " . self::IDADE_MINIMA . " anos";
            }
        }

        if ($emailMudou) {
            $usuarioComEmail = $this->repository->getUsuarioByEmail($usuario->getEmail());

            // na edição, o e-mail do próprio usuário não conta como duplicado
            if (!isset($this->erros['email']) && $usuarioComEmail && $usuarioComEmail->getId() !== $usuario->getId()) {
                $this->erros['email'] = "Este e-mail já está cadastrado";
            }
        }

        $usuarioComNickname = $this->repository->getUsuarioByNickname($usuario->getNickname());

        if (!isset($this->erros['nickname']) && $usuarioComNickname && $usuarioComNickname->getId() !== $usuario->getId()) {
            $this->erros['nickname'] = "Este nickname já está em uso";
        }

        return empty($this->erros);
    }
}