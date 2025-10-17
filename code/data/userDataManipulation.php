<?php
class userDatabase {
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $conn;

    // Constructor to initialize DB credentials
    public function __construct() {
        $this->host = "localhost";
        $this->username = "root";
        $this->password = "password123";
        $this->dbname = "inventory_system";
        $this->connect();
    }

    // Connect to the database
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    // Execute a SELECT query and return results as associative array
    public function select($query, $params = []) {
        $stmt = $this->prepareAndBind($query, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // Execute INSERT query and return inserted ID
    public function insert($query, $params = []) {
    $stmt = $this->prepareAndBind($query, $params);

    if ($stmt === false) {
        // Optionally throw an exception or handle error
        die("Prepare failed: " . $this->conn->error);
    }

    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();
    return $insertId;
}

    // Execute UPDATE or DELETE query and return affected rows
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

    // Close connection
    public function close() {
        $this->conn->close();
    }
}

// Usage example
/*
$db = new Database('localhost', 'root', '', 'test_db');
$users = $db->select("SELECT * FROM users WHERE age > ?", [25]);
$insertId = $db->insert("INSERT INTO users (name, age) VALUES (?, ?)", ['Alice', 30]);
$rowsAffected = $db->execute("UPDATE users SET age = ? WHERE id = ?", [31, $insertId]);
$db->close();
*/
?>
