<?php
// setup_db.php - Database Setup
include 'config.php';

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
}

// Create tank_status table
$sql = "CREATE TABLE IF NOT EXISTS tank_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    water_level DECIMAL(5,2),
    status ENUM('SAFE', 'LEAK', 'LOW') DEFAULT 'SAFE',
    valve_state ENUM('OPEN', 'CLOSED') DEFAULT 'OPEN',
    battery_level INT,
    signal_strength VARCHAR(20),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Create leak_logs table
$sql = "CREATE TABLE IF NOT EXISTS leak_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50),
    source VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Create alerts table
$sql = "CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(50),
    alert_level VARCHAR(20),
    message TEXT,
    status ENUM('ACTIVE', 'ACKNOWLEDGED', 'RESOLVED') DEFAULT 'ACTIVE',
    acknowledged_by VARCHAR(50),
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Insert default data
$sql = "INSERT INTO tank_status (water_level, status, valve_state, battery_level, signal_strength) 
        VALUES (75.00, 'SAFE', 'OPEN', 92, 'Strong')";
$conn->query($sql);

echo "<h2>✅ Database setup complete!</h2>";
echo "<p><a href='index.php'>Go to Dashboard</a></p>";
?>