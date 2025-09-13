<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Position;

class PositionController extends Controller {
    private Position $model;
    public function __construct(){ $this->model = new Position(); }

    public function index(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        if (($_SESSION['type'] ?? 0) != 1) { header("Location: index.php?url=home/index"); exit; }
        $this->render('positions/index', [
            'positions'=>$this->model->all(),
            'page'=>'positions','title'=>'Puestos'
        ]);
    }
    public function manage($id=null){
        $pos = $id ? $this->model->get((int)$id) : null;
        $this->render('positions/manage', ['position'=>$pos], null, false);
    }
    public function save(){
        header('Content-Type: application/json');
        $d = [
            'position_id'    => $_POST['id'] ?? null,
            'name'           => trim($_POST['name'] ?? ''),
            'description'    => $_POST['description'] ?? null,
            'suggested_role' => (int)($_POST['suggested_role'] ?? 0),
            'status'         => (int)($_POST['status'] ?? 1),
        ];
        if ($d['name']===''){ echo json_encode(['status'=>'error','msg'=>'Nombre requerido']); return; }
        $ok = $this->model->save($d);
        echo json_encode($ok?['status'=>'success','msg'=>'Puesto guardado']:['status'=>'error','msg'=>'No se pudo guardar']);
    }
    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $res = $this->model->delete($id);
        echo json_encode($res['ok']?['status'=>'success']:['status'=>'error','msg'=>$res['msg'] ?? 'No se pudo eliminar']);
    }
}
