<?php
require_once './apis/userHandlers.php';
require_once './apis/departmentHandlers.php';
require_once './apis/inventoryHandlers.php';
require_once './apis/itemHandlers.php';
require_once './auth/mainAuth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse the URL path
$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = explode('/', $path);

$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$subId = $segments[2] ?? null;

// Redirect to dashboard if root
if (empty($resource)) {
    header("Location: pages/");
    exit();
}

/* -------------------- AUTH -------------------- */

// 🔐 SIGNUP
if ($resource === 'signup' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    $department = $input['department'] ?? null;

    if (!$username || !$password || !$department) {
        http_response_code(400);
        echo json_encode(['error' => 'Username, password, and department are required']);
        exit;
    }

    $result = sign_up_user($username, $password, $department);
    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
    exit;
}

// 🔐 LOGIN
if ($resource === 'login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        exit;
    }

    $result = login_user($username, $password);
    http_response_code($result['success'] ? 200 : 401);
    echo json_encode($result);
    exit;
}

/* -------------------- USERS -------------------- */
if ($resource === 'users') {
    requireAuth();
    $user_id = isset($id) ? (int)$id : null;

    switch ($method) {
        case 'GET':
            $user_id ? get_User_by_id($user_id) : get_users();
            break;

        case 'PUT':
            if ($user_id) {
                $input = json_decode(file_get_contents('php://input'), true);
                $username = $input['username'] ?? null;
                $password = $input['password'] ?? null;
                $department_id = $input['department_id'] ?? null;
                $result = update_user_by_id($user_id, $username, $password, $department_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'User ID required']);
            }
            break;

        case 'DELETE':
            if ($user_id) {
                $result = delete_user_by_id($user_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'User ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}

/* -------------------- DEPARTMENTS -------------------- */
if ($resource === 'department') {
    requireAuth();
    $department_id = isset($id) ? (int)$id : null;

    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;

    switch ($method) {
        case 'GET':
            if ($department_id) {
                get_department_by_id($department_id);
            } else {
                get_department($token);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? null;
            if (!$name) {
                http_response_code(400);
                echo json_encode(['error' => 'Department name required']);
                break;
            }
            $result = add_department($name);
            http_response_code($result['success'] ? 201 : 500);
            echo json_encode($result);
            break;

        case 'PUT':
            if ($department_id) {
                $input = json_decode(file_get_contents('php://input'), true);
                $name = $input['name'] ?? null;
                $result = update_department_by_id($department_id, $name);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Department ID required']);
            }
            break;

        case 'DELETE':
            if ($department_id) {
                $result = delete_department_by_id($department_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Department ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}

/* -------------------- INVENTORY -------------------- */
if ($resource === 'inventory') {
    requireAuth();
    $inventory_id = isset($id) ? (int)$id : null;

    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;

    switch ($method) {
        case 'GET':
            if ($inventory_id && $segments[1] === 'department') {
                $dept_id = (int)$segments[2];
                get_inventories_by_department($dept_id);
            } elseif ($inventory_id) {
                get_inventory_by_id($inventory_id);
            } else {
                get_inventories($token);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? null;
            $department_id = $input['department_id'] ?? null;
            $result = add_inventory($name, $department_id);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'PUT':
            if ($inventory_id) {
                $input = json_decode(file_get_contents('php://input'), true);
                $name = $input['name'] ?? null;
                $department_id = $input['department_id'] ?? null;
                $result = update_inventory_by_id($inventory_id, $name, $department_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Inventory ID required']);
            }
            break;

        case 'DELETE':
            if ($inventory_id) {
                $result = delete_inventory_by_id($inventory_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Inventory ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}

/* -------------------- ITEMS -------------------- */
if ($resource === 'item') {
    requireAuth();
    $item_id = isset($id) ? (int)$id : null;

    switch ($method) {
        case 'GET':
            if ($segments[1] === 'inventory' && isset($segments[2])) {
                $inventory_id = (int)$segments[2];
                get_items_by_inventory($inventory_id);
            } elseif ($item_id) {
                get_item_by_id($item_id);
            } else {
                get_items();
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? null;
            $quantity = $input['quantity'] ?? null;
            $inventory_id = $input['inventory_id'] ?? null;
            $result = add_item($name, $quantity, $inventory_id);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'PUT':
            if ($item_id) {
                $input = json_decode(file_get_contents('php://input'), true);
                $name = $input['name'] ?? null;
                $quantity = $input['quantity'] ?? null;
                $result = update_item_by_id($item_id, $name, $quantity);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Item ID required']);
            }
            break;

        case 'DELETE':
            if ($item_id) {
                $result = delete_item_by_id($item_id);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Item ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}

// If route not matched
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
