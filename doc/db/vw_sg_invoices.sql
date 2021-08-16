-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2020 at 05:03 PM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 5.6.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rt_rt`
--

-- --------------------------------------------------------

--
-- Structure for view `vw_sg_invoices`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`rt_rt`@`localhost` SQL SECURITY DEFINER VIEW `vw_sg_invoices`  AS  select `s`.`order_id` AS `order_id`,`s`.`date_created` AS `date_created`,`s`.`num_items_sold` AS `num_items_sold`,`s`.`net_total` AS `gross_total`,`s`.`shipping_total` AS `shipping_total`,`s`.`net_total` AS `net_total`,`s`.`status` AS `status`,`m`.`meta_value` AS `customer_id` from (`wp_wc_order_stats` `s` left join `wp_postmeta` `m` on((`s`.`order_id` = `m`.`post_id`))) where (`m`.`meta_key` = '_customer_user') order by `s`.`order_id` desc ;
COMMIT;

CREATE ALGORITHM=UNDEFINED DEFINER=`rt_rt`@`localhost` SQL SECURITY DEFINER VIEW `vw_sg_invoices`  AS  select `s`.`order_id` AS `order_id`,`s`.`date_created` AS `date_created`,`s`.`num_items_sold` AS `num_items_sold`,`s`.`net_total` AS `gross_total`,`s`.`shipping_total` AS `shipping_total`,`s`.`net_total` AS `net_total`,`s`.`status` AS `status`,`m`.`meta_value` AS `customer_id` from (`wp_wc_order_stats` `s` left join `wp_postmeta` `m` on(`s`.`order_id` = `m`.`post_id`)) where `m`.`meta_key` = '_customer_user' order by `s`.`order_id` desc ;
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
