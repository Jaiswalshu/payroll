<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password
$dbname = "PMS";

// Clear any previous output
ob_clean();

// Create connection without specifying database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database server connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Check if database exists, create if it doesn't
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result->num_rows == 0) {
    if (!$conn->query("CREATE DATABASE $dbname")) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create database: ' . $conn->error
        ]);
        exit;
    }
}

// Select the database
$conn->select_db($dbname);

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>