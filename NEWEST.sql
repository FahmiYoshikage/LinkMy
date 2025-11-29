-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Nov 29, 2025 at 03:02 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.27

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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'bi-folder',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#667eea',
  `is_expanded` tinyint(1) DEFAULT '1',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `otp_code` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verification_type` enum('email','slug_change') COLLATE utf8mb4_general_ci DEFAULT 'email' COMMENT 'Type of verification: email registration or slug change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `otp_code`, `created_at`, `expires_at`, `is_used`, `ip_address`, `verification_type`) VALUES
(14, 'kunalmr40@gmail.com', '488129', '2025-11-12 05:31:28', '2025-11-11 23:41:28', 0, NULL, 'email'),
(20, 'kunalmr40@gmail.com', '086821', '2025-11-13 00:05:44', '2025-11-12 18:15:44', 0, '::1', 'email'),
(23, 'vivoy12gweh@gmail.com', '492648', '2025-11-13 00:30:33', '2025-11-12 18:40:33', 0, '::1', 'email'),
(24, 'vivoy12gweh@gmail.com', '347508', '2025-11-13 00:48:57', '2025-11-12 18:58:57', 0, '::1', 'email'),
(25, 'vivoy12gweh@gmail.com', '185055', '2025-11-13 00:49:14', '2025-11-12 18:59:14', 1, '::1', 'email'),
(26, 'kunalmr40@gmail.com', '347860', '2025-11-13 00:57:47', '2025-11-12 19:07:47', 1, '::1', 'email'),
(27, 'kanduang@gmail.com', '871525', '2025-11-16 14:55:48', '2025-11-16 15:05:48', 0, '172.22.0.1', 'email'),
(28, 'kanduang@gmail.com', '536895', '2025-11-16 14:55:51', '2025-11-16 15:05:51', 0, '172.22.0.1', 'email'),
(29, 'kanduang@gmail.com', '789981', '2025-11-16 14:55:54', '2025-11-16 15:05:54', 0, '172.22.0.1', 'email'),
(30, 'kanduang@gmail.com', '588594', '2025-11-16 14:55:58', '2025-11-16 15:05:58', 0, '172.22.0.1', 'email'),
(31, 'kanduang@gmail.com', '008442', '2025-11-16 14:56:01', '2025-11-16 15:06:01', 0, '172.22.0.1', 'email'),
(32, 'kanduang@gmail.com', '608147', '2025-11-16 14:56:04', '2025-11-16 15:06:04', 0, '172.22.0.1', 'email'),
(33, 'nilaanidia@gmail.com', '602361', '2025-11-16 14:57:15', '2025-11-16 15:07:15', 1, '172.22.0.1', 'email'),
(34, 'yogazogo@gmail.com', '429554', '2025-11-16 15:04:26', '2025-11-16 15:14:26', 1, '172.22.0.1', 'email'),
(35, 'irfannazrildebian@gmail.com', '297327', '2025-11-16 15:05:08', '2025-11-16 15:15:08', 1, '172.22.0.1', 'email'),
(36, 'jagajagaketiga@gmail.com', '834199', '2025-11-16 17:07:33', '2025-11-16 17:17:33', 1, '172.22.0.1', 'email'),
(37, 'hutyasooitsthyven@gmail.com', '414534', '2025-11-17 08:25:23', '2025-11-17 08:35:23', 0, '172.22.0.1', 'email'),
(38, 'hutasooitsthyven@gmail.com', '025074', '2025-11-17 08:25:35', '2025-11-17 08:35:35', 0, '172.22.0.1', 'email'),
(39, 'hutasoitsthyven@gmail.com', '796910', '2025-11-17 08:25:44', '2025-11-17 08:35:44', 1, '172.22.0.1', 'email'),
(40, 'fahmiilham029@gmail.com', '968615', '2025-11-18 03:49:46', '2025-11-18 03:59:46', 0, '172.22.0.1', 'email'),
(41, 'fahmiilham029@gmail.com', '460305', '2025-11-18 03:49:50', '2025-11-18 03:59:50', 1, '172.22.0.1', 'email'),
(42, 'fahmiilham029@gmail.com', '163434', '2025-11-18 03:55:53', '2025-11-18 04:05:53', 1, '172.22.0.1', 'email'),
(43, 'vivoy12gweh@gmail.com', '619176', '2025-11-18 03:57:38', '2025-11-18 04:07:38', 1, '172.22.0.1', 'email');

-- --------------------------------------------------------

--
-- Table structure for table `gradient_presets`
--

CREATE TABLE `gradient_presets` (
  `preset_id` int NOT NULL,
  `preset_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `gradient_css` text COLLATE utf8mb4_general_ci NOT NULL,
  `preview_color_1` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `preview_color_2` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gradient_presets`
--

INSERT INTO `gradient_presets` (`preset_id`, `preset_name`, `gradient_css`, `preview_color_1`, `preview_color_2`, `is_default`) VALUES
(1, 'Purple Dream', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea', '#764ba2', 1),
(2, 'Ocean Blue', 'linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)', '#00c6ff', '#0072ff', 1),
(3, 'Sunset Orange', 'linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)', '#ff6a00', '#ee0979', 1),
(4, 'Fresh Mint', 'linear-gradient(135deg, #00b09b 0%, #96c93d 100%)', '#00b09b', '#96c93d', 1),
(5, 'Pink Lemonade', 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)', '#ff9a9e', '#fecfef', 1),
(6, 'Royal Purple', 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)', '#8e2de2', '#4a00e0', 1),
(7, 'Fire Blaze', 'linear-gradient(135deg, #f85032 0%, #e73827 100%)', '#f85032', '#e73827', 1),
(8, 'Emerald Water', 'linear-gradient(135deg, #348f50 0%, #56b4d3 100%)', '#348f50', '#56b4d3', 1),
(9, 'Candy Shop', 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', '#f093fb', '#f5576c', 1),
(10, 'Cool Blues', 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', '#4facfe', '#00f2fe', 1),
(11, 'Warm Flame', 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)', '#ff9a56', '#ff6a88', 1),
(12, 'Deep Sea', 'linear-gradient(135deg, #2e3192 0%, #1bffff 100%)', '#2e3192', '#1bffff', 1),
(13, 'Nebula Night', 'linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%)', '#3a1c71', '#ffaf7b', 1),
(14, 'Aurora Borealis', 'linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%)', '#00c9ff', '#92fe9d', 1),
(15, 'Crimson Tide', 'linear-gradient(135deg, #c31432 0%, #240b36 100%)', '#c31432', '#240b36', 1),
(16, 'Golden Hour', 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 50%, #e17055 100%)', '#ffeaa7', '#e17055', 1),
(17, 'Midnight Blue', 'linear-gradient(135deg, #000428 0%, #004e92 100%)', '#000428', '#004e92', 1),
(18, 'Rose Petal', 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)', '#ffecd2', '#fcb69f', 1),
(19, 'Electric Violet', 'linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)', '#4776e6', '#8e54e9', 1),
(20, 'Jungle Green', 'linear-gradient(135deg, #134e5e 0%, #71b280 100%)', '#134e5e', '#71b280', 1),
(21, 'Peach Cream', 'linear-gradient(135deg, #ff9a8b 0%, #ff6a88 50%, #ff99ac 100%)', '#ff9a8b', '#ff99ac', 1),
(22, 'Arctic Ice', 'linear-gradient(135deg, #667db6 0%, #0082c8 50%, #0082c8 100%, #667db6 100%)', '#667db6', '#0082c8', 1),
(23, 'Sunset Glow', 'linear-gradient(135deg, #ffa751 0%, #ffe259 100%)', '#ffa751', '#ffe259', 1),
(24, 'Purple Haze', 'linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)', '#c471f5', '#fa71cd', 1);

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `link_id` int NOT NULL,
  `user_id` int NOT NULL,
  `profile_id` int DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `order_index` int DEFAULT '0',
  `icon_class` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'bi-link-45deg',
  `click_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL COMMENT 'Link category for grouping'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`link_id`, `user_id`, `profile_id`, `title`, `url`, `order_index`, `icon_class`, `click_count`, `is_active`, `created_at`, `category_id`) VALUES
