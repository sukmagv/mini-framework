<?php

namespace Core;

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
                    foreach ($route['middleware'] as $mw) {
                        $mwResult = $mw();

                        if ($mwResult !== null) {
                            $GLOBALS['logger']->app("INFO", "Request blocked by middleware", [
                                'route' => $route['path'],
                                'response' => $mwResult
                            ]);

                            echo json_encode($mwResult);
                            return null;
                        }
                    }

                    if (is_callable($route['callback'])) {

                        $GLOBALS['logger']->app("INFO", "Route matched function callback", [
                            'route' => $route['path']
                        ]);

                        $response = call_user_func_array($route['callback'], $params);

                    } else {
                        list($controller, $methodName) = $route['callback'];
                        $instance = new $controller();

                        $GLOBALS['logger']->app("INFO", "Route matched controller", [
                            'controller' => $controller,
                            'method'     => $methodName
                        ]);

                        $response = $instance->$methodName(...$params);
                    }

                    $GLOBALS['logger']->app("INFO","Response returned", [
                        'method' => $method,
                        'url'    => $uri,
                        'response' => $response
                    ]);

                    echo json_encode($response);
                    
                    return $response;
                }

                $allowedMethods[] = $route['method'];
            }
        }

        if (!empty($allowedMethods)) {
            die(json_encode(Response::failed(
                'Method Not Allowed', 
                405
            ) + ['allowed_methods' => $allowedMethods]));
        }

        die(json_encode(Response::failed(
            'Route Not Found', 
            404
        )));

    }
}