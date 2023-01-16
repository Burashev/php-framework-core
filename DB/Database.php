<?php
declare(strict_types=1);

namespace App\Core\DB;

use App\Core\Application;
use PDO;

final class Database
{
    public PDO $PDO;

    public function __construct(array $config)
    {
        ["dsn" => $dsn, "username" => $username, "password" => $password] = $config;

        $this->PDO = new PDO($dsn, $username, $password);
        $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations(): void
    {
        $newMigrations = [];

        $this->createMigrationsTable();

        $appliedMigrations = $this->getAppliedMigrations();
        $migrations = scandir(Application::$ROOT_DIR . '/migrations');
        $migrationsToApply = array_diff($migrations, $appliedMigrations, ['.', '..']);

        foreach ($migrationsToApply as $migration) {
            $migrationObject = require Application::$ROOT_DIR . "/migrations/$migration";
            echo "Applying migration {$migration}" . PHP_EOL;
            $migrationObject->up();
            echo "Migration {$migration} has been applied" . PHP_EOL;

            $newMigrations[] = $migration;
        }

        if (empty($newMigrations)) {
            echo 'No new migrations' . PHP_EOL;
            return;
        }

        $this->saveNewMigrationsToTable($newMigrations);
        echo 'Migrations have been applied' . PHP_EOL;
    }

    public function rollbackMigrations(): void
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        if (empty($appliedMigrations)) {
            echo 'No applied migrations' . PHP_EOL;
            return;
        }

        foreach ($appliedMigrations as $migration) {
            $migrationObject = require Application::$ROOT_DIR . "/migrations/$migration";
            $migrationObject->down();
        }

        Application::$app->db->PDO->exec("DROP TABLE IF EXISTS migrations;");
    }

    private function createMigrationsTable(): void
    {
        $this->PDO->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=INNODB;
        ");
    }

    private function saveNewMigrationsToTable(array $migrations): bool {
        $query = "INSERT INTO migrations(migration) VALUES ";

        foreach ($migrations as $migration) {
            $query .= "('$migration'),";
        }

        $query[-1] = ';';

        $statement = $this->PDO->prepare($query);

        return $statement->execute();
    }

    private function getAppliedMigrations(): array|false
    {
        $statement = $this->PDO->prepare("SELECT migration from migrations");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
