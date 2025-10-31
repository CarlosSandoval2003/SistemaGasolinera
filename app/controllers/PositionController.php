<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Position;

class PositionController extends Controller {
    private Position $model;

    public function __construct(){
        $this->model = new Position();
    }

    public function index(){
        if (session_status()===PHP_SESSION_NONE) session_start();
        if (($_SESSION['type'] ?? 0) != 1) { header("Location: index.php?url=home/index"); exit; }

        $this->render('positions/index', [
            'positions' => $this->model->all(),
            'page'      => 'positions',
            'title'     => 'Puestos'
        ]);
    }

    public function manage($id=null){
        // Permite pasar ?id=123 o /manage/123
        if (!$id && isset($_GET['id'])) $id = (int)$_GET['id'];
        $pos = $id ? $this->model->get((int)$id) : null;

        // Render sin layout (para modal)
        $this->render('positions/manage', ['position'=>$pos], null, false);
    }

    public function save(){
        header('Content-Type: application/json');

        $d = [
            'position_id' => $_POST['id'] ?? null,
            'name'        => trim($_POST['name'] ?? ''),
            'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
            'status'      => (int)($_POST['status'] ?? 1),
        ];

        if ($d['name'] === ''){
            echo json_encode(['status'=>'error','msg'=>'El nombre es requerido.']); return;
        }
        if (strlen($d['name']) > 100){
            echo json_encode(['status'=>'error','msg'=>'Nombre demasiado largo (máx 100).']); return;
        }
        if ($d['description'] !== null && strlen($d['description']) > 500){
            echo json_encode(['status'=>'error','msg'=>'Descripción demasiado larga (máx 500).']); return;
        }

        $ok = $this->model->save($d);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Puesto guardado']
            : ['status'=>'error','msg'=>'No se pudo guardar']);
    }

    public function delete(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0){ echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }

        $res = $this->model->delete($id);
        echo json_encode($res['ok']
            ? ['status'=>'success']
            : ['status'=>'error','msg'=>$res['msg'] ?? 'No se pudo eliminar']);
    }
}
