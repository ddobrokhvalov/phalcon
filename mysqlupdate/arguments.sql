-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Апр 21 2016 г., 15:13
-- Версия сервера: 5.6.17
-- Версия PHP: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `fas`
--

-- --------------------------------------------------------

--
-- Структура таблицы `arguments_categoty`
--

CREATE TABLE IF NOT EXISTS `arguments_categoty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `arguments_categoty`
--

INSERT INTO `arguments_categoty` (`id`, `name`) VALUES
(1, 'test1'),
(2, 'test2'),
(3, 'test3');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Апр 21 2016 г., 15:15
-- Версия сервера: 5.6.17
-- Версия PHP: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `fas`
--

-- --------------------------------------------------------

--
-- Структура таблицы `arguments`
--

CREATE TABLE IF NOT EXISTS `arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `arguments`
--

INSERT INTO `arguments` (`id`, `name`) VALUES
  (1, 'test4'),
  (2, 'test5'),
  (3, 'test6');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `arguments` ADD `category_id` INT NOT NULL , ADD `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , ADD INDEX (`category_id`) ;
UPDATE `fas`.`arguments` SET `category_id` = '1' WHERE `arguments`.`id` = 1;
UPDATE `fas`.`arguments` SET `category_id` = '2' WHERE `arguments`.`id` = 2;
UPDATE `fas`.`arguments` SET `category_id` = '3' WHERE `arguments`.`id` = 3;
ALTER TABLE `arguments` CHANGE `data` `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `arguments` ADD `text` TEXT NOT NULL AFTER `name`;