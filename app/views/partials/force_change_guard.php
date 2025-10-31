<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Ruta actual (formato "controller/action" que ya usas)
$current = $_GET['url'] ?? '';

// Rutas permitidas mientras esté obligado a cambiar contraseña
$allowedWhenForced = [
  'account/firstChange',
  'account/updateFirst',
  'auth/login',
  'auth/showLoginForm',
  'auth/logout',
];

if (!empty($_SESSION['user_id']) && !empty($_SESSION['force_pwd_change'])) {
    // ¿La ruta actual está permitida?
    $ok = false;
    foreach ($allowedWhenForced as $allow) {
        if (strpos($current, $allow) === 0) { $ok = true; break; }
    }
    if (!$ok) {
        header("Location: index.php?url=account/firstChange");
        exit;
    }
}
