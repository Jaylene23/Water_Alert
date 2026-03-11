<?php
include 'config.php';

// Replace these with your desired username/password
$username = 'admin';
$password = 'admin123'; // Plain password

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

if($stmt->execute()){
    echo "User '$username' created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>