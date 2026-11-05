<?php
namespace app\database;

use Exception;
use PDO;
use app\database\DatabaseInitializer;

class ConnectionFactory {

    private static ?PDO $connection = null;

    public static function getConnection(){

        if (self::$connection == null) {

            try {

                //$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME ;
                //self::$connection = self::createConnection($dsn);

                //$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME ;
                //self::$connection = self::createConnection($dsn);

                $url = parse_url(getenv('DATABASE_URL'));

                $dsn = "mysql:host={$url['host']};port={$url['port']};dbname=" . ltrim($url['path'], '/');
                
                self::$connection = new PDO(
                    $dsn,
                    $url['user'],
                    $url['pass']
                );

                
                (new DatabaseInitializer())->migrate(self::$connection);

            } catch(Exception $e){

                print($e->getMessage());
                exit;

                // RNF 04: o detalhe técnico vai só para o log, nunca para a tela do usuário
                // error_log("Não foi possível acessar o banco " . DB_NAME . ": " . $e->getMessage() . " (tentando inicializar)");

                // $dsn = "mysql:host=" . DB_HOST;
                // self::$connection = self::createConnection($dsn);

                // $databaseInit = new DatabaseInitializer();
                // $databaseInit->init(self::$connection);

                // // Re-attempt after initialization
                // $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME ;
                // self::$connection = self::createConnection($dsn);

            }
        }

        return self::$connection;

    }

    private static function createConnection(string $dsn){
        $connection = new PDO($dsn, DB_USER, DB_PASS);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // o MySQL usa o mesmo fuso do PHP (America/Sao_Paulo, no Config.php): sem isso, com o banco em UTC,
        // o CURRENT_DATE já é "amanhã" a partir das 21h
        $connection->exec("SET time_zone = '" . date('P') . "'");

        return $connection;
    }
}
