<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    protected PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getByUsername(string $username): ?array {
        $sql = "SELECT * FROM user_list WHERE username = :username LIMIT 1";
        $st  = $this->conn->prepare($sql);
        $st->execute(['username' => $username]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getById(int $id): ?array {
        $st = $this->conn->prepare("SELECT * FROM user_list WHERE user_id = ?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function usernameExists(string $username, int $excludeId = 0): bool {
        if ($excludeId > 0) {
            $st = $this->conn->prepare("SELECT 1 FROM user_list WHERE username = ? AND user_id <> ? LIMIT 1");
            $st->execute([$username, $excludeId]);
        } else {
            $st = $this->conn->prepare("SELECT 1 FROM user_list WHERE username = ? LIMIT 1");
            $st->execute([$username]);
        }
        return (bool)$st->fetchColumn();
    }

    private function verifyPasswordHash(string $plain, string $hash): bool {
        // Compatibilidad con MD5 legado (32 chars hex) vs password_hash moderno
        if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            return md5($plain) === $hash;
        }
        return password_verify($plain, $hash);
    }

    public function updateAccount(int $id, string $fullname, string $username, ?string $oldPass, ?string $newPass): array {
        // 1) Username duplicado
        if ($this->usernameExists($username, $id)) {
            return ['ok' => false, 'msg' => 'El nombre de usuario ya está en uso.'];
        }

        // 2) ¿Hay cambio de contraseña?
        $setPassword = false;
        $newHash = null;

        if (!empty($newPass)) {
            // Necesitamos verificar el password actual
            if (empty($oldPass)) {
                return ['ok' => false, 'msg' => 'Debes ingresar tu contraseña actual para cambiarla.'];
            }
            $st = $this->conn->prepare("SELECT password FROM user_list WHERE user_id = ?");
            $st->execute([$id]);
            $currentHash = $st->fetchColumn();
            if (!$currentHash || !$this->verifyPasswordHash($oldPass, $currentHash)) {
                return ['ok' => false, 'msg' => 'La contraseña actual no es correcta.'];
            }
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $setPassword = true;
        }

        // 3) Update
        if ($setPassword) {
            $sql = "UPDATE user_list SET fullname = ?, username = ?, password = ? WHERE user_id = ?";
            $params = [$fullname, $username, $newHash, $id];
        } else {
            $sql = "UPDATE user_list SET fullname = ?, username = ? WHERE user_id = ?";
            $params = [$fullname, $username, $id];
        }

        $st = $this->conn->prepare($sql);
        $ok = $st->execute($params);

        return $ok ? ['ok' => true] : ['ok' => false, 'msg' => 'No se pudo actualizar.'];
    }
}
