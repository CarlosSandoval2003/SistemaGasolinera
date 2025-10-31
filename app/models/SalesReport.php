<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SalesReport extends Database {
    /**
     * Obtiene transacciones entre fechas, opcionalmente filtradas por usuario, NIT y recibo.
     * - $from, $to: 'YYYY-mm-dd'
     * - $onlyUserId: si se filtra por usuario
     * - $nit: filtro LIKE sobre customer_code
     * - $receipt: filtro LIKE sobre receipt_no
     */
    public function getTransactions(
        string $from,
        string $to,
        ?int $onlyUserId = null,
        ?string $nit = null,
        ?string $receipt = null
    ): array {
        $db = $this->getConnection();

        $sql = "SELECT 
                    t.*,
                    p.name AS petrol,
                    COALESCE(c.customer_code, 'CF') AS nit
                FROM transaction_list t
                INNER JOIN petrol_type_list p ON p.petrol_type_id = t.petrol_type_id
                LEFT JOIN customer_list c     ON c.customer_id     = t.customer_id
                WHERE DATE(t.date_added) BETWEEN :dfrom AND :dto";
        $params = [':dfrom' => $from, ':dto' => $to];

        if ($onlyUserId !== null && $onlyUserId > 0) {
            $sql .= " AND t.user_id = :uid";
            $params[':uid'] = $onlyUserId;
        }
        if (!empty($nit)) {
            $sql .= " AND COALESCE(c.customer_code, 'CF') LIKE :nit";
            $params[':nit'] = "%{$nit}%";
        }
        if (!empty($receipt)) {
            $sql .= " AND t.receipt_no LIKE :rcpt";
            $params[':rcpt'] = "%{$receipt}%";
        }

        $sql .= " ORDER BY t.date_added ASC";
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
