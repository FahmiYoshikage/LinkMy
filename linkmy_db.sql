-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Nov 21, 2025 at 01:23 AM
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
-- Table structure for table `appearance`
--

CREATE TABLE `appearance` (
  `appearance_id` int NOT NULL,
  `user_id` int NOT NULL,
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
-- Dumping data for table `appearance`
--

INSERT INTO `appearance` (`appearance_id`, `user_id`, `profile_title`, `bio`, `profile_pic_filename`, `bg_image_filename`, `theme_name`, `button_style`, `font_family`, `updated_at`, `custom_bg_color`, `custom_button_color`, `custom_text_color`, `custom_link_text_color`, `gradient_preset`, `profile_layout`, `container_style`, `enable_categories`, `show_profile_border`, `enable_animations`, `enable_glass_effect`, `shadow_intensity`, `boxed_layout`, `outer_bg_type`, `outer_bg_color`, `outer_bg_gradient_start`, `outer_bg_gradient_end`, `outer_bg_image`, `container_bg_color`, `container_max_width`, `container_border_radius`, `container_shadow`) VALUES
(1, 1, 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-10 23:29:30', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(7, 7, 'mandatori', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-13 01:05:43', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(8, 8, 'Nyla', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-16 14:58:17', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(9, 9, 'MalingPangsit', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'dark', 'rounded', 'Inter', '2025-11-16 15:16:57', '#ffffff', '#667eea', '#333333', '#333333', 'Midnight Blue', 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(10, 10, 'sumber_air_su_dekat', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-16 15:16:41', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(11, 11, 'AjiSantoso', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-16 17:08:12', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1),
(12, 12, 'Fahmi', 'I Love Internet and tech', 'user_12_1763450873.jpg', NULL, 'gradient', 'pill', 'Inter', '2025-11-20 15:55:35', '#ffffff', '#9eb0ff', '#333333', '#000000', 'Pink Lemonade', 'minimal', 'wide', 1, 0, 1, 1, 'heavy', 1, 'gradient', '#667eea', '#5d75df', '#872ce2', NULL, '#ffffff', 480, 30, 1),
(13, 13, 'naganiga', 'Welcome to my LinkMy page!', 'default-avatar.png', NULL, 'light', 'rounded', 'Inter', '2025-11-18 03:59:27', NULL, NULL, NULL, NULL, NULL, 'centered', 'wide', 0, 1, 1, 0, 'medium', 0, 'color', '#667eea', '#667eea', '#764ba2', NULL, '#ffffff', 480, 30, 1);

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
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
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
(26, 'kunalmr40@gmail.com', '347860', '2025-11-13 00:57:47', '2025-11-12 19:07:47', 1, '::1'),
(27, 'kanduang@gmail.com', '871525', '2025-11-16 14:55:48', '2025-11-16 15:05:48', 0, '172.22.0.1'),
(28, 'kanduang@gmail.com', '536895', '2025-11-16 14:55:51', '2025-11-16 15:05:51', 0, '172.22.0.1'),
(29, 'kanduang@gmail.com', '789981', '2025-11-16 14:55:54', '2025-11-16 15:05:54', 0, '172.22.0.1'),
(30, 'kanduang@gmail.com', '588594', '2025-11-16 14:55:58', '2025-11-16 15:05:58', 0, '172.22.0.1'),
(31, 'kanduang@gmail.com', '008442', '2025-11-16 14:56:01', '2025-11-16 15:06:01', 0, '172.22.0.1'),
(32, 'kanduang@gmail.com', '608147', '2025-11-16 14:56:04', '2025-11-16 15:06:04', 0, '172.22.0.1'),
(33, 'nilaanidia@gmail.com', '602361', '2025-11-16 14:57:15', '2025-11-16 15:07:15', 1, '172.22.0.1'),
(34, 'yogazogo@gmail.com', '429554', '2025-11-16 15:04:26', '2025-11-16 15:14:26', 1, '172.22.0.1'),
(35, 'irfannazrildebian@gmail.com', '297327', '2025-11-16 15:05:08', '2025-11-16 15:15:08', 1, '172.22.0.1'),
(36, 'jagajagaketiga@gmail.com', '834199', '2025-11-16 17:07:33', '2025-11-16 17:17:33', 1, '172.22.0.1'),
(37, 'hutyasooitsthyven@gmail.com', '414534', '2025-11-17 08:25:23', '2025-11-17 08:35:23', 0, '172.22.0.1'),
(38, 'hutasooitsthyven@gmail.com', '025074', '2025-11-17 08:25:35', '2025-11-17 08:35:35', 0, '172.22.0.1'),
(39, 'hutasoitsthyven@gmail.com', '796910', '2025-11-17 08:25:44', '2025-11-17 08:35:44', 1, '172.22.0.1'),
(40, 'fahmiilham029@gmail.com', '968615', '2025-11-18 03:49:46', '2025-11-18 03:59:46', 0, '172.22.0.1'),
(41, 'fahmiilham029@gmail.com', '460305', '2025-11-18 03:49:50', '2025-11-18 03:59:50', 1, '172.22.0.1'),
(42, 'fahmiilham029@gmail.com', '163434', '2025-11-18 03:55:53', '2025-11-18 04:05:53', 1, '172.22.0.1'),
(43, 'vivoy12gweh@gmail.com', '619176', '2025-11-18 03:57:38', '2025-11-18 04:07:38', 1, '172.22.0.1');

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

INSERT INTO `links` (`link_id`, `user_id`, `title`, `url`, `order_index`, `icon_class`, `click_count`, `is_active`, `created_at`, `category_id`) VALUES
(1, 1, 'Instagram', 'https://instagram.com', 1, 'bi-instagram', 0, 1, '2025-11-10 23:29:30', NULL),
(2, 1, 'GitHub', 'https://github.com', 2, 'bi-github', 0, 1, '2025-11-10 23:29:30', NULL),
(3, 1, 'LinkedIn', 'https://linkedin.com', 3, 'bi-linkedin', 0, 1, '2025-11-10 23:29:30', NULL),
(6, 7, 'labaka saturnu saturnika', 'https://08.shinigami.asia/', 1, 'bi-link-45deg', 1, 1, '2025-11-13 01:06:28', NULL),
(10, 9, 'Youtube', 'youtube://youtube.com/dQw4w9WgXcQ?si=ZW-Lq6EZsG0aJslv', 1, 'bi-youtube', 3, 1, '2025-11-16 15:13:34', NULL),
(11, 10, 'bahlil', 'https://ibehelp.gt.tc/', 1, 'bi-link-45deg', 9, 1, '2025-11-16 15:17:53', NULL),
(12, 10, 'ss', 'https://ibehelp.gt.tc/', 2, 'bi-link-45deg', 4, 1, '2025-11-16 15:29:29', NULL),
(13, 10, 'se', 'https://ibehelp.gt.tc/', 3, 'bi-link-45deg', 4, 1, '2025-11-16 15:29:37', NULL),
(14, 10, 'ss', 'https://ibehelp.gt.tc/', 4, 'bi-link-45deg', 1, 1, '2025-11-16 15:29:45', NULL),
(16, 12, 'INSTAGRAM', 'https://www.instagram.com/fahmi.ilham06/', 1, 'bi-instagram', 1, 1, '2025-11-18 05:38:56', 19),
(17, 12, 'Toko Ngawi', 'https://shopee.co.id/', 2, 'bi-shop', 1, 1, '2025-11-18 06:27:04', 19);

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
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_categories`
--

CREATE TABLE `link_categories` (
  `category_id` int NOT NULL,
  `user_id` int NOT NULL,
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

INSERT INTO `link_categories` (`category_id`, `user_id`, `category_name`, `category_icon`, `category_color`, `display_order`, `is_expanded`, `created_at`) VALUES
(1, 1, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(3, 7, 'Social Media', 'bi-people-fill', '#667eea', 1, 1, '2025-11-15 14:30:55'),
(8, 1, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(10, 7, 'Professional', 'bi-briefcase-fill', '#28a745', 2, 1, '2025-11-15 14:30:55'),
(15, 1, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(17, 7, 'Content', 'bi-play-circle-fill', '#dc3545', 3, 1, '2025-11-15 14:30:55'),
(19, 12, 'Social Media', 'bi-folder', '#667eea', 1, 1, '2025-11-18 23:32:10');

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
(28, 'fahmiilham029@gmail.com', '4d5826078ac029d00486e7c81b486874747403938097309c56684812a91ecbf2', '2025-11-18 04:15:06', '2025-11-18 05:15:06', 0, '172.22.0.1');

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
('70081f39c6e0dce8332fa6de9efd1903', '', 1764228865),
('b00bb622c5f3a16fb07f61d08116fa94', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1763643470;', 1764248270),
('b05fdf8d0a44e15e4209daab3884b3ed', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1763579816;', 1764184616),
('b568e22a74c3633bcb5d20d7b97027f5', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1763513922;', 1764118722),
('dfcaeace911d02bc728ded23a08f0424', 'user_id|i:12;username|s:5:\"fahmi\";page_slug|s:5:\"fahmi\";last_activity|i:1763687499;', 1764292299),
('eab6c29612c6b7ff768941d3cf20dd34', '', 1764123882);

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `page_slug`, `email`, `email_verified`, `email_verified_at`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@linkmy.com', 0, NULL, '2025-11-10 23:29:30'),
(7, 'mandatori', '$2y$10$2KHGnWqmaE09y6rBkTGeYOgBhor2mHbzJHcxL24HCim3CmSrdqpaO', 'heheheha', 'kunalmr40@gmail.com', 1, '2025-11-13 01:05:43', '2025-11-13 01:05:43'),
(8, 'Nyla', '$2y$10$a3IhdC22rwMIkhr4rofyAeXW3J.2pSoQTDbcPAld9zUYdxrPmymTi', 'nylaa', 'nilaanidia@gmail.com', 1, '2025-11-16 14:58:17', '2025-11-16 14:58:17'),
(9, 'MalingPangsit', '$2y$10$xjejtjDgzhGgOwu5sO3es.WJq9Tge6I5XpKTWqrdoWLPd88uXStpq', 'MalingPangsit', 'irfannazrildebian@gmail.com', 1, '2025-11-16 15:06:11', '2025-11-16 15:06:11'),
(10, 'sumber_air_su_dekat', '$2y$10$xMfhL6qCj7FyqBczv.gdOutuEpb/CQJfqHTy4RfnhfnRud79ZNlEq', 'tulongg', 'yogazogo@gmail.com', 1, '2025-11-16 15:16:41', '2025-11-16 15:16:41'),
(11, 'AjiSantoso', '$2y$10$eM6Fi50ax/DPgbCqkJOWKOKccKk.Lmq0xsFDeuh/Sp1Z.wrUinM1S', 'ajilahsapalagi', 'jagajagaketiga@gmail.com', 1, '2025-11-16 17:08:12', '2025-11-16 17:08:12'),
(12, 'fahmi', '$2y$10$N7EAhQe57LT7a.yzLH7WbeYsB/8/.ySFY/RY8Co54RdyW558HEQbe', 'fahmi', 'fahmiilham029@gmail.com', 1, '2025-11-18 03:56:37', '2025-11-18 03:56:37'),
(13, 'naganiga', '$2y$10$CTMt1mHEgKlhnlgo3my32eg2TqYqir4PLAA/xNrIONsfPplkWWb/u', 'naganiga', 'vivoy12gweh@gmail.com', 1, '2025-11-18 03:59:27', '2025-11-18 03:59:27');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_page_data`
-- (See below for the actual view)
--
CREATE TABLE `v_public_page_data` (
`bg_image_filename` varchar(255)
,`bio` text
,`boxed_layout` tinyint(1)
,`button_style` varchar(20)
,`container_bg_color` varchar(50)
,`container_border_radius` int
,`container_max_width` int
,`container_shadow` tinyint(1)
,`container_style` varchar(20)
,`custom_bg_color` varchar(20)
,`custom_button_color` varchar(20)
,`custom_link_text_color` varchar(20)
,`custom_text_color` varchar(20)
,`enable_animations` tinyint(1)
,`enable_categories` tinyint(1)
,`enable_glass_effect` tinyint(1)
,`font_family` varchar(50)
,`gradient_preset` varchar(50)
,`outer_bg_color` varchar(50)
,`outer_bg_gradient_end` varchar(50)
,`outer_bg_gradient_start` varchar(50)
,`outer_bg_image` varchar(255)
,`outer_bg_type` varchar(20)
,`page_slug` varchar(50)
,`profile_layout` varchar(20)
,`profile_pic_filename` varchar(255)
,`profile_title` varchar(100)
,`shadow_intensity` enum('none','light','medium','heavy')
,`show_profile_border` tinyint(1)
,`theme_name` varchar(20)
,`user_id` int
,`username` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_public_page_data_with_categories`
-- (See below for the actual view)
--
CREATE TABLE `v_public_page_data_with_categories` (
`bg_image_filename` varchar(255)
,`bio` text
,`button_style` varchar(20)
,`category_color` varchar(20)
,`category_expanded` tinyint(1)
,`category_icon` varchar(50)
,`category_id` int
,`category_name` varchar(50)
,`click_count` int
,`container_style` varchar(20)
,`custom_bg_color` varchar(20)
,`custom_button_color` varchar(20)
,`custom_link_text_color` varchar(20)
,`custom_text_color` varchar(20)
,`email` varchar(100)
,`enable_animations` tinyint(1)
,`enable_categories` tinyint(1)
,`enable_glass_effect` tinyint(1)
,`gradient_preset` varchar(50)
,`icon_class` varchar(50)
,`is_active` tinyint(1)
,`link_id` int
,`link_title` varchar(100)
,`order_index` int
,`page_slug` varchar(50)
,`profile_layout` varchar(20)
,`profile_pic_filename` varchar(255)
,`profile_title` varchar(100)
,`shadow_intensity` enum('none','light','medium','heavy')
,`show_profile_border` tinyint(1)
,`theme_name` varchar(20)
,`url` varchar(500)
,`user_id` int
,`username` varchar(50)
);

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
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `link_analytics`
--
ALTER TABLE `link_analytics`
  ADD PRIMARY KEY (`analytics_id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `clicked_at` (`clicked_at`);

--
-- Indexes for table `link_categories`
--
ALTER TABLE `link_categories`
  ADD PRIMARY KEY (`category_id`),
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
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appearance`
--
ALTER TABLE `appearance`
  MODIFY `appearance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `link_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `link_analytics`
--
ALTER TABLE `link_analytics`
  MODIFY `analytics_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `link_categories`
--
ALTER TABLE `link_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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

-- --------------------------------------------------------

--
-- Structure for view `v_public_page_data`
--
DROP TABLE IF EXISTS `v_public_page_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_public_page_data`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`page_slug` AS `page_slug`, `a`.`profile_title` AS `profile_title`, `a`.`bio` AS `bio`, `a`.`profile_pic_filename` AS `profile_pic_filename`, `a`.`bg_image_filename` AS `bg_image_filename`, `a`.`theme_name` AS `theme_name`, `a`.`button_style` AS `button_style`, `a`.`font_family` AS `font_family`, `a`.`custom_bg_color` AS `custom_bg_color`, `a`.`custom_button_color` AS `custom_button_color`, `a`.`custom_text_color` AS `custom_text_color`, `a`.`custom_link_text_color` AS `custom_link_text_color`, `a`.`gradient_preset` AS `gradient_preset`, `a`.`profile_layout` AS `profile_layout`, `a`.`container_style` AS `container_style`, `a`.`show_profile_border` AS `show_profile_border`, `a`.`enable_animations` AS `enable_animations`, `a`.`enable_glass_effect` AS `enable_glass_effect`, `a`.`shadow_intensity` AS `shadow_intensity`, `a`.`enable_categories` AS `enable_categories`, `a`.`boxed_layout` AS `boxed_layout`, `a`.`outer_bg_type` AS `outer_bg_type`, `a`.`outer_bg_color` AS `outer_bg_color`, `a`.`outer_bg_gradient_start` AS `outer_bg_gradient_start`, `a`.`outer_bg_gradient_end` AS `outer_bg_gradient_end`, `a`.`outer_bg_image` AS `outer_bg_image`, `a`.`container_bg_color` AS `container_bg_color`, `a`.`container_max_width` AS `container_max_width`, `a`.`container_border_radius` AS `container_border_radius`, `a`.`container_shadow` AS `container_shadow` FROM (`users` `u` join `appearance` `a` on((`u`.`user_id` = `a`.`user_id`))) WHERE (`u`.`email_verified` = 1) ;

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
-- Constraints for table `appearance`
--
ALTER TABLE `appearance`
  ADD CONSTRAINT `appearance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_category_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
