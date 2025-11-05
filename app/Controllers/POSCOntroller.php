<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\POSModel;

class POSCOntroller
{
    private $POSModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->POSModel = new POSModel($db);
    }

    // Add your custom controllers below to handle business logic.
    public function dashboard()
    {
        $userId = $_SESSION['user_id'] ?? '';
        if ($userId == '') {
            header('Location: /');
            exit;
        }
        $products = $this->POSModel->getProducts();
        $transactions = $this->POSModel->getTransactionHistory();

        echo $GLOBALS['templates']->render('Layout/DashboardLayout', ['products' => $products, 'transactions' => $transactions]);
    }

    public function getMembershipCard()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json'); // ✅ helps AJAX parse response properly

            $CardNumber = $_POST['card_number'] ?? '';
            $memberCards = $this->POSModel->getMembershipCards();

            // Safety: ensure it's iterable
            if (!is_array($memberCards)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid data from model']);
                return;
            }

            // Check if card exists
            foreach ($memberCards as $card) {
                if (isset($card['card_number']) && $card['card_number'] === $CardNumber) {
                    echo json_encode(['status' => 'success', 'is_valid' => true]);
                    return;
                }
            }

            // Not found
            echo json_encode(['status' => 'error', 'message' => 'Card not found']);
            return;
        }
    }

    public function cardPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardNumber = $_POST['cardNumber'];
            $total = floatval($_POST['total']);

            // Fetch wallet info
            $wallet = $this->POSModel->getWalletBalance($cardNumber);
            if ($wallet) {
                $balance = floatval($wallet['wallet']);

                if ($balance >= $total) {
                    $remainingBalance = $balance - $total;

                    $this->POSModel->updateWallet($cardNumber, $remainingBalance);
                    echo json_encode([
                        'success' => true,
                        'amountPaid' => $total,
                        'change' => 0
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Insufficient wallet balance.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Card not found.'
                ]);
            }
        }
    }

    public function confirmTransaction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionNo = $_POST['transaction_no'];
            $subTotal = $_POST['subtotal'];
            $discount = $_POST['discount'];
            $finalTotal = $_POST['final_total'];
            $paymentMethod = $_POST['payment_mode'];
            $orders = json_decode($_POST['orders'], true);
            $userId = $_SESSION['user_id'];
            $cardNumber = $_POST['card_number'];

            $insertTransaction = $this->POSModel->insertTransaction($transactionNo, $subTotal, $discount, $finalTotal, $paymentMethod, $userId, $cardNumber);

            if ($insertTransaction) {
                $sale = $this->POSModel->getSalesIdByTransactionNo($transactionNo);
                if ($sale && isset($sale['id'])) {
                    $this->POSModel->insertSalesItem($orders, $sale['id']);
                    foreach ($orders as $order) {
                        $product = $this->POSModel->getProductByName($order['name']);
                        $updatedQty = $product['qty'] - $order['qty'];
                        $this->POSModel->updateProductQty($order['name'], $updatedQty);
                    }
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Transaction confirmed successfully.',
                        'transaction_no' => $transactionNo
                    ]);
                    return;
                }
            }

            // ❌ If something failed
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to confirm transaction.',
                'orders' => json_decode($_POST['orders'], true)
            ]);
        }
    }

    public function undoCardPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardNumber = $_POST['card_number'];
            $amountPaid = floatval($_POST['amount_paid']);

            $wallet = $this->POSModel->getWalletBalance($cardNumber);
            if ($wallet) {
                $balance = floatval($wallet['wallet']);

                $updatedQty = $balance + $amountPaid;

                $this->POSModel->updateWallet($cardNumber, $updatedQty);
                echo json_encode([
                    'status' => 'success',
                    'message' => $amountPaid
                ]);
            }
        }
    }

    public function verifyAdmin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $adminUsername = $_POST['username'];

            $admin = $this->POSModel->getUserById($adminUsername);
            if ($admin && isset($admin['user_type']) && $admin['user_type'] === 1) {
                echo json_encode(['status' => 'success', 'valid' => true]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
            }
        }
    }

    public function removeTransaction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $sale_id = $_POST['sale_id'] ?? '';

            // Get the sales items for this transaction
            $salesItems = $this->POSModel->getSalesItems($sale_id);

            // Restore product quantities
            foreach ($salesItems as $item) {
                $product = $this->POSModel->getProductByName($item['item_name']);
                if ($product) {
                    $updatedQty = $product['qty'] + $item['qty'];
                    $this->POSModel->updateProductQty($item['item_name'], $updatedQty);
                }
            }

            // Get transaction details
            $transaction = $this->POSModel->getSalesDetails($sale_id);
            $refundInfo = null;

            // ✅ If payment mode is via card, refund the wallet
            if (
                isset($transaction['payment_method'], $transaction['membership_card']) &&
                strtolower($transaction['payment_method']) === 'card' &&
                !empty($transaction['membership_card'])
            ) {
                $cardNumber = $transaction['membership_card'];
                $wallet = $this->POSModel->getWalletBalance($cardNumber);

                if ($wallet) {
                    $newBalance = $wallet['wallet'] + $transaction['final_total'];
                    $this->POSModel->updateWallet($cardNumber, $newBalance);

                    $member = $this->POSModel->getMemberByCard($cardNumber);

                    $refundInfo = [
                        'member_name' => $member['first_name'] . ' ' . $member['last_name'],
                        'membership_id' => $cardNumber,
                        'amount' => $transaction['final_total']
                    ];
                }
            }

            // Delete the transaction and related sales items
            if ($this->POSModel->deleteTransaction($sale_id)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Transaction removed successfully',
                    'refund' => $refundInfo,
                    'transactionNum' => $transaction['transaction_no']
                ]);
                return;
            }

            echo json_encode(['status' => 'error', 'message' => 'Failed to remove transaction']);
        }
    }


    public function getSalesItems()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sale_id = $_POST['sale_id'] ?? '';

            // Get the sales items for this transaction
            $salesItems = $this->POSModel->getSalesItems($sale_id);
            $salesDetails = $this->POSModel->getSalesDetails($sale_id);

            if ($salesItems && $salesDetails) {
                echo json_encode([
                    'status' => 'success',
                    'items' => $salesItems,
                    'subtotal' => $salesDetails['sub_total'],
                    'total' => $salesDetails['final_total'],
                    'discount' => $salesDetails['discount'],
                    'mode' => $salesDetails['payment_method'],
                    'transactionNumber' => $salesDetails['transaction_no'],
                    'datetime' => $salesDetails['created_at']

                ]);
                return;
            }
        }
    }
}
