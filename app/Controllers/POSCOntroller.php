<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\POSModel;

class POSController
{
    private $POSModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->POSModel = new POSModel($db);
    }

    public function dashboard()
    {
        $userId = $_SESSION['user_id'] ?? '';
        if ($userId == '') {
            header('Location: /');
            exit;
        }

        $products = $this->POSModel->getProducts();
        $transactions = $this->POSModel->getTransactionHistory();

        echo $GLOBALS['templates']->render('Layout/DashboardLayout', [
            'products' => $products,
            'transactions' => $transactions
        ]);
    }

    public function getMembershipCard()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $CardNumber = $_POST['card_number'] ?? '';
            $memberCards = $this->POSModel->getMembershipCards();

            if (!is_array($memberCards)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid data from model']);
                return;
            }

            foreach ($memberCards as $card) {
                if (isset($card['card_number']) && $card['card_number'] === $CardNumber) {
                    echo json_encode(['status' => 'success', 'is_valid' => true]);
                    return;
                }
            }

            echo json_encode(['status' => 'error', 'message' => 'Card not found']);
            return;
        }
    }

    public function cardPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $cardNumber = $_POST['cardNumber'] ?? '';
            $total = floatval($_POST['total'] ?? 0);

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
            header('Content-Type: application/json');

            $transactionNo  = $_POST['transaction_no'] ?? '';
            $subTotal       = $_POST['subtotal'] ?? 0;
            $discount       = $_POST['discount'] ?? 0;
            $finalTotal     = $_POST['final_total'] ?? 0;
            $paymentMethod  = $_POST['payment_mode'] ?? '';
            $orders         = json_decode($_POST['orders'] ?? '[]', true);
            $userId         = $_SESSION['user_id'] ?? '';
            $cardNumber     = $_POST['card_number'] ?? '';
            $cashAmount = isset($_POST['cash_amount']) ? floatval($_POST['cash_amount']) : null;
            $cashChange = isset($_POST['cash_change']) ? floatval($_POST['cash_change']) : null;

            if ($userId === '' || $transactionNo === '' || !is_array($orders)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request data.']);
                return;
            }

            $insertTransaction = $this->POSModel->insertTransaction(
                $transactionNo,
                $subTotal,
                $discount,
                $finalTotal,
                $paymentMethod,
                $userId,
                $cardNumber,
                $cashAmount,
                $cashChange
            );

            if ($insertTransaction) {
                $sale = $this->POSModel->getSalesIdByTransactionNo($transactionNo);

                if ($sale && isset($sale['id'])) {
                    $this->POSModel->insertSalesItem($orders, $sale['id']);

                    foreach ($orders as $order) {
                        $product = $this->POSModel->getProductByName($order['name']);
                        if ($product && isset($product['qty'])) {
                            $updatedQty = $product['qty'] - $order['qty'];
                            $this->POSModel->updateProductQty($order['name'], $updatedQty);
                        }
                    }

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Transaction confirmed successfully.',
                        'transaction_no' => $transactionNo,
                        'print_url' => '/print-receipt?transaction_no=' . urlencode($transactionNo),
                    ]);
                    return;
                }
            }

            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to confirm transaction.',
                'orders' => $orders
            ]);
        }
    }

    public function printReceipt()
    {
        $userId = $_SESSION['user_id'] ?? '';
        if ($userId === '') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        $transactionNo = $_GET['transaction_no'] ?? '';
        if ($transactionNo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Missing transaction_no']);
            return;
        }

        $sale = $this->POSModel->getSaleByTransactionNo($transactionNo);
        if (!$sale) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
            return;
        }

        $items = $this->POSModel->getSalesItems($sale['id']) ?? [];

        $cashier = $this->POSModel->getCashierByUserId($userId);
        $cashierName = $cashier
            ? trim(($cashier['first_name'] ?? '') . ' ' . ($cashier['last_name'] ?? ''))
            : 'Cashier';

        $addSpaces = function ($string = '', $validLen = 0) {
            $string = (string)$string;
            if (strlen($string) < $validLen) {
                $string .= str_repeat(' ', $validLen - strlen($string));
            }
            return $string;
        };

        $cashAmount = isset($sale['cash_amount']) ? (float)$sale['cash_amount'] : null;
        $cashChange = isset($sale['cash_change']) ? (float)$sale['cash_change'] : null;

        if ($cashAmount === null && isset($_GET['cash_amount'])) $cashAmount = (float)$_GET['cash_amount'];
        if ($cashChange === null && isset($_GET['cash_change'])) $cashChange = (float)$_GET['cash_change'];

        try {
            require(__DIR__ . '/../../vendor/autoload.php');

            $connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector(
                "smb://" . getenv('COMPUTERNAME') . "/xprinter"
            );
            $printer = new \Mike42\Escpos\Printer($connector);

            date_default_timezone_set("Asia/Manila");
            $date = date("m/d/Y");
            $time = date("h:i:sa");

            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setLineSpacing(10);
            $printer->text("RACQUET CLUB - POS\n");
            $printer->text("TRANSACTION SLIP\n");
            $printer->setEmphasis(false);
            $printer->feed(1);

            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text("Terminal : " . getenv('COMPUTERNAME') . "\n");
            $printer->text("Trans #  : {$sale['transaction_no']}\n");
            $printer->text("Cashier  : {$cashierName}\n");
            $printer->text("Date     : {$date}\n");
            $printer->text("Time     : {$time}\n");
            $printer->text("Mode     : {$sale['payment_method']}\n");

            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text($addSpaces('Item(s)', 20) . $addSpaces('Subtotal', 10) . "\n");
            $printer->setEmphasis(false);
            $printer->text("------------------------------\n");
            $printer->feed(1);

            foreach ($items as $item) {
                $qty = (int)($item['qty'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $lineTotal = (float)($item['total'] ?? ($qty * $price));
                $name = (string)($item['item_name'] ?? 'Item');

                $label = $qty . 'x' . number_format($price, 2) . ' - ' . $name;

                $name_lines = str_split($label, 20);
                foreach ($name_lines as $k => $l) {
                    $l = trim($l);
                    $name_lines[$k] = $addSpaces($l . ' ', 20);
                }

                $subtotalStr = number_format($lineTotal, 2);
                $subtotal_lines = str_split($subtotalStr, 10);
                foreach ($subtotal_lines as $k => $l) {
                    $l = trim($l);
                    $subtotal_lines[$k] = $addSpaces($l, 10);
                }

                $counter = max(count($name_lines), count($subtotal_lines));

                for ($i = 0; $i < $counter; $i++) {
                    $line = '';
                    if (isset($name_lines[$i])) $line .= $name_lines[$i];
                    if (isset($subtotal_lines[$i])) $line .= $subtotal_lines[$i];
                    $printer->text($line . "\n");
                }
            }

            $printer->feed(1);
            $printer->text("------------------------------\n");
            $printer->text($addSpaces('SUBTOTAL', 20) . $addSpaces(number_format((float)$sale['sub_total'], 2), 10) . "\n");
            $printer->text($addSpaces('DISCOUNT', 20) . $addSpaces(number_format((float)$sale['discount'], 2), 10) . "\n");

            $printer->setEmphasis(true);
            $printer->text($addSpaces('TOTAL', 20) . $addSpaces(number_format((float)$sale['final_total'], 2), 10) . "\n");
            $printer->setEmphasis(false);

            if (strtolower((string)$sale['payment_method']) === 'cash') {
                $printer->text($addSpaces('CASH', 20) . $addSpaces(number_format((float)($cashAmount ?? 0), 2), 10) . "\n");
                $printer->text($addSpaces('CHANGE', 20) . $addSpaces(number_format((float)($cashChange ?? 0), 2), 10) . "\n");
            }

            $printer->feed(2);
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text("POS Supplier\n");
            $printer->text("ONESYSTEAM\n");
            $printer->text("P3 - Lunao, Gingoog City\n");
            $printer->text("Website  : www.onesysteam.com\n");
            $printer->text("TIN #    : 611376341-000000\n");
            $printer->feed(2);
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->text("THIS IS NOT AN OFFICIAL RECEIPT\n");
            $printer->text("FOR DOCUMENTATION PURPOSES ONLY\n");
            $printer->cut();
            $printer->pulse();
            $printer->close();

            echo json_encode(['status' => 'success', 'message' => 'Printed successfully']);
            return;
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Print failed: ' . $e->getMessage()]);
            return;
        }
    }

    public function undoCardPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $cardNumber = $_POST['card_number'] ?? '';
            $amountPaid = floatval($_POST['amount_paid'] ?? 0);

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

            $adminUsername = $_POST['username'] ?? '';
            $admin = $this->POSModel->getUserById($adminUsername);

            if ($admin && isset($admin['user_type']) && (int)$admin['user_type'] === 1) {
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

            $salesItems = $this->POSModel->getSalesItems($sale_id);

            foreach ($salesItems as $item) {
                $product = $this->POSModel->getProductByName($item['item_name']);
                if ($product && isset($product['qty'])) {
                    $updatedQty = $product['qty'] + $item['qty'];
                    $this->POSModel->updateProductQty($item['item_name'], $updatedQty);
                }
            }

            $transaction = $this->POSModel->getSalesDetails($sale_id);
            $refundInfo = null;

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
                        'member_name' => ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''),
                        'membership_id' => $cardNumber,
                        'amount' => $transaction['final_total']
                    ];
                }
            }

            if ($this->POSModel->deleteTransaction($sale_id)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Transaction removed successfully',
                    'refund' => $refundInfo,
                    'transactionNum' => $transaction['transaction_no'] ?? ''
                ]);
                return;
            }

            echo json_encode(['status' => 'error', 'message' => 'Failed to remove transaction']);
        }
    }

    public function getSalesItems()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $sale_id = $_POST['sale_id'] ?? '';

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

            echo json_encode(['status' => 'error', 'message' => 'No data found']);
        }
    }
}
