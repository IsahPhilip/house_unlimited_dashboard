<?php
// inc/send_email.php - 100% WORKING WITH MAILTRAP ONLY

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only load PHPMailer if not already loaded
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function send_email($to, $subject, $html, $text = null) {
    $mail = new PHPMailer(true);

    try {
        // Mailtrap SMTP Settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAILTRAP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAILTRAP_USERNAME'];
        $mail->Password   = $_ENV['MAILTRAP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAILTRAP_PORT'];

        // Sender & Recipient
        $mail->setFrom($_ENV['MAILTRAP_FROM_EMAIL'], $_ENV['MAILTRAP_FROM_NAME']);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $text ?? strip_tags($html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailtrap Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>