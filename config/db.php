<?php
$host = "127.0.0.1";
$dbname = "rental_management";
$username = "root";  // Change if necessary
$password = "hawk@2024";      // Change if necessary

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
  