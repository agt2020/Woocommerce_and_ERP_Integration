-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2020 at 05:04 PM
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
-- Structure for view `vw_sg_invoices_items`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_sg_invoices_items`  AS  select `i`.`order_id` AS `order_id`,`i`.`order_item_id` AS `order_item_id`,`i`.`order_item_name` AS `order_item_name`,`i`.`order_item_type` AS `order_item_type`,`m`.`meta_key` AS `meta_key`,`m`.`meta_value` AS `meta_value` from (`wp_woocommerce_order_items` `i` left join `wp_woocommerce_order_itemmeta` `m` on((`i`.`order_item_id` = `m`.`order_item_id`))) where ((`m`.`meta_key` = '_line_subtotal') or (`m`.`meta_key` = '_qty') or (`m`.`meta_key` = '_variation_id')) ;
COMMIT;


CREATE ALGORITHM=UNDEFINED DEFINER=`rt_rt`@`localhost` SQL SECURITY DEFINER VIEW `vw_sg_invoices_items`  AS  select `i`.`order_id` AS `order_id`,`i`.`order_item_id` AS `order_item_id`,`i`.`order_item_name` AS `order_item_name`,`i`.`order_item_type` AS `order_item_type`,`m`.`meta_key` AS `meta_key`,`m`.`meta_value` AS `meta_value` from (`wp_woocommerce_order_items` `i` left join `wp_woocommerce_order_itemmeta` `m` on(`i`.`order_item_id` = `m`.`order_item_id`)) where `m`.`meta_key` = '_line_subtotal' or `m`.`meta_key` = '_qty' or `m`.`meta_key` = '_variation_id' ;
COMMIT;

CREATE ALGORITHM=UNDEFINED DEFINER=`rt_rt`@`localhost` SQL SECURITY DEFINER VIEW `vw_sg_invoices_items`  AS  select `i`.`order_id` AS `order_id`,`i`.`order_item_id` AS `order_item_id`,`i`.`order_item_name` AS `order_item_name`,`i`.`order_item_type` AS `order_item_type`,`m`.`meta_key` AS `meta_key`,`m`.`meta_value` AS `meta_value` from (`wp_woocommerce_order_items` `i` left join `wp_woocommerce_order_itemmeta` `m` on(`i`.`order_item_id` = `m`.`order_item_id`)) where `m`.`meta_key` = '_line_total' or `m`.`meta_key` = '_qty' or `m`.`meta_key` = '_variation_id' ;
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
