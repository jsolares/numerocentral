-- MySQL dump 10.13  Distrib 5.5.47, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: numerocentral
-- ------------------------------------------------------
-- Server version	5.5.47-0ubuntu0.14.04.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `passwd` varchar(64) DEFAULT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `android_devices`
--

DROP TABLE IF EXISTS `android_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `android_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) DEFAULT NULL,
  `deviceID` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=583 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `android_test`
--

DROP TABLE IF EXISTS `android_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `android_test` (
  `user` varchar(50) DEFAULT NULL,
  `pass` varchar(50) DEFAULT NULL,
  `call` varchar(50) DEFAULT NULL,
  `imei` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `thedate` datetime DEFAULT NULL,
  `misc` varchar(4000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `callrecords_table`
--

DROP TABLE IF EXISTS `callrecords_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callrecords_table` (
  `calldate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clid` varchar(80) NOT NULL DEFAULT '',
  `src` varchar(80) NOT NULL DEFAULT '',
  `dst` varchar(80) NOT NULL DEFAULT '',
  `dcontext` varchar(80) NOT NULL DEFAULT '',
  `channel` varchar(80) NOT NULL DEFAULT '',
  `dstchannel` varchar(80) NOT NULL DEFAULT '',
  `lastapp` varchar(80) NOT NULL DEFAULT '',
  `lastdata` varchar(80) NOT NULL DEFAULT '',
  `duration` int(11) NOT NULL DEFAULT '0',
  `billsec` int(11) NOT NULL DEFAULT '0',
  `disposition` varchar(45) NOT NULL DEFAULT '',
  `amaflags` int(11) NOT NULL DEFAULT '0',
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `uniqueid` varchar(32) NOT NULL DEFAULT '',
  `userfield` varchar(255) NOT NULL DEFAULT '',
  KEY `accountcode_index` (`accountcode`),
  KEY `calldate` (`calldate`),
  KEY `disposition` (`disposition`),
  KEY `date_account` (`accountcode`,`calldate`),
  KEY `uniqueid` (`uniqueid`) USING HASH
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `callrecords_table_stats`
--

DROP TABLE IF EXISTS `callrecords_table_stats`;
/*!50001 DROP VIEW IF EXISTS `callrecords_table_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `callrecords_table_stats` (
  `calldate` tinyint NOT NULL,
  `duration` tinyint NOT NULL,
  `dcontext` tinyint NOT NULL,
  `disposition` tinyint NOT NULL,
  `accountcode` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `compramm`
--

DROP TABLE IF EXISTS `compramm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compramm` (
  `id_compra` int(11) NOT NULL AUTO_INCREMENT,
  `id_oferta` int(11) NOT NULL,
  `uid_vendedor` int(11) NOT NULL,
  `uid_comprador` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL,
  PRIMARY KEY (`id_compra`)
) ENGINE=InnoDB AUTO_INCREMENT=3183 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conferences`
--

DROP TABLE IF EXISTS `conferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conferences` (
  `confno` varchar(80) NOT NULL DEFAULT '0',
  `pin` varchar(20) DEFAULT NULL,
  `adminpin` varchar(20) DEFAULT NULL,
  `members` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`confno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id_contact` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_group` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `email` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `address` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `number` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `blocked` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id_contact`)
) ENGINE=MyISAM AUTO_INCREMENT=33175 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etiquetas`
--

DROP TABLE IF EXISTS `etiquetas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etiquetas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `descripcion` varchar(300) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=324 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etiquetas_backup`
--

DROP TABLE IF EXISTS `etiquetas_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etiquetas_backup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) NOT NULL,
  `uniqueid` varchar(32) NOT NULL,
  `etiqueta` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1336 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etiquetas_llamadas`
--

DROP TABLE IF EXISTS `etiquetas_llamadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `etiquetas_llamadas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) NOT NULL,
  `uniqueid` varchar(32) NOT NULL,
  `etiqueta` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `etiqueta_accountcode` (`accountcode`),
  KEY `etiqueta_uniqueid` (`uniqueid`),
  KEY `uniqueid` (`uniqueid`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=88077 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fac_clientes`
--

DROP TABLE IF EXISTS `fac_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fac_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `nit` varchar(15) NOT NULL DEFAULT '',
  `email` varchar(200) NOT NULL DEFAULT '',
  `cuenta` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fac_detalles`
--

DROP TABLE IF EXISTS `fac_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fac_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_factura` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio` double(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fac_facturas`
--

DROP TABLE IF EXISTS `fac_facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fac_facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `numero` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fac_servicios`
--

DROP TABLE IF EXISTS `fac_servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fac_servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `descripcion` varchar(200) NOT NULL DEFAULT '',
  `monto` double(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `facturacion`
--

DROP TABLE IF EXISTS `facturacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facturacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) NOT NULL,
  `email` varchar(800) DEFAULT NULL,
  `misc` varchar(800) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2946 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `keypad` tinyint(4) NOT NULL,
  `id_contact` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL DEFAULT '',
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=900 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incomming_prefs`
--

DROP TABLE IF EXISTS `incomming_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incomming_prefs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) DEFAULT NULL,
  `mode` tinyint(4) NOT NULL DEFAULT '0',
  `dialmode` int(11) NOT NULL DEFAULT '0',
  `screen` int(11) NOT NULL DEFAULT '1',
  `record` tinyint(4) DEFAULT '0',
  `blockanon` tinyint(4) NOT NULL DEFAULT '0',
  `playrecording` tinyint(4) NOT NULL DEFAULT '0',
  `connplay` tinyint(4) NOT NULL DEFAULT '0',
  `missemail` tinyint(4) NOT NULL DEFAULT '1',
  `disabled` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=548 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivr_audio`
--

DROP TABLE IF EXISTS `ivr_audio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivr_audio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `recording` varchar(20) NOT NULL DEFAULT '',
  `hora_inicio` time DEFAULT '00:00:00',
  `hora_fin` time DEFAULT '23:59:59',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=544 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivr_ooaudio`
--

DROP TABLE IF EXISTS `ivr_ooaudio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivr_ooaudio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `recording` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=234 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivr_option`
--

DROP TABLE IF EXISTS `ivr_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivr_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `keypad` smallint(6) DEFAULT NULL,
  `number` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7345 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llamadas`
--

DROP TABLE IF EXISTS `llamadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llamadas` (
  `llamadas` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_recarga`
--

DROP TABLE IF EXISTS `log_recarga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_recarga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logdate` datetime DEFAULT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `id_vendedor` int(11) NOT NULL DEFAULT '0',
  `minutes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_recarga_clientes`
--

DROP TABLE IF EXISTS `log_recarga_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_recarga_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logdate` datetime DEFAULT NULL,
  `id_vendedor` int(11) NOT NULL DEFAULT '0',
  `accountcode` varchar(10) NOT NULL DEFAULT '',
  `minutes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=117 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcador_audio`
--

DROP TABLE IF EXISTS `marcador_audio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcador_audio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `recording` varchar(20) NOT NULL DEFAULT '',
  `hora_inicio` time DEFAULT '00:00:00',
  `hora_fin` time DEFAULT '23:59:59',
  `amd` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcador_event`
--

DROP TABLE IF EXISTS `marcador_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcador_event` (
  `id_event` bigint(20) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `inicio` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_event`),
  KEY `marcador_event_accountcode` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcador_event_detail`
--

DROP TABLE IF EXISTS `marcador_event_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcador_event_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_event` bigint(20) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `status_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_event` (`id_event`),
  CONSTRAINT `marcador_event_detail_ibfk_1` FOREIGN KEY (`id_event`) REFERENCES `marcador_event` (`id_event`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12075 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mminutos`
--

DROP TABLE IF EXISTS `mminutos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mminutos` (
  `id_oferta` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `minutos` int(11) NOT NULL DEFAULT '0',
  `precio` double NOT NULL DEFAULT '0',
  `fecha_ingreso` datetime NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT '0',
  `uid_comprador` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_oferta`)
) ENGINE=InnoDB AUTO_INCREMENT=9979 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nc_mynumber`
--

DROP TABLE IF EXISTS `nc_mynumber`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nc_mynumber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(10) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `preferred` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6490 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `numeros`
--

DROP TABLE IF EXISTS `numeros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `numeros` (
  `numero` varchar(20) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `id_pago` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `forma_pago` tinyint(4) NOT NULL DEFAULT '0',
  `banco` tinyint(4) NOT NULL DEFAULT '0',
  `documento` varchar(200) NOT NULL DEFAULT '',
  `monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `minutos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `factura` varchar(50) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `fecha_aplica` date NOT NULL DEFAULT '0000-00-00',
  `motivo_pago` tinyint(4) NOT NULL DEFAULT '0',
  `facturar` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_pago`)
) ENGINE=MyISAM AUTO_INCREMENT=8953 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paypal`
--

DROP TABLE IF EXISTS `paypal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(255) NOT NULL DEFAULT '',
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `ammount_usd` double NOT NULL DEFAULT '0',
  `ammount_qtz` double NOT NULL DEFAULT '0',
  `fecha_process` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT '',
  `id_pago` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `detallefac` varchar(500) DEFAULT NULL,
  `abreviacion` varchar(15) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `minutes` double DEFAULT NULL,
  `valid_days` tinyint(4) DEFAULT NULL,
  `numbers` tinyint(4) DEFAULT NULL,
  `choose_number` tinyint(4) DEFAULT '0',
  `record` tinyint(4) DEFAULT '0',
  `incomming` tinyint(4) DEFAULT '0',
  `outgoing` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recupera_clave`
--

DROP TABLE IF EXISTS `recupera_clave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recupera_clave` (
  `token` varchar(64) NOT NULL,
  `accountcode` varchar(20) DEFAULT NULL,
  `fecha_creado` datetime DEFAULT NULL,
  `fecha_utilizado` datetime DEFAULT NULL,
  `ip` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `saldo`
--

DROP TABLE IF EXISTS `saldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saldo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `saldo_minutos` int(11) NOT NULL DEFAULT '0',
  `saldo_qtz` decimal(10,2) DEFAULT NULL,
  `saldo_vencido` int(11) NOT NULL DEFAULT '0',
  `fecha_ingreso_saldo` datetime DEFAULT NULL,
  `fecha_saldo_vencido` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=905 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transpagos`
--

DROP TABLE IF EXISTS `transpagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transpagos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `empresa` tinyint(4) NOT NULL DEFAULT '-1',
  `producto` varchar(20) NOT NULL DEFAULT '',
  `celular` varchar(10) NOT NULL DEFAULT '',
  `monto` double NOT NULL DEFAULT '0',
  `estado` tinyint(4) NOT NULL DEFAULT '0',
  `transaccion` varchar(20) NOT NULL DEFAULT '',
  `result` varchar(200) NOT NULL DEFAULT '',
  `ingreso` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `proceso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=590 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `passwd` varchar(64) DEFAULT NULL,
  `email` varchar(300) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `accountcode` varchar(20) DEFAULT NULL,
  `id_plan` tinyint(4) DEFAULT '1',
  `fax` varchar(20) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `supervisa` varchar(800) DEFAULT NULL,
  `id_vendedor` int(11) NOT NULL DEFAULT '1',
  `extensiones` int(11) NOT NULL DEFAULT '0',
  `exten1digit` int(11) NOT NULL DEFAULT '0',
  `nit` varchar(20) NOT NULL DEFAULT '',
  `monto` double NOT NULL DEFAULT '0',
  `minutos` double NOT NULL DEFAULT '0',
  `marcador` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1603 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendedores`
--

DROP TABLE IF EXISTS `vendedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL DEFAULT '',
  `numero_recarga` varchar(10) NOT NULL DEFAULT '',
  `saldo` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visanet`
--

DROP TABLE IF EXISTS `visanet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visanet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(50) NOT NULL DEFAULT '',
  `txn_uuid` varchar(30) NOT NULL DEFAULT '',
  `txn_refno` varchar(40) NOT NULL DEFAULT '',
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `ammount_qtz` double NOT NULL DEFAULT '0',
  `fecha_requested` datetime DEFAULT NULL,
  `fecha_process` datetime DEFAULT NULL,
  `decision` varchar(10) NOT NULL DEFAULT '',
  `message` varchar(80) NOT NULL DEFAULT '',
  `reason_code` int(11) NOT NULL DEFAULT '0',
  `status` varchar(50) NOT NULL DEFAULT '',
  `id_pago` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1037 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voicemail_users`
--

DROP TABLE IF EXISTS `voicemail_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voicemail_users` (
  `uniqueid` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `context` varchar(50) NOT NULL DEFAULT '',
  `mailbox` int(5) NOT NULL DEFAULT '0',
  `password` varchar(4) NOT NULL DEFAULT '0',
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `pager` varchar(50) NOT NULL DEFAULT '',
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniqueid`),
  KEY `mailbox_context` (`mailbox`,`context`)
) ENGINE=MyISAM AUTO_INCREMENT=817 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webcall`
--

DROP TABLE IF EXISTS `webcall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webcall` (
  `calldate` datetime DEFAULT NULL,
  `src` varchar(20) DEFAULT NULL,
  `account` varchar(20) DEFAULT NULL,
  `ivr` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `callrecords_table_stats`
--

/*!50001 DROP TABLE IF EXISTS `callrecords_table_stats`*/;
/*!50001 DROP VIEW IF EXISTS `callrecords_table_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `callrecords_table_stats` AS select `callrecords_table`.`calldate` AS `calldate`,`callrecords_table`.`duration` AS `duration`,`callrecords_table`.`dcontext` AS `dcontext`,replace(if((`callrecords_table`.`dcontext` = 'from-pstn'),if((not((`callrecords_table`.`userfield` like '%:g1/%'))),'NO ANSWER',`callrecords_table`.`disposition`),`callrecords_table`.`disposition`),'FAILED','NO ANSWER') AS `disposition`,`callrecords_table`.`accountcode` AS `accountcode` from `callrecords_table` */;
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

-- Dump completed on 2018-01-05 16:14:13
