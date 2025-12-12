<?php

namespace Core;

class Migrator
{
    private \mysqli $conn;
    private string $path;

    public function __construct(\mysqli $conn, string $path)
    {
        $this->conn = $conn;
        $this->path = $path;
    }

    /**
     * Run migration base on selected migration files
     *
     * @return void
     */
    public function run(): void
    {
        foreach (glob($this->path.'/*.php') as $file) {

            require_once $file;

            $class = "Database\\Migrations\\" . basename($file, ".php");
            $migration = new $class;

            $migration->up($this->conn);
            echo "Migrated: $class\n";
        }
    }

    /**
     * Rollback migration to drop table that has already been created
     *
     * @param integer $steps
     * @return void
     */
    public function rollback(int $steps = 1): void
    {
        $files = array_reverse(glob($this->path.'/*.php'));
        $rollback = array_slice($files, 0, $steps);

        foreach ($rollback as $file) {

            require_once $file;

            $class = "Database\\Migrations\\" . basename($file, ".php");
            $migration = new $class;

            $migration->down($this->conn);
            echo "Rolled Back: $class\n";
        }
    }
}
