<?php
/**
 * Database Configuration File
 * Establishes connection to MySQL database and creates it if it doesn't exist
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'room_management');

// Function to create and return a database connection
function getConnection() {
    static $conn = null;
    
    // If connection already exists, return it
    if ($conn !== null) {
        return $conn;
    }
    
    try {
        // Connect to MySQL server without specifying a database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        // Check for connection errors
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create the database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        if (!$conn->query($sql)) {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Select the database
        if (!$conn->select_db(DB_NAME)) {
            throw new Exception("Error selecting database: " . $conn->error);
        }
        
        return $conn;
    } catch (Exception $e) {
        // Log the error and display a user-friendly message
        error_log("Database connection error: " . $e->getMessage());
        die("Sorry, there was a problem connecting to the database. Please try again later.");
    }
}

// Get the database connection
$conn = getConnection();

// Set character set to UTF-8
if ($conn) {
    $conn->set_charset("utf8mb4");
}
?>

