<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PetrolType extends Database
{
    public function all(): array {
        $sql = "SELECT petrol_type_id, name, purchase_price_gal, sale_price_gal, price, status
                FROM petrol_type_list
                ORDER BY name ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $st = $this->getConnection()->prepare(
            "SELECT petrol_type_id, name, purchase_price_gal, sale_price_gal, price, status
             FROM petrol_type_list WHERE petrol_type_id = ?"
        );
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function save(array $data): bool {
        // Normaliza y sincroniza legacy "price" con sale_price_gal
        $name  = $data['name'];
        $pcomp = (float)$data['purchase_price_gal'];
        $pvent = (float)$data['sale_price_gal'];
        $stat  = (int)$data['status'];
        $id    = $data['id'] ?? null;

        if (empty($id)) {
            $sql = "INSERT INTO petrol_type_list (name, purchase_price_gal, sale_price_gal, price, status)
                    VALUES (:name, :pcomp, :pvent, :legacy_price, :status)";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':name'         => $name,
                ':pcomp'        => $pcomp,
                ':pvent'        => $pvent,
                ':legacy_price' => $pvent,   // legacy "price" = sale_price_gal
                ':status'       => $stat,
            ]);
        } else {
            $sql = "UPDATE petrol_type_list
                    SET name=:name,
                        purchase_price_gal=:pcomp,
                        sale_price_gal=:pvent,
                        price=:legacy_price,     -- sincroniza legacy
                        status=:status
                    WHERE petrol_type_id=:id";
            $st = $this->getConnection()->prepare($sql);
            return $st->execute([
                ':name'         => $name,
                ':pcomp'        => $pcomp,
                ':pvent'        => $pvent,
                ':legacy_price' => $pvent,   // legacy "price" = sale_price_gal
                ':status'       => $stat,
                ':id'           => $id,
            ]);
        }
    }

    public function delete(int $id): bool {
        $st = $this->getConnection()->prepare("DELETE FROM petrol_type_list WHERE petrol_type_id = ?");
        return $st->execute([$id]);
    }
}
