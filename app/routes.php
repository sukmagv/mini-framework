<?php

use Core\Router;
use Core\Response;
use App\Controllers\ProductController;
use Core\HttpStatus;

$logAndCheckMiddleware = function ($response = null) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    $GLOBALS['logger']->info("Request received", [
        'method' => $method,
        'uri' => $uri,
        'content_type' => $contentType,
        'time' => date('Y-m-d H:i:s')
    ]);

    if (in_array($method, ['POST','PUT','PATCH']) &&
        stripos($contentType, 'application/json') === false &&
        stripos($contentType, 'application/x-www-form-urlencoded') === false &&
        stripos($contentType, 'multipart/form-data') === false
    ) {
        $GLOBALS['logger']->warning("Invalid Content-Type", [
            'method' => $method,
            'uri' => $uri,
            'content_type' => $contentType
        ]);
        return Response::failed("Unsupported Content-Type", HttpStatus::BAD_REQUEST);
    }

    if ($response !== null) {
        $GLOBALS['logger']->info("Response returned", [
            'method' => $method,
            'uri' => $uri,
            'response' => $response
        ]);
    }

    return null;
};


$router = new Router;

$router->middleware($logAndCheckMiddleware);

$router->add('GET', '/product', [ProductController::class, 'index']);
$router->add('GET', '/product/:id', [ProductController::class, 'show']);
$router->add('POST', '/product', [ProductController::class, 'store']);
$router->add('PUT', '/product/:id', [ProductController::class, 'update']);
$router->add('DELETE', '/product/:id', [ProductController::class, 'delete']);

$response = $router->run();

echo json_encode($response);
?>