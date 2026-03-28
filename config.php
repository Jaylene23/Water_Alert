<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "water_alert_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

function isLoggedIn()  { return isset($_SESSION['user']) && !empty($_SESSION['user']); }
function getRole()     { return $_SESSION['role'] ?? ''; }
function isAdmin()     { return getRole() === 'admin'; }
function isManager()   { return getRole() === 'manager'; }
function isUser()      { return getRole() === 'user'; }

function requireRole(...$roles) {
    if (!isLoggedIn() || !in_array(getRole(), $roles)) {
        header("Location: login.php");
        exit;
    }
}

function getTankStatus($conn) {
    $result = $conn->query("SELECT * FROM tank_status WHERE id = 1");
    return $result->fetch_assoc();
}
?>
