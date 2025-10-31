<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dashboard;

class HomeController extends Controller {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $model = new Dashboard();

        $todayDate   = date('Y-m-d');
        $todaySales  = (float)$model->getTodaySales();

        $data = [
            'today_sales'     => $todaySales,
            'today_date'      => $todayDate,
            'containerStocks' => $model->getContainerStocks(),   // contenedores activos con stock/capacidad/mínimo
            'salesLastDays'   => $model->getSalesLastNDays(7),   // últimos 7 días
            'salesFuelToday'  => $model->getSalesByFuelToday(),  // ventas de hoy por combustible
            'page'            => 'home',
            'title'           => 'Inicio | Gasolinera'
        ];

        $this->render('home/index', $data, $data['title']);
    }
}
