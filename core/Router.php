<?php
namespace Core;

/**
 * Class Router untuk menangani routing request
 */
class Router
{
    private array $routes = [];
    private string $currentRoute = '';
    private array $params = [];

    public function add($method, $route, $controller, $action)
    {
        $this->routes[$method][$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function get($route, $controller, $action)
    {
        $this->add('GET', $route, $controller, $action);
    }

    public function post($route, $controller, $action)
    {
        $this->add('POST', $route, $controller, $action);
    }

    public function match($url, $method)
    {
        foreach ($this->routes[$method] as $route => $params) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $url, $matches)) {
                $this->currentRoute = $route;
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    private function convertRouteToRegex($route)
    {
        return '#^' . preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $route) . '$#';
    }

    public function dispatch()
    {
        $url = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        if ($this->match($url, $method)) {
            $controller = $this->params['controller'];
            $action = $this->params['action'];
            
            $controllerClass = "App\\Controllers\\{$controller}";
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $action)) {
                    return $controllerInstance->$action();
                }
            }
        }
        
        // Jika route tidak ditemukan
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
} 