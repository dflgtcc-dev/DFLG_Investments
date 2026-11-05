<?php

namespace app\helpers;

class UploadImagem
{
    private const TAMANHO_MAXIMO = 2097152; // 2 MB
    private const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private string $pasta;
    private string $erro = '';

    public function __construct(string $pasta)
    {
        $this->pasta = $pasta;
    }

    // true quando o usuário escolheu um arquivo no formulário
    public function enviado(?array $arquivo): bool
    {
        return !empty($arquivo) && $arquivo['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function validar(array $arquivo): bool
    {
        if (
            $arquivo['error'] === UPLOAD_ERR_INI_SIZE
            || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE
            || $arquivo['size'] > self::TAMANHO_MAXIMO
        ) {
            $this->erro = "A foto deve ter no máximo 2 MB";

            return false;
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $this->erro = "Não foi possível enviar a foto, tente novamente";

            return false;
        }

        // confere o conteúdo do arquivo, sem confiar no nome nem no tipo informado pelo navegador
        $info = getimagesize($arquivo['tmp_name']);

        if (!$info || !isset(self::TIPOS_PERMITIDOS[$info['mime']])) {
            $this->erro = "Envie uma imagem JPG, PNG ou WEBP";

            return false;
        }

        return true;
    }

    // devolve o nome do arquivo salvo ou false; o nome é gerado aqui, nunca o nome original
    public function salvar(array $arquivo, string $nomeBase)
    {
        if (!$this->validar($arquivo)) {
            return false;
        }

        $info = getimagesize($arquivo['tmp_name']);
        $nome = $nomeBase . '_' . time() . '.' . self::TIPOS_PERMITIDOS[$info['mime']];

        if (!is_dir($this->pasta)) {
            mkdir($this->pasta, 0775, true);
        }

        if (!move_uploaded_file($arquivo['tmp_name'], $this->pasta . '/' . $nome)) {
            $this->erro = "Não foi possível salvar a foto";

            return false;
        }

        return $nome;
    }

    public function remover(?string $nome): void
    {
        if (!$nome) {
            return;
        }

        // basename impede que um nome com "../" apague arquivos fora da pasta
        $caminho = $this->pasta . '/' . basename($nome);

        if (is_file($caminho)) {
            unlink($caminho);
        }
    }

    public function getErro(): string
    {
        return $this->erro;
    }
}