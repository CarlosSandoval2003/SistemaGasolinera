<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AccountController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new User();
    }

    private function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login"); exit;
        }
    }

    /** Vista normal (contraseña opcional) – si hay force, redirige al flujo de primer uso */
    public function index() {
        $this->requireLogin();
        if (!empty($_SESSION['force_pwd_change'])) {
            header("Location: index.php?url=account/firstChange"); exit;
        }
        $user = $this->model->getById((int)$_SESSION['user_id']);
        $this->render('account/manage', [
            'user'  => $user,
            'page'  => 'manage_account',
            'title' => 'Administrar Cuenta'
        ]);
    }

    /** Vista obligatoria para primer uso */
    public function firstChange() {
        $this->requireLogin();
        if (empty($_SESSION['force_pwd_change'])) {
            header("Location: index.php?url=home/index"); exit;
        }
        $user = $this->model->getById((int)$_SESSION['user_id']);
        $this->render('account/first_change', [
            'user'  => $user,
            'page'  => 'manage_account',
            'title' => 'Cambiar contraseña (primer uso)'
        ]);
    }

    /** POST: actualizar contraseña normal (pide contraseña actual) */
    public function update() {
        $this->requireLogin();
        header('Content-Type: application/json');

        $id       = (int)$_SESSION['user_id'];
        $fullname = trim($_POST['fullname'] ?? '');   // (ignorado en modelo)
        $username = trim($_POST['username'] ?? '');   // (ignorado en modelo)
        $newPass  = $_POST['password'] ?? null;
        $oldPass  = $_POST['old_password'] ?? null;

        $res = $this->model->updateAccount($id, $fullname, $username, $oldPass, $newPass);
        echo json_encode($res['ok']
            ? ['status'=>'success','msg'=>'Contraseña actualizada.']
            : ['status'=>'error','msg'=>$res['msg'] ?? 'Error al actualizar.']
        );
    }

    /** POST: guardar nueva contraseña en primer uso */
    public function updateFirst() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if (empty($_SESSION['force_pwd_change'])) {
            echo json_encode(['status'=>'error','msg'=>'No requiere cambio de primer uso.']); return;
        }

        $id      = (int)$_SESSION['user_id'];
        $p1      = $_POST['new_password'] ?? '';
        $p2      = $_POST['confirm_password'] ?? '';

        if ($p1 === '' || $p2 === '') {
            echo json_encode(['status'=>'error','msg'=>'Completa ambos campos.']); return;
        }
        if ($p1 !== $p2) {
            echo json_encode(['status'=>'error','msg'=>'Las contraseñas no coinciden.']); return;
        }
        if (strlen($p1) < 6) {
            echo json_encode(['status'=>'error','msg'=>'Mínimo 6 caracteres.']); return;
        }

        $res = $this->model->setPasswordFirstUse($id, $p1);
        if ($res['ok']) {
            unset($_SESSION['force_pwd_change']);
            echo json_encode(['status'=>'success','msg'=>'¡Contraseña actualizada! Redirigiendo…','redirect'=>'index.php?url=home/index']);
        } else {
            echo json_encode(['status'=>'error','msg'=>$res['msg'] ?? 'No se pudo guardar.']);
        }
    }
}
