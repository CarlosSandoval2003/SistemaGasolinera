<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Dashboard extends Database {

    /** Total de ventas del día (Q) */
    public function getTodaySales(): float {
        $stmt = $this->getConnection()->prepare(
            "SELECT COALESCE(SUM(total),0) AS total
               FROM transaction_list
              WHERE DATE(date_added) = ?"
        );
        $stmt->execute([date("Y-m-d")]);
        return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    /** Stock de contenedores con info de mínimo y capacidad (solo activos) */
    public function getContainerStocks(): array {
        $sql = "SELECT
                  c.container_id,
                  c.name AS container,
                  p.name AS petrol_name,
                  c.capacity_liters,
                  c.min_level_liters,
                  COALESCE(s.qty_liters,0) AS qty_liters
                FROM container c
                INNER JOIN petrol_type_list p ON p.petrol_type_id = c.petrol_type_id
                LEFT JOIN container_stock s   ON s.container_id = c.container_id
                WHERE c.status = 1
                ORDER BY p.name, c.name";
        $st = $this->getConnection()->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Ventas por día (últimos N días), formato [{date:'YYYY-MM-DD', total:Q}] */
    public function getSalesLastNDays(int $days = 7): array {
        $days = max(1, min($days, 60)); // acotamos
        $sql = "SELECT DATE(date_added) AS date, COALESCE(SUM(total),0) AS total
                  FROM transaction_list
                 WHERE date_added >= DATE_SUB(CURDATE(), INTERVAL :n DAY)
                 GROUP BY DATE(date_added)
                 ORDER BY DATE(date_added)";
        $st = $this->getConnection()->prepare($sql);
        $st->bindValue(':n', $days-1, PDO::PARAM_INT); // p.ej. hoy + 6 días atrás = 7 días
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar: asegurar que vengan todas las fechas (aunque 0)
        $map = [];
        foreach ($rows as $r) $map[$r['date']] = (float)$r['total'];

        $out = [];
        for ($i=$days-1; $i>=0; $i--){
            $d = date('Y-m-d', strtotime("-$i day"));
            $out[] = ['date'=>$d, 'total'=> $map[$d] ?? 0.0];
        }
        return $out;
    }

    /** Ventas de hoy por combustible, formato [{petrol, total}] */
    public function getSalesByFuelToday(): array {
        $sql = "SELECT p.name AS petrol, COALESCE(SUM(t.total),0) AS total
                  FROM transaction_list t
                  LEFT JOIN petrol_type_list p ON p.petrol_type_id = t.petrol_type_id
                 WHERE DATE(t.date_added) = CURDATE()
                 GROUP BY p.name
                 ORDER BY total DESC";
        $st = $this->getConnection()->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
