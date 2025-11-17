<?php
// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die("Error: The .env file is missing. Please create one in the project root based on .env.example.");
}

// Validate required environment variables
$dotenv->required(['DB_HOST', 'DB_USER', 'DB_NAME', 'BASE_URL', 'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS']);

// Database Connection
$db = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '', $_ENV['DB_NAME']);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

// Load helper functions
require_once __DIR__ . '/functions.php';

// Nigeria Timezone
date_default_timezone_set('Africa/Lagos');

// Core Constants
define('BASE_URL', $_ENV['BASE_URL']);
define('SITE_NAME', 'House Unlimited & Land Services Nigeria');
define('COMPANY_PHONE', '+2348030000000');
define('COMPANY_EMAIL', 'info@houseunlimited.ng');

// Paystack Keys
define('PAYSTACK_PUBLIC_KEY', $_ENV['PAYSTACK_PUBLIC_KEY']);
define('PAYSTACK_SECRET_KEY', $_ENV['PAYSTACK_SECRET_KEY']);

// Email (SMTP) Settings
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_USER', $_ENV['SMTP_USER']);
define('SMTP_PASS', $_ENV['SMTP_PASS']);
define('SMTP_PORT', $_ENV['SMTP_PORT']);
define('SMTP_SECURE', $_ENV['SMTP_SECURE']);

// Paths
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');
define('PROPERTY_PATH', UPLOAD_PATH . 'properties/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');

// Session & Security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start(); // Must be called AFTER ini_set()
?>