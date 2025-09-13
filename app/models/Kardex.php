<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Kardex extends Database
{
    /**
     * Devuelve movimientos del kardex con joins y delta (+/-) ya calculado.
     * Filtros soportados:
     *  - date_from, date_to (YYYY-mm-dd)
     *  - container_id
     *  - petrol_type_id
     *  - kind (IN|OUT|TRANSFER_IN|TRANSFER_OUT|ADJUST)
     *  - user_id
     */
    public function list(array $f = []): array {
        $w = []; $p = [];
        if (!empty($f['date_from'])) { $w[] = "tx.created_at >= ?"; $p[] = $f['date_from'] . " 00:00:00"; }
        if (!empty($f['date_to']))   { $w[] = "tx.created_at <= ?"; $p[] = $f['date_to']   . " 23:59:59"; }
        if (!empty($f['container_id']))   { $w[] = "tx.container_id = ?";   $p[] = (int)$f['container_id']; }
        if (!empty($f['petrol_type_id'])) { $w[] = "tx.petrol_type_id = ?"; $p[] = (int)$f['petrol_type_id']; }
        if (!empty($f['kind']))           { $w[] = "tx.kind = ?";           $p[] = $f['kind']; }
        if (!empty($f['user_id']))        { $w[] = "tx.user_id = ?";        $p[] = (int)$f['user_id']; }

        $where = $w ? ("WHERE ".implode(" AND ", $w)) : "";

        $sql = "
            SELECT
                tx.tx_id,
                tx.created_at,
                tx.kind,
                tx.qty_liters,
                tx.note,
                tx.ref_id,
                tx.container_id,
                c.name AS container_name,
                tx.petrol_type_id,
                pt.name AS petrol_name,
                u.fullname AS user_fullname,
                -- delta con signo
                CASE 
                    WHEN tx.kind IN ('IN','TRANSFER_IN') THEN tx.qty_liters
                    WHEN tx.kind IN ('OUT','TRANSFER_OUT') THEN -tx.qty_liters
                    ELSE tx.qty_liters
                END AS delta
            FROM container_tx tx
            INNER JOIN `container` c ON c.container_id = tx.container_id
            INNER JOIN petrol_type_list pt ON pt.petrol_type_id = tx.petrol_type_id
            LEFT JOIN user_list u ON u.user_id = tx.user_id
            $where
            ORDER BY tx.created_at ASC, tx.tx_id ASC
        ";
        $st = $this->getConnection()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Saldo de apertura para un contenedor antes de date_from.
     * Si no se pasa container_id o date_from => 0.00
     */
    public function openingBalance(?int $container_id, ?string $date_from): float {
        if (!$container_id || !$date_from) return 0.0;
        $sql = "
            SELECT COALESCE(SUM(
                CASE 
                    WHEN kind IN ('IN','TRANSFER_IN') THEN qty_liters
                    WHEN kind IN ('OUT','TRANSFER_OUT') THEN -qty_liters
                    ELSE qty_liters
                END
            ),0) AS open_bal
            FROM container_tx
            WHERE container_id = ?
              AND created_at < ?
        ";
        $st = $this->getConnection()->prepare($sql);
        $st->execute([$container_id, $date_from . " 00:00:00"]);
        return (float)$st->fetchColumn();
    }

    /** Listas para filtros */
    public function containers(): array {
        $st = $this->getConnection()->query("SELECT container_id, name FROM `container` WHERE status=1 ORDER BY name");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function petrolTypes(): array {
        $st = $this->getConnection()->query("SELECT petrol_type_id, name FROM petrol_type_list WHERE status=1 ORDER BY name");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public function users(): array {
        $st = $this->getConnection()->query("SELECT user_id, fullname FROM user_list WHERE status=1 ORDER BY fullname");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
