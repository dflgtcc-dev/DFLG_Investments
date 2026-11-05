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
define('DB_HOST', 'cmw3v6p4h02ls01mythhikrul-addon-mysql');
define('DB_NAME', 'ethiodeploy');

define('DB_USER', 'ethiodeploy');
define('DB_PASS', '7042da1c5222a0cc4b8d83b18a11e39fac16a50e4bffcff');


