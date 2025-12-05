<?php

use App\Controllers\ProductController;
use Core\Router;

$router = new Router;

$router->add('GET', '/product', [ProductController::class, 'index']);
$router->add('GET', '/product/:id', [ProductController::class, 'show']);
$router->add('POST', '/product', [ProductController::class, 'store']);
$router->add('PUT', '/product/:id', [ProductController::class, 'update']);
$router->add('DELETE', '/product/:id', [ProductController::class, 'delete']);

echo json_encode($router->run());

?>