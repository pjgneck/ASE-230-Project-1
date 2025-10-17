<?php
class ItemDatabase {
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
    // Item-specific methods
    // -----------------------------

    public function getAllItems() {
        return $this->select("SELECT * FROM items");
    }

    public function getItemById($id) {
        $results = $this->select("SELECT * FROM items WHERE id = ?", [$id]);
        return $results[0] ?? null;
    }

    public function getItemsByInventory($inventory_id) {
        return $this->select("SELECT * FROM items WHERE inventory_id = ?", [$inventory_id]);
    }

    public function addItem($name, $quantity, $inventory_id) {
        return $this->insert("INSERT INTO items (name, quantity, inventory_id) VALUES (?, ?, ?)", [$name, $quantity, $inventory_id]);
    }

    public function updateItem($id, $name, $quantity) {
        return $this->execute("UPDATE items SET name = ?, quantity = ? WHERE id = ?", [$name, $quantity, $id]);
    }

    public function deleteItem($id) {
        return $this->execute("DELETE FROM items WHERE id = ?", [$id]);
    }
}
?>
