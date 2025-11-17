<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class UsersModel
{
    private $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsers()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($adminUsername)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $adminUsername, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
