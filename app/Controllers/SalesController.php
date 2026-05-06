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
        // 1. Setup & Memory Fix
        ini_set('memory_limit', '512M');
        $frequency = 'daily'; // POS is daily only
        $mode = $_GET['mode'] ?? 'both';
        $isCheck = $_GET['check'] ?? false; // Check if this is just a pre-download check

        // Create the cashier name array for the filter (only for the current logged-in user)
        $cashierFullName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        $cashierFilter = [$cashierFullName];

        // 2. Fetch Grouped Data from Model
        // This uses your getFilteredSales logic which groups by Item Name and Price
        $reportData = $this->SalesModel->getFilteredSales($frequency, $cashierFilter);

        // Filter by mode immediately to see if the specific selection has data
        if ($mode !== 'both') {
            $reportData = array_filter($reportData, function ($row) use ($mode) {
                return strtolower($row['payment_method']) === strtolower($mode);
            });
        }

        // IF DATA IS EMPTY
        if (empty($reportData)) {
            if ($isCheck) {
                // Return JSON for the AJAX request
                header('Content-Type: application/json');
                echo json_encode(['status' => 'empty']);
                exit();
            } else {
                // Fallback for direct access
                $_SESSION['danger'][] = 'No sales data found for today.';
                header('Location: /sales');
                exit();
            }
        }

        // IF CHECK ONLY AND DATA EXISTS
        if ($isCheck) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit();
        }

        $groupedRegular = [];
        $groupedWalkIn = [];

        // 4. Separate Regular Products from Walk-ins
        foreach ($reportData as $row) {
            if (str_contains(strtolower($row['item_name']), 'walk-in')) {
                $groupedWalkIn[$row['payment_method']][] = $row;
            } else {
                $groupedRegular[$row['payment_method']][] = $row;
            }
        }

        // 5. Initialize Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Info
        $sheet->setCellValue('A1', 'DAILY SALES SUMMARY REPORT');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Cashier: ' . $cashierFullName);
        $sheet->setCellValue('F2', 'Date: ' . date('F d, Y'));
        $sheet->getStyle('A2:F2')->getFont()->setBold(true);

        $currentRow = 4;

        // 6. RENDER REGULAR SALES TABLES (Grouped by Payment Method)
        foreach ($groupedRegular as $method => $sales) {
            $sheet->setCellValue('A' . $currentRow, "PRODUCT SALES: " . strtoupper($method));
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
            $currentRow++;

            $headers = ['Date', 'Item Name', 'Total Qty', 'Price', 'Raw Sales', 'Discount', 'Total Sales'];
            $sheet->fromArray($headers, NULL, 'A' . $currentRow);
            $sheet->getStyle("A$currentRow:G$currentRow")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']]
            ]);
            $currentRow++;

            $startDataRow = $currentRow;
            foreach ($sales as $s) {
                $sheet->setCellValue('A' . $currentRow, $s['sale_date']);
                $sheet->setCellValue('B' . $currentRow, $s['item_name']);
                $sheet->setCellValue('C' . $currentRow, $s['total_qty']);
                $sheet->setCellValue('D' . $currentRow, $s['unit_price']);
                $sheet->setCellValue('E' . $currentRow, $s['raw_sales']);
                $sheet->setCellValue('F' . $currentRow, $s['total_discount']);
                $sheet->setCellValue('G' . $currentRow, $s['total_sales']);
                $currentRow++;
            }

            $sheet->setCellValue('F' . $currentRow, "Total $method Products:");
            $sheet->setCellValue('G' . $currentRow, "=SUM(G$startDataRow:G" . ($currentRow - 1) . ")");
            $sheet->getStyle("F$currentRow:G$currentRow")->getFont()->setBold(true);

            $currentRow += 2;
        }

        // 7. RENDER WALK-IN TABLES
        if (!empty($groupedWalkIn)) {
            foreach ($groupedWalkIn as $method => $walks) {
                $sheet->setCellValue('A' . $currentRow, "WALK-IN PAYMENTS: " . strtoupper($method));
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                $currentRow++;

                $walkInHeaders = ['Date', 'Description', 'Total Collected'];
                $sheet->fromArray($walkInHeaders, NULL, 'A' . $currentRow);
                $sheet->getStyle("A$currentRow:C$currentRow")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '007bff']]
                ]);
                $currentRow++;

                $startWalkInRow = $currentRow;
                foreach ($walks as $w) {
                    $sheet->setCellValue('A' . $currentRow, $w['sale_date']);
                    $sheet->setCellValue('B' . $currentRow, $w['item_name']);
                    $sheet->setCellValue('C' . $currentRow, $w['total_sales']); // This is the final_total sum
                    $currentRow++;
                }

                $sheet->setCellValue('B' . $currentRow, "Total $method Walk-ins:");
                $sheet->setCellValue('C' . $currentRow, "=SUM(C$startWalkInRow:C" . ($currentRow - 1) . ")");
                $sheet->getStyle("B$currentRow:C$currentRow")->getFont()->setBold(true);

                $currentRow += 2;
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 8. OUTPUT BUFFERING (CRITICAL FIX)
        ob_start();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        if (ob_get_length()) ob_clean();
        $writer->save('php://output');
        $fileContents = ob_get_contents();
        $fileSize = ob_get_length();
        ob_end_clean();

        $fileName = "Daily_POS_Report_" . date('Ymd') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: max-age=0');

        echo $fileContents;
        exit;
    }
}
