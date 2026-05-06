<?php

namespace app;

use app\Controllers\POSController;
use app\Controllers\SalesController;
use app\Controllers\ShiftController;
use app\Controllers\UsersController;
use app\Controllers\SyncController;

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

        // Sync API endpoints
        // Router::add('/api/sync-trigger', fn() => (new SyncController())->handleAutoSync(), 'GET');
        // Router::add('/api/manual-trigger', fn() => (new SyncController())->pullAndPush(), 'GET');

        //Cashier Shift Management
        Router::add('/end-shift', fn() => (new ShiftController())->endShift(), 'GET');

        //login & logout with ID route
        Router::add('/attendance', fn() => Router::render('Attendance'));
        Router::add('/attendance/submit', fn() => (new UsersController())->recordAttendace($_POST['idNumber'] ?? 0), 'POST');
        // Router::add('/attendance/timeOut', fn() => (new UsersController())->timeOut($_POST['idNumber'] ?? 0), 'POST');
        // Router::add('/attendance-logs', fn() => (new UsersController())->showLogs());
        // Router::add('/check-attendance-status', fn() => (new UsersController())->checkStatus(), 'POST');

        Router::add('/generate-sales-report', fn() => (new SalesController())->generateSalesReport(), 'GET');

        Router::add('/payment', fn() => (new POSController())->payment(), 'POST');

        // Add to cart routes
        Router::add('/save-cart', fn() => (new POSController())->saveCart(), 'POST');
        Router::add('/saved-carts', fn() => (new POSController())->getSavedCarts(), 'GET');
        Router::add('/load-cart', fn() => (new POSController())->loadCart(), 'POST');
        Router::add('/delete-saved-cart', fn() => (new POSController())->deleteSavedCart(), 'POST');

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
                $response = call_user_func($callback, $params);
                if (is_array($response)) {
                    echo json_encode($response);
                } else {
                    echo $response;
                }

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
