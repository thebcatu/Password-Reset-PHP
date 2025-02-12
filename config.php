<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resetpassworddemo');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("SET time_zone = '+00:00'");

$createDB = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
$conn->query($createDB);
$conn->select_db(DB_NAME);

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createOTPTable = "CREATE TABLE IF NOT EXISTS password_reset_otp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expiry_time TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createTokensTable = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry_time TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($createUsersTable);
$conn->query($createOTPTable);
$conn->query($createTokensTable);

$defaultUser = "INSERT IGNORE INTO users (email, password) VALUES 
('contact@mahendrasingh.com.np', '" . password_hash('Demo@123', PASSWORD_DEFAULT) . "')";
$conn->query($defaultUser);
?>