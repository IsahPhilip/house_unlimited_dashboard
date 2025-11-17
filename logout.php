<?php
// logout.php - Secure Logout
require 'inc/config.php';

// Start session to access it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log the logout action (optional but recommended)
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $db->query("INSERT INTO activity_log (user_id, action, ip_address) VALUES ($user_id, 'User logged out', '{$_SERVER['REMOTE_ADDR']}')");
}

// Completely destroy the session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session data
session_destroy();

// Redirect to login page (clean URL)
header('Location: login.php');
exit;
?>