<?php

namespace Core;

use Dotenv\Dotenv;

class Database 
{
    private $hostname;
    private $username;
    private $password;
    private $database;
    private $connection;
    private static $instance = null;

    public function __construct() 
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/../');
        
        $dotenv->load();
        
        $this->hostname = $_ENV['DB_HOSTNAME'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->database = $_ENV['DB_NAME'];

        foreach (['hostname','username','database'] as $prop) {
            if (empty($this->$prop)) {
                die("Environment variable {$prop} belum di-set!");
            }
        }

        mysqli_report(MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new \mysqli(
                $this->hostname,
                $this->username,
                $this->password,
                $this->database
            );
        } catch (\mysqli_sql_exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function connection() 
    {
        return $this->connection;
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database;
        }
        return self::$instance;
    }
}

?>