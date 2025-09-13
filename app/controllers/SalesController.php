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
            'fuels'     => $this->model->getActiveFuels(),
            'customers' => $this->model->getActiveCustomers(),
            'page'      => 'sales',
            'title'     => 'POS | Petrol Station',
        ]);
    }

    public function save() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        try {
            // Validaciones básicas
            $type = (int)($_POST['type'] ?? 1);
            $total = (float)($_POST['total'] ?? 0);
            $tendered = (float)($_POST['tendered_amount'] ?? 0);

            if ($type === 1 && $tendered < $total) {
                echo json_encode(['status' => 'error', 'msg' => 'Tendered Amount is invalid.']); return;
            }

            [$id, $rcpt] = $this->model->saveTransaction([
                'customer_id'     => (int)($_POST['customer_id'] ?? 0),
                'petrol_type_id'  => (int)($_POST['petrol_type_id'] ?? 0),
                'price'           => (float)($_POST['price'] ?? 0),
                'liter'           => (float)($_POST['liter'] ?? 0),
                'amount'          => (float)($_POST['amount'] ?? 0),
                'discount'        => (float)($_POST['discount'] ?? 0),
                'total'           => $total,
                'tendered_amount' => $tendered,
                'change'          => (float)($_POST['change'] ?? 0),
                'type'            => $type,
            ], (int)$_SESSION['user_id']);

            // ↓ Nuevo: Descontar del contenedor por defecto
$petrol_type_id = (int)($_POST['petrol_type_id'] ?? 0);
$liter          = (float)($_POST['liter'] ?? 0);

$containerModel = new \App\Models\Container();
$inventoryModel = new \App\Models\Inventory();

$defaultContainer = $containerModel->defaultForType($petrol_type_id);
if (!$defaultContainer) {
    throw new \RuntimeException("No hay contenedor por defecto para este combustible.");
}

$inventoryModel->out(
    $defaultContainer['container_id'],
    $petrol_type_id,
    $liter,
    "Venta recibo #{$rcpt}",
    $id,
    $_SESSION['user_id'] ?? null
);


            echo json_encode([
                'status' => 'success',
                'msg'    => 'Transaction saved',
                'transaction_id' => $id
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'msg' => 'ERROR: '.$e->getMessage()]);
        }
    }

    public function receipt($id) {
        $data = $this->model->getReceipt((int)$id);
        $this->render('sales/receipt', ['tx' => $data], null, false); // sin layout (modal)
    }
}
