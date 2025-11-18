<?php
// inc/config.php - FIXED & CLEAN (No more SMTP required)
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    die("Error: .env file missing or invalid.");
}

// ONLY require the essentials (SMTP removed!)
$dotenv->required([
    'DB_HOST',
    'DB_USER',
    'DB_NAME',
    'BASE_URL',
    'MAILTRAP_API_KEY',
    'MAILTRAP_INBOX_ID',
    'MAILTRAP_SENDER_EMAIL'
]);

$dotenv->required(['PAYSTACK_PUBLIC_KEY', 'PAYSTACK_SECRET_KEY']);

// Database Connection
$db = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '', $_ENV['DB_NAME']);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

// Load helpers
require_once __DIR__ . '/functions.php';

// Timezone
date_default_timezone_set('Africa/Lagos');

// Core Constants
define('BASE_URL', rtrim($_ENV['BASE_URL'], '/'));
define('SITE_NAME', 'House Unlimited & Land Services Nigeria');
define('COMPANY_PHONE', '+2348030000000');
define('COMPANY_EMAIL', 'info@houseunlimited.ng');

// Paystack (optional)
define('PAYSTACK_PUBLIC_KEY', $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '');
define('PAYSTACK_SECRET_KEY', $_ENV['PAYSTACK_SECRET_KEY'] ?? '');

// Mailtrap API Key
define('MAILTRAP_API_KEY', $_ENV['MAILTRAP_API_KEY']);

// Paths
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');
define('PROPERTY_PATH', UPLOAD_PATH . 'properties/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');

// Security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 only on HTTPS
ini_set('session.use_strict_mode', 1);

session_start();
?>