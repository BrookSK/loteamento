<?php
declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layout/header.php'): void
    {
        $viewFile = APP_PATH . 'views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada.';
            exit;
        }

        extract($data, EXTR_SKIP);

        require APP_PATH . 'views/' . $layout;
        require $viewFile;
        require APP_PATH . 'views/layout/footer.php';
    }

    protected function viewAuth(string $view, array $data = []): void
    {
        $viewFile = APP_PATH . 'views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada.';
            exit;
        }

        extract($data, EXTR_SKIP);

        require APP_PATH . 'views/layout/auth_layout.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $this->url($path));
        exit;
    }

    protected function url(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return APP_URL . $path;
    }
}
