<?php

use App\Core\Request;

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            $_SESSION['flash']['error'][] = 'Invalid request. Please try again.';
            $base = Request::detectBasePath();
            $back = $base . '/';
            header('Location: ' . $back);
            exit;
        }
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $base = Request::detectBasePath();
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        return $base . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/' . ltrim($path, '/'));
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = ''): string
    {
        return e($_POST[$key] ?? $default);
    }
}

if (!function_exists('flash_pull')) {
    function flash_pull(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}

if (!function_exists('translate')) {
    function translate(string $key, array $translations): string
    {
        return $translations[$key] ?? $key;
    }
}
