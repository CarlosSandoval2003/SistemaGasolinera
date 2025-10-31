<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Supplier extends Database
{
    public function all(): array {
        $st = $this->getConnection()->query("SELECT * FROM supplier_list ORDER BY name ASC");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listFiltered(string $q = '', ?int $status = null): array {
        $pdo = $this->getConnection();
        $sql = "SELECT * FROM supplier_list WHERE 1=1";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (code LIKE :q OR name LIKE :q OR contact LIKE :q OR email LIKE :q OR address LIKE :q)";
            $params[':q'] = "%$q%";
        }
        if ($status !== null) {
            $sql .= " AND status = :st";
            $params[':st'] = $status;
        }

        $sql .= " ORDER BY name ASC";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array {
        $st = $this->getConnection()->prepare("SELECT * FROM supplier_list WHERE supplier_id=?");
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Verifica duplicados por nombre, contacto y email (ignorando un ID opcional).
     * Devuelve ['has'=>bool, 'name'=>bool, 'contact'=>bool, 'email'=>bool]
     * - No considera duplicado cuando los campos vienen vacíos (contact/email opcionales).
     */
    public function checkDuplicates(string $name, ?string $contact, ?string $email, ?int $ignoreId = null): array {
        $pdo = $this->getConnection();
        $res = ['has'=>false, 'name'=>false, 'contact'=>false, 'email'=>false];

        // Nombre (requerido)
        $sql = "SELECT 1 FROM supplier_list WHERE name = :v";
        $p = [':v'=>$name];
        if ($ignoreId !== null) { $sql .= " AND supplier_id <> :id"; $p[':id']=$ignoreId; }
        $st = $pdo->prepare($sql); $st->execute($p);
        if ($st->fetchColumn()) { $res['has']=true; $res['name']=true; }

        // Contacto (si viene)
        if ($contact !== null && $contact !== '') {
            $sql = "SELECT 1 FROM supplier_list WHERE contact = :v";
            $p = [':v'=>$contact];
            if ($ignoreId !== null) { $sql .= " AND supplier_id <> :id"; $p[':id']=$ignoreId; }
            $st = $pdo->prepare($sql); $st->execute($p);
            if ($st->fetchColumn()) { $res['has']=true; $res['contact']=true; }
        }

        // Email (si viene)
        if ($email !== null && $email !== '') {
            $sql = "SELECT 1 FROM supplier_list WHERE email = :v";
            $p = [':v'=>$email];
            if ($ignoreId !== null) { $sql .= " AND supplier_id <> :id"; $p[':id']=$ignoreId; }
            $st = $pdo->prepare($sql); $st->execute($p);
            if ($st->fetchColumn()) { $res['has']=true; $res['email']=true; }
        }

        return $res;
    }

    public function save(array $d): bool {
        $pdo = $this->getConnection();

        if (!empty($d['supplier_id'])) {
            $sql = "UPDATE supplier_list 
                    SET code=:code, name=:name, contact=:contact, email=:email, address=:address, status=:status 
                    WHERE supplier_id=:supplier_id";
            $params = [
                ':code'=>$d['code'], ':name'=>$d['name'], ':contact'=>$d['contact'],
                ':email'=>$d['email'], ':address'=>$d['address'], ':status'=>$d['status'],
                ':supplier_id'=>$d['supplier_id']
            ];
        } else {
            $sql = "INSERT INTO supplier_list (code,name,contact,email,address,status) 
                    VALUES (:code,:name,:contact,:email,:address,:status)";
            $params = [
                ':code'=>$d['code'], ':name'=>$d['name'], ':contact'=>$d['contact'],
                ':email'=>$d['email'], ':address'=>$d['address'], ':status'=>$d['status']
            ];
        }

        $st = $pdo->prepare($sql);
        return $st->execute($params);
    }

    public function delete(int $id): bool {
        $st = $this->getConnection()->prepare("DELETE FROM supplier_list WHERE supplier_id=?");
        return $st->execute([$id]);
    }
}
