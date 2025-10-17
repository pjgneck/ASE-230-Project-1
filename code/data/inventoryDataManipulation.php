<?php
class InventoryDatabase {
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

    // Generic SELECT
    public function select($query, $params = []) {
        $stmt = $this->prepareAndBind($query, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // Generic INSERT
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

    // Prepare and bind params
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

    private function getParamType($param) {
        return match (true) {
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
    // Inventory-specific methods
    // -----------------------------

    public function getAllInventories() {
        return $this->select("SELECT * FROM inventories");
    }

    public function getInventoryById($id) {
        $results = $this->select("SELECT * FROM inventories WHERE id = ?", [$id]);
        return $results[0] ?? null;
    }

    public function getInventoriesByDepartment($department_id) {
        return $this->select("SELECT * FROM inventories WHERE department_id = ?", [$department_id]);
    }

    public function addInventory($name, $department_id) {
        return $this->insert("INSERT INTO inventories (name, department_id) VALUES (?, ?)", [$name, $department_id]);
    }

    public function updateInventory($id, $name, $department_id) {
        return $this->execute("UPDATE inventories SET name = ?, department_id = ? WHERE id = ?", [$name, $department_id, $id]);
    }

    public function deleteInventory($id) {
        return $this->execute("DELETE FROM inventories WHERE id = ?", [$id]);
    }
}
?>
