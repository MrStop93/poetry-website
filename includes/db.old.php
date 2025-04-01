<?php
require_once 'config.php';

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'poetry_db';
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function sanitize($data) {
        return $this->conn->real_escape_string(htmlspecialchars(trim($data)));
    }
}

$db = new Database();



?>