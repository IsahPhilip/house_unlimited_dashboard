<?php
// authenticate.php 
ob_start();

require 'inc/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

        // === SEND EMAIL (PHPMailer) ===
        $mail = new PHPMailer(true);
        try {
            // Server settings from config.php
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(COMPANY_EMAIL, SITE_NAME);
            $mail->addAddress($email, $user['name']);

            $mail->isHTML(true);
            $mail->Subject = 'Your Magic Login Link';
            $mail->Body    = "
                <h2>Welcome back, {$user['name']}!</h2>
                <p>Click below to log in instantly:</p>
                <p><a href='$magic_link' style='padding:15px 30px; background:#1e40af; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Login to Dashboard</a></p>
                <p>Or copy: <code>$magic_link</code></p>
                <p><small>Link expires in 15 minutes.</small></p>
            ";
            $mail->AltBody = "Login here: $magic_link";

            $mail->send();
            header('Location: login.php?success=1&email=' . urlencode($email));
            exit;
        } catch (Exception $e) {
            // Production-ready error handling
            // Log the detailed error for the admin to see, but show a generic message to the user.
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            header('Location: login.php?error=' . urlencode('Could not send login link. Please contact support.') . '&email=' . urlencode($email));
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