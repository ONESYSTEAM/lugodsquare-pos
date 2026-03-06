<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class SalesModel
{
    private $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

    // Add your custom methods below to interact with the database.
    public function getSalesData($userId, $mode)
    {
        // We join sales (s) and sales_items (si)
        // s.id is used to link to si.sale_id
        $sql = "SELECT 
                s.transaction_no, 
                s.sub_total, 
                s.discount, 
                s.final_total, 
                s.payment_method, 
                s.created_at,
                si.item_name, 
                si.qty, 
                si.price, 
                si.total AS item_total
            FROM sales s
            LEFT JOIN sales_items si ON s.id = si.sale_id
            WHERE s.user_id = :user_id 
            AND DATE(s.created_at) = CURDATE()";

        // Filter by payment_method (matches your column name)
        if ($mode === 'cash') {
            $sql .= " AND s.payment_method = 'cash'";
        } elseif ($mode === 'gcash') {
            $sql .= " AND s.payment_method = 'gcash'";
        }

        $sql .= " ORDER BY s.created_at DESC, s.transaction_no DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
