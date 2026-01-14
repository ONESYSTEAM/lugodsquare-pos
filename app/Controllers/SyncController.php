<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\SyncModel;

class SyncController
{
    private $syncModel;

    public function __construct()
    {
        $db = new DBConnection(); // local DB connection
        $this->syncModel = new SyncModel($db);
    }

    public function handleAutoSync()
    {
        // 1. PUSH: Send local changes (synced = 0) to Remote
        $pushResults = $this->syncModel->pushLocalData();

        // 2. PULL: Get remote changes (synced_to_local = 0) from Remote
        // We no longer need $since! The Model handles flags now.
        $pullResults = $this->syncModel->pullRemoteData();

        $results = [
            'push' => $pushResults,
            'pull' => $pullResults
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    /**
     * Keep this for manual debugging if needed
     */
    public function pullAndPush()
    {
        return $this->handleAutoSync();
    }
}
