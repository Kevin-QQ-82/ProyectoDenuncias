<?php

require_once __DIR__ . "/../config/database.php";

class Controller {
    protected $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    protected function respond($data, $status = 200) {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public function getAllTables() {
        $sql = "SHOW TABLES";
        $result = $this->db->query($sql);

        if (!$result) {
            return [];
        }

        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    public function validateTable($table) {
        $tables = $this->getAllTables();
        return in_array($table, $tables);
    }

    public function getTableColumns($table) {
        $sql = "DESCRIBE $table";
        $result = $this->db->query($sql);

        if (!$result) {
            return null;
        }

        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    public function getIdColumn($table) {
        $sql = "DESCRIBE $table";
        $result = $this->db->query($sql);

        if (!$result) {
            return null;
        }

        while ($row = $result->fetch_assoc()) {
            if (strpos($row['Field'], 'id') === 0) {
                return $row['Field'];
            }
        }

        return null;
    }
}
