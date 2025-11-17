<?php
// inc/functions.php - Helper functions

function escape($string) {
    global $db;
    return htmlspecialchars($db->real_escape_string(trim($string)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function is_agent() {
    return isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['agent', 'admin']);
}

function format_ngn($amount) {
    return '₦' . number_format((float)$amount, 0, '.', ',');
}

function format_phone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) === 11 && $phone[0] === '0') {
        $phone = '234' . substr($phone, 1);
    } elseif (strlen($phone) === 10) {
        $phone = '234' . $phone;
    }
    return '+' . $phone;
}

function generate_ref($prefix = 'HUL') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function log_activity($action) {
    global $db, $user;
    $user_id = $_SESSION['user']['id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $action, $ip, $ua);
    $stmt->execute();
}

function send_sms($phone, $message) {
    // Placeholder for Termii, Africa's Talking, or Twilio
    // return true;
}
?>