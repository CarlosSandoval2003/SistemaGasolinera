<?php
namespace App\Core;

class Router {
    public function dispatch() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth/login';
        $url = explode('/', $url);

        $controllerName = ucfirst($url[0]) . 'Controller';
        $method = isset($url[1]) ? $url[1] : 'login';

        // RUTA ABSOLUTA
        $controllerFile = APP_PATH . "/controllers/{$controllerName}.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = "App\\Controllers\\{$controllerName}";
            $controller = new $controllerClass();

            $httpMethod = $_SERVER['REQUEST_METHOD'];

            if ($method === 'login') {
                if ($httpMethod === 'GET' && method_exists($controller, 'showLoginForm')) {
                    $controller->showLoginForm();
                    return;
                }
                if ($httpMethod === 'POST' && method_exists($controller, 'login')) {
                    $controller->login();
                    return;
                }
            }

            if (method_exists($controller, $method)) {
                unset($url[0], $url[1]);
                $params = $url ? array_values($url) : [];
                call_user_func_array([$controller, $method], $params);
            } else {
                echo "Método '{$method}' no encontrado en {$controllerName}.";
            }
        } else {
            echo "Controlador '{$controllerName}' no encontrado. Buscado en: {$controllerFile}";
        }
    }
}
