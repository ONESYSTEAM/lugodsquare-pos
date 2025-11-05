<?php

namespace app;

use app\Controllers\BookingController;
use app\Controllers\POSCOntroller;
use app\Controllers\UsersController;

class Router
{
    public static $routes = [];

    public static function init()
    {
        // Define application routes here
        Router::add('/', fn() => (new UsersController())->index());
        Router::add('/login', fn() => (new UsersController())->login($_POST['username'] ?? 0, $_POST['password'] ?? 0), 'POST');
        Router::add('/logout', fn() => (new UsersController())->logout());

        Router::add('/Dashboard', fn() => (new POSCOntroller())->dashboard());

        Router::add('/verify-membership', fn()=>(new POSCOntroller())->getMembershipCard(), 'POST');
        Router::add('/cardPayment', fn() => (new POSCOntroller())->cardPayment(), 'POST');
        Router::add('/confirm-transaction', fn() => (new POSController())->confirmTransaction(), 'POST');
        Router::add('/undo-card-payment', fn() => (new POSCOntroller())->undoCardPayment(), 'POST');

        Router::add('/verify-admin', fn() => (new POSCOntroller())->verifyAdmin(),'POST');
        Router::add('/remove-transaction', fn() => (new POSCOntroller())->removeTransaction(), 'POST');
        Router::add('/get-sales-items', fn() => (new POSCOntroller())->getSalesItems(), 'POST');
        
        Router::run();
    }

    public static function add($path, $callback)
    {
        $path = str_replace(['{', '}'], ['(?P<', '>[^/]+)'], $path);

        Router::$routes[$path] = $callback;
    }

    public static function run()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach (self::$routes as $route => $callback) {
            if (preg_match("#^$route$#", $requestUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                echo call_user_func($callback, $params);

                return;
            }
        }
        echo template()->render('Errors/404');
    }

    public static function render($view, $data = [])
    {
        $viewPath = __DIR__ . "/Views/{$view}.php";

        if (file_exists($viewPath)) {
            $templates = new \League\Plates\Engine(__DIR__ . '/Views');
            echo $templates->render($view, $data);
        } else {
            echo template()->render('Errors/404');
        }
    }
}
