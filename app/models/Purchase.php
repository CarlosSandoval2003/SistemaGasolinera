<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Purchase extends Database
{
    public function list(string $df=null, string $dt=null, ?int $supplier_id=null): array {
        $w = []; $p = [];
        if ($df) { $w[] = "DATE(p.`date`) >= ?"; $p[]=$df; }
        if ($dt) { $w[] = "DATE(p.`date`) <= ?"; $p[]=$dt; }
        if ($supplier_id) { $w[] = "p.supplier_id = ?"; $p[]=$supplier_id; }
        $where = $w ? "WHERE ".implode(" AND ", $w) : "";
        $sql = "SELECT p.*, s.name AS supplier_name
                FROM purchase_list p
                INNER JOIN supplier_list s ON s.supplier_id = p.supplier_id
                $where
                ORDER BY p.`date` DESC, p.purchase_id DESC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHeader(int $id): ?array {
        $sql = "SELECT p.*, s.name AS supplier_name, s.code AS supplier_code
                FROM purchase_list p
                INNER JOIN supplier_list s ON s.supplier_id = p.supplier_id
                WHERE p.purchase_id = ?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function getItems(int $purchase_id): array {
        $sql = "SELECT i.*, t.name AS petrol_name, c.name AS container_name
                FROM purchase_items i
                INNER JOIN petrol_type_list t ON t.petrol_type_id = i.petrol_type_id
                INNER JOIN `container` c     ON c.container_id     = i.container_id
                WHERE i.purchase_id = ?
                ORDER BY i.item_id ASC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$purchase_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function genReceiptNo(): string {
        // PO-YYYYMM-XXXX
        $ym    = date('Ym');
        $start = date('Y-m-01');
        $end   = date('Y-m-01', strtotime('+1 month'));

        $sql = "SELECT COUNT(*)
                FROM purchase_list
                WHERE `date` >= ? AND `date` < ?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$start, $end]);
        $seq = (int)$st->fetchColumn() + 1;

        return sprintf("PO-%s-%04d", $ym, $seq);
    }

    /**
     * $header: supplier_id, date, payment_type, notes
     * $items:  [ [petrol_type_id, container_id, qty_liters, unit_cost], ... ]
     * $user_id: from session
     */
    public function save(array $header, array $items, int $user_id): int {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            // total de la compra
            $total = 0;
            foreach ($items as $it) {
                $total += (float)$it['qty_liters'] * (float)$it['unit_cost'];
            }
            $receipt = $this->genReceiptNo();

            // header
            $hs = $pdo->prepare(
                "INSERT INTO purchase_list 
                 (supplier_id, receipt_no, `date`, payment_type, notes, status, total_cost, user_id)
                 VALUES (?,?,?,?,?,1,?,?)"
            );
            $hs->execute([
                $header['supplier_id'],
                $receipt,
                $header['date'],
                $header['payment_type'],
                $header['notes'] ?? null,
                $total,
                $user_id
            ]);
            $pid = (int)$pdo->lastInsertId();

            // statements reusables
            $is = $pdo->prepare(
                "INSERT INTO purchase_items
                 (purchase_id, petrol_type_id, container_id, qty_liters, unit_cost, line_total)
                 VALUES (?,?,?,?,?,?)"
            );

            // stock: insert/update en container_stock
            $upStock = $pdo->prepare(
                "INSERT INTO container_stock (container_id, qty_liters, updated_at)
                 VALUES (?, ?, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                    qty_liters = qty_liters + VALUES(qty_liters),
                    updated_at = CURRENT_TIMESTAMP"
            );

            // kardex en container_tx
            $mv = $pdo->prepare(
                "INSERT INTO container_tx 
                 (container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
                 VALUES (?,?,?,?,?,?,?, CURRENT_TIMESTAMP)"
            );

            // sanity & apply
            $chk = $pdo->prepare("SELECT petrol_type_id FROM `container` WHERE container_id = ?");
            foreach ($items as $it) {
                // contenedor debe ser del mismo tipo de combustible
                $chk->execute([$it['container_id']]);
                $pt_of_container = (int)$chk->fetchColumn();
                if ($pt_of_container !== (int)$it['petrol_type_id']) {
                    throw new Exception("Container fuel mismatch.");
                }

                $line_total = (float)$it['qty_liters'] * (float)$it['unit_cost'];
                $is->execute([
                    $pid,
                    $it['petrol_type_id'],
                    $it['container_id'],
                    $it['qty_liters'],
                    $it['unit_cost'],
                    $line_total
                ]);

                // actualizar stock del contenedor
                $upStock->execute([$it['container_id'], $it['qty_liters']]);

                // registrar movimiento
                $note = "Compra {$receipt}";
                $mv->execute([
                    $it['container_id'],
                    $it['petrol_type_id'],
                    'IN',                // entrada por compra
                    $it['qty_liters'],
                    $note,
                    $pid,                // ref_id = id de la compra
                    $user_id
                ]);
            }

            $pdo->commit();
            return $pid;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
