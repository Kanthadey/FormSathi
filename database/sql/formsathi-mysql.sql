-- FormSathi database for MySQL 5.7+ / MariaDB 10.3+ (e.g. XAMPP + phpMyAdmin)
--
-- Exported from the app's own migrations and seeder: every table, the
-- e-passport form template, and the migration history (so
-- `php artisan migrate` knows the schema is already in place).
-- No user data is included.
--
-- Import in phpMyAdmin: Import -> choose this file -> Go.
-- It creates the database `formsathi` if it doesn't exist and uses it.
-- To use a different name, change the two lines below.

CREATE DATABASE IF NOT EXISTS `formsathi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `formsathi`;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: formsathi_build_tmp
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
-- Table structure for table `access_logs`
--

DROP TABLE IF EXISTS `access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `subject_type` varchar(40) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `access_logs_actor_id_foreign` (`actor_id`),
  KEY `access_logs_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `access_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `access_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_logs`
--

LOCK TABLES `access_logs` WRITE;
/*!40000 ALTER TABLE `access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `form_template_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `copied` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`copied`)),
  `summary` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `funded_by` varchar(10) NOT NULL DEFAULT 'free',
  PRIMARY KEY (`id`),
  KEY `applications_form_template_id_foreign` (`form_template_id`),
  KEY `applications_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `applications_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`),
  CONSTRAINT `applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
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
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `career_items`
--

DROP TABLE IF EXISTS `career_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `career_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `kind` varchar(20) NOT NULL,
  `data` text NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_items_user_id_kind_index` (`user_id`,`kind`),
  CONSTRAINT `career_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_items`
--

LOCK TABLES `career_items` WRITE;
/*!40000 ALTER TABLE `career_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_fields`
--

DROP TABLE IF EXISTS `document_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `document_id` bigint(20) unsigned NOT NULL,
  `key` varchar(60) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_fields_document_id_key_unique` (`document_id`,`key`),
  KEY `document_fields_user_id_key_index` (`user_id`,`key`),
  CONSTRAINT `document_fields_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_fields_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_fields`
--

LOCK TABLES `document_fields` WRITE;
/*!40000 ALTER TABLE `document_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(40) NOT NULL,
  `category` varchar(20) NOT NULL,
  `title` text DEFAULT NULL,
  `original_name` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `expires_at` date DEFAULT NULL,
  `expiry_reminded_at` timestamp NULL DEFAULT NULL,
  `ocr_status` varchar(20) NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ocr_suggestions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_user_id_category_index` (`user_id`,`category`),
  KEY `documents_expires_at_expiry_reminded_at_index` (`expires_at`,`expiry_reminded_at`),
  CONSTRAINT `documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
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
-- Table structure for table `form_templates`
--

DROP TABLE IF EXISTS `form_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `name_bn` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `official_url` varchar(255) DEFAULT NULL,
  `sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`sections`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_templates_slug_version_unique` (`slug`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_templates`
--

LOCK TABLES `form_templates` WRITE;
/*!40000 ALTER TABLE `form_templates` DISABLE KEYS */;
INSERT INTO `form_templates` VALUES (1,'e-passport',1,'ই-পাসপোর্ট আবেদন','E-passport application','https://www.epassport.gov.bd','[{\"key\":\"personal\",\"bn\":\"\\u09ac\\u09cd\\u09af\\u0995\\u09cd\\u09a4\\u09bf\\u0997\\u09a4 \\u09a4\\u09a5\\u09cd\\u09af\",\"en\":\"Personal information\",\"fields\":[{\"key\":\"full_name\",\"site\":\"Full Name (as per NID\\/BRC)\",\"profile\":\"name_en\",\"format\":\"upper\",\"required\":true,\"help\":{\"bn\":\"\\u098f\\u09a8\\u0986\\u0987\\u09a1\\u09bf\\/\\u099c\\u09a8\\u09cd\\u09ae \\u09a8\\u09bf\\u09ac\\u09a8\\u09cd\\u09a7\\u09a8\\u09c7 \\u09af\\u09c7\\u09ae\\u09a8 \\u09ac\\u09be\\u09a8\\u09be\\u09a8, \\u09a0\\u09bf\\u0995 \\u09a4\\u09c7\\u09ae\\u09a8\\u0964\",\"en\":\"Spelled exactly as on your NID or birth registration.\"}},{\"key\":\"given_name\",\"site\":\"First Name (Given Name)\",\"profile\":\"given_name_en\",\"format\":\"upper\",\"required\":true},{\"key\":\"surname\",\"site\":\"Last Name (Surname)\",\"profile\":\"surname_en\",\"format\":\"upper\",\"required\":true},{\"key\":\"gender\",\"site\":\"Gender\",\"profile\":\"gender\",\"format\":\"option_en\",\"required\":true},{\"key\":\"date_of_birth\",\"site\":\"Date of Birth\",\"profile\":\"date_of_birth\",\"format\":\"date_dmy\",\"required\":true},{\"key\":\"district_of_birth\",\"site\":\"District of Birth\",\"profile\":\"district_of_birth\",\"format\":\"as_is\",\"required\":true},{\"key\":\"marital_status\",\"site\":\"Marital Status\",\"profile\":\"marital_status\",\"format\":\"option_en\",\"required\":true},{\"key\":\"profession\",\"site\":\"Profession\",\"profile\":\"profession\",\"format\":\"as_is\",\"required\":true},{\"key\":\"religion\",\"site\":\"Religion\",\"profile\":\"religion\",\"format\":\"option_en\",\"required\":false},{\"key\":\"mobile\",\"site\":\"Mobile Number\",\"profile\":\"account_mobile\",\"format\":\"digits\",\"required\":true,\"bn\":\"\\u09ae\\u09cb\\u09ac\\u09be\\u0987\\u09b2 \\u09a8\\u09ae\\u09cd\\u09ac\\u09b0\",\"en\":\"Mobile number\"},{\"key\":\"email\",\"site\":\"Email\",\"profile\":\"email\",\"format\":\"as_is\",\"required\":false}]},{\"key\":\"present_address\",\"bn\":\"\\u09ac\\u09b0\\u09cd\\u09a4\\u09ae\\u09be\\u09a8 \\u09a0\\u09bf\\u0995\\u09be\\u09a8\\u09be\",\"en\":\"Present address\",\"fields\":[{\"key\":\"present_district\",\"site\":\"District\",\"profile\":\"present_district\",\"format\":\"as_is\",\"required\":true},{\"key\":\"present_police_station\",\"site\":\"Police Station\",\"profile\":\"present_police_station\",\"format\":\"as_is\",\"required\":true},{\"key\":\"present_post_office\",\"site\":\"Post Office\",\"profile\":\"present_post_office\",\"format\":\"as_is\",\"required\":true},{\"key\":\"present_post_code\",\"site\":\"Post Code\",\"profile\":\"present_post_code\",\"format\":\"digits\",\"required\":true},{\"key\":\"present_village_road\",\"site\":\"Village \\/ House \\/ Road\",\"profile\":\"present_village_road\",\"format\":\"as_is\",\"required\":true}]},{\"key\":\"permanent_address\",\"bn\":\"\\u09b8\\u09cd\\u09a5\\u09be\\u09af\\u09bc\\u09c0 \\u09a0\\u09bf\\u0995\\u09be\\u09a8\\u09be\",\"en\":\"Permanent address\",\"fields\":[{\"key\":\"permanent_district\",\"site\":\"District\",\"profile\":\"permanent_district\",\"format\":\"as_is\",\"required\":true},{\"key\":\"permanent_police_station\",\"site\":\"Police Station\",\"profile\":\"permanent_police_station\",\"format\":\"as_is\",\"required\":true},{\"key\":\"permanent_post_office\",\"site\":\"Post Office\",\"profile\":\"permanent_post_office\",\"format\":\"as_is\",\"required\":true},{\"key\":\"permanent_post_code\",\"site\":\"Post Code\",\"profile\":\"permanent_post_code\",\"format\":\"digits\",\"required\":true},{\"key\":\"permanent_village_road\",\"site\":\"Village \\/ House \\/ Road\",\"profile\":\"permanent_village_road\",\"format\":\"as_is\",\"required\":true}]},{\"key\":\"id_documents\",\"bn\":\"\\u09aa\\u09b0\\u09bf\\u099a\\u09af\\u09bc\\u09aa\\u09a4\\u09cd\\u09b0\",\"en\":\"ID documents\",\"fields\":[{\"key\":\"nid\",\"site\":\"National ID No.\",\"profile\":\"nid_number\",\"format\":\"digits\",\"required\":false,\"help\":{\"bn\":\"\\u098f\\u09a8\\u0986\\u0987\\u09a1\\u09bf \\u09a8\\u09be \\u09a5\\u09be\\u0995\\u09b2\\u09c7 \\u099c\\u09a8\\u09cd\\u09ae \\u09a8\\u09bf\\u09ac\\u09a8\\u09cd\\u09a7\\u09a8 \\u09a8\\u09ae\\u09cd\\u09ac\\u09b0 \\u09a6\\u09bf\\u09a8\\u0964\",\"en\":\"If you have no NID, give your birth registration number.\"}},{\"key\":\"brc\",\"site\":\"Birth Registration No.\",\"profile\":\"birth_reg_number\",\"format\":\"digits\",\"required\":false},{\"key\":\"previous_passport\",\"site\":\"Previous Passport No.\",\"profile\":\"passport_number\",\"format\":\"upper\",\"required\":false,\"help\":{\"bn\":\"\\u09a8\\u09ac\\u09be\\u09af\\u09bc\\u09a8\\u09c7\\u09b0 \\u0995\\u09cd\\u09b7\\u09c7\\u09a4\\u09cd\\u09b0\\u09c7 \\u0986\\u0997\\u09c7\\u09b0 \\u09aa\\u09be\\u09b8\\u09aa\\u09cb\\u09b0\\u09cd\\u099f\\u09c7\\u09b0 \\u09a8\\u09ae\\u09cd\\u09ac\\u09b0\\u0964\",\"en\":\"For a renewal: your old passport number.\"}}]},{\"key\":\"parents\",\"bn\":\"\\u09aa\\u09bf\\u09a4\\u09be-\\u09ae\\u09be\\u09a4\\u09be\\u09b0 \\u09a4\\u09a5\\u09cd\\u09af\",\"en\":\"Parents\' information\",\"fields\":[{\"key\":\"father_name\",\"site\":\"Father\'s Name\",\"profile\":\"father_name_en\",\"format\":\"upper\",\"required\":true},{\"key\":\"father_profession\",\"site\":\"Father\'s Profession\",\"profile\":\"father_profession\",\"format\":\"as_is\",\"required\":true},{\"key\":\"father_nid\",\"site\":\"Father\'s NID No.\",\"profile\":\"father_nid_number\",\"format\":\"digits\",\"required\":false},{\"key\":\"mother_name\",\"site\":\"Mother\'s Name\",\"profile\":\"mother_name_en\",\"format\":\"upper\",\"required\":true},{\"key\":\"mother_profession\",\"site\":\"Mother\'s Profession\",\"profile\":\"mother_profession\",\"format\":\"as_is\",\"required\":true},{\"key\":\"mother_nid\",\"site\":\"Mother\'s NID No.\",\"profile\":\"mother_nid_number\",\"format\":\"digits\",\"required\":false}]},{\"key\":\"spouse\",\"bn\":\"\\u09b8\\u09cd\\u09ac\\u09be\\u09ae\\u09c0\\/\\u09b8\\u09cd\\u09a4\\u09cd\\u09b0\\u09c0\\u09b0 \\u09a4\\u09a5\\u09cd\\u09af\",\"en\":\"Spouse\'s information\",\"fields\":[{\"key\":\"spouse_name\",\"site\":\"Spouse\'s Name\",\"profile\":\"spouse_name_en\",\"format\":\"upper\",\"required\":false,\"help\":{\"bn\":\"\\u09b6\\u09c1\\u09a7\\u09c1 \\u09ac\\u09bf\\u09ac\\u09be\\u09b9\\u09bf\\u09a4 \\u09b9\\u09b2\\u09c7\\u0964\",\"en\":\"Only if married.\"}}]},{\"key\":\"emergency\",\"bn\":\"\\u099c\\u09b0\\u09c1\\u09b0\\u09bf \\u09af\\u09cb\\u0997\\u09be\\u09af\\u09cb\\u0997\",\"en\":\"Emergency contact\",\"fields\":[{\"key\":\"emergency_name\",\"site\":\"Name\",\"profile\":\"emergency_contact_name\",\"format\":\"upper\",\"required\":true},{\"key\":\"emergency_relation\",\"site\":\"Relationship\",\"profile\":\"emergency_contact_relation\",\"format\":\"as_is\",\"required\":true},{\"key\":\"emergency_mobile\",\"site\":\"Mobile Number\",\"profile\":\"emergency_contact_mobile\",\"format\":\"digits\",\"required\":true},{\"key\":\"emergency_address\",\"site\":\"Address\",\"profile\":\"emergency_contact_address\",\"format\":\"as_is\",\"required\":true}]}]',1,NULL,'2026-10-23 10:11:44','2026-10-23 10:11:44');
/*!40000 ALTER TABLE `form_templates` ENABLE KEYS */;
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
-- Table structure for table `job_kits`
--

