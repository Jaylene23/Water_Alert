<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname   = "water_alert_db";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);
$conn->set_charset("utf8mb4");

// Check login
function isLoggedIn(){
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Helper function
function getTankStatus($conn){
    $stmt = $conn->prepare("SELECT * FROM tank_status ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>