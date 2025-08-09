<?php
namespace App\Controllers;
require_once '../app/models/User.php';

use App\Models\User;

class AuthController {
    public function showLoginForm() {
        require_once '../app/views/auth/login.view.php';
    }

    public function login() {
    session_start();
    header('Content-Type: application/json');

    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Método no permitido.']);
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new \App\Models\User();
        $user = $userModel->getByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['type'] = $user['type'];

            echo json_encode(['status' => 'success', 'msg' => 'Login successful']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid credentials']);
        }
    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'msg' => 'ERROR: ' . $e->getMessage()]);
    }
}


    public function logout() {
        session_start();
        session_destroy();
        header("Location: index.php?url=auth/login");
        exit;
    }
}
