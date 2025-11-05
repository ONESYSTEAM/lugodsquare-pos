<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class POSMOdel
{
    private $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

    // Add your custom methods below to interact with the database.
    public function getProducts()
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembershipCards()
    {
        $stmt = $this->db->prepare("SELECT * FROM members");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWalletBalance($cardNumber)
    {
        $stmt = $this->db->prepare("SELECT wallet FROM members WHERE card_number = :card_number");
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMemberByCard($cardNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE card_number = :card_number");
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateWallet($cardNumber, $balance)
    {
        $stmt = $this->db->prepare("UPDATE members SET wallet = :wallet_balance WHERE card_number = :card_number");
        $stmt->bindParam(':wallet_balance', $balance, PDO::PARAM_STR);
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function insertTransaction($transactionNo, $subTotal, $discount, $finalTotal, $paymentMethod, $userId, $cardNumber)
    {
        $stmt = $this->db->prepare("INSERT INTO sales (transaction_no, sub_total, discount, membership_card, final_total, payment_method, created_at, user_id)
                                    VALUES (:transaction_no, :sub_total, :discount, :card_number, :final_total, :payment_method, NOW(), :user_id)");
        $stmt->bindParam(':transaction_no', $transactionNo, PDO::PARAM_STR);
        $stmt->bindParam(':sub_total', $subTotal, PDO::PARAM_STR);
        $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
        $stmt->bindParam(':final_total', $finalTotal, PDO::PARAM_STR);
        $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getSalesIdByTransactionNo($transactionNo)
    {
        $stmt = $this->db->prepare("SELECT id FROM sales WHERE transaction_no = :transaction_no LIMIT 1");
        $stmt->bindParam(':transaction_no', $transactionNo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function insertSalesItem($orders, $saleId)
    {
        $stmt = $this->db->prepare("INSERT INTO sales_items (sale_id, item_name, qty, price, total) 
                                VALUES (:sale_id, :item_name, :qty, :price, :total)");

        foreach ($orders as $order) {
            $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
            $stmt->bindParam(':item_name', $order['name'], PDO::PARAM_STR);
            $stmt->bindParam(':qty', $order['qty'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $order['price'], PDO::PARAM_STR);
            $stmt->bindParam(':total', $order['total'], PDO::PARAM_STR);

            $stmt->execute(); // run each insert
        }

        return true; // ✅ return after all inserts
    }

    public function updateProductQty($productName, $newQty)
    {
        $stmt = $this->db->prepare("UPDATE products SET qty = :qty WHERE product_name = :product_name");
        $stmt->bindParam(':qty', $newQty, PDO::PARAM_INT);
        $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getProductByName($productName)
    {
        $stmt = $this->db->prepare("SELECT qty FROM products WHERE product_name = :product_name");
        $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTransactionHistory()
    {
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($adminUsername)
    {
        $stmt = $this->db->prepare("SELECT * FROM users_tbl WHERE username = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $adminUsername, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteTransaction($saleId)
    {
        $stmt = $this->db->prepare("DELETE FROM sales WHERE id = :sale_id");
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getSalesItems($saleId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sales_items WHERE sale_id = :sale_id");
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesDetails($saleId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE id = :sale_id");
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
