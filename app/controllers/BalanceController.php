<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Balance;

class BalanceController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new Balance();
    }

    public function index()
    {
        $customers = $this->model->getAllCustomersWithBalances();

        $this->render('balances/index', [
            'customers' => $customers,
            'page' => 'balances',
            'title' => 'Customer Balances | Petrol Station'
        ]);
    }

    public function viewBalance($id)
    {
        $customer = $this->model->getCustomerById($id);
        $debts = $this->model->getDebtList($id);
        $payments = $this->model->getPaymentList($id);
        $balance = $this->model->getCustomerBalance($id);
        $paid = isset($_GET['paid']);

        $this->render('balances/view', [
            'customer' => $customer,
            'debts' => $debts,
            'payments' => $payments,
            'balance' => $balance,
            'paid' => $paid
        ], null, false); // sin layout
    }

    public function manage($id)
    {
        $customer = $this->model->getCustomerById($id);
        $balance = $this->model->getCustomerBalance($id);

        $this->render('balances/manage', [
            'customer' => $customer,
            'balance' => $balance
        ], null, false); // sin layout
    }

    public function save()
    {
        header('Content-Type: application/json');

        $customer_id = $_POST['customer_id'] ?? null;
        $amount = $_POST['amount'] ?? null;

        if (!$customer_id || !$amount || $amount <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos inválidos']);
            return;
        }

        $success = $this->model->savePayment($customer_id, $amount);

        if ($success) {
            echo json_encode([
                'status' => 'success',
                'msg' => 'Pago registrado con éxito.',
                'redirect' => "index.php?url=balance/view/$customer_id&paid=true"
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar el pago.']);
        }
    }
}
