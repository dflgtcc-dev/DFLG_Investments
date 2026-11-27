<?php

//Configuração do ambiente
define('DEV_ENVIRONMENT', false); // true para ambiente de desenvolvimento, false para produção

if (DEV_ENVIRONMENT == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Configuração do Sistema
define('APP_NAME', 'DFLG Investments');
date_default_timezone_set('America/Sao_Paulo');
define('URL_BASE', 'https://dflg-investments.ethiodeploy.app');

// URL de um arquivo de public/assets com versão (?v=) que muda quando o arquivo muda.
// Evita que o navegador ou o cache da hospedagem sigam entregando CSS/JS antigo depois do deploy.
function asset(string $caminho): string
{
    $arquivo = __DIR__ . '/../../public/assets/' . $caminho;
    $versao = is_file($arquivo) ? substr(md5_file($arquivo), 0, 10) : '0';

    return URL_BASE . '/assets/' . $caminho . '?v=' . $versao;
}

//Configurações do Banco de dados
define('DB_HOST', '');
define('DB_NAME', '');

define('DB_USER', '');
define('DB_PASS', '');


