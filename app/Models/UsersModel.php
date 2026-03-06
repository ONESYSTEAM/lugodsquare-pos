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

    public function getUserByIdNumberAndNoActiveShift($idNumber)
    {
        // We look for a record where time_out is NULL 
        // AND the time_in happened TODAY.
        $stmt = $this->db->prepare("SELECT u.id FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id 
            AND a.time_out IS NULL 
            AND DATE(a.time_in) = CURDATE()
        WHERE u.id_number = :id_number 
        AND a.id IS NULL 
        LIMIT 1");

        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserWithActiveShift($idNumber)
    {
        // We want a user WHERE an active attendance record (time_out IS NULL) EXISTS
        $stmt = $this->db->prepare("SELECT u.id, a.id as attendance_id 
        FROM users u
        INNER JOIN attendance a ON u.id = a.user_id 
        WHERE u.id_number = :id_number AND a.work_date = CURDATE()
        AND a.time_out IS NULL
        LIMIT 1");
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function timeIn($idNumber, $userId)
    {
        $stmt = $this->db->prepare("INSERT INTO attendance (user_id, id_number, time_in, work_date, synced) VALUES (:user_id, :id_number, NOW(), CURDATE(), 0)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }
    public function timeOut($idNumber)
    {
        $stmt = $this->db->prepare("UPDATE attendance SET time_out = NOW(), synced = 0 WHERE id_number = :id_number AND time_out IS NULL");
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function confirmUserByIdNumber($idNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_number = :id_number");
        $stmt->bindParam(':id_number', $idNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
