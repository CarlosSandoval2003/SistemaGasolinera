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
            'title' => 'Mantenimiento | Gasolina'
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

        $id   = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');

        // Nuevos campos por GALÓN
        $purchase_price_gal = isset($_POST['purchase_price_gal']) ? (float)$_POST['purchase_price_gal'] : 0;
        $sale_price_gal     = isset($_POST['sale_price_gal']) ? (float)$_POST['sale_price_gal'] : 0;
        $status             = isset($_POST['status']) ? (int)$_POST['status'] : 0;

        if ($name === '') {
            echo json_encode(['status'=>'error','msg'=>'El nombre es obligatorio.']); return;
        }
        if ($purchase_price_gal <= 0) {
            echo json_encode(['status'=>'error','msg'=>'El precio de compra por galón debe ser mayor a 0.']); return;
        }
        if ($sale_price_gal <= 0) {
            echo json_encode(['status'=>'error','msg'=>'El precio de venta por galón debe ser mayor a 0.']); return;
        }

        $ok = $this->model->save([
            'id'                 => $id,
            'name'               => $name,
            'purchase_price_gal' => $purchase_price_gal,
            'sale_price_gal'     => $sale_price_gal,
            'status'             => $status
        ]);

        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Gasolina guardada correctamente.']
            : ['status'=>'error','msg'=>'No se pudo guardar.']);
    }

    public function delete() {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }

        $ok = $this->model->delete($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Gasolina eliminada.']
            : ['status'=>'error','msg'=>'No se pudo eliminar.']);
    }
}
