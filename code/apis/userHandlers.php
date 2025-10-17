<?php
require_once './data/userDataManipulation.php';
require_once './auth/bearer_auth.php';

function get_users() {
    $db = new userDatabase();

    // Join with departments to get department name
    $users = $db->select("
        SELECT u.id, u.username, d.name AS department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
    ");

    header('Content-Type: application/json');
    echo json_encode($users, JSON_PRETTY_PRINT);

    $db->close();
}

function get_User_by_id($user_id) {
    $db = new userDatabase();

    // Join with departments to get department name
    $users = $db->select("
        SELECT u.id, u.username, d.name AS department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.id = ?
    ", [$user_id]);

    header('Content-Type: application/json');
    echo json_encode($users, JSON_PRETTY_PRINT);

    $db->close();
}

function update_user_by_id($id, $username = null, $password = null, $department_id = null) {
    $db = new userDatabase();

    $fields = [];
    $params = [];

    if ($username !== null) {
        $fields[] = "username = ?";
        $params[] = $username;
    }

    if ($password !== null) {
        $fields[] = "password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($department_id !== null) {
        $fields[] = "department_id = ?";
        $params[] = $department_id;
    }

    if (empty($fields)) {
        return ["error" => "No fields to update"];
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $rows = $db->execute($sql, $params);
    $db->close();

    return [
        "success" => $rows > 0,
        "updated_id" => $id,
        "rows_affected" => $rows
    ];
}

function delete_user_by_id($id) {
    $db = new userDatabase();

    $sql = "DELETE FROM users WHERE id = ?";
    $rows = $db->execute($sql, [$id]);

    $db->close();

    return [
        "success" => $rows > 0,
        "deleted_id" => $id,
        "rows_affected" => $rows
    ];
}

function add_user($username, $password, $department_name, $token = null) {
    $db = new userDatabase();

    $deptResult = $db->select("SELECT id FROM departments WHERE name = ?", [$department_name]);
    if (empty($deptResult)) {
        $db->close();
        return ["success" => false, "error" => "Department not found"];
    }

    $department_id = $deptResult[0]['id'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($token === null) {
        $token = generateSecureToken();
    }

    $sql = "INSERT INTO users (username, password, department_id, token) VALUES (?, ?, ?, ?)";
    $insertId = $db->insert($sql, [$username, $hashedPassword, $department_id, $token]);

    $db->close();

    return [
        "success" => $insertId > 0,
        "inserted_id" => $insertId,
        "token" => $token
    ];
}
?>
