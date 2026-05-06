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
    public function getFilteredSales($frequency, $cashierNames = [])
    {
        $dateCondition = "";
        switch ($frequency) {
            case 'daily':
                $dateCondition = "DATE(si.created_at) = CURDATE()";
                break;
            case 'weekly':
                $dateCondition = "DATE(si.created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND CURDATE()";
                break;
            case 'monthly':
                $dateCondition = "MONTH(si.created_at) = MONTH(CURDATE()) AND YEAR(si.created_at) = YEAR(CURDATE())";
                break;
            case 'yearly':
                $dateCondition = "YEAR(si.created_at) = YEAR(CURDATE())";
                break;
        }

        $cashierCondition = "";
        $params = [];
        if (!empty($cashierNames)) {
            $placeholders = implode(',', array_fill(0, count($cashierNames), '?'));
            $cashierCondition = " AND CONCAT(u.first_name, ' ', u.last_name) IN ($placeholders)";
            $params = $cashierNames;
        }

        $sql = "SELECT 
                DATE(si.created_at) AS sale_date,
                s.payment_method, 
                CONCAT(u.first_name, ' ', u.last_name) AS cashier_name, 
                si.item_name, 
                SUM(si.qty) AS total_qty, 
                si.price AS unit_price, 
                s.final_total AS final_total,
                ROUND(SUM(si.qty * si.price), 2) AS raw_sales, 
                ROUND(SUM(((si.qty * si.price) / NULLIF(s.sub_total, 0)) * s.discount), 2) AS total_discount, 
                ROUND(SUM((si.qty * si.price) - (((si.qty * si.price) / NULLIF(s.sub_total, 0)) * s.discount)), 2) AS total_sales
            FROM sales_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE $dateCondition $cashierCondition
            GROUP BY sale_date, s.payment_method, cashier_name, si.item_name, si.price
            ORDER BY s.payment_method ASC, sale_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
