/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: MOCKUP
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `actualizaciones_casos`
--

DROP TABLE IF EXISTS `actualizaciones_casos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `actualizaciones_casos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_caso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_caso` (`id_caso`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `1` FOREIGN KEY (`id_caso`) REFERENCES `casos` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actualizaciones_casos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `actualizaciones_casos` WRITE;
/*!40000 ALTER TABLE `actualizaciones_casos` DISABLE KEYS */;
INSERT INTO `actualizaciones_casos` VALUES
(4,3,2,'Revisa tus caries.','2026-03-11 17:17:57'),
(5,3,2,'DIAGNÓSTICO DE CIERRE:\nCaries de segundo grado tratadas con resina.','2026-03-11 17:18:28'),
(6,2,2,'loco','2026-03-11 17:44:40'),
(7,2,2,'DIAGNÓSTICO DE CIERRE:\nResuelto','2026-03-11 17:45:35'),
(9,5,2,'DIAGNÓSTICO DE CIERRE:\nno','2026-03-11 17:47:01'),
(12,8,2,'DIAGNÓSTICO DE CIERRE:\npokpokapokpodas','2026-03-12 17:09:17');
/*!40000 ALTER TABLE `actualizaciones_casos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `adjuntos`
--

DROP TABLE IF EXISTS `adjuntos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `adjuntos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_caso` int(11) DEFAULT NULL,
  `id_actualizacion` int(11) DEFAULT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_caso` (`id_caso`),
  KEY `id_actualizacion` (`id_actualizacion`),
  CONSTRAINT `1` FOREIGN KEY (`id_caso`) REFERENCES `casos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`id_actualizacion`) REFERENCES `actualizaciones_casos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjuntos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `adjuntos` WRITE;
/*!40000 ALTER TABLE `adjuntos` DISABLE KEYS */;
INSERT INTO `adjuntos` VALUES
(3,7,NULL,'uploads/1773255868_631_radio.jpg','radio.jpg','2026-03-11 19:04:28'),
(4,8,NULL,'uploads/1773335264_652_Expediente_00005_20260311.pdf','Expediente_00005_20260311.pdf','2026-03-12 17:07:44');
/*!40000 ALTER TABLE `adjuntos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `casos`
--

DROP TABLE IF EXISTS `casos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `casos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_paciente` int(11) NOT NULL,
  `id_doctor_asignado` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('abierto','en_tratamiento','resuelto') DEFAULT 'abierto',
  `apto_para_trivia` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `id_caso_padre` int(11) DEFAULT NULL,
  `diagnostico_final` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_paciente` (`id_paciente`),
  KEY `id_doctor_asignado` (`id_doctor_asignado`),
  CONSTRAINT `1` FOREIGN KEY (`id_paciente`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`id_doctor_asignado`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `casos` WRITE;
/*!40000 ALTER TABLE `casos` DISABLE KEYS */;
INSERT INTO `casos` VALUES
(2,1,2,'duele otro diente','aaaaaaay','resuelto',1,'2026-03-11 16:08:11',NULL,NULL),
(3,3,2,'Revisión de caries.','- Tengo Caries','resuelto',1,'2026-03-11 17:17:18',NULL,NULL),
(5,3,2,'aaaaaaaaaa','bbbbbbbbbbbbbbb','resuelto',0,'2026-03-11 17:44:10',NULL,NULL),
(7,1,2,'aszxc','awdadasd','en_tratamiento',0,'2026-03-11 19:04:28',NULL,NULL),
(8,1,2,'AAAAAAAAA','ME DUELE EL CULOA','resuelto',1,'2026-03-12 17:07:44',NULL,'pokpokapokpodas');
/*!40000 ALTER TABLE `casos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `trivia`
--

DROP TABLE IF EXISTS `trivia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trivia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_caso` int(11) NOT NULL,
  `diagnostico_correcto` varchar(255) NOT NULL,
  `opcion_falsa_1` varchar(255) NOT NULL,
  `opcion_falsa_2` varchar(255) NOT NULL,
  `opcion_falsa_3` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_caso` (`id_caso`),
  CONSTRAINT `1` FOREIGN KEY (`id_caso`) REFERENCES `casos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trivia`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `trivia` WRITE;
/*!40000 ALTER TABLE `trivia` DISABLE KEYS */;
INSERT INTO `trivia` VALUES
(1,3,'Caries de segundo grado tratadas con resina.','Caries Siguen','Caries Locas','Caries Inexistentes'),
(2,2,'Resuelto','123','456','1235');
/*!40000 ALTER TABLE `trivia` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('paciente','doctor','estudiante') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'Juan Paciente','paciente@test.com','$2y$12$lId6LOzA0R0u9Z0UlGKN7OnGjlWiyzAU9xjQJO1j3iL89D.0DY5o6','paciente'),
(2,'Dra. Elena','doctor@test.com','$2y$12$v1GhrbsAyqlt3W.dmtVWwO.R2TwA.tUgCMgWCLOuzvpRvIHFN0pna','doctor'),
(3,'Estudiante Alex','estudiante@test.com','$2y$12$SbtyhZ7qYp94oZ7t6a1kwOga3APb4nkVrMQB4.c9L5cb4jvZsmS8O','estudiante');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-18 18:06:00
