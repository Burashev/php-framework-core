<?php
declare(strict_types=1);
require "vendor/autoload.php";

$config = [
    "db" => [
        "dsn" => "mysql:host=localhost;port=3306;dbname=test",
        "username" => "root",
        "password" => "rootroot"
    ]
];

$app = new \Den\Application(dirname(__DIR__), $config);