DROP TABLE IF EXISTS `job_kits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_kits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `advert` text NOT NULL,
  `result` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ready',
  `funded_by` varchar(10) NOT NULL DEFAULT 'free',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_kits_user_id_foreign` (`user_id`),
  CONSTRAINT `job_kits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_kits`
--

LOCK TABLES `job_kits` WRITE;
/*!40000 ALTER TABLE `job_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_kits` ENABLE KEYS */;
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
-- Table structure for table `known_devices`
--

DROP TABLE IF EXISTS `known_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `known_devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `known_devices_token_hash_unique` (`token_hash`),
  KEY `known_devices_user_id_foreign` (`user_id`),
  CONSTRAINT `known_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `known_devices`
--

LOCK TABLES `known_devices` WRITE;
/*!40000 ALTER TABLE `known_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `known_devices` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_23_000001_create_wallet_tables',1),(5,'2026_10_23_000002_create_form_tables',1),(6,'2026_10_24_000001_create_mvp_completion_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_codes`
--

DROP TABLE IF EXISTS `otp_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `consumed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otp_codes_mobile_index` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_codes`
--

LOCK TABLES `otp_codes` WRITE;
/*!40000 ALTER TABLE `otp_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product` varchar(30) NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'BDT',
  `gateway` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'initiated',
  `invoice` varchar(40) NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `trx_id` varchar(255) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_invoice_unique` (`invoice`),
  UNIQUE KEY `payments_payment_id_unique` (`payment_id`),
  KEY `payments_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_fields`
