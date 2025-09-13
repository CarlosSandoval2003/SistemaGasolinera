<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Container extends Database {
    public function all() {
        $sql = "SELECT c.*, p.name AS petrol_name,
                       IFNULL(s.qty_liters,0) AS qty_liters
                FROM container c
                JOIN petrol_type_list p ON p.petrol_type_id = c.petrol_type_id
                LEFT JOIN container_stock s ON s.container_id = c.container_id
                ORDER BY p.name, c.name";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $st = $this->getConnection()->prepare(
            "SELECT c.*, IFNULL(s.qty_liters,0) qty_liters FROM container c
             LEFT JOIN container_stock s ON s.container_id=c.container_id
             WHERE c.container_id=?"
        );
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            if (!empty($data['is_default'])) {
                // desmarcar otros default del mismo tipo
                $upd = $pdo->prepare("UPDATE container SET is_default=0 WHERE petrol_type_id=?");
                $upd->execute([$data['petrol_type_id']]);
            }
            $st = $pdo->prepare(
                "INSERT INTO container(name, petrol_type_id, capacity_liters, min_level_liters, is_default, status)
                 VALUES(:name,:petrol_type_id,:capacity,:min,:is_default,:status)"
            );
            $ok = $st->execute([
                ':name'=>$data['name'],
                ':petrol_type_id'=>$data['petrol_type_id'],
                ':capacity'=>$data['capacity_liters'],
                ':min'=>$data['min_level_liters'],
                ':is_default'=>!empty($data['is_default'])?1:0,
                ':status'=>$data['status'] ?? 1
            ]);
            if ($ok) {
                $id = (int)$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO container_stock(container_id, qty_liters) VALUES(?,0)")
                    ->execute([$id]);
            }
            $pdo->commit();
            return $ok;
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function update($id, array $data) {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            if (!empty($data['is_default'])) {
                $pdo->prepare("UPDATE container SET is_default=0 WHERE petrol_type_id=? AND container_id<>?")
                    ->execute([$data['petrol_type_id'], $id]);
            }
            $st = $pdo->prepare(
                "UPDATE container SET name=:name, petrol_type_id=:petrol_type_id,
                 capacity_liters=:capacity, min_level_liters=:min, is_default=:is_default, status=:status
                 WHERE container_id=:id"
            );
            $ok = $st->execute([
                ':name'=>$data['name'],
                ':petrol_type_id'=>$data['petrol_type_id'],
                ':capacity'=>$data['capacity_liters'],
                ':min'=>$data['min_level_liters'],
                ':is_default'=>!empty($data['is_default'])?1:0,
                ':status'=>$data['status'] ?? 1,
                ':id'=>$id
            ]);
            $pdo->commit();
            return $ok;
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $st = $this->getConnection()->prepare("DELETE FROM container WHERE container_id=?");
        return $st->execute([$id]);
    }

    public function petrolTypesActive() {
        return $this->getConnection()
            ->query("SELECT petrol_type_id, name FROM petrol_type_list WHERE status=1 ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function defaultForType($petrol_type_id) {
        $st = $this->getConnection()->prepare("SELECT * FROM container WHERE petrol_type_id=? AND is_default=1 LIMIT 1");
        $st->execute([$petrol_type_id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function movements($container_id, $from=null, $to=null) {
        $sql = "SELECT * FROM container_tx WHERE container_id=? ";
        $params = [$container_id];
        if ($from) { $sql.=" AND DATE(created_at)>=?"; $params[]=$from; }
        if ($to)   { $sql.=" AND DATE(created_at)<=?"; $params[]=$to; }
        $sql.=" ORDER BY created_at DESC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
