<?php
declare(strict_types=1);

namespace Den;

use Den\DB\Database;

final class Application
{
    public Router $router;
    public Response $response;
    public Database $db;
    public Session $session;

    public static Application $app;

    public static string $ROOT_DIR;

    public function __construct(string $rootDir, array $config)
    {
        self::$ROOT_DIR = $rootDir;

        $this->router = new Router();
        $this->response = new Response();
        $this->db = new Database($config['db']);
        $this->session = new Session();

        self::$app = $this;
    }

    public function run(): void
    {
        echo $this->router->resolve();
    }
}
