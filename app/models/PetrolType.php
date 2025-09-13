<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PetrolType extends Database
{
    public function all(): array {
        $sql = "SELECT * FROM petrol_type_list ORDER BY name ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $st = $this->getConnection()->prepare("SELECT * FROM petrol_type_list WHERE petrol_type_id = ?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function save(array $data): bool {
        if (empty($data['id'])) {
            $sql = "INSERT INTO petrol_type_list (name, price, status) VALUES (:name, :price, :status)";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':name'   => $data['name'],
                ':price'  => $data['price'],
                ':status' => $data['status'],
            ]);
        } else {
            $sql = "UPDATE petrol_type_list SET name = :name, price = :price, status = :status WHERE petrol_type_id = :id";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':name'   => $data['name'],
                ':price'  => $data['price'],
                ':status' => $data['status'],
                ':id'     => $data['id'],
            ]);
        }
    }

    public function delete(int $id): bool {
        $st = $this->getConnection()->prepare("DELETE FROM petrol_type_list WHERE petrol_type_id = ?");
        return $st->execute([$id]);
    }
}
