<?php
// inc/send_email.php - Now using Mailtrap

use Mailtrap\Config;
use Mailtrap\Email;
use Mailtrap\MailtrapClient;
use Symfony\Component\Mime\Address;

function send_email($to, $subject, $html, $text = null) {
    $apiKey = $_ENV['MAILTRAP_API_KEY'] ?? null;
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'] ?? null;
    $senderEmail = $_ENV['MAILTRAP_SENDER_EMAIL'] ?? null;
    $senderName = 'House Unlimited Nigeria'; // Or get from .env if you want

    if (!$apiKey || !$inboxId || !$senderEmail) {
        error_log("Mailtrap API key, Inbox ID, or Sender Email is missing in .env file.");
        return false;
    }

    try {
        $config = new Config($apiKey);
        $mailtrap = new MailtrapClient($config);

        $email = (new Email())
            ->from(new Address($senderEmail, $senderName))
            ->to(new Address($to))
            ->subject($subject)
            ->html($html);
        
        if ($text) {
            $email->text($text);
        }

        $response = $mailtrap->sending()->emails()->send($email);

        // Check if the email was sent successfully
        if ($response->isSuccess()) {
            return true;
        } else {
            // Log the error from Mailtrap's response
            $errorBody = $response->getBody();
            error_log('Mailtrap Error: ' . json_encode($errorBody));
            return false;
        }

    } catch (Exception $e) {
        error_log('Mailtrap Exception: ' . $e->getMessage());
        return false;
    }
}
