<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Sale extends Database {
    public function getActiveFuels() {
        $sql = "SELECT petrol_type_id, name, price FROM petrol_type_list WHERE status = 1 ORDER BY name ASC";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveCustomers() {
        $sql = "SELECT customer_id, customer_code, fullname FROM customer_list WHERE status = 1 ORDER BY fullname";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function newReceiptNo(PDO $db) {
        // Sencillo y único por timestamp — luego lo cambiamos si quieres
        return date('Ym') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
    }

    public function saveTransaction(array $data, int $userId) {
        $db = $this->getConnection();
        $db->beginTransaction();
        try {
            $receipt = $this->newReceiptNo($db);

            $stmt = $db->prepare("
                INSERT INTO transaction_list
                (customer_id, receipt_no, petrol_type_id, price, liter, amount, discount, total,
                 tendered_amount, `change`, type, user_id)
                VALUES
                (:customer_id, :receipt_no, :petrol_type_id, :price, :liter, :amount, :discount, :total,
                 :tendered_amount, :change, :type, :user_id)
            ");

            $stmt->execute([
                ':customer_id'     => $data['customer_id'],
                ':receipt_no'      => $receipt,
                ':petrol_type_id'  => $data['petrol_type_id'],
                ':price'           => $data['price'],
                ':liter'           => $data['liter'],
                ':amount'          => $data['amount'],
                ':discount'        => $data['discount'],
                ':total'           => $data['total'],
                ':tendered_amount' => $data['tendered_amount'],
                ':change'          => $data['change'],
                ':type'            => $data['type'], // 1 cash, 2 credit
                ':user_id'         => $userId
            ]);

            $transactionId = (int)$db->lastInsertId();

            // Si es crédito, registramos deuda
            if ((int)$data['type'] === 2) {
                $stmt2 = $db->prepare("
                    INSERT INTO debt_list (transaction_id, customer_id, amount)
                    VALUES (:transaction_id, :customer_id, :amount)
                ");
                $stmt2->execute([
                    ':transaction_id' => $transactionId,
                    ':customer_id'    => $data['customer_id'],
                    ':amount'         => $data['total']
                ]);
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
