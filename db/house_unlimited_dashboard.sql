-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 11:46 PM
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
(4, 34, 'User logged in via magic link', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-17 22:28:05');

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
  `from_user` int(11) NOT NULL,
  `to_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `from_user`, `to_user`, `message`, `property_id`, `read_status`, `created_at`) VALUES
(1, 4, 2, 'Good morning Chioma, is the Ikoyi duplex still available?', 1, 1, '2025-11-15 09:23:00'),
(2, 2, 4, 'Yes it is! When would you like to inspect?', 1, 1, '2025-11-15 09:25:00'),
(3, 5, 3, 'Ahmed, the Maitama mansion – can we do ₦1.1b cash?', 3, 0, '2025-11-17 14:10:00'),
(4, 7, 2, 'Chioma please I need a 3 bedroom in Lekki under ₦200m', 2, 0, '2025-11-17 18:05:00');

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

INSERT INTO `properties` (`id`, `agent_id`, `title`, `slug`, `description`, `price`, `location`, `state`, `type`, `bedrooms`, `bathrooms`, `toilets`, `land_size`, `features`, `status`, `views`, `featured`, `created_at`, `updated_at`) VALUES
(7, 2, '5 Bedroom Fully Detached Duplex with BQ – Ikoyi', '5-bed-duplex-ikoyi', 'Brand new smart home with swimming pool, cinema, and rooftop terrace. C of O.', 850000000.00, 'Bourdillon Road, Ikoyi', 'Lagos', 'sale', 5, 6, 7, 800.00, '[\"Swimming Pool\",\"Cinema\",\"BQ\",\"Smart Home\",\"C of O\",\"24hr Power\"]', 'active', 0, 1, '2025-03-10 00:00:00', '2025-11-17 20:51:59'),
(8, 2, '4 Bedroom Terrace Duplex – Banana Island', '4-bed-terrace-banana-island', 'Tastefully finished with marble floors, imported fittings. Ready title.', 650000000.00, 'Banana Island Estate', 'Lagos', 'sale', 4, 5, 6, 400.00, '[\"Marble Finish\",\"BQ\",\"Gym\",\"C of O\"]', 'active', 0, 1, '2025-04-20 00:00:00', '2025-11-17 20:51:59'),
(9, 3, '6 Bedroom Ambassadorial Mansion – Maitama', '6-bed-mansion-maitama', 'Diplomatic zone. Massive compound, guest chalet, pool, and helipad.', 1200000000.00, 'Maitama District', 'Abuja', 'sale', 6, 8, 10, 3000.00, '[\"Helipad\",\"Guest Chalet\",\"Pool\",\"C of O\",\"Embassy Zone\"]', 'active', 0, 1, '2025-06-15 00:00:00', '2025-11-17 20:51:59'),
(10, 6, 'Waterfront 4 Bedroom Detached House – Old GRA', 'waterfront-old-gra-ph', 'Direct waterfront with jetty. Perfect for oil executives.', 480000000.00, 'Old GRA', 'Rivers', 'sale', 4, 5, 6, 1200.00, '[\"Waterfront\",\"Jetty\",\"BQ\",\"C of O\"]', 'active', 0, 1, '2025-07-01 00:00:00', '2025-11-17 20:51:59'),
(11, 2, '500sqm Bare Land – Epe (Ilara Gardens)', 'land-epe-ilara', 'Dry land in gated estate. Buy & build instantly.', 15000000.00, 'Ilara Gardens, Epe', 'Lagos', 'sale', NULL, NULL, NULL, 500.00, '[\"Gated Estate\",\"Perimeter Fencing\",\"Good Title\"]', 'active', 0, 0, '2025-10-01 00:00:00', '2025-11-17 20:51:59'),
(12, 3, '3 Bedroom Luxury Apartment – Wuye', '3-bed-apartment-wuye', 'Brand new, serviced, with gym and pool.', 180000000.00, 'Wuye District', 'Abuja', 'sale', 3, 3, 4, NULL, '[\"Serviced\",\"Gym\",\"Pool\",\"24hr Power\"]', 'active', 0, 0, '2025-11-10 00:00:00', '2025-11-17 20:51:59');

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
(10, 6, 'wuye-apartment-1.jpg', 1, '2025-11-17 20:52:01');

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
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `photo`, `bio`, `magic_token`, `token_expires`, `created_at`, `updated_at`) VALUES
(1, 'Skyler Crypt', 'admin@houseunlimited.ng', NULL, '$2y$10$gL3g.zB9J.p7vR8/uK3qUeJ5iG9yO7wX6sC4lR2tE1oF0bI8nS9mC', 'admin', 'active', 'default.png', NULL, NULL, NULL, '2025-11-17 20:36:58', '2025-11-17 22:36:46'),
(10, 'Julia Abege', 'julia@houseunlimited.ng', '+2348030000000', NULL, 'admin', 'active', 'julia.jpg', 'Founder & CEO – Building the future of Nigerian real estate', NULL, NULL, '2024-01-01 10:00:00', '2025-11-17 20:41:15'),
(11, 'Chioma Okeke', 'chioma@houseunlimited.ng', '+2348123456789', NULL, 'agent', 'active', 'chioma.jpg', 'Top agent in Lekki Phase 1 | 50+ properties sold', NULL, NULL, '2024-06-15 00:00:00', '2025-11-17 20:41:15'),
(12, 'Ahmed Yusuf', 'ahmed.yusuf@gmail.com', '+2349065432109', NULL, 'agent', 'active', 'ahmed.jpg', 'Abuja specialist | Luxury & Diplomatic Quarters', NULL, NULL, '2024-07-20 00:00:00', '2025-11-17 20:41:15'),
(13, 'Tolu Adebayo', 'tolu.adebayo@yahoo.com', '+2347012345678', NULL, 'client', 'active', 'tolu.jpg', 'Looking for 5-bedroom duplex in Ikoyi', NULL, NULL, '2025-08-10 00:00:00', '2025-11-17 20:41:15'),
(14, 'Fatima Bello', 'fatima.bello@gmail.com', '+2348135557799', NULL, 'client', 'active', 'fatima.jpg', 'First-time buyer | Budget ₦120m', NULL, NULL, '2025-09-05 00:00:00', '2025-11-17 20:41:15'),
(15, 'Victor Okafor', 'victorokafor@outlook.com', '+2349081122334', NULL, 'agent', 'active', 'victor.jpg', 'Port Harcourt expert | Oil & Gas executives', NULL, NULL, '2024-11-01 00:00:00', '2025-11-17 20:41:15'),
(16, 'Sandra Eze', 'sandra.eze@gmail.com', '+2347056789123', NULL, 'client', 'active', 'sandra.jpg', 'Investor | Cash buyer in Lagos', NULL, NULL, '2025-10-20 00:00:00', '2025-11-17 20:41:15'),
(17, 'Emeka Nwosu', 'emeka.nwosu@hotmail.com', '+2348147778899', NULL, 'client', 'active', 'emeka.jpg', 'Looking for land in Epe', NULL, NULL, '2025-11-01 00:00:00', '2025-11-17 20:41:15'),
(34, 'Isah Philip', 'philipisah30@gmail.com', '+2349013643677', NULL, 'client', 'active', 'default.png', NULL, NULL, NULL, '2025-11-17 22:38:06', '2025-11-17 23:28:04');

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
  ADD KEY `to_user` (`to_user`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_convo` (`from_user`,`to_user`),
  ADD KEY `idx_time` (`created_at`),
  ADD KEY `idx_read` (`read_status`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
