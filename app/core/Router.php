<?php

namespace app\core;

use PDOException;

class Router
{
    // controllers que o administrador pode acessar; as telas financeiras e de gamificação são só do usuário comum
    private const CONTROLLERS_DO_ADMINISTRADOR = ['AdminController', 'PerfilController', 'AutenticacaoController'];

    private array $routes = [];

    public function get($route, $action){

        $this->routes[] = [
            'method' => 'get',
            'route' => $route,
            'action' => $action
        ];
    }

    public function post($route, $action){

        $this->routes[] = [
            'method' => 'post',
            'route' => $route,
            'action' => $action
        ];
    }


    public function run()
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove a pasta do projeto (caso esteja rodando em subpasta no Apache)
        $scriptDir = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));

        if ($scriptDir !== '/') {
            $uri = str_replace($scriptDir, '', $uri);
        }

        $uri = str_replace('/public', '', $uri);

        // Garante que a URI não fique vazia e comece com "/"
        if (empty($uri)) {
            $uri = '/';
        }
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {

            if ($route['route'] == $uri && $route['method'] == $method) {

                return $this->dispatch($route);
            }
        }

        http_response_code(404);
        exit('Rota não encontrada');
    }

    public function dispatch($route){

        list($controller, $method) = explode('@', $route['action']);

        $this->restringirAdministrador($controller);

        $controllerClass = "app\\controllers\\$controller";

        if (!class_exists($controllerClass)) {
            print "Controller $controller não encontrado";
            die;
        }

        if (!method_exists($controllerClass, $method)) {
            print "Método $method não encontrado em $controllerClass";
            die;
        }

        // RNF 04: guarda a saída para que um erro de banco não deixe a página pela metade
        ob_start();

        try {
            $controller = new $controllerClass;
            $controller->$method();
        } catch (PDOException $e) {
            ob_end_clean();

            // o detalhe técnico vai só para o log, o usuário vê uma mensagem clara
            error_log("Erro de banco de dados: " . $e->getMessage());
            http_response_code(503);
            require __DIR__ . '/../views/erro/erro_banco.php';

            return;
        }

        ob_end_flush();

    }

    // o administrador usa só o painel e o próprio perfil; qualquer outra tela o leva de volta ao painel
    private function restringirAdministrador(string $controller): void
    {
        $usuario = $_SESSION['usuario_logado'] ?? null;

        if ($usuario && $usuario->eAdmin() && !in_array($controller, self::CONTROLLERS_DO_ADMINISTRADOR, true)) {
            header('Location: ' . URL_BASE . '/admin');
            exit;
        }
    }

}