<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Employee extends Database {
    /* Código incremental mensual */
    private function genCode(): string {
        $ym   = date('Ym');
        $from = date('Y-m-01');
        $to   = date('Y-m-01', strtotime('+1 month'));
        $st = $this->getConnection()->prepare("SELECT COUNT(*) FROM employee WHERE created_at >= ? AND created_at < ?");
        $st->execute([$from, $to]);
        $seq = (int)$st->fetchColumn() + 1;
        return sprintf('EMP-%s-%04d', $ym, $seq);
    }

    /* Listado (sin join a usuarios) */
    public function all(): array {
        $sql = "SELECT e.*, p.name AS position_name
                FROM employee e
                INNER JOIN positions p ON p.position_id = e.position_id
                ORDER BY e.status DESC, e.fullname ASC";
        $st = $this->getConnection()->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array {
        $sql = "SELECT e.*, p.name AS position_name
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
                $d['first_name'],
                $d['last_name'],
                $d['fullname'],
                $d['dpi'],
                $d['email'],
                $d['phone'],
                $d['address'],
                $d['hire_date'],
                (float)$d['salary'],
                (int)$d['status'],
                (int)$d['position_id'],
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
                $d['first_name'],
                $d['last_name'],
                $d['fullname'],
                $d['dpi'],
                $d['email'],
                $d['phone'],
                $d['address'],
                $d['hire_date'],
                (float)$d['salary'],
                (int)$d['status'],
                (int)$d['position_id'],
                (int)$d['employee_id'],
            ]);
            return ['ok'=>$ok, 'id'=>(int)$d['employee_id']];
        }
    }

    public function delete(int $id): array {
        $st = $this->getConnection()->prepare("DELETE FROM employee WHERE employee_id=?");
        return ['ok'=>$st->execute([$id])];
    }

    /** Búsqueda para autocomplete/listado */
    public function search(string $q, int $limit=20): array {
        $pdo = $this->getConnection();
        if ($q==='') {
            $st = $pdo->prepare("SELECT employee_id, code, fullname, dpi, email, phone FROM employee ORDER BY fullname LIMIT ?");
            $st->bindValue(1, $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }
        $like = "%$q%";
        $st = $pdo->prepare(
            "SELECT employee_id, code, fullname, dpi, email, phone
             FROM employee
             WHERE code LIKE ? OR fullname LIKE ? OR dpi LIKE ? OR email LIKE ? OR phone LIKE ?
             ORDER BY fullname LIMIT ?"
        );
        $st->bindValue(1, $like); $st->bindValue(2, $like); $st->bindValue(3, $like);
        $st->bindValue(4, $like); $st->bindValue(5, $like);
        $st->bindValue(6, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
