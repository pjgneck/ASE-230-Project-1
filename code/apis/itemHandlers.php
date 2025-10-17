<?php
require_once './data/itemDataManipulation.php';
require_once './auth/bearer_auth.php';

function get_items() {
    $db = new ItemDatabase();
    $items = $db->getAllItems(); 

    header('Content-Type: application/json');
    echo json_encode($items, JSON_PRETTY_PRINT);

    $db->close();
}

function get_item_by_id($id) {
    $db = new ItemDatabase();
    $item = $db->getItemById($id);

    header('Content-Type: application/json');
    echo json_encode($item, JSON_PRETTY_PRINT);

    $db->close();
}

function add_item($name, $quantity, $inventory_id) {
    $db = new ItemDatabase();

    $insertId = $db->addItem($name, $quantity, $inventory_id);

    $db->close();

    return [
        "success" => $insertId > 0,
        "inserted_id" => $insertId
    ];
}

function update_item_by_id($id, $name = null, $quantity = null) {
    $db = new ItemDatabase();

    if ($name === null && $quantity === null) {
        $db->close();
        return ["error" => "No fields provided for update"];
    }
    
    $rows = $db->updateItem($id, $name , $quantity);

    $db->close();

    return [
        "success" => $rows > 0,
        "updated_id" => $id,
        "rows_affected" => $rows
    ];
}

function delete_item_by_id($id) {
    $db = new ItemDatabase();
    $rows = $db->deleteItem($id);
    $db->close();

    return [
        "success" => $rows > 0,
        "deleted_id" => $id
    ];
}

function get_items_by_inventory($inventory_id) {
    $db = new ItemDatabase();
   $items = $db->getItemsByInventory($inventory_id);

    header('Content-Type: application/json');
    echo json_encode($items, JSON_PRETTY_PRINT);

    $db->close();
}
?>
