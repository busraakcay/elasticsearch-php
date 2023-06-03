<?php

class DBConnection
{
    private $conn;

    public function __construct()
    {
        $this->conn = null;
    }

    public function connect()
    {
        require_once 'app/config/db_config.php';

        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set other PDO attributes if needed (e.g., character set)
            // $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // $this->conn->exec("SET NAMES utf8");
        } catch (PDOException $e) {
            echo "Database connection failed: " . $e->getMessage();
        }

        return $this->conn;
    }
}
