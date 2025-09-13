<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SalesReport extends Database {
    public function getTransactions(string $from, string $to, ?int $onlyUserId = null): array {
        $db = $this->getConnection();

        $sql = "SELECT t.*, p.name AS petrol
                FROM transaction_list t
                INNER JOIN petrol_type_list p ON p.petrol_type_id = t.petrol_type_id
                WHERE DATE(t.date_added) BETWEEN :dfrom AND :dto";
        $params = [':dfrom' => $from, ':dto' => $to];

        if ($onlyUserId !== null) {
            $sql .= " AND t.user_id = :uid";
            $params[':uid'] = $onlyUserId;
        }

        $sql .= " ORDER BY t.date_added ASC";
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