(1, 1, 1, 'Instagram', 'https://instagram.com', 1, 'bi-instagram', 0, 1, '2025-11-10 23:29:30', NULL),
(2, 1, 1, 'GitHub', 'https://github.com', 2, 'bi-github', 0, 1, '2025-11-10 23:29:30', NULL),
(3, 1, 1, 'LinkedIn', 'https://linkedin.com', 3, 'bi-linkedin', 0, 1, '2025-11-10 23:29:30', NULL),
(6, 7, 2, 'labaka saturnu saturnika', 'https://08.shinigami.asia/', 1, 'bi-link-45deg', 1, 1, '2025-11-13 01:06:28', NULL),
(10, 9, 4, 'Youtube', 'youtube://youtube.com/dQw4w9WgXcQ?si=ZW-Lq6EZsG0aJslv', 1, 'bi-youtube', 3, 1, '2025-11-16 15:13:34', NULL),
(11, 10, 5, 'bahlil', 'https://ibehelp.gt.tc/', 1, 'bi-link-45deg', 9, 1, '2025-11-16 15:17:53', NULL),
(12, 10, 5, 'ss', 'https://ibehelp.gt.tc/', 2, 'bi-link-45deg', 4, 1, '2025-11-16 15:29:29', NULL),
(13, 10, 5, 'se', 'https://ibehelp.gt.tc/', 3, 'bi-link-45deg', 4, 1, '2025-11-16 15:29:37', NULL),
(14, 10, 5, 'ss', 'https://ibehelp.gt.tc/', 4, 'bi-link-45deg', 1, 1, '2025-11-16 15:29:45', NULL),
(16, 12, 7, 'Instagram', 'https://www.instagram.com/fahmi.ilham06/', 6, 'bi-instagram', 3, 1, '2025-11-18 05:38:56', 19),
(17, 12, 7, 'Github', 'https://github.com/FahmiYoshikage', 5, 'bi-github', 8, 1, '2025-11-18 06:27:04', 19),
(18, 12, 7, 'LinkMy', 'https://linkmy.iet.ovh/', 1, 'bi-globe', 2, 1, '2025-11-21 01:50:07', 21),
(19, 12, 7, 'Kas Triforce', 'https://triforce.fahmi.app/', 2, 'bi-globe', 5, 1, '2025-11-21 01:50:43', 21),
(20, 12, 7, 'Linkedin', 'https://www.linkedin.com/in/fahmi-ilham-bagaskara-65a197305/', 4, 'bi-linkedin', 4, 1, '2025-11-21 01:53:28', 19),
(22, 12, 7, 'Twitter / X', 'https://x.com/FahmiVoldigoad', 7, 'bi-twitter-x', 2, 1, '2025-11-21 01:55:52', 19),
(24, 12, 7, 'Facebook', 'https://www.facebook.com/Fahmi1lham/', 8, 'bi-facebook', 4, 1, '2025-11-21 02:06:05', 19),
(25, 12, 7, 'Shopee', 'https://shopee.co.id/', 3, 'bi-shop-window', 3, 1, '2025-11-21 02:12:18', 22),
(26, 12, 7, 'Netdata', 'https://monitor.fahmi.app', 9, 'bi-speedometer', 2, 1, '2025-11-27 12:42:33', 23),
(27, 12, 7, 'Affine', 'https://affine.fahmi.app', 10, 'bi-journal-text', 4, 1, '2025-11-27 12:43:31', 23),
(28, 12, 7, 'Dozzle', 'https://dozzle.fahmi.app', 11, 'bi-activity', 3, 1, '2025-11-27 12:44:56', 23);

