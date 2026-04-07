<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, log them
ini_set('log_errors', 1);

// Set JSON header
header("Content-Type: application/json");

// Include config
require_once __DIR__ . '/config.php';

// Function to send JSON response and exit
function sendResponse($success, $data = [], $error = null) {
    $response = ['success' => $success];
    if ($success) {
        $response = array_merge($response, $data);
    } else {
        $response['error'] = $error ?? 'Unknown error';
    }
    echo json_encode($response);
    exit;
}

// Check if config loaded properly
if (!isset($conn) || !$conn) {
    sendResponse(false, [], 'Database connection not established');
}

// Check database connection
if ($conn->connect_error) {
    sendResponse(false, [], 'Database connection failed: ' . $conn->connect_error);
}

// Check authentication
if (!function_exists('isLoggedIn')) {
    sendResponse(false, [], 'Authentication functions not loaded');
}

if (!isLoggedIn()) {
    sendResponse(false, [], 'Unauthenticated. Please login.');
}

// Get action
$action = $_POST['action'] ?? '';

if (empty($action)) {
    sendResponse(false, [], 'No action specified');
}

// Handle different actions
switch($action) {
    case 'get_status':
        if (function_exists('getTankStatus')) {
            $status = getTankStatus($conn);
            sendResponse(true, ['data' => $status]);
        } else {
            sendResponse(false, [], 'getTankStatus function not found');
        }
        break;
        
    case 'get_logs':
        $limit = (function_exists('isUser') && isUser()) ? 10 : 50;
        $result = $conn->query("SELECT * FROM leak_logs ORDER BY created_at DESC LIMIT $limit");
        if (!$result) {
            sendResponse(false, [], 'Query failed: ' . $conn->error);
        }
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        sendResponse(true, ['logs' => $logs]);
        break;
        
    case 'get_alerts':
        $result = $conn->query("SELECT * FROM alerts ORDER BY sent_at DESC LIMIT 20");
        if (!$result) {
            sendResponse(false, [], 'Query failed: ' . $conn->error);
        }
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        sendResponse(true, ['alerts' => $alerts]);
        break;
        
    case 'simulate_leak':
        if (!function_exists('isManager') || !isManager()) {
            sendResponse(false, [], 'Forbidden - Manager access required');
        }
        $status = ($_POST['status'] ?? 'SAFE') === 'LEAK' ? 'LEAK' : 'SAFE';
        $level = ($status === 'LEAK') ? 10 : 75;
        $conn->query("INSERT INTO leak_logs (status, source) VALUES ('$status', 'WEB_SIMULATION')");
        $conn->query("UPDATE tank_status SET water_level='$level', status='$status', last_updated=NOW() WHERE tank_id = 1");
        if ($status === 'LEAK') {
            $msg = "Simulated Leak! Water level at {$level}%";
            $conn->query("INSERT INTO alerts (alert_type, message) VALUES ('LEAK', '$msg')");
        }
        sendResponse(true);
        break;
        
    case 'toggle_valve':
        if (!function_exists('isManager') || !isManager()) {
            sendResponse(false, [], 'Forbidden - Manager access required');
        }
        $current = $_POST['current'] ?? 'OPEN';
        $new = ($current === 'OPEN') ? 'CLOSED' : 'OPEN';
        $conn->query("UPDATE tank_status SET valve_state='$new', last_updated=NOW() WHERE tank_id = 1");
        sendResponse(true, ['new' => $new]);
        break;
        
    case 'get_users':
        if (!function_exists('isAdmin') || !isAdmin()) {
            sendResponse(false, [], 'Forbidden - Admin access required');
        }
        
        // Check if users table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
        if ($tableCheck->num_rows == 0) {
            sendResponse(false, [], 'Users table does not exist');
        }
        
        // Use user_id column
        $result = $conn->query("SELECT user_id, username, role, created_at FROM users ORDER BY user_id ASC");
        if (!$result) {
            sendResponse(false, [], 'Query failed: ' . $conn->error);
        }
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'role' => $row['role'],
                'created_at' => $row['created_at']
            ];
        }
        sendResponse(true, ['users' => $users]);
        break;
        
    case 'create_user':
        if (!function_exists('isAdmin') || !isAdmin()) {
            sendResponse(false, [], 'Forbidden - Admin access required');
        }
        
        $uname = trim($_POST['username'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        $role = trim($_POST['new_role'] ?? 'user');
        
        if (empty($uname) || empty($pass)) {
            sendResponse(false, [], 'Username and password are required');
        }
        
        if (!in_array($role, ['admin', 'manager', 'user'])) {
            sendResponse(false, [], 'Invalid role');
        }
        
        // Check if username exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $uname);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            sendResponse(false, [], 'Username already exists');
        }
        $check->close();
        
        // Insert user with plain text password
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $uname, $pass, $role);
        if ($stmt->execute()) {
            sendResponse(true);
        } else {
            sendResponse(false, [], 'Insert failed: ' . $stmt->error);
        }
        $stmt->close();
        break;
        
    case 'delete_user':
        if (!function_exists('isAdmin') || !isAdmin()) {
            sendResponse(false, [], 'Forbidden - Admin access required');
        }
        
        $user_id = (int)($_POST['uid'] ?? 0);
        
        // Get current user ID from session
        $currentUserId = null;
        if (isset($_SESSION['user_id'])) {
            $currentUserId = $_SESSION['user_id'];
        } else {
            // Try to get from username
            $username = $_SESSION['user'] ?? '';
            if ($username) {
                $result = $conn->query("SELECT user_id FROM users WHERE username = '" . $conn->real_escape_string($username) . "'");
                if ($result && $row = $result->fetch_assoc()) {
                    $currentUserId = $row['user_id'];
                    $_SESSION['user_id'] = $currentUserId; // Store for future use
                }
            }
        }
        
        if ($user_id == 0 || $user_id == $currentUserId) {
            sendResponse(false, [], 'Cannot delete yourself');
        }
        
        // Check last admin
        $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
        $targetRole = $conn->query("SELECT role FROM users WHERE user_id = $user_id")->fetch_assoc()['role'];
        
        if ($targetRole == 'admin' && $adminCount <= 1) {
            sendResponse(false, [], 'Cannot delete the last admin user');
        }
        
        if ($conn->query("DELETE FROM users WHERE user_id = $user_id")) {
            sendResponse(true);
        } else {
            sendResponse(false, [], 'Delete failed: ' . $conn->error);
        }
        break;
        
    case 'get_stats':
        if (!function_exists('isAdmin') || !isAdmin()) {
            sendResponse(false, [], 'Forbidden - Admin access required');
        }
        
        $total_logs = 0;
        $total_leaks = 0;
        $total_alerts = 0;
        $total_users = 0;
        
        $result = $conn->query("SELECT COUNT(*) as c FROM leak_logs");
        if ($result) $total_logs = $result->fetch_assoc()['c'];
        
        $result = $conn->query("SELECT COUNT(*) as c FROM leak_logs WHERE status='LEAK'");
        if ($result) $total_leaks = $result->fetch_assoc()['c'];
        
        $result = $conn->query("SELECT COUNT(*) as c FROM alerts");
        if ($result) $total_alerts = $result->fetch_assoc()['c'];
        
        $result = $conn->query("SELECT COUNT(*) as c FROM users");
        if ($result) $total_users = $result->fetch_assoc()['c'];
        
        sendResponse(true, [
            'stats' => compact('total_logs', 'total_leaks', 'total_alerts', 'total_users')
        ]);
        break;
        
    default:
        sendResponse(false, [], 'Unknown action: ' . $action);
}
?>