<?php

namespace app\Models;

use config\DBConnection;
use PDO;

class SyncModel
{
    private $db;

    public $tables = [
        'courts',
        'members',
        'booking',
        'sales',
        'sales_items',
        'users',
        'email_verifications',
        'products',
    ];

    public function __construct(DBConnection $db)
    {
        $this->db = $db->getConnection();
    }

    public function pullRemoteData($targetTable = null)
    {
        $apiKey = $_ENV['SYNC_API_KEY'] ?? '';
        $summary = [];

        $tablesToSync = $targetTable ? [$targetTable] : $this->tables;

        foreach ($tablesToSync as $table) {
            try {
                $cleanTable = trim($table);

                $url = "https://pos.lugodsquare.com/api/get-updates?key=" . urlencode($apiKey) . "&table=" . urlencode($cleanTable);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $rawResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200) {
                    $summary[$table] = ['status' => 'error', 'message' => "HTTP Code $httpCode"];
                    continue;
                }

                $remoteData = json_decode($rawResponse, true);

                if (!is_array($remoteData) || !isset($remoteData['data'])) {
                    $summary[$table] = ['status' => 'error', 'message' => 'Invalid JSON response'];
                    continue;
                }

                $processedIds = [];
                foreach ($remoteData['data'] as $row) {
                    $row['synced'] = 1;

                    if (isset($row['synced_to_local'])) {
                        unset($row['synced_to_local']);
                    }

                    $columns = array_keys($row);
                    $placeholders = implode(',', array_fill(0, count($columns), '?'));
                    $updates = implode(',', array_map(fn($c) => "`$c`=VALUES(`$c`)", $columns));

                    $stmt = $this->db->prepare("
                        INSERT INTO `$cleanTable` (" . implode(',', $columns) . ")
                        VALUES ($placeholders)
                        ON DUPLICATE KEY UPDATE $updates
                    ");
                    $stmt->execute(array_values($row));

                    $processedIds[] = $row['id'];
                }

                if (!empty($processedIds)) {
                    $this->confirmReceiptToRemote($cleanTable, $processedIds);
                }

                $summary[$table] = ['status' => 'success', 'updated' => count($remoteData['data'])];
            } catch (\Exception $e) {
                $summary[$table] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return $summary;
    }

    private function confirmReceiptToRemote($table, $ids)
    {
        $apiKey = $_ENV['SYNC_API_KEY'] ?? '';
        $url = "https://pos.lugodsquare.com/api/confirm-sync?key=" . urlencode($apiKey) . "&table=" . urlencode($table);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ids' => $ids]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }

    public function pushLocalData()
    {
        $apiKey = $_ENV['SYNC_API_KEY'] ?? '';
        $summary = [];

        foreach ($this->tables as $table) {
            try {
                $cleanTable = trim($table);
                $stmt = $this->db->prepare("SELECT * FROM `$cleanTable` WHERE synced = 0");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    $summary[$table] = ['status' => 'no_changes'];
                    continue;
                }

                $url = "https://pos.lugodsquare.com/api/push-updates?key=" . urlencode($apiKey) . "&table=" . urlencode($cleanTable);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($rows));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $ids = array_column($rows, 'id');
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $this->db->prepare("UPDATE `$cleanTable` SET synced = 1 WHERE id IN ($placeholders)")->execute($ids);
                    $summary[$table] = ['status' => 'success', 'pushed' => count($rows)];
                } else {
                    $summary[$table] = ['status' => 'error', 'code' => $httpCode];
                }
            } catch (\Exception $e) {
                $summary[$table] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $summary;
    }

    public function getAllLocalProducts()
    {
        $stmt = $this->db->prepare("SELECT id, product_image FROM products");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
