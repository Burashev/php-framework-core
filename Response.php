<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function setStatusCode($code = 404): void
    {
        http_response_code($code);
    }

    public function redirect(string $location): void
    {
        header("Location: {$location}");
    }
}
