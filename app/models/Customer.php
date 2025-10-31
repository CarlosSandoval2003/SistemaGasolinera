<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Customer extends Database
{
    /** Lista con filtros opcionales por NIT y Nombre */
    public function listFiltered(string $nit = '', string $name = ''): array
    {
        $sql = "SELECT customer_id, customer_code, fullname, status
                FROM customer_list
                WHERE 1=1";
        $params = [];

        if ($nit !== '') {
            $sql .= " AND customer_code LIKE :nit";
            $params[':nit'] = "%{$nit}%";
        }
        if ($name !== '') {
            $sql .= " AND fullname LIKE :name";
            $params[':name'] = "%{$name}%";
        }

        $sql .= " ORDER BY fullname ASC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $sql = "SELECT customer_id, customer_code, fullname, status
                FROM customer_list
                ORDER BY fullname ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT customer_id, customer_code, fullname, status
                FROM customer_list
                WHERE customer_id = ?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Guarda cliente. Si viene id => NO actualiza el NIT (customer_code).
     * En insert, sí inserta NIT.
     */
    public function save(array $d): bool
    {
        $pdo = $this->getConnection();

        if (!empty($d['id'])) {
            // UPDATE (sin cambiar NIT)
            $sql = "UPDATE customer_list
                       SET fullname = :fullname,
                           status   = :status
                     WHERE customer_id = :id";
            $st = $pdo->prepare($sql);
            return $st->execute([
                ':fullname' => $d['fullname'],
                ':status'   => (int)$d['status'],
                ':id'       => (int)$d['id'],
            ]);
        } else {
            // INSERT (dejamos contact/email/address vacíos)
            $sql = "INSERT INTO customer_list (customer_code, fullname, contact, email, address, status)
                    VALUES (:customer_code, :fullname, '', '', '', :status)";
            $st = $pdo->prepare($sql);
            return $st->execute([
                ':customer_code' => $d['customer_code'], // NIT
                ':fullname'      => $d['fullname'],
                ':status'        => (int)$d['status'],
            ]);
        }
    }

    public function delete(int $id): bool
    {
        $st = $this->getConnection()->prepare("DELETE FROM customer_list WHERE customer_id=?");
        return $st->execute([$id]);
    }
}
