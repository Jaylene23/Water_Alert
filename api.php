<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
include 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthenticated']);
    exit;
}

$response = ['success' => false];
$action   = $_POST['action'] ?? '';

/* ── GET STATUS — all roles ── */
if ($action === 'get_status') {
    $response = ['success' => true, 'data' => getTankStatus($conn)];
}

/* ── GET LOGS — all roles; users limited to 10 ── */
elseif ($action === 'get_logs') {
    $limit = isUser() ? 10 : 50;
    $res   = $conn->query("SELECT * FROM leak_logs ORDER BY created_at DESC LIMIT $limit");
    $logs  = [];
    while ($row = $res->fetch_assoc()) $logs[] = $row;
    $response = ['success' => true, 'logs' => $logs];
}

/* ── GET ALERTS — all roles ── */
elseif ($action === 'get_alerts') {
    $res    = $conn->query("SELECT * FROM alerts ORDER BY sent_at DESC LIMIT 20");
    $alerts = [];
    while ($row = $res->fetch_assoc()) $alerts[] = $row;
    $response = ['success' => true, 'alerts' => $alerts];
}

/* ── SIMULATE LEAK — manager only ── */
elseif ($action === 'simulate_leak') {
    if (!isManager()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $status = ($_POST['status'] ?? 'SAFE') === 'LEAK' ? 'LEAK' : 'SAFE';
    $level  = ($status === 'LEAK') ? 10 : 75;
    $conn->query("INSERT INTO leak_logs (status, source) VALUES ('$status', 'WEB_SIMULATION')");
    $conn->query("UPDATE tank_status SET water_level='$level', status='$status', last_updated=NOW() WHERE id=1");
    if ($status === 'LEAK') {
        $msg = "Simulated Leak! Water level at {$level}%";
        $conn->query("INSERT INTO alerts (alert_type, message) VALUES ('LEAK', '$msg')");
    }
    $response = ['success' => true];
}

/* ── TOGGLE VALVE — manager only ── */
elseif ($action === 'toggle_valve') {
    if (!isManager()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $current = $_POST['current'] ?? 'OPEN';
    $new     = ($current === 'OPEN') ? 'CLOSED' : 'OPEN';
    $conn->query("UPDATE tank_status SET valve_state='$new' WHERE id=1");
    $response = ['success' => true, 'new' => $new];
}

/* ── GET USERS — admin only ── */
elseif ($action === 'get_users') {
    if (!isAdmin()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $res   = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
    $users = [];
    while ($row = $res->fetch_assoc()) $users[] = $row;
    $response = ['success' => true, 'users' => $users];
}

/* ── CREATE USER — admin only, plain text password ── */
elseif ($action === 'create_user') {
    if (!isAdmin()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $uname = trim($_POST['username'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $role  = trim($_POST['new_role'] ?? 'user');
    if (!$uname || !$pass || !in_array($role, ['admin', 'manager', 'user'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']); exit;
    }
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $uname, $pass, $role);
    if ($stmt->execute()) $response = ['success' => true];
    else $response = ['success' => false, 'error' => $stmt->error];
}

/* ── DELETE USER — admin only ── */
elseif ($action === 'delete_user') {
    if (!isAdmin()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $id = (int)($_POST['uid'] ?? 0);
    if ($id && $id != ($_SESSION['uid'] ?? 0)) {
        $conn->query("DELETE FROM users WHERE id=$id");
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'error' => 'Cannot delete yourself'];
    }
}

/* ── GET STATS — admin only ── */
elseif ($action === 'get_stats') {
    if (!isAdmin()) { echo json_encode(['success' => false, 'error' => 'Forbidden']); exit; }
    $total_logs   = $conn->query("SELECT COUNT(*) c FROM leak_logs")->fetch_assoc()['c'];
    $total_leaks  = $conn->query("SELECT COUNT(*) c FROM leak_logs WHERE status='LEAK'")->fetch_assoc()['c'];
    $total_alerts = $conn->query("SELECT COUNT(*) c FROM alerts")->fetch_assoc()['c'];
    $total_users  = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
    $response = ['success' => true, 'stats' => compact('total_logs', 'total_leaks', 'total_alerts', 'total_users')];
}

echo json_encode($response);
?>
