<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\SalesModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesController
{
    private $SalesModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->SalesModel = new SalesModel($db);
    }



    public function generateSalesReport()
    {
        $mode = $_GET['mode'] ?? 'both';
        $userId = $_SESSION['user_id'];
        $sales = $this->SalesModel->getSalesData($userId, $mode);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- 1. REPORT HEADER SECTION ---
        $sheet->setCellValue('A1', 'SALES REPORT');
        $sheet->mergeCells('A1:I1'); // Merge across the width of the table
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Cashier: ' . $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
        $sheet->setCellValue('G2', 'Date: ' . date('F d, Y')); // e.g., March 04, 2026
        $sheet->getStyle('A2:G2')->getFont()->setBold(true);

        // --- 2. TABLE HEADERS (Moved to Row 4) ---
        $headers = ['Time', 'Trans #', 'Item Name', 'Qty', 'Price', 'Item Total', 'Discount', 'Final Total', 'Method'];
        $sheet->fromArray($headers, NULL, 'A4');

        // Style the table headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

        // 3. Fill Data
        $row = 5;
        $grandTotal = 0;
        $lastTransNo = null;

        foreach ($sales as $sale) {
            $sheet->setCellValue('A' . $row, $sale['created_at']);
            $sheet->setCellValue('B' . $row, $sale['transaction_no']);
            $sheet->setCellValue('C' . $row, $sale['item_name']);
            $sheet->setCellValue('D' . $row, $sale['qty']);
            $sheet->setCellValue('E' . $row, $sale['price']);
            $sheet->setCellValue('F' . $row, $sale['item_total']);

            if ($lastTransNo !== $sale['transaction_no']) {
                $sheet->setCellValue('G' . $row, $sale['discount']);
                $sheet->setCellValue('H' . $row, $sale['final_total']);
                $grandTotal += $sale['final_total'];
            }

            $sheet->setCellValue('I' . $row, strtoupper($sale['payment_method']));
            $lastTransNo = $sale['transaction_no'];
            $row++;
        }

        // 4. Add Grand Total Row
        $row++;
        $sheet->setCellValue('G' . $row, 'GRAND TOTAL:');
        $sheet->setCellValue('H' . $row, $grandTotal);
        $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);

        // 5. Force Download
        $fileName = "Detailed_Sales_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
