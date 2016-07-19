-- phpMyAdmin SQL Dump
-- version 4.4.15.5
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июл 08 2016 г., 10:23
-- Версия сервера: 5.5.48
-- Версия PHP: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `fas`
--

-- --------------------------------------------------------

--
-- Структура таблицы `arguments_categoty`
--

CREATE TABLE IF NOT EXISTS `arguments_categoty` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `arguments_categoty`
--

INSERT INTO `arguments_categoty` (`id`, `name`, `parent_id`) VALUES
(1, 'test1', 0),
(2, 'test2', 1),
(3, 'test3', 2),
(4, 'test5', 2),
(5, 'test6', 2),
(6, 'test7', 3),
(7, 'test8', 3),
(8, 'test9', 0),
(9, 'test10', 0),
(10, 'test11', 0),
(11, 'test12', 0),
(12, 'test13', 0),
(13, 'test14', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `arguments_categoty`
--
ALTER TABLE `arguments_categoty`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `arguments_categoty`
--
ALTER TABLE `arguments_categoty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
