<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesReport;

class SalesReportController extends Controller {
    private SalesReport $model;

    public function __construct(){
        $this->model = new SalesReport();
    }

    public function index(){
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ✅ Defaults: desde = hoy - 7 días, hasta = hoy
        $dfrom   = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $dto     = $_GET['date_to']   ?? date('Y-m-d');

        $nit     = trim($_GET['nit'] ?? '');
        $receipt = trim($_GET['receipt'] ?? '');

        $date_error = null;
        if ($dfrom && $dto && $dfrom > $dto) {
            $date_error = 'Rango de fechas inválido.';
        }

        $rows = $date_error ? [] : $this->model->getTransactions($dfrom, $dto, null, $nit, $receipt);

        $this->render('salesReport/index', [
            'rows'       => $rows,
            'dfrom'      => $dfrom,
            'dto'        => $dto,
            'date_error' => $date_error,
            'filters'    => ['nit' => $nit, 'receipt' => $receipt],
            'page'       => 'sales_report',
            'title'      => 'Reporte de Ventas'
        ]);
    }
}
