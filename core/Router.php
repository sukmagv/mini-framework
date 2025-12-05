<?php

namespace Core;

/**
 * Base router class for registering and executing HTTP routes
 */
class Router
{
    private array $routes = [];

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
            'callback' => $callback
        ];
    }

    /**
     * Run the router and execute the matching route callback
     *
     * @return mixed
     */
    public function run(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route) {
            if ($route['method'] != $method) {
                continue;
            }

            $regexPattern = preg_replace_callback('/:\w+/', function ($params) {
                return '([^/]+)';
                },
                $route['path']
            );
    
            if (preg_match("#^{$regexPattern}$#", $uri, $params)) {
                array_shift($params);

                if (is_callable($route['callback'])) {
                    return call_user_func_array($route['callback'], $params);
                } else {
                    list($controller, $method) = $route['callback'];
                    $instance = new $controller();
                    return $instance->$method(...$params);
                }
            }
        }

        header('HTTP/1.1 404 Not Found');
        die(json_encode(['status' => 'failed', 'message' => 'Route Not Found']));
    }
}