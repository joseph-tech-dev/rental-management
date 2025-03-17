<?php
// Include the database configuration file
require_once 'config.php';

// Create the rooms table
$sql_rooms = "CREATE TABLE IF NOT EXISTS rooms (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('vacant', 'occupied') NOT NULL DEFAULT 'vacant',
    price DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create the users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create the bookings table
$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create the payments table
$sql_payments = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the SQL statements
try {
    // Create rooms table
    if ($conn->query($sql_rooms) === TRUE) {
        echo "Table 'rooms' created successfully<br>";
    } else {
        echo "Error creating 'rooms' table: " . $conn->error . "<br>";
    }
    
    // Create users table
    if ($conn->query($sql_users) === TRUE) {
        echo "Table 'users' created successfully<br>";
    } else {
        echo "Error creating 'users' table: " . $conn->error . "<br>";
    }
    
    // Create bookings table
    if ($conn->query($sql_bookings) === TRUE) {
        echo "Table 'bookings' created successfully<br>";
    } else {
        echo "Error creating 'bookings' table: " . $conn->error . "<br>";
    }
    
    // Create payments table
    if ($conn->query($sql_payments) === TRUE) {
        echo "Table 'payments' created successfully<br>";
    } else {
        echo "Error creating 'payments' table: " . $conn->error . "<br>";
    }
    
    echo "Database structure created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn->close();
?>

