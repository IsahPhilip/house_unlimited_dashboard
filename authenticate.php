<?php
ob_start();
require 'inc/config.php';
require 'inc/send_email.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/codes/house_unlimited_dashboard');
}
// =======================
// 1. MAGIC LINK REQUEST
// =======================
if (isset($_POST['magic_login'])) {
    $email = trim($_POST['email']);

    $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $token = bin2hex(random_bytes(20));
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $stmt2 = $db->prepare("UPDATE users SET magic_token = ?, token_expires = ? WHERE id = ?");
        $stmt2->bind_param('ssi', $token, $expires, $user['id']);
        $stmt2->execute();

        $magic_link = BASE_URL . "/authenticate.php?token=" . $token;

        // === SEND EMAIL (using PHPMailer) ===
        $subject = 'Your Magic Login Link to ' . SITE_NAME;
        $body = "
            <div style='font-family: sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #333;'>Welcome back, {$user['name']}!</h2>
                <p>Click the button below to securely log in to your dashboard. This link is only valid for the next 15 minutes.</p>
                <p style='text-align: center;'>
                    <a href='$magic_link' style='display: inline-block; padding: 15px 30px; background-color: #1e40af; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Login to Your Dashboard</a>
                </p>
                <p>If you can't click the button, you can copy and paste this link into your browser:</p>
                <p><code>$magic_link</code></p>
                <hr>
                <p style='font-size: 12px; color: #888;'>If you did not request this, you can safely ignore this email.</p>
            </div>
        ";
        
        if (send_email($email, $subject, $body)) {
            header('Location: login.php?success=1&email=' . urlencode($email));
            exit;
        } else {
            error_log("Magic link email failed to send to {$email}");
            header('Location: login.php?error=' . urlencode('We could not send the login link. Please try again or contact support.') . '&email=' . urlencode($email));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('User not found or inactive'));
        exit;
    }
}

// =======================
// 2. MAGIC LINK VERIFY (GET request)
// =======================
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    $now = date('Y-m-d H:i:s');

    $stmt = $db->prepare("SELECT * FROM users WHERE magic_token = ? AND token_expires > ?");
    $stmt->bind_param('ss', $token, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // SUCCESS — LOG USER IN
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'photo' => $user['photo'] ?? 'default.png'
        ];

        // Clear the token
        $db->query("UPDATE users SET magic_token = NULL, token_expires = NULL WHERE id = " . $user['id']);

        log_activity("User logged in via magic link");

        // FINAL REDIRECT TO DASHBOARD
        header('Location: dashboard/index.php');
        exit;
    } else {
        header('Location: login.php?error=' . urlencode('Invalid or expired magic link'));
        exit;
    }
}

// =======================
// 3. PASSWORD LOGIN
// =======================
if (isset($_POST['password_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'] ?? '')) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'photo' => $user['photo'] ?? 'default.png'
        ];
        log_activity("User logged in with password");
        header('Location: dashboard/index.php');
        exit;
    } else {
        header('Location: login.php?error=' . urlencode('Wrong email or password'));
        exit;
    }
}
// If no valid request
header('Location: login.php');
exit;
?>