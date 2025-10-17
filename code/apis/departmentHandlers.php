<?php
require_once './data/departmentDataManipulation.php';
require_once './auth/bearer_auth.php';

function get_department_by_id($id) {
    $db = new DepartmentDatabase();
    $department = $db->getDepartmentById($id);

    $db->close();
}


function add_department($name) {
    $db = new DepartmentDatabase();

    $insertId = $db->addDepartment($name);

    $db->close();

    return [
        "success" => $insertId > 0,
        "inserted_id" => $insertId
    ];
}


function update_department_by_id($id, $name = null) {
    $db = new DepartmentDatabase();

    if ($name === null) {
        $db->close();
        return ["error" => "No name provided"];
    }

    $rows = $db->updateDepartment($id, $name);

    $db->close();

    return [
        "success" => $rows > 0,
        "updated_id" => $id,
        "rows_affected" => $rows
    ];
}


function delete_department_by_id($id) {
    $db = new DepartmentDatabase();

    $rows = $db->deleteDepartment($id);

    $db->close();

    return [
        "success" => $rows > 0,
        "deleted_id" => $id,
        "rows_affected" => $rows
    ];
}


function get_department($token) {
    $db = new DepartmentDatabase();

    // Verify token and get user’s department
    $userResult = $db->select("
        SELECT d.id, d.name 
        FROM users u
        JOIN departments d ON u.department_id = d.id
        WHERE u.token = ?
    ", [$token]);

    header('Content-Type: application/json');
    echo json_encode($userResult, JSON_PRETTY_PRINT);

    $db->close();
}
?>
