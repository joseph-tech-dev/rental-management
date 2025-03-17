<?php
// Start the session
session_start();

// Regenerate the session ID to prevent session fixation attacks
// This creates a new session ID and deletes the old one
session_regenerate_id(true);

// Unset all session variables
$_SESSION = array();

// If a session cookie is used, destroy it by setting its expiration time to the past
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set a logout message (optional)
$logoutMessage = "You have been successfully logged out.";

// Redirect to login page with optional message
header("Location: login.php?message=" . urlencode($logoutMessage));
exit();

