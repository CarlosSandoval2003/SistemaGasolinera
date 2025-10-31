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

    /** Compatibilidad MD5 (32 hex) y password_hash */
    private function verifyPasswordHash(string $plain, string $hash): bool {
        if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            return md5($plain) === $hash;
        }
        return password_verify($plain, $hash);
    }

    /** Expuesta para login (no cambies nombre existentes usados por login) */
    public function verifyPasswordForLogin(string $plain, string $hash): bool {
        return $this->verifyPasswordHash($plain, $hash);
    }

    /**
     * Mantengo la firma para compatibilidad,
     * pero sólo cambia la contraseña (no fullname/username).
     */
    public function updateAccount(int $id, string $fullname, string $username, ?string $oldPass, ?string $newPass): array {
        if (empty($newPass)) {
            return ['ok' => false, 'msg' => 'Nueva contraseña requerida.'];
        }
        if (empty($oldPass)) {
            return ['ok' => false, 'msg' => 'Debes ingresar tu contraseña actual.'];
        }

        // Verificar contraseña actual
        $st = $this->conn->prepare("SELECT password FROM user_list WHERE user_id = ?");
        $st->execute([$id]);
        $currentHash = $st->fetchColumn();
        if (!$currentHash) return ['ok'=>false,'msg'=>'Usuario no existe.'];
        if (!$this->verifyPasswordHash($oldPass, $currentHash)) {
            return ['ok'=>false,'msg'=>'La contraseña actual no es correcta.'];
        }

        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $up = $this->conn->prepare("UPDATE user_list SET password = ? WHERE user_id = ?");
        $ok = $up->execute([$newHash, $id]);
        return $ok ? ['ok'=>true] : ['ok'=>false,'msg'=>'No se pudo actualizar la contraseña.'];
    }

    /** Primer uso: establece contraseña SIN pedir la actual y limpia el flag */
    public function setPasswordFirstUse(int $id, string $newPass): array {
        if (empty($newPass)) return ['ok'=>false,'msg'=>'Nueva contraseña requerida.'];

        $st = $this->conn->prepare("SELECT first_use_password FROM user_list WHERE user_id=?");
        $st->execute([$id]);
        $flag = $st->fetchColumn();
        if ($flag === false) return ['ok'=>false,'msg'=>'Usuario no existe.'];
        if ((int)$flag !== 1)  return ['ok'=>false,'msg'=>'Este usuario no requiere cambio de primer uso.'];

        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $up = $this->conn->prepare("UPDATE user_list SET password=?, first_use_password=0 WHERE user_id=?");
        $ok = $up->execute([$newHash, $id]);
        return $ok ? ['ok'=>true] : ['ok'=>false,'msg'=>'No se pudo guardar la nueva contraseña.'];
    }
}
