<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Employee;
use App\Models\Position;

class EmployeeController extends Controller {
    private Employee $model;
    public function __construct(){ $this->model = new Employee(); }

    public function index(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        if (($_SESSION['type'] ?? 0) != 1) { header("Location: index.php?url=home/index"); exit; }
        $pos = new Position();
        $this->render('employees/index', [
            'employees'=>$this->model->all(),
            'positions'=>$pos->all(),
            'page'=>'employees','title'=>'Empleados'
        ]);
    }
    public function manage($id=null){
        $pos = new Position();
        $emp = $id ? $this->model->get((int)$id) : null;
        $this->render('employees/manage', ['employee'=>$emp,'positions'=>$pos->all()], null, false);
    }
    public function save(){
        header('Content-Type: application/json');
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $d = [
            'employee_id' => $_POST['id'] ?? null,
            'first_name'  => $first,
            'last_name'   => $last,
            'fullname'    => trim(($first.' '.$last)),
            'dpi'         => trim($_POST['dpi'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'address'     => trim($_POST['address'] ?? ''),
            'hire_date'   => $_POST['hire_date'] ?? null,
            'salary'      => (float)($_POST['salary'] ?? 0),
            'status'      => (int)($_POST['status'] ?? 1),
            'position_id' => (int)($_POST['position_id'] ?? 0),
        ];
        if ($d['first_name']==='' || $d['last_name']==='' || $d['dpi']==='' || $d['email']==='' || !$d['position_id']){
            echo json_encode(['status'=>'error','msg'=>'Completa: nombre, apellido, DPI, email y puesto.']); return;
        }
        $res = $this->model->save($d);
        echo json_encode($res['ok']?['status'=>'success','msg'=>'Empleado guardado']:['status'=>'error','msg'=>'No se pudo guardar']);
    }
    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $res = $this->model->delete($id);
        echo json_encode($res['ok']?['status'=>'success']:['status'=>'error','msg'=>'No se pudo eliminar']);
    }

    // Modales de usuario
    public function assignUser($employee_id){
        $emp = $this->model->get((int)$employee_id);
        if(!$emp){ echo "Empleado no encontrado"; return; }
        // sugerir username y tipo según puesto
        $suggest = $this->model->suggestUsername($emp['first_name'],$emp['last_name']);
        $this->render('employees/assign_user', ['emp'=>$emp,'suggest'=>$suggest], null, false);
    }
    public function createUser(){
        header('Content-Type: application/json');
        $eid = (int)($_POST['employee_id'] ?? 0);
        $u   = trim($_POST['username'] ?? '');
        $p   = $_POST['password'] ?? '';
        $t   = (int)($_POST['type'] ?? 0); // 0 Cashier, 1 Admin
        if(!$eid || $u==='' || $p===''){ echo json_encode(['status'=>'error','msg'=>'Datos incompletos']); return; }
        $res = $this->model->createUser($eid,$u,$p,$t);
        echo json_encode($res['ok']?['status'=>'success','msg'=>'Usuario creado']:['status'=>'error','msg'=>$res['msg'] ?? 'Error']);
    }
    public function linkUser($employee_id){
        // modal de vincular existente
        $emp = $this->model->get((int)$employee_id);
        // usuarios libres:
        $pdo = (new \App\Core\Database())->getConnection();
        $st = $pdo->query("SELECT u.user_id, u.username FROM user_list u LEFT JOIN employee e ON e.user_id=u.user_id WHERE e.user_id IS NULL ORDER BY u.username");
        $users = $st->fetchAll(\PDO::FETCH_ASSOC);
        $this->render('employees/link_user', ['emp'=>$emp,'users'=>$users], null, false);
    }
    public function doLinkUser(){
        header('Content-Type: application/json');
        $eid = (int)($_POST['employee_id'] ?? 0);
        $uid = (int)($_POST['user_id'] ?? 0);
        if(!$eid || !$uid){ echo json_encode(['status'=>'error','msg'=>'Datos inválidos']); return; }
        $res = $this->model->linkUser($eid,$uid);
        echo json_encode($res['ok']?['status'=>'success','msg'=>'Vinculado']:['status'=>'error','msg'=>$res['msg'] ?? 'Error']);
    }
}
