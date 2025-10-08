<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Sale extends Database {

    public function getActiveFuels() {
        $sql = "SELECT petrol_type_id, name, sale_price_gal AS price
                FROM petrol_type_list
                WHERE status = 1
                ORDER BY name ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Precio de venta por galón desde BD (fuente de verdad) */
    public function getSalePriceGal(int $petrolTypeId): float {
        $st = $this->getConnection()->prepare(
            "SELECT sale_price_gal FROM petrol_type_list WHERE petrol_type_id=? AND status=1"
        );
        $st->execute([$petrolTypeId]);
        $p = $st->fetchColumn();
        return $p !== false ? (float)$p : 0.0;
    }

    /** Simulación SAT: devuelve fila por NIT o null */
    public function findSatByNit(string $nit): ?array {
        $st = $this->getConnection()->prepare("SELECT * FROM sat_nits WHERE nit = ?");
        $st->execute([$nit]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /** Garantiza cliente en customer_list a partir de una fila SAT; regresa customer_id */
    public function ensureCustomerFromSAT(array $satRow): int {
        $db = $this->getConnection();

        // ¿Existe?
        $st = $db->prepare("SELECT customer_id FROM customer_list WHERE customer_code = ? LIMIT 1");
        $st->execute([$satRow['nit']]);
        $cid = $st->fetchColumn();
        if ($cid) return (int)$cid;

        // Crear
        $ins = $db->prepare("
            INSERT INTO customer_list (customer_code, fullname, contact, email, address, status)
            VALUES (:code, :fullname, '', '', '', 1)
        ");
        $ins->execute([
            ':code'     => $satRow['nit'],
            ':fullname' => $satRow['fullname']
        ]);
        return (int)$db->lastInsertId();
    }

    private function newReceiptNo(PDO $db) {
        return date('Ym') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
    }

    /**
     * Guarda la transacción y DESCARGA INVENTARIO:
     * - Selecciona contenedor (default o más lleno del tipo).
     * - Verifica stock suficiente (qty_liters se maneja como GALONES).
     * - Inserta transacción.
     * - Descuenta stock y registra en container_tx (OUT).
     */
    public function saveTransaction(array $data, int $userId) {
        $db = $this->getConnection();
        $db->beginTransaction();
        try {
            // 1) Elegir contenedor (default; si no, el más lleno del tipo)
            $qDef = $db->prepare("
                SELECT c.container_id
                FROM `container` c
                WHERE c.status=1 AND c.is_default=1 AND c.petrol_type_id=?
                LIMIT 1
            ");
            $qDef->execute([$data['petrol_type_id']]);
            $container_id = $qDef->fetchColumn();

            if (!$container_id) {
                $qMax = $db->prepare("
                    SELECT cs.container_id
                    FROM container_stock cs
                    INNER JOIN `container` c ON c.container_id=cs.container_id
                    WHERE c.status=1 AND c.petrol_type_id=?
                    ORDER BY cs.qty_liters DESC
                    LIMIT 1
                ");
                $qMax->execute([$data['petrol_type_id']]);
                $container_id = $qMax->fetchColumn();
            }

            if (!$container_id) {
                throw new \RuntimeException("No hay contenedor disponible para este combustible.");
            }

            // 2) Verificar stock suficiente (qty_liters = GALONES)
            $qStock = $db->prepare("SELECT qty_liters FROM container_stock WHERE container_id=? FOR UPDATE");
            $qStock->execute([$container_id]);
            $stock = $qStock->fetchColumn();
            $stock = $stock !== false ? (float)$stock : 0.0;

            $gal = (float)$data['gallons']; // viene como '%.3f' – cast a float para comparaciones
            if ($gal <= 0) throw new \InvalidArgumentException("Galones inválidos.");
            if ($stock < $gal) {
                throw new \RuntimeException("Stock insuficiente en contenedor (disp: {$stock}, req: {$gal}).");
            }

            // 3) Recibo
            $receipt = $this->newReceiptNo($db);

            // 4) Insert transacción (NO conviertas a float: ya viene formateado)
            $stmt = $db->prepare("
                INSERT INTO transaction_list
                (customer_id, receipt_no, petrol_type_id, price, liter, amount, discount, total,
                 tendered_amount, `change`, type, user_id)
                VALUES
                (:customer_id, :receipt_no, :petrol_type_id, :price, :liter, :amount, 0, :total,
                 :tendered_amount, :change, :type, :user_id)
            ");
            $stmt->execute([
                ':customer_id'     => $data['customer_id'],
                ':receipt_no'      => $receipt,
                ':petrol_type_id'  => $data['petrol_type_id'],
                ':price'           => $data['price'],    // '%.4f'
                ':liter'           => $data['gallons'],  // guardamos GALONES en 'liter' (%.3f)
                ':amount'          => $data['amount'],   // '%.2f'
                ':total'           => $data['total'],    // '%.2f'
                ':tendered_amount' => $data['tendered_amount'], // '%.2f'
                ':change'          => $data['change'],          // '%.2f'
                ':type'            => $data['type'],     // 1=Efectivo, 3=Tarjeta
                ':user_id'         => $userId
            ]);
            $transactionId = (int)$db->lastInsertId();

            // 5) Descontar stock + kardex (OUT)
            $db->prepare("
                UPDATE container_stock
                SET qty_liters = qty_liters - ?, updated_at = CURRENT_TIMESTAMP
                WHERE container_id = ?
            ")->execute([$gal, $container_id]);

            $db->prepare("
                INSERT INTO container_tx
                (container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
                VALUES (?,?,?,?,?,?,?, CURRENT_TIMESTAMP)
            ")->execute([
                $container_id,
                $data['petrol_type_id'],
                'OUT',
                $gal,
                'Venta recibo '.$receipt,
                $transactionId,
                $userId
            ]);

            // 6) Validación post: evitar negativo por race conditions
            $qStock->execute([$container_id]);
            $newStock = (float)$qStock->fetchColumn();
            if ($newStock < -0.0005) { // pequeña tolerancia
                throw new \RuntimeException("Stock negativo detectado en contenedor.");
            }

            $db->commit();
            return [$transactionId, $receipt];

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getReceipt(int $id) {
        $sql = "
            SELECT t.*, c.customer_code, c.fullname,
                   p.name AS petrol_name,
                   u.fullname AS cashier
            FROM transaction_list t
            LEFT JOIN customer_list c ON c.customer_id = t.customer_id
            LEFT JOIN petrol_type_list p ON p.petrol_type_id = t.petrol_type_id
            LEFT JOIN user_list u ON u.user_id = t.user_id
            WHERE t.transaction_id = :id
        ";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }
}
