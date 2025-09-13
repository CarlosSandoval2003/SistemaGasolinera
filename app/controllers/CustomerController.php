<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;

class CustomerController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new Customer();
    }


public function index() {
    $model = new Customer();
    $customers = $model->getAll();

    $this->render('customers/index', [
        'customers' => $customers,
        'page' => 'customer', 
        'title' => 'Clientes | Petrol Station'
    ]);
}



public function manage($id = null)
{
    if (!$id && isset($_GET['id'])) {
        $id = $_GET['id'];
    }

    $customer = $id ? $this->model->getById($id) : null;

    $this->render('customers/manage', ['customer' => $customer], null, false);
}



    public function save()
    {
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;


        $customer_code = strtoupper(uniqid("CST-"));

        $data = [
            'id' => $id,
            'fullname' => $_POST['fullname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'contact' => $_POST['contact'] ?? '',
            'address' => $_POST['address'] ?? '',
            'status' => $_POST['status'] ?? 1,
        ];

        if (!$id) {
            $data['customer_code'] = $customer_code;
        }

        $success = $this->model->save($data);

        if ($success) {
            echo json_encode(['status' => 'success', 'msg' => 'Cliente guardado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'No se pudo guardar el cliente.']);
        }
    }

   
    public function viewCustomer($id)
    {
        $customer = $this->model->getById($id);
        $this->render('customers/view', ['customer' => $customer], null, false);
    }

   
    public function delete()
    {
        header('Content-Type: application/json');

        if (!isset($_POST['id'])) {
            echo json_encode(['status' => 'error', 'msg' => 'ID no proporcionado']);
            return;
        }

        $deleted = $this->model->delete($_POST['id']);

        if ($deleted) {
            echo json_encode(['status' => 'success', 'msg' => 'Cliente eliminado exitosamente']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar el cliente']);
        }
    }
}
