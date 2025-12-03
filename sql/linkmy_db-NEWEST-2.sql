-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Dec 03, 2025 at 11:27 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_profile_full` (IN `p_slug` VARCHAR(50))   BEGIN
  SELECT * FROM v_public_profiles WHERE slug = p_slug LIMIT 1;
  SELECT * FROM categories_v3 WHERE profile_id = (SELECT id FROM profiles WHERE slug = p_slug) ORDER BY position ASC;
  SELECT l.*, c.name AS category_name
  FROM links l
  LEFT JOIN categories_v3 c ON l.category_id = c.id
  WHERE l.profile_id = (SELECT id FROM profiles WHERE slug = p_slug) AND l.is_active = 1
  ORDER BY l.position ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_user_profiles` (IN `p_user_id` INT)   BEGIN
  SELECT * FROM v_profile_stats WHERE user_id = p_user_id ORDER BY display_order ASC, created_at ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_increment_click` (IN `p_link_id` INT, IN `p_ip` VARCHAR(45), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(100), IN `p_user_agent` TEXT, IN `p_referrer` VARCHAR(255))   BEGIN
  UPDATE links SET clicks = clicks + 1 WHERE id = p_link_id;
  INSERT INTO clicks (link_id, ip, country, city, user_agent, referrer)
  VALUES (p_link_id, p_ip, p_country, p_city, p_user_agent, p_referrer);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories_v3`
--

