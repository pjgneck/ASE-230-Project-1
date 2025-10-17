<?php
require_once './data/inventoryDataManipulation.php';
require_once './auth/bearer_auth.php';

function get_inventories($token) {
    $db = new InventoryDatabase();
    $inventories = $db->select("
        SELECT i.id, i.name, i.department_id
        FROM inventories i 
        JOIN users u ON u.department_id = i.department_id
        WHERE u.token = ?
    ", [$token]);
    header('Content-Type: application/json');
    echo json_encode($inventories, JSON_PRETTY_PRINT);

    $db->close();
}

function get_inventory_by_id($id) {
    $db = new InventoryDatabase();
    $inventory = $db->select("SELECT id, name, department_id FROM inventories WHERE id = ?", [$id]);

    header('Content-Type: application/json');
    echo json_encode($inventory, JSON_PRETTY_PRINT);

    $db->close();
}

function add_inventory($name, $department_id) {
    $db = new InventoryDatabase();

    $insertId = $db->insert(
        "INSERT INTO inventories (name, department_id) VALUES (?, ?)",
        [$name, $department_id]
    );

    $db->close();

    return [
        "success" => $insertId > 0,
        "inserted_id" => $insertId
    ];
}

function update_inventory_by_id($id, $name = null, $department_id = null) {
    $db = new InventoryDatabase();

    if ($name === null && $department_id === null) {
        $db->close();
        return ["error" => "No fields provided for update"];
    }

    $fields = [];
    $params = [];

    if ($name !== null) {
        $fields[] = "name = ?";
        $params[] = $name;
    }

    if ($department_id !== null) {
        $fields[] = "department_id = ?";
        $params[] = $department_id;
    }

    $params[] = $id;
    $sql = "UPDATE inventories SET " . implode(', ', $fields) . " WHERE id = ?";
    $rows = $db->execute($sql, $params);

    $db->close();

    return [
        "success" => $rows > 0,
        "updated_id" => $id,
        "rows_affected" => $rows
    ];
}

function delete_inventory_by_id($id) {
    $db = new InventoryDatabase();
    $rows = $db->execute("DELETE FROM inventories WHERE id = ?", [$id]);
    $db->close();

    return [
        "success" => $rows > 0,
        "deleted_id" => $id
    ];
}

function get_inventories_by_department($department_id) {
    $db = new InventoryDatabase();
    $inventories = $db->select(
        "SELECT id, name FROM inventories WHERE department_id = ?",
        [$department_id]
    );

    header('Content-Type: application/json');
    echo json_encode($inventories, JSON_PRETTY_PRINT);

    $db->close();
}
?>
