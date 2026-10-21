<?php

namespace App\Core;

use Exception;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        $normalizedPath = '/' . trim($path, '/');
        // Convert route pattern {param} into regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $normalizedPath);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute middleware pipeline
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $result = $middleware->handle($request, $response);
                    if ($result === false) {
                        return; // Middleware aborted execution
                    }
                }

                // Call handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func($handler, $request, $response, $params);
                    return;
                }

                if (is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller class not found: {$controllerClass}");
                    }
                    $controller = new $controllerClass();
                    if (!method_exists($controller, $action)) {
                        throw new Exception("Action {$action} not found on controller {$controllerClass}");
                    }
                    $controller->$action($request, $response, $params);
                    return;
                }
            }
        }

        // 404 Not Found
        $response->status(404);
        if ($request->isAjax()) {
            $response->json(['error' => 'Endpoint not found', 'status' => 404], 404);
        } else {
            try {
                $html = View::render('errors/404', ['title' => 'Page Not Found - Afriserva Properties']);
                $response->html($html, 404);
            } catch (Exception $e) {
                $response->html("<h1>404 Not Found</h1><p>The requested property or resource could not be found.</p>", 404);
            }
        }
    }
}
