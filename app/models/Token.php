<?php

namespace App\Models;

use PDO;

class Token
{
    private $db;
    private $table_name = "Tokens";

    public $id;
    public $userId;
    public $refreshToken;
    public $expiresIn;
    public $updatedAt;
    public $createdAt;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET userId=:userId, refreshToken=:refreshToken, expiresIn=:expiresIn, createdAt=:createdAt, updatedAt=:updatedAt";
        $stmt = $this->db->prepare($query);

        $this->userId = htmlspecialchars(strip_tags($this->userId));
        $this->refreshToken = htmlspecialchars(strip_tags($this->refreshToken));
        $this->expiresIn = htmlspecialchars(strip_tags($this->expiresIn));
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');

        $stmt->bindParam(":userId", $this->userId);
        $stmt->bindParam(":refreshToken", $this->refreshToken);
        $stmt->bindParam(":expiresIn", $this->expiresIn);
        $stmt->bindParam(":createdAt", $this->createdAt);
        $stmt->bindParam(":updatedAt", $this->updatedAt);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTokenByUserId()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE userId = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $this->userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getTokenByUserRefreshToken()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE refreshToken = ? AND expiresIn > NOW() LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $this->refreshToken);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function addToken()
    {
        $query = 'INSERT INTO tokens (userId, refreshToken, expiresIn) VALUES (:userId, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))';
        $stmt = $this->db->prepare($query);
        if ($stmt->execute(['userId' => $this->userId, 'token' => $this->refreshToken])) {
            return true;
        }
        return false;
    }

    public function saveRefreshToken()
    {
        $query = "UPDATE " . $this->table_name . " SET refreshToken = :refreshToken, expiresIn = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE userId = :userId ";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute(['refreshToken' => $this->refreshToken, 'userId' => $this->userId])) {
            return true;
        }
        return false;
    }

    public function removalToken()
    {
        if (empty($this->userId)) {
            return false;
        }
        $query = "DELETE FROM " . $this->table_name . " WHERE userId = :userId ";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute(['userId' => $this->userId])) {
            return true;
        }
        return false;
    }
}
