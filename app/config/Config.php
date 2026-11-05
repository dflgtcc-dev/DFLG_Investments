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
define('URL_BASE', 'https://dflg-investments.ethiodeploy.app/');

//Configurações do Banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_dflg_investments');

define('DB_USER', 'root');
define('DB_PASS', '');
