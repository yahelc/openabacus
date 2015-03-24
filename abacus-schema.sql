-- MySQL dump 10.13  Distrib 5.6.22, for linux-glibc2.5 (x86_64)
--
-- ------------------------------------------------------
-- Server version	5.6.19-log

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
-- Table structure for table `auth`
--

DROP TABLE IF EXISTS `auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth` (
  `auth_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`auth_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_ftp`
--

DROP TABLE IF EXISTS `client_ftp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_ftp` (
  `client_ftp_id` int(12) NOT NULL AUTO_INCREMENT,
  `client` varchar(32) DEFAULT NULL,
  `ftp_host` varchar(255) DEFAULT NULL,
  `ftp_user` varchar(255) DEFAULT NULL,
  `ftp_password` varchar(64) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `port` int(11) DEFAULT '21',
  `secure` tinyint(1) DEFAULT '0',
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_ftp_id`),
  UNIQUE KEY `client_host_user` (`client`,`ftp_host`,`ftp_user`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_query_map`
--

DROP TABLE IF EXISTS `client_query_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_query_map` (
  `client_query_map_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) DEFAULT NULL,
  `client` varchar(32) NOT NULL DEFAULT '',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_query_map_id`),
  UNIQUE KEY `query_id` (`query_id`,`client`),
  KEY `idx1` (`client_query_map_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1115 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_sftp`
--

DROP TABLE IF EXISTS `client_sftp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_sftp` (
  `client_sftp_id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(32) DEFAULT NULL,
  `sftp_host` varchar(255) DEFAULT NULL,
  `sftp_user` varchar(255) DEFAULT NULL,
  `sftp_password` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `sftp_port` int(11) DEFAULT '22',
  `public_key` text,
  `private_key` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_sftp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_user__client`
--

DROP TABLE IF EXISTS `client_user__client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_user__client` (
  `client_user__client_id` int(11) NOT NULL AUTO_INCREMENT,
  `create_user` varchar(32) DEFAULT NULL,
  `client` varchar(32) DEFAULT NULL,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_user__client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cron_log`
--

