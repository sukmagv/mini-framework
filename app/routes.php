<?php

use Core\Router;
use Core\Response;
use App\Controllers\ProductController;
use Core\HttpStatus;

$checkRequestFormat = function () {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (in_array($method, ['GET', 'DELETE'])) {
        return null;
    }

    if (
        stripos($contentType, 'application/json') === false &&
        stripos($contentType, 'application/x-www-form-urlencoded') === false &&
        stripos($contentType, 'multipart/form-data') === false
    ) {
        return Response::failed("Unsupported Content-Type", HttpStatus::BAD_REQUEST);
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