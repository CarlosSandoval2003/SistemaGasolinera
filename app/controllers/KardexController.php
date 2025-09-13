<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Kardex;

class KardexController extends Controller
{
    private Kardex $model;
    public function __construct(){ $this->model = new Kardex(); }

    public function index(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        if (($_SESSION['type'] ?? 0) != 1) { header("Location: index.php?url=home/index"); exit; }

        // defaults de fecha (últimos 7 días)
        $df = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $dt = $_GET['date_to']   ?? date('Y-m-d');

        $filters = [
            'date_from'      => $df,
            'date_to'        => $dt,
            'container_id'   => isset($_GET['container_id']) ? (int)$_GET['container_id'] : null,
            'petrol_type_id' => isset($_GET['petrol_type_id']) ? (int)$_GET['petrol_type_id'] : null,
            'kind'           => $_GET['kind']   ?? null,
            'user_id'        => isset($_GET['user_id']) ? (int)$_GET['user_id'] : null,
        ];

        $rows = $this->model->list($filters);
        $open = $this->model->openingBalance($filters['container_id'], $filters['date_from']);

        $this->render('kardex/index', [
            'rows'     => $rows,
            'open_bal' => $open,
            'filters'  => $filters,
            'containers' => $this->model->containers(),
            'types'      => $this->model->petrolTypes(),
            'users'      => $this->model->users(),
            'page'       => 'kardex',
            'title'      => 'Kardex'
        ]);
    }

    /** Export CSV usando los mismos filtros del index */
    public function export(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        if (($_SESSION['type'] ?? 0) != 1) { header("Location: index.php?url=home/index"); exit; }

        $filters = [
            'date_from'      => $_GET['date_from'] ?? null,
            'date_to'        => $_GET['date_to']   ?? null,
            'container_id'   => isset($_GET['container_id']) ? (int)$_GET['container_id'] : null,
            'petrol_type_id' => isset($_GET['petrol_type_id']) ? (int)$_GET['petrol_type_id'] : null,
            'kind'           => $_GET['kind']   ?? null,
            'user_id'        => isset($_GET['user_id']) ? (int)$_GET['user_id'] : null,
        ];

        $rows = $this->model->list($filters);
        $open = $this->model->openingBalance($filters['container_id'], $filters['date_from']);

        $filename = 'kardex_'.date('Ymd_His').'.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $out = fopen('php://output','w');
        // BOM para Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        $withSaldo = !empty($filters['container_id']);
        $headers = ['Fecha','Contenedor','Combustible','Movimiento','Entrada (L)','Salida (L)','Delta (L)'];
        if ($withSaldo) $headers[] = 'Saldo (L)';
        $headers = array_merge($headers, ['Nota','Usuario','Ref']);
        fputcsv($out, $headers);

        $saldo = $withSaldo ? $open : 0;
        if ($withSaldo){
            fputcsv($out, ['*Saldo inicial*','','','', '', '', '', number_format($open,2), '', '', '']);
        }

        foreach($rows as $r){
            $in  = 0.0; $outL = 0.0;
            if ($r['delta'] >= 0) $in = $r['qty_liters']; else $outL = abs($r['qty_liters']);
            if ($withSaldo) $saldo += (float)$r['delta'];

            $line = [
                $r['created_at'],
                $r['container_name'],
                $r['petrol_name'],
                $r['kind'],
                number_format($in,2),
                number_format($outL,2),
                number_format($r['delta'],2),
            ];
            if ($withSaldo) $line[] = number_format($saldo,2);
            $line = array_merge($line, [
                $r['note'] ?? '',
                $r['user_fullname'] ?? '',
                $r['ref_id'] ?? ''
            ]);
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }
}
