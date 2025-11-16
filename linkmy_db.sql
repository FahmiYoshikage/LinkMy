-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: linkmy_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appearance`
--

DROP TABLE IF EXISTS `appearance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appearance` (
  `appearance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_title` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic_filename` varchar(255) DEFAULT 'default-avatar.png',
  `bg_image_filename` varchar(255) DEFAULT NULL,
  `theme_name` varchar(20) DEFAULT 'light',
  `button_style` varchar(20) DEFAULT 'rounded',
  `font_family` varchar(50) DEFAULT 'Inter',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `custom_bg_color` varchar(20) DEFAULT NULL COMMENT 'Custom background color hex',
  `custom_button_color` varchar(20) DEFAULT NULL COMMENT 'Custom button color hex',
  `custom_text_color` varchar(20) DEFAULT NULL COMMENT 'Custom text color hex',
  `custom_link_text_color` varchar(20) DEFAULT NULL,
  `gradient_preset` varchar(50) DEFAULT NULL COMMENT 'Predefined gradient name',
  `profile_layout` varchar(20) DEFAULT 'centered' COMMENT 'Profile layout style: centered, left, minimal',
  `show_profile_border` tinyint(1) DEFAULT 1 COMMENT 'Show border around profile picture',
  `enable_animations` tinyint(1) DEFAULT 1 COMMENT 'Enable hover animations on links',
  `enable_glass_effect` tinyint(1) DEFAULT 0,
  `shadow_intensity` enum('none','light','medium','heavy') DEFAULT 'medium',
  PRIMARY KEY (`appearance_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `appearance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appearance`
--

LOCK TABLES `appearance` WRITE;
/*!40000 ALTER TABLE `appearance` DISABLE KEYS */;
INSERT INTO `appearance` VALUES (1,1,'Admin LinkMy','Welcome to LinkMy - Your Personal Link Hub','default-avatar.png',NULL,'light','rounded','Inter','2025-11-10 23:29:30',NULL,NULL,NULL,NULL,NULL,'centered',1,1,0,'medium'),(5,5,'Fahmi Ilham Bagaskara','This is my sosial media and my project','user_5_1762882308.png',NULL,'dark','pill','Inter','2025-11-16 08:58:35','#704343','#000000','#6b0000','#000000','Sunset Glow','left',1,1,1,'heavy'),(6,6,'nagatoro','Welcome to my LinkMy page!','default-avatar.png',NULL,'light','sharp','Inter','2025-11-13 00:57:02',NULL,NULL,NULL,NULL,NULL,'centered',1,1,0,'medium'),(7,7,'mandatori','Welcome to my LinkMy page!','default-avatar.png',NULL,'light','rounded','Inter','2025-11-13 01:05:43',NULL,NULL,NULL,NULL,NULL,'centered',1,1,0,'medium');
/*!40000 ALTER TABLE `appearance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_otp` (`otp_code`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
INSERT INTO `email_verifications` VALUES (14,'kunalmr40@gmail.com','488129','2025-11-12 05:31:28','2025-11-11 23:41:28',0,NULL),(20,'kunalmr40@gmail.com','086821','2025-11-13 00:05:44','2025-11-12 18:15:44',0,'::1'),(23,'vivoy12gweh@gmail.com','492648','2025-11-13 00:30:33','2025-11-12 18:40:33',0,'::1'),(24,'vivoy12gweh@gmail.com','347508','2025-11-13 00:48:57','2025-11-12 18:58:57',0,'::1'),(25,'vivoy12gweh@gmail.com','185055','2025-11-13 00:49:14','2025-11-12 18:59:14',1,'::1'),(26,'kunalmr40@gmail.com','347860','2025-11-13 00:57:47','2025-11-12 19:07:47',1,'::1');
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gradient_presets`
--

DROP TABLE IF EXISTS `gradient_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gradient_presets` (
  `preset_id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_name` varchar(50) NOT NULL,
  `gradient_css` text NOT NULL,
  `preview_color_1` varchar(20) NOT NULL,
  `preview_color_2` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`preset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gradient_presets`
--

LOCK TABLES `gradient_presets` WRITE;
/*!40000 ALTER TABLE `gradient_presets` DISABLE KEYS */;
INSERT INTO `gradient_presets` VALUES (1,'Purple Dream','linear-gradient(135deg, #667eea 0%, #764ba2 100%)','#667eea','#764ba2',1),(2,'Ocean Blue','linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)','#00c6ff','#0072ff',1),(3,'Sunset Orange','linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)','#ff6a00','#ee0979',1),(4,'Fresh Mint','linear-gradient(135deg, #00b09b 0%, #96c93d 100%)','#00b09b','#96c93d',1),(5,'Pink Lemonade','linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)','#ff9a9e','#fecfef',1),(6,'Royal Purple','linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)','#8e2de2','#4a00e0',1),(7,'Fire Blaze','linear-gradient(135deg, #f85032 0%, #e73827 100%)','#f85032','#e73827',1),(8,'Emerald Water','linear-gradient(135deg, #348f50 0%, #56b4d3 100%)','#348f50','#56b4d3',1),(9,'Candy Shop','linear-gradient(135deg, #f093fb 0%, #f5576c 100%)','#f093fb','#f5576c',1),(10,'Cool Blues','linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)','#4facfe','#00f2fe',1),(11,'Warm Flame','linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)','#ff9a56','#ff6a88',1),(12,'Deep Sea','linear-gradient(135deg, #2e3192 0%, #1bffff 100%)','#2e3192','#1bffff',1),(13,'Nebula Night','linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%)','#3a1c71','#ffaf7b',1),(14,'Aurora Borealis','linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%)','#00c9ff','#92fe9d',1),(15,'Crimson Tide','linear-gradient(135deg, #c31432 0%, #240b36 100%)','#c31432','#240b36',1),(16,'Golden Hour','linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 50%, #e17055 100%)','#ffeaa7','#e17055',1),(17,'Midnight Blue','linear-gradient(135deg, #000428 0%, #004e92 100%)','#000428','#004e92',1),(18,'Rose Petal','linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)','#ffecd2','#fcb69f',1),(19,'Electric Violet','linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)','#4776e6','#8e54e9',1),(20,'Jungle Green','linear-gradient(135deg, #134e5e 0%, #71b280 100%)','#134e5e','#71b280',1),(21,'Peach Cream','linear-gradient(135deg, #ff9a8b 0%, #ff6a88 50%, #ff99ac 100%)','#ff9a8b','#ff99ac',1),(22,'Arctic Ice','linear-gradient(135deg, #667db6 0%, #0082c8 50%, #0082c8 100%, #667db6 100%)','#667db6','#0082c8',1),(23,'Sunset Glow','linear-gradient(135deg, #ffa751 0%, #ffe259 100%)','#ffa751','#ffe259',1),(24,'Purple Haze','linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)','#c471f5','#fa71cd',1);
/*!40000 ALTER TABLE `gradient_presets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `link_analytics`
--

DROP TABLE IF EXISTS `link_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_analytics` (
  `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) NOT NULL,
  `clicked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `referrer` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`analytics_id`),
  KEY `link_id` (`link_id`),
  KEY `clicked_at` (`clicked_at`),
  CONSTRAINT `fk_analytics_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `link_analytics`
--

LOCK TABLES `link_analytics` WRITE;
/*!40000 ALTER TABLE `link_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `link_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `link_categories`
--

DROP TABLE IF EXISTS `link_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_icon` varchar(50) DEFAULT 'bi-folder',
  `category_color` varchar(20) DEFAULT '#667eea',
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_category_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `link_categories`
--

LOCK TABLES `link_categories` WRITE;
/*!40000 ALTER TABLE `link_categories` DISABLE KEYS */;
INSERT INTO `link_categories` VALUES (1,1,'Social Media','bi-people-fill','#667eea',1,1,'2025-11-15 14:30:55'),(2,5,'Social Media','bi-people-fill','#667eea',1,1,'2025-11-15 14:30:55'),(3,7,'Social Media','bi-people-fill','#667eea',1,1,'2025-11-15 14:30:55'),(4,6,'Social Media','bi-people-fill','#667eea',1,1,'2025-11-15 14:30:55'),(8,1,'Professional','bi-briefcase-fill','#28a745',2,1,'2025-11-15 14:30:55'),(9,5,'Professional','bi-briefcase-fill','#28a745',2,1,'2025-11-15 14:30:55'),(10,7,'Professional','bi-briefcase-fill','#28a745',2,1,'2025-11-15 14:30:55'),(11,6,'Professional','bi-briefcase-fill','#28a745',2,1,'2025-11-15 14:30:55'),(15,1,'Content','bi-play-circle-fill','#dc3545',3,1,'2025-11-15 14:30:55'),(16,5,'Content','bi-play-circle-fill','#dc3545',3,1,'2025-11-15 14:30:55'),(17,7,'Content','bi-play-circle-fill','#dc3545',3,1,'2025-11-15 14:30:55'),(18,6,'Content','bi-play-circle-fill','#dc3545',3,1,'2025-11-15 14:30:55');
/*!40000 ALTER TABLE `link_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `order_index` int(11) DEFAULT 0,
  `icon_class` varchar(50) DEFAULT 'bi-link-45deg',
  `click_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL COMMENT 'Link category for grouping',
  PRIMARY KEY (`link_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_link_category` FOREIGN KEY (`category_id`) REFERENCES `link_categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links`
--

LOCK TABLES `links` WRITE;
/*!40000 ALTER TABLE `links` DISABLE KEYS */;
INSERT INTO `links` VALUES (1,1,'Instagram','https://instagram.com',1,'bi-instagram',0,1,'2025-11-10 23:29:30',NULL),(2,1,'GitHub','https://github.com',2,'bi-github',0,1,'2025-11-10 23:29:30',NULL),(3,1,'LinkedIn','https://linkedin.com',3,'bi-linkedin',0,1,'2025-11-10 23:29:30',NULL),(4,5,'Instagram Saya','https://www.instagram.com/fahmi.ilham06/',1,'bi-instagram',1,1,'2025-11-11 17:31:26',NULL),(5,6,'Linkedin','https://www.instagram.com/with.io',1,'bi-link-45deg',2,1,'2025-11-13 00:56:41',NULL),(6,7,'labaka saturnu saturnika','https://08.shinigami.asia/',1,'bi-link-45deg',1,1,'2025-11-13 01:06:28',NULL),(7,5,'Linkedin','https://www.linkedin.com/in/fahmi-ilham-bagaskara-65a197305/',2,'bi-linkedin',0,1,'2025-11-13 05:48:55',NULL),(8,5,'Facebook','https://www.facebook.com/Fahmi1lham/',3,'bi-facebook',0,1,'2025-11-13 07:02:56',NULL),(9,5,'Spotify','https://spotify',4,'bi-spotify',0,1,'2025-11-15 14:33:00',NULL);
/*!40000 ALTER TABLE `links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reset_token` (`reset_token`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`reset_token`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_email_token` (`email`,`reset_token`),
  KEY `idx_token_used` (`reset_token`,`is_used`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'fahmiilham029@gmail.com','6c1cbedf46da80bfbfe15fb66a659107513f69fb9cef8f88da6ab0ead2eb1cd4','2025-11-16 08:04:03','2025-11-16 10:04:03',1,'::1');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_icons`
--

DROP TABLE IF EXISTS `social_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_icons` (
  `icon_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(50) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `icon_color` varchar(20) DEFAULT NULL,
  `base_url` varchar(100) DEFAULT NULL COMMENT 'Base URL pattern for the platform',
  PRIMARY KEY (`icon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_icons`
--

LOCK TABLES `social_icons` WRITE;
/*!40000 ALTER TABLE `social_icons` DISABLE KEYS */;
INSERT INTO `social_icons` VALUES (1,'Instagram','bi-instagram','#E4405F','https://instagram.com/'),(2,'Facebook','bi-facebook','#1877F2','https://facebook.com/'),(3,'Twitter/X','bi-twitter-x','#000000','https://twitter.com/'),(4,'LinkedIn','bi-linkedin','#0A66C2','https://linkedin.com/in/'),(5,'GitHub','bi-github','#181717','https://github.com/'),(6,'YouTube','bi-youtube','#FF0000','https://youtube.com/'),(7,'TikTok','bi-tiktok','#000000','https://tiktok.com/@'),(8,'WhatsApp','bi-whatsapp','#25D366','https://wa.me/'),(9,'Telegram','bi-telegram','#26A5E4','https://t.me/'),(10,'Discord','bi-discord','#5865F2','https://discord.gg/'),(11,'Twitch','bi-twitch','#9146FF','https://twitch.tv/'),(12,'Spotify','bi-spotify','#1DB954','https://open.spotify.com/'),(13,'Medium','bi-medium','#000000','https://medium.com/@'),(14,'Reddit','bi-reddit','#FF4500','https://reddit.com/u/'),(15,'Pinterest','bi-pinterest','#E60023','https://pinterest.com/'),(16,'Snapchat','bi-snapchat','#FFFC00','https://snapchat.com/add/'),(17,'Email','bi-envelope-fill','#EA4335','mailto:'),(18,'Website','bi-globe','#667eea','https://'),(19,'Link','bi-link-45deg','#6c757d',NULL);
/*!40000 ALTER TABLE `social_icons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `page_slug` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `page_slug` (`page_slug`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','admin@linkmy.com',0,NULL,'2025-11-10 23:29:30'),(5,'fahmi','$2y$10$P9c9VWWsb0J.zjHpFrYkLuCK8Ggq.X6UoA7DJtjbej.XoFu9OUeMK','KagayakuVerse','fahmiilham029@gmail.com',0,NULL,'2025-11-11 16:24:38'),(6,'nagatoro','$2y$10$jhf4tHg74zrLqpAC1xeHzetb4t5fSNPC9B0H/Ga9acC2Anf4EWZeG','nagatoro','vivoy12gweh@gmail.com',1,'2025-11-13 00:56:09','2025-11-13 00:56:09'),(7,'mandatori','$2y$10$2KHGnWqmaE09y6rBkTGeYOgBhor2mHbzJHcxL24HCim3CmSrdqpaO','heheheha','kunalmr40@gmail.com',1,'2025-11-13 01:05:43','2025-11-13 01:05:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_public_page_data`
--

DROP TABLE IF EXISTS `v_public_page_data`;
/*!50001 DROP VIEW IF EXISTS `v_public_page_data`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_public_page_data` AS SELECT
 1 AS `user_id`,
  1 AS `username`,
  1 AS `page_slug`,
  1 AS `profile_title`,
  1 AS `bio`,
  1 AS `profile_pic_filename`,
  1 AS `bg_image_filename`,
  1 AS `theme_name`,
  1 AS `button_style`,
  1 AS `font_family`,
  1 AS `custom_bg_color`,
  1 AS `custom_button_color`,
  1 AS `custom_text_color`,
  1 AS `custom_link_text_color`,
  1 AS `gradient_preset`,
  1 AS `profile_layout`,
  1 AS `show_profile_border`,
  1 AS `enable_animations`,
  1 AS `enable_glass_effect`,
  1 AS `shadow_intensity`,
  1 AS `link_id`,
  1 AS `link_title`,
  1 AS `link_url`,
  1 AS `icon_class`,
  1 AS `click_count`,
  1 AS `order_index`,
  1 AS `category_id`,
  1 AS `category_name`,
  1 AS `category_icon`,
  1 AS `category_color` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_public_page_data`
--

/*!50001 DROP VIEW IF EXISTS `v_public_page_data`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_public_page_data` AS select `u`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`page_slug` AS `page_slug`,`a`.`profile_title` AS `profile_title`,`a`.`bio` AS `bio`,`a`.`profile_pic_filename` AS `profile_pic_filename`,`a`.`bg_image_filename` AS `bg_image_filename`,`a`.`theme_name` AS `theme_name`,`a`.`button_style` AS `button_style`,`a`.`font_family` AS `font_family`,`a`.`custom_bg_color` AS `custom_bg_color`,`a`.`custom_button_color` AS `custom_button_color`,`a`.`custom_text_color` AS `custom_text_color`,`a`.`custom_link_text_color` AS `custom_link_text_color`,`a`.`gradient_preset` AS `gradient_preset`,`a`.`profile_layout` AS `profile_layout`,`a`.`show_profile_border` AS `show_profile_border`,`a`.`enable_animations` AS `enable_animations`,`a`.`enable_glass_effect` AS `enable_glass_effect`,`a`.`shadow_intensity` AS `shadow_intensity`,`l`.`link_id` AS `link_id`,`l`.`title` AS `link_title`,`l`.`url` AS `link_url`,`l`.`icon_class` AS `icon_class`,`l`.`click_count` AS `click_count`,`l`.`order_index` AS `order_index`,`l`.`category_id` AS `category_id`,`lc`.`category_name` AS `category_name`,`lc`.`category_icon` AS `category_icon`,`lc`.`category_color` AS `category_color` from (((`users` `u` left join `appearance` `a` on(`u`.`user_id` = `a`.`user_id`)) left join `links` `l` on(`u`.`user_id` = `l`.`user_id` and `l`.`is_active` = 1)) left join `link_categories` `lc` on(`l`.`category_id` = `lc`.`category_id`)) order by `u`.`user_id`,`lc`.`order_index`,`l`.`order_index` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-16 16:24:44
