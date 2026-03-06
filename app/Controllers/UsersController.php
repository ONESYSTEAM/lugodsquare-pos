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

    public function recordAttendace($idNumber)
    {
        $user = $this->UsersModel->confirmUserByIdNumber($idNumber);
        if (!$user) {
            $_SESSION['danger'][] = 'ID number not recognized. Please try again.';
            header("Location: /attendance");
            exit;
        }
        $username = $user['first_name'] . ' ' . $user['last_name'];
        $userId = $this->UsersModel->getUserByIdNumberAndNoActiveShift($idNumber);
        if ($userId) {
            $userId = $userId['id']; // Extract the user ID from the result array
            $this->UsersModel->timeIn($idNumber, $userId);
            $_SESSION['success'][] = $username . ' timed in successfully. Have a great day!';
            header('Location: /login');
        } else {
            $userId = $this->UsersModel->getUserWithActiveShift($idNumber);
            if ($userId) {
                $this->UsersModel->timeOut($idNumber);
                $_SESSION['success'][] = $username . ' timed out successfully.';
                header('Location: /login');
            } else {
                $_SESSION['danger'][] = 'Failed to time out. Please ensure the ID card is valid and no active shift exists.';
                header("Location: /attendance");
                exit;
            }
        }
    }
}
