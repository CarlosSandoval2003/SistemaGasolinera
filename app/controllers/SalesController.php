<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Sale;

class SalesController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new Sale();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) { header("Location: /psms/public/"); exit; }

        $this->render('sales/index', [
            'fuels' => $this->model->getActiveFuels(),
            'page'  => 'sales',
            'title' => 'POS | Gasolinera',
        ]);
    }

    /** AJAX: buscar NIT en SAT simulado */
    public function nitLookup() {
        header('Content-Type: application/json');
        $nit = trim($_GET['nit'] ?? '');
        if ($nit === '') { echo json_encode(['status'=>'error','msg'=>'NIT vacío']); return; }

        $sat = $this->model->findSatByNit($nit);
        if (!$sat) { echo json_encode(['status'=>'not_found']); return; }

        echo json_encode(['status'=>'ok','data'=>$sat]);
    }

    /** Modal de simulación de pago con tarjeta (solo UI) */
    public function cardModal(){
        $this->render('sales/card_modal', [], null, false);
    }

    /** Guardar venta */
   public function save() {
    $customer_id = null;
    $isNewCustomer = false;
    $newCustomerName = null;

    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json');

    try {
        if (empty($_SESSION['user_id'])) throw new \RuntimeException("No autenticado.");

        $petrol_type_id = (int)($_POST['petrol_type_id'] ?? 0);
        $gallons        = (float)($_POST['gallons'] ?? 0);
        $type           = (int)($_POST['type'] ?? 1);

        if ($petrol_type_id <= 0) throw new \InvalidArgumentException("Seleccione gasolina.");

        $price = $this->model->getSalePriceGal($petrol_type_id);
        if ($price <= 0) throw new \RuntimeException("Precio no disponible.");

        $src           = $_POST['src'] ?? 'gallons';
        $typed_amount  = (float)($_POST['amount'] ?? 0);

        if ($src === 'amount' && $typed_amount > 0) {
            $amount_cents = (int) round($typed_amount * 100, 0);
            $amount_q     = $amount_cents / 100.0;
            $gallons      = (float) number_format(($amount_q / $price), 3, '.', '');
        } else {
            if ($gallons <= 0) throw new \InvalidArgumentException("Ingrese galones > 0.");
            $amount_cents = (int) round(($gallons * $price) * 100, 0);
            $amount_q     = $amount_cents / 100.0;
            $gallons      = (float) number_format($gallons, 3, '.', '');
        }

        $total_cents = $amount_cents;
        $total_q     = $amount_q;

        $tendered = (float)($_POST['tendered_amount'] ?? 0);
        if ($type === 3) { $tendered = $total_q; }

        $change_cents = $type === 1 ? (int) round($tendered * 100) - $total_cents : 0;
        if ($type === 1 && $change_cents < 0) {
            echo json_encode(['status'=>'error','msg'=>'Pago recibido menor al total.']); return;
        }

        $cf_or_nit  = $_POST['cf_or_nit'] ?? 'CF';
        $nit_val    = trim($_POST['nit'] ?? '');
        $customer_id = null;

        if ($cf_or_nit === 'NIT') {
            if ($nit_val === '') throw new \InvalidArgumentException("Ingrese NIT.");
            $sat = $this->model->findSatByNit($nit_val);
            if (!$sat) throw new \RuntimeException("NIT no encontrado en SAT simulado.");
            $res = $this->model->ensureCustomerFromSAT($sat);
            $customer_id     = (int)$res['id'];
            $isNewCustomer   = (bool)$res['is_new'];
            $newCustomerName = $res['fullname'] ?? null;
        }

        $price_str   = sprintf('%.4f', $price);
        $gals_str    = sprintf('%.3f', $gallons);
        $amount_str  = sprintf('%.2f', $amount_q);
        $total_str   = sprintf('%.2f', $total_q);
        $tender_str  = sprintf('%.2f', $tendered);
        $change_str  = sprintf('%.2f', $change_cents / 100);

        [$id, $rcpt] = $this->model->saveTransaction([
            'customer_id'     => $customer_id ? (int)$customer_id : null,
            'petrol_type_id'  => $petrol_type_id,
            'price'           => $price_str,
            'gallons'         => $gals_str,
            'amount'          => $amount_str,
            'total'           => $total_str,
            'tendered_amount' => $tender_str,
            'change'          => $change_str,
            'type'            => $type,
        ], (int)$_SESSION['user_id']);

        echo json_encode([
            'status'            => 'success',
            'msg'               => 'Venta guardada',
            'transaction_id'    => $id,
            'new_customer'      => $isNewCustomer,
            'customer_fullname' => $newCustomerName
        ]);
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'msg' => 'ERROR: '.$e->getMessage()]);
    }
}


    public function receipt($id) {
        $data = $this->model->getReceipt((int)$id);
        $this->render('sales/receipt', ['tx' => $data], null, false);
    }
}
