<?php

use App\Controllers\ProductController;
use Core\Router;

$checkRequestFormat = function() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (stripos($contentType, 'application/json') === false &&
        stripos($contentType, 'application/x-www-form-urlencoded') === false &&
        stripos($contentType, 'multipart/form-data') === false) 
        {
        http_response_code(400);
        return [
            'status' => 'failed',
            'message' => 'Invalid request format. Use JSON or form-data.'
        ];
    }
    return null;
};


$router = new Router;

$router->middleware($checkRequestFormat);

$router->add('GET', '/product', [ProductController::class, 'index']);
$router->add('GET', '/product/:id', [ProductController::class, 'show']);
$router->add('POST', '/product', [ProductController::class, 'store']);
$router->add('PUT', '/product/:id', [ProductController::class, 'update']);
$router->add('DELETE', '/product/:id', [ProductController::class, 'delete']);

echo json_encode($router->run());

?>