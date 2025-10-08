<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Employee;
use App\Models\Position;

class EmployeeController extends Controller {
    private Employee $model;

    public function __construct(){
        $this->model = new Employee();
    }

    public function index(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        // (Opcional) protege con rol si lo necesitas
        $pos = new Position();
        $this->render('employees/index', [
            'employees' => $this->model->all(),
            'positions' => $pos->all(),
            'page'      => 'employees',
            'title'     => 'Empleados'
        ]);
    }

public function manage($id = null){
    // Asegura obtener el ID ya sea por ruta o por querystring (?id=)
    if ($id === null) {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    } else {
        $id = (int)$id;
    }

    $pos = new Position();
    $emp = $id ? $this->model->get($id) : null;

    // Si pidieron editar y no existe, puedes mostrar algo de feedback simple
    if ($id && !$emp) {
        // Renderiza igual el form vacío, o responde error. Aquí seguimos con form.
    }

    $this->render('employees/manage', [
        'employee'  => $emp,
        'positions' => $pos->all()
    ], null, false);
}


    public function save(){
        header('Content-Type: application/json');

        // Sanitiza y arma payload
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $dpi   = trim($_POST['dpi'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addr  = trim($_POST['address'] ?? '');
        $hire  = $_POST['hire_date'] ?? null;
        $sal   = (string)($_POST['salary'] ?? '0');
        $status= (int)($_POST['status'] ?? 1);
        $posId = (int)($_POST['position_id'] ?? 0);

        // Validaciones servidor
        if ($first==='' || $last==='' || $dpi==='' || $email==='' || !$posId){
            echo json_encode(['status'=>'error','msg'=>'Completa: nombres, apellidos, DPI, email y puesto.']); return;
        }
        if (!preg_match('/^\d{13}$/', $dpi)){
            echo json_encode(['status'=>'error','msg'=>'DPI inválido: deben ser 13 dígitos.']); return;
        }
        if ($phone!=='' && !preg_match('/^\d{8}$/', $phone)){
            echo json_encode(['status'=>'error','msg'=>'Teléfono inválido: deben ser 8 dígitos.']); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode(['status'=>'error','msg'=>'Email inválido.']); return;
        }
        if (!preg_match('/^\d{1,9}(\.\d{1,2})?$/', $sal)){
            echo json_encode(['status'=>'error','msg'=>'Salario inválido.']); return;
        }

        $data = [
            'employee_id' => $_POST['id'] ?? null,
            'first_name'  => $first,
            'last_name'   => $last,
            'fullname'    => trim($first.' '.$last),
            'dpi'         => $dpi,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => $addr,
            'hire_date'   => $hire ?: null,
            'salary'      => (float)$sal,
            'status'      => $status,
            'position_id' => $posId,
        ];

        $res = $this->model->save($data);
        echo json_encode($res['ok']
            ? ['status'=>'success','msg'=>'Empleado guardado']
            : ['status'=>'error','msg'=>$res['msg'] ?? 'No se pudo guardar']
        );
    }

    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        $res = $this->model->delete($id);
        echo json_encode($res['ok'] ? ['status'=>'success'] : ['status'=>'error','msg'=>$res['msg'] ?? 'No se pudo eliminar']);
    }

    /** Búsqueda rápida JSON: q en código, nombre, dpi, email, teléfono */
    public function search(){
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        $rows = $this->model->search($q, 20);
        echo json_encode(['status'=>'success','data'=>$rows]);
    }
}
