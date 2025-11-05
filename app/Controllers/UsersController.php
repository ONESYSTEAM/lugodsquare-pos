<?php

namespace app\Controllers;

use config\DBConnection;
use app\Models\UsersModel;

class UsersController
{
    private $UsersModel;

    public function __construct()
    {
        $db = new DBConnection();
        $this->UsersModel = new UsersModel($db);
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
}
