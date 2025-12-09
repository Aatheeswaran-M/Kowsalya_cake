<?php
// config/database.php

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Load environment variables from .env file if it exists
        $this->loadEnv();
        
        // Set database credentials from environment variables or use defaults
        $this->host = getenv('DB_HOST') ?: "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
        $this->port = getenv('DB_PORT') ?: 4000;
        $this->db_name = getenv('DB_NAME') ?: "test";
        $this->username = getenv('DB_USERNAME') ?: "FSQsQmvtgfFX5Nt.root";
        $this->password = getenv('DB_PASSWORD') ?: "Vn3D4RqPmQ6Tx3Vc";
    }

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || trim($line) === '') {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!empty($key)) {
                        putenv("$key=$value");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }
    }


    public function getConnection() {
        $this->conn = null;
        try {
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_SSL_CA => true,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false
            );
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                $options
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            // Log error instead of exposing it
            error_log("Connection error: " . $exception->getMessage());
            if (getenv('APP_DEBUG') === 'true') {
                echo "Connection error: " . $exception->getMessage();
            } else {
                echo "Database connection error. Please try again later.";
            }
        }
        return $this->conn;
    }
}

?>



