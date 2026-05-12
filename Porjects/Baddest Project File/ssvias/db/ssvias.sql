-- SSVIAS: Stolen Vehicle Identification and Alert System
-- Database Schema for MySQL / XAMPP

CREATE DATABASE IF NOT EXISTS ssvias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ssvias;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `email`        VARCHAR(191) NOT NULL UNIQUE,
  `phone`        VARCHAR(20) DEFAULT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `role`         ENUM('admin','officer','owner','public') NOT NULL DEFAULT 'public',
  `avatar`       VARCHAR(255) DEFAULT NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: vehicles
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_id`     INT UNSIGNED NOT NULL,
  `plate_number` VARCHAR(20) NOT NULL,
  `vin`          VARCHAR(50) DEFAULT NULL,
  `make`         VARCHAR(100) NOT NULL,
  `model`        VARCHAR(100) NOT NULL,
  `color`        VARCHAR(50) NOT NULL,
  `year`         YEAR NOT NULL,
  `type`         ENUM('car','motorcycle','truck','bus','other') DEFAULT 'car',
  `status`       ENUM('active','stolen','recovered') NOT NULL DEFAULT 'active',
  `image_path`   VARCHAR(255) DEFAULT NULL,
  `description`  TEXT DEFAULT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX(`plate_number`),
  INDEX(`vin`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: stolen_reports
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stolen_reports` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id`      INT UNSIGNED NOT NULL,
  `reporter_id`     INT UNSIGNED NOT NULL,
  `last_seen_location` VARCHAR(255) DEFAULT NULL,
  `description`     TEXT DEFAULT NULL,
  `status`          ENUM('pending','verified','closed') NOT NULL DEFAULT 'pending',
  `reported_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: sightings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sightings` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id`   INT UNSIGNED NOT NULL,
  `reporter_id`  INT UNSIGNED DEFAULT NULL,
  `location`     VARCHAR(255) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `image_path`   VARCHAR(255) DEFAULT NULL,
  `verified`     TINYINT(1) NOT NULL DEFAULT 0,
  `sighted_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `message`      TEXT NOT NULL,
  `type`         ENUM('alert','info','success','warning') DEFAULT 'info',
  `is_read`      TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table: admin_logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id`     INT UNSIGNED NOT NULL,
  `action`       VARCHAR(255) NOT NULL,
  `target_type`  VARCHAR(50) DEFAULT NULL,
  `target_id`    INT UNSIGNED DEFAULT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Seed: Default admin user  (password: admin123)
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('System Admin', 'admin@ssvias.cm', '+237600000001', '$2y$12$LNwuS3OEFMz/ZTnJwvRaK.82Qz5z0Aa5H5jH.FdDTKiWqr2j3Nlzy', 'admin'),
('Officer Fon', 'officer@ssvias.cm', '+237677000002', '$2y$12$LNwuS3OEFMz/ZTnJwvRaK.82Qz5z0Aa5H5jH.FdDTKiWqr2j3Nlzy', 'officer'),
('John Nkemdirim', 'john@example.cm', '+237690123456', '$2y$12$LNwuS3OEFMz/ZTnJwvRaK.82Qz5z0Aa5H5jH.FdDTKiWqr2j3Nlzy', 'owner'),
('Mary Bih', 'mary@example.cm', '+237655987654', '$2y$12$LNwuS3OEFMz/ZTnJwvRaK.82Qz5z0Aa5H5jH.FdDTKiWqr2j3Nlzy', 'public');

-- Seed: Sample vehicles
INSERT INTO `vehicles` (`owner_id`, `plate_number`, `vin`, `make`, `model`, `color`, `year`, `type`, `status`, `description`) VALUES
(3, 'NW-1234-A', 'WBA3A5C59DF596039', 'Toyota', 'Camry', 'Silver', 2019, 'car', 'active', 'Daily commuter vehicle'),
(3, 'NW-5678-B', 'JN1AZ4EH6FM730608', 'Honda', 'Civic', 'Black', 2020, 'car', 'stolen', 'Stolen near Commercial Avenue'),
(3, 'NW-9900-C', '1HGBH41JXMN109186', 'Yamaha', 'R15', 'Red', 2021, 'motorcycle', 'active', 'Personal use');

-- Seed: Stolen report
INSERT INTO `stolen_reports` (`vehicle_id`, `reporter_id`, `last_seen_location`, `description`, `status`) VALUES
(2, 3, 'Commercial Avenue, Bamenda', 'Vehicle was parked and taken between 10pm-11pm on Friday', 'verified');

-- Seed: Sample sighting
INSERT INTO `sightings` (`vehicle_id`, `reporter_id`, `location`, `description`, `verified`) VALUES
(2, 4, 'Up Station, Bamenda', 'Saw this black Civic speeding through Up Station area around noon', 1);

-- Seed: Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(3, 'Theft Report Verified', 'Your stolen vehicle report for plate NW-5678-B has been verified by authorities.', 'success'),
(3, 'New Sighting Alert', 'Your stolen vehicle (NW-5678-B) has been sighted at Up Station, Bamenda.', 'alert'),
(3, 'Welcome to SSVIAS', 'Your account has been created. Register your vehicles to get started.', 'info');
