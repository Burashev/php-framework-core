<?php
declare(strict_types=1);

namespace Den\App\Core;

class Controller
{
    public function render($view, $data = []): string
    {
        return Application::$app->router->renderView($view, $data);
    }
}
