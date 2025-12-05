<?php

namespace Core;

use Dotenv\Dotenv;

/**
 * Base database class for managing MySQLi connections and singleton access
 */

class Database 
{
    private string $hostname;
    private string $username;
    private string $password;
    private string $database;
    private \mysqli $connection;
    private static ?Database $instance = null;

    /**
     * Initialize the database connection.
     *
     * Load DB credentials from .env and establish MySQLi connection.
     * Dies if credentials are missing or connection fails.
     */
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

        $connect = new \mysqli(
            $this->hostname,
            $this->username,
            $this->password,
            $this->database
        );

        if ($connect->connect_error) {
            die("Koneksi gagal:  {$connect->connect_error}");
        }

        $this->connection = $connect;
    }

    /**
     * Get the active MySQLi connection
     *
     * @return \mysqli
     */
    public function connection(): \mysqli
    {
        return $this->connection;
    }

    /**
     * Get the singleton instance of the Database class.
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database;
        }
        return self::$instance;
    }
}

?>