--

DROP TABLE IF EXISTS `profile_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `key` varchar(60) NOT NULL,
  `value` text NOT NULL,
  `source_document_id` bigint(20) unsigned DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_fields_user_id_key_unique` (`user_id`,`key`),
  KEY `profile_fields_source_document_id_foreign` (`source_document_id`),
  CONSTRAINT `profile_fields_source_document_id_foreign` FOREIGN KEY (`source_document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `profile_fields_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_fields`
--

LOCK TABLES `profile_fields` WRITE;
/*!40000 ALTER TABLE `profile_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_fields` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_messages`
--

DROP TABLE IF EXISTS `ticket_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `author_id` bigint(20) unsigned DEFAULT NULL,
  `from_staff` tinyint(1) NOT NULL DEFAULT 0,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_messages_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_messages_user_id_foreign` (`user_id`),
  KEY `ticket_messages_author_id_foreign` (`author_id`),
  CONSTRAINT `ticket_messages_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_messages`
--

LOCK TABLES `ticket_messages` WRITE;
/*!40000 ALTER TABLE `ticket_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tickets_user_id_foreign` (`user_id`),
  KEY `tickets_status_updated_at_index` (`status`,`updated_at`),
  CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `data_key` text NOT NULL,
  `plan` varchar(20) NOT NULL DEFAULT 'free',
  `locale` varchar(2) NOT NULL DEFAULT 'bn',
  `pin_failed_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `pin_locked_until` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `premium_until` timestamp NULL DEFAULT NULL,
  `form_credits` int(10) unsigned NOT NULL DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_mobile_unique` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
