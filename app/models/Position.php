<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Position extends Database {
    public function all(): array {
        $st = $this->getConnection()->query(
            "SELECT position_id, name, description, status 
             FROM positions 
             ORDER BY status DESC, name ASC"
        );
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array {
        $st = $this->getConnection()->prepare(
            "SELECT position_id, name, description, status 
             FROM positions WHERE position_id=?"
        );
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Guardar sin tocar suggested_role (lo quitamos de la lógica).
     * En INSERT se usará el valor por defecto de la columna en BD.
     */
    public function save(array $d): bool {
        $pdo = $this->getConnection();

        if (!empty($d['position_id'])) {
            // UPDATE: no actualizamos suggested_role
            $sql = "UPDATE positions 
                       SET name = ?, description = ?, status = ?
                     WHERE position_id = ?";
            $st = $pdo->prepare($sql);
            return $st->execute([
                $d['name'] ?? null,
                $d['description'] ?? null,
                (int)($d['status'] ?? 1),
                (int)$d['position_id']
            ]);
        } else {
            // INSERT: omitimos suggested_role para que tome el DEFAULT de la tabla
            $sql = "INSERT INTO positions (name, description, status)
                    VALUES (?,?,?)";
            $st = $pdo->prepare($sql);
            return $st->execute([
                $d['name'] ?? null,
                $d['description'] ?? null,
                (int)($d['status'] ?? 1)
            ]);
        }
    }

    public function delete(int $id): array {
        // Evitar borrar si hay empleados usando el puesto
        $c = $this->getConnection()->prepare("SELECT COUNT(*) FROM employee WHERE position_id=?");
        $c->execute([$id]);
        if ((int)$c->fetchColumn() > 0) {
            return ['ok'=>false,'msg'=>'No se puede eliminar: hay empleados con este puesto.'];
        }

        $st = $this->getConnection()->prepare("DELETE FROM positions WHERE position_id=?");
        return ['ok'=>$st->execute([$id])];
    }
}
