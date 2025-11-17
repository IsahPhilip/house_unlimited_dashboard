<?php
// inc/auth.php - Secure session guard (CLEAN REDIRECT TO login.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// List of public pages that don't require login
$public_pages = ['login.php', 'authenticate.php', 'forgot.php', 'register.php'];

$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $public_pages)) {
    // Allow access to login, magic link, etc.
    return;
}

// If not logged in → redirect to login.php (clean URL)
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Optional: Session timeout (30 days inactivity)
$expiry = 30 * 24 * 60 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $expiry)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();

$user = $_SESSION['user'];
?>