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

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();

function setup_database($db) {
    // Check for referrals table
    $result = $db->query("SHOW TABLES LIKE 'referrals'");
    if ($result->num_rows == 0) {
        $db->multi_query("
            CREATE TABLE `referrals` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `referrer_id` int(11) NOT NULL,
              `referee_id` int(11) NOT NULL,
              `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
              `bonus_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `referrer_id` (`referrer_id`),
              KEY `referee_id` (`referee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        while ($db->next_result()) {;} // flush multi_query
    }

    // Check for referral_settings table
    $result = $db->query("SHOW TABLES LIKE 'referral_settings'");
    if ($result->num_rows == 0) {
        $db->multi_query("
            CREATE TABLE `referral_settings` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `requirement_type` varchar(255) NOT NULL,
              `requirement_value` int(11) NOT NULL,
              `bonus_amount` decimal(10,2) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        while ($db->next_result()) {;} // flush multi_query
    }

    // Check for referrer_id column in users table
    $result = $db->query("SHOW COLUMNS FROM `users` LIKE 'referrer_id'");
    if ($result->num_rows == 0) {
        $db->query("ALTER TABLE `users` ADD `referrer_id` INT(11) NULL DEFAULT NULL AFTER `id`");
    }

    // Check for wallet_balance column in users table
    $result = $db->query("SHOW COLUMNS FROM `users` LIKE 'wallet_balance'");
    if ($result->num_rows == 0) {
        $db->query("ALTER TABLE `users` ADD `wallet_balance` DECIMAL(10, 2) NOT NULL DEFAULT '0.00' AFTER `phone`");
    }
}

setup_database($db);
?>