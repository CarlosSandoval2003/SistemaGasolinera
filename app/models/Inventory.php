<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Inventory extends Database {
    // entrada directa (compras/ajuste)
    public function in($container_id, $petrol_type_id, $liters, $note='', $ref_id=null, $user_id=null, $kind='IN') {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id)
                           VALUES(?,?,?,?,?,?,?)")
                ->execute([$container_id,$petrol_type_id,$kind,$liters,$note,$ref_id,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters + ? , updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$container_id]);
            $pdo->commit();
            return true;
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // salida (ventas)
    public function out($container_id, $petrol_type_id, $liters, $note='', $ref_id=null, $user_id=null, $kind='OUT') {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            // valida stock
            $cur = $pdo->prepare("SELECT qty_liters FROM container_stock WHERE container_id=?");
            $cur->execute([$container_id]);
            $qty = (float)($cur->fetch(PDO::FETCH_ASSOC)['qty_liters'] ?? 0);
            if ($qty < $liters) {
                throw new \RuntimeException("Stock insuficiente en el contenedor.");
            }

            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id)
                           VALUES(?,?,?,?,?,?,?)")
                ->execute([$container_id,$petrol_type_id,$kind,$liters,$note,$ref_id,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters - ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$container_id]);
            $pdo->commit();
            return true;
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // transferir entre contenedores del mismo tipo
    public function transfer($from_id, $to_id, $petrol_type_id, $liters, $note='', $user_id=null) {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            // validar tipo y stock origen
            $st = $pdo->prepare("SELECT c.petrol_type_id, s.qty_liters FROM container c
                                 LEFT JOIN container_stock s ON s.container_id=c.container_id
                                 WHERE c.container_id=?");
            $st->execute([$from_id]); $from = $st->fetch(PDO::FETCH_ASSOC);
            $st->execute([$to_id]);   $to   = $st->fetch(PDO::FETCH_ASSOC);

            if (!$from || !$to || (int)$from['petrol_type_id'] !== (int)$to['petrol_type_id'] || (int)$from['petrol_type_id'] !== (int)$petrol_type_id) {
                throw new \RuntimeException("Los contenedores deben ser del mismo tipo de combustible.");
            }
            if ((float)$from['qty_liters'] < $liters) {
                throw new \RuntimeException("Stock insuficiente en el contenedor origen.");
            }

            // registrar OUT e IN
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, user_id)
                           VALUES(?,?,?,?,?,?)")
                ->execute([$from_id,$petrol_type_id,'TRANSFER_OUT',$liters,$note,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters - ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$from_id]);

            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, user_id)
                           VALUES(?,?,?,?,?,?)")
                ->execute([$to_id,$petrol_type_id,'TRANSFER_IN',$liters,$note,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters + ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$to_id]);

            $pdo->commit();
            return true;
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
