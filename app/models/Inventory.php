<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Inventory extends Database {

    /** Info completa del contenedor (con joins) */
    private function getContainerFull(int $id): ?array {
        $sql = "SELECT c.container_id, c.petrol_type_id, c.status, c.capacity_liters, c.min_level_liters,
                       COALESCE(s.qty_liters,0) AS qty_liters
                  FROM container c
             LEFT JOIN container_stock s ON s.container_id = c.container_id
                 WHERE c.container_id = ?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /** Valida que el contenedor esté activo, sea del tipo indicado y tenga stock/capacidad según caso */
    private function assertContainerForIn(array $c, int $petrol_type_id, float $liters): void {
        if (!$c) throw new \RuntimeException("Contenedor no existe.");
        if ((int)$c['status'] !== 1) throw new \RuntimeException("Contenedor inactivo.");
        if ((int)$c['petrol_type_id'] !== (int)$petrol_type_id) throw new \RuntimeException("Tipo de combustible inválido para el contenedor.");
        if ($liters <= 0) throw new \RuntimeException("Litros debe ser mayor a 0.");

        // Capacidad: no rebasar
        $after = (float)$c['qty_liters'] + $liters;
        if ($after > (float)$c['capacity_liters']) {
            throw new \RuntimeException("Capacidad excedida en el contenedor destino.");
        }
    }

    private function assertContainerForOut(array $c, int $petrol_type_id, float $liters): void {
        if (!$c) throw new \RuntimeException("Contenedor no existe.");
        if ((int)$c['status'] !== 1) throw new \RuntimeException("Contenedor inactivo.");
        if ((int)$c['petrol_type_id'] !== (int)$petrol_type_id) throw new \RuntimeException("Tipo de combustible inválido para el contenedor.");
        if ($liters <= 0) throw new \RuntimeException("Litros debe ser mayor a 0.");

        // Stock suficiente
        if ((float)$c['qty_liters'] < $liters) {
            throw new \RuntimeException("Stock insuficiente en el contenedor.");
        }
    }

    // ===== ENTRADA (compras/ajustes positivos) =====
    public function in($container_id, $petrol_type_id, $liters, $note='', $ref_id=null, $user_id=null, $kind='IN') {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $c = $this->getContainerFull((int)$container_id);
            $this->assertContainerForIn($c, (int)$petrol_type_id, (float)$liters);

            // Movimiento
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
                           VALUES(?,?,?,?,?,?,?, CURRENT_TIMESTAMP)")
                ->execute([$container_id,$petrol_type_id,$kind,$liters,$note,$ref_id,$user_id]);

            // Stock
            $pdo->prepare("INSERT INTO container_stock (container_id, qty_liters, updated_at)
                                VALUES (?, ?, CURRENT_TIMESTAMP)
                           ON DUPLICATE KEY UPDATE qty_liters = qty_liters + VALUES(qty_liters),
                                                   updated_at = CURRENT_TIMESTAMP")
                ->execute([$container_id,$liters]);

            $pdo->commit();
            return ['ok'=>true];
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // ===== SALIDA (ventas/ajustes negativos) =====
    public function out($container_id, $petrol_type_id, $liters, $note='', $ref_id=null, $user_id=null, $kind='OUT') {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $c = $this->getContainerFull((int)$container_id);
            $this->assertContainerForOut($c, (int)$petrol_type_id, (float)$liters);

            // Movimiento
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
                           VALUES(?,?,?,?,?,?,?, CURRENT_TIMESTAMP)")
                ->execute([$container_id,$petrol_type_id,$kind,$liters,$note,$ref_id,$user_id]);

            // Stock
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters - ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$container_id]);

            // Aviso mínimo
            $after = (float)$c['qty_liters'] - (float)$liters;
            $warning = null;
            if ($after <= (float)$c['min_level_liters']) {
                $warning = "Aviso: el contenedor quedó en o por debajo del mínimo establecido.";
            }

            $pdo->commit();
            return ['ok'=>true, 'warning'=>$warning];
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // ===== TRANSFERENCIA entre contenedores del mismo tipo =====
    public function transfer($from_id, $to_id, $petrol_type_id, $liters, $note='', $user_id=null) {
        if ((int)$from_id === (int)$to_id) {
            throw new \RuntimeException("No puedes transferir al mismo contenedor.");
        }

        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $from = $this->getContainerFull((int)$from_id);
            $to   = $this->getContainerFull((int)$to_id);

            if (!$from || !$to) throw new \RuntimeException("Contenedor inválido.");
            if ((int)$from['status'] !== 1 || (int)$to['status'] !== 1) {
                throw new \RuntimeException("Ambos contenedores deben estar activos.");
            }
            if ((int)$from['petrol_type_id'] !== (int)$to['petrol_type_id'] ||
                (int)$from['petrol_type_id'] !== (int)$petrol_type_id) {
                throw new \RuntimeException("Los contenedores deben ser del mismo tipo de combustible.");
            }
            if ($liters <= 0) {
                throw new \RuntimeException("Los litros deben ser > 0.");
            }
            // Stock suficiente en origen
            if ((float)$from['qty_liters'] < $liters) {
                throw new \RuntimeException("Stock insuficiente en el contenedor origen.");
            }
            // Capacidad suficiente en destino
            $destAfter = (float)$to['qty_liters'] + (float)$liters;
            if ($destAfter > (float)$to['capacity_liters']) {
                throw new \RuntimeException("Capacidad excedida en el contenedor destino.");
            }

            // OUT origen
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, user_id, created_at)
                           VALUES(?,?,?,?,?, ?, CURRENT_TIMESTAMP)")
                ->execute([$from_id,$petrol_type_id,'TRANSFER_OUT',$liters,$note,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters - ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$from_id]);

            // IN destino
            $pdo->prepare("INSERT INTO container_tx(container_id, petrol_type_id, kind, qty_liters, note, user_id, created_at)
                           VALUES(?,?,?,?,?, ?, CURRENT_TIMESTAMP)")
                ->execute([$to_id,$petrol_type_id,'TRANSFER_IN',$liters,$note,$user_id]);
            $pdo->prepare("UPDATE container_stock SET qty_liters = qty_liters + ?, updated_at=CURRENT_TIMESTAMP WHERE container_id=?")
                ->execute([$liters,$to_id]);

            // Aviso: origen bajo el mínimo
            $fromAfter = (float)$from['qty_liters'] - (float)$liters;
            $warning = null;
            if ($fromAfter <= (float)$from['min_level_liters']) {
                $warning = "Transferencia realizada. Aviso: el contenedor ORIGEN quedó en o por debajo del mínimo.";
            }

            $pdo->commit();
            return ['ok'=>true, 'warning'=>$warning];
        } catch(\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
