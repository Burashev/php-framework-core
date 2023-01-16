<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private Request $request;
    private array $routes;

    public function __construct()
    {
        $this->request = new Request();
        $this->routes = [];
    }

    public function get(string $path, \Closure|string|array $callback): void
    {
        $this->routes["GET"][$path] = $callback;
    }

    public function post(string $path, \Closure|string|array $callback): void
    {
        $this->routes["POST"][$path] = $callback;
    }

    public function resolve(): mixed
    {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        if (!array_key_exists($method, $this->routes)) {
            echo "Method {$method} doesn't exist in routes array";
            Application::$app->response->setStatusCode(404);
            die();
        }
        if (!array_key_exists($path, $this->routes[$method])) {
            echo "Path {$path} doesn't exist in {$method} routes";
            Application::$app->response->setStatusCode(404);
            die();
        }

        $callback = $this->routes[$method][$path];

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            $callback[0] = new $callback[0]();
        }

        return call_user_func($callback, $this->request);
    }

    public function renderView($view, array $data = []): string
    {
        $viewContent = $this->viewContent($view, $data);
        $layoutName = $this->getLayoutNameFromViewContent($viewContent);

        if (!$layoutName) {
            return $viewContent;
        }

        $layoutContent = $this->layoutContent($layoutName);
        return str_replace("{{ content }}", $viewContent, $layoutContent);
    }

    protected function layoutContent(string $layout): string
    {
        ob_start();
        include_once Application::$ROOT_DIR . "/Views/layouts/{$layout}.view.php";
        return ob_get_clean();
    }

    protected function viewContent(string $view, array $data): string
    {

        foreach ($data as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include_once Application::$ROOT_DIR . "/Views/{$view}.view.php";
        return ob_get_clean();
    }

    protected function getLayoutNameFromViewContent(string &$content): string|null {
        $regex = "/^@layout\('(?P<layout>.+)'\)/";
        preg_match($regex, $content, $matches);

        if (!array_key_exists("layout", $matches)) {
            return null;
        }

        $content = preg_replace($regex, "", $content); // TODO: Normal replacement for @pattern

        return $matches["layout"];
    }
}
