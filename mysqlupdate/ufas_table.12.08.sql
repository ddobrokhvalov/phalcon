-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               10.1.13-MariaDB - mariadb.org binary distribution
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица fas_live.ufas
CREATE TABLE IF NOT EXISTS `ufas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `number` char(2) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `ufas` (`name`, `number`, `address`, `phone`, `email`) VALUES
	('УФАС по Ямало-Ненецкому автономному округу', '89', 'ул. Губкина, д. 13, г. Салехард, Ямало-Ненецкий автономный округ, 629001', '(34922)3-47-08', 'to89@fas.gov.ru')