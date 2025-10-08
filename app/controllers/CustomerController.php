<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;

class CustomerController extends Controller
{
    private Customer $model;

    public function __construct()
    {
        $this->model = new Customer();
    }

    public function index()
    {
        $customers = $this->model->getAll();
        $this->render('customers/index', [
            'customers' => $customers,
            'page'      => 'customer',
            'title'     => 'Clientes'
        ]);
    }

    public function manage($id = null)
    {
        if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
        $customer = $id ? $this->model->getById((int)$id) : null;

        $this->render('customers/manage', [
            'customer' => $customer
        ], null, false);
    }

    public function save()
    {
        header('Content-Type: application/json');

        $id        = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $nit       = strtoupper(trim($_POST['nit'] ?? ''));
        $status    = (int)($_POST['status'] ?? 1);

        if ($firstName === '' || $lastName === '' || $nit === '') {
            echo json_encode(['status'=>'error','msg'=>'Completa Nombres, Apellidos y NIT.']);
            return;
        }

        if (!preg_match('/^[A-Z0-9-]{3,30}$/', $nit)) {
            echo json_encode(['status'=>'error','msg'=>'NIT inválido. Usa letras/números y guiones.']);
            return;
        }

        $payload = [
            'id'            => $id,
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'fullname'      => trim($firstName.' '.$lastName),
            'customer_code' => $nit,   // NIT vive aquí (compatible con POS)
            'status'        => $status,
        ];

        $ok = $this->model->save($payload);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Cliente guardado correctamente.']
            : ['status'=>'error','msg'=>'No se pudo guardar el cliente.']
        );
    }

    public function viewCustomer($id)
    {
        $customer = $this->model->getById((int)$id);
        $this->render('customers/view', ['customer' => $customer], null, false);
    }

    public function delete()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status'=>'error','msg'=>'ID no proporcionado']);
            return;
        }
        $ok = $this->model->delete($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Cliente eliminado']
            : ['status'=>'error','msg'=>'Error al eliminar el cliente']
        );
    }
}
