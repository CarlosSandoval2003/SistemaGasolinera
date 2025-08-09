<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Dashboard extends Database {
    public function countPetrolTypes() {
        $stmt = $this->getConnection()->query("SELECT COUNT(petrol_type_id) as count FROM petrol_type_list WHERE status = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function countCustomers() {
        $stmt = $this->getConnection()->query("SELECT COUNT(customer_id) as count FROM customer_list WHERE status = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function getBalance() {
        $debts = $this->getConnection()->query("SELECT SUM(amount) as total FROM debt_list")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $payments = $this->getConnection()->query("SELECT SUM(amount) as total FROM payment_list")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        return $debts - $payments;
    }

    public function getTodaySales() {
        $stmt = $this->getConnection()->prepare("SELECT SUM(total) as total FROM transaction_list WHERE DATE(date_added) = ?");
        $stmt->execute([date("Y-m-d")]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }
}
