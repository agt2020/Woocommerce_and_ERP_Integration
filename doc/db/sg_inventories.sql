-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2020 at 05:02 PM
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
-- Table structure for table `sg_inventories`
--

CREATE TABLE `sg_inventories` (
  `id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sg_inventories`
--

INSERT INTO `sg_inventories` (`id`, `name`, `code`, `is_default`) VALUES
('10', 'انبار سایت هیوان', '7', NULL),
('3', 'انبار آرتی اینترنتی', '2', 1),
('4', 'انبار فروش عمده آرتی', '1', NULL),
('5', 'انبار ضایعاتی', '3', NULL),
('6', 'انبار مواد اولیه', '4', NULL),
('7', 'انبار غرفه هیوان', '5', NULL),
('9', 'انبار فروش عمده هیوان', '6', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sg_inventories`
--
ALTER TABLE `sg_inventories`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
