<?php

namespace app;

use app\Controllers\BookingController;
use app\Controllers\POSController;
use app\Controllers\UsersController;

class Router
{
    public static $routes = [];

    public static function init()
    {
        Router::add('/', fn() => (new UsersController())->index(), 'GET');
        Router::add('/login', fn() => (new UsersController())->login($_POST['username'] ?? '', $_POST['password'] ?? ''), 'POST');
        Router::add('/logout', fn() => (new UsersController())->logout(), 'GET');
        Router::add('/Dashboard', fn() => (new POSController())->dashboard(), 'GET');

        Router::add('/verify-membership', fn() => (new POSController())->getMembershipCard(), 'POST');
        Router::add('/cardPayment', fn() => (new POSController())->cardPayment(), 'POST');
        Router::add('/confirm-transaction', fn() => (new POSController())->confirmTransaction(), 'POST');
        Router::add('/undo-card-payment', fn() => (new POSController())->undoCardPayment(), 'POST');

        Router::add('/verify-admin', fn() => (new POSController())->verifyAdmin(), 'POST');
        Router::add('/remove-transaction', fn() => (new POSController())->removeTransaction(), 'POST');
        Router::add('/get-sales-items', fn() => (new POSController())->getSalesItems(), 'POST');

        Router::add('/print-receipt', fn() => (new POSController())->printReceipt(), 'GET');
        Router::run();
    }

    public static function add($path, $callback, $method = 'GET')
    {
        $path = str_replace(['{', '}'], ['(?P<', '>[^/]+)'], $path);

        self::$routes[] = [
            'path' => $path,
            'callback' => $callback,
            'method' => strtoupper($method)
        ];
    }

    public static function run()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach (self::$routes as $route) {

            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match("#^{$route['path']}$#", $requestUri, $matches)) {
                $params = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                echo call_user_func($route['callback'], $params);
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
