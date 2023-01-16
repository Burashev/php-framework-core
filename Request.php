<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $parsedUrl;

    public function __construct()
    {
        $this->parsedUrl = parse_url($_SERVER['REQUEST_URI'] ?? '/');
    }

    public function getPath(): string
    {
        return $this->parsedUrl['path'];
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function input(): array
    {
        $bodyPOST = [];
        $bodyGET = $this->query();

        foreach ($_POST as $key => $value) {
            $bodyPOST[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return array_merge($bodyGET, $bodyPOST);
    }

    public function query(): array
    {
        $body = [];

        foreach ($_GET as $key => $value) {
            $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $body;
    }


}
