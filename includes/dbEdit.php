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
        $this->conn->autocommit(false); // تعطيل الإلتزام التلقائي
    }

    // إضافة دوال Transaction
    public function begin_transaction() {
        return $this->conn->begin_transaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollback();
    }

    // دوال أخرى موجودة سابقاً
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Database error: " . $this->conn->error);
        }
        return $result;
    }

    public function sanitize($data) {
        return $this->conn->real_escape_string(htmlspecialchars(trim($data)));
    }

    public function __destruct() {
        $this->conn->close();
    }
}
?>