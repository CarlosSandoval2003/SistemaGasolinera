<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Users;

class UserController extends Controller
{
    private Users $model;

    public function __construct() {
        $this->model = new Users();
    }

    public function index() {
        // Filtros
        $q     = trim($_GET['q']   ?? '');
        $role  = ($_GET['role']    ?? '') === '' ? null : (int)$_GET['role'];
        $state = ($_GET['status']  ?? '') === '' ? null : (int)$_GET['status'];

        $users = $this->model->search($q, $role, $state);
        $this->render('users/index', [
            'users'  => $users,
            'roles'  => Users::roles(),
            'q'      => $q,
            'role'   => $role,
            'status' => $state,
            'page'   => 'users',
            'title'  => 'Usuarios'
        ]);
    }

    public function manage() {
        $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $user = $id ? $this->model->find($id) : null;

        $this->render('users/manage', [
            'user'  => $user,
            'roles' => Users::roles(),
        ], null, false);
    }

    public function save() {
        header('Content-Type: application/json');

        $id       = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $type     = isset($_POST['type']) ? (int)$_POST['type'] : 0;
        $status   = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if ($fullname === '' || $username === '') {
            echo json_encode(['status'=>'error','msg'=>'Nombre completo y usuario son requeridos.']); return;
        }

        if ($this->model->usernameExists($username, $id ?: null)) {
            echo json_encode(['status'=>'error','msg'=>'El nombre de usuario ya existe.']); return;
        }

        $ok = $this->model->save([
            'id'       => $id,
            'fullname' => $fullname,
            'username' => $username,
            'type'     => $type,
            'status'   => $status
        ]);

        if ($ok) {
            $msg = $id ? 'Usuario actualizado correctamente.' : 'Usuario creado. (Contraseña por defecto: 123456)';
            echo json_encode(['status'=>'success','msg'=>$msg]);
        } else {
            echo json_encode(['status'=>'error','msg'=>'No se pudo guardar el usuario.']);
        }
    }

    public function delete() {
        header('Content-Type: application/json');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0)  { echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        if ($id === 1) { echo json_encode(['status'=>'error','msg'=>'No puedes borrar el administrador principal.']); return; }

        $ok = $this->model->delete($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Usuario eliminado.']
            : ['status'=>'error','msg'=>'No se pudo eliminar.']);
    }

    /** Reinicia la contraseña del usuario a la predeterminada de un solo uso (123456) */
    public function resetPassword() {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0)  { echo json_encode(['status'=>'error','msg'=>'ID inválido']); return; }
        if ($id === 1) { echo json_encode(['status'=>'error','msg'=>'No puedes reiniciar la contraseña del superadmin desde aquí.']); return; }

        $ok = $this->model->resetPassword($id);
        echo json_encode($ok
            ? ['status'=>'success','msg'=>'Contraseña reiniciada a 123456 (uso único).']
            : ['status'=>'error','msg'=>'No se pudo reiniciar la contraseña.']);
    }
}
