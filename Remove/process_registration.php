<?php
// Start session
session_start();

// Include database connection
require_once 'config.php';

// Initialize error variable
$error = "";

// Function to validate and sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Store form data in session for sticky form
    $_SESSION['form_data'] = [
        'username' => isset($_POST['username']) ? $_POST['username'] : '',
        'fullname' => isset($_POST['fullname']) ? $_POST['fullname'] : '',
        'email' => isset($_POST['email']) ? $_POST['email'] : ''
    ];
    
    // Validate input fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password']) || 
        empty($_POST['fullname']) || empty($_POST['email'])) {
        $error = "All fields are required";
    } else {
        // Sanitize user inputs
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $fullname = sanitize_input($_POST['fullname']);
        $email = sanitize_input($_POST['email']);
        
        // Validate username (alphanumeric and 3-20 characters)
        if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
            $error = "Username must be 3-20 characters and contain only letters and numbers";
        }
        // Validate email
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        }
        // Validate password (at least 8 characters, including at least one letter and one number)
        elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
            $error = "Password must be at least 8 characters long and include letters and numbers";
        }
        // Check if passwords match
        elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        }
        else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username already exists. Please choose another one.";
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = "Email already registered. Please use a different email or login.";
                } else {
                    // Everything is valid, hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user into database
                    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $hashed_password, $fullname, $email);
                    
                    // Execute the query
                    if ($stmt->execute()) {
                        // Clear form data from session
                        unset($_SESSION['form_data']);
                        
                        // Set success message
                        $_SESSION['success_message'] = "Registration successful! You can now log in.";
                        
                        // Redirect to login page
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                }
            }
        }
    }
    
    // If there's an error, store it in session and redirect back to registration form
    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location: register.php");
        exit();
    }
} else {
    // If not a POST request, redirect to the registration form
    header("Location: register.php");
    exit();
}
?>

