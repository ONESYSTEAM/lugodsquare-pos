<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class POSModel
{
    private $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

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
        $stmt = $this->db->prepare("UPDATE members SET wallet = :wallet_balance, synced = 0 WHERE card_number = :card_number");
        $stmt->bindParam(':wallet_balance', $balance, PDO::PARAM_STR);
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function insertTransaction($transactionNo, $subTotal, $discount, $finalTotal, $paymentMethod, $userId, $cardNumber, $cashAmount = null, $cashChange = null)
    {
        $stmt = $this->db->prepare("INSERT INTO sales (transaction_no, sub_total, discount, membership_card, final_total,
            payment_method, cash_amount, cash_change, created_at, user_id, synced) 
            VALUES (:transaction_no, :sub_total, :discount, :card_number, :final_total,
            :payment_method, :cash_amount, :cash_change, NOW(), :user_id, 0
        )
    ");

        $stmt->bindParam(':transaction_no', $transactionNo, PDO::PARAM_STR);
        $stmt->bindParam(':sub_total', $subTotal, PDO::PARAM_STR);
        $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
        $stmt->bindParam(':final_total', $finalTotal, PDO::PARAM_STR);
        $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
        $stmt->bindParam(':card_number', $cardNumber, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->bindValue(':cash_amount', $cashAmount !== null ? number_format((float)$cashAmount, 2, '.', '') : null);
        $stmt->bindValue(':cash_change', $cashChange !== null ? number_format((float)$cashChange, 2, '.', '') : null);

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
        $stmt = $this->db->prepare("INSERT INTO sales_items (sale_id, item_name, qty, price, total, synced) 
                                    VALUES (:sale_id, :item_name, :qty, :price, :total, 0)");

        foreach ($orders as $order) {
            $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
            $stmt->bindParam(':item_name', $order['name'], PDO::PARAM_STR);
            $stmt->bindParam(':qty', $order['qty'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $order['price'], PDO::PARAM_STR);
            $stmt->bindParam(':total', $order['total'], PDO::PARAM_STR);

            $stmt->execute();
        }

        return true;
    }

    public function updateProductQty($productName, $newQty)
    {
        $stmt = $this->db->prepare("UPDATE products SET qty = :qty, synced = 0 WHERE product_name = :product_name");
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
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE user_id = :user_id AND is_deleted = 0 ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $fetchAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteTransaction($saleId)
    {
        $stmt = $this->db->prepare("UPDATE sales SET is_deleted = 1, synced = 0 WHERE id = :sale_id");
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

    public function getSaleByTransactionNo($transactionNo)
    {
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE transaction_no = :transaction_no LIMIT 1");
        $stmt->bindParam(':transaction_no', $transactionNo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCashierByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminUserByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function payment($transactionNo, $paymentMethod, $amount, $cashierId)
    {
        $stmt = $this->db->prepare("INSERT INTO sales (payment_method, final_total, transaction_no, user_id, synced) 
                                    VALUES (:payment_method, :final_total, :transaction_no, :user_id, 0)");
        $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
        $stmt->bindParam(':final_total', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':transaction_no', $transactionNo, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $cashierId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function paymentItems($itemName, $amount, $saleId)
    {
        $stmt = $this->db->prepare("INSERT INTO sales_items ( sale_id, item_name, qty, price, total, synced) 
                                    VALUES (:sale_id, :item_name, 1, 0, :amount, 0)");
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->bindParam(':item_name', $itemName, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
