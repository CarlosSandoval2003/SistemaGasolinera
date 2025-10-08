<?php
// Cambia esta contraseña por la que quieras hashear
$password = 'admin123';

// Hashearla
$hash = password_hash($password, PASSWORD_DEFAULT);

// Mostrar el hash
echo "Contraseña original: $password\n";
echo "Hash generado: $hash\n";
