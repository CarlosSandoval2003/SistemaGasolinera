<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesReport;

class SalesReportController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new SalesReport();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $dfrom = $_GET['date_from'] ?? date("Y-m-d", strtotime("-1 week"));
        $dto   = $_GET['date_to']   ?? date("Y-m-d");

        // Validación de rango de fechas (servidor)
        $date_error = null;
        $tsFrom = strtotime($dfrom);
        $tsTo   = strtotime($dto);
        if ($tsFrom === false || $tsTo === false || $tsFrom > $tsTo) {
            $date_error = 'Rango de fechas inválido. Verifica "Desde" y "Hasta".';
        }

        // Si no es admin (type != 1), limitar por usuario
        $onlyUserId = (isset($_SESSION['type']) && (int)$_SESSION['type'] !== 1)
            ? (int)($_SESSION['user_id'] ?? 0)
            : null;

        $rows = [];
        if ($date_error === null) {
            $rows = $this->model->getTransactions($dfrom, $dto, $onlyUserId);
        }

        $this->render('sales_report/index', [
            'rows'       => $rows,
            'dfrom'      => $dfrom,
            'dto'        => $dto,
            'date_error' => $date_error,
            'page'       => 'sales_report',
            'title'      => 'Reporte de Ventas | Gasolinera'
        ]);
    }
}
