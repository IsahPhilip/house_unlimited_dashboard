<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Only require what we actually need
$dotenv->required([
    'DB_HOST', 
    'DB_USER', 
    'DB_NAME', 
    'BASE_URL'
]);

// Database
$db = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '', $_ENV['DB_NAME']);
if ($db->connect_error) die("DB Error: " . $db->connect_error);
$db->set_charset('utf8mb4');

require_once __DIR__ . '/functions.php';

date_default_timezone_set('Africa/Lagos');

define('BASE_URL', rtrim($_ENV['BASE_URL'], '/'));
define('SITE_NAME', 'House Unlimited & Land Services Nigeria');

define('PROPERTY_PATH', __DIR__ . '/../assets/uploads/properties/');

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
?>