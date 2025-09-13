<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\PetrolType;

class PetrolTypeController extends Controller
{
    private $model;

    public function __construct() {
        $this->model = new PetrolType();
    }

    public function index() {
        $types = $this->model->all();
        $this->render('maintenance/index', [
            'types' => $types,
            'page'  => 'maintenance',
            'title' => 'Maintenance | Petrol Types'
        ]);
    }

    public function manage() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $type = $id ? $this->model->find($id) : null;
        $this->render('maintenance/manage', ['type' => $type], null, false);
    }

    public function viewPetrol() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $type = $id ? $this->model->find($id) : null;
        $this->render('maintenance/view', ['type' => $type], null, false);
    }

    public function save() {
        header('Content-Type: application/json');

        $id     = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $name   = trim($_POST['name'] ?? '');
        $price  = (float)($_POST['price'] ?? 0);
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

        if ($name === '' || $price <= 0) {
            echo json_encode(['status'=>'error','msg'=>'Name y Price son obligatorios']); return;
        }

        $ok = $this->model->save([
            'id'     => $id,
            'name'   => $name,
            'price'  => $price,
            'status' => $status
        ]);

        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Petrol type guardado correctamente.']
            : ['status'=>'error','msg'=>'No se pudo guardar.']);
    }

    public function delete() {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }

        $ok = $this->model->delete($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Petrol type eliminado.']
            : ['status'=>'error','msg'=>'No se pudo eliminar.']);
    }
}
