<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Purchase extends Database
{
    /* =======================
       HISTÓRICO (ya existente)
       ======================= */
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
        $ym    = date('Ym');
        $start = date('Y-m-01');
        $end   = date('Y-m-01', strtotime('+1 month'));

        $sql = "SELECT COUNT(*) FROM purchase_list WHERE `date` >= ? AND `date` < ?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$start, $end]);
        $seq = (int)$st->fetchColumn() + 1;

        return sprintf("PO-%s-%04d", $ym, $seq);
    }

    /** (Histórico) Compra inmediata previa */
    public function save(array $header, array $items, int $user_id): int {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $total = 0;
            foreach ($items as $it) $total += (float)$it['qty_liters'] * (float)$it['unit_cost'];
            $receipt = $this->genReceiptNo();

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

            $is = $pdo->prepare(
                "INSERT INTO purchase_items
                 (purchase_id, petrol_type_id, container_id, qty_liters, unit_cost, line_total)
                 VALUES (?,?,?,?,?,?)"
            );

            $upStock = $pdo->prepare(
                "INSERT INTO container_stock (container_id, qty_liters, updated_at)
                 VALUES (?, ?, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE 
                    qty_liters = qty_liters + VALUES(qty_liters),
                    updated_at = CURRENT_TIMESTAMP"
            );

            $mv = $pdo->prepare(
                "INSERT INTO container_tx 
                 (container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
                 VALUES (?,?,?,?,?,?,?, CURRENT_TIMESTAMP)"
            );

            $chk = $pdo->prepare("SELECT petrol_type_id FROM `container` WHERE container_id = ?");
            foreach ($items as $it) {
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
                $upStock->execute([$it['container_id'], $it['qty_liters']]);
                $mv->execute([
                    $it['container_id'],
                    $it['petrol_type_id'],
                    'IN',
                    $it['qty_liters'],
                    'Compra '.$receipt,
                    $pid,
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

    /* =======================
       NUEVO: Órdenes de compra
       ======================= */

    public function listOrders(string $df=null, string $dt=null, ?int $supplier_id=null): array {
        $w=[]; $p=[];
        if ($df) { $w[]="DATE(o.fecha) >= ?"; $p[]=$df; }
        if ($dt) { $w[]="DATE(o.fecha) <= ?"; $p[]=$dt; }
        if ($supplier_id) { $w[]="o.proveedor_id = ?"; $p[]=$supplier_id; }
        $where = $w ? "WHERE ".implode(" AND ",$w) : "";
        $sql = "SELECT o.*, s.name AS supplier_name
                FROM compras_orden o
                LEFT JOIN supplier_list s ON s.supplier_id=o.proveedor_id
                $where
                ORDER BY o.fecha DESC, o.id DESC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function genOrderCode(): string {
        $ym = date('Ym');
        $start = date('Y-m-01'); $end = date('Y-m-01', strtotime('+1 month'));
        $st = $this->getConnection()->prepare("SELECT COUNT(*) FROM compras_orden WHERE fecha >= ? AND fecha < ?");
        $st->execute([$start,$end]);
        $seq = (int)$st->fetchColumn() + 1;
        return sprintf("OC-%s-%04d",$ym,$seq);
    }

    private function getCostsForTypes(array $typeIds): array {
    if (empty($typeIds)) return [];
    $in = implode(',', array_fill(0, count($typeIds), '?'));
    $st = $this->getConnection()->prepare(
        "SELECT petrol_type_id, purchase_price_gal 
         FROM petrol_type_list 
         WHERE petrol_type_id IN ($in)"
    );
    $st->execute($typeIds);
    $map = [];
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){
        $map[(int)$r['petrol_type_id']] = (float)$r['purchase_price_gal'];
    }
    return $map;
}

public function saveOrder(array $header, array $items, int $user_id): int {
    $pdo = $this->getConnection();
    $pdo->beginTransaction();
    try{
        // 1) Traer costos por galón desde BD según los tipos del pedido
        $typeIds = array_values(array_unique(array_map(fn($x)=>(int)$x['petrol_type_id'], $items)));
        $costs   = $this->getCostsForTypes($typeIds);

        // 2) Calcular totales usando SIEMPRE el costo de la BD
        $subtotal=0; $impuestos=0;
        foreach($items as &$it){
            $ptId = (int)$it['petrol_type_id'];
            $unit = $costs[$ptId] ?? 0.0;               // Q/gal desde BD
            $qty  = (float)$it['qty_liters'];           // aquí tratamos la cantidad como GALONES
            $it['unit_cost'] = $unit;                   // forzar costo desde BD
            $subtotal += $qty * $unit;
        }
        unset($it);
        $total  = $subtotal + $impuestos;
        $codigo = $this->genOrderCode();

        // 3) Insert header (estatus inicial SOLICITADO)
        $hs = $pdo->prepare("INSERT INTO compras_orden
          (codigo, proveedor_id, fecha, estatus, observaciones, subtotal, impuestos, total, created_by)
          VALUES (?, ?, ?, 'SOLICITADO', ?, ?, ?, ?, ?)");
        $hs->execute([$codigo, $header['supplier_id'], $header['date'], $header['notes'] ?? null, $subtotal, $impuestos, $total, $user_id]);
        $oid = (int)$pdo->lastInsertId();

        // 4) Insert items (guardando precio_unitario = costo BD)
        $is = $pdo->prepare("INSERT INTO compras_orden_items
          (orden_id, petrol_type_id, descripcion, cantidad_litros, precio_unitario, impuestos)
          VALUES (?,?,?,?,?,0)");

        foreach($items as $it){
            $is->execute([
                $oid,
                (int)$it['petrol_type_id'],
                null,
                (float)$it['qty_liters'],        // tratamos este campo como GALONES
                (float)$it['unit_cost']          // Q/gal desde BD
            ]);
        }

        $pdo->commit();
        return $oid;
    }catch(Exception $e){
        $pdo->rollBack();
        throw $e;
    }
}

    public function getOrderHeader(int $id): ?array {
        $sql = "SELECT o.*, s.name AS supplier_name
                FROM compras_orden o
                LEFT JOIN supplier_list s ON s.supplier_id=o.proveedor_id
                WHERE o.id=?";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function getOrderItems(int $orden_id): array {
        $sql = "SELECT i.*, t.name AS petrol_name
                FROM compras_orden_items i
                INNER JOIN petrol_type_list t ON t.petrol_type_id=i.petrol_type_id
                WHERE i.orden_id=? ORDER BY i.id ASC";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$orden_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReceipt(int $ordenId, array $recibo, array $itemsRecibo, int $user_id): int {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try{
            // validar estatus
            $st = $pdo->prepare("SELECT estatus FROM compras_orden WHERE id=? FOR UPDATE");
            $st->execute([$ordenId]);
            $estatus = $st->fetchColumn();
            if(!$estatus) throw new Exception("La orden no existe.");
            if($estatus==='RECIBIDO' || $estatus==='CANCELADO') throw new Exception("La orden no es editable.");

            // insertar recibo
            $hs = $pdo->prepare("INSERT INTO compras_recibo (orden_id, fecha, documento_proveedor, observaciones, created_by)
                                 VALUES (?,?,?,?,?)");
            $hs->execute([$ordenId, $recibo['fecha'], $recibo['documento_proveedor'], $recibo['observaciones'], $user_id]);
            $reciboId = (int)$pdo->lastInsertId();

            // helpers
            $upStock = $pdo->prepare(
              "INSERT INTO container_stock (container_id, qty_liters, updated_at)
               VALUES (?, ?, CURRENT_TIMESTAMP)
               ON DUPLICATE KEY UPDATE qty_liters = qty_liters + VALUES(qty_liters),
                                       updated_at = CURRENT_TIMESTAMP"
            );
            $mv = $pdo->prepare(
              "INSERT INTO container_tx 
               (container_id, petrol_type_id, kind, qty_liters, note, ref_id, user_id, created_at)
               VALUES (?,?,?,?,?,?,?, CURRENT_TIMESTAMP)"
            );
            $chk = $pdo->prepare("SELECT petrol_type_id FROM `container` WHERE container_id=?");

            $is = $pdo->prepare("INSERT INTO compras_recibo_items
              (recibo_id, orden_item_id, contenedor_id, petrol_type_id, cantidad_recibida_litros, costo_unitario)
              VALUES (?,?,?,?,?,?)");

            foreach($itemsRecibo as $it){
                if(($it['rec_litros'] ?? 0) <= 0) continue;

                // validar contenedor vs tipo
                $chk->execute([$it['contenedor_id']]);
                $pt_of_container = (int)$chk->fetchColumn();
                if ($pt_of_container !== (int)$it['petrol_type_id']) {
                    throw new Exception("El contenedor no coincide con el tipo de gasolina.");
                }

                $is->execute([
                    $reciboId,
                    $it['orden_item_id'],
                    $it['contenedor_id'],
                    $it['petrol_type_id'],
                    $it['rec_litros'],
                    $it['costo_u']
                ]);

                // stock + kardex
                $upStock->execute([$it['contenedor_id'], $it['rec_litros']]);
                $mv->execute([
                    $it['contenedor_id'],
                    $it['petrol_type_id'],
                    'IN',
                    $it['rec_litros'],
                    'Recibo OC '.$ordenId,
                    $reciboId,
                    $user_id
                ]);
            }

            // marcar OC como recibida
            $pdo->prepare("UPDATE compras_orden SET estatus='RECIBIDO' WHERE id=?")->execute([$ordenId]);

            $pdo->commit();
            return $reciboId;
        }catch(Exception $e){
            $pdo->rollBack();
            throw $e;
        }
    }
}
