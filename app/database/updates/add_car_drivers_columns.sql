ALTER TABLE car_drivers
  ADD COLUMN employee_id VARCHAR(30) NULL UNIQUE AFTER id,
  ADD COLUMN first_name VARCHAR(75) NOT NULL AFTER employee_id,
  ADD COLUMN last_name VARCHAR(75) NOT NULL AFTER first_name,
  ADD COLUMN mobile_number VARCHAR(20) NULL AFTER last_name,
  ADD COLUMN email VARCHAR(150) NULL AFTER mobile_number,
  ADD COLUMN license_number VARCHAR(50) NULL UNIQUE AFTER email,
  ADD COLUMN license_class VARCHAR(20) NULL AFTER license_number,
  ADD COLUMN license_expiry DATE NULL AFTER license_class;
