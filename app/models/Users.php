<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Users extends Database
{
    // Mapa único de roles para toda la app
    public const ROLE_LABELS = [
        0 => 'Cashier',
        1 => 'Administrator',
        2 => 'Mantenimiento',
        3 => 'Consulta',
        4 => 'Pedido',
        5 => 'Abastecimiento',
    ];

    public static function roles(): array {
        return self::ROLE_LABELS;
    }

    private function isValidType(int $t): bool {
        return array_key_exists($t, self::ROLE_LABELS);
    }

    public function all(): array {
        $sql = "SELECT * FROM user_list WHERE user_id != 1 ORDER BY fullname ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $st = $this->getConnection()->prepare("SELECT * FROM user_list WHERE user_id = ?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function usernameExists(string $username, ?int $ignoreId = null): bool {
        $sql  = "SELECT COUNT(*) FROM user_list WHERE username = ?";
        $args = [$username];
        if ($ignoreId !== null) {
            $sql .= " AND user_id <> ?";
            $args[] = $ignoreId;
        }
        $st = $this->getConnection()->prepare($sql);
        $st->execute($args);
        return (bool)$st->fetchColumn();
    }

    public function save(array $data): bool {
        $type = (int)($data['type'] ?? 0);
        if (!$this->isValidType($type)) $type = 0; // fallback a Cashier si llega algo inválido

        if (empty($data['id'])) {
            $sql = "INSERT INTO user_list (fullname, username, password, type, status)
                    VALUES (:fullname, :username, :password, :type, 1)";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':fullname' => $data['fullname'],
                ':username' => $data['username'],
                ':password' => password_hash('123456', PASSWORD_DEFAULT),
                ':type'     => $type,
            ]);
        } else {
            $sql = "UPDATE user_list SET fullname = :fullname, username = :username, type = :type
                    WHERE user_id = :id";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':fullname' => $data['fullname'],
                ':username' => $data['username'],
                ':type'     => $type,
                ':id'       => (int)$data['id'],
            ]);
        }
    }

    public function delete(int $id): bool {
        if ($id === 1) return false; // nunca borrar superadmin
        $st = $this->getConnection()->prepare("DELETE FROM user_list WHERE user_id = ?");
        return $st->execute([$id]);
    }

    public function getById(int $id): ?array {
        $st = $this->getConnection()->prepare("SELECT * FROM user_list WHERE user_id = ?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function verifyLegacyOrBcrypt(string $plain, string $stored): bool {
        if (strlen($stored) === 32 && ctype_xdigit($stored)) {
            return md5($plain) === $stored;
        }
        return password_verify($plain, $stored);
    }

    public function updateAccount(int $id, string $fullname, string $username, ?string $old = null, ?string $new = null): array {
        if ($this->usernameExists($username, $id)) {
            return ['ok' => false, 'msg' => 'El username ya está en uso.'];
        }
        $user = $this->getById($id);
        if (!$user) return ['ok' => false, 'msg' => 'Usuario no encontrado.'];

        $updatePass = false; $newHash = null;
        if ($new !== null && $new !== '') {
            if ($old === null || $old === '') return ['ok'=>false,'msg'=>'Debes ingresar tu contraseña actual.'];
            if (!$this->verifyLegacyOrBcrypt($old, $user['password'])) return ['ok'=>false,'msg'=>'La contraseña actual es incorrecta.'];
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $updatePass = true;
        }

        if ($updatePass) {
            $sql = "UPDATE user_list SET fullname=:fullname, username=:username, password=:password WHERE user_id=:id";
            $params = [':fullname'=>$fullname, ':username'=>$username, ':password'=>$newHash, ':id'=>$id];
        } else {
            $sql = "UPDATE user_list SET fullname=:fullname, username=:username WHERE user_id=:id";
            $params = [':fullname'=>$fullname, ':username'=>$username, ':id'=>$id];
        }

        $st = $this->getConnection()->prepare($sql);
        $ok = $st->execute($params);
        return $ok ? ['ok'=>true] : ['ok'=>false,'msg'=>'No se pudo actualizar.'];
    }
}
