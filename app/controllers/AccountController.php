<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AccountController extends Controller
{
    private $model;

    public function __construct() {
        $this->model = new User();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }
        $user = $this->model->getById((int)$_SESSION['user_id']);

        $this->render('account/manage', [
            'user'  => $user,
            'page'  => 'manage_account',
            'title' => 'Manage Account'
        ]);
    }

    public function update() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status'=>'error','msg'=>'No autenticado']); return;
        }

        $id       = (int)$_SESSION['user_id'];
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $newPass  = $_POST['password'] ?? null;
        $oldPass  = $_POST['old_password'] ?? null;

        if ($fullname === '' || $username === '') {
            echo json_encode(['status'=>'error','msg'=>'Nombre y usuario son obligatorios.']); return;
        }

        $res = $this->model->updateAccount($id, $fullname, $username, $oldPass, $newPass);

        if ($res['ok']) {
            // refrescar nombre en sesión
            $_SESSION['fullname'] = $fullname;
            echo json_encode(['status'=>'success','msg'=>'Cuenta actualizada correctamente.']);
        } else {
            echo json_encode(['status'=>'error','msg'=>$res['msg'] ?? 'Error al actualizar.']);
        }
    }
}
