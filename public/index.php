<?php

require_once '../app/core/Router.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/config/config.php';


spl_autoload_register(function ($class) {
    $file = '../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});


use App\Core\Router;

$router = new Router();
$router->dispatch();
