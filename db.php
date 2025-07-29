<?php
// Database connection parameters
$host = "localhost";
$username = "u8gr0sjr9p4p4";
$password = "9yxuqyo3mt85";
$database = "dbwpowtglcdeak";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");
?>
