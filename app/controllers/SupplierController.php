<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

class SupplierController extends Controller
{
    private Supplier $model;
    public function __construct(){ $this->model = new Supplier(); }

    public function index(){
        $this->render('suppliers/index', [
            'suppliers' => $this->model->all(),
            'page' => 'suppliers',
            'title' => 'Suppliers'
        ]);
    }

    public function manage($id=null){
        $sup = $id ? $this->model->get((int)$id) : null;
        $this->render('suppliers/manage', ['supplier'=>$sup], null, false);
    }

public function save(){
    header('Content-Type: application/json');
    $id = $_POST['id'] ?? null;

    $code = trim($_POST['code'] ?? '');
    if ($code === '') {
        $code = $this->generateCode(); // <-- nuevo
    }

    $data = [
        'supplier_id' => $id,
        'code'   => $code,
        'name'   => trim($_POST['name'] ?? ''),
        'contact'=> trim($_POST['contact'] ?? ''),
        'email'  => trim($_POST['email'] ?? ''),
        'address'=> trim($_POST['address'] ?? ''),
        'status' => (int)($_POST['status'] ?? 1),
    ];
    if($data['name'] === ''){
        echo json_encode(['status'=>'error','msg'=>'Nombre es requerido']); return;
    }

    try{
        $ok = $this->model->save($data);
        echo json_encode($ok ? ['status'=>'success','msg'=>'Proveedor guardado']
                             : ['status'=>'error','msg'=>'No se pudo guardar']);
    }catch(\PDOException $e){
        // Si falló por código duplicado, reintenta 1 vez
        if (strpos($e->getMessage(), 'uq_supplier_code') !== false && empty($id)) {
            $data['code'] = $this->generateCode();
            try {
                $ok = $this->model->save($data);
                echo json_encode($ok ? ['status'=>'success','msg'=>'Proveedor guardado']
                                     : ['status'=>'error','msg'=>'No se pudo guardar']);
            } catch(\Throwable $e2){
                echo json_encode(['status'=>'error','msg'=>'Error al guardar: '.$e2->getMessage()]);
            }
        } else {
            echo json_encode(['status'=>'error','msg'=>'Error al guardar: '.$e->getMessage()]);
        }
    }
}

private function generateCode(): string {
    // Ejemplo SUP-202508-00AF (a tu gusto)
    return 'SUP-'.date('Ym').'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));
}


    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        $ok = $this->model->delete($id);
        echo json_encode($ok ? ['status'=>'success'] : ['status'=>'error','msg'=>'No se pudo eliminar']);
    }
}
