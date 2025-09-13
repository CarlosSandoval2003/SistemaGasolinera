<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Balance extends Database
{
    public function getAllCustomersWithBalances()
    {
        $sql = "SELECT * FROM customer_list ORDER BY fullname ASC";
        $stmt = $this->getConnection()->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($customers as &$customer) {
            $customer['balance'] = $this->getCustomerBalance($customer['customer_id']);
        }

        return $customers;
    }

    public function getCustomerById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM customer_list WHERE customer_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerBalance($customer_id)
    {
        $debts = $this->getConnection()->prepare("SELECT SUM(amount) FROM debt_list WHERE customer_id = ?");
        $debts->execute([$customer_id]);
        $debtTotal = $debts->fetchColumn() ?? 0;

        $payments = $this->getConnection()->prepare("SELECT SUM(amount) FROM payment_list WHERE customer_id = ?");
        $payments->execute([$customer_id]);
        $paymentTotal = $payments->fetchColumn() ?? 0;

        return $debtTotal - $paymentTotal;
    }

    public function getDebtList($customer_id)
{
    $sql = "SELECT d.*, t.receipt_no FROM debt_list d 
            INNER JOIN transaction_list t ON d.transaction_id = t.transaction_id 
            WHERE d.customer_id = ? ORDER BY d.date_added ASC";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute([$customer_id]);
    $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($debts as &$debt) {
        $dt = new \DateTime($debt['date_added'], new \DateTimeZone('UTC'));
        $dt->setTimezone(new \DateTimeZone('America/Guatemala')); // o tu zona local
        $debt['formatted_date'] = $dt->format('Y-m-d h:i A');
    }

    return $debts;
}


    public function getPaymentList($customer_id)
{
    $sql = "SELECT * FROM payment_list WHERE customer_id = ? ORDER BY date_added ASC";
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->execute([$customer_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($payments as &$payment) {
        $dt = new \DateTime($payment['date_added'], new \DateTimeZone('UTC'));
        $dt->setTimezone(new \DateTimeZone('America/Guatemala')); // cambia si usas otro país
        $payment['formatted_date'] = $dt->format('Y-m-d h:i A');
    }

    return $payments;
}


    public function savePayment($customer_id, $amount)
    {
        $sql = "INSERT INTO payment_list (customer_id, amount, date_added) VALUES (?, ?, NOW())";
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute([$customer_id, $amount]);
    }
}
