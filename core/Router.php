<?php

namespace Core;

use Throwable;

/**
 * Base router class for registering and executing HTTP routes
 */
class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];

    /**
     * Add a new route to the router
     * 
     * The callback is executed when the route is matched
     *
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function add(string $method, string $path, callable|array $callback): void
    {
        $this->routes[] = [
            'method'   => $method,
            'path'     => $path,
            'callback' => $callback,
            'middleware' => $this->globalMiddleware
        ];
    }

    /**
     * Add global middleware (applied to all routes)
     */
    public function middleware(callable $func): self
    {
        $this->globalMiddleware[] = $func;
        return $this;
    }

    /**
     * Run the router and execute the matching route callback
     *
     * @return mixed
     */
    public function run(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $regexPattern = preg_replace_callback('/:\w+/', fn($m) => '([^/]+)', $route['path']);

            if (preg_match("#^{$regexPattern}$#", $uri, $params)) {
                array_shift($params);

                if ($route['method'] === $method) {
                     if (str_contains($route['path'], ':id')) {
                        $id = $params[0] ?? null;  

                        if (!$id) {
                            return Response::failed("ID is required", HttpStatus::BAD_REQUEST);
                        }

                        if (!is_numeric($id)) {
                            return Response::failed("ID must be a number", HttpStatus::BAD_REQUEST);
                        }
                    }

                    try {
                        foreach ($route['middleware'] as $mw) {
                            $result = $mw($response ?? null);
                            if ($result !== null) {
                                return $result;
                            }
                        }

                        if (is_callable($route['callback'])) {
                            $response = call_user_func_array($route['callback'], $params);

                        } else {
                            list($controller, $methodName) = $route['callback'];
                            $instance = new $controller();

                            $reflection = new \ReflectionMethod($instance, $methodName);
                            $args = [];

                            foreach ($reflection->getParameters() as $param) {
                                $type = $param->getType()?->getName();

                                if ($type === Request::class) {
                                    $args[] = new Request();
                                } elseif (!empty($params)) {
                                    $args[] = array_shift($params);
                                } else {
                                    $args[] = null;
                                }
                            }

                            $response = $instance->$methodName(...$args);

                            if (str_contains($response['message'], 'created')) {
                                return Response::success($response['message'], $response['data'], HttpStatus::CREATED);
                            }

                            return Response::success($response['message'], $response['data'], HttpStatus::OK);
                        }
                        
                        return $response;

                    } catch (Throwable $e) {
                        return $this->handle($e);
                    }
                }

                $allowedMethods[] = $route['method'];
            }
        }

        if (!empty($allowedMethods)) {
            $response = $this->handle(new \Exception('Method Not Allowed'));
            $response['allowed_methods'] = $allowedMethods;
            $GLOBALS['logger']->warning("Method not allowed", [
                'method' => $method,
                'uri'    => $uri,
                'allowed_methods' => $allowedMethods,
                'response' => $response
            ]);
            return $response;
        }

        $response = $this->handle(new \Exception('Route not found'));
        $GLOBALS['logger']->warning("Route not matched", [
            'method' => $method,
            'uri'    => $uri,
            'response' => $response
        ]);
        return $response;
    }

    /**
     * Exception handler
     *
     * @param Throwable $e
     * @return array
     */
    public function handle(Throwable $e):  array
    {   
        $GLOBALS['logger']->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $message = strtolower($e->getMessage());

        if (str_contains($message, 'method not allowed')) {
            return Response::failed($e->getMessage(), HttpStatus::METHOD_NOT_ALLOWED);
        }

        if (str_contains($message, 'not found')) {
            return Response::failed($e->getMessage(), HttpStatus::NOT_FOUND);
        }

        if (str_contains($message, 'invalid') || str_contains($message, 'required')) {
            return Response::failed($e->getMessage(), HttpStatus::BAD_REQUEST);
        }

        return Response::failed("Internal Server Error", HttpStatus::INTERNAL_SERVER_ERROR);
    }

    /**
     * Load logs and middleware
     *
     * @return self
     */
    public function loadMiddleware(): self
    {
        $this->middleware(function ($response = null) {

            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $uri    = $_SERVER['REQUEST_URI'] ?? '';
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            $GLOBALS['logger']->info("Request received", [
                'method' => $method,
                'uri' => $uri,
                'content_type' => $contentType,
                'time' => date('Y-m-d H:i:s')
            ]);

            if (in_array($method, ['POST','PUT','PATCH'])) {
                if (
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
            }

            if ($response !== null) {
                $GLOBALS['logger']->info("Response returned", [
                    'method' => $method,
                    'uri' => $uri,
                    'response' => $response
                ]);
            }

            return null;
        });

        return $this;
    }
}