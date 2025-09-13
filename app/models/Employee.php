<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Employee extends Database {
    // Helpers
    private function genCode(): string {
        $ym   = date('Ym');
        $from = date('Y-m-01');
        $to   = date('Y-m-01', strtotime('+1 month'));
        $st = $this->getConnection()->prepare("SELECT COUNT(*) FROM employee WHERE created_at >= ? AND created_at < ?");
        $st->execute([$from, $to]);
        $seq = (int)$st->fetchColumn() + 1;
        return sprintf('EMP-%s-%04d', $ym, $seq);
    }
    public function suggestUsername(string $first, string $last): string {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i','', substr($first,0,1).$last));
        if ($base==='') $base = 'user';
        $pdo = $this->getConnection();
        $u = $base; $i=0;
        while (true) {
            $st = $pdo->prepare("SELECT 1 FROM user_list WHERE LOWER(username)=LOWER(?) LIMIT 1");
            $st->execute([$u]);
            if (!$st->fetch()) return $u;
            $i++; $u = $base.$i;
        }
    }

    // CRUD
    public function all(): array {
        $sql = "SELECT e.*, p.name AS position_name, u.username
                FROM employee e
                INNER JOIN positions p ON p.position_id = e.position_id
                LEFT JOIN user_list u ON u.user_id = e.user_id
                ORDER BY e.status DESC, e.fullname ASC";
        $st = $this->getConnection()->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get(int $id): ?array {
        $sql = "SELECT e.*, p.name AS position_name, p.suggested_role
                FROM employee e
                INNER JOIN positions p ON p.position_id = e.position_id
                WHERE e.employee_id=?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
public function save(array $d): array {
    $pdo = $this->getConnection();

    if (empty($d['employee_id'])) {
        // INSERT
        $code = $this->genCode();
        $sql = "INSERT INTO employee
                (code, first_name, last_name, fullname, dpi, email, phone, address, hire_date, salary, status, position_id, user_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NULL)";
        $st = $pdo->prepare($sql);
        $ok = $st->execute([
            $code,
            $d['first_name'] ?? null,
            $d['last_name'] ?? null,
            $d['fullname'] ?? null,
            $d['dpi'] ?? null,
            $d['email'] ?? null,
            $d['phone'] ?? null,
            $d['address'] ?? null,
            $d['hire_date'] ?? null,
            (float)($d['salary'] ?? 0),
            (int)($d['status'] ?? 1),
            (int)($d['position_id'] ?? 0),
        ]);
        $id = $ok ? (int)$pdo->lastInsertId() : 0;
        return ['ok'=>$ok, 'id'=>$id];

    } else {
        // UPDATE
        $sql = "UPDATE employee SET
                    first_name = ?, last_name = ?, fullname = ?,
                    dpi = ?, email = ?, phone = ?, address = ?, hire_date = ?,
                    salary = ?, status = ?, position_id = ?
                WHERE employee_id = ?";
        $st = $pdo->prepare($sql);
        $ok = $st->execute([
            $d['first_name'] ?? null,
            $d['last_name'] ?? null,
            $d['fullname'] ?? null,
            $d['dpi'] ?? null,
            $d['email'] ?? null,
            $d['phone'] ?? null,
            $d['address'] ?? null,
            $d['hire_date'] ?? null,
            (float)($d['salary'] ?? 0),
            (int)($d['status'] ?? 1),
            (int)($d['position_id'] ?? 0),
            (int)$d['employee_id'],
        ]);
        return ['ok'=>$ok, 'id'=>(int)$d['employee_id']];
    }
}
    public function delete(int $id): array {
        // Si tiene user vinculado, opcionalmente bloquear o permitir (dejamos permitir: FK SET NULL)
        $st = $this->getConnection()->prepare("DELETE FROM employee WHERE employee_id=?");
        return ['ok'=>$st->execute([$id])];
    }

    // Usuarios
    public function createUser(int $employee_id, string $username, string $password, int $type): array {
        $pdo = $this->getConnection();
        $e = $this->get($employee_id);
        if (!$e) return ['ok'=>false,'msg'=>'Empleado no encontrado'];
        if (!empty($e['user_id'])) return ['ok'=>false,'msg'=>'El empleado ya tiene usuario'];

        // username único
        $st = $pdo->prepare("SELECT 1 FROM user_list WHERE LOWER(username)=LOWER(?) LIMIT 1");
        $st->execute([$username]);
        if ($st->fetch()) return ['ok'=>false,'msg'=>'El usuario ya existe'];

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $ins = $pdo->prepare("INSERT INTO user_list (fullname, username, password, type, status) VALUES (?,?,?,?,1)");
        $ok = $ins->execute([$e['fullname'], $username, $hash, $type]);
        if (!$ok) return ['ok'=>false,'msg'=>'No se pudo crear el usuario'];

        $uid = (int)$pdo->lastInsertId();
        $up = $pdo->prepare("UPDATE employee SET user_id=? WHERE employee_id=?");
        $up->execute([$uid, $employee_id]);

        return ['ok'=>true, 'user_id'=>$uid];
    }

    public function linkUser(int $employee_id, int $user_id): array {
        $pdo = $this->getConnection();
        // usuario libre?
        $c = $pdo->prepare("SELECT COUNT(*) FROM employee WHERE user_id=?");
        $c->execute([$user_id]);
        if ((int)$c->fetchColumn() > 0) return ['ok'=>false,'msg'=>'Ese usuario ya está asignado a otro empleado'];
        $up = $pdo->prepare("UPDATE employee SET user_id=? WHERE employee_id=?");
        return ['ok'=>$up->execute([$user_id, $employee_id])];
    }
}
