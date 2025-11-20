-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 12:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `house_unlimited_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Admin logged in', '105.112.34.567', NULL, '2025-11-17 19:00:00'),
(2, 1, 'Admin approved property ID #3', '105.112.34.567', NULL, '2025-11-17 19:15:00'),
(3, 1, 'Admin updated system settings', '105.112.34.567', NULL, '2025-11-17 19:59:33'),
(4, 34, 'User logged in via magic link', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 22:28:05'),
(5, 34, 'User logged out', '::1', NULL, '2025-11-17 22:49:56'),
(6, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-18 04:53:27'),
(7, 1, 'User logged out', '::1', NULL, '2025-11-18 04:56:39'),
(8, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-18 19:59:11'),
(9, 1, 'User logged out', '::1', NULL, '2025-11-18 20:50:03'),
(10, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-18 20:50:14'),
(11, 1, 'User logged out', '::1', NULL, '2025-11-18 21:02:22'),
(12, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-18 21:35:24'),
(13, 1, 'User logged out', '::1', NULL, '2025-11-18 21:50:44'),
(14, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-19 18:40:53'),
(15, 1, 'User logged out', '::1', NULL, '2025-11-19 19:05:42'),
(16, 1, 'User logged in with password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-19 20:04:55'),
(17, 4, 'GOD MODE LOGIN → Victor Okafor (agent)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-19 20:14:46'),
(18, 4, 'User logged out', '::1', NULL, '2025-11-20 01:03:46'),
(19, 5, 'GOD MODE LOGIN → Grace Adeyemi (agent)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 01:20:23'),
(20, 5, 'User logged out', '::1', NULL, '2025-11-20 02:12:25'),
(21, 1, 'GOD MODE LOGIN → Isah Philip (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 02:16:36'),
(22, 1, 'User logged out', '::1', NULL, '2025-11-20 08:59:21'),
(23, 1, 'GOD MODE LOGIN → Isah Philip (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 09:02:04'),
(24, 1, 'Admin performed ban on user ID #22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 09:30:37'),
(25, 1, 'Admin performed unban on user ID #22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 09:33:30'),
(26, 1, 'Admin performed make_agent on user ID #22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 09:33:46'),
(27, 1, 'Admin permanently deleted user ID #22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 22:20:17'),
(28, 1, 'User logged out', '::1', NULL, '2025-11-20 22:26:41'),
(29, 5, 'GOD MODE LOGIN → Grace Adeyemi (agent)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-20 22:29:55');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `viewing_date` date NOT NULL,
  `viewing_time` time NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `whatsapp_sent` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `property_id`, `agent_id`, `viewing_date`, `viewing_time`, `message`, `status`, `whatsapp_sent`, `created_at`) VALUES
(1, 4, 1, 2, '2025-11-20', '14:00:00', 'Coming with my wife and lawyer', 'confirmed', 1, '2025-11-17 20:52:02'),
(2, 5, 3, 3, '2025-11-22', '11:00:00', 'Cash buyer – very serious', 'pending', 0, '2025-11-17 20:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `category` enum('receipt','contract','deed','survey','c-of-o','invoice','other') DEFAULT 'other',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `property_id`, `title`, `file_path`, `category`, `created_at`) VALUES
(1, 4, 1, 'Payment Receipt – ₦50,000,000 Booking Fee', 'receipt_hul_8f9g3h2j.pdf', 'receipt', '2025-11-10 00:00:00'),
(2, 4, 1, 'Certificate of Occupancy – Ikoyi Property', 'c-of-o_ikoyi_bourdillon.pdf', 'c-of-o', '2025-11-10 00:00:00'),
(3, 5, 3, 'Payment Receipt – ₦50m Booking', 'receipt_hul_k2m9p5q8.pdf', 'receipt', '2025-11-12 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `message`, `property_id`, `is_read`, `created_at`) VALUES
(1, 4, 2, 'Good morning Chioma, is the Ikoyi duplex still available?', 1, 1, '2025-11-15 09:23:00'),
(2, 2, 4, 'Yes it is! When would you like to inspect?', 1, 1, '2025-11-15 09:25:00'),
(3, 5, 3, 'Ahmed, the Maitama mansion – can we do ₦1.1b cash?', 3, 0, '2025-11-17 14:10:00'),
(4, 7, 2, 'Chioma please I need a 3 bedroom in Lekki under ₦200m', 2, 0, '2025-11-17 18:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `property_id`, `amount`, `status`, `payment_date`) VALUES
(1, 1, 8, 99999999.99, 'successful', '2025-11-20 20:39:40');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `description` longtext NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `location` varchar(150) NOT NULL,
  `state` varchar(100) NOT NULL,
  `type` enum('sale','rent') NOT NULL,
  `bedrooms` tinyint(4) DEFAULT NULL,
  `bathrooms` tinyint(4) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `toilets` tinyint(4) DEFAULT NULL,
  `land_size` decimal(10,2) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `status` enum('pending','active','rejected','sold','rented') DEFAULT 'pending',
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `agent_id`, `title`, `slug`, `description`, `price`, `location`, `state`, `type`, `bedrooms`, `bathrooms`, `size`, `toilets`, `land_size`, `features`, `status`, `views`, `featured`, `created_at`, `updated_at`) VALUES
(7, 2, '5 Bedroom Fully Detached Duplex with BQ – Ikoyi', '5-bed-duplex-ikoyi', 'Brand new smart home with swimming pool, cinema, and rooftop terrace. C of O.', 850000000.00, 'Bourdillon Road, Ikoyi', 'Lagos', 'sale', 5, 6, NULL, 7, 800.00, '[\"Swimming Pool\",\"Cinema\",\"BQ\",\"Smart Home\",\"C of O\",\"24hr Power\"]', 'active', 0, 1, '2025-03-10 00:00:00', '2025-11-17 20:51:59'),
(8, 2, '4 Bedroom Terrace Duplex – Banana Island', '4-bed-terrace-banana-island', 'Tastefully finished with marble floors, imported fittings. Ready title.', 650000000.00, 'Banana Island Estate', 'Lagos', 'sale', 4, 5, NULL, 6, 400.00, '[\"Marble Finish\",\"BQ\",\"Gym\",\"C of O\"]', 'active', 0, 1, '2025-04-20 00:00:00', '2025-11-17 20:51:59'),
(9, 3, '6 Bedroom Ambassadorial Mansion – Maitama', '6-bed-mansion-maitama', 'Diplomatic zone. Massive compound, guest chalet, pool, and helipad.', 1200000000.00, 'Maitama District', 'Abuja', 'sale', 6, 8, NULL, 10, 3000.00, '[\"Helipad\",\"Guest Chalet\",\"Pool\",\"C of O\",\"Embassy Zone\"]', 'active', 0, 1, '2025-06-15 00:00:00', '2025-11-17 20:51:59'),
(10, 6, 'Waterfront 4 Bedroom Detached House – Old GRA', 'waterfront-old-gra-ph', 'Direct waterfront with jetty. Perfect for oil executives.', 480000000.00, 'Old GRA', 'Rivers', 'sale', 4, 5, NULL, 6, 1200.00, '[\"Waterfront\",\"Jetty\",\"BQ\",\"C of O\"]', 'active', 0, 1, '2025-07-01 00:00:00', '2025-11-17 20:51:59'),
(11, 2, '500sqm Bare Land – Epe (Ilara Gardens)', 'land-epe-ilara', 'Dry land in gated estate. Buy & build instantly.', 15000000.00, 'Ilara Gardens, Epe', 'Lagos', 'sale', NULL, NULL, NULL, NULL, 500.00, '[\"Gated Estate\",\"Perimeter Fencing\",\"Good Title\"]', 'active', 0, 0, '2025-10-01 00:00:00', '2025-11-17 20:51:59'),
(12, 3, '3 Bedroom Luxury Apartment – Wuye', '3-bed-apartment-wuye', 'Brand new, serviced, with gym and pool.', 180000000.00, 'Wuye District', 'Abuja', 'sale', 3, 3, NULL, 4, NULL, '[\"Serviced\",\"Gym\",\"Pool\",\"24hr Power\"]', 'active', 0, 0, '2025-11-10 00:00:00', '2025-11-17 20:51:59'),
(14, 5, 'ffffffffffff', '', '<p>ffffffffffffffffffffffffffff</p>', 50000000.00, 'ggggggggggggggg', '', 'sale', 1, 1, '555', NULL, NULL, NULL, '', 0, 0, '2025-11-20 23:51:14', '2025-11-20 23:51:14');

--
-- Triggers `properties`
--
DELIMITER $$
CREATE TRIGGER `after_property_insert` AFTER INSERT ON `properties` FOR EACH ROW BEGIN
    INSERT INTO property_progress (property_id, phase, percentage, updated_by)
    VALUES
        (NEW.id, 'Foundation', 0, NEW.agent_id),
        (NEW.id, 'Structure', 0, NEW.agent_id),
        (NEW.id, 'Roofing', 0, NEW.agent_id),
        (NEW.id, 'Plumbing & Electrical', 0, NEW.agent_id),
        (NEW.id, 'Finishing', 0, NEW.agent_id),
        (NEW.id, 'Handover', 0, NEW.agent_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_featured`, `uploaded_at`) VALUES
