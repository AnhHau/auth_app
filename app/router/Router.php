<?php

namespace App\Router;

class Router
{
    private $routes = [];

    public function add($method, $path, $controller)
    {
        $this->routes[] = ['method' => $method, 'path' => $path, 'controller' => $controller];
    }

    public function dispatch($method, $uri)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                list($class, $method) = explode('@', $route['controller']);
                $class = 'App\\Controllers\\' . $class;
                if (class_exists($class) && method_exists($class, $method)) {
                    $controller = new $class();
                    return $controller->$method();
                }
            }
        }
        http_response_code(404);
        echo "404 Not Found";
    }
}
// require_once './api.php';
