<?php
session_start();

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection - DIRECT CONNECTION (without environment variables)
$servername = "localhost";
$username   = "root";
$password   = "";  // Blank password
$dbname     = "water_alert_db";
$port       = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Database connection error. Please try again later.");
}
$conn->set_charset("utf8mb4");

// Rest of your functions remain the same...
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