DROP TABLE IF EXISTS `cron_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_log` (
  `cron_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `scheduled_query_id` int(11) DEFAULT NULL,
  `client` varchar(32) DEFAULT NULL,
  `body` mediumtext,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cron_log_id`),
  KEY `scheduled_query_id` (`scheduled_query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26655 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custom_field`
--

DROP TABLE IF EXISTS `custom_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_field` (
  `custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `slug` varchar(32) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `value` varchar(32) DEFAULT NULL,
  `query_sql` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`custom_field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `database_credentials`
--

DROP TABLE IF EXISTS `database_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_credentials` (
  `database_credentials_id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `cert_path` varchar(255) DEFAULT NULL,
  `database_name` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_slug` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(4) DEFAULT '1',
  `is_framework` tinyint(4) DEFAULT '0',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`database_credentials_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `query`
--

DROP TABLE IF EXISTS `query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query` (
  `query_id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `query_sql` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `global` int(1) NOT NULL DEFAULT '0',
  `order_index` int(11) DEFAULT '0',
  `create_user` varchar(32) DEFAULT NULL,
  `public` int(1) DEFAULT '1',
  `min_user_role_id` int(11) DEFAULT '1',
  `description` text,
  PRIMARY KEY (`query_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2582 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER query_version_archiver BEFORE UPDATE ON query
FOR EACH ROW BEGIN
IF (NEW.query_sql != OLD.query_sql) THEN
  INSERT INTO query_version (query_id, slug, name, query_sql, create_dt, modified_dt, global, order_index, create_user, public, min_user_role_id, description) SELECT OLD.query_id, OLD.slug, OLD.name, OLD.query_sql,  OLD.create_dt, OLD.modified_dt, OLD.global, OLD.order_index, OLD.create_user, OLD.public, OLD.min_user_role_id, OLD.description FROM query WHERE query_id=OLD.query_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `query__custom_field`
--

DROP TABLE IF EXISTS `query__custom_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query__custom_field` (
  `query__custom_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_field_id` int(11) DEFAULT NULL,
  `query_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`query__custom_field_id`),
  UNIQUE KEY `query_id` (`query_id`,`custom_field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5331 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `query_log`
--

DROP TABLE IF EXISTS `query_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query_log` (
  `query_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_name` varchar(255) DEFAULT NULL,
  `client` varchar(32) DEFAULT NULL,
  `query_sql` mediumtext,
  `create_user` varchar(32) DEFAULT NULL,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `row_count` int(11) DEFAULT NULL,
  `query_time` int(11) DEFAULT NULL,
  `file` varchar(1024) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `scheduled_query_id` int(11) DEFAULT NULL,
  `post_parameters` text,
  `file_size` int(11) DEFAULT NULL,
  `complete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`query_log_id`),
  KEY `scheduled_query_id` (`scheduled_query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45955 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `query_log_scheduled_query_backfill`
--

DROP TABLE IF EXISTS `query_log_scheduled_query_backfill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query_log_scheduled_query_backfill` (
  `scheduled_query_id` int(11) NOT NULL DEFAULT '0',
  `query_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `client` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `name` varchar(1023) CHARACTER SET utf8 DEFAULT NULL,
  `query_log_id` int(11) NOT NULL DEFAULT '0',
  KEY `query_log_id` (`query_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `query_safety_whitelist`
--

DROP TABLE IF EXISTS `query_safety_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query_safety_whitelist` (
  `query_safety_whitelist_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `client` varchar(32) NOT NULL,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `create_user` varchar(128) NOT NULL,
  `note` text,
  PRIMARY KEY (`query_safety_whitelist_id`),
  UNIQUE KEY `query_id` (`query_id`,`client`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `query_version`
--

DROP TABLE IF EXISTS `query_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `query_version` (
  `query_version_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `query_sql` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `global` int(1) NOT NULL DEFAULT '0',
  `order_index` int(11) DEFAULT '0',
  `create_user` varchar(32) DEFAULT NULL,
  `public` int(1) DEFAULT '1',
  `min_user_role_id` int(11) DEFAULT '1',
  `description` text,
  `version_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`query_version_id`),
  KEY `slug` (`slug`),
  KEY `query_id` (`query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduled_query`
--

DROP TABLE IF EXISTS `scheduled_query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduled_query` (
  `scheduled_query_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) DEFAULT NULL,
  `client` varchar(32) DEFAULT NULL,
  `frequency` varchar(32) DEFAULT NULL,
  `dayofweek` int(2) DEFAULT NULL,
  `dayofmonth` int(2) DEFAULT NULL,
  `run_time` time DEFAULT NULL,
  `post_parameters` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `create_user` varchar(32) DEFAULT NULL,
  `active` int(1) DEFAULT NULL,
  `last_run_dt` datetime DEFAULT NULL,
  `next_run_dt` datetime DEFAULT NULL,
  `name` varchar(1023) DEFAULT NULL,
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`scheduled_query_id`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=470 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schema_notes`
--

DROP TABLE IF EXISTS `schema_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_notes` (
  `schema_notes_id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `description` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user` varchar(32) DEFAULT NULL,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`schema_notes_id`)
) ENGINE=InnoDB AUTO_INCREMENT=449 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `body` text,
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `create_user` varchar(32) DEFAULT NULL,
  `public` int(1) DEFAULT '1',
  `global` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timer_log`
--

DROP TABLE IF EXISTS `timer_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timer_log` (
  `timer_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `query_log_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `start_dt` datetime DEFAULT NULL,
  `end_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`timer_log_id`),
  KEY `query_log_id` (`query_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1675 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `create_user` varchar(128) NOT NULL,
  `user_role_id` int(2) DEFAULT NULL,
  `user_type_id` int(11) DEFAULT '1',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `create_user` (`create_user`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `user_role_id` int(2) NOT NULL DEFAULT '0',
  `user_role_name` varchar(14) NOT NULL DEFAULT '',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_type`
--

DROP TABLE IF EXISTS `user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type` (
  `user_type_id` int(11) NOT NULL DEFAULT '1',
  `user_type_name` varchar(14) NOT NULL DEFAULT '',
  `create_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified_dt` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-03-19 11:31:23