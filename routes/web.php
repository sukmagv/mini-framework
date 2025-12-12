<?php

use Core\Router;
use App\Controllers\ProductController;

$router = new Router;

$router->loadMiddleware();

$router->add('GET', '/product', [ProductController::class, 'index']);
$router->add('GET', '/product/:id', [ProductController::class, 'show']);
$router->add('POST', '/product', [ProductController::class, 'store']);
$router->add('PUT', '/product/:id', [ProductController::class, 'update']);
$router->add('DELETE', '/product/:id', [ProductController::class, 'delete']);

$response = $router->run();

echo json_encode($response);
?>