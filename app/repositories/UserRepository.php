<?php

namespace App\Repositories;

use App\Models\User;
use Firebase\JWT\JWT;

class UserRepository
{
    private $db;
    private $user;

    public function __construct($db)
    {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function createUser($data)
    {
        $result = [];
        if (!$this->user->emailExists($data['email'])) {
            $this->user->firstName = $data['firstName'];
            $this->user->lastName = $data['lastName'];
            $this->user->email = $data['email'];
            $this->user->hash = $data['password'];
            $this->user->id = $this->user->create();
            if ($this->user->id) {
                $result = [
                    "id" => $this->user->id,
                    "firstName" => $this->user->firstName,
                    "lastName" => $this->user->lastName,
                    "email" => $this->user->email,
                    "displayName" => $this->user->firstName . ' ' . $this->user->lastName
                ];
            }
        }
        return $result;
    }

    public function getUserByEmail($email)
    {
        $this->user->email = $email;
        return $this->user->getUserByEmail();
    }

    public function getUserById($id)
    {
        $this->user->id = $id;
        return $this->user->getUserById();
    }
}
