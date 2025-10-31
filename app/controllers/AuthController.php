<?php
namespace App\Controllers;
require_once APP_PATH . '/models/User.php';

use App\Models\User;

class AuthController {
    public function showLoginForm() {
        require_once APP_PATH . '/views/auth/login.view.php';
    }

    public function login() {
        session_start();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['status' => 'error', 'msg' => 'Método no permitido.']); exit;
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $userModel = new \App\Models\User();
            $user = $userModel->getByUsername($username);

            if (!$user) {
                echo json_encode(['status'=>'error','msg'=>'Usuario o contraseña inválidos']); return;
            }

            // Usa el verificador del modelo (compatible MD5/password_hash)
            $okPass = $userModel->verifyPasswordForLogin($password, $user['password']);
            if (!$okPass) {
                echo json_encode(['status'=>'error','msg'=>'Usuario o contraseña inválidos']); return;
            }

            // Sesión base
            $_SESSION['user_id']  = (int)$user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['type']     = (int)$user['type'];

            // ¿Primer uso?
            $force = !empty($user['first_use_password']) ? (int)$user['first_use_password'] : 0;
            if ($force === 1) {
                $_SESSION['force_pwd_change'] = 1;
                echo json_encode([
                    'status' => 'success',
                    'msg' => 'Debes cambiar tu contraseña por ser de primer uso.',
                    'force_change' => true,
                    'redirect' => 'index.php?url=account/firstChange'
                ]);
                return;
            }

            // Login normal
            unset($_SESSION['force_pwd_change']);
            echo json_encode([
                'status' => 'success',
                'msg' => 'Login correcto',
                'redirect' => 'index.php?url=home/index'
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'msg' => 'ERROR: '.$e->getMessage()]);
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: index.php?url=auth/login");
        exit;
    }
}