CREATE TABLE `categories_v3` (
  `id` int UNSIGNED NOT NULL,
  `profile_id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'bi-folder',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#667eea',
  `position` int DEFAULT '0',
  `is_expanded` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link categories/folders (v3)';

--
-- Dumping data for table `categories_v3`
--

INSERT INTO `categories_v3` (`id`, `profile_id`, `name`, `icon`, `color`, `position`, `is_expanded`, `created_at`) VALUES
(1, 1, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(3, 2, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(8, 1, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(10, 2, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(15, 1, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(17, 2, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(19, 7, 'Social Media', 'bi-folder', '#667eea', 1, 1, '2025-11-18 23:32:10'),
(21, 7, 'Project', 'bi-code-slash', '#667eea', 2, 1, '2025-11-21 01:49:18'),
(22, 7, 'Marketplace', 'bi-shop', '#667eea', 3, 1, '2025-11-21 02:09:03'),
(23, 7, 'Personal-Services', 'bi-globe', '#667eea', 4, 1, '2025-11-27 12:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `clicks`
--

CREATE TABLE `clicks` (
  `id` bigint UNSIGNED NOT NULL,
  `link_id` int UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link click analytics';

--
-- Dumping data for table `clicks`
--

INSERT INTO `clicks` (`id`, `link_id`, `ip`, `country`, `city`, `user_agent`, `referrer`, `clicked_at`) VALUES
(1, 24, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '', '2025-11-21 10:14:22'),
(2, 20, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:21:41'),
(3, 17, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:21:48'),
(4, 22, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:24:06'),
(5, 24, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:24:22'),
(6, 17, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:24:32'),
(7, 16, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 12:24:39'),
(8, 17, '157.20.32.163', 'Indonesia', 'Jakarta', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '', '2025-11-21 13:36:32'),
(9, 20, '157.20.32.163', 'Indonesia', 'Jakarta', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '', '2025-11-21 13:37:45'),
(10, 25, '157.20.32.163', 'Indonesia', 'Jakarta', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '', '2025-11-21 13:39:13'),
(11, 19, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 15:52:31'),
(12, 19, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-21 17:48:34'),
(13, 19, '182.8.97.167', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-22 06:42:57'),
(14, 24, '182.8.97.185', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-23 11:51:11'),
(15, 18, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:01'),
(16, 19, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:08'),
(17, 25, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:26'),
(18, 20, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:30'),
(19, 17, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:50'),
(20, 16, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:04:53'),
(21, 24, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:05:12'),
(22, 22, '182.8.123.112', 'Indonesia', 'Sidoarjo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-25 14:05:15'),
(23, 26, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 12:42:37'),
(24, 27, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 12:46:58'),
(25, 28, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 12:48:38'),
(26, 28, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-27 12:51:06'),
(27, 27, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '', '2025-11-27 12:51:17'),
(28, 27, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 13:02:10'),
(29, 26, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 14:45:37'),
(30, 28, '182.8.97.203', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-27 21:06:44'),
(31, 25, '114.8.228.89', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Linux; Android 13; V2110 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 Instagram 407.0.0.55.243 Android (33/13; 300dpi; 720x1509; vivo; V2110; 2110; mt6768; in_ID; 827398133; IABMV/1)', '', '2025-11-27 22:14:13'),
(32, 20, '114.8.228.89', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Linux; Android 13; V2110 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 Instagram 407.0.0.55.243 Android (33/13; 300dpi; 720x1509; vivo; V2110; 2110; mt6768; in_ID; 827398133; IABMV/1)', '', '2025-11-27 22:14:46'),
(33, 27, '2404:c0:b602:702a:a190:1b2d:2066:e7f3', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-29 04:53:44'),
(34, 17, '210.57.215.18', 'Indonesia', 'Mulyorejo', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-11-29 13:55:35'),
(64, 26, '182.8.125.74', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-12-02 02:37:44'),
(65, 24, '182.8.125.74', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-12-02 03:28:44'),
(66, 18, '182.8.125.74', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-12-02 03:55:45'),
(67, 31, '182.8.122.225', 'Indonesia', 'Surabaya', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '2025-12-03 10:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('registration','slug_change') COLLATE utf8mb4_unicode_ci DEFAULT 'registration',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email OTP verifications';

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `otp`, `type`, `ip`, `is_used`, `created_at`, `expires_at`) VALUES
(1, 'kunalmr40@gmail.com', '488129', 'registration', NULL, 0, '2025-11-12 05:31:28', '2025-11-11 23:41:28'),
(2, 'kunalmr40@gmail.com', '086821', 'registration', '::1', 0, '2025-11-13 00:05:44', '2025-11-12 18:15:44'),
(3, 'vivoy12gweh@gmail.com', '492648', 'registration', '::1', 0, '2025-11-13 00:30:33', '2025-11-12 18:40:33'),
(4, 'vivoy12gweh@gmail.com', '347508', 'registration', '::1', 0, '2025-11-13 00:48:57', '2025-11-12 18:58:57'),
(5, 'vivoy12gweh@gmail.com', '185055', 'registration', '::1', 1, '2025-11-13 00:49:14', '2025-11-12 18:59:14'),
(6, 'kunalmr40@gmail.com', '347860', 'registration', '::1', 1, '2025-11-13 00:57:47', '2025-11-12 19:07:47'),
(7, 'kanduang@gmail.com', '871525', 'registration', '172.22.0.1', 0, '2025-11-16 14:55:48', '2025-11-16 15:05:48'),
(8, 'kanduang@gmail.com', '536895', 'registration', '172.22.0.1', 0, '2025-11-16 14:55:51', '2025-11-16 15:05:51'),
(9, 'kanduang@gmail.com', '789981', 'registration', '172.22.0.1', 0, '2025-11-16 14:55:54', '2025-11-16 15:05:54'),
(10, 'kanduang@gmail.com', '588594', 'registration', '172.22.0.1', 0, '2025-11-16 14:55:58', '2025-11-16 15:05:58'),
(11, 'kanduang@gmail.com', '008442', 'registration', '172.22.0.1', 0, '2025-11-16 14:56:01', '2025-11-16 15:06:01'),
(12, 'kanduang@gmail.com', '608147', 'registration', '172.22.0.1', 0, '2025-11-16 14:56:04', '2025-11-16 15:06:04'),
(13, 'nilaanidia@gmail.com', '602361', 'registration', '172.22.0.1', 1, '2025-11-16 14:57:15', '2025-11-16 15:07:15'),
(14, 'yogazogo@gmail.com', '429554', 'registration', '172.22.0.1', 1, '2025-11-16 15:04:26', '2025-11-16 15:14:26'),
(15, 'irfannazrildebian@gmail.com', '297327', 'registration', '172.22.0.1', 1, '2025-11-16 15:05:08', '2025-11-16 15:15:08'),
(16, 'jagajagaketiga@gmail.com', '834199', 'registration', '172.22.0.1', 1, '2025-11-16 17:07:33', '2025-11-16 17:17:33'),
(17, 'hutyasooitsthyven@gmail.com', '414534', 'registration', '172.22.0.1', 0, '2025-11-17 08:25:23', '2025-11-17 08:35:23'),
(18, 'hutasooitsthyven@gmail.com', '025074', 'registration', '172.22.0.1', 0, '2025-11-17 08:25:35', '2025-11-17 08:35:35'),
(19, 'hutasoitsthyven@gmail.com', '796910', 'registration', '172.22.0.1', 1, '2025-11-17 08:25:44', '2025-11-17 08:35:44'),
(20, 'fahmiilham029@gmail.com', '968615', 'registration', '172.22.0.1', 0, '2025-11-18 03:49:46', '2025-11-18 03:59:46'),
(21, 'fahmiilham029@gmail.com', '460305', 'registration', '172.22.0.1', 1, '2025-11-18 03:49:50', '2025-11-18 03:59:50'),
(22, 'fahmiilham029@gmail.com', '163434', 'registration', '172.22.0.1', 1, '2025-11-18 03:55:53', '2025-11-18 04:05:53'),
(23, 'vivoy12gweh@gmail.com', '619176', 'registration', '172.22.0.1', 1, '2025-11-18 03:57:38', '2025-11-18 04:07:38'),
(32, 'fahmiilham029@gmail.com', '449041', 'slug_change', '172.22.0.1', 1, '2025-12-02 09:49:42', '2025-12-02 10:04:42'),
(33, 'classtriforce@gmail.com', '906725', 'registration', '2025-12-03 07:30:51', 0, '2025-12-03 07:20:51', '2017-02-22 00:01:00'),
(34, 'classtriforce@gmail.com', '660023', 'registration', '2025-12-03 07:30:54', 0, '2025-12-03 07:20:54', '2017-02-22 00:01:00'),
(35, 'classtriforce@gmail.com', '108362', 'registration', '2025-12-03 07:30:57', 0, '2025-12-03 07:20:57', '2017-02-22 00:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` int UNSIGNED NOT NULL,
  `profile_id` int UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'bi-link-45deg',
  `position` int DEFAULT '0',
  `clicks` int UNSIGNED DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `category_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile links';

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`id`, `profile_id`, `title`, `url`, `icon`, `position`, `clicks`, `is_active`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Instagram', 'https://instagram.com', 'bi-instagram', 1, 0, 1, NULL, '2025-11-10 23:29:30', NULL),
(2, 1, 'GitHub', 'https://github.com', 'bi-github', 2, 0, 1, NULL, '2025-11-10 23:29:30', NULL),
(3, 1, 'LinkedIn', 'https://linkedin.com', 'bi-linkedin', 3, 0, 1, NULL, '2025-11-10 23:29:30', NULL),
(6, 2, 'labaka saturnu saturnika', 'https://08.shinigami.asia/', 'bi-link-45deg', 1, 1, 1, NULL, '2025-11-13 01:06:28', NULL),
(10, 4, 'Youtube', 'youtube://youtube.com/dQw4w9WgXcQ?si=ZW-Lq6EZsG0aJslv', 'bi-youtube', 1, 3, 1, NULL, '2025-11-16 15:13:34', NULL),
(11, 5, 'bahlil', 'https://ibehelp.gt.tc/', 'bi-link-45deg', 1, 9, 1, NULL, '2025-11-16 15:17:53', NULL),
(12, 5, 'ss', 'https://ibehelp.gt.tc/', 'bi-link-45deg', 2, 4, 1, NULL, '2025-11-16 15:29:29', NULL),
(13, 5, 'se', 'https://ibehelp.gt.tc/', 'bi-link-45deg', 3, 4, 1, NULL, '2025-11-16 15:29:37', NULL),
(14, 5, 'ss', 'https://ibehelp.gt.tc/', 'bi-link-45deg', 4, 1, 1, NULL, '2025-11-16 15:29:45', NULL),
(16, 7, 'Instagram', 'https://www.instagram.com/fahmi.ilham06/', 'bi-instagram text-danger', 6, 3, 1, 19, '2025-11-18 05:38:56', '2025-12-03 06:54:12'),
(17, 7, 'Github', 'https://github.com/FahmiYoshikage', 'bi-github', 5, 8, 1, 19, '2025-11-18 06:27:04', NULL),
(18, 7, 'LinkMy', 'https://linkmy.iet.ovh/', 'bi-globe', 1, 3, 1, 21, '2025-11-21 01:50:07', '2025-12-02 03:55:45'),
(19, 7, 'Kas Triforce', 'https://triforce.fahmi.app/', 'bi-globe', 2, 5, 1, 21, '2025-11-21 01:50:43', NULL),
(20, 7, 'Linkedin', 'https://www.linkedin.com/in/fahmi-ilham-bagaskara-65a197305/', 'bi-linkedin text-primary', 4, 4, 1, 19, '2025-11-21 01:53:28', '2025-12-02 12:22:04'),
(22, 7, 'Twitter / X', 'https://x.com/FahmiVoldigoad', 'bi-twitter-x text-black', 7, 2, 1, 19, '2025-11-21 01:55:52', '2025-12-02 12:25:41'),
(24, 7, 'Facebook', 'https://www.facebook.com/Fahmi1lham/', 'bi-facebook text-primary', 8, 5, 1, 19, '2025-11-21 02:06:05', '2025-12-02 12:12:15'),
(25, 7, 'Shopee', 'https://shopee.co.id/', 'bi-shop-window text-warning', 3, 3, 1, 22, '2025-11-21 02:12:18', '2025-12-02 12:12:43'),
(26, 7, 'Netdata', 'https://monitor.fahmi.app', 'bi-speedometer text-success', 9, 3, 1, 23, '2025-11-27 12:42:33', '2025-12-03 07:32:33'),
(27, 7, 'Affine', 'https://affine.fahmi.app', 'bi-journal-text', 10, 4, 1, 23, '2025-11-27 12:43:31', '2025-12-02 12:15:18'),
(28, 7, 'Dozzle', 'https://dozzle.fahmi.app', 'bi-activity', 11, 3, 1, 23, '2025-11-27 12:44:56', NULL),
(31, 17, 'jawa', 'https://jawa.com', 'bi-boxes', 1, 1, 1, NULL, '2025-12-03 10:45:27', '2025-12-03 10:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens';

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `ip`, `is_used`, `created_at`, `expires_at`) VALUES
(1, 'fahmiilham029@gmail.com', '6c1cbedf46da80bfbfe15fb66a659107513f69fb9cef8f88da6ab0ead2eb1cd4', '::1', 1, '2025-11-16 08:04:03', '2025-11-16 10:04:03'),
(2, 'vivoy12gweh@gmail.com', '19742517fa3adf6f2e44770717caf24afdf3d7428de56a3919f507d9559b38ce', '172.22.0.1', 1, '2025-11-16 14:41:59', '2025-11-16 15:41:59'),
(3, 'vivoy12gweh@gmail.com', '40df38f21ef0814d5861dd24330b62dd681a10135dc8ad02c67235563b3692db', '172.22.0.1', 0, '2025-11-16 14:44:30', '2025-11-16 15:44:30'),
(4, 'yogazogo@gmail.com', 'f9db76152a96a068b13d0b2b7e8ea6e90c78737179f15cdde18d772a3d6f1635', '172.22.0.1', 1, '2025-11-16 15:32:42', '2025-11-16 16:32:42'),
(5, 'yogazogo@gmail.com', '3825de7fe7aecfb86d17b17a44e09f4c86dd70c4080a81fe3f475f79300ea49b', '172.22.0.1', 0, '2025-11-16 15:33:42', '2025-11-16 16:33:42'),
(6, 'yogazogo@gmail.com', '0b9d60c64087519f0782242dbac6938d774de56240be79042aee3918c7e30314', '172.22.0.1', 0, '2025-11-16 15:34:17', '2025-11-16 16:34:17'),
(7, 'yogazogo@gmail.com', '2521247c657781324b7435758f92eaf8b8592219bc348a6e83b51ef6877ebf55', '172.22.0.1', 0, '2025-11-16 15:34:22', '2025-11-16 16:34:22'),
(8, 'yogazogo@gmail.com', '28682be98eaa1db7db819dbae3a849946503ecb75ae7d09bc2a75077beed68d6', '172.22.0.1', 0, '2025-11-16 15:34:42', '2025-11-16 16:34:42'),
(9, 'yogazogo@gmail.com', '5303b98ff2cb41b3312aa2ef5eddd64de92dae32487fd81dcdf88c6d70de53b7', '172.22.0.1', 0, '2025-11-16 15:34:45', '2025-11-16 16:34:45'),
(10, 'yogazogo@gmail.com', '5a6f5d74544ac9b0fcfbb0a029fcc17c04496af2b47965f17f9c650ba784a058', '172.22.0.1', 0, '2025-11-16 15:34:48', '2025-11-16 16:34:48'),
(11, 'yogazogo@gmail.com', 'a4607ee086b51786a9ce008c20722ae29961703d38ebbabdffe306b535edc84d', '172.22.0.1', 0, '2025-11-16 15:34:51', '2025-11-16 16:34:51'),
(12, 'yogazogo@gmail.com', '0abd0e7a2da28997bc63606a663c4d1c669b2de53003230e5bdff7708a4ae9df', '172.22.0.1', 0, '2025-11-16 15:34:55', '2025-11-16 16:34:55'),
(13, 'yogazogo@gmail.com', '59a15bd22dd914837c218ac1c765d407c5d47136ae992fb0a49534eff47090e9', '172.22.0.1', 0, '2025-11-16 15:34:59', '2025-11-16 16:34:59'),
(14, 'yogazogo@gmail.com', 'd3c556e263aff2374725fd8aec8742eda663a1a5d43af348e73be13cfa0a3929', '172.22.0.1', 0, '2025-11-16 15:35:02', '2025-11-16 16:35:02'),
(15, 'yogazogo@gmail.com', '4936967e10e011d66aecd4e399deffd7364d87a1695ffee586d8843a081a1458', '172.22.0.1', 0, '2025-11-16 15:35:05', '2025-11-16 16:35:05'),
(16, 'yogazogo@gmail.com', 'b7cab2018eb6df735c0b34a449ecc8b148d986d70914c76eaf138c1c970dae07', '172.22.0.1', 0, '2025-11-16 15:35:09', '2025-11-16 16:35:09'),
(17, 'yogazogo@gmail.com', '814835977066a827d7beab291d203ba0dd9c1cec69913dd7bb7a604feb94f9f8', '172.22.0.1', 0, '2025-11-16 15:35:12', '2025-11-16 16:35:12'),
(18, 'yogazogo@gmail.com', '5fd4c7d5fc17f0b52619c9aa2ebc23246ff4e85f121cf2cde1fe688dd7e1b297', '172.22.0.1', 0, '2025-11-16 15:35:16', '2025-11-16 16:35:16'),
(19, 'yogazogo@gmail.com', 'a79058205edd3d2a6ba976730bdf51bcdba30bc2ed7fb44c1a4dc2341a630ea4', '172.22.0.1', 0, '2025-11-16 15:35:19', '2025-11-16 16:35:19'),
(20, 'yogazogo@gmail.com', '380ea66737bc95ac77dcc44deaad4faffd8275c0c8b624cc03a8b62018dba7cb', '172.22.0.1', 0, '2025-11-16 15:35:22', '2025-11-16 16:35:22'),
(21, 'yogazogo@gmail.com', '05d4d1231ce2613de96021c43a93cbee2a12ca06b8ee4607a0c338a9582989db', '172.22.0.1', 0, '2025-11-16 15:35:25', '2025-11-16 16:35:25'),
(22, 'yogazogo@gmail.com', '6fddb6511181137b6a4c5cd8c6e06dd0d63a1b14ac802d7557d84dce1d68516f', '172.22.0.1', 0, '2025-11-16 15:35:29', '2025-11-16 16:35:29'),
(23, 'fahmiilham029@gmail.com', '005812366756f9db7082c3691708ab9db699313ef4e9d5bdee9e42e75d749330', '172.22.0.1', 0, '2025-11-16 15:37:17', '2025-11-16 16:37:17'),
(24, 'fahmiilham029@gmail.com', 'a820162107731817503612ad47f488abba08d7695c0cd086d490574e2979ecf2', '172.22.0.1', 0, '2025-11-17 09:38:07', '2025-11-17 10:38:07'),
(25, 'fahmiilham029@gmail.com', '1d5784438aea80749fc8e1171143a225f4a16b898453139451475516675d09b1', '172.22.0.1', 1, '2025-11-18 04:07:02', '2025-11-18 05:07:02'),
(26, 'fahmiilham029@gmail.com', '26f24651d5214724b1c0001bb089b8d84d986a86649ac65794e7bedd3d030783', '172.22.0.1', 0, '2025-11-18 04:14:13', '2025-11-18 05:14:13'),
(27, 'fahmiilham029@gmail.com', '60e4e862d3283bb97256ced568b9c69bd55a8ecbafa5bd5db37a4cd42da9f1dc', '172.22.0.1', 0, '2025-11-18 04:15:03', '2025-11-18 05:15:03'),
(28, 'fahmiilham029@gmail.com', '4d5826078ac029d00486e7c81b486874747403938097309c56684812a91ecbf2', '172.22.0.1', 0, '2025-11-18 04:15:06', '2025-11-18 05:15:06'),
(29, 'fahmiilham029@gmail.com', '26d01466f60b1fe9a66cfe9d6bf1c4bb856e1cc1756addf6a66cdbea4e3ed9d3', '172.22.0.1', 0, '2025-11-28 09:34:46', '2025-11-28 10:34:46'),
(30, 'fahmiilham029@gmail.com', '2905a9fb416d32fb719150f2036c14f3e6d435332466c9370cc4ab08b795eb6a', '172.22.0.1', 0, '2025-11-28 09:34:50', '2025-11-28 10:34:50'),
(31, 'vivoy12gweh@gmail.com', '8a9842726456d505770e609331377a65acbb58402e90d5e00784f178193dd1b3', '172.22.0.1', 1, '2025-11-29 09:00:57', '2025-11-29 10:00:57'),
(32, 'fahmiilham029@gmail.com', 'ab9d672a0d5d1de82b9377a7ee7b81907eb0549f686e043fd55e05168abb8d4a', '172.22.0.1', 0, '2025-11-29 16:37:14', '2025-11-29 17:37:14'),
(33, 'fahmiilham029@gmail.com', 'cdc3e25d9614276db5ae137b9051435a0e78069400988d7beba4f91e4d8d0e8d', '172.22.0.1', 0, '2025-11-29 16:59:18', '2025-11-29 17:59:18'),
(64, 'vivoy12gweh@gmail.com', '5b6404941932588c4208cddf31a5ef53adb35eb2cc0b09c1bc2231467e44cc1c', '172.22.0.1', 0, '2025-12-02 03:40:56', '2025-12-02 04:40:56'),
(65, 'vivoy12gweh@gmail.com', '226de2355df45661162a0db03c02d3a452cbd2a777b571d5846fb2dc6e06a0a8', '172.22.0.1', 0, '2025-12-02 06:03:53', '2025-12-02 07:03:53'),
(66, 'vivoy12gweh@gmail.com', '5c2c2d53edea02c97ac9904dfcfe3bfd1ff5aaf9599fcd478c2213deae9aa705', '172.22.0.1', 1, '2025-12-02 06:51:11', '2025-12-02 07:51:11'),
(67, 'fahmiilham029@gmail.com', 'c49826a6f0a9ef42a30bc4479da2ef5548aa4e4e82662fc546ccf4b6a4ed3642', '172.22.0.1', 1, '2025-12-03 07:27:14', '2025-12-03 08:27:14'),
(68, 'vivoy12gweh@gmail.com', '9444123c7a7a24375660635ebef14fa1ba6c345ff5db819062f98492f3ccca67', '172.22.0.1', 0, '2025-12-03 10:28:38', '2025-12-03 11:28:38');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default-avatar.png',
  `is_active` tinyint(1) DEFAULT '1',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User profiles';

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `slug`, `name`, `title`, `bio`, `avatar`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin - Main Profile', 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(2, 7, 'heheheha', 'mandatori - Main Profile', 'mandatori', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(3, 8, 'nylaa', 'Nyla - Main Profile', 'Nyla', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(4, 9, 'MalingPangsit', 'MalingPangsit - Main Profile', 'MalingPangsit', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(5, 10, 'tulongg', 'sumber_air_su_dekat - Main Profile', 'sumber_air_su_dekat', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(6, 11, 'ajilahsapalagi', 'AjiSantoso - Main Profile', 'AjiSantoso', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(7, 12, 'fahmi', 'fahmi - Main Profile', 'Fahmi Ilham Bagaskara', 'I Love Internet and tech', 'user_12_1764708120.jpg', 1, 0, '2025-11-29 14:34:56', '2025-12-03 11:15:56'),
(8, 13, 'naganiga', 'naganiga - Main Profile', 'naganiga', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 0, '2025-11-29 14:34:56', NULL),
(17, 12, 'www', 'Www Profile', NULL, NULL, 'default-avatar.png', 1, 0, '2025-12-03 07:15:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profile_activity_log`
--

CREATE TABLE `profile_activity_log` (
  `log_id` int NOT NULL,
  `profile_id` int NOT NULL,
  `action_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'created, updated, cloned, deleted',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit log for profile activities';

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User sessions';

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `data`, `ip`, `user_agent`, `last_activity`, `created_at`) VALUES
('11df0ee7945fc8959d74b2aa9483be40', NULL, '', NULL, NULL, 1765037683, '2025-12-02 01:56:14'),
('252e1330ef75380fd6a3924e343cb21f', NULL, '', NULL, NULL, 1765078741, '2025-12-02 01:56:14'),
('3140bee33bf1f05f6e245a9d6cb94e93', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764473709;active_profile_id|i:7;', NULL, NULL, 1765078509, '2025-12-02 01:56:14'),
('5e3964c181fa2e3648ee16a45859d1fe', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764470098;active_profile_id|i:7;', NULL, NULL, 1765074898, '2025-12-02 01:56:14'),
('72c3e6c9daf14ef854677443e9becf98', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764577248;active_profile_id|i:7;', NULL, NULL, 1765182048, '2025-12-02 01:56:14'),
('8bc23cac8dbadd7f65f80e4c7e0a7c27', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764471889;active_profile_id|i:7;', NULL, NULL, 1765076689, '2025-12-02 01:56:14'),
('95c285dc01b011d473e897199548cdbd', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764474071;active_profile_id|i:7;', NULL, NULL, 1765078871, '2025-12-02 01:56:14'),
('c24d22b7e5e2bb91369fffe85998fe4c', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:8:\"triforce\";last_activity|i:1764434222;active_profile_id|i:16;', NULL, NULL, 1765039022, '2025-12-02 01:56:14'),
('d565b58b5f24dd750248f4d9df99c35d', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764597113;active_profile_id|i:7;', NULL, NULL, 1765201913, '2025-12-02 01:56:14'),
('d9cd0d5c8ba32db7a679fb527b48ce08', NULL, 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764460676;active_profile_id|i:7;', NULL, NULL, 1765065476, '2025-12-02 01:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `social_icons`
--

CREATE TABLE `social_icons` (
  `icon_id` int NOT NULL,
  `platform_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon_class` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `base_url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Base URL pattern for the platform'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_icons`
--

INSERT INTO `social_icons` (`icon_id`, `platform_name`, `icon_class`, `icon_color`, `base_url`) VALUES
(1, 'Instagram', 'bi-instagram', '#E4405F', 'https://instagram.com/'),
(2, 'Facebook', 'bi-facebook', '#1877F2', 'https://facebook.com/'),
(3, 'Twitter/X', 'bi-twitter-x', '#000000', 'https://twitter.com/'),
(4, 'LinkedIn', 'bi-linkedin', '#0A66C2', 'https://linkedin.com/in/'),
(5, 'GitHub', 'bi-github', '#181717', 'https://github.com/'),
(6, 'YouTube', 'bi-youtube', '#FF0000', 'https://youtube.com/'),
(7, 'TikTok', 'bi-tiktok', '#000000', 'https://tiktok.com/@'),
(8, 'WhatsApp', 'bi-whatsapp', '#25D366', 'https://wa.me/'),
(9, 'Telegram', 'bi-telegram', '#26A5E4', 'https://t.me/'),
(10, 'Discord', 'bi-discord', '#5865F2', 'https://discord.gg/'),
(11, 'Twitch', 'bi-twitch', '#9146FF', 'https://twitch.tv/'),
(12, 'Spotify', 'bi-spotify', '#1DB954', 'https://open.spotify.com/'),
(13, 'Medium', 'bi-medium', '#000000', 'https://medium.com/@'),
(14, 'Reddit', 'bi-reddit', '#FF4500', 'https://reddit.com/u/'),
(15, 'Pinterest', 'bi-pinterest', '#E60023', 'https://pinterest.com/'),
(16, 'Snapchat', 'bi-snapchat', '#FFFC00', 'https://snapchat.com/add/'),
(17, 'Email', 'bi-envelope-fill', '#EA4335', 'mailto:'),
(18, 'Website', 'bi-globe', '#667eea', 'https://'),
(19, 'Link', 'bi-link-45deg', '#6c757d', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int UNSIGNED NOT NULL,
  `profile_id` int UNSIGNED NOT NULL,
  `bg_type` enum('color','gradient','image') COLLATE utf8mb4_unicode_ci DEFAULT 'gradient',
  `bg_value` text COLLATE utf8mb4_unicode_ci,
  `button_style` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'rounded',
  `button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#667eea',
  `text_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#333333',
  `font` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Inter',
  `layout` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'centered',
  `container_style` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'wide',
  `enable_animations` tinyint(1) DEFAULT '1',
  `enable_glass_effect` tinyint(1) DEFAULT '0',
  `shadow_intensity` enum('none','light','medium','heavy') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile appearance settings';

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `profile_id`, `bg_type`, `bg_value`, `button_style`, `button_color`, `text_color`, `font`, `layout`, `container_style`, `enable_animations`, `enable_glass_effect`, `shadow_intensity`, `created_at`, `updated_at`) VALUES
(1, 1, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(2, 2, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(3, 3, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(4, 4, 'color', 'linear-gradient(135deg, #000428 0%, #004e92 100%)', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(5, 5, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(6, 6, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57'),
(7, 7, 'gradient', 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)', 'pill', '#667eea', '#000000', 'Inter', 'minimal', 'boxed', 1, 1, 'heavy', '2025-11-30 01:22:59', '2025-12-03 10:50:02'),
(8, 8, 'color', '#667eea', 'rounded', '#667eea', '#333333', 'Inter', 'centered', 'wide', 1, 0, 'medium', '2025-11-29 14:34:57', '2025-11-29 14:34:57');

-- --------------------------------------------------------

--
-- Table structure for table `theme_boxed`
--

CREATE TABLE `theme_boxed` (
  `id` int UNSIGNED NOT NULL,
  `theme_id` int UNSIGNED NOT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  `outer_bg_type` enum('color','gradient','image') COLLATE utf8mb4_unicode_ci DEFAULT 'gradient',
  `outer_bg_value` text COLLATE utf8mb4_unicode_ci,
  `container_bg_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `container_max_width` int DEFAULT '480',
  `container_radius` int DEFAULT '30',
  `container_shadow` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Boxed layout settings';

--
-- Dumping data for table `theme_boxed`
--

INSERT INTO `theme_boxed` (`id`, `theme_id`, `enabled`, `outer_bg_type`, `outer_bg_value`, `container_bg_color`, `container_max_width`, `container_radius`, `container_shadow`) VALUES
(1, 7, 1, 'gradient', 'linear-gradient(135deg, #fe9a9a 0%, #2650f7 100%)', '#ffffff', 480, 30, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0' COMMENT 'Verified badge (influencer/founder)',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_verified`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@linkmy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 1, '2025-11-10 23:29:30', NULL),
(7, 'mandatori', 'kunalmr40@gmail.com', '$2y$10$2KHGnWqmaE09y6rBkTGeYOgBhor2mHbzJHcxL24HCim3CmSrdqpaO', 0, 1, '2025-11-13 01:05:43', NULL),
(8, 'Nyla', 'nilaanidia@gmail.com', '$2y$10$a3IhdC22rwMIkhr4rofyAeXW3J.2pSoQTDbcPAld9zUYdxrPmymTi', 0, 1, '2025-11-16 14:58:17', NULL),
(9, 'MalingPangsit', 'irfannazrildebian@gmail.com', '$2y$10$xjejtjDgzhGgOwu5sO3es.WJq9Tge6I5XpKTWqrdoWLPd88uXStpq', 0, 1, '2025-11-16 15:06:11', NULL),
(10, 'sumber_air_su_dekat', 'yogazogo@gmail.com', '$2y$10$xMfhL6qCj7FyqBczv.gdOutuEpb/CQJfqHTy4RfnhfnRud79ZNlEq', 0, 1, '2025-11-16 15:16:41', NULL),
(11, 'AjiSantoso', 'jagajagaketiga@gmail.com', '$2y$10$eM6Fi50ax/DPgbCqkJOWKOKccKk.Lmq0xsFDeuh/Sp1Z.wrUinM1S', 0, 1, '2025-11-16 17:08:12', NULL),
(12, 'fahmi', 'fahmiilham029@gmail.com', '$2y$10$SLf4cpy7KkQg73bp8ckKwO0qplasBAFJbXmm83n51KAd600TC4zum', 1, 1, '2025-11-18 03:56:37', '2025-12-03 07:31:53'),
(13, 'naganiga', 'vivoy12gweh@gmail.com', '$2y$10$u3CZIuBarPZmlGP82dmMeeIpISJfmcTUZX9rOMk2GhdBFopnIZu8W', 0, 1, '2025-11-18 03:59:27', '2025-12-02 06:51:39');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_profile_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_profile_stats` (
`id` int unsigned
,`user_id` int unsigned
,`slug` varchar(50)
,`name` varchar(100)
,`title` varchar(100)
,`bio` text
,`avatar` varchar(255)
,`is_active` tinyint(1)
,`username` varchar(50)
,`email` varchar(100)
,`is_verified` tinyint(1)
,`link_count` bigint
,`total_clicks` decimal(32,0)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_profiles`
-- (See below for the actual view)
--
CREATE TABLE `v_public_profiles` (
`id` int unsigned
,`slug` varchar(50)
,`name` varchar(100)
,`title` varchar(100)
,`bio` text
,`avatar` varchar(255)
,`username` varchar(50)
,`is_verified` tinyint(1)
,`bg_type` enum('color','gradient','image')
,`bg_value` text
,`button_style` varchar(20)
,`button_color` varchar(20)
,`text_color` varchar(20)
,`font` varchar(50)
,`layout` varchar(20)
,`container_style` varchar(20)
,`enable_animations` tinyint(1)
,`enable_glass_effect` tinyint(1)
,`shadow_intensity` enum('none','light','medium','heavy')
,`boxed_enabled` tinyint(1)
,`outer_bg_type` enum('color','gradient','image')
,`outer_bg_value` text
,`container_bg_color` varchar(20)
,`container_max_width` int
,`container_radius` int
,`container_shadow` tinyint(1)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories_v3`
--
ALTER TABLE `categories_v3`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profile_id` (`profile_id`),
  ADD KEY `idx_position` (`position`);

--
-- Indexes for table `clicks`
--
ALTER TABLE `clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_link_id` (`link_id`),
  ADD KEY `idx_clicked_at` (`clicked_at`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profile_id` (`profile_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profile_id` (`profile_id`),
  ADD KEY `idx_profile_id` (`profile_id`);

--
-- Indexes for table `theme_boxed`
--
ALTER TABLE `theme_boxed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `theme_id` (`theme_id`),
  ADD KEY `idx_theme_id` (`theme_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories_v3`
--
ALTER TABLE `categories_v3`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `clicks`
--
ALTER TABLE `clicks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `theme_boxed`
--
ALTER TABLE `theme_boxed`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

-- --------------------------------------------------------

--
-- Structure for view `v_profile_stats`
--
DROP TABLE IF EXISTS `v_profile_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_profile_stats`  AS SELECT `p`.`id` AS `id`, `p`.`user_id` AS `user_id`, `p`.`slug` AS `slug`, `p`.`name` AS `name`, `p`.`title` AS `title`, `p`.`bio` AS `bio`, `p`.`avatar` AS `avatar`, `p`.`is_active` AS `is_active`, `u`.`username` AS `username`, `u`.`email` AS `email`, `u`.`is_verified` AS `is_verified`, count(distinct `l`.`id`) AS `link_count`, coalesce(sum(`l`.`clicks`),0) AS `total_clicks`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at` FROM ((`profiles` `p` join `users` `u` on((`p`.`user_id` = `u`.`id`))) left join `links` `l` on(((`l`.`profile_id` = `p`.`id`) and (`l`.`is_active` = 1)))) GROUP BY `p`.`id`, `p`.`user_id`, `p`.`slug`, `p`.`name`, `p`.`title`, `p`.`bio`, `p`.`avatar`, `p`.`is_active`, `u`.`username`, `u`.`email`, `u`.`is_verified`, `p`.`created_at`, `p`.`updated_at` ;

-- --------------------------------------------------------

--
-- Structure for view `v_public_profiles`
--
DROP TABLE IF EXISTS `v_public_profiles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_public_profiles`  AS SELECT `p`.`id` AS `id`, `p`.`slug` AS `slug`, `p`.`name` AS `name`, `p`.`title` AS `title`, `p`.`bio` AS `bio`, `p`.`avatar` AS `avatar`, `u`.`username` AS `username`, `u`.`is_verified` AS `is_verified`, `t`.`bg_type` AS `bg_type`, `t`.`bg_value` AS `bg_value`, `t`.`button_style` AS `button_style`, `t`.`button_color` AS `button_color`, `t`.`text_color` AS `text_color`, `t`.`font` AS `font`, `t`.`layout` AS `layout`, `t`.`container_style` AS `container_style`, `t`.`enable_animations` AS `enable_animations`, `t`.`enable_glass_effect` AS `enable_glass_effect`, `t`.`shadow_intensity` AS `shadow_intensity`, `tb`.`enabled` AS `boxed_enabled`, `tb`.`outer_bg_type` AS `outer_bg_type`, `tb`.`outer_bg_value` AS `outer_bg_value`, `tb`.`container_bg_color` AS `container_bg_color`, `tb`.`container_max_width` AS `container_max_width`, `tb`.`container_radius` AS `container_radius`, `tb`.`container_shadow` AS `container_shadow` FROM (((`profiles` `p` join `users` `u` on((`p`.`user_id` = `u`.`id`))) left join `themes` `t` on((`t`.`profile_id` = `p`.`id`))) left join `theme_boxed` `tb` on((`tb`.`theme_id` = `t`.`id`))) WHERE (`p`.`is_active` = 1) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories_v3`
--
ALTER TABLE `categories_v3`
  ADD CONSTRAINT `categories_v3_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clicks`
--
ALTER TABLE `clicks`
  ADD CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `fk_links_category_v3` FOREIGN KEY (`category_id`) REFERENCES `categories_v3` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `themes`
--
ALTER TABLE `themes`
  ADD CONSTRAINT `themes_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `theme_boxed`
--
ALTER TABLE `theme_boxed`
  ADD CONSTRAINT `theme_boxed_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
