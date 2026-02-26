<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\UsersModel;
use app\Models\ShiftModel;

class UsersController
{
    private $UsersModel;
    private $ShiftModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->UsersModel = new UsersModel($db);
        $this->ShiftModel = new ShiftModel($db);
    }

    public function index()
    {
        $userId = $_SESSION['user_id'] ?? '';
        $userType = $_SESSION['user_type'] ?? '';

        if ($userId == '') {
            echo $GLOBALS['templates']->render('Login');
            exit;
        }
        if ($userId != 0) {
            header('Location: /Dashboard');
            exit;
        }
        if ($userType != 1) {
            $_SESSION['danger'][] = 'You are not allowed to proceed to the page you requested.';
            echo $GLOBALS['templates']->render('Login');
            exit;
        }

        header('Location: /');
        exit;
    }

    public function login($username, $password)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $GLOBALS['templates']->render('Login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $_SESSION['danger'][] = 'All fields are required.';
            echo $GLOBALS['templates']->render('Login');
            exit;
        }

        $user = $this->UsersModel->getUserByUsername($username);

        if ($this->ShiftModel->hasEndedShiftToday($user['id'])) {
            $_SESSION['danger'][] = 'You have already ended your shift today. You can log in tomorrow.';
            echo $GLOBALS['templates']->render('Login');
            exit;
        }

        // Start shift if not already started
        $this->ShiftModel->startShiftIfNotExists($user['id']);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['danger'][] = 'Invalid username or password.';
            echo $GLOBALS['templates']->render('Login');
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        header('Location: /');
        exit;
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Location: /login");
        exit;
    }

    public function getUsers()
    {
        $users = $this->UsersModel->getUsers();
        echo $GLOBALS['templates']->render('Users', [
            'users' => $users
        ]);
    }

    public function attendance()
    {
        $status = $this->attendanceStatus();
        echo $GLOBALS['templates']->render('Attendance', [
            'isTimedIn' => $status
        ]);
    }

    private function attendanceStatus()
    {
        $isTimedIn = false;
        if (isset($_SESSION['user_id'])) {
            $attendance = $this->UsersModel->getAttendanceByUserId($_SESSION['user_id']);
            if ($attendance && !$attendance['time_out']) {
                $isTimedIn = true;
            }
        }
        return $isTimedIn;
    }

    public function timeIn($idNumber)
    {
        $loggedInUserId = $_SESSION['user_id'] ?? 0;

        $isOwner = $this->UsersModel->verifyIdOwnership($idNumber, $loggedInUserId);

        if (!$isOwner) {
            $_SESSION['danger'][] = 'This ID card does not belong to your account.';
            header("Location: /attendance");
            exit;
        }
        $result = $this->UsersModel->timeIn($idNumber, $_SESSION['user_id'] ?? 0);
        if ($result) {
            $_SESSION['success'][] = 'Time in successful. Have a great day!';
        } else {
            $_SESSION['danger'][] = 'Failed to time in.';
        }
        header("Location: /");
        exit;
    }

    public function timeOut($idNumber)
    {
        $loggedInUserId = $_SESSION['user_id'] ?? 0;

        $isOwner = $this->UsersModel->verifyIdOwnership($idNumber, $loggedInUserId);

        if (!$isOwner) {
            $_SESSION['danger'][] = 'This ID card does not belong to your account.';
            header("Location: /attendance");
            exit;
        }
        $result = $this->UsersModel->timeOut($idNumber);
        if ($result) {
            $_SESSION['success'][] = 'Time out successful.';
        } else {
            $_SESSION['danger'][] = 'Failed to time out.';
        }
        header("Location: /");
        exit;
    }

    public function showLogs()
    {
        $userId = $_SESSION['user_id'] ?? '';
        if ($userId === '') {
            header('Location: /login');
            exit;
        }
        $logs = $this->UsersModel->getDailyLogs($userId);

        $status = $this->attendanceStatus();
        echo $GLOBALS['templates']->render('AttendanceLogs', [
            'attendances' => $logs,
            'isTimedIn'   => $status
        ]);
    }
}
