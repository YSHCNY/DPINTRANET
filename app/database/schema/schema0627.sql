-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 27, 2026 at 10:54 PM
-- Server version: 10.6.23-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.24

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
-- Table structure for table `car_bookings`
--

CREATE TABLE IF NOT EXISTS `car_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_trip` datetime NOT NULL,
  `date_requested` datetime NOT NULL,
  `destinations` varchar(255) NOT NULL,
  `purpose` varchar(180) NOT NULL,
  `passengers` int(11) NOT NULL,
  `departure_expected` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `return_expected` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `special_instructions` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `status` enum('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_by` int(11) DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_range` (`start_at`,`end_at`),
  KEY `idx_booking_vehicle` (`vehicle_id`,`start_at`),
  KEY `idx_booking_driver` (`driver_id`,`start_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `car_booking_logs`
--

CREATE TABLE IF NOT EXISTS `car_booking_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `actor_user_id` int(11) NOT NULL,
  `action` varchar(64) NOT NULL,
  `payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload_json`)),
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_logs_booking` (`booking_id`),
  KEY `idx_booking_logs_actor` (`actor_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `car_drivers`
--

CREATE TABLE IF NOT EXISTS `car_drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_name` varchar(120) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `car_vehicles`
--

CREATE TABLE IF NOT EXISTS `car_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(32) NOT NULL,
  `vehicle_name` varchar(120) NOT NULL,
  `image_filename` varchar(255) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_plate_number` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractsTbl`
--

CREATE TABLE IF NOT EXISTS `contractsTbl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contractName` varchar(255) NOT NULL,
  `contractCode` varchar(255) NOT NULL,
  `startDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `contractDescription` varchar(255) NOT NULL,
  `uploadDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('Memo','Letter','Report','Circular','Others') NOT NULL,
  `description` text DEFAULT NULL,
  `sender_email` varchar(100) NOT NULL DEFAULT 'noreply@dalton.com.ph',
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `is_confidential` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` varchar(191) DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` datetime DEFAULT NULL,
  `edited_by` varchar(191) DEFAULT NULL,
  `edit_summary` text DEFAULT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT 0,
  `draft_notified` tinyint(1) NOT NULL DEFAULT 0,
  `draft_notified_at` datetime DEFAULT NULL,
  `draft_notified_by` varchar(191) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'draft',
  `version` int(11) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `draft_recipients` text DEFAULT NULL,
  `draft_cc` text DEFAULT NULL,
  `workflow_status` varchar(64) DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_id` (`tracking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_attachments`
--

CREATE TABLE IF NOT EXISTS `document_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_attachment_versions`
--

CREATE TABLE IF NOT EXISTS `document_attachment_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `original_attachment_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `version_number` int(11) NOT NULL DEFAULT 1,
  `uploaded_by` varchar(191) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `original_attachment_id` (`original_attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_circulations`
--

CREATE TABLE IF NOT EXISTS `document_circulations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `cc` tinyint(1) DEFAULT 0,
  `status` text DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_draft_recipients`
--

CREATE TABLE IF NOT EXISTS `document_draft_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `cc` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_threads`
--

CREATE TABLE IF NOT EXISTS `document_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `actor_role` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'message',
  `content` text NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `attachment_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `version` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_document_threads_document_id` (`document_id`),
  KEY `document_id` (`document_id`),
  KEY `actor_id` (`actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_thread_entries`
--

CREATE TABLE IF NOT EXISTS `document_thread_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `actor_type` varchar(20) NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `actor_name` varchar(191) DEFAULT NULL,
  `role_label` varchar(191) DEFAULT NULL,
  `entry_kind` varchar(40) NOT NULL,
  `content` text DEFAULT NULL,
  `cycle_reference` varchar(191) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `idempotency_key` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_thread_idempotency` (`thread_id`,`idempotency_key`),
  KEY `idx_thread_entries_doc_time` (`document_id`,`created_at`),
  KEY `idx_thread_entries_thread_time` (`thread_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_thread_entry_files`
--

CREATE TABLE IF NOT EXISTS `document_thread_entry_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_entry_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `thread_entry_id` (`thread_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_thread_messages`
--

CREATE TABLE IF NOT EXISTS `document_thread_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` varchar(191) DEFAULT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `directionFrom` varchar(255) NOT NULL,
  `directionTo` varchar(255) NOT NULL,
  `category` text NOT NULL,
  `uploader` text NOT NULL,
  `position` text NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `uploadedat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filescategory`
--

CREATE TABLE IF NOT EXISTS `filescategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` text NOT NULL,
  `categoryType` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_name` varchar(120) NOT NULL,
  `room_code` varchar(32) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_room_code` (`room_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_bookings`
--

CREATE TABLE IF NOT EXISTS `room_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_trip` date NOT NULL,
  `date_requested` date NOT NULL,
  `purpose` varchar(180) NOT NULL,
  `attendees` int(11) NOT NULL,
  `departure_expected` datetime NOT NULL,
  `return_expected` datetime NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `status` enum('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_by` int(11) DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_room_booking_range` (`start_at`,`end_at`),
  KEY `idx_room_booking_room` (`room_id`,`start_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `standardUsers`
--

CREATE TABLE IF NOT EXISTS `standardUsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('admin','staff','viewer','portal_user') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `is_portal_user` tinyint(1) DEFAULT 1,
  `pin_code` varchar(10) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_department` (`department`),
  KEY `idx_status` (`status`),
  KEY `idx_portal` (`is_portal_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `systemLogs`
--

CREATE TABLE IF NOT EXISTS `systemLogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userName` text NOT NULL,
  `logDesc` text NOT NULL,
  `module` text NOT NULL,
  `logDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `UserTbl`
--

CREATE TABLE IF NOT EXISTS `UserTbl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `email` text NOT NULL,
  `position` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userLevel` int(11) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `car_bookings`
--
ALTER TABLE `car_bookings`
  ADD CONSTRAINT `fk_car_bookings_driver` FOREIGN KEY (`driver_id`) REFERENCES `car_drivers` (`id`),
  ADD CONSTRAINT `fk_car_bookings_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `car_vehicles` (`id`);

--
-- Constraints for table `car_booking_logs`
--
ALTER TABLE `car_booking_logs`
  ADD CONSTRAINT `fk_car_booking_logs_booking` FOREIGN KEY (`booking_id`) REFERENCES `car_bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_attachments`
--
ALTER TABLE `document_attachments`
  ADD CONSTRAINT `document_attachments_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_circulations`
--
ALTER TABLE `document_circulations`
  ADD CONSTRAINT `document_circulations_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_thread_entries`
--
ALTER TABLE `document_thread_entries`
  ADD CONSTRAINT `document_thread_entries_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_thread_entries_ibfk_2` FOREIGN KEY (`thread_id`) REFERENCES `document_threads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_thread_entry_files`
--
ALTER TABLE `document_thread_entry_files`
  ADD CONSTRAINT `document_thread_entry_files_ibfk_1` FOREIGN KEY (`thread_entry_id`) REFERENCES `document_thread_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_bookings`
--
ALTER TABLE `room_bookings`
  ADD CONSTRAINT `fk_room_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