(1, 1, 'ikoyi-duplex-1.jpg', 1, '2025-11-17 20:52:01'),
(2, 1, 'ikoyi-duplex-2.jpg', 0, '2025-11-17 20:52:01'),
(3, 1, 'ikoyi-duplex-3.jpg', 0, '2025-11-17 20:52:01'),
(4, 2, 'banana-terrace-1.jpg', 1, '2025-11-17 20:52:01'),
(5, 2, 'banana-terrace-2.jpg', 0, '2025-11-17 20:52:01'),
(6, 3, 'maitama-mansion-1.jpg', 1, '2025-11-17 20:52:01'),
(7, 3, 'maitama-mansion-2.jpg', 0, '2025-11-17 20:52:01'),
(8, 4, 'ph-waterfront-1.jpg', 1, '2025-11-17 20:52:01'),
(9, 5, 'epe-land.jpg', 1, '2025-11-17 20:52:01'),
(10, 6, 'wuye-apartment-1.jpg', 1, '2025-11-17 20:52:01'),
(11, 14, 'prop_691f9b62551e8.png', 0, '2025-11-20 23:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `property_progress`
--

CREATE TABLE `property_progress` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `phase` enum('Foundation','Structure','Roofing','Plumbing & Electrical','Finishing','Handover') NOT NULL,
  `percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 CHECK (`percentage` <= 100),
  `description` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_progress`
--

INSERT INTO `property_progress` (`id`, `property_id`, `phase`, `percentage`, `description`, `updated_by`, `updated_at`, `created_at`) VALUES
(1, 14, 'Foundation', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14'),
(2, 14, 'Structure', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14'),
(3, 14, 'Roofing', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14'),
(4, 14, 'Plumbing & Electrical', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14'),
(5, 14, 'Finishing', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14'),
(6, 14, 'Handover', 0, NULL, 5, '2025-11-20 23:51:14', '2025-11-20 23:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referee_id` int(11) NOT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `bonus_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_settings`
--

CREATE TABLE `referral_settings` (
  `id` int(11) NOT NULL,
  `requirement_type` varchar(255) NOT NULL,
  `requirement_value` int(11) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'House Unlimited & Land Services Nigeria', '2025-11-17 20:36:59'),
(2, 'company_phone', '+2348030000000', '2025-11-17 20:36:59'),
(3, 'company_email', 'info@houseunlimited.ng', '2025-11-17 20:36:59'),
(4, 'booking_fee', '50000000', '2025-11-17 20:36:59'),
(5, 'commission_rate', '5', '2025-11-17 20:36:59'),
(6, 'property_approval_required', '1', '2025-11-17 20:36:59'),
(7, 'maintenance_mode', '0', '2025-11-17 20:36:59'),
(8, 'default_currency', 'NGN', '2025-11-17 20:36:59'),
(9, 'site_logo', 'logo.png', '2025-11-17 20:36:59');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_ref` varchar(100) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `gateway` enum('paystack','flutterwave') DEFAULT 'paystack',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `property_id`, `amount`, `payment_ref`, `status`, `gateway`, `metadata`, `created_at`) VALUES
(1, 4, 1, 50000000.00, 'HUL-8F9G3H2J', 'success', 'paystack', NULL, '2025-11-10 14:22:00'),
(2, 5, 3, 50000000.00, 'HUL-K2M9P5Q8', 'success', 'paystack', NULL, '2025-11-12 09:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `wallet_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('client','agent','admin') DEFAULT 'client',
  `status` enum('active','banned') DEFAULT 'active',
  `photo` varchar(100) DEFAULT 'default.png',
  `bio` text DEFAULT NULL,
  `magic_token` varchar(100) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `referrer_id`, `username`, `name`, `email`, `phone`, `wallet_balance`, `password`, `role`, `status`, `photo`, `bio`, `magic_token`, `token_expires`, `created_at`, `updated_at`) VALUES
(1, NULL, '', 'Isah Philip', 'admin@houseunlimited.ng', '+2348030000000', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 'isah.jpg', 'Founder & CEO – House Unlimited Nigeria', NULL, NULL, '2024-01-01 10:00:00', '2025-11-18 05:51:51'),
(2, NULL, '', 'Chioma Okeke', 'chioma@houseunlimited.ng', '+2348123456789', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'active', 'chioma.jpg', 'Lekki & Ikoyi Specialist | ₦5B+ in sales', NULL, NULL, '2024-06-15 00:00:00', '2025-11-18 05:51:51'),
(3, NULL, '', 'Ahmed Yusuf', 'ahmed.yusuf@houseunlimited.ng', '+2349065432109', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'active', 'ahmed.jpg', 'Abuja Luxury Expert | Maitama & Asokoro', NULL, NULL, '2024-07-20 00:00:00', '2025-11-18 05:51:51'),
(4, NULL, '', 'Victor Okafor', 'victor@houseunlimited.ng', '+2349081122334', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'active', 'victor.jpg', 'Port Harcourt & Oil Money Properties', NULL, NULL, '2024-11-01 00:00:00', '2025-11-18 05:51:51'),
(5, NULL, '', 'Grace Adeyemi', 'grace@houseunlimited.ng', '+2347012345678', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'active', 'grace.jpg', 'Banana Island & Victoria Island Queen', NULL, NULL, '2025-01-10 00:00:00', '2025-11-18 05:51:51'),
(6, NULL, '', 'Tunde Lawal', 'tunde@houseunlimited.ng', '+2348155558899', 0.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'active', 'tunde.jpg', 'Epe & Ibeju-Lekki Land Expert', NULL, NULL, '2025-02-05 00:00:00', '2025-11-18 05:51:51'),
(7, NULL, '', 'Dr. Fatima Bello', 'fatima.bello@gmail.com', '+2348135557799', 0.00, NULL, 'client', 'active', 'fatima.jpg', 'Cash buyer | Looking for ₦1B+ mansion in Abuja', NULL, NULL, '2025-09-05 00:00:00', '2025-11-18 05:51:51'),
(8, NULL, '', 'Engr. Emeka Nwosu', 'emeka.nwosu@shell.com', '+2348147778899', 0.00, NULL, 'client', 'active', 'emeka.jpg', 'Oil exec | Wants waterfront in PH', NULL, NULL, '2025-10-01 00:00:00', '2025-11-18 05:51:51'),
(9, NULL, '', 'Mrs. Sandra Eze', 'sandra.eze@gtbank.com', '+2347056789123', 0.00, NULL, 'client', 'active', 'sandra.jpg', 'Banker | Investor in Lekki Phase 1', NULL, NULL, '2025-10-20 00:00:00', '2025-11-18 05:51:51'),
(10, NULL, '', 'Chief Tolu Adebayo', 'tolu.adebayo@yahoo.com', '+2347012345678', 0.00, NULL, 'client', 'active', 'tolu.jpg', 'Politician | Needs 10-bedroom in Ikoyi', NULL, NULL, '2025-08-10 00:00:00', '2025-11-18 05:51:51'),
(11, NULL, '', 'Mr. David Okocha', 'david.okocha@nnpc.gov.ng', '+2349098765432', 0.00, NULL, 'client', 'active', 'david.jpg', 'NNPC Director | Abuja mansion', NULL, NULL, '2025-11-01 00:00:00', '2025-11-18 05:51:51'),
(12, NULL, '', 'Aisha Mohammed', 'aisha.m@gmail.com', '+2348101234567', 0.00, NULL, 'client', 'active', 'default.png', 'First-time buyer | Budget ₦80M', NULL, NULL, '2025-11-10 00:00:00', '2025-11-18 05:51:51'),
(13, NULL, '', 'Kemi Adewale', 'kemi.adewale@hotmail.com', '+2347087654321', 0.00, NULL, 'client', 'active', 'default.png', 'Looking for 3-bed in Ajah', NULL, NULL, '2025-11-12 00:00:00', '2025-11-18 05:51:51'),
(14, NULL, '', 'Chinedu Okonkwo', 'chinedu.ok@gmail.com', '+2348165432109', 0.00, NULL, 'client', 'active', 'default.png', 'Land in Epe | ₦15M budget', NULL, NULL, '2025-11-13 00:00:00', '2025-11-18 05:51:51'),
(15, NULL, '', 'Bola Yusuf', 'bola.yusuf@outlook.com', '+2349034567890', 0.00, NULL, 'client', 'active', 'default.png', 'Renting in VI | ₦20M/year', NULL, NULL, '2025-11-14 00:00:00', '2025-11-18 05:51:51'),
(16, NULL, '', 'Funmi Alabi', 'funmi.alabi@gmail.com', '+2348112233445', 0.00, NULL, 'client', 'active', 'default.png', 'Student | Studio in Yaba', NULL, NULL, '2025-11-15 00:00:00', '2025-11-18 05:51:51'),
(17, NULL, '', 'Ibrahim Sani', 'ibrahim.sani@yahoo.com', '+2348141122334', 0.00, NULL, 'client', 'active', 'default.png', 'Duplex in Gwarinpa, Abuja', NULL, NULL, '2025-11-16 00:00:00', '2025-11-18 05:51:51'),
(18, NULL, '', 'Peace John', 'peace.john@gmail.com', '+2348078899001', 0.00, NULL, 'client', 'active', 'default.png', 'Family home in Ikeja GRA', NULL, NULL, '2025-11-17 00:00:00', '2025-11-18 05:51:51'),
(19, NULL, '', 'Samuel Obi', 'samuel.obi@accessbank.com', '+2348129988776', 0.00, NULL, 'client', 'active', 'default.png', '4-bed terrace in Lekki', NULL, NULL, '2025-11-17 00:00:00', '2025-11-18 05:51:51'),
(20, NULL, '', 'Zainab Usman', 'zainab.usman@gmail.com', '+2348098877665', 0.00, NULL, 'client', 'active', 'default.png', 'Apartment in Surulere', NULL, NULL, '2025-11-18 00:00:00', '2025-11-18 05:51:51'),
(21, NULL, '', 'Isah Philip', 'philipisah30@gmail.com', '+2349013643677', 0.00, NULL, 'client', 'active', 'default.png', NULL, NULL, NULL, '2025-11-18 22:02:58', '2025-11-18 22:02:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `idx_date` (`viewing_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_convo` (`sender_id`,`recipient_id`),
  ADD KEY `idx_time` (`created_at`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_recipient_read` (`recipient_id`,`is_read`),
  ADD KEY `idx_conversation` (`sender_id`,`recipient_id`,`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_location` (`location`);
ALTER TABLE `properties` ADD FULLTEXT KEY `search_idx` (`title`,`description`,`location`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_featured` (`is_featured`);

--
-- Indexes for table `property_progress`
--
ALTER TABLE `property_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_phase_per_property` (`property_id`,`phase`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_property` (`property_id`),
  ADD KEY `idx_phase` (`phase`),
  ADD KEY `idx_updated` (`updated_at`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referee_id` (`referee_id`);

--
-- Indexes for table `referral_settings`
--
ALTER TABLE `referral_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_ref` (`payment_ref`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_ref` (`payment_ref`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `magic_token` (`magic_token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `property_progress`
--
ALTER TABLE `property_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_settings`
--
ALTER TABLE `referral_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_progress`
--
ALTER TABLE `property_progress`
  ADD CONSTRAINT `property_progress_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_progress_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
