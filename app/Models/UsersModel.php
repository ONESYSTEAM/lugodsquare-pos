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

    public function getUserByIdNumber($idNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_number = :id_number LIMIT 1");
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function timeIn($idNumber, $userId)
    {
        $stmt = $this->db->prepare("INSERT INTO attendance (user_id, id_number, time_in, work_date) VALUES (:user_id, :id_number, NOW(), CURDATE())");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }
    public function timeOut($idNumber)
    {
        $stmt = $this->db->prepare("UPDATE attendance SET time_out = NOW() WHERE id_number = :id_number AND time_out IS NULL");
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }
    public function getAttendanceByUserId($userId)
    {
        // We add work_date = CURDATE() to ensure we only catch shifts started today
        $stmt = $this->db->prepare("SELECT * FROM attendance 
            WHERE user_id = :user_id AND work_date = CURDATE() 
            AND time_out IS NULL 
            ORDER BY time_in DESC 
            LIMIT 1
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDailyLogs()
    {
        $sql = "SELECT a.user_id, a.id_number, a.time_in, a.time_out, u.first_name, u.last_name 
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                WHERE a.work_date = CURDATE()
                ORDER BY a.time_in DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
