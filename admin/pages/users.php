<?php
session_start();
include '/opt/lampp/htdocs/project/config/db.php'; // Ensure this file connects to your database

header('Content-Type: application/json');

// Fetch all users from database without pagination
$stmt = $conn->prepare("SELECT * FROM users ORDER BY user_id ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "add") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (:full_name, :email, :phone, :role, :password)");
    $stmt->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':role' => $role,
        ':password' => $password
    ]);

    echo json_encode(["message" => "User added successfully!"]);
    exit;
}

// Fetch a single user by ID
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == "fetch" && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }
    exit;
}





// Handle Edit User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "edit") {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET full_name = :full_name, email = :email, phone = :phone, role = :role WHERE user_id = :user_id");
    $stmt->execute([
        ':user_id' => $user_id,
        ':full_name' => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':role' => $role
    ]);

    echo json_encode(["message" => "User updated successfully!"]);
    exit;
}

// Handle Delete User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "delete") {
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    echo json_encode(["message" => "User deleted successfully!"]);
    exit;
}
?>