-- --------------------------------------------------------

--
-- Table structure for table `link_analytics`
--

CREATE TABLE `link_analytics` (
  `analytics_id` int NOT NULL,
  `link_id` int NOT NULL,
  `clicked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `referrer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link_analytics`
--

INSERT INTO `link_analytics` (`analytics_id`, `link_id`, `clicked_at`, `referrer`, `user_agent`, `ip_address`, `country`, `city`) VALUES
(1, 24, '2025-11-21 10:14:22', '', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(2, 20, '2025-11-21 12:21:41', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(3, 17, '2025-11-21 12:21:48', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(4, 22, '2025-11-21 12:24:06', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(5, 24, '2025-11-21 12:24:22', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(6, 17, '2025-11-21 12:24:32', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(7, 16, '2025-11-21 12:24:39', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(8, 17, '2025-11-21 13:36:32', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '157.20.32.163', 'Indonesia', 'Jakarta'),
(9, 20, '2025-11-21 13:37:45', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '157.20.32.163', 'Indonesia', 'Jakarta'),
(10, 25, '2025-11-21 13:39:13', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', '157.20.32.163', 'Indonesia', 'Jakarta'),
(11, 19, '2025-11-21 15:52:31', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(12, 19, '2025-11-21 17:48:34', '', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(13, 19, '2025-11-22 06:42:57', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.167', 'Indonesia', 'Surabaya'),
(14, 24, '2025-11-23 11:51:11', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.185', 'Indonesia', 'Surabaya'),
(15, 18, '2025-11-25 14:04:01', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(16, 19, '2025-11-25 14:04:08', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(17, 25, '2025-11-25 14:04:26', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(18, 20, '2025-11-25 14:04:30', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(19, 17, '2025-11-25 14:04:50', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(20, 16, '2025-11-25 14:04:53', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(21, 24, '2025-11-25 14:05:12', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(22, 22, '2025-11-25 14:05:15', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.123.112', 'Indonesia', 'Sidoarjo'),
(23, 26, '2025-11-27 12:42:37', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(24, 27, '2025-11-27 12:46:58', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(25, 28, '2025-11-27 12:48:38', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(26, 28, '2025-11-27 12:51:06', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.97.203', 'Indonesia', 'Surabaya'),
(27, 27, '2025-11-27 12:51:17', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '182.8.97.203', 'Indonesia', 'Surabaya'),
(28, 27, '2025-11-27 13:02:10', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(29, 26, '2025-11-27 14:45:37', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(30, 28, '2025-11-27 21:06:44', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '182.8.97.203', 'Indonesia', 'Surabaya'),
(31, 25, '2025-11-27 22:14:13', '', 'Mozilla/5.0 (Linux; Android 13; V2110 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 Instagram 407.0.0.55.243 Android (33/13; 300dpi; 720x1509; vivo; V2110; 2110; mt6768; in_ID; 827398133; IABMV/1)', '114.8.228.89', 'Indonesia', 'Surabaya'),
(32, 20, '2025-11-27 22:14:46', '', 'Mozilla/5.0 (Linux; Android 13; V2110 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 Instagram 407.0.0.55.243 Android (33/13; 300dpi; 720x1509; vivo; V2110; 2110; mt6768; in_ID; 827398133; IABMV/1)', '114.8.228.89', 'Indonesia', 'Surabaya'),
(33, 27, '2025-11-29 04:53:44', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2404:c0:b602:702a:a190:1b2d:2066:e7f3', 'Indonesia', 'Surabaya'),
(34, 17, '2025-11-29 13:55:35', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '210.57.215.18', 'Indonesia', 'Mulyorejo');

-- --------------------------------------------------------

--
-- Table structure for table `link_categories`
--

CREATE TABLE `link_categories` (
  `category_id` int NOT NULL,
  `user_id` int NOT NULL,
  `profile_id` int DEFAULT NULL,
  `category_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `category_icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'bi-folder',
  `category_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '#667eea',
  `display_order` int DEFAULT '0',
  `is_expanded` tinyint(1) DEFAULT '1' COMMENT 'Default expanded state for category',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link_categories`
--

INSERT INTO `link_categories` (`category_id`, `user_id`, `profile_id`, `category_name`, `category_icon`, `category_color`, `display_order`, `is_expanded`, `created_at`) VALUES
(1, 1, 1, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(3, 7, 2, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(8, 1, 1, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(10, 7, 2, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(15, 1, 1, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(17, 7, 2, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(19, 12, 7, 'Social Media', 'bi-folder', '#667eea', 1, 1, '2025-11-18 23:32:10'),
(21, 12, 7, 'Project', 'bi-code-slash', '#667eea', 2, 1, '2025-11-21 01:49:18'),
(22, 12, 7, 'Marketplace', 'bi-shop', '#667eea', 3, 1, '2025-11-21 02:09:03'),
(23, 12, 7, 'Personal-Services', 'bi-globe', '#667eea', 4, 1, '2025-11-27 12:40:29');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `reset_token`, `created_at`, `expires_at`, `is_used`, `ip_address`) VALUES
(1, 'fahmiilham029@gmail.com', '6c1cbedf46da80bfbfe15fb66a659107513f69fb9cef8f88da6ab0ead2eb1cd4', '2025-11-16 08:04:03', '2025-11-16 10:04:03', 1, '::1'),
(2, 'vivoy12gweh@gmail.com', '19742517fa3adf6f2e44770717caf24afdf3d7428de56a3919f507d9559b38ce', '2025-11-16 14:41:59', '2025-11-16 15:41:59', 1, '172.22.0.1'),
(3, 'vivoy12gweh@gmail.com', '40df38f21ef0814d5861dd24330b62dd681a10135dc8ad02c67235563b3692db', '2025-11-16 14:44:30', '2025-11-16 15:44:30', 0, '172.22.0.1'),
(4, 'yogazogo@gmail.com', 'f9db76152a96a068b13d0b2b7e8ea6e90c78737179f15cdde18d772a3d6f1635', '2025-11-16 15:32:42', '2025-11-16 16:32:42', 1, '172.22.0.1'),
(5, 'yogazogo@gmail.com', '3825de7fe7aecfb86d17b17a44e09f4c86dd70c4080a81fe3f475f79300ea49b', '2025-11-16 15:33:42', '2025-11-16 16:33:42', 0, '172.22.0.1'),
(6, 'yogazogo@gmail.com', '0b9d60c64087519f0782242dbac6938d774de56240be79042aee3918c7e30314', '2025-11-16 15:34:17', '2025-11-16 16:34:17', 0, '172.22.0.1'),
(7, 'yogazogo@gmail.com', '2521247c657781324b7435758f92eaf8b8592219bc348a6e83b51ef6877ebf55', '2025-11-16 15:34:22', '2025-11-16 16:34:22', 0, '172.22.0.1'),
(8, 'yogazogo@gmail.com', '28682be98eaa1db7db819dbae3a849946503ecb75ae7d09bc2a75077beed68d6', '2025-11-16 15:34:42', '2025-11-16 16:34:42', 0, '172.22.0.1'),
(9, 'yogazogo@gmail.com', '5303b98ff2cb41b3312aa2ef5eddd64de92dae32487fd81dcdf88c6d70de53b7', '2025-11-16 15:34:45', '2025-11-16 16:34:45', 0, '172.22.0.1'),
(10, 'yogazogo@gmail.com', '5a6f5d74544ac9b0fcfbb0a029fcc17c04496af2b47965f17f9c650ba784a058', '2025-11-16 15:34:48', '2025-11-16 16:34:48', 0, '172.22.0.1'),
(11, 'yogazogo@gmail.com', 'a4607ee086b51786a9ce008c20722ae29961703d38ebbabdffe306b535edc84d', '2025-11-16 15:34:51', '2025-11-16 16:34:51', 0, '172.22.0.1'),
(12, 'yogazogo@gmail.com', '0abd0e7a2da28997bc63606a663c4d1c669b2de53003230e5bdff7708a4ae9df', '2025-11-16 15:34:55', '2025-11-16 16:34:55', 0, '172.22.0.1'),
(13, 'yogazogo@gmail.com', '59a15bd22dd914837c218ac1c765d407c5d47136ae992fb0a49534eff47090e9', '2025-11-16 15:34:59', '2025-11-16 16:34:59', 0, '172.22.0.1'),
(14, 'yogazogo@gmail.com', 'd3c556e263aff2374725fd8aec8742eda663a1a5d43af348e73be13cfa0a3929', '2025-11-16 15:35:02', '2025-11-16 16:35:02', 0, '172.22.0.1'),
(15, 'yogazogo@gmail.com', '4936967e10e011d66aecd4e399deffd7364d87a1695ffee586d8843a081a1458', '2025-11-16 15:35:05', '2025-11-16 16:35:05', 0, '172.22.0.1'),
(16, 'yogazogo@gmail.com', 'b7cab2018eb6df735c0b34a449ecc8b148d986d70914c76eaf138c1c970dae07', '2025-11-16 15:35:09', '2025-11-16 16:35:09', 0, '172.22.0.1'),
(17, 'yogazogo@gmail.com', '814835977066a827d7beab291d203ba0dd9c1cec69913dd7bb7a604feb94f9f8', '2025-11-16 15:35:12', '2025-11-16 16:35:12', 0, '172.22.0.1'),
(18, 'yogazogo@gmail.com', '5fd4c7d5fc17f0b52619c9aa2ebc23246ff4e85f121cf2cde1fe688dd7e1b297', '2025-11-16 15:35:16', '2025-11-16 16:35:16', 0, '172.22.0.1'),
(19, 'yogazogo@gmail.com', 'a79058205edd3d2a6ba976730bdf51bcdba30bc2ed7fb44c1a4dc2341a630ea4', '2025-11-16 15:35:19', '2025-11-16 16:35:19', 0, '172.22.0.1'),
(20, 'yogazogo@gmail.com', '380ea66737bc95ac77dcc44deaad4faffd8275c0c8b624cc03a8b62018dba7cb', '2025-11-16 15:35:22', '2025-11-16 16:35:22', 0, '172.22.0.1'),
(21, 'yogazogo@gmail.com', '05d4d1231ce2613de96021c43a93cbee2a12ca06b8ee4607a0c338a9582989db', '2025-11-16 15:35:25', '2025-11-16 16:35:25', 0, '172.22.0.1'),
(22, 'yogazogo@gmail.com', '6fddb6511181137b6a4c5cd8c6e06dd0d63a1b14ac802d7557d84dce1d68516f', '2025-11-16 15:35:29', '2025-11-16 16:35:29', 0, '172.22.0.1'),
(23, 'fahmiilham029@gmail.com', '005812366756f9db7082c3691708ab9db699313ef4e9d5bdee9e42e75d749330', '2025-11-16 15:37:17', '2025-11-16 16:37:17', 0, '172.22.0.1'),
(24, 'fahmiilham029@gmail.com', 'a820162107731817503612ad47f488abba08d7695c0cd086d490574e2979ecf2', '2025-11-17 09:38:07', '2025-11-17 10:38:07', 0, '172.22.0.1'),
(25, 'fahmiilham029@gmail.com', '1d5784438aea80749fc8e1171143a225f4a16b898453139451475516675d09b1', '2025-11-18 04:07:02', '2025-11-18 05:07:02', 1, '172.22.0.1'),
(26, 'fahmiilham029@gmail.com', '26f24651d5214724b1c0001bb089b8d84d986a86649ac65794e7bedd3d030783', '2025-11-18 04:14:13', '2025-11-18 05:14:13', 0, '172.22.0.1'),
(27, 'fahmiilham029@gmail.com', '60e4e862d3283bb97256ced568b9c69bd55a8ecbafa5bd5db37a4cd42da9f1dc', '2025-11-18 04:15:03', '2025-11-18 05:15:03', 0, '172.22.0.1'),
(28, 'fahmiilham029@gmail.com', '4d5826078ac029d00486e7c81b486874747403938097309c56684812a91ecbf2', '2025-11-18 04:15:06', '2025-11-18 05:15:06', 0, '172.22.0.1'),
(29, 'fahmiilham029@gmail.com', '26d01466f60b1fe9a66cfe9d6bf1c4bb856e1cc1756addf6a66cdbea4e3ed9d3', '2025-11-28 09:34:46', '2025-11-28 10:34:46', 0, '172.22.0.1'),
(30, 'fahmiilham029@gmail.com', '2905a9fb416d32fb719150f2036c14f3e6d435332466c9370cc4ab08b795eb6a', '2025-11-28 09:34:50', '2025-11-28 10:34:50', 0, '172.22.0.1'),
(31, 'vivoy12gweh@gmail.com', '8a9842726456d505770e609331377a65acbb58402e90d5e00784f178193dd1b3', '2025-11-29 09:00:57', '2025-11-29 10:00:57', 1, '172.22.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `profile_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'Owner of this profile',
  `slug` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Unique URL slug for this profile',
  `profile_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Profile display name (e.g., "Personal", "Business")',
  `profile_description` text COLLATE utf8mb4_general_ci COMMENT 'Internal note about this profile',
  `profile_title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Public title shown on profile page',
  `bio` text COLLATE utf8mb4_general_ci COMMENT 'Profile bio/description',
  `profile_pic_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Profile picture',
  `is_primary` tinyint(1) DEFAULT '0' COMMENT '1 = primary/default profile, 0 = secondary',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '1 = active, 0 = deactivated',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_accessed_at` timestamp NULL DEFAULT NULL COMMENT 'Last time profile was viewed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Multiple profiles per user - each with independent appearance and content';

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`profile_id`, `user_id`, `slug`, `profile_name`, `profile_description`, `profile_title`, `bio`, `profile_pic_filename`, `is_primary`, `is_active`, `created_at`, `updated_at`, `last_accessed_at`) VALUES
(1, 1, 'admin', 'admin - Main Profile', NULL, 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(2, 7, 'heheheha', 'mandatori - Main Profile', NULL, 'mandatori', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(3, 8, 'nylaa', 'Nyla - Main Profile', NULL, 'Nyla', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(4, 9, 'MalingPangsit', 'MalingPangsit - Main Profile', NULL, 'MalingPangsit', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(5, 10, 'tulongg', 'sumber_air_su_dekat - Main Profile', NULL, 'sumber_air_su_dekat', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(6, 11, 'ajilahsapalagi', 'AjiSantoso - Main Profile', NULL, 'AjiSantoso', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(7, 12, 'fahmi', 'fahmi - Main Profile', NULL, 'Fahmi Ilham Bagaskara', 'I Love Internet and tech', 'user_12_1763450873.jpg', 1, 1, '2025-11-29 14:34:56', NULL, NULL),
(8, 13, 'naganiga', 'naganiga - Main Profile', NULL, 'naganiga', 'Welcome to my LinkMy page!', 'default-avatar.png', 1, 1, '2025-11-29 14:34:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `session_data` text NOT NULL,
  `session_expire` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `session_data`, `session_expire`) VALUES
('40b2ac013508b9a282f72499265e3cee', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764322476;', 1764927276),
('434c81bbb4d93028b2cde774641a72eb', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764247834;', 1764852634),
('6121170c67155c822c9e1dc9f7a56bbf', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764322602;', 1764927402),
('787b19de3a31e945c7cc31df27e4be8c', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764146178;', 1764750978),
('9b83bc05e30cd7aff61f38d4567bf44c', 'user_id|i:13;username|s:8:\"naganiga\";page_slug|s:8:\"naganiga\";last_activity|i:1764413069;', 1765017869),
('c24d22b7e5e2bb91369fffe85998fe4c', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1764428035;active_profile_id|i:7;', 1765032835),
('dfcaeace911d02bc728ded23a08f0424', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1763856883;', 1764461683);

-- --------------------------------------------------------

--
-- Table structure for table `social_icons`
--

CREATE TABLE `social_icons` (
  `icon_id` int NOT NULL,
  `platform_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `icon_class` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `icon_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `base_url` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Base URL pattern for the platform'
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `page_slug` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified` tinyint(1) DEFAULT '0' COMMENT 'Verified badge (1=verified founder/influencer)',
  `last_slug_change_at` datetime DEFAULT NULL COMMENT 'Last time user changed their primary slug (30-day cooldown)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `page_slug`, `email`, `email_verified`, `email_verified_at`, `created_at`, `is_verified`, `last_slug_change_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@linkmy.com', 0, NULL, '2025-11-10 23:29:30', 0, NULL),
(7, 'mandatori', '$2y$10$2KHGnWqmaE09y6rBkTGeYOgBhor2mHbzJHcxL24HCim3CmSrdqpaO', 'heheheha', 'kunalmr40@gmail.com', 1, '2025-11-13 01:05:43', '2025-11-13 01:05:43', 0, NULL),
(8, 'Nyla', '$2y$10$a3IhdC22rwMIkhr4rofyAeXW3J.2pSoQTDbcPAld9zUYdxrPmymTi', 'nylaa', 'nilaanidia@gmail.com', 1, '2025-11-16 14:58:17', '2025-11-16 14:58:17', 0, NULL),
(9, 'MalingPangsit', '$2y$10$xjejtjDgzhGgOwu5sO3es.WJq9Tge6I5XpKTWqrdoWLPd88uXStpq', 'MalingPangsit', 'irfannazrildebian@gmail.com', 1, '2025-11-16 15:06:11', '2025-11-16 15:06:11', 0, NULL),
(10, 'sumber_air_su_dekat', '$2y$10$xMfhL6qCj7FyqBczv.gdOutuEpb/CQJfqHTy4RfnhfnRud79ZNlEq', 'tulongg', 'yogazogo@gmail.com', 1, '2025-11-16 15:16:41', '2025-11-16 15:16:41', 0, NULL),
(11, 'AjiSantoso', '$2y$10$eM6Fi50ax/DPgbCqkJOWKOKccKk.Lmq0xsFDeuh/Sp1Z.wrUinM1S', 'ajilahsapalagi', 'jagajagaketiga@gmail.com', 1, '2025-11-16 17:08:12', '2025-11-16 17:08:12', 0, NULL),
(12, 'fahmi', '$2y$10$N7EAhQe57LT7a.yzLH7WbeYsB/8/.ySFY/RY8Co54RdyW558HEQbe', 'fahmi', 'fahmiilham029@gmail.com', 1, '2025-11-18 03:56:37', '2025-11-18 03:56:37', 1, NULL),
(13, 'naganiga', '$2y$10$IkhZqrb5qxaitJkjVobgUuVGincqnp0neGwXmS.BWmLgLtdTfQOnO', 'naganiga', 'vivoy12gweh@gmail.com', 1, '2025-11-18 03:59:27', '2025-11-18 03:59:27', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_appearance`
--

CREATE TABLE `user_appearance` (
  `appearance_id` int NOT NULL,
  `user_id` int NOT NULL,
  `profile_id` int DEFAULT NULL,
  `profile_title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `profile_pic_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default-avatar.png',
  `bg_image_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `theme_name` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'light',
  `button_style` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'rounded',
  `font_family` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Inter',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `custom_bg_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Custom background color hex',
  `custom_button_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Custom button color hex',
  `custom_text_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Custom text color hex',
  `custom_link_text_color` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gradient_preset` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Predefined gradient name',
  `profile_layout` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'centered' COMMENT 'Profile layout style: centered, left, minimal',
  `container_style` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'wide' COMMENT 'wide|boxed',
  `enable_categories` tinyint(1) DEFAULT '0' COMMENT 'Enable link categories/folders',
  `show_profile_border` tinyint(1) DEFAULT '1' COMMENT 'Show border around profile picture',
  `enable_animations` tinyint(1) DEFAULT '1' COMMENT 'Enable hover animations on links',
  `enable_glass_effect` tinyint(1) DEFAULT '0',
  `shadow_intensity` enum('none','light','medium','heavy') COLLATE utf8mb4_general_ci DEFAULT 'medium',
  `boxed_layout` tinyint(1) DEFAULT '0' COMMENT '0=full width, 1=boxed mode',
  `outer_bg_type` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'color' COMMENT 'color, gradient, image',
  `outer_bg_color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '#667eea' COMMENT 'Outer background color',
  `outer_bg_gradient_start` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '#667eea' COMMENT 'Gradient start color',
  `outer_bg_gradient_end` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '#764ba2' COMMENT 'Gradient end color',
  `outer_bg_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Path to background image',
  `container_bg_color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '#ffffff' COMMENT 'Inner container background',
  `container_max_width` int DEFAULT '480' COMMENT 'Max width in pixels for boxed container',
  `container_border_radius` int DEFAULT '30' COMMENT 'Border radius in pixels',
  `container_shadow` tinyint(1) DEFAULT '1' COMMENT 'Show shadow on container'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_appearance`
--

INSERT INTO `user_appearance` (`appearance_id`, `user_id`, `profile_id`, `profile_title`, `bio`, `profile_pic_filename`, `bg_image_filename`, `theme_name`, `button_style`, `font_family`, `updated_at`, `custom_bg_color`, `custom_button_color`, `custom_text_color`, `custom_link_text_color`, `gradient_preset`, `profile_layout`, `container_style`, `enable_categories`, `show_profile_border`, `enable_animations`, `enable_glass_effect`, `shadow_intensity`, `boxed_layout`, `outer_bg_type`, `outer_bg_color`, `outer_bg_gradient_start`, `outer_bg_gradient_end`, `outer_bg_image`, `container_bg_color`, `container_max_width`, `container_border_radius`, `container_shadow`) VALUES
(1, 1, 1, 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(7, 7, 2, 'mandatori', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(8, 8, 3, 'Nyla', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(9, 9, 4, 'MalingPangsit', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'dark', 'rounded', 'Inter', '2025-11-29 14:34:57', '#ffffff', '#667eea', '#333333', '#333333', 'Midnight Blue', 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(10, 10, 5, 'sumber_air_su_dekat', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(11, 11, 6, 'AjiSantoso', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(12, 12, 7, 'Fahmi Ilham Bagaskara', 'I Love Internet and tech', 'user_12_1763450873.jpg', NULL, 'gradient', 'pill', 'Inter', '2025-11-29 14:34:57', '#ffffff', '#9eb0ff', '#333333', '#000000', 'Rose Petal', 'minimal', 'wide', 1, 0, 1, 1, 'heavy', 1, 'gradient', '#667eea', '#334fcc', '#452862', NULL, '#ffffff', 600, 15, 1),
(13, 13, 8, 'naganiga', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-29 14:34:57', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_slugs`
--

CREATE TABLE `user_slugs` (
  `slug_id` int NOT NULL,
  `user_id` int NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0' COMMENT '1 = primary slug, 0 = alias slug',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Multiple slugs per user - all point to same profile';

--
-- Dumping data for table `user_slugs`
--

INSERT INTO `user_slugs` (`slug_id`, `user_id`, `slug`, `is_primary`, `created_at`) VALUES
(1, 1, 'admin', 1, '2025-11-10 23:29:30'),
(2, 7, 'heheheha', 1, '2025-11-13 01:05:43'),
(3, 8, 'nylaa', 1, '2025-11-16 14:58:17'),
(4, 9, 'MalingPangsit', 1, '2025-11-16 15:06:11'),
(5, 10, 'tulongg', 1, '2025-11-16 15:16:41'),
(6, 11, 'ajilahsapalagi', 1, '2025-11-16 17:08:12'),
(7, 12, 'fahmi', 1, '2025-11-18 03:56:37'),
(8, 13, 'naganiga', 1, '2025-11-18 03:59:27'),
(16, 12, 'triforce', 0, '2025-11-29 10:45:16');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_page_data`
-- (See below for the actual view)
--
CREATE TABLE `v_public_page_data` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_page_data_with_categories`
-- (See below for the actual view)
--
CREATE TABLE `v_public_page_data_with_categories` (
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp_code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `gradient_presets`
--
ALTER TABLE `gradient_presets`
  ADD PRIMARY KEY (`preset_id`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_profile_id` (`profile_id`);

--
-- Indexes for table `link_analytics`
--
ALTER TABLE `link_analytics`
  ADD PRIMARY KEY (`analytics_id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `clicked_at` (`clicked_at`),
  ADD KEY `idx_location` (`country`,`city`);

--
-- Indexes for table `link_categories`
--
ALTER TABLE `link_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_profile_id` (`profile_id`);

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
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `unique_slug` (`slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_user_primary` (`user_id`,`is_primary`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_expire` (`session_expire`);

--
-- Indexes for table `social_icons`
--
ALTER TABLE `social_icons`
  ADD PRIMARY KEY (`icon_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `page_slug` (`page_slug`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_verified` (`is_verified`);

--
-- Indexes for table `user_appearance`
--
ALTER TABLE `user_appearance`
  ADD PRIMARY KEY (`appearance_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_profile_id` (`profile_id`);

--
-- Indexes for table `user_slugs`
--
ALTER TABLE `user_slugs`
  ADD PRIMARY KEY (`slug_id`),
  ADD UNIQUE KEY `unique_slug` (`slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_user_primary` (`user_id`,`is_primary`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `gradient_presets`
--
ALTER TABLE `gradient_presets`
  MODIFY `preset_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `link_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `link_analytics`
--
ALTER TABLE `link_analytics`
  MODIFY `analytics_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `link_categories`
--
ALTER TABLE `link_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `profile_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `social_icons`
--
ALTER TABLE `social_icons`
  MODIFY `icon_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_appearance`
--
ALTER TABLE `user_appearance`
  MODIFY `appearance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_slugs`
--
ALTER TABLE `user_slugs`
  MODIFY `slug_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

-- --------------------------------------------------------

--
-- Structure for view `v_public_page_data`
--
DROP TABLE IF EXISTS `v_public_page_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_public_page_data`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`page_slug` AS `page_slug`, `u`.`is_verified` AS `is_verified`, `a`.`profile_title` AS `profile_title`, `a`.`bio` AS `bio`, `a`.`profile_pic_filename` AS `profile_pic_filename`, `a`.`bg_image_filename` AS `bg_image_filename`, `a`.`theme_name` AS `theme_name`, `a`.`button_style` AS `button_style`, `a`.`font_family` AS `font_family`, `a`.`custom_bg_color` AS `custom_bg_color`, `a`.`custom_button_color` AS `custom_button_color`, `a`.`custom_text_color` AS `custom_text_color`, `a`.`custom_link_text_color` AS `custom_link_text_color`, `a`.`gradient_preset` AS `gradient_preset`, `a`.`profile_layout` AS `profile_layout`, `a`.`container_style` AS `container_style`, `a`.`show_profile_border` AS `show_profile_border`, `a`.`enable_animations` AS `enable_animations`, `a`.`enable_glass_effect` AS `enable_glass_effect`, `a`.`shadow_intensity` AS `shadow_intensity`, `a`.`enable_categories` AS `enable_categories`, `a`.`boxed_layout` AS `boxed_layout`, `a`.`outer_bg_type` AS `outer_bg_type`, `a`.`outer_bg_color` AS `outer_bg_color`, `a`.`outer_bg_gradient_start` AS `outer_bg_gradient_start`, `a`.`outer_bg_gradient_end` AS `outer_bg_gradient_end`, `a`.`outer_bg_image` AS `outer_bg_image`, `a`.`container_bg_color` AS `container_bg_color`, `a`.`container_max_width` AS `container_max_width`, `a`.`container_border_radius` AS `container_border_radius`, `a`.`container_shadow` AS `container_shadow` FROM (`users` `u` join `appearance` `a` on((`u`.`user_id` = `a`.`user_id`))) WHERE (`u`.`email_verified` = 1) ;

-- --------------------------------------------------------

--
-- Structure for view `v_public_page_data_with_categories`
--
DROP TABLE IF EXISTS `v_public_page_data_with_categories`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_public_page_data_with_categories`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`page_slug` AS `page_slug`, `u`.`email` AS `email`, `a`.`profile_title` AS `profile_title`, `a`.`bio` AS `bio`, `a`.`profile_pic_filename` AS `profile_pic_filename`, `a`.`bg_image_filename` AS `bg_image_filename`, `a`.`theme_name` AS `theme_name`, `a`.`button_style` AS `button_style`, `a`.`gradient_preset` AS `gradient_preset`, `a`.`custom_bg_color` AS `custom_bg_color`, `a`.`custom_button_color` AS `custom_button_color`, `a`.`custom_text_color` AS `custom_text_color`, `a`.`custom_link_text_color` AS `custom_link_text_color`, `a`.`profile_layout` AS `profile_layout`, `a`.`container_style` AS `container_style`, `a`.`enable_categories` AS `enable_categories`, `a`.`show_profile_border` AS `show_profile_border`, `a`.`enable_animations` AS `enable_animations`, `a`.`enable_glass_effect` AS `enable_glass_effect`, `a`.`shadow_intensity` AS `shadow_intensity`, `l`.`link_id` AS `link_id`, `l`.`title` AS `link_title`, `l`.`url` AS `url`, `l`.`icon_class` AS `icon_class`, `l`.`click_count` AS `click_count`, `l`.`is_active` AS `is_active`, `l`.`order_index` AS `order_index`, `l`.`category_id` AS `category_id`, `c`.`category_name` AS `category_name`, `c`.`category_icon` AS `category_icon`, `c`.`category_color` AS `category_color`, `c`.`is_expanded` AS `category_expanded` FROM (((`users` `u` left join `appearance` `a` on((`u`.`user_id` = `a`.`user_id`))) left join `links` `l` on(((`u`.`user_id` = `l`.`user_id`) and (`l`.`is_active` = 1)))) left join `link_categories` `c` on((`l`.`category_id` = `c`.`category_id`))) ORDER BY `c`.`display_order` ASC, `l`.`order_index` ASC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `fk_link_category` FOREIGN KEY (`category_id`) REFERENCES `link_categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_links_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `link_analytics`
--
ALTER TABLE `link_analytics`
  ADD CONSTRAINT `fk_analytics_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE;

--
-- Constraints for table `link_categories`
--
ALTER TABLE `link_categories`
  ADD CONSTRAINT `fk_category_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_link_categories_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_appearance`
--
ALTER TABLE `user_appearance`
  ADD CONSTRAINT `fk_user_appearance_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_appearance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_slugs`
--
ALTER TABLE `user_slugs`
  ADD CONSTRAINT `fk_user_slugs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
