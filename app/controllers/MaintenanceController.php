<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Container;

class MaintenanceController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Solo admin
        if (!isset($_SESSION['user_id']) || ($_SESSION['type'] ?? 0) != 1) {
            header("Location: index.php?url=home/index"); exit;
        }

        // Petrol types los recibes del controlador que ya usabas para esta vista
        // Si ya tienes un modelo para petrol types, úsalo. Para no tocar nada más:
        // desde tu PetrolTypeController llamabas a render con $types, aquí replicamos:
        $db = (new \App\Core\Database())->getConnection();
        $types = $db->query("SELECT * FROM petrol_type_list ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        // Contenedores
        $containerModel = new Container();
        $containers = $containerModel->all();

        $this->render('maintenance/index', [
            'types'      => $types,
            'containers' => $containers,
            'page'       => 'maintenance',
            'title'      => 'Maintenance'
        ]);
    }
}
