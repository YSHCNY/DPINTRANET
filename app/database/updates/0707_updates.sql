-- Create staff directory table for the new directory module
CREATE TABLE `staff_directory` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(32) NOT NULL,
  `firstName` varchar(150) NOT NULL,
  `lastName` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `position` varchar(120) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `deployment_date` date DEFAULT NULL,
  `status` enum('active','inactive','on_leave','contract') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_staff_id` (`staff_id`),
  KEY `idx_department` (`department`),
  KEY `idx_status` (`status`),
  KEY `idx_deployment_date` (`deployment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;