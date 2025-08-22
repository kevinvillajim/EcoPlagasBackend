-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: ecoplagasbackend
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_activation_tokens`
--

DROP TABLE IF EXISTS `account_activation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_activation_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_activation_tokens_token_unique` (`token`),
  KEY `account_activation_tokens_user_id_foreign` (`user_id`),
  KEY `account_activation_tokens_token_index` (`token`),
  KEY `account_activation_tokens_expires_at_index` (`expires_at`),
  CONSTRAINT `account_activation_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_activation_tokens`
--

LOCK TABLES `account_activation_tokens` WRITE;
/*!40000 ALTER TABLE `account_activation_tokens` DISABLE KEYS */;
INSERT INTO `account_activation_tokens` VALUES (13,15,'rFuURcA9hvfU84gruXwS2Y7O2vuvhPCSmGZYCuw4luerGPkxQ1DC2Kc3RmdENUks','2025-08-21 17:39:22',1,'2025-08-21 17:18:09','2025-08-21 17:39:22'),(14,20,'R8VVf5YzDe84MeQfohSFctcgR8W0lca9hwtCffokV4wFD97330N4mvNDZHC9HVA4','2025-08-22 03:21:39',1,'2025-08-22 03:18:18','2025-08-22 03:21:39'),(15,21,'SXnN5rn17KEnuJSZ70yRzWpvHUC2HIFxRHNvDgQ011H47wJ8dSe7Km96BFjlw8i2','2025-08-23 03:19:38',0,'2025-08-22 03:19:38','2025-08-22 03:19:38');
/*!40000 ALTER TABLE `account_activation_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_settings`
--

DROP TABLE IF EXISTS `admin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_settings`
--

LOCK TABLES `admin_settings` WRITE;
/*!40000 ALTER TABLE `admin_settings` DISABLE KEYS */;
INSERT INTO `admin_settings` VALUES (1,'business_hours','{\"monday\":{\"open\":\"08:00\",\"close\":\"18:00\",\"isOpen\":true},\"tuesday\":{\"open\":\"08:00\",\"close\":\"18:00\",\"isOpen\":true},\"wednesday\":{\"open\":\"08:00\",\"close\":\"18:00\",\"isOpen\":true},\"thursday\":{\"open\":\"08:00\",\"close\":\"18:00\",\"isOpen\":true},\"friday\":{\"open\":\"08:00\",\"close\":\"21:00\",\"isOpen\":true},\"saturday\":{\"open\":\"08:00\",\"close\":\"21:00\",\"isOpen\":true},\"sunday\":{\"open\":\"08:00\",\"close\":\"12:00\",\"isOpen\":true}}','2025-08-21 04:54:59','2025-08-22 07:47:25'),(2,'service_settings','{\"defaultServiceDuration\":120,\"bufferTimeBetweenServices\":30,\"minimumAdvanceDays\":1,\"enableAdvanceBooking\":false,\"allowWeekendBooking\":true}','2025-08-21 04:54:59','2025-08-21 08:28:32'),(3,'pricing_settings','{\"currency\":\"USD\",\"baseServicePrice\":75,\"includeTax\":true,\"taxRate\":0,\"showPrices\":true,\"emergencyServiceSurcharge\":50,\"weekendSurcharge\":25,\"servicePrices\":{\"residential\":{\"min\":75,\"max\":250,\"enabled\":true},\"commercial\":{\"min\":100,\"max\":500,\"enabled\":true},\"industrial\":{\"min\":200,\"max\":1000,\"enabled\":true},\"emergency\":{\"min\":80,\"max\":300,\"enabled\":true}}}','2025-08-21 04:54:59','2025-08-22 03:46:57'),(4,'notification_settings','{\"emailNotifications\":true,\"clientReminders\":true,\"adminAlerts\":true,\"reminderHours\":24,\"followUpDays\":7}','2025-08-21 04:54:59','2025-08-21 04:54:59');
/*!40000 ALTER TABLE `admin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `certificate_number` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `valid_until` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` enum('pending','valid','expired','revoked') NOT NULL DEFAULT 'pending',
  `issued_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_ruc` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `treated_area` varchar(255) DEFAULT NULL,
  `desinsectacion` tinyint(1) NOT NULL DEFAULT 0,
  `desinfeccion` tinyint(1) NOT NULL DEFAULT 0,
  `desratizacion` tinyint(1) NOT NULL DEFAULT 0,
  `otro_servicio` tinyint(1) NOT NULL DEFAULT 0,
  `producto_desinsectacion` varchar(255) DEFAULT NULL,
  `categoria_desinsectacion` varchar(255) DEFAULT NULL,
  `registro_desinsectacion` varchar(255) DEFAULT NULL,
  `producto_desinfeccion` varchar(255) DEFAULT NULL,
  `categoria_desinfeccion` varchar(255) DEFAULT NULL,
  `registro_desinfeccion` varchar(255) DEFAULT NULL,
  `producto_desratizacion` varchar(255) DEFAULT NULL,
  `categoria_desratizacion` varchar(255) DEFAULT NULL,
  `registro_desratizacion` varchar(255) DEFAULT NULL,
  `producto_otro` varchar(255) DEFAULT NULL,
  `categoria_otro` varchar(255) DEFAULT NULL,
  `registro_otro` varchar(255) DEFAULT NULL,
  `service_description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificates_certificate_number_unique` (`certificate_number`),
  KEY `certificates_service_id_foreign` (`service_id`),
  KEY `certificates_issued_by_foreign` (`issued_by`),
  KEY `certificates_user_id_status_index` (`user_id`,`status`),
  KEY `certificates_certificate_number_index` (`certificate_number`),
  KEY `certificates_valid_until_index` (`valid_until`),
  CONSTRAINT `certificates_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (3,15,38,'CERT-XW5FSQAV-2025',NULL,NULL,'2025-08-21','2025-08-22','pest_control','valid',1,'2025-08-22 01:29:29','2025-08-22 01:29:29','Kevin Villacreses','1720598877','Ferroviaria, Quito','Quito','0963368896','Casa',1,0,0,0,'Nada','si','no',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Servicio programado desde la aplicación web'),(4,20,39,'CERT-S5DLMY8E-2025',NULL,NULL,'2025-08-21','2025-11-22','pest_control','valid',1,'2025-08-22 03:30:01','2025-08-22 03:30:01','Efraín Villacreses','17101174001','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle','Quito','09995031066','Casa',1,0,0,0,'DELTAMETRINA','JASKDJLASD','12345A',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Solucion Integrande control de Plagas'),(5,20,40,'CERT-XCCEUGHY-2025',NULL,NULL,'2025-08-21','2025-11-22','pest_control','valid',1,'2025-08-22 04:00:59','2025-08-22 04:00:59','Efraín Villacreses','1720598877','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','Quito','09995031066',NULL,0,1,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Servicio programado desde la aplicación web');
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `status` enum('open','closed','pending') NOT NULL DEFAULT 'open',
  `last_activity` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chats_client_id_index` (`client_id`),
  KEY `chats_status_index` (`status`),
  CONSTRAINT `chats_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chats`
--

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
INSERT INTO `chats` VALUES (1,15,'closed','2025-08-22 01:11:00','2025-08-21 23:58:15','2025-08-22 01:11:12'),(2,17,'open','2025-08-22 00:32:30','2025-08-22 00:22:16','2025-08-22 00:32:30'),(3,18,'open','2025-08-22 00:32:25','2025-08-22 00:22:16','2025-08-22 00:32:25'),(4,19,'open','2025-08-22 00:22:16','2025-08-22 00:22:16','2025-08-22 00:22:16'),(5,20,'open','2025-08-22 04:02:15','2025-08-22 03:57:54','2025-08-22 04:02:15');
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `video_url` text DEFAULT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_is_active_order_index_index` (`is_active`),
  KEY `gallery_category_index` (`category`),
  KEY `gallery_featured_is_active_index` (`featured`,`is_active`),
  KEY `gallery_media_type_index` (`media_type`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery`
--

LOCK TABLES `gallery` WRITE;
/*!40000 ALTER TABLE `gallery` DISABLE KEYS */;
INSERT INTO `gallery` VALUES (1,'Control de Roedores - Restaurante Centro','Exitoso tratamiento integral contra roedores en importante restaurante del centro de Guayaquil. Eliminación completa en 48 horas con productos eco-amigables.','./servicios1.jpg',NULL,'image','control_roedores',1,1,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(2,'Fumigación Residencial - Casa Familiar','Tratamiento completo de fumigación en vivienda familiar. Control efectivo de cucarachas y hormigas con total seguridad para niños y mascotas.','./servicios2.jpg',NULL,'image','fumigacion_residencial',1,1,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(3,'Control de Hormigas - Jardín Residencial','Tratamiento especializado para eliminación de colonias de hormigas en jardín y áreas exteriores. Resultados duraderos sin dañar las plantas.','./servicios3.jpg',NULL,'image','control_hormigas',1,0,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(4,'Desinfección de Oficinas','Servicio de desinfección completa para oficinas corporativas. Eliminación de bacterias, virus y control preventivo de plagas.','./servicios4.jpg',NULL,'image','desinfeccion',1,1,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(5,'Control de Voladores - Hotel Boutique','Eliminación de insectos voladores en hotel boutique. Tratamiento discreto que no afecta la experiencia de los huéspedes.','./servicios5.jpg',NULL,'image','control_voladores',1,0,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(6,'Fumigación Comercial - Centro Comercial','Tratamiento integral en centro comercial de gran tamaño. Control de múltiples tipos de plagas con cronograma adaptado al horario comercial.','./clientes1.jpg',NULL,'image','fumigacion_comercial',1,1,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(7,'Control de Roedores - Almacén Industrial','Sistema de control de roedores en almacén de alimentos. Implementación de programa de monitoreo continuo y control sanitario.','./clientes2.jpg',NULL,'image','control_roedores',1,0,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(8,'Desinfección General - Clínica Médica','Desinfección especializada en clínica médica. Protocolos hospitalarios con productos certificados para área de salud.','./why2.jpg',NULL,'image','desinfeccion',1,1,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(9,'Control de Hormigas - Complejo Residencial','Tratamiento en complejo residencial de 200 unidades. Plan integral con garantía extendida y seguimiento mensual.','./why3.jpg',NULL,'image','control_hormigas',1,0,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(10,'Fumigación Residencial - Casa de Campo','Tratamiento especializado en casa de campo con jardines extensos. Control ecológico respetando el ecosistema natural.','./why4.jpg',NULL,'image','fumigacion_residencial',1,0,'2025-08-20 17:11:06','2025-08-20 17:11:06'),(11,'Desifeccion','Perfecto todo bien','/storage/gallery/1755826247_68a7c8473f01e.png',NULL,'image','fumigacion_comercial',1,0,'2025-08-22 01:30:47','2025-08-22 01:30:47'),(12,'Control de Plagas en Restaurante','Servicio de control en Infestación de cucarachas en el norte de Quito','/storage/gallery/1755833878_68a7e6167c061.mp4',NULL,'video','fumigacion_comercial',1,1,'2025-08-22 03:37:58','2025-08-22 03:38:27');
/*!40000 ALTER TABLE `gallery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_media`
--

DROP TABLE IF EXISTS `gallery_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gallery_id` bigint(20) unsigned NOT NULL,
  `media_url` text NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `order_index` int(11) NOT NULL DEFAULT 0,
  `is_thumbnail` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_media_gallery_id_order_index_index` (`gallery_id`,`order_index`),
  CONSTRAINT `gallery_media_gallery_id_foreign` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_media`
--

LOCK TABLES `gallery_media` WRITE;
/*!40000 ALTER TABLE `gallery_media` DISABLE KEYS */;
INSERT INTO `gallery_media` VALUES (1,11,'/storage/gallery/1755826247_68a7c8473f01e.png','image',0,0,'2025-08-22 01:30:47','2025-08-22 01:30:47'),(2,12,'/storage/gallery/1755833878_68a7e6167c061.mp4','video',0,0,'2025-08-22 03:37:58','2025-08-22 03:37:58');
/*!40000 ALTER TABLE `gallery_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (12,'emails','{\"uuid\":\"d8b20f8c-ab7d-4709-9458-951bd5df66a1\",\"displayName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"command\":\"O:45:\\\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\\\":2:{s:14:\\\"\\u0000*\\u0000certificate\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Certificate\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:2:{i:0;s:4:\\\"user\\\";i:1;s:7:\\\"service\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\"},\"createdAt\":1755778275,\"delay\":null}',0,NULL,1755778275,1755778275),(13,'emails','{\"uuid\":\"3179c40b-499e-4078-aa3d-62362efaadac\",\"displayName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"command\":\"O:45:\\\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\\\":2:{s:14:\\\"\\u0000*\\u0000certificate\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Certificate\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:2:{i:0;s:4:\\\"user\\\";i:1;s:7:\\\"service\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\"},\"createdAt\":1755778300,\"delay\":null}',0,NULL,1755778300,1755778300),(14,'emails','{\"uuid\":\"7b5cf960-ed3b-440e-88fe-2a44a25c29fc\",\"displayName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"command\":\"O:45:\\\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\\\":2:{s:14:\\\"\\u0000*\\u0000certificate\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Certificate\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:2:{i:0;s:4:\\\"user\\\";i:1;s:7:\\\"service\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\"},\"createdAt\":1755778448,\"delay\":null}',0,NULL,1755778448,1755778448),(15,'emails','{\"uuid\":\"66fdcc21-ce32-47e1-8bcd-2123c930f8c5\",\"displayName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\",\"command\":\"O:45:\\\"App\\\\Jobs\\\\SendCertificateExpiryNotificationJob\\\":2:{s:14:\\\"\\u0000*\\u0000certificate\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\Certificate\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:2:{i:0;s:4:\\\"user\\\";i:1;s:7:\\\"service\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\"},\"createdAt\":1755778491,\"delay\":null}',0,NULL,1755778491,1755778491);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `type` enum('text','file','image') NOT NULL DEFAULT 'text',
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_chat_id_created_at_index` (`chat_id`,`created_at`),
  KEY `messages_sender_id_index` (`sender_id`),
  KEY `messages_read_index` (`read`),
  CONSTRAINT `messages_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,15,'hola','text',1,'2025-08-22 00:00:53','2025-08-22 00:01:03'),(2,1,1,'hola que tal como te ayudamos','text',1,'2025-08-22 00:01:23','2025-08-22 00:19:36'),(3,1,15,'hola','text',1,'2025-08-22 00:20:15','2025-08-22 00:20:37'),(4,1,1,'hola que tal','text',1,'2025-08-22 00:20:31','2025-08-22 00:20:37'),(5,1,1,'no','text',1,'2025-08-22 00:20:40','2025-08-22 00:25:23'),(6,2,17,'Hola, necesito ayuda con una fumigación','text',1,'2025-08-22 00:22:16','2025-08-22 00:25:26'),(7,3,18,'Buenos días, tengo problemas con hormigas','text',1,'2025-08-22 00:22:16','2025-08-22 00:25:28'),(8,4,19,'Quisiera programar un servicio','text',1,'2025-08-22 00:22:16','2025-08-22 00:25:29'),(9,1,15,'hola','text',1,'2025-08-22 00:24:52','2025-08-22 00:25:23'),(10,1,1,'hola','text',1,'2025-08-22 00:25:16','2025-08-22 00:25:23'),(11,2,1,'hola','text',1,'2025-08-22 00:26:06','2025-08-22 00:32:29'),(12,3,1,'juan','text',1,'2025-08-22 00:26:11','2025-08-22 00:27:17'),(13,3,1,'hola','text',1,'2025-08-22 00:32:25','2025-08-22 01:09:28'),(14,2,1,'si','text',1,'2025-08-22 00:32:30','2025-08-22 01:05:08'),(15,1,1,'vale??','text',1,'2025-08-22 00:38:07','2025-08-22 01:03:15'),(16,1,15,'no?','text',1,'2025-08-22 00:38:27','2025-08-22 01:03:15'),(17,1,1,'hola','text',1,'2025-08-22 00:40:14','2025-08-22 01:03:15'),(18,1,1,'hola','text',1,'2025-08-22 00:40:33','2025-08-22 01:03:15'),(19,1,15,'Hola','text',1,'2025-08-22 00:40:39','2025-08-22 01:03:15'),(20,1,1,'finciona?','text',1,'2025-08-22 00:47:27','2025-08-22 01:03:15'),(21,1,15,'si perfectamente','text',1,'2025-08-22 00:47:35','2025-08-22 01:03:15'),(22,1,1,'gracias','text',1,'2025-08-22 00:47:42','2025-08-22 01:03:15'),(23,1,1,'pero el polling parece muy cercano no?','text',1,'2025-08-22 00:48:27','2025-08-22 01:03:15'),(24,1,15,'osea si pero que le vamos a hacer?','text',1,'2025-08-22 00:48:44','2025-08-22 01:03:15'),(25,1,1,'jeje rayos','text',1,'2025-08-22 00:48:55','2025-08-22 01:03:15'),(26,1,15,'si lo lei','text',1,'2025-08-22 00:56:48','2025-08-22 01:03:15'),(27,1,1,'funciona','text',1,'2025-08-22 01:03:35','2025-08-22 01:03:37'),(28,1,1,'si','text',1,'2025-08-22 01:03:48','2025-08-22 01:04:14'),(29,1,15,'si','text',1,'2025-08-22 01:03:59','2025-08-22 01:04:14'),(30,1,1,'parece','text',1,'2025-08-22 01:04:19','2025-08-22 01:04:22'),(31,1,15,'hola','text',1,'2025-08-22 01:10:29','2025-08-22 01:10:48'),(32,1,1,'hola Kevin Que tal','text',1,'2025-08-22 01:11:00','2025-08-22 01:11:07'),(33,5,20,'HOLA TENGO UNA SEMANA Y AUN TENGO CUCARACHAS','text',1,'2025-08-22 03:57:54','2025-08-22 04:02:12'),(34,5,20,'QUE PUEDO HACER?','text',1,'2025-08-22 03:58:01','2025-08-22 04:02:12'),(35,5,20,'YA LE ENVIAMOS UN TECLICO','text',1,'2025-08-22 04:02:01','2025-08-22 04:02:12'),(36,5,1,'SI','text',1,'2025-08-22 04:02:15','2025-08-22 04:03:20');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_15_000001_update_users_table',1),(5,'2024_01_15_000002_create_services_table',1),(6,'2024_01_15_000003_create_certificates_table',1),(7,'2024_01_15_000004_create_notifications_table',1),(8,'2024_01_15_000005_create_reviews_table',1),(9,'2025_08_11_204811_create_personal_access_tokens_table',1),(10,'2025_08_11_213047_create_password_reset_tokens_table',1),(11,'2025_08_12_012756_add_moderation_fields_to_reviews_table',1),(12,'2025_08_14_053322_create_gallery_table',1),(13,'2025_08_14_060107_modify_gallery_table_featured_only',1),(14,'2025_08_14_112000_add_video_support_gallery',1),(15,'2025_08_14_113000_create_gallery_media_table',1),(16,'2025_08_15_052352_update_certificates_table_allow_nullable_files',1),(18,'2025_08_15_060652_add_certificate_fields_to_certificates_table',2),(19,'2025_08_20_122005_add_missing_columns_to_gallery_table',3),(20,'2025_08_20_185257_add_profile_fields_to_users_table',3),(21,'2025_08_20_205643_add_city_to_users_table',4),(22,'2025_08_20_212039_create_service_settings_table',5),(23,'2025_08_20_213313_rename_duration_hours_to_duration_minutes',6),(26,'2025_08_20_220853_create_admin_settings_table',7),(27,'2025_08_21_004137_add_enable_advance_booking_to_admin_settings',8),(28,'2025_08_21_005918_add_service_prices_to_admin_settings',9),(30,'2025_08_21_015958_add_minimum_advance_days_to_admin_settings',10),(31,'2025_08_21_052009_add_ruc_to_users_table',11),(32,'2025_08_21_070307_modify_notifications_table_allow_null_user_id',12),(33,'2025_08_21_185543_create_chats_table',13),(34,'2025_08_21_185548_create_messages_table',13);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `notifications_type_index` (`type`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (3,1,'service_scheduled','Nuevo servicio programado','El cliente Kevin VIllacreses ha programado un servicio de residential para el 2025-08-21 a las 08:00 en Miguel Hermoso y Marco Kelly, Ferroviaria, Quito.','{\"service_id\":35,\"client_name\":\"Kevin VIllacreses\",\"client_email\":\"kevinvillajim@hotmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-21\",\"scheduled_time\":\"08:00\",\"address\":\"Miguel Hermoso y Marco Kelly, Ferroviaria, Quito\",\"is_admin_notification\":true}',NULL,NULL,'2025-08-21 09:25:52','2025-08-21 09:25:52'),(5,1,'service_scheduled','Nuevo servicio programado','El cliente Kevin VIllacreses ha programado un servicio de residential para el 2025-08-21 a las 10:30 en Miguel Hermoso y Marco Kelly, Ferroviaria, Quito.','{\"service_id\":36,\"client_name\":\"Kevin VIllacreses\",\"client_email\":\"kevinvillajim@hotmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-21\",\"scheduled_time\":\"10:30\",\"address\":\"Miguel Hermoso y Marco Kelly, Ferroviaria, Quito\",\"is_admin_notification\":true}','2025-08-22 01:19:04',NULL,'2025-08-21 09:39:17','2025-08-22 01:19:04'),(7,1,'service_scheduled','Nuevo servicio programado','El cliente Kevin VIllacreses ha programado un servicio de residential para el 2025-08-21 a las 13:00 en Ferroviaria, quito.','{\"service_id\":37,\"client_name\":\"Kevin VIllacreses\",\"client_email\":\"kevinvillajim@hotmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-21\",\"scheduled_time\":\"13:00\",\"address\":\"Ferroviaria, quito\",\"is_admin_notification\":true}','2025-08-22 01:26:10',NULL,'2025-08-21 09:39:46','2025-08-22 01:26:10'),(16,NULL,'certificate_expiry_reminder','Certificado próximo a vencer','El certificado CERT-FCDWUFXQ-2025 del cliente Kevin VIllacreses vence el 28/08/2025','\"{\\\"certificate_id\\\":2,\\\"certificate_number\\\":\\\"CERT-FCDWUFXQ-2025\\\",\\\"client_name\\\":\\\"Kevin VIllacreses\\\",\\\"expiry_date\\\":\\\"2025-08-28\\\",\\\"days_until_expiry\\\":6.697608254513889}\"',NULL,NULL,'2025-08-21 12:15:26','2025-08-21 12:15:26'),(18,15,'service_scheduled','Servicio programado exitosamente','Tu servicio de residential ha sido programado para el 2025-08-21 a las 13:00. Te enviaremos actualizaciones sobre el estado de tu servicio.','{\"service_id\":38,\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-21\",\"scheduled_time\":\"13:00\",\"address\":\"Ferroviaria, Quito\"}','2025-08-22 01:26:20',NULL,'2025-08-21 17:48:06','2025-08-22 01:26:20'),(19,1,'service_scheduled','Nuevo servicio programado','El cliente Kevin Villacreses ha programado un servicio de residential para el 2025-08-21 a las 13:00 en Ferroviaria, Quito.','{\"service_id\":38,\"client_name\":\"Kevin Villacreses\",\"client_email\":\"kevinvillajim@hotmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-21\",\"scheduled_time\":\"13:00\",\"address\":\"Ferroviaria, Quito\",\"is_admin_notification\":true}','2025-08-22 01:26:13',NULL,'2025-08-21 17:48:06','2025-08-22 01:26:13'),(20,15,'certificate_generated','Certificado Generado','Tu certificado de control de plagas CERT-XW5FSQAV-2025 ha sido generado y enviado por correo electrónico. Es válido hasta el 22/08/2025.','{\"certificate_id\":3,\"certificate_number\":\"CERT-XW5FSQAV-2025\",\"valid_until\":\"2025-08-22T05:00:00.000000Z\",\"service_id\":38}','2025-08-22 01:29:42',NULL,'2025-08-22 01:29:30','2025-08-22 01:29:42'),(21,20,'certificate_generated','Certificado Generado','Tu certificado de control de plagas CERT-S5DLMY8E-2025 ha sido generado y enviado por correo electrónico. Es válido hasta el 22/11/2025.','{\"certificate_id\":4,\"certificate_number\":\"CERT-S5DLMY8E-2025\",\"valid_until\":\"2025-11-22T05:00:00.000000Z\",\"service_id\":39}','2025-08-22 03:42:49',NULL,'2025-08-22 03:30:03','2025-08-22 03:42:49'),(22,20,'service_scheduled','Servicio programado exitosamente','Tu servicio de residential ha sido programado para el 2025-08-22 a las 08:00. Te enviaremos actualizaciones sobre el estado de tu servicio.','{\"service_id\":40,\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"08:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\"}',NULL,NULL,'2025-08-22 03:53:20','2025-08-22 03:53:20'),(23,1,'service_scheduled','Nuevo servicio programado','El cliente Efraín Villacreses ha programado un servicio de residential para el 2025-08-22 a las 08:00 en Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito.','{\"service_id\":40,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"08:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\",\"is_admin_notification\":true}','2025-08-22 03:53:34',NULL,'2025-08-22 03:53:20','2025-08-22 03:53:34'),(24,20,'certificate_generated','Certificado Generado','Tu certificado de control de plagas CERT-XCCEUGHY-2025 ha sido generado y enviado por correo electrónico. Es válido hasta el 22/11/2025.','{\"certificate_id\":5,\"certificate_number\":\"CERT-XCCEUGHY-2025\",\"valid_until\":\"2025-11-22T05:00:00.000000Z\",\"service_id\":40}',NULL,NULL,'2025-08-22 04:01:01','2025-08-22 04:01:01'),(25,20,'service_scheduled','Servicio programado exitosamente','Tu servicio de residential ha sido programado para el 2025-08-22 a las 08:00. Te enviaremos actualizaciones sobre el estado de tu servicio.','{\"service_id\":41,\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"08:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\"}',NULL,NULL,'2025-08-22 08:35:14','2025-08-22 08:35:14'),(26,1,'service_scheduled','Nuevo servicio programado','El cliente Efraín Villacreses ha programado un servicio de residential para el 2025-08-22 a las 08:00 en Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito.','{\"service_id\":41,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"08:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\",\"is_admin_notification\":true}',NULL,NULL,'2025-08-22 08:35:14','2025-08-22 08:35:14'),(27,20,'service_scheduled','Solicitud de emergencia recibida','Tu solicitud de emergencia ha sido recibida. Nuestro equipo se contactará contigo en los próximos 15 minutos para coordinar la atención inmediata.','{\"service_id\":42,\"service_type\":\"emergency\",\"emergency_type\":\"immediate\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito - Urgente\"}',NULL,NULL,'2025-08-22 08:48:48','2025-08-22 08:48:48'),(28,1,'emergency','🚨 EMERGENCIA INMEDIATA - Acción requerida','ATENCIÓN INMEDIATA REQUERIDA: El cliente Efraín Villacreses necesita servicio de emergencia AHORA. Contactar inmediatamente al 09995031066. Dirección: Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito - Urgente. Problema: Tengo un raton en el baño y tengo que matarlo','{\"service_id\":42,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"client_phone\":\"09995031066\",\"service_type\":\"emergency\",\"emergency_type\":\"immediate\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito - Urgente\",\"description\":\"Tengo un raton en el ba\\u00f1o y tengo que matarlo\",\"is_admin_notification\":true,\"is_urgent\":true}',NULL,NULL,'2025-08-22 08:48:48','2025-08-22 08:48:48'),(29,20,'service_scheduled','Servicio de emergencia programado','Tu servicio de emergencia ha sido programado fuera del horario normal. Te contactaremos dentro de las próximas 2 horas para confirmar la cita.','{\"service_id\":43,\"service_type\":\"emergency\",\"emergency_type\":\"out_of_hours\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\"}',NULL,NULL,'2025-08-22 08:54:29','2025-08-22 08:54:29'),(30,1,'emergency','⚠️ Servicio de emergencia fuera de horario','El cliente Efraín Villacreses solicita servicio de emergencia fuera del horario normal. Contactar en las próximas 2 horas para coordinar cita.','{\"service_id\":43,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"client_phone\":\"09995031066\",\"service_type\":\"emergency\",\"emergency_type\":\"out_of_hours\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\",\"description\":\"Te espero\",\"is_admin_notification\":true,\"is_urgent\":false}',NULL,NULL,'2025-08-22 08:54:29','2025-08-22 08:54:29'),(31,20,'service_scheduled','Servicio programado exitosamente','Tu servicio de residential ha sido programado para el 2025-08-22 a las 13:00. Te enviaremos actualizaciones sobre el estado de tu servicio.','{\"service_id\":47,\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"13:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\"}','2025-08-22 09:47:34',NULL,'2025-08-22 09:47:10','2025-08-22 09:47:34'),(32,1,'service_scheduled','Nuevo servicio programado','El cliente Efraín Villacreses ha programado un servicio de residential para el 2025-08-22 a las 13:00 en Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito.','{\"service_id\":47,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"service_type\":\"residential\",\"scheduled_date\":\"2025-08-22\",\"scheduled_time\":\"13:00\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\",\"is_admin_notification\":true}',NULL,NULL,'2025-08-22 09:47:10','2025-08-22 09:47:10'),(33,20,'service_scheduled','Solicitud de emergencia recibida','Tu solicitud de emergencia ha sido recibida. Nuestro equipo se contactará contigo en los próximos 15 minutos para coordinar la atención inmediata.','{\"service_id\":48,\"service_type\":\"emergency\",\"emergency_type\":\"immediate\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\"}',NULL,NULL,'2025-08-22 09:48:20','2025-08-22 09:48:20'),(34,1,'emergency','🚨 EMERGENCIA INMEDIATA - Acción requerida','ATENCIÓN INMEDIATA REQUERIDA: El cliente Efraín Villacreses necesita servicio de emergencia AHORA. Contactar inmediatamente al 09995031066. Dirección: Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito. Problema: ok','{\"service_id\":48,\"client_name\":\"Efra\\u00edn Villacreses\",\"client_email\":\"efravillacreses@gmail.com\",\"client_phone\":\"09995031066\",\"service_type\":\"emergency\",\"emergency_type\":\"immediate\",\"address\":\"Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito\",\"description\":\"ok\",\"is_admin_notification\":true,\"is_urgent\":true}',NULL,NULL,'2025-08-22 09:48:20','2025-08-22 09:48:20');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (5,'App\\Models\\User',7,'auth_token','a969f42b55207fc037eceace2a0ef9a02ca51ceff013e0e7d27dc49cdc4abd11','[\"*\"]',NULL,NULL,'2025-08-21 00:19:22','2025-08-21 00:19:22'),(6,'App\\Models\\User',8,'auth_token','b724c4cc27141cf54d674bf26a2f5a809523e1e91d60428be6e86d48a8dd0f7b','[\"*\"]','2025-08-21 00:47:32',NULL,'2025-08-21 00:31:53','2025-08-21 00:47:32'),(10,'App\\Models\\User',12,'auth_token','bfdd374f350bb3d1faaafaae01ab5b2789491b5c7d9b68841bb67e21055847c7','[\"*\"]',NULL,NULL,'2025-08-21 01:08:46','2025-08-21 01:08:46'),(13,'App\\Models\\User',2,'auth_token','349d408a2622a2c493128349fb5d545bc2bba1cb9e516d137990d89cb0b45d17','[\"*\"]','2025-08-21 01:51:46',NULL,'2025-08-21 01:51:12','2025-08-21 01:51:46'),(16,'App\\Models\\User',2,'test','2f1099d05168f6f293e58790d7240be603d0b03d25d7d48c8f1d2b9a9191aee6','[\"*\"]',NULL,NULL,'2025-08-21 02:43:03','2025-08-21 02:43:03'),(17,'App\\Models\\User',2,'test','410de871f04176228d03c10ee3df8f06df93853d3ef383f2b72df1033e52210e','[\"*\"]','2025-08-21 03:17:05',NULL,'2025-08-21 02:51:28','2025-08-21 03:17:05'),(18,'App\\Models\\User',14,'auth_token','2c55ee94cf5203c1f6d52b7f75126394c944ccc76169d8f7bbb449b758e14f41','[\"*\"]','2025-08-21 16:29:42',NULL,'2025-08-21 03:02:58','2025-08-21 16:29:42'),(27,'App\\Models\\User',15,'auth_token','07bdec5313d16956fbaa5984473e89ae16c25aca3e9903df53935c94e21b8ca6','[\"*\"]','2025-08-22 01:31:14',NULL,'2025-08-22 01:16:19','2025-08-22 01:31:14');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text NOT NULL,
  `response_message` text DEFAULT NULL,
  `response_by` bigint(20) unsigned DEFAULT NULL,
  `response_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','rejected','auto_approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `is_auto_approved` tinyint(1) NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_response_by_foreign` (`response_by`),
  KEY `reviews_service_id_rating_index` (`service_id`,`rating`),
  KEY `reviews_is_public_index` (`is_public`),
  KEY `reviews_moderated_by_foreign` (`moderated_by`),
  KEY `reviews_status_index` (`status`),
  KEY `reviews_status_is_featured_index` (`status`,`is_featured`),
  KEY `reviews_verified_is_public_index` (`verified`,`is_public`),
  CONSTRAINT `reviews_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_response_by_foreign` FOREIGN KEY (`response_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,2,1,5,'Excelente servicio, muy profesionales y puntuales. El problema de cucarachas se solucionó completamente. El técnico fue muy educado y explicó todo el proceso detalladamente.',NULL,NULL,NULL,1,'auto_approved','2025-08-15 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-15 17:11:06',1,'Guayaquil - Norte',1,1,42),(2,2,2,5,'Contratamos sus servicios para nuestro restaurante y quedamos muy satisfechos. Trabajo discreto, efectivo y con todas las certificaciones requeridas. Personal muy profesional.',NULL,NULL,NULL,1,'auto_approved','2025-08-13 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-13 17:11:06',1,'Samborondón',1,1,38),(3,2,3,5,'Increíble resultado! Tenía hormigas por toda la casa y después del tratamiento no he vuelto a ver ni una. Productos seguros para mis hijos y mascotas. Recomendado 100%.',NULL,NULL,NULL,1,'auto_approved','2025-08-10 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-10 17:11:06',1,'Vía a la Costa',1,1,35),(4,2,4,5,'Servicio de desinfección completo para mi oficina. Personal capacitado, productos certificados y resultados garantizados. Totalmente recomendados.',NULL,NULL,NULL,1,'auto_approved','2025-08-08 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-08 17:11:06',1,'Centro de Guayaquil',1,1,29),(5,2,5,5,'Tuve un problema serio con ratones en mi bodega. EcoPlagas resolvió el problema rápidamente y me dieron consejos para prevenir futuras infestaciones.',NULL,NULL,NULL,1,'auto_approved','2025-08-05 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-05 17:11:06',1,'Duran',1,0,31),(6,2,6,5,'Servicio excelente. Llegaron puntuales, trabajaron de manera muy limpia y ordenada. Los productos no tienen olores fuertes y son seguros.',NULL,NULL,NULL,1,'auto_approved','2025-08-02 17:11:06','2025-08-20 17:11:06',NULL,'2025-08-02 17:11:06',1,'Los Ceibos',1,0,27),(7,2,7,4,'Buen servicio en general. El problema se resolvió aunque tardaron un poco más de lo esperado. El personal fue amable y profesional.',NULL,NULL,NULL,1,'auto_approved','2025-07-31 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-31 17:11:06',1,'Kennedy Norte',1,0,18),(8,2,8,5,'Contraté el servicio para mi consultorio médico. Cumplieron con todas las normas de bioseguridad y me entregaron los certificados correspondientes.',NULL,NULL,NULL,1,'auto_approved','2025-07-26 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-26 17:11:06',1,'Urdesa',1,0,33),(9,2,9,5,'Las hormigas habían invadido mi jardín y cocina. Después del tratamiento, desaparecieron completamente. El técnico me explicó el proceso y fue muy amable.',NULL,NULL,NULL,1,'auto_approved','2025-07-21 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-21 17:11:06',1,'Alborada',1,0,25),(10,2,10,5,'Excelente servicio para mi hotel. Trabajo discreto, sin interrumpir las operaciones. Los huéspedes no se dieron cuenta del tratamiento.',NULL,NULL,NULL,1,'auto_approved','2025-07-16 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-16 17:11:06',1,'Malecón 2000',1,0,41),(11,2,11,4,'Buen servicio, resolvieron el problema de ratones en mi casa. El precio es justo y el personal muy educado. Solo me gustaría que dieran más seguimiento.',NULL,NULL,NULL,1,'auto_approved','2025-07-11 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-11 17:11:06',1,'Mapasingue',1,0,16),(12,2,12,5,'Servicio impecable para mi clínica dental. Cumplieron con todos los protocolos de bioseguridad. Personal muy capacitado y productos de alta calidad.',NULL,NULL,NULL,1,'auto_approved','2025-07-06 17:11:06','2025-08-20 17:11:06',NULL,'2025-07-06 17:11:06',1,'Ceibos Norte',1,0,30),(13,2,13,3,'El servicio fue bueno pero tardaron más de lo esperado en completar el trabajo. El resultado final fue satisfactorio.',NULL,NULL,NULL,1,'approved','2025-08-18 17:11:06','2025-08-22 03:31:31',1,'2025-08-22 03:31:31',0,'Bastión Popular',1,0,8),(14,2,14,2,'No estoy satisfecha con el resultado. El problema persistió después del servicio y tuve que llamar de nuevo.',NULL,NULL,NULL,1,'rejected','2025-08-17 17:11:06','2025-08-22 03:32:00',1,'2025-08-22 03:32:00',0,'Monte Sinaí',1,0,3),(15,2,15,1,'Muy mal servicio, no solucionaron el problema y fueron impuntuales. No recomiendo para nada.',NULL,NULL,NULL,1,'rejected','2025-08-16 17:11:06','2025-08-20 17:11:06',1,'2025-08-19 17:11:06',0,'Guasmo',1,0,0),(16,15,38,5,'Genial muy buena experiencia son unos profesionales completamente',NULL,NULL,NULL,1,'auto_approved','2025-08-22 01:27:00','2025-08-22 01:27:46',NULL,'2025-08-22 01:27:00',1,'Quito Ecuador',1,0,9);
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_settings`
--

DROP TABLE IF EXISTS `service_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_type` varchar(255) NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 2,
  `min_price` decimal(8,2) DEFAULT NULL,
  `max_price` decimal(8,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_settings_service_type_unique` (`service_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_settings`
--

LOCK TABLES `service_settings` WRITE;
/*!40000 ALTER TABLE `service_settings` DISABLE KEYS */;
INSERT INTO `service_settings` VALUES (1,'residential',180,60.00,150.00,'Control de Plagas Residencial',1,'2025-08-21 02:21:47','2025-08-21 02:52:52'),(2,'commercial',180,100.00,500.00,'Control de Plagas Comercial',1,'2025-08-21 02:21:47','2025-08-21 02:21:47'),(3,'industrial',240,200.00,1000.00,'Control de Plagas Industrial',1,'2025-08-21 02:21:47','2025-08-21 02:21:47'),(4,'emergency',60,80.00,300.00,'Servicio de Emergencia',1,'2025-08-21 02:21:47','2025-08-21 02:21:47');
/*!40000 ALTER TABLE `service_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `technician_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `address` text NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `next_service_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_user_id_status_index` (`user_id`,`status`),
  KEY `services_technician_id_scheduled_date_index` (`technician_id`,`scheduled_date`),
  KEY `services_status_index` (`status`),
  CONSTRAINT `services_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,2,1,'control_hormigas','Servicio de control hormigas realizado exitosamente.','Urdesa, Casa familiar','2025-07-05',NULL,'2025-08-09 12:11:06','completed',185.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(2,2,1,'control_hormigas','Servicio de control hormigas realizado exitosamente.','Los Ceibos, Urbanización privada','2025-08-10',NULL,'2025-07-04 12:11:06','completed',72.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(3,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Guayaquil - Norte, Av. Francisco de Orellana','2025-07-23',NULL,'2025-07-10 12:11:06','completed',295.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(4,2,1,'fumigacion_comercial','Servicio de fumigacion comercial realizado exitosamente.','Duran, Centro Comercial','2025-07-12',NULL,'2025-08-02 12:11:06','completed',103.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(5,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Malecón 2000, Hotel boutique','2025-08-06',NULL,'2025-07-19 12:11:06','completed',209.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(6,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Malecón 2000, Hotel boutique','2025-06-25',NULL,'2025-07-15 12:11:06','completed',64.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(7,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Alborada, Edificio comercial','2025-08-14',NULL,'2025-07-09 12:11:06','completed',241.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(8,2,1,'fumigacion_comercial','Servicio de fumigacion comercial realizado exitosamente.','Guayaquil - Norte, Av. Francisco de Orellana','2025-07-16',NULL,'2025-07-27 12:11:06','completed',289.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(9,2,1,'control_roedores','Servicio de control roedores realizado exitosamente.','Centro de Guayaquil, Av. 9 de Octubre','2025-07-24',NULL,'2025-07-10 12:11:06','completed',197.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(10,2,1,'fumigacion_comercial','Servicio de fumigacion comercial realizado exitosamente.','Los Ceibos, Urbanización privada','2025-08-12',NULL,'2025-07-24 12:11:06','completed',98.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(11,2,1,'fumigacion_residencial','Servicio de fumigacion residencial realizado exitosamente.','Kennedy Norte, Conjunto residencial','2025-08-17',NULL,'2025-07-09 12:11:06','completed',93.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(12,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Los Ceibos, Urbanización privada','2025-07-29',NULL,'2025-08-11 12:11:06','completed',118.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(13,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Vía a la Costa, Km 15','2025-08-16',NULL,'2025-08-11 12:11:06','completed',275.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(14,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Alborada, Edificio comercial','2025-08-03',NULL,'2025-08-08 12:11:06','completed',289.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(15,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Kennedy Norte, Conjunto residencial','2025-07-30',NULL,'2025-07-24 12:11:06','completed',76.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(16,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Guayaquil - Norte, Av. Francisco de Orellana','2025-07-18',NULL,'2025-08-13 12:11:06','completed',257.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(17,2,1,'control_voladores','Servicio de control voladores realizado exitosamente.','Samborondón, Vía Samborondón','2025-08-03',NULL,'2025-08-17 12:11:06','completed',232.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(18,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Guayaquil - Norte, Av. Francisco de Orellana','2025-07-16',NULL,'2025-08-15 12:11:06','completed',180.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(19,2,1,'control_roedores','Servicio de control roedores realizado exitosamente.','Centro de Guayaquil, Av. 9 de Octubre','2025-08-06',NULL,'2025-08-14 12:11:06','completed',172.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(20,2,1,'desinfeccion','Servicio de desinfeccion realizado exitosamente.','Urdesa, Casa familiar','2025-07-04',NULL,'2025-08-19 12:11:06','completed',69.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(21,2,1,'fumigacion_comercial','Servicio de fumigacion comercial realizado exitosamente.','Guayaquil - Norte, Av. Francisco de Orellana','2025-06-24',NULL,'2025-07-22 12:11:06','completed',175.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(22,2,1,'fumigacion_residencial','Servicio de fumigacion residencial realizado exitosamente.','Centro de Guayaquil, Av. 9 de Octubre','2025-07-23',NULL,'2025-07-31 12:11:06','completed',66.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(23,2,1,'control_hormigas','Servicio de control hormigas realizado exitosamente.','Alborada, Edificio comercial','2025-06-29',NULL,'2025-07-03 12:11:06','completed',179.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(24,2,1,'control_hormigas','Servicio de control hormigas realizado exitosamente.','Duran, Centro Comercial','2025-07-13',NULL,'2025-07-29 12:11:06','completed',260.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(25,2,1,'fumigacion_comercial','Servicio de fumigacion comercial realizado exitosamente.','Kennedy Norte, Conjunto residencial','2025-07-28',NULL,'2025-07-18 12:11:06','completed',289.00,'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',NULL,NULL,'2025-08-20 17:11:06','2025-08-20 17:11:06',NULL),(30,2,NULL,'residential','Test service for slot blocking','Test Address 123','2025-08-21','14:00:00',NULL,'scheduled',NULL,NULL,NULL,NULL,'2025-08-21 01:44:39','2025-08-21 05:14:51','2025-08-21 05:14:51'),(38,15,NULL,'residential','Servicio programado desde la aplicación web','Ferroviaria, Quito','2025-08-21','13:00:00','2025-08-22 01:19:57','completed',NULL,NULL,NULL,NULL,'2025-08-21 17:48:06','2025-08-22 01:19:57',NULL),(39,20,NULL,'residential','descripcion','direccion','2025-08-23','08:00:00','2025-08-21 22:25:02','completed',60.00,'llevar 5 cajas de monitoreo',NULL,NULL,'2025-08-22 03:23:30','2025-08-22 03:25:02',NULL),(40,20,NULL,'residential','Servicio programado desde la aplicación web','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','2025-08-25','08:00:00','2025-08-21 23:00:25','completed',80.00,NULL,NULL,NULL,'2025-08-22 03:53:20','2025-08-22 04:00:25',NULL),(41,20,NULL,'residential','Sin Problemas','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','2025-08-22','08:00:00',NULL,'scheduled',NULL,NULL,NULL,NULL,'2025-08-22 08:35:14','2025-08-22 08:35:14',NULL),(42,20,NULL,'emergency','Tengo un raton en el baño y tengo que matarlo','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito - Urgente','2025-08-22','03:48:00',NULL,'scheduled',NULL,'EMERGENCIA INMEDIATA - Atención inmediata solicitada - immediate',NULL,NULL,'2025-08-22 08:48:48','2025-08-22 08:48:48',NULL),(43,20,NULL,'emergency','Te espero','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','2025-08-23','06:00:00',NULL,'scheduled',NULL,'EMERGENCIA FUERA DE HORARIO - Cita fuera de horario - out_of_hours',NULL,NULL,'2025-08-22 08:54:29','2025-08-22 08:54:29',NULL),(44,21,NULL,'residential','Urgente','asdasdasd','2025-08-22','09:00:00',NULL,'scheduled',60.00,NULL,NULL,NULL,'2025-08-22 09:15:47','2025-08-22 09:15:47',NULL),(45,21,NULL,'residential','okasd','asdasdasd','2025-08-23','09:00:00',NULL,'scheduled',63.00,NULL,NULL,NULL,'2025-08-22 09:27:57','2025-08-22 09:27:57',NULL),(46,21,NULL,'residential','jkasdhasd','asdasd','2025-08-23','11:30:00',NULL,'scheduled',65.00,NULL,NULL,NULL,'2025-08-22 09:35:35','2025-08-22 09:35:35',NULL),(47,20,NULL,'residential','Servicio programado desde la aplicación web','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','2025-08-22','13:00:00',NULL,'scheduled',NULL,NULL,NULL,NULL,'2025-08-22 09:47:10','2025-08-22 09:47:10',NULL),(48,20,NULL,'emergency','ok','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle, Quito','2025-08-22','04:48:00',NULL,'scheduled',NULL,'EMERGENCIA INMEDIATA - Atención inmediata solicitada - immediate',NULL,NULL,'2025-08-22 09:48:20','2025-08-22 09:48:20',NULL);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
INSERT INTO `sessions` VALUES ('O77vnWngTl7PtJb87G3iqm7KZpQ2kC0MK4j4hmWA',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEFSVWQ3RnV2YnF1WllaellZbGloeEd5MmNXTVkzcGt3UkZqb1dqWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1755820862),('o7r8siJCCFvcvoPlyv1H7ECdtqCtaFWGdau5vpAj',NULL,'127.0.0.1','curl/8.2.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoib0RzTnRtS0ZjVHVFS3pTcEhZMWxwcE81eXlNb3JURmJuTHpVUkxQVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1755731834),('pLx5kjqsGIre2FoJfJavSlqlpMs9Y22vaopdKQHk',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRzRlRmR6MkJ0TlBDd3R6M0VFREZic2V4aVBVaEp5ZksxT29pRkdIYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1755820863),('tiB0rFcbC6clFrCRlaRSOHFguoiHLBsH6oThvNxv',NULL,'127.0.0.1','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic1BZclBGVmZGa2UxNmIzTWk1VXNyOXhJS0VsVHhlSkdWTk9vcmxiQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1755820837);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `security_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_settings`)),
  `role` enum('client','technician','admin') NOT NULL DEFAULT 'client',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ruc` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador EcoPlagas','admin@admin.com','+593 99 123 4567','Oficina Principal EcoPlagas, Quito, Ecuador',NULL,NULL,NULL,NULL,NULL,NULL,'admin',1,NULL,'2025-08-20 17:11:05','$2y$12$btVrO.Bx/myYm1cST9St0up1IyoYZkXUxFGwPnyWP3koXNWdkcyei',NULL,'2025-08-20 17:11:05','2025-08-20 17:11:05',NULL),(2,'Usuario Test','test@test.com','+593 98 765 4321','Dirección de prueba','Quito',NULL,NULL,NULL,NULL,NULL,'client',1,NULL,'2025-08-20 17:11:06','$2y$12$op9eXNZUxRmkR5riwMBXxez97Rk0CGA8PrsmMFrMpkv4DQhu63qYy',NULL,'2025-08-20 17:11:06','2025-08-21 02:10:18',NULL),(15,'Kevin Villacreses','kevinvillajim@hotmail.com','0963368896','Ferroviaria','Quito','cedula','1720598877001','1996-02-06','{\"preferred_time_slot\":\"morning\",\"allow_weekend_service\":false,\"emergency_contact\":null,\"emergency_contact_name\":null,\"special_instructions\":null,\"allergies_or_concerns\":null}','{\"session_timeout\":30}','client',1,NULL,'2025-08-21 17:39:22','$2y$12$.5BvG.PUZqAoJdu8OiNi6OMDsquVSAhlXBaU7fBBw.mxiZvFyCmni',NULL,'2025-08-21 17:18:09','2025-08-22 01:15:09',NULL),(16,'Mr. Regan Prosacco','client@test.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'client',1,NULL,'2025-08-22 00:19:03','$2y$12$HrhYK9x/uekegtRBLcH5EuF6mTgdyOnGoKhs7AkY79I0sGOLuJOhu','q2RtiqpgAp','2025-08-22 00:19:03','2025-08-22 00:19:03',NULL),(17,'María González','maria@test.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'client',1,NULL,NULL,'$2y$12$80k0CAVyCg7q/pMAuHT.2OEFduWvNdLklXbZ2E490ysK6e9Zk8miK',NULL,'2025-08-22 00:22:15','2025-08-22 00:22:15',NULL),(18,'Juan Pérez','juan@test.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'client',1,NULL,NULL,'$2y$12$TIDx/9hVwUcj8vRKX6ylL.MXROTZhXUwOU3/UnK0E.VV6TIJQhgYq',NULL,'2025-08-22 00:22:16','2025-08-22 00:22:16',NULL),(19,'Ana López','ana@test.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'client',1,NULL,NULL,'$2y$12$eBxnVaBqIdtlomqunbqo6ORzVvJ32Gu5EnTQ4Vtb73AJda83CxrZ.',NULL,'2025-08-22 00:22:16','2025-08-22 00:22:16',NULL),(20,'Efraín Villacreses','efravillacreses@gmail.com','09995031066','Vicente Andrade y Av. Maldonado Conjunto portal de Chimbacalle','Quito','cedula','1710118074001',NULL,'{\"preferred_time_slot\":\"morning\",\"allow_weekend_service\":false,\"emergency_contact\":null,\"emergency_contact_name\":null,\"special_instructions\":null,\"allergies_or_concerns\":null}','{\"session_timeout\":30}','client',1,NULL,'2025-08-22 03:21:39','$2y$12$z6fmt84uiyt8FChkAZadfO0UsUa015yJ3p3urB6OevJ00EN6QRkc2',NULL,'2025-08-22 03:18:18','2025-08-22 03:26:55',NULL),(21,'Steve Villacreses','stevevillacreses98@hotmail.com','0963778546','Miguel Hermoso y Marco Kelly','Quito',NULL,NULL,NULL,NULL,NULL,'client',1,NULL,NULL,'$2y$12$udZql839fwyzEIHyRx2Hae1J7xi3INkrT7eyRzcsonTo3KOL0YF4a',NULL,'2025-08-22 03:19:38','2025-08-22 03:19:38',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ecoplagasbackend'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-22 11:59:51
