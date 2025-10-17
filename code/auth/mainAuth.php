<?php
function login_user($username, $password) {
    $db = new userDatabase();

    // Query the database for this user
    $sql = "SELECT id, username, password, token FROM users WHERE username = ?";
    $users = $db->select($sql, [$username]);

    // Close connection early if user not found
    if (empty($users)) {
        $db->close();
        return ["success" => false, "error" => "Invalid username or password"];
    }

    $user = $users[0];

    // Verify password
    if (!password_verify($password, $user['password'])) {
        $db->close();
        return ["success" => false, "error" => "Invalid username or password"];
    }

    // Use existing token or generate a new one
    $token = $user['token'];
    if (!$token) {
        $token = generateSecureToken();
        $db->execute("UPDATE users SET token = ? WHERE id = ?", [$token, $user['id']]);
    }

    $db->close();

    // Return user info and token
    return [
        "success" => true,
        "message" => "Login successful",
        "user" => $user['username'],
        "token" => $token,
    ];
}

function sign_up_user($username, $password, $department_name) {
    $db = new userDatabase();

    // Step 1: Get department ID from name
    $deptResult = $db->select("SELECT id FROM departments WHERE name = ?", [$department_name]);
    if (empty($deptResult)) {
        $db->close();
        return ["success" => false, "error" => "Department not found"];
    }
    $department_id = $deptResult[0]['id'];

    // Step 2: Check if username already exists
    $existing = $db->select("SELECT id FROM users WHERE username = ?", [$username]);
    if (!empty($existing)) {
        $db->close();
        return ["success" => false, "error" => "Username already exists"];
    }

    // Step 3: Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Step 4: Generate token
    $token = bin2hex(random_bytes(16));

    // Step 5: Insert user
    $sql = "INSERT INTO users (username, password, department_id, token) VALUES (?, ?, ?, ?)";
    $insertId = $db->insert($sql, [$username, $hashedPassword, $department_id, $token]);

    $db->close();

    // Step 6: Return result
    if ($insertId > 0) {
        return [
            "success" => true,
            "inserted_id" => $insertId,
            "username" => $username,
            "token" => $token
        ];
    } else {
        return ["success" => false, "error" => "Failed to create user"];
    }
}
?>