-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mariadb:3306
-- Generation Time: Jul 27, 2026 at 08:59 AM
-- Server version: 10.6.27-MariaDB-ubu2204
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `daltondb`
--

-- --------------------------------------------------------

--
-- Table structure for table `car_drivers`
--

CREATE TABLE `car_drivers` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(30) DEFAULT NULL,
  `first_name` varchar(75) DEFAULT NULL,
  `last_name` varchar(75) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_class` varchar(20) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `driver_name` varchar(120) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_drivers`
--

INSERT INTO `car_drivers` (`id`, `employee_id`, `first_name`, `last_name`, `mobile_number`, `email`, `license_number`, `license_class`, `license_expiry`, `driver_name`, `status`, `created_at`, `updated_at`) VALUES
(11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'John Doe', 'inactive', '2026-07-08 15:42:40', '2026-07-22 03:05:32'),
(12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Eric Perez', 'active', '2026-07-16 05:58:14', '2026-07-16 05:58:14'),
(13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nil Manzan', 'active', '2026-07-16 05:58:36', '2026-07-16 05:58:36'),
(14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jun Castanarez', 'active', '2026-07-16 05:58:51', '2026-07-16 05:58:51'),
(15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jerry Barasona', 'active', '2026-07-16 05:58:58', '2026-07-16 05:58:58'),
(16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bobby Alindajao', 'inactive', '2026-07-16 05:59:09', '2026-07-22 03:05:45'),
(17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rowel Cao', 'active', '2026-07-16 05:59:19', '2026-07-16 05:59:19'),
(18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cedric Perez', 'inactive', '2026-07-16 05:59:26', '2026-07-22 03:05:47'),
(19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alfie Legazpi', 'active', '2026-07-16 05:59:44', '2026-07-16 05:59:44'),
(20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AL Norberte', 'active', '2026-07-22 03:06:32', '2026-07-22 03:06:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car_drivers`
--
ALTER TABLE `car_drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_car_drivers_employee_id` (`employee_id`),
  ADD UNIQUE KEY `uq_car_drivers_license_number` (`license_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `car_drivers`
--
ALTER TABLE `car_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
