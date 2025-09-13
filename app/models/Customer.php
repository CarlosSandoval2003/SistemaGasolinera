<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Customer extends Database
{
    public function getAll()
    {
        $sql = "SELECT * FROM customer_list ORDER BY fullname ASC";
        $stmt = $this->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM customer_list WHERE customer_id = ?";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save($data)
{
    $conn = $this->getConnection();

    if (!empty($data['id'])) {
        // UPDATE
        $sql = "UPDATE customer_list SET fullname = :fullname, email = :email, contact = :contact, address = :address, status = :status WHERE customer_id = :id";

        $params = [
            'id' => $data['id'],
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'address' => $data['address'],
            'status' => $data['status']
        ];
    } else {
        // INSERT
        $sql = "INSERT INTO customer_list (fullname, email, contact, address, status, customer_code) VALUES (:fullname, :email, :contact, :address, :status, :customer_code)";

        $params = [
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'address' => $data['address'],
            'status' => $data['status'],
            'customer_code' => $data['customer_code']
        ];
    }

    $stmt = $conn->prepare($sql);
    return $stmt->execute($params);
}


    public function delete($id)
    {
        $sql = "DELETE FROM customer_list WHERE customer_id = ?";
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
}
