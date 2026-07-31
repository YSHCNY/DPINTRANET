CREATE TABLE IF NOT EXISTS employee_mobilizations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id BIGINT UNSIGNED NOT NULL,
  movement_type ENUM('Mobilization','Demobilization') NOT NULL,
  movement_date DATE NOT NULL,
  remarks TEXT DEFAULT NULL,
  status ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  created_by BIGINT UNSIGNED DEFAULT NULL,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_employee_mobilizations_employee_id (employee_id),
  KEY idx_employee_mobilizations_movement_date (movement_date),
  KEY idx_employee_mobilizations_movement_type (movement_type),
  KEY idx_employee_mobilizations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
