<?php

use Core\Router;
use App\Controllers\ProductController;

$router = new Router;

$router->loadMiddleware();

$router->get('/product', [ProductController::class, 'index']);
$router->get('/product/:id', [ProductController::class, 'show']);
$router->post('/product', [ProductController::class, 'store']);
$router->put('/product/:id', [ProductController::class, 'update']);
$router->delete('/product/:id', [ProductController::class, 'delete']);

$response = $router->run();

echo json_encode($response);
?>