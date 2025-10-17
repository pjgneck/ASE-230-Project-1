<?php
class DepartmentDatabase {
    private $host = 'localhost';
    private $username = 'root';
    private $password = 'password123';
    private $dbname = 'inventory_system';
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    // SELECT query
    public function select($query, $params = []) {
        $stmt = $this->prepareAndBind($query, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // INSERT query
    public function insert($query, $params = []) {
        $stmt = $this->prepareAndBind($query, $params);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    // UPDATE or DELETE
    public function execute($query, $params = []) {
        $stmt = $this->prepareAndBind($query, $params);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }

    // Prepare statement and bind parameters
    private function prepareAndBind($query, $params) {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $this->conn->error);
        }

        if ($params) {
            $types = '';
            $values = [];
            foreach ($params as $param) {
                $types .= $this->getParamType($param);
                $values[] = $param;
            }
            $stmt->bind_param($types, ...$values);
        }
        return $stmt;
    }

    // Determine parameter type for bind_param
    private function getParamType($param) {
        return match(true) {
            is_int($param) => 'i',
            is_float($param) => 'd',
            is_string($param) => 's',
            default => 'b',
        };
    }

    public function close() {
        $this->conn->close();
    }

    // -----------------------------
    // Department-specific methods
    // -----------------------------

    public function getAllDepartments() {
        return $this->select("SELECT * FROM departments");
    }

    public function getDepartmentById($id) {
        $results = $this->select("SELECT * FROM departments WHERE id = ?", [$id]);
        return $results[0] ?? null;
    }

    public function addDepartment($name) {
        return $this->insert("INSERT INTO departments (name) VALUES (?)", [$name]);
    }

    public function updateDepartment($id, $name) {
        return $this->execute("UPDATE departments SET name = ? WHERE id = ?", [$name, $id]);
    }

    public function deleteDepartment($id) {
        return $this->execute("DELETE FROM departments WHERE id = ?", [$id]);
    }
}
?>
