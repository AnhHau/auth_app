<?php

namespace App\Models;

use PDO;

class User
{
    private $db;
    private $table_name = "Users";

    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $hash;
    public $updatedAt;
    public $createdAt;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET firstName=:firstName, lastName=:lastName, email=:email, hash=:hash, createdAt=:createdAt, updatedAt=:updatedAt";
        $stmt = $this->db->prepare($query);

        $this->firstName = htmlspecialchars(strip_tags($this->firstName));
        $this->lastName = htmlspecialchars(strip_tags($this->lastName));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->hash = password_hash($this->hash, PASSWORD_BCRYPT);
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');

        $stmt->bindParam(":firstName", $this->firstName);
        $stmt->bindParam(":lastName", $this->lastName);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":hash", $this->hash);
        $stmt->bindParam(":createdAt", $this->createdAt);
        $stmt->bindParam(":updatedAt", $this->updatedAt);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getUserByEmail()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        // $stmt->execute(['email' => $email]);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getUserById()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function emailExists($email)
    {
        $email = htmlspecialchars(strip_tags($email));
        $query = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}
