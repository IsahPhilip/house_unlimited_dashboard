<?php
// inc/send_email.php - 100% WORKING WITH MAILTRAP + AUTO .env LOADING

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer via Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file (if not already loaded)
if (!class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../.env')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

function send_email($to, $subject, $html_body, $text_body = null) {
    $mail = new PHPMailer(true);

    // Required Mailtrap settings from .env
    $host     = $_ENV['MAILTRAP_HOST'] ?? '';
    $port     = $_ENV['MAILTRAP_PORT'] ?? '2525';
    $username = $_ENV['MAILTRAP_USERNAME'] ?? '';
    $password = $_ENV['MAILTRAP_PASSWORD'] ?? '';
    $from_email = $_ENV['MAILTRAP_FROM_EMAIL'] ?? 'no-reply@houseunlimited.ng';
    $from_name  = $_ENV['MAILTRAP_FROM_NAME'] ?? 'House Unlimited';

    if (empty($host) || empty($username) || empty($password)) {
        error_log("MAILTRAP ERROR: Missing SMTP credentials in .env");
        return false;
    }

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;

        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body ?? strip_tags($html_body);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailtrap failed to send email to {$to}: {$mail->ErrorInfo}");
        return false;
    }
}