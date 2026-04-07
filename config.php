<?php
session_start();

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
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

// Authentication functions
function isLoggedIn() { 
    return isset($_SESSION['user']) && !empty($_SESSION['user']); 
}

function getRole() { 
    return $_SESSION['role'] ?? ''; 
}

function isAdmin() { 
    return getRole() === 'admin'; 
}

function isManager() { 
    return getRole() === 'manager'; 
}

function isUser() { 
    return getRole() === 'user'; 
}

function requireRole(...$roles) {
    if (!isLoggedIn() || !in_array(getRole(), $roles)) {
        header("Location: login.php");
        exit;
    }
}

function getTankStatus($conn) {
    // Check if there's any data in tank_status table
    $result = $conn->query("SELECT * FROM tank_status LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $tank = $result->fetch_assoc();
        // Map the fields to match what the frontend expects
        return [
            'water_level' => $tank['water_level'],
            'status' => $tank['status'],
            'battery' => $tank['battery'],
            'signal_strength' => $tank['signal_strength'],
            'valve_state' => $tank['valve_state'],
            'last_updated' => $tank['last_updated']
        ];
    }
    
    // If no data exists, insert default data
    $conn->query("
        INSERT INTO tank_status (tank_name, latitude, longitude, water_level, status, valve_state, battery, signal_strength, last_updated) 
        VALUES ('Main Tank', 0, 0, 75, 'SAFE', 'OPEN', 85, 'GOOD', NOW())
    ");
    
    // Return the newly inserted data
    $result = $conn->query("SELECT * FROM tank_status LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $tank = $result->fetch_assoc();
        return [
            'water_level' => $tank['water_level'],
            'status' => $tank['status'],
            'battery' => $tank['battery'],
            'signal_strength' => $tank['signal_strength'],
            'valve_state' => $tank['valve_state'],
            'last_updated' => $tank['last_updated']
        ];
    }
    
    // Ultimate fallback - return default values
    return [
        'water_level' => 75,
        'status' => 'SAFE',
        'battery' => 85,
        'signal_strength' => 'GOOD',
        'valve_state' => 'OPEN',
        'last_updated' => date('Y-m-d H:i:s')
    ];
}
?>