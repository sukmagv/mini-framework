<?php

require __DIR__.'/../vendor/autoload.php';

use Core\Router;
use Core\Database;
use App\Controllers\ProductController;

$db = new Database();

$router = new Router();

$router->add('GET', '/', function() {
    echo "Welcome to the Mini Framework!";
});

$router->add('GET', '/product', [ProductController::class, 'index']);
$router->add('GET', '/product/:id', [ProductController::class, 'show']);
$router->add('POST', '/product', [ProductController::class, 'store']);
$router->add('PUT', '/product/:id', [ProductController::class, 'update']);
$router->add('DELETE', '/product/:id', [ProductController::class, 'delete']);

echo $router->run();

?>