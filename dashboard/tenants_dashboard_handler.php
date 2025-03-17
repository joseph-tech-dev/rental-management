<?php
session_start();

// Enable error reporting for development
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Include database connection (adjust the path as needed)
require_once '/opt/lampp/htdocs/project/config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get tenant's user_id
$user_id = $_SESSION['user_id'];

try {
    // Prepare query to fetch tenant information
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user exists
    if (!$user) {
        throw new Exception("User not found");
    }

    // Fetch properties available for the tenant to view
    $query = "SELECT * FROM properties WHERE status = 'available'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's leases
    $query = "SELECT * FROM leases WHERE tenant_id = :tenant_id AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch maintenance requests made by the tenant
    $query = "SELECT * FROM maintenance_requests WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $maintenance_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's rent payments
    $query = "SELECT * FROM payments WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tenant's complaints
    $query = "SELECT * FROM complaints WHERE tenant_id = :tenant_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':tenant_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch notifications for the tenant
    $query = "SELECT * FROM notifications WHERE user_id = :user_id AND status = 'unread'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle database errors gracefully (log them, display a user-friendly message)
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    // Handle other exceptions
    die("An error occurred: " . $e->getMessage());
}

// Include header file (adjust the path as needed)
include '../includes/header.php';
?>
