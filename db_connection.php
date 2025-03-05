<?php
// Database credentials
$host = "localhost";  // Database host (usually 'localhost')
$username = "root";   // Database username
$password = "";       // Database password (leave empty for XAMPP)
$database = "fedcba";   // The name of your database

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Output a more detailed error message
    die("Connection failed: " . $conn->connect_error);
}  
?>
