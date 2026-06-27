-- Car Booking Module Schema
-- MySQL

CREATE TABLE IF NOT EXISTS car_vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plate_number VARCHAR(32) NOT NULL,
  vehicle_name VARCHAR(120) NOT NULL,
  image_filename VARCHAR(255) DEFAULT NULL,
  capacity INT NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_plate_number (plate_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS car_drivers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  driver_name VARCHAR(120) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS car_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,

  date_trip DATE NOT NULL,
  date_requested DATE NOT NULL,

  destinations VARCHAR(255) NOT NULL,
  purpose VARCHAR(180) NOT NULL,
  passengers INT NOT NULL,

  departure_expected DATETIME NOT NULL,
  return_expected DATETIME NOT NULL,

  special_instructions TEXT NULL,
  remarks TEXT NULL,

  vehicle_id INT NOT NULL,
  driver_id INT NOT NULL,

  status ENUM('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  created_by INT NULL,

  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,

  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,

  CONSTRAINT fk_car_bookings_vehicle FOREIGN KEY (vehicle_id) REFERENCES car_vehicles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_car_bookings_driver FOREIGN KEY (driver_id) REFERENCES car_drivers(id) ON DELETE RESTRICT,

  KEY idx_booking_range (start_at, end_at),
  KEY idx_booking_vehicle (vehicle_id, start_at),
  KEY idx_booking_driver (driver_id, start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS car_booking_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  actor_user_id INT NOT NULL,
  action VARCHAR(64) NOT NULL,
  payload_json JSON NULL,
  created_at DATETIME NOT NULL,

  KEY idx_booking_logs_booking (booking_id),
  KEY idx_booking_logs_actor (actor_user_id),

  CONSTRAINT fk_car_booking_logs_booking FOREIGN KEY (booking_id) REFERENCES car_bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_name VARCHAR(120) NOT NULL,
  room_code VARCHAR(32) NOT NULL,
  capacity INT NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_room_code (room_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS room_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_trip DATE NOT NULL,
  date_requested DATE NOT NULL,
  purpose VARCHAR(180) NOT NULL,
  attendees INT NOT NULL,
  departure_expected DATETIME NOT NULL,
  return_expected DATETIME NOT NULL,
  special_instructions TEXT NULL,
  remarks TEXT NULL,
  room_id INT NOT NULL,
  status ENUM('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  created_by INT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,

  CONSTRAINT fk_room_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
  KEY idx_room_booking_range (start_at, end_at),
  KEY idx_room_booking_room (room_id, start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed sample drivers (uncomment if you want)
-- INSERT INTO car_drivers (driver_name, status, created_at, updated_at) VALUES
-- ('Default Driver', 'active', NOW(), NOW());

-- Migration note: to add image support to an existing DB, run:
-- ALTER TABLE car_vehicles ADD COLUMN image_filename VARCHAR(255) DEFAULT NULL AFTER vehicle_name;


