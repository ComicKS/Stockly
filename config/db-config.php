<?php

class Database {
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "db_simplecrud";
    public $conn;

    public function __construct() {
        $this->getConnection();
    }

    public function getConnection() {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->conn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            $this->conn->set_charset('utf8mb4');
        } catch (Exception $e) {
            die('Connection error: ' . $e->getMessage());
        }
    }

    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollback();
    }
}

?>