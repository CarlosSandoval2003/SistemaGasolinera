<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

class SupplierController extends Controller
{
    private Supplier $model;
    public function __construct(){ $this->model = new Supplier(); }

    public function index(){
        // Filtros opcionales desde la URL (para persistencia por servidor)
        $q      = isset($_GET['q']) ? trim($_GET['q']) : '';
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;

        $this->render('suppliers/index', [
            'suppliers' => $this->model->listFiltered($q, $status),
            'q'         => $q,
            'status'    => $status,
            'page'      => 'suppliers',
            'title'     => 'Proveedores'
        ]);
    }

    public function manage($id=null){
        // 👇 Fallback cuando el router envía ?id= en la URL
        if (!$id && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        }
        $sup = $id ? $this->model->get((int)$id) : null;
        $this->render('suppliers/manage', ['supplier'=>$sup], null, false);
    }

    public function save(){
        header('Content-Type: application/json');
        $id   = $_POST['id'] ?? null;

        // Si no mandan código, generar
        $code = trim($_POST['code'] ?? '');
        if ($code === '') $code = $this->generateCode();

        // Sanitizar y limitar
        $name    = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status  = (int)($_POST['status'] ?? 1);

        if ($name === '') { echo json_encode(['status'=>'error','msg'=>'El nombre es requerido.']); return; }
        if (strlen($name) > 120) { echo json_encode(['status'=>'error','msg'=>'Nombre demasiado largo (máx 120).']); return; }

        // ✅ SOLO 8 dígitos para contacto
        if ($contact !== '' && !preg_match('/^\d{8}$/', $contact)) {
            echo json_encode(['status'=>'error','msg'=>'El contacto debe ser un número de 8 dígitos.']); return;
        }

        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120)) {
            echo json_encode(['status'=>'error','msg'=>'Email inválido o demasiado largo (máx 120).']); return;
        }
        if (strlen($address) > 200) {
            echo json_encode(['status'=>'error','msg'=>'Dirección demasiado larga (máx 200).']); return;
        }

        $data = [
            'supplier_id' => $id,
            'code'   => $code,
            'name'   => $name,
            'contact'=> $contact,
            'email'  => $email,
            'address'=> $address,
            'status' => $status,
        ];

        try{
            $ok = $this->model->save($data);
            echo json_encode($ok ? ['status'=>'success','msg'=>'Proveedor guardado correctamente.']
                                 : ['status'=>'error','msg'=>'No se pudo guardar el proveedor.']);
        }catch(\PDOException $e){
            // Si falló por código duplicado, reintenta 1 vez
            if (strpos($e->getMessage(), 'uq_supplier_code') !== false && empty($id)) {
                $data['code'] = $this->generateCode();
                try {
                    $ok = $this->model->save($data);
                    echo json_encode($ok ? ['status'=>'success','msg'=>'Proveedor guardado correctamente.']
                                         : ['status'=>'error','msg'=>'No se pudo guardar el proveedor.']);
                } catch(\Throwable $e2){
                    echo json_encode(['status'=>'error','msg'=>'Error al guardar: '.$e2->getMessage()]);
                }
            } else {
                echo json_encode(['status'=>'error','msg'=>'Error al guardar: '.$e->getMessage()]);
            }
        }
    }

    private function generateCode(): string {
        // Ejemplo: SUP-202510-AB12
        return 'SUP-'.date('Ym').'-'.strtoupper(substr(bin2hex(random_bytes(2)),0,4));
    }

    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        $ok = $this->model->delete($id);
        echo json_encode($ok ? ['status'=>'success','msg'=>'Proveedor eliminado.']
                             : ['status'=>'error','msg'=>'No se pudo eliminar']);
    }
}
