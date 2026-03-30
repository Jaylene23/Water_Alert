<?php
include 'config.php';

$water   = (int)($_GET['water']   ?? 0);
$battery = (int)($_GET['battery'] ?? 100);
$signal  = strtoupper(preg_replace('/[^A-Za-z_]/', '', $_GET['signal'] ?? 'GOOD'));
$status  = ($water < 20) ? 'LEAK' : 'SAFE';

if (!in_array($signal, ['GOOD', 'WEAK', 'NO_SIGNAL'])) $signal = 'GOOD';

$conn->query("UPDATE tank_status SET
    water_level      = '$water',
    battery          = '$battery',
    signal_strength  = '$signal',
    status           = '$status',
    last_updated     = NOW()
  WHERE id = 1");

$conn->query("INSERT INTO leak_logs (status, source) VALUES ('$status', 'ESP32')");

if ($water < 20) {
    $msg = "Leak Alert! Water level at {$water}%";
    $conn->query("INSERT INTO alerts (alert_type, message) VALUES ('LEAK', '$msg')");
}

http_response_code(200);
echo "OK";
?>
