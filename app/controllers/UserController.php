<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Users;

class UserController extends Controller
{
    private $model;

    public function __construct() {
        $this->model = new Users();
    }

public function index() {
    $users = $this->model->all();
    $this->render('users/index', [
        'users' => $users,
        'roles' => \App\Models\Users::roles(), // <-- mapa
        'page'  => 'users',
        'title' => 'Users | Petrol Station'
    ]);
}

public function manage() {
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $user = $id ? $this->model->find($id) : null;

    $this->render('users/manage', [
        'user'  => $user,
        'roles' => \App\Models\Users::roles(), 
    ], null, false);
}



    public function save() {
        header('Content-Type: application/json');

        $id       = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $type     = isset($_POST['type']) ? (int)$_POST['type'] : 0;

        if ($fullname === '' || $username === '') {
            echo json_encode(['status'=>'error','msg'=>'Nombre y usuario son requeridos.']); return;
        }

        // Validar username único
        if ($this->model->usernameExists($username, $id ?: null)) {
            echo json_encode(['status'=>'error','msg'=>'El nombre de usuario ya existe.']); return;
        }

        $ok = $this->model->save([
            'id'       => $id,
            'fullname' => $fullname,
            'username' => $username,
            'type'     => $type,
        ]);

        if ($ok) {
            $msg = $id ? 'Usuario actualizado correctamente.' : 'Usuario creado. (Password por defecto: 123456)';
            echo json_encode(['status'=>'success','msg'=>$msg]);
        } else {
            echo json_encode(['status'=>'error','msg'=>'No se pudo guardar el usuario.']);
        }
    }

    public function delete() {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        if ($id === 1) { echo json_encode(['status'=>'error','msg'=>'No puedes borrar el administrador principal.']); return; }

        $ok = $this->model->delete($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Usuario eliminado.']
            : ['status'=>'error','msg'=>'No se pudo eliminar.']);
    }
}
