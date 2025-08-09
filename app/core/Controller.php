<?php
namespace App\Core;

class Controller {
    public function model($model) {
        require_once "../app/models/$model.php";
        return new $model();
    }

    public function view($view, $data = []) {
        extract($data);
        require_once "../app/views/$view.php";
    }

    public function render($view, $data = [], $title = null, $layout = true) {
    extract($data);

    ob_start();

    $viewPath = "../app/views/{$view}.view.php";

    if (!file_exists($viewPath)) {
        die("Vista no encontrada: $viewPath");
    }

    require $viewPath;
    $content = ob_get_clean();

    if ($layout) {
        require "../app/views/layout.php";
    } else {
        echo $content; 
    }
}

}
