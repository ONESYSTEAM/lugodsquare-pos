<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class ShiftModel
{
    private $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

    // Add your custom methods below to interact with the database.

    public function startShiftIfNotExists($user_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM cashier_shifts WHERE user_id = :user_id AND status = 'open'");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shift) {
            $stmt = $this->db->prepare("INSERT INTO cashier_shifts (user_id, start_time, status) VALUES (:user_id, NOW(), 'open')");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            return $this->db->lastInsertId(); // new shift created
        }

        return $shift['id']; // existing shift
    }

    public function endCurrentShift($user_id)
    {
        $stmt = $this->db->prepare("
        UPDATE cashier_shifts
        SET 
            end_time = NOW(),
            total_sales = (
                SELECT COALESCE(SUM(final_total), 0)
                FROM sales
                WHERE sales.user_id = cashier_shifts.user_id
                AND sales.created_at BETWEEN cashier_shifts.start_time AND NOW()
            ),
            status = 'closed'
        WHERE user_id = :user_id
        AND status = 'open'
    ");

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function hasEndedShiftToday($user_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cashier_shifts 
        WHERE user_id = :user_id AND DATE(start_time) = CURDATE() AND status = 'closed' ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
