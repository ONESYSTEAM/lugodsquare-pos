<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\ShiftModel;

class ShiftController
{
    private $ShiftModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->ShiftModel = new ShiftModel($db);
    }

    // Add your custom controllers below to handle business logic.
    public function endShift()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['danger'][] = 'You must be logged in to end a shift.';
            header('Location: /');
            exit;
        }

        $this->ShiftModel->endCurrentShift($userId);
        $_SESSION['success'][] = 'Shift ended successfully.';

        //logout the user after ending the shift
        session_start();
        session_unset();
        session_destroy();

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Location: /login");
        exit;
    }
}
