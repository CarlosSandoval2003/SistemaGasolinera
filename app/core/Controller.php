<?php
namespace App\Core;

class Controller {
    public function model($model) {
        $path = APP_PATH . "/models/{$model}.php";
        if (!file_exists($path)) {
            die("Modelo no encontrado: {$path}");
        }
        require_once $path;
        return new $model();
    }

    public function view($view, $data = []) {
        extract($data);
        $path = APP_PATH . "/views/{$view}.php";
        if (!file_exists($path)) {
            die("Vista no encontrada: {$path}");
        }
        require_once $path;
    }

    public function render($view, $data = [], $title = null, $layout = true) {
        extract($data);
        ob_start();

        $viewPath = APP_PATH . "/views/{$view}.view.php";
        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$viewPath}");
        }

        require $viewPath;
        $content = ob_get_clean();

        if ($layout) {
            $layoutPath = APP_PATH . "/views/layout.php";
            if (!file_exists($layoutPath)) {
                die("Layout no encontrado: {$layoutPath}");
            }
            require $layoutPath;
        } else {
            echo $content;
        }
    }
}
