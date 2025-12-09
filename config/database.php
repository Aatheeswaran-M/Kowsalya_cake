<?php
// config/database.php

class Database {
   private $host = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
    private $port = 4000;
    private $db_name = "test";
    private $username = "wrvr86JNUurTeWf.root";
    private $password = "qKK70gh40nF28kL0";  // <-- Change immediately
    private $ca = "C:\Users\LAB8\Downloads";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

?>



