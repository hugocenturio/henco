<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void    { $this->add('GET',    $path, $handler); }
    public function post(string $path, string $handler): void   { $this->add('POST',   $path, $handler); }
    public function any(string $path, string $handler): void    { $this->add('ANY',    $path, $handler); }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $this->compile($path),
            'params'  => $this->paramNames($path),
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $request->method) {
                continue;
            }
            if (preg_match($route['pattern'], $request->path, $matches)) {
                array_shift($matches);
                $params = array_combine($route['params'], $matches) ?: [];
                $this->invoke($route['handler'], $request, $params);
                return;
            }
        }
        $this->notFound();
    }

    private function invoke(string $handler, Request $request, array $params): void
    {
        [$class, $method] = explode('@', $handler);
        $fqcn = "App\\Controllers\\$class";
        if (!class_exists($fqcn) || !method_exists($fqcn, $method)) {
            $this->notFound();
            return;
        }
        $controller = new $fqcn($request);
        $controller->$method($params);
    }

    private function compile(string $path): string
    {
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '([^/]+)', $path);
        return '#^' . rtrim($regex, '/') . '/?$#';
    }

    private function paramNames(string $path): array
    {
        preg_match_all('#\{([a-zA-Z_]+)\}#', $path, $m);
        return $m[1];
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 Not Found</h1>';
        exit;
    }
}
