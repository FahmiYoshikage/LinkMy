-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 02:56 PM
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
-- Database: `linkmy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appearance`
--
-- Creation: Nov 10, 2025 at 11:28 PM
--

CREATE TABLE `appearance` (
  `appearance_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_title` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic_filename` varchar(255) DEFAULT 'default-avatar.png',
  `bg_image_filename` varchar(255) DEFAULT NULL,
  `theme_name` varchar(20) DEFAULT 'light',
  `button_style` varchar(20) DEFAULT 'rounded',
  `font_family` varchar(50) DEFAULT 'Inter',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appearance`
--

INSERT INTO `appearance` (`appearance_id`, `user_id`, `profile_title`, `bio`, `profile_pic_filename`, `bg_image_filename`, `theme_name`, `button_style`, `font_family`, `updated_at`) VALUES
(1, 1, 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-10 23:29:30'),
(5, 5, 'FAHMI ILHAM BAGASKARA', 'This is my sosial media and my project', 'user_5_1762882308.png', NULL, 'gradient', 'pill', 'Inter', '2025-11-13 07:03:12'),
(6, 6, 'nagatoro', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'sharp', 'Inter', '2025-11-13 00:57:02'),
(7, 7, 'mandatori', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-13 01:05:43');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--
-- Creation: Nov 11, 2025 at 06:46 PM
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `otp_code`, `created_at`, `expires_at`, `is_used`, `ip_address`) VALUES
(14, 'kunalmr40@gmail.com', '488129', '2025-11-12 05:31:28', '2025-11-11 23:41:28', 0, NULL),
(20, 'kunalmr40@gmail.com', '086821', '2025-11-13 00:05:44', '2025-11-12 18:15:44', 0, '::1'),
(23, 'vivoy12gweh@gmail.com', '492648', '2025-11-13 00:30:33', '2025-11-12 18:40:33', 0, '::1'),
(24, 'vivoy12gweh@gmail.com', '347508', '2025-11-13 00:48:57', '2025-11-12 18:58:57', 0, '::1'),
(25, 'vivoy12gweh@gmail.com', '185055', '2025-11-13 00:49:14', '2025-11-12 18:59:14', 1, '::1'),
(26, 'kunalmr40@gmail.com', '347860', '2025-11-13 00:57:47', '2025-11-12 19:07:47', 1, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--
-- Creation: Nov 10, 2025 at 11:28 PM
--

CREATE TABLE `links` (
  `link_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `order_index` int(11) DEFAULT 0,
  `icon_class` varchar(50) DEFAULT 'bi-link-45deg',
  `click_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`link_id`, `user_id`, `title`, `url`, `order_index`, `icon_class`, `click_count`, `is_active`, `created_at`) VALUES
(1, 1, 'Instagram', 'https://instagram.com', 1, 'bi-instagram', 0, 1, '2025-11-10 23:29:30'),
(2, 1, 'GitHub', 'https://github.com', 2, 'bi-github', 0, 1, '2025-11-10 23:29:30'),
(3, 1, 'LinkedIn', 'https://linkedin.com', 3, 'bi-linkedin', 0, 1, '2025-11-10 23:29:30'),
(4, 5, 'Instagram Saya', 'https://www.instagram.com/fahmi.ilham06/', 1, 'bi-instagram', 1, 1, '2025-11-11 17:31:26'),
(5, 6, 'Linkedin', 'https://www.instagram.com/with.io', 1, 'bi-link-45deg', 2, 1, '2025-11-13 00:56:41'),
(6, 7, 'labaka saturnu saturnika', 'https://08.shinigami.asia/', 1, 'bi-link-45deg', 1, 1, '2025-11-13 01:06:28'),
(7, 5, 'Linkedin', 'https://www.linkedin.com/in/fahmi-ilham-bagaskara-65a197305/', 2, 'bi-linkedin', 0, 1, '2025-11-13 05:48:55'),
(8, 5, 'Facebook', 'https://www.facebook.com/Fahmi1lham/', 3, 'bi-facebook', 0, 1, '2025-11-13 07:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--
-- Creation: Nov 13, 2025 at 01:29 AM
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Nov 11, 2025 at 05:49 PM
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `page_slug` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `page_slug`, `email`, `email_verified`, `email_verified_at`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@linkmy.com', 0, NULL, '2025-11-10 23:29:30'),
(5, 'fahmi', '$2y$10$A0wbPQKufjDZwS0R6vzfLefSj5aawcw07d7yZzHQG82QlXskPKnCm', 'KagayakuVerse', 'fahmiilham029@gmail.com', 0, NULL, '2025-11-11 16:24:38'),
(6, 'nagatoro', '$2y$10$jhf4tHg74zrLqpAC1xeHzetb4t5fSNPC9B0H/Ga9acC2Anf4EWZeG', 'nagatoro', 'vivoy12gweh@gmail.com', 1, '2025-11-13 00:56:09', '2025-11-13 00:56:09'),
(7, 'mandatori', '$2y$10$2KHGnWqmaE09y6rBkTGeYOgBhor2mHbzJHcxL24HCim3CmSrdqpaO', 'heheheha', 'kunalmr40@gmail.com', 1, '2025-11-13 01:05:43', '2025-11-13 01:05:43');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_page_data`
-- (See below for the actual view)
--
CREATE TABLE `v_public_page_data` (
`user_id` int(11)
,`username` varchar(50)
,`page_slug` varchar(50)
,`profile_title` varchar(100)
,`bio` text
,`profile_pic_filename` varchar(255)
,`bg_image_filename` varchar(255)
,`theme_name` varchar(20)
,`button_style` varchar(20)
,`font_family` varchar(50)
,`link_id` int(11)
,`link_title` varchar(100)
,`link_url` varchar(500)
,`icon_class` varchar(50)
,`click_count` int(11)
,`order_index` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `v_public_page_data`
--
DROP TABLE IF EXISTS `v_public_page_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_public_page_data`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`page_slug` AS `page_slug`, `a`.`profile_title` AS `profile_title`, `a`.`bio` AS `bio`, `a`.`profile_pic_filename` AS `profile_pic_filename`, `a`.`bg_image_filename` AS `bg_image_filename`, `a`.`theme_name` AS `theme_name`, `a`.`button_style` AS `button_style`, `a`.`font_family` AS `font_family`, `l`.`link_id` AS `link_id`, `l`.`title` AS `link_title`, `l`.`url` AS `link_url`, `l`.`icon_class` AS `icon_class`, `l`.`click_count` AS `click_count`, `l`.`order_index` AS `order_index` FROM ((`users` `u` left join `appearance` `a` on(`u`.`user_id` = `a`.`user_id`)) left join `links` `l` on(`u`.`user_id` = `l`.`user_id` and `l`.`is_active` = 1)) ORDER BY `u`.`user_id` ASC, `l`.`order_index` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appearance`
--
ALTER TABLE `appearance`
  ADD PRIMARY KEY (`appearance_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp_code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`reset_token`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_email_token` (`email`,`reset_token`),
  ADD KEY `idx_token_used` (`reset_token`,`is_used`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `page_slug` (`page_slug`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appearance`
--
ALTER TABLE `appearance`
  MODIFY `appearance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appearance`
--
ALTER TABLE `appearance`
  ADD CONSTRAINT `appearance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
