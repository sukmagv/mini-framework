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
                            $result = $mw();
                            if ($result !== null) {
                                return $result;
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
                        }

                        $GLOBALS['logger']->app("INFO","Response returned", [
                            'method' => $method,
                            'url'    => $uri,
                            'response' => $response
                        ]);
                        
                        return $response;

                    } catch (\InvalidArgumentException $e) {
                        return Response::failed($e->getMessage(), HttpStatus::BAD_REQUEST);
                    } catch (\Throwable $e) {
                        return Response::failed($e->getMessage(), HttpStatus::INTERNAL_SERVER_ERROR);
                    }
                }

                $allowedMethods[] = $route['method'];
            }
        }

        if (!empty($allowedMethods)) {
            return Response::failed('Method Not Allowed', HttpStatus::METHOD_NOT_ALLOWED) + ['allowed_methods' => $allowedMethods];
        }

        return Response::failed('Route Not Found', HttpStatus::NOT_FOUND);
    }
}