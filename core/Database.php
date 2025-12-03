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

    public function connection() 
    {
        return $this->connection;
    }
}

?>