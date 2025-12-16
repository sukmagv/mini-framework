<?php

namespace Core;

use Throwable;
use Enums\HttpMethod;
use Enums\HttpStatus;

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
     * @param HttpMethod $method
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function add(HttpMethod $method, string $path, callable|array $callback): void
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
     *
     * @param callable $func
     * @return self
     */
    public function middleware(callable $func): self
    {
        $this->globalMiddleware[] = $func;
        return $this;
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

    /**
     * Run the middleware
     *
     * @param array $middleware
     * @return array|null
     */
    private function runMiddleware(array $middleware): ?array
    {
        foreach ($middleware as $mw) {
            $result = $mw();
            if ($result !== null) return $result;
        }
        return null;
    }

    /**
     * Check ID parameter to make sure param is existed and numeric type
     *
     * @param string $path
     * @param array $params
     * @return void
     */
    private function checkParam(string $path, array $params): void
    {
        preg_match_all('/:(\w+)/', $path, $matches);
        
        foreach ($matches[1] as $index => $paramName) {
            $value = $params[$index] ?? null;

            if ($value === null) {
                throw new \Exception("{$paramName} is required");
            }

            if (is_numeric($value) === false) {
                throw new \Exception("{$paramName} must be number");
            }
        }
    }

    /**
     * Run related controller
     *
     * @param callable|array $callback
     * @param array $params
     * @return array
     */
    private function runController(callable|array $callback, array $params): array
    {
        if (is_callable($callback)) {
            return $callback(...$params);
        }

        [$controller, $methodName] = $callback;
        $instance = new $controller();
        $reflection = new \ReflectionMethod($instance, $methodName);
        $args = [];

        $args = array_map(
            fn($p) =>
                $p->getType()?->getName() === Request::class
                    ? new Request()
                    : array_shift($params),
            $reflection->getParameters()
        );

        return $reflection->invoke($instance, ...$args);
    }

    /**
     * To handle allowed HTTP methods
     *
     * @param HttpMethod $method
     * @param string $uri
     * @param array $allowedMethods
     * @return void
     */
    private function handleAllowedMethods(HttpMethod $method, string $uri, array $allowedMethods): void
    {
        $GLOBALS['logger']->warning("Method not allowed", [
            'method' => $method,
            'uri' => $uri,
            'allowed_methods' => $allowedMethods,
        ]);

        throw new \Exception("Method Not Allowed");
    }

    /**
     * Run the router and execute the matching route callback
     *
     * @return mixed
     */
    public function run(): mixed
    {
        try {
            $method = HttpMethod::from($_SERVER['REQUEST_METHOD']);
            $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $allowedMethods = [];

            foreach ($this->routes as $route) {
                $regexPattern = preg_replace_callback('/:\w+/', fn($m) => '([^/]+)', $route['path']);

                if (preg_match("#^{$regexPattern}$#", $uri, $params)) {
                    array_shift($params);

                    if ($route['method'] === $method) {
                        $this->checkParam($route['path'], $params);

                        if ($mwResponse = $this->runMiddleware($route['middleware'])) {
                            return $mwResponse;
                        }

                        return $this->runController($route['callback'], $params);
                    }

                    $allowedMethods[] = $route['method'];
                }
            }

            if (!empty($allowedMethods)) {
                $this->handleAllowedMethods($method, $uri, $allowedMethods);
            }

            throw new \Exception("Route not found");

        } catch (Throwable $e) {
            return ErrorHandler::handle($e);
        }
    }

    /**
     * Register a GET route
     *
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function get(string $path, callable|array $callback): void
    {
        $this->add(HttpMethod::GET, $path, $callback);
    }

    /**
     * Register a POST route
     *
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function post(string $path, callable|array $callback): void
    {
        $this->add(HttpMethod::POST, $path, $callback);
    }

    /**
     * Register a PUT route
     *
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function put(string $path, callable|array $callback): void
    {
        $this->add(HttpMethod::PUT, $path, $callback);
    }

    /**
     * Register a DELETE route
     *
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    public function delete(string $path, callable|array $callback): void
    {
        $this->add(HttpMethod::DELETE, $path, $callback);
    }
}