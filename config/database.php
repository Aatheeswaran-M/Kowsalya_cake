<?php
// config/database.php

class Database {
    private $host = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
    private $port = 4000;
    private $db_name = "test";
    private $username = "FSQsQmvtgfFX5Nt.root";
    private $password = "Vn3D4RqPmQ6Tx3Vc";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_SSL_CA => true,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::ATTR_TIMEOUT => 10
            );
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                $options
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

?>



