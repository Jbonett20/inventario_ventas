/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: inventario_ventas
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB

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
-- Current Database: `inventario_ventas`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `inventario_ventas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `inventario_ventas`;

--
-- Table structure for table `vb_base_diaria`
--

DROP TABLE IF EXISTS `vb_base_diaria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_base_diaria` (
  `id_base` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `base` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto base del d??a',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_base`),
  UNIQUE KEY `fecha` (`fecha`),
  KEY `idx_base_fecha` (`fecha`),
  KEY `idx_base_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_base_diaria`
--

LOCK TABLES `vb_base_diaria` WRITE;
/*!40000 ALTER TABLE `vb_base_diaria` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_base_diaria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_categorias`
--

DROP TABLE IF EXISTS `vb_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_categorias` (
  `id_categoria` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_categorias`
--

LOCK TABLES `vb_categorias` WRITE;
/*!40000 ALTER TABLE `vb_categorias` DISABLE KEYS */;
INSERT INTO `vb_categorias` VALUES
(1,'Producto Farmacia','Medicamentos y productos farmacéuticos',1,'2026-07-23 17:08:29','2026-07-23 21:13:59'),
(2,'Producto Varios','Productos de consumo general',1,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(4,'RESPUESTOS','',1,'2026-09-15 21:01:15','2026-09-15 21:01:15');
/*!40000 ALTER TABLE `vb_categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_cierres_inventario`
--

DROP TABLE IF EXISTS `vb_cierres_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_cierres_inventario` (
  `id_cierre` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `periodo` enum('DIARIO','SEMANAL','QUINCENAL','MENSUAL','PERSONALIZADO') NOT NULL DEFAULT 'DIARIO' COMMENT 'Cada cuanto se cierra',
  `fecha_desde` date DEFAULT NULL COMMENT 'Primer dia del periodo',
  `fecha_hasta` date DEFAULT NULL COMMENT 'Ultimo dia del periodo',
  `total_invertido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_capital` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_utilidad` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_ventas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ventas del periodo (facturas activas)',
  `total_devoluciones` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Devoluciones del periodo',
  `total_ingresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Compras / mercancia que entro',
  `total_egresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Gastos del periodo',
  `total_ganancia` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Utilidad de las ventas',
  `total_facturas` int(11) NOT NULL DEFAULT 0 COMMENT 'Cuantas facturas',
  `total_anuladas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de facturas anuladas',
  `utilidad_neta` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ganancia - egresos - devoluciones',
  `id_usuario` int(10) unsigned DEFAULT NULL COMMENT 'Quien hizo el cierre',
  `observacion` text DEFAULT NULL,
  `total_unidad` int(11) NOT NULL DEFAULT 0,
  `total_fraccion` int(11) NOT NULL DEFAULT 0,
  `fecha_cierre` date NOT NULL,
  `estado` enum('PROGRAMADO','CERRADO','ANULADO') NOT NULL DEFAULT 'CERRADO' COMMENT 'PROGRAMADO = agendado a futuro',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cierre`),
  KEY `idx_cierre_fecha` (`fecha_cierre`),
  KEY `idx_cierre_rango` (`fecha_desde`,`fecha_hasta`),
  KEY `idx_cierre_estado` (`estado`),
  KEY `fk_cierre_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_cierres_inventario`
--

LOCK TABLES `vb_cierres_inventario` WRITE;
/*!40000 ALTER TABLE `vb_cierres_inventario` DISABLE KEYS */;
INSERT INTO `vb_cierres_inventario` VALUES
(5,'SEMANAL','2026-10-01','2026-10-07',1726729.32,4359579.42,2632850.10,0.00,0.00,0.00,0.00,0.00,0,0.00,0.00,1,NULL,303,1259,'2026-10-07','CERRADO','2026-10-31 18:52:30'),
(6,'DIARIO','2026-10-31','2026-10-31',2086729.32,4809579.42,2722850.10,78302.02,0.00,0.00,1000.00,23577.95,3,0.00,22577.95,1,'Cierre del día desde Balance Diario',321,1259,'2026-10-31','CERRADO','2026-10-31 19:30:27');
/*!40000 ALTER TABLE `vb_cierres_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_clientes`
--

DROP TABLE IF EXISTS `vb_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_clientes` (
  `id_cliente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(5) NOT NULL DEFAULT 'CC' COMMENT 'CC, NIT, CE, PP',
  `documento` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `regimen` varchar(50) DEFAULT NULL COMMENT 'Regimen Simple, Comun, etc.',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `idx_cliente_doc` (`tipo_documento`,`documento`),
  KEY `idx_cliente_nombre` (`nombre`(100)),
  KEY `idx_cliente_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_clientes`
--

LOCK TABLES `vb_clientes` WRITE;
/*!40000 ALTER TABLE `vb_clientes` DISABLE KEYS */;
INSERT INTO `vb_clientes` VALUES
(1,'CC','12345','CLIENTE GENERAL','Calle Coveñas-Sucre #123','1234',NULL,NULL,NULL,1,'2026-07-23 17:08:30','2026-07-23 21:12:20'),
(3,'NIT','900748644-9','DISTRAMOTOR','CR 9W N 23-34','3113834864','','','',1,'2026-09-22 16:39:51','2026-09-22 16:39:51'),
(4,'CC','1063315194','KAREN PAMPLONA','CL 7 # 4 - 16 BARRIO 27 DE JULIO','3045532556','compulibano@hotmail.com','Común','',1,'2026-10-31 20:21:36','2026-10-31 20:21:36');
/*!40000 ALTER TABLE `vb_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_configuraciones`
--

DROP TABLE IF EXISTS `vb_configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_configuraciones` (
  `id_config` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grupo` varchar(50) NOT NULL COMMENT 'Grupo de configuraci??n',
  `clave` varchar(100) NOT NULL COMMENT 'Clave ??nica dentro del grupo',
  `valor` text DEFAULT NULL COMMENT 'Valor almacenado',
  `tipo` enum('TEXTO','NUMERO','BOOLEANO','JSON') NOT NULL DEFAULT 'TEXTO',
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `idx_config_grupo_clave` (`grupo`,`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_configuraciones`
--

LOCK TABLES `vb_configuraciones` WRITE;
/*!40000 ALTER TABLE `vb_configuraciones` DISABLE KEYS */;
INSERT INTO `vb_configuraciones` VALUES
(1,'fe','nit','1193242195','TEXTO','NIT de la empresa para facturación electrónica','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(2,'fe','software_id','','TEXTO','Software ID registrado en DIAN','2026-07-23 17:08:30','2026-07-23 17:08:30'),
(3,'fe','clave_certificado','','TEXTO','Clave del certificado digital','2026-07-23 17:08:30','2026-07-23 17:08:30'),
(4,'fe','llave_envio','','TEXTO','Llave de envío a la DIAN','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(5,'fe','usuario_api','','TEXTO','Usuario de la API de facturación','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(6,'fe','llave_api','','TEXTO','Llave de la API de facturación','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(7,'fe','url_api','https://www.factin.app:8443/Movimientoapi','TEXTO','URL de la API de facturación electrónica','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(8,'fe','url_api_articulos','http://159.65.32.58:5041/Inarticulosapi','TEXTO','URL de la API de artículos','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(9,'fe','prefijo','SD30','TEXTO','Prefijo de factura electrónica','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(10,'fe','resolucion','18764106894836','TEXTO','Número de resolución DIAN','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(11,'fe','rango_inicio','1','NUMERO','Inicio del rango de facturación electrónica','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(12,'fe','rango_fin','2000','NUMERO','Fin del rango de facturación electrónica','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(13,'empresa','nombre','SUPER DROGAS LA 30','TEXTO','Nombre de la empresa','2026-07-23 17:08:30','2026-07-23 17:08:30'),
(14,'empresa','direccion','CRA 26 No. 31-30','TEXTO','Dirección de la empresa','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(15,'empresa','telefono','2858727','TEXTO','Teléfono de la empresa','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(16,'sistema','timeout_sesion','30','NUMERO','Timeout de sesión en minutos','2026-07-23 17:08:30','2026-07-23 21:13:59'),
(17,'sistema','intentos_maximos','5','NUMERO','Máximos intentos de login fallidos','2026-07-23 17:08:30','2026-07-23 21:13:59');
/*!40000 ALTER TABLE `vb_configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_creditos`
--

DROP TABLE IF EXISTS `vb_creditos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_creditos` (
  `id_credito` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `id_cliente` int(10) unsigned NOT NULL,
  `valor_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `valor_aumento` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Aumento por cr??dito',
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','PAGADO','VENCIDO','INHABILITADO') NOT NULL DEFAULT 'ACTIVO',
  `motivo_inhabilitado` varchar(255) DEFAULT NULL COMMENT 'Por que se inhabilito',
  `fecha_inhabilitado` datetime DEFAULT NULL COMMENT 'Cuando se inhabilito',
  `id_usuario_inhabilitado` int(10) unsigned DEFAULT NULL COMMENT 'Quien lo inhabilito',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_credito`),
  KEY `idx_cred_cliente` (`id_cliente`),
  KEY `idx_cred_estado` (`estado`),
  KEY `idx_cred_usuario_inhab` (`id_usuario_inhabilitado`),
  CONSTRAINT `fk_cred_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_creditos`
--

LOCK TABLES `vb_creditos` WRITE;
/*!40000 ALTER TABLE `vb_creditos` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_creditos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_creditos_abonos`
--

DROP TABLE IF EXISTS `vb_creditos_abonos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_creditos_abonos` (
  `id_abono` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_credito` int(10) unsigned NOT NULL,
  `valor` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_abono`),
  KEY `idx_abono_credito` (`id_credito`),
  CONSTRAINT `fk_abono_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_creditos_abonos`
--

LOCK TABLES `vb_creditos_abonos` WRITE;
/*!40000 ALTER TABLE `vb_creditos_abonos` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_creditos_abonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_creditos_detalle`
--

DROP TABLE IF EXISTS `vb_creditos_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_creditos_detalle` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_credito` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `idx_cred_det_credito` (`id_credito`),
  CONSTRAINT `fk_cred_det_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_creditos_detalle`
--

LOCK TABLES `vb_creditos_detalle` WRITE;
/*!40000 ALTER TABLE `vb_creditos_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_creditos_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_detalle_facturas`
--

DROP TABLE IF EXISTS `vb_detalle_facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_detalle_facturas` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_factura` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripci??n al momento de facturar',
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades',
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en fracciones',
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '% IVA aplicado',
  `iva_valor` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor del IVA calculado',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sin IVA',
  `total` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total con IVA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_decimal` decimal(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cantidad fraccionada decimal (ej: 0.5 varilla, 0.25 thinner)',
  PRIMARY KEY (`id_detalle`),
  KEY `idx_det_factura` (`id_factura`),
  KEY `idx_det_producto` (`id_producto`),
  CONSTRAINT `fk_det_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_detalle_facturas`
--

LOCK TABLES `vb_detalle_facturas` WRITE;
/*!40000 ALTER TABLE `vb_detalle_facturas` DISABLE KEYS */;
INSERT INTO `vb_detalle_facturas` VALUES
(1,1,9,'Acetaminofén 500mg X100 tabletas',1,10,12000.00,19.00,4560.00,24000.00,28560.00,'2026-09-13 16:18:40',0.0000),
(8,8,23,'PANOTIL GOTAS',1,0,24000.00,0.00,0.00,24000.00,24000.00,'2026-09-15 16:01:17',0.0000),
(9,9,24,'NEUMATICO 275/300*17 COMSTAR',1,0,16000.00,19.00,3040.00,16000.00,19040.00,'2026-09-15 20:21:39',0.0000),
(10,10,24,'NEUMATICO 275/300*17 COMSTAR',1,0,16000.00,19.00,3040.00,16000.00,19040.00,'2026-09-15 20:55:44',0.0000),
(11,11,24,'NEUMATICO 275/300*17 COMSTAR',1,0,16000.00,19.00,3040.00,16000.00,19040.00,'2026-09-15 20:56:27',0.0000),
(12,12,24,'NEUMATICO 275/300*17 COMSTAR',1,0,16000.00,19.00,3040.00,16000.00,19040.00,'2026-09-15 20:57:37',0.0000),
(13,13,11,'Arroz Diana X5kg',0,0,12000.00,0.00,0.00,6000.00,6000.00,'2026-09-15 21:04:16',0.5000),
(14,14,9,'Acetaminofén 500mg X100 tabletas',0,10,14741.19,19.00,2800.83,14741.19,17542.02,'2026-09-16 18:31:06',0.0000),
(15,14,23,'PANOTIL GOTAS',1,0,24000.00,0.00,0.00,24000.00,24000.00,'2026-09-16 18:31:06',0.0000),
(16,15,11,'Arroz Diana X5kg',0,0,12000.00,0.00,0.00,6000.00,6000.00,'2026-09-16 20:14:23',0.5000),
(17,15,24,'NEUMATICO 275/300*17 COMSTAR',2,0,16000.00,19.00,6080.00,32000.00,38080.00,'2026-09-16 20:14:23',0.0000),
(18,16,24,'NEUMATICO 275/300*17 COMSTAR',1,0,16000.00,19.00,3040.00,16000.00,19040.00,'2026-09-29 20:28:31',0.0000),
(19,17,11,'Arroz Diana X5kg',0,0,12000.00,0.00,0.00,6000.00,6000.00,'2026-10-31 18:48:22',0.5000),
(20,17,9,'Acetaminofén 500mg X100 tabletas',1,0,14741.19,19.00,2800.83,14741.19,17542.02,'2026-10-31 18:48:22',0.0000),
(21,17,13,'Thinner X1 litro',0,0,8000.00,19.00,760.00,4000.00,4760.00,'2026-10-31 18:48:22',0.5000),
(22,18,25,'DOLEX GRIPA',1,0,25000.00,0.00,0.00,25000.00,25000.00,'2026-10-31 19:17:44',0.0000),
(23,19,25,'DOLEX GRIPA',1,0,25000.00,0.00,0.00,25000.00,25000.00,'2026-10-31 19:21:31',0.0000),
(24,20,26,'TINTA EPSON 544 AZUL',1,0,50000.00,19.00,9500.00,50000.00,59500.00,'2026-10-31 20:16:51',0.0000),
(25,21,11,'Arroz Diana X5kg',0,0,12000.00,0.00,0.00,6000.00,6000.00,'2026-11-17 18:58:42',0.5000),
(26,22,11,'Arroz Diana X5kg',0,0,12000.00,0.00,0.00,6000.00,6000.00,'2026-11-17 19:30:33',0.5000);
/*!40000 ALTER TABLE `vb_detalle_facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_devoluciones`
--

DROP TABLE IF EXISTS `vb_devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_devoluciones` (
  `id_devolucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `id_factura` int(10) unsigned DEFAULT NULL,
  `id_cliente` int(10) unsigned DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tipo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' COMMENT 'Resumen: metodo unico o MIXTO',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_devolucion`),
  KEY `idx_dev_factura` (`id_factura`),
  KEY `idx_dev_cliente` (`id_cliente`),
  KEY `idx_dev_fecha` (`fecha`),
  CONSTRAINT `fk_dev_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_dev_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_devoluciones`
--

LOCK TABLES `vb_devoluciones` WRITE;
/*!40000 ALTER TABLE `vb_devoluciones` DISABLE KEYS */;
INSERT INTO `vb_devoluciones` VALUES
(3,'DEV-46502-2026091515',9,1,'ERROR DE VENTA',16000.00,'EFECTIVO',1,'2026-09-15','2026-09-15 20:22:22');
/*!40000 ALTER TABLE `vb_devoluciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_devoluciones_detalle`
--

DROP TABLE IF EXISTS `vb_devoluciones_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_devoluciones_detalle` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_devolucion` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned DEFAULT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `idx_devdet_devolucion` (`id_devolucion`),
  CONSTRAINT `fk_devdet_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_devoluciones_detalle`
--

LOCK TABLES `vb_devoluciones_detalle` WRITE;
/*!40000 ALTER TABLE `vb_devoluciones_detalle` DISABLE KEYS */;
INSERT INTO `vb_devoluciones_detalle` VALUES
(3,3,24,1,0,16000.00,16000.00,'2026-09-15 20:22:22');
/*!40000 ALTER TABLE `vb_devoluciones_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_devoluciones_pagos`
--

DROP TABLE IF EXISTS `vb_devoluciones_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_devoluciones_pagos` (
  `id_pago_devolucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_devolucion` int(10) unsigned NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N┬░ de transaccion (opcional)',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago_devolucion`),
  KEY `idx_dpago_dev` (`id_devolucion`),
  KEY `idx_dpago_metodo` (`metodo`),
  KEY `fk_dpago_usuario` (`id_usuario`),
  CONSTRAINT `fk_dpago_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_devoluciones_pagos`
--

LOCK TABLES `vb_devoluciones_pagos` WRITE;
/*!40000 ALTER TABLE `vb_devoluciones_pagos` DISABLE KEYS */;
INSERT INTO `vb_devoluciones_pagos` VALUES
(4,3,'EFECTIVO',16000.00,NULL,1,'2026-09-15 20:22:22');
/*!40000 ALTER TABLE `vb_devoluciones_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_egresos`
--

DROP TABLE IF EXISTS `vb_egresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_egresos` (
  `id_egreso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_tipo_egreso` int(10) unsigned NOT NULL,
  `pagado_a` varchar(200) DEFAULT NULL,
  `valor` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `mes` varchar(7) DEFAULT NULL COMMENT 'AAAA-MM para reportes',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_egreso`),
  KEY `idx_egreso_tipo` (`id_tipo_egreso`),
  KEY `idx_egreso_fecha` (`fecha`),
  KEY `idx_egreso_mes` (`mes`),
  KEY `fk_egreso_usuario` (`id_usuario`),
  CONSTRAINT `fk_egreso_tipo` FOREIGN KEY (`id_tipo_egreso`) REFERENCES `vb_tipos_egreso` (`id_tipo_egreso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_egreso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_egresos`
--

LOCK TABLES `vb_egresos` WRITE;
/*!40000 ALTER TABLE `vb_egresos` DISABLE KEYS */;
INSERT INTO `vb_egresos` VALUES
(2,3,'Serlys',1000.00,'2026-10-31','2026-10','',1,'2026-10-31 18:50:22');
/*!40000 ALTER TABLE `vb_egresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_empresa`
--

DROP TABLE IF EXISTS `vb_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_empresa` (
  `id_empresa` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mostrar_fe` tinyint(1) NOT NULL DEFAULT 1,
  `logo` varchar(255) DEFAULT NULL,
  `regimen` varchar(50) DEFAULT NULL,
  `actividad_economica` varchar(10) DEFAULT NULL COMMENT 'C??digo CIIU',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_empresa`
--

LOCK TABLES `vb_empresa` WRITE;
/*!40000 ALTER TABLE `vb_empresa` DISABLE KEYS */;
INSERT INTO `vb_empresa` VALUES
(1,'1193242195-8','Drogas la rebaja','Calle 6 #13-15','2858727-3126568609','',1,NULL,NULL,NULL,'2026-07-23 17:08:30','2026-07-26 20:42:35');
/*!40000 ALTER TABLE `vb_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_facturas`
--

DROP TABLE IF EXISTS `vb_facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_facturas` (
  `id_factura` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL COMMENT 'UUID ??nico de la factura',
  `codigo` bigint(20) NOT NULL COMMENT 'N??mero consecutivo de factura',
  `id_empresa` int(10) unsigned DEFAULT NULL,
  `id_cliente` int(10) unsigned DEFAULT NULL,
  `id_vendedor` int(10) unsigned DEFAULT NULL,
  `id_vendedor_registrado` int(10) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `tipo` enum('NORMAL','ELECTRONICA') NOT NULL DEFAULT 'NORMAL',
  `tipo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_iva` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total a pagar',
  `pago_recibido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cambio` decimal(14,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ganancia` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cufe` varchar(250) DEFAULT NULL COMMENT 'CUFE para factura electr??nica',
  `qr` text DEFAULT NULL COMMENT 'QR para factura electr??nica',
  `estado` enum('ACTIVA','ANULADA') NOT NULL DEFAULT 'ACTIVA',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_factura`),
  KEY `idx_fact_codigo` (`codigo`),
  KEY `idx_fact_fecha` (`fecha`),
  KEY `idx_fact_cliente` (`id_cliente`),
  KEY `idx_fact_vendedor` (`id_vendedor`),
  KEY `idx_fact_estado` (`estado`),
  KEY `idx_fact_tipo` (`tipo`),
  KEY `fk_fact_empresa` (`id_empresa`),
  KEY `idx_fact_vendedor_reg` (`id_vendedor_registrado`),
  CONSTRAINT `fk_fact_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fact_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `vb_empresa` (`id_empresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fact_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fact_vendedor_reg` FOREIGN KEY (`id_vendedor_registrado`) REFERENCES `vb_vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_facturas`
--

LOCK TABLES `vb_facturas` WRITE;
/*!40000 ALTER TABLE `vb_facturas` DISABLE KEYS */;
INSERT INTO `vb_facturas` VALUES
(1,'1ad46ae6-0265-4421-b787-d86df585e35e',46500,1,1,3,NULL,'2026-09-13','11:18:40','NORMAL','EFECTIVO',24000.00,4560.00,28560.00,40000.00,11440.00,0.00,8000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-13 16:18:40','2026-09-13 16:18:40'),
(8,'be1be39e-716d-4646-a58c-c64b58fdf184',46501,1,1,1,NULL,'2026-09-15','11:01:17','NORMAL','EFECTIVO',24000.00,0.00,24000.00,24000.00,0.00,0.00,3857.00,NULL,NULL,'ACTIVA',NULL,'2026-09-15 16:01:17','2026-09-15 16:01:17'),
(9,'5d4bd61a-f256-491c-b274-d8454b0c7f63',46502,1,1,1,NULL,'2026-09-15','15:21:39','NORMAL','EFECTIVO',16000.00,3040.00,19040.00,19040.00,0.00,0.00,7000.00,NULL,NULL,'ANULADA',NULL,'2026-09-15 20:21:39','2026-09-15 20:22:22'),
(10,'1bd6472c-c132-46fb-8b53-1e27f4771a3b',1,1,1,1,1,'2026-09-15','15:55:44','ELECTRONICA','EFECTIVO',16000.00,3040.00,19040.00,19040.00,0.00,0.00,7000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-15 20:55:44','2026-09-15 20:55:44'),
(11,'66ddacb4-f94f-415f-ad11-55f39fd97c85',46503,1,1,1,1,'2026-09-15','15:56:27','NORMAL','EFECTIVO',16000.00,3040.00,19040.00,19040.00,0.00,0.00,7000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-15 20:56:27','2026-09-15 20:56:27'),
(12,'a0aea3d8-0ec3-4c9a-90d5-d83a5f54bb2a',46504,1,1,1,1,'2026-09-15','15:57:37','NORMAL','EFECTIVO',16000.00,3040.00,19040.00,24040.00,5000.00,0.00,7000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-15 20:57:37','2026-09-15 20:57:37'),
(13,'ae6a2ff7-245b-47b7-a9c7-8775442d342c',2,1,1,1,NULL,'2026-09-15','16:04:16','ELECTRONICA','EFECTIVO',6000.00,0.00,6000.00,6000.00,0.00,0.00,2000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-15 21:04:16','2026-09-15 21:04:16'),
(14,'076d619b-3d4f-4ae4-8c2d-92f85cd9243d',46505,1,1,1,NULL,'2026-09-16','13:31:06','NORMAL','NEQUI',38741.19,2800.83,41542.02,45927535.00,45885992.98,0.00,3857.00,NULL,NULL,'ANULADA',NULL,'2026-09-16 18:31:06','2026-09-16 18:38:48'),
(15,'539ba5b7-35be-4602-8c2f-7f12c27fd92c',46506,1,1,1,NULL,'2026-09-16','15:14:23','NORMAL','MIXTO',38000.00,6080.00,44080.00,44080.00,0.00,0.00,16000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-16 20:14:23','2026-09-16 20:14:23'),
(16,'4e871947-ccb0-4358-9910-f60980594a3d',46507,1,1,1,NULL,'2026-09-29','15:28:31','NORMAL','EFECTIVO',16000.00,3040.00,19040.00,19040.00,0.00,0.00,7000.00,NULL,NULL,'ACTIVA',NULL,'2026-09-29 20:28:31','2026-09-29 20:28:31'),
(17,'a237ef64-bb56-4fe6-9f50-d8921e071da9',46508,1,1,1,NULL,'2026-10-31','13:48:22','NORMAL','EFECTIVO',24741.19,3560.83,28302.02,28302016.00,28273713.98,0.00,13577.95,NULL,NULL,'ACTIVA',NULL,'2026-10-31 18:48:22','2026-10-31 18:48:22'),
(18,'3e859516-8568-4cfc-9587-e00935207402',46509,1,1,1,NULL,'2026-10-31','14:17:44','NORMAL','EFECTIVO',25000.00,0.00,25000.00,25000.00,0.00,0.00,5000.00,NULL,NULL,'ACTIVA',NULL,'2026-10-31 19:17:44','2026-10-31 19:17:44'),
(19,'cf3f208a-de2d-44da-ba0c-a886c957a8d2',46510,1,1,1,NULL,'2026-10-31','14:21:31','NORMAL','MIXTO',25000.00,0.00,25000.00,25000.00,0.00,0.00,5000.00,NULL,NULL,'ACTIVA',NULL,'2026-10-31 19:21:31','2026-10-31 19:21:31'),
(20,'bce54bf6-fbf6-4809-9d16-98daf011ac0f',46511,1,1,1,NULL,'2026-10-31','15:16:51','NORMAL','EFECTIVO',50000.00,9500.00,59500.00,59500.00,0.00,0.00,26000.00,NULL,NULL,'ACTIVA',NULL,'2026-10-31 20:16:51','2026-10-31 20:16:51'),
(21,'db63fa12-f071-49f2-9890-ca644e0f36bc',46512,1,1,1,NULL,'2026-11-17','13:58:42','NORMAL','MIXTO',6000.00,0.00,6000.00,6000.00,0.00,0.00,2000.00,NULL,NULL,'ACTIVA',NULL,'2026-11-17 18:58:42','2026-11-17 18:58:42'),
(22,'a6e10571-587d-4e15-b31e-3ad1ae585110',46513,1,1,1,NULL,'2026-11-17','14:30:33','NORMAL','MIXTO',6000.00,0.00,6000.00,6000.00,0.00,0.00,2000.00,NULL,NULL,'ACTIVA',NULL,'2026-11-17 19:30:33','2026-11-17 19:30:33');
/*!40000 ALTER TABLE `vb_facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_facturas_pagos`
--

DROP TABLE IF EXISTS `vb_facturas_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_facturas_pagos` (
  `id_pago` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_factura` int(10) unsigned NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N┬░ de aprobacion / transaccion (opcional)',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `idx_fpagofactura` (`id_factura`),
  KEY `idx_fpagometodo` (`metodo`),
  KEY `idx_fpagousuario` (`id_usuario`),
  CONSTRAINT `fk_fpago_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_facturas_pagos`
--

LOCK TABLES `vb_facturas_pagos` WRITE;
/*!40000 ALTER TABLE `vb_facturas_pagos` DISABLE KEYS */;
INSERT INTO `vb_facturas_pagos` VALUES
(1,1,'EFECTIVO',28560.00,NULL,3,'2026-09-13 16:18:40'),
(10,8,'EFECTIVO',24000.00,NULL,1,'2026-09-15 16:01:17'),
(11,9,'EFECTIVO',19040.00,NULL,1,'2026-09-15 20:21:39'),
(12,10,'EFECTIVO',19040.00,NULL,1,'2026-09-15 20:55:44'),
(13,11,'EFECTIVO',19040.00,NULL,1,'2026-09-15 20:56:27'),
(14,12,'EFECTIVO',14040.00,NULL,1,'2026-09-15 20:57:37'),
(15,12,'EFECTIVO',5000.00,NULL,1,'2026-09-15 20:57:37'),
(16,13,'EFECTIVO',6000.00,NULL,1,'2026-09-15 21:04:16'),
(17,14,'NEQUI',31542.02,NULL,1,'2026-09-16 18:31:06'),
(18,14,'NEQUI',10000.00,NULL,1,'2026-09-16 18:31:06'),
(19,15,'NEQUI',10000.00,NULL,1,'2026-09-16 20:14:23'),
(20,15,'EFECTIVO',20000.00,NULL,1,'2026-09-16 20:14:23'),
(21,15,'TRANSFERENCIA',14080.00,NULL,1,'2026-09-16 20:14:23'),
(22,16,'EFECTIVO',19040.00,NULL,1,'2026-09-29 20:28:31'),
(23,17,'EFECTIVO',28302.02,NULL,1,'2026-10-31 18:48:22'),
(24,18,'EFECTIVO',25000.00,NULL,1,'2026-10-31 19:17:44'),
(25,19,'NEQUI',10000.00,NULL,1,'2026-10-31 19:21:31'),
(26,19,'DAVIPLATA',15000.00,NULL,1,'2026-10-31 19:21:31'),
(27,20,'EFECTIVO',59500.00,NULL,1,'2026-10-31 20:16:51'),
(28,21,'EFECTIVO',1000.00,NULL,1,'2026-11-17 18:58:42'),
(29,21,'NEQUI',1000.00,NULL,1,'2026-11-17 18:58:42'),
(30,21,'DAVIPLATA',1000.00,NULL,1,'2026-11-17 18:58:42'),
(31,21,'ADDI',3000.00,NULL,1,'2026-11-17 18:58:42'),
(32,22,'EFECTIVO',1000.00,NULL,1,'2026-11-17 19:30:33'),
(33,22,'NEQUI',1000.00,NULL,1,'2026-11-17 19:30:33'),
(34,22,'DAVIPLATA',1000.00,NULL,1,'2026-11-17 19:30:33'),
(35,22,'ADDI',3000.00,NULL,1,'2026-11-17 19:30:33');
/*!40000 ALTER TABLE `vb_facturas_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_ingresos`
--

DROP TABLE IF EXISTS `vb_ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_ingresos` (
  `id_ingreso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) unsigned NOT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra pagado en este ingreso',
  `tipo` enum('MANUAL','FACTURA_COMPRA') NOT NULL DEFAULT 'MANUAL',
  `id_factura_compra` int(10) unsigned DEFAULT NULL COMMENT 'Si viene de una factura de compra',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ingreso`),
  KEY `idx_ing_producto` (`id_producto`),
  KEY `idx_ing_fecha` (`fecha`),
  KEY `idx_ing_tipo` (`tipo`),
  KEY `fk_ing_usuario` (`id_usuario`),
  CONSTRAINT `fk_ing_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ing_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_ingresos`
--

LOCK TABLES `vb_ingresos` WRITE;
/*!40000 ALTER TABLE `vb_ingresos` DISABLE KEYS */;
INSERT INTO `vb_ingresos` VALUES
(19,9,200,0,5000.00,'MANUAL',NULL,'Ingreso por unidades: 200 und | Compra: $5.000',3,'2026-09-13','2026-09-13 16:39:26');
/*!40000 ALTER TABLE `vb_ingresos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_ingreso_after_insert` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    UPDATE `vb_inventario` SET `ultimo_ingreso` = NOW() WHERE `id_producto` = NEW.id_producto;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_ingreso_movimiento` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    INSERT INTO `vb_movimientos_inventario`
        (`id_producto`, `tipo`, `unidad`, `fraccion`, `unidad_resultante`, `fraccion_resultante`, `observacion`, `id_usuario`, `created_at`)
    SELECT
        NEW.id_producto, 'INGRESO', NEW.cantidad_unidad, NEW.cantidad_fraccion,
        `unidad`, `fraccion`, NEW.observacion, NEW.id_usuario, NOW()
    FROM `vb_inventario` WHERE `id_producto` = NEW.id_producto;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_ingreso_stock` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    DECLARE v_fraccion INT DEFAULT 0;
    DECLARE v_unidad_cerrada INT DEFAULT 1;

    SELECT `fraccion`, `unidad_cerrada` INTO v_fraccion, v_unidad_cerrada
    FROM `vb_productos` WHERE `id_producto` = NEW.id_producto;

    UPDATE `vb_inventario`
    SET
        `unidad` = `unidad` + NEW.cantidad_unidad,
        `fraccion` = `fraccion` + NEW.cantidad_fraccion
    WHERE `id_producto` = NEW.id_producto;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vb_ingresos_detalle_factura`
--

DROP TABLE IF EXISTS `vb_ingresos_detalle_factura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_ingresos_detalle_factura` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ingreso_factura` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned NOT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra por unidad cerrada segun esta factura',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `idx_ingdet_factura` (`id_ingreso_factura`),
  KEY `idx_ingdet_producto` (`id_producto`),
  CONSTRAINT `fk_ingdet_factura` FOREIGN KEY (`id_ingreso_factura`) REFERENCES `vb_ingresos_factura` (`id_ingreso_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ingdet_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_ingresos_detalle_factura`
--

LOCK TABLES `vb_ingresos_detalle_factura` WRITE;
/*!40000 ALTER TABLE `vb_ingresos_detalle_factura` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_ingresos_detalle_factura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_ingresos_factura`
--

DROP TABLE IF EXISTS `vb_ingresos_factura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_ingresos_factura` (
  `id_ingreso_factura` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_factura` varchar(200) DEFAULT NULL,
  `id_proveedor` int(10) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pendiente, 1=completado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_ingreso_factura`),
  KEY `idx_ingfac_proveedor` (`id_proveedor`),
  CONSTRAINT `fk_ingfac_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `vb_proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_ingresos_factura`
--

LOCK TABLES `vb_ingresos_factura` WRITE;
/*!40000 ALTER TABLE `vb_ingresos_factura` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_ingresos_factura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_inventario`
--

DROP TABLE IF EXISTS `vb_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_inventario` (
  `id_inventario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) unsigned NOT NULL,
  `unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades (cajas)',
  `fraccion` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `ultimo_ingreso` datetime DEFAULT NULL,
  `ultimo_egreso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_inventario`),
  UNIQUE KEY `idx_inv_producto` (`id_producto`),
  CONSTRAINT `fk_inv_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_inventario`
--

LOCK TABLES `vb_inventario` WRITE;
/*!40000 ALTER TABLE `vb_inventario` DISABLE KEYS */;
INSERT INTO `vb_inventario` VALUES
(1,8,50,0.0000,NULL,NULL,'2026-07-26 20:12:14','2026-07-26 20:12:14'),
(2,9,218,490.0000,'2026-09-13 11:39:26','2026-10-31 13:48:22','2026-07-26 20:12:14','2026-10-31 18:48:22'),
(3,10,10,60.0000,'2026-09-12 15:07:38',NULL,'2026-07-26 20:12:14','2026-09-12 20:07:54'),
(4,11,2,47.5000,NULL,'2026-11-17 14:30:33','2026-07-26 20:12:14','2026-11-17 19:30:33'),
(5,12,5,550.0000,NULL,NULL,'2026-07-26 20:12:14','2026-07-26 20:12:14'),
(6,13,3,11.0000,NULL,'2026-10-31 13:48:22','2026-07-26 20:12:14','2026-10-31 18:48:22'),
(7,14,0,100.0000,NULL,NULL,'2026-07-26 20:12:14','2026-07-26 20:12:14'),
(16,23,1,0.0000,NULL,'2026-09-16 13:31:06','2026-09-15 15:59:21','2026-09-16 18:38:48'),
(17,24,14,0.0000,NULL,'2026-09-29 15:28:31','2026-09-15 20:15:08','2026-09-29 20:28:31'),
(18,25,18,0.0000,NULL,'2026-10-31 14:21:31','2026-10-31 19:08:24','2026-10-31 19:21:31'),
(19,26,9,0.0000,NULL,'2026-10-31 15:16:51','2026-10-31 20:10:42','2026-10-31 20:16:51');
/*!40000 ALTER TABLE `vb_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_ivas`
--

DROP TABLE IF EXISTS `vb_ivas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_ivas` (
  `id_iva` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iva` decimal(5,2) NOT NULL COMMENT 'Porcentaje del IVA (0, 5, 19)',
  `nombre` varchar(50) DEFAULT NULL COMMENT 'Nombre del IVA ej. "IVA 19%"',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_iva`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_ivas`
--

LOCK TABLES `vb_ivas` WRITE;
/*!40000 ALTER TABLE `vb_ivas` DISABLE KEYS */;
INSERT INTO `vb_ivas` VALUES
(1,19.00,'IVA 19%',1,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(2,5.00,'IVA 5%',1,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(3,0.00,'IVA 0% (Exento)',1,'2026-07-23 17:08:29','2026-07-23 17:08:29');
/*!40000 ALTER TABLE `vb_ivas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_lotes`
--

DROP TABLE IF EXISTS `vb_lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_lotes` (
  `id_lote` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) unsigned NOT NULL,
  `codigo_lote` varchar(100) NOT NULL COMMENT 'N??mero de lote',
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `fecha_vencimiento` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_lote`),
  KEY `idx_lote_producto` (`id_producto`),
  KEY `idx_lote_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `fk_lote_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_lotes`
--

LOCK TABLES `vb_lotes` WRITE;
/*!40000 ALTER TABLE `vb_lotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_lotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_movimientos_inventario`
--

DROP TABLE IF EXISTS `vb_movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_movimientos_inventario` (
  `id_movimiento` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) unsigned NOT NULL,
  `tipo` enum('INGRESO','EGRESO','AJUSTE','DEVOLUCION') NOT NULL,
  `unidad` int(11) NOT NULL DEFAULT 0,
  `fraccion` int(11) NOT NULL DEFAULT 0,
  `unidad_resultante` int(11) NOT NULL DEFAULT 0 COMMENT 'Stock resultante en unidades',
  `fraccion_resultante` int(11) NOT NULL DEFAULT 0 COMMENT 'Stock resultante en fracciones',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_mov_producto` (`id_producto`),
  KEY `idx_mov_tipo` (`tipo`),
  KEY `idx_mov_fecha` (`created_at`),
  KEY `fk_mov_usuario` (`id_usuario`),
  CONSTRAINT `fk_mov_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_movimientos_inventario`
--

LOCK TABLES `vb_movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `vb_movimientos_inventario` DISABLE KEYS */;
INSERT INTO `vb_movimientos_inventario` VALUES
(21,9,'EGRESO',-1,-10,19,490,NULL,3,'2026-09-13 16:18:40'),
(22,9,'INGRESO',200,0,219,490,'Ingreso por unidades: 200 und | Compra: $5.000',3,'2026-09-13 16:39:26'),
(31,23,'AJUSTE',2,0,2,0,'',1,'2026-09-15 16:00:40'),
(32,23,'AJUSTE',0,2,2,2,'',1,'2026-09-15 16:00:59'),
(33,23,'AJUSTE',0,-2,2,0,'',1,'2026-09-15 16:01:05'),
(34,23,'EGRESO',-1,0,1,0,NULL,1,'2026-09-15 16:01:17'),
(35,24,'AJUSTE',20,0,20,0,'',1,'2026-09-15 20:16:37'),
(36,24,'EGRESO',-1,0,19,0,NULL,1,'2026-09-15 20:21:39'),
(37,24,'DEVOLUCION',1,0,0,0,'Devolución #DEV-46502-20260915152222 - ERROR DE VENTA',1,'2026-09-15 20:22:22'),
(38,24,'EGRESO',-1,0,19,0,NULL,1,'2026-09-15 20:55:44'),
(39,24,'EGRESO',-1,0,18,0,NULL,1,'2026-09-15 20:56:27'),
(40,24,'EGRESO',-1,0,17,0,NULL,1,'2026-09-15 20:57:37'),
(41,11,'EGRESO',0,-1,2,50,NULL,1,'2026-09-15 21:04:16'),
(42,9,'EGRESO',0,-10,219,480,NULL,1,'2026-09-16 18:31:06'),
(43,23,'EGRESO',-1,0,0,0,NULL,1,'2026-09-16 18:31:06'),
(44,11,'EGRESO',0,-1,2,49,NULL,1,'2026-09-16 20:14:23'),
(45,24,'EGRESO',-2,0,15,0,NULL,1,'2026-09-16 20:14:23'),
(46,24,'EGRESO',-1,0,14,0,NULL,1,'2026-09-29 20:28:31'),
(47,11,'EGRESO',0,-1,2,49,NULL,1,'2026-10-31 18:48:22'),
(48,9,'EGRESO',-1,0,218,490,NULL,1,'2026-10-31 18:48:22'),
(49,13,'EGRESO',0,-1,3,11,NULL,1,'2026-10-31 18:48:22'),
(50,25,'AJUSTE',20,0,20,0,'',1,'2026-10-31 19:11:55'),
(51,25,'EGRESO',-1,0,19,0,NULL,1,'2026-10-31 19:17:44'),
(52,25,'EGRESO',-1,0,18,0,NULL,1,'2026-10-31 19:21:31'),
(53,26,'AJUSTE',10,0,10,0,'',1,'2026-10-31 20:11:55'),
(54,26,'EGRESO',-1,0,9,0,NULL,1,'2026-10-31 20:16:51'),
(55,11,'EGRESO',0,-1,2,48,NULL,1,'2026-11-17 18:58:42'),
(56,11,'EGRESO',0,-1,2,48,NULL,1,'2026-11-17 19:30:33');
/*!40000 ALTER TABLE `vb_movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_plan_separe`
--

DROP TABLE IF EXISTS `vb_plan_separe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_plan_separe` (
  `id_plan_separe` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `id_cliente` int(10) unsigned NOT NULL,
  `valor_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `valor_abonado` decimal(14,2) NOT NULL DEFAULT 0.00,
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','COMPLETADO','CANCELADO') NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_plan_separe`),
  KEY `idx_plan_cliente` (`id_cliente`),
  CONSTRAINT `fk_plan_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_plan_separe`
--

LOCK TABLES `vb_plan_separe` WRITE;
/*!40000 ALTER TABLE `vb_plan_separe` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_plan_separe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_plan_separe_abonos`
--

DROP TABLE IF EXISTS `vb_plan_separe_abonos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_plan_separe_abonos` (
  `id_abono` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_plan_separe` int(10) unsigned NOT NULL,
  `valor` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_abono`),
  KEY `idx_planabono_plan` (`id_plan_separe`),
  CONSTRAINT `fk_planabono_plan` FOREIGN KEY (`id_plan_separe`) REFERENCES `vb_plan_separe` (`id_plan_separe`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_plan_separe_abonos`
--

LOCK TABLES `vb_plan_separe_abonos` WRITE;
/*!40000 ALTER TABLE `vb_plan_separe_abonos` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_plan_separe_abonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_plan_separe_detalle`
--

DROP TABLE IF EXISTS `vb_plan_separe_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_plan_separe_detalle` (
  `id_detalle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_plan_separe` int(10) unsigned NOT NULL,
  `id_producto` int(10) unsigned DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `id_plan_separe` (`id_plan_separe`),
  CONSTRAINT `vb_plan_separe_detalle_ibfk_1` FOREIGN KEY (`id_plan_separe`) REFERENCES `vb_plan_separe` (`id_plan_separe`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_plan_separe_detalle`
--

LOCK TABLES `vb_plan_separe_detalle` WRITE;
/*!40000 ALTER TABLE `vb_plan_separe_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `vb_plan_separe_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_productos`
--

DROP TABLE IF EXISTS `vb_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_productos` (
  `id_producto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` bigint(20) unsigned NOT NULL COMMENT 'Codigo interno numerico asignado por el sistema',
  `codigo_barras_1` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras principal',
  `codigo_barras_2` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras secundario',
  `codigo_barras_3` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras terciario',
  `descripcion` text NOT NULL,
  `presentacion` varchar(100) DEFAULT NULL COMMENT 'Ej. "X30ML", "X24 tabletas"',
  `marca` varchar(100) DEFAULT NULL,
  `id_proveedor` int(10) unsigned DEFAULT NULL,
  `id_categoria` int(10) unsigned DEFAULT NULL,
  `id_iva` int(10) unsigned DEFAULT NULL,
  `id_seccion` int(10) unsigned DEFAULT NULL,
  `unidad_cerrada` int(11) NOT NULL DEFAULT 1 COMMENT 'Unidades por caja/empaque',
  `fraccion` int(11) NOT NULL DEFAULT 0 COMMENT '0=no fraccionable, >0=cantidad por unidad',
  `valor_compra` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de compra',
  `valor_venta` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta p??blico',
  `valor_unidad` decimal(12,2) DEFAULT 0.00 COMMENT 'Precio por unidad suelta (si fraccionable)',
  `precio_maximo_regulado` decimal(12,2) DEFAULT NULL COMMENT 'Tope legal de venta (medicamentos). NULL = no aplica',
  `rentabilidad` decimal(8,2) DEFAULT 0.00 COMMENT '% de rentabilidad calculado',
  `stock_minimo` int(11) NOT NULL DEFAULT 1 COMMENT 'Alerta de stock m??nimo',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de imagen del producto',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipo_venta` enum('UNIDAD','CAJA','FRACCION_DECIMAL') NOT NULL DEFAULT 'UNIDAD' COMMENT 'UNIDAD=unidad suelta, CAJA=solo caja completa, FRACCION_DECIMAL=venta por fracción (ej: 0.5, 0.25)',
  `unidad_medida` varchar(30) DEFAULT NULL COMMENT 'Ej: METRO, LITRO, KILO, VARILLA, BOTELLA, LIBRA',
  `cantidad_por_unidad` decimal(12,4) NOT NULL DEFAULT 1.0000 COMMENT 'Cuánto equivale 1 unidad (ej: 1 varilla = 6 metros, 1 thinner = 1 litro)',
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `uq_prod_codigo` (`codigo`),
  KEY `idx_prod_barras1` (`codigo_barras_1`),
  KEY `idx_prod_barras2` (`codigo_barras_2`),
  KEY `idx_prod_descripcion` (`descripcion`(100)),
  KEY `idx_prod_activo` (`activo`),
  KEY `idx_prod_proveedor` (`id_proveedor`),
  KEY `idx_prod_categoria` (`id_categoria`),
  KEY `idx_prod_iva` (`id_iva`),
  KEY `fk_prod_seccion` (`id_seccion`),
  FULLTEXT KEY `ft_prod_descripcion` (`descripcion`,`presentacion`),
  CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `vb_categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_iva` FOREIGN KEY (`id_iva`) REFERENCES `vb_ivas` (`id_iva`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `vb_proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `vb_secciones` (`id_seccion`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_productos`
--

LOCK TABLES `vb_productos` WRITE;
/*!40000 ALTER TABLE `vb_productos` DISABLE KEYS */;
INSERT INTO `vb_productos` VALUES
(8,1000001,NULL,'IBUPROFENO',NULL,'Ibuprofeno 400mg X30 tabletas','X30 TABLETAS','MK',1,1,1,NULL,30,0,2500.00,5500.00,0.00,NULL,54.55,10,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','UNIDAD','',1.0000),
(9,1000002,'','ACETAMINOFEN','','Acetaminofén 500mg X100 tabletas','X100 TABLETAS','GEN',3,1,1,NULL,100,10,4913.24,14741.19,1842.65,NULL,66.67,5,'',1,'2026-07-26 20:12:14','2026-09-13 17:27:45','UNIDAD','',1.0000),
(10,1000003,NULL,'VARILLA-12',NULL,'Varilla corrugada 1/2\"','X6 METROS','SIDERTEC',4,2,3,NULL,1,0,18000.00,30000.00,0.00,NULL,40.00,5,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','FRACCION_DECIMAL','METRO',6.0000),
(11,1000004,NULL,'ARROZ',NULL,'Arroz Diana X5kg','X1 KILO','Diana',4,2,3,NULL,1,0,8000.00,12000.00,0.00,NULL,33.33,5,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','FRACCION_DECIMAL','KILO',1.0000),
(12,1000005,NULL,'CEMENTO-50',NULL,'Cemento Argos 50kg','X110 LIBRAS','Argos',4,2,3,NULL,1,0,35000.00,55000.00,0.00,NULL,36.36,3,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','FRACCION_DECIMAL','LIBRA',110.0000),
(13,1000006,NULL,'THINNER-1L',NULL,'Thinner X1 litro','X1 LITRO','Pintuco',4,2,1,NULL,1,0,4500.00,8000.00,0.00,NULL,43.75,10,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','FRACCION_DECIMAL','LITRO',1.0000),
(14,1000007,NULL,'CARNE-RES',NULL,'Carne de res (granel)','X1 KILO','N/A',4,2,3,NULL,1,0,14000.00,22000.00,0.00,NULL,36.36,20,NULL,1,'2026-07-26 20:12:14','2026-09-13 17:27:45','FRACCION_DECIMAL','KILO',1.0000),
(23,1000008,'7703445050014','','','PANOTIL GOTAS','X8ML','ZOMBON',3,1,3,1,1,0,20143.00,24000.00,1000.00,NULL,16.07,1,'',1,'2026-09-15 15:59:21','2026-09-15 15:59:21','UNIDAD','',1.0000),
(24,1000009,'21313464687413134','','','NEUMATICO 275/300*17 COMSTAR','Unidad','COMSTAR',6,2,1,1,1,0,9000.00,16000.00,0.00,NULL,43.75,1,'',1,'2026-09-15 20:15:08','2026-09-15 20:15:08','UNIDAD','',1.0000),
(25,1000010,'','','','DOLEX GRIPA','200 ML','GENFAR',1,1,3,2,24,0,20000.00,25000.00,1000.00,NULL,20.00,1,'',1,'2026-10-31 19:08:24','2026-10-31 19:17:01','UNIDAD','',1.0000),
(26,1000011,'','','','TINTA EPSON 544 AZUL','X100ML','EPSON',6,2,1,1,1,0,24000.00,50000.00,0.00,NULL,52.00,1,'prod_26_6ae64c917ca75.png',1,'2026-10-31 20:10:42','2026-10-31 20:16:17','CAJA','',1.0000);
/*!40000 ALTER TABLE `vb_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_productos_precios_historial`
--

DROP TABLE IF EXISTS `vb_productos_precios_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_productos_precios_historial` (
  `id_historial` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) unsigned NOT NULL,
  `origen` enum('INGRESO','EDICION_PRODUCTO','APLICACION_MASIVA','REVERSION_INGRESO') NOT NULL DEFAULT 'INGRESO',
  `id_referencia` int(10) unsigned DEFAULT NULL COMMENT 'id_ingreso que origino el cambio',
  `cantidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad ingresada en esta compra',
  `precio_compra` decimal(12,2) DEFAULT NULL COMMENT 'Lo que se pago en esta compra',
  `costo_anterior` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio antes',
  `costo_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio despues',
  `precio_venta_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unidad_anterior` decimal(12,2) DEFAULT NULL,
  `precio_unidad_nuevo` decimal(12,2) DEFAULT NULL,
  `rentabilidad` decimal(8,2) DEFAULT NULL COMMENT 'Margen usado (%)',
  `motivo` text DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `idx_ph_producto` (`id_producto`),
  KEY `idx_ph_fecha` (`created_at`),
  KEY `fk_ph_usuario` (`id_usuario`),
  CONSTRAINT `fk_ph_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ph_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_productos_precios_historial`
--

LOCK TABLES `vb_productos_precios_historial` WRITE;
/*!40000 ALTER TABLE `vb_productos_precios_historial` DISABLE KEYS */;
INSERT INTO `vb_productos_precios_historial` VALUES
(17,9,'INGRESO',19,200,5000.00,4000.00,4913.24,12000.00,14741.19,1500.00,1842.65,66.67,'Ingreso #19',3,'2026-09-13 16:39:26');
/*!40000 ALTER TABLE `vb_productos_precios_historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_proveedores`
--

DROP TABLE IF EXISTS `vb_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_proveedores` (
  `id_proveedor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `dias_pago` int(11) DEFAULT NULL COMMENT 'D??as de cr??dito',
  `rentabilidad` decimal(5,2) DEFAULT NULL COMMENT '% rentabilidad por defecto',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_proveedor`),
  KEY `idx_prov_codigo` (`codigo`),
  KEY `idx_prov_nombre` (`nombre`),
  KEY `idx_prov_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_proveedores`
--

LOCK TABLES `vb_proveedores` WRITE;
/*!40000 ALTER TABLE `vb_proveedores` DISABLE KEYS */;
INSERT INTO `vb_proveedores` VALUES
(1,'01','MEDIMARCAS',NULL,NULL,'3145070639',NULL,'SUCRE',NULL,NULL,10.00,1,NULL,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(2,'02','MULTIDROGAS',NULL,NULL,'3117090563',NULL,'CORDOBA',NULL,NULL,10.00,0,NULL,'2026-07-23 17:08:29','2026-07-23 21:04:08'),
(3,'30','COOPI DROGAS',NULL,NULL,'3664400',NULL,'ATLANTICO',NULL,NULL,10.00,1,NULL,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(4,'04','MASSALUD',NULL,NULL,'3183352013',NULL,'SUCRE',NULL,NULL,10.00,1,NULL,'2026-07-23 17:08:29','2026-07-23 17:08:29'),
(5,'55','OTROS',NULL,NULL,'0',NULL,'SUCRE',NULL,NULL,10.00,0,NULL,'2026-07-23 17:08:29','2026-07-23 21:02:30'),
(6,'000','AKITA','VICTOR','','','','','',0,NULL,1,NULL,'2026-09-15 20:09:49','2026-09-15 20:09:49');
/*!40000 ALTER TABLE `vb_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_rangos_facturacion`
--

DROP TABLE IF EXISTS `vb_rangos_facturacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_rangos_facturacion` (
  `id_rango` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('NORMAL','ELECTRONICA') NOT NULL DEFAULT 'NORMAL',
  `prefijo` varchar(10) DEFAULT NULL,
  `inicio` bigint(20) NOT NULL,
  `fin` bigint(20) NOT NULL,
  `resolucion` varchar(250) DEFAULT NULL COMMENT 'N??mero de resoluci??n DIAN',
  `fecha_resolucion` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_rango`),
  KEY `idx_rango_tipo` (`tipo`,`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_rangos_facturacion`
--

LOCK TABLES `vb_rangos_facturacion` WRITE;
/*!40000 ALTER TABLE `vb_rangos_facturacion` DISABLE KEYS */;
INSERT INTO `vb_rangos_facturacion` VALUES
(1,'NORMAL','',46500,10000000,'',NULL,1,'2026-07-23 17:08:30','2026-07-23 17:08:30'),
(2,'ELECTRONICA','SD30',1,2000,'18764106894836',NULL,1,'2026-07-23 17:08:30','2026-07-23 17:08:30');
/*!40000 ALTER TABLE `vb_rangos_facturacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_secciones`
--

DROP TABLE IF EXISTS `vb_secciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_secciones` (
  `id_seccion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_seccion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_secciones`
--

LOCK TABLES `vb_secciones` WRITE;
/*!40000 ALTER TABLE `vb_secciones` DISABLE KEYS */;
INSERT INTO `vb_secciones` VALUES
(1,'General',1,'2026-07-23 17:08:29'),
(2,'Farmacia',1,'2026-07-23 17:08:29'),
(3,'Aseo',1,'2026-07-23 17:08:29');
/*!40000 ALTER TABLE `vb_secciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_tipos_egreso`
--

DROP TABLE IF EXISTS `vb_tipos_egreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_tipos_egreso` (
  `id_tipo_egreso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `concepto` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tipo_egreso`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_tipos_egreso`
--

LOCK TABLES `vb_tipos_egreso` WRITE;
/*!40000 ALTER TABLE `vb_tipos_egreso` DISABLE KEYS */;
INSERT INTO `vb_tipos_egreso` VALUES
(1,'1001','SERVICIOS PUBLICOS','PAGO DE SERVICIOS PUBLICOS',1,'2026-07-23 17:08:30'),
(2,'1002','HONORARIOS','HONORARIOS PROFESIONALES',1,'2026-07-23 17:08:30'),
(3,'1003','FACTURAS DE VENTAS','FACTURAS DE VENTAS',1,'2026-07-23 17:08:30');
/*!40000 ALTER TABLE `vb_tipos_egreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_usuarios`
--

DROP TABLE IF EXISTS `vb_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_usuarios` (
  `id_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `nombre_completo` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=admin, 1=cajero, 2=inventario',
  `imagen` varchar(255) DEFAULT 'assets/img/user.png',
  `fecha_inicial` date DEFAULT NULL COMMENT 'Inicio de vigencia',
  `fecha_final` date DEFAULT NULL COMMENT 'Fin de vigencia',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=activo, 1=inactivo, 2=bloqueado',
  `intentos_fallidos` tinyint(1) NOT NULL DEFAULT 0,
  `ultimo_login` datetime DEFAULT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `idx_usr_tipo` (`tipo`),
  KEY `idx_usr_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_usuarios`
--

LOCK TABLES `vb_usuarios` WRITE;
/*!40000 ALTER TABLE `vb_usuarios` DISABLE KEYS */;
INSERT INTO `vb_usuarios` VALUES
(1,'admin','$2y$10$naco4gG..pBox8HqFFudI.pxdbMpA7d81aS9Bbf2.5Ju8Pype.a56','Administrador',NULL,0,'assets/img/user.png','2026-01-01','2030-12-31',0,0,'2026-11-19 17:44:45',NULL,'2026-07-23 17:08:30','2026-11-19 22:44:45'),
(2,'caja01','$2y$10$bfCQoMrxXanuvPdmVMDb/.QhqrSE/.RwMzGlBF451.X97Wt0auv.O','Cajero 1',NULL,1,'assets/img/user.png','2026-01-01','2028-12-31',0,0,'2026-09-12 15:52:44',NULL,'2026-07-23 17:08:30','2026-09-12 20:52:44'),
(3,'superadmin','$2y$10$9tfC67GXEUz0L./xxZ0Q1eRNf26PGyDO2x15JCPCAnR3gXNNwbKEG','Super Administrador','superadmin@sistema.com',3,'assets/img/user.png',NULL,NULL,0,0,'2026-10-19 08:00:39',NULL,'2026-07-23 21:22:11','2026-10-19 13:00:39');
/*!40000 ALTER TABLE `vb_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vb_vendedores`
--

DROP TABLE IF EXISTS `vb_vendedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vb_vendedores` (
  `id_vendedor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `comision` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_vendedor`),
  UNIQUE KEY `uk_vend_codigo` (`codigo`),
  KEY `idx_vend_cedula` (`cedula`),
  KEY `idx_vend_nombre` (`nombre`),
  KEY `idx_vend_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vb_vendedores`
--

LOCK TABLES `vb_vendedores` WRITE;
/*!40000 ALTER TABLE `vb_vendedores` DISABLE KEYS */;
INSERT INTO `vb_vendedores` VALUES
(1,'V0001','1234565789','PABLO','3004554688',NULL,NULL,0.00,1,'2026-09-15 20:55:17','2026-09-15 20:55:17'),
(2,'V0002','2234556','LUIS','3013851992',NULL,NULL,0.00,1,'2026-10-31 19:38:04','2026-10-31 19:40:07');
/*!40000 ALTER TABLE `vb_vendedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'inventario_ventas'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-19 18:46:16
