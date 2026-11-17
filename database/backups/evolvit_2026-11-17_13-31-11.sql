-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kavr_evaluaties_dev
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
-- Current Database: `kavr_evaluaties_dev`
--

/*!40000 DROP DATABASE IF EXISTS `kavr_evaluaties_dev`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `kavr_evaluaties_dev` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `kavr_evaluaties_dev`;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluation_periods`
--

DROP TABLE IF EXISTS `evaluation_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_periods_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `evaluation_periods_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluation_periods`
--

LOCK TABLES `evaluation_periods` WRITE;
/*!40000 ALTER TABLE `evaluation_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluation_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_12_203206_create_school_years_table',1),(5,'2026_11_12_203756_create_evaluation_periods_table',1),(6,'2026_11_12_210010_add_profile_fields_to_users_table',1),(7,'2026_11_13_103810_create_schools_table',2),(8,'2026_11_13_135741_create_school_locations_table',2),(9,'2026_11_13_135756_create_school_settings_table',2),(10,'2026_11_13_140056_create_school_user_table',3),(11,'2026_11_13_141915_add_platform_admin_to_users_table',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_locations`
--

DROP TABLE IF EXISTS `school_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `location_number` varchar(30) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_main_location` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_locations_school_id_location_number_unique` (`school_id`,`location_number`),
  CONSTRAINT `school_locations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_locations`
--

LOCK TABLES `school_locations` WRITE;
/*!40000 ALTER TABLE `school_locations` DISABLE KEYS */;
INSERT INTO `school_locations` VALUES (1,2,'Campus Speelhof',NULL,'Truierweg 89','3840','Sint-Truiden','info.speelhof@atheneumsinttruiden.be','011684347',0,1,'2026-11-16 20:32:56','2026-11-17 11:00:30'),(2,2,'Campus Tichelrij',NULL,'Tichelrijlaan 1','3800','Sint-Truiden','info.tichelrij@atheneumsinttruiden.be','011682124',0,1,'2026-11-16 20:33:49','2026-11-17 11:07:02'),(5,1,'Campus Voeren',NULL,'Kloosterstraat 19','3798','Voeren',NULL,NULL,1,1,'2026-11-17 11:20:12','2026-11-17 11:20:19'),(6,1,'Campus Riemst (Oud gemeentehuis)',NULL,'Kerkstraat 35','3770','Riemst',NULL,NULL,0,1,'2026-11-17 11:20:46','2026-11-17 11:20:56'),(7,1,'Campus Riemst (St.-Hubertusstraat)',NULL,'St.-Hubertusstraat 9','3770','Riemst',NULL,NULL,0,1,'2026-11-17 11:21:15','2026-11-17 11:21:15'),(8,1,'Campus Zichen (Zichen-Zussen-Bolder / Riemst)',NULL,'Kerkstraat 28','3770','Riemst',NULL,NULL,0,1,'2026-11-17 11:21:42','2026-11-17 11:21:42'),(9,1,'Campus Basisschool De Klinker (Riemst)',NULL,NULL,NULL,NULL,NULL,NULL,0,1,'2026-11-17 11:21:56','2026-11-17 11:21:56');
/*!40000 ALTER TABLE `school_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_settings`
--

DROP TABLE IF EXISTS `school_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `login_description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `primary_color` varchar(7) NOT NULL DEFAULT '#142b45',
  `secondary_color` varchar(7) NOT NULL DEFAULT '#59aaa5',
  `accent_color` varchar(7) NOT NULL DEFAULT '#e5a65b',
  `language` varchar(5) NOT NULL DEFAULT 'nl',
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/Brussels',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_settings_school_id_unique` (`school_id`),
  CONSTRAINT `school_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_settings`
--

LOCK TABLES `school_settings` WRITE;
/*!40000 ALTER TABLE `school_settings` DISABLE KEYS */;
INSERT INTO `school_settings` VALUES (1,1,'Ruimte voor talent.','Beeld. Muziek. Theater.','Eén plek voor alle evaluaties, leerdoelen en leerlingengroei.','school-logos/1/qplB56cDIP84fY3YvoDSt2MBGlYFlRiMaaURuGHY.jpg','#142B45','#59AAA5','#E5A65B','nl','Europe/Brussels',NULL,'2026-11-17 10:47:10'),(2,2,'Leren met lef!','Jouw talent. Jouw toekomst.','Eén centrale plek voor evaluaties, leerdoelen en de groei van elke leerling.','school-logos/2/dDzzWBPdb4kajDWMAywvYEZDMvhuWzE4lqB7PN9O.jpg','#1B2E4A','#C3E2E6','#C4104E','nl','Europe/Brussels','2026-11-13 14:02:31','2026-11-17 10:33:49');
/*!40000 ALTER TABLE `school_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_user`
--

DROP TABLE IF EXISTS `school_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `school_id` bigint(20) unsigned NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'teacher',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_user_user_id_school_id_unique` (`user_id`,`school_id`),
  KEY `school_user_school_id_foreign` (`school_id`),
  CONSTRAINT `school_user_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_user`
--

LOCK TABLES `school_user` WRITE;
/*!40000 ALTER TABLE `school_user` DISABLE KEYS */;
INSERT INTO `school_user` VALUES (1,2,1,'school_admin',1,NULL,'2026-11-13 13:04:11');
/*!40000 ALTER TABLE `school_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_years`
--

DROP TABLE IF EXISTS `school_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_years` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(9) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_years_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_years`
--

LOCK TABLES `school_years` WRITE;
/*!40000 ALTER TABLE `school_years` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `institution_number` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country_code` varchar(2) NOT NULL DEFAULT 'BE',
  `director_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_code_unique` (`code`),
  UNIQUE KEY `schools_slug_unique` (`slug`),
  UNIQUE KEY `schools_institution_number_unique` (`institution_number`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'Kunstacademie Voeren-Riemst','KAVR','kunstacademie-voeren-riemst','50252','info@ka-vr.be','043811119','https://www.ka-vr.be/','Kloosterstraat 19','3798','Voeren','BE','Conrad Onclin',1,'2026-11-11 14:02:31','2026-11-17 11:19:09'),(2,'Atheneum Sint-Truiden','AST','atheneum-sinttruiden','044487','info@peterhons.be','0476324676','http://www.atheneumsinttruiden.be','Speelhoflaan 9','3800','Sint-Truiden','BE','Pascale Liebens',0,'2026-11-13 14:02:31','2026-11-17 11:25:06');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('U4cjtwZuE4ByarOjnmg8yEFaktMyoaNQTDy2Tze8',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIwSnMxRDZBVkRJTzJiWUtGbFl6d2Z1Q1lzamFNSzkyZm9MQXpXM0hYIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAxXC9zY2hvb2xzXC9zZWxlY3QiLCJyb3V0ZSI6InNjaG9vbHMuc2VsZWN0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIsInBhc3N3b3JkX2hhc2hfd2ViIjoiM2RkMDZhYTUxOGFmODNiY2NlODU0ZmJlYTIxODk4NmE5ZDRjYTFjZTQ2NzE3NjZmMGRiZDcyYThiNzVjNzU3MCJ9',1794918607);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `is_platform_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Peter Hons','peter.hons@atheneumsinttruiden.be',NULL,'$2y$12$ARBj658jX0yFBR70pD2rcuG8bfpT3JBTiknupSkXxFYaS0qdlu4o.',NULL,'2026-11-13 07:19:55','2026-11-13 13:22:19','Peter','Hons',1,1,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'kavr_evaluaties_dev'
--

--
-- Dumping routines for database 'kavr_evaluaties_dev'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-17 13:31:12
