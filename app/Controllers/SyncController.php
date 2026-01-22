<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\SyncModel;

class SyncController
{
    private $syncModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->syncModel = new SyncModel($db);
    }

    public function handleAutoSync()
    {
        $pushResults = $this->syncModel->pushLocalData();
        $pullResults = $this->syncModel->pullRemoteData();

        $results = [
            'push' => $pushResults,
            'pull' => $pullResults
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    public function pullAndPush()
    {
        return $this->handleAutoSync();
    }
}
