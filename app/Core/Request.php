<?php

namespace App\Core;

class Request
{
    public string $method;
    public string $path;
    public array  $query;
    public array  $post;
    public array  $files;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = self::detectBasePath();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $this->path = '/' . ltrim($uri, '/');

        $this->query = $_GET;
        $this->post  = $_POST;
        $this->files = $_FILES;
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public static function detectBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base   = rtrim(str_replace('\\', '/', dirname($script)), '/');
        return $base === '/' ? '' : $base;
    }
}
