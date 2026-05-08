<?php

namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function db(): \mysqli
    {
        return Database::connection();
    }

    protected function view(string $view, array $data = [], ?string $layout = 'main', array $layoutData = []): void
    {
        View::setLayout($layout, $layoutData);
        View::render($view, $data);
    }

    protected function redirect(string $path): void
    {
        $base = Request::detectBasePath();
        $target = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : $base . $path;
        header('Location: ' . $target);
        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['role_id'] ?? 0) != 1) {
            $this->redirect('/dashboard');
        }
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }
}
