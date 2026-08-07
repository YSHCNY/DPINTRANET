-- Migration: Add 2FA foundation tables
-- Date: 2026-08-05
-- Notes: This file adds three new tables used for future-proof 2FA
-- Only hashed device tokens are stored. No raw tokens or secrets.

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE;
SET SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- Trusted devices table
CREATE TABLE IF NOT EXISTS `trusted_devices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('admin','standard') NOT NULL COMMENT 'Which user namespace',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID in the corresponding user table',
  `device_token_hash` CHAR(64) NOT NULL COMMENT 'Hex SHA-256 of the device token (only hashes stored)',
  `device_token_algo` VARCHAR(16) NOT NULL DEFAULT 'sha256',
  `browser_name` VARCHAR(128) NULL,
  `operating_system` VARCHAR(128) NULL,
  `device_name` VARCHAR(128) NULL,
  `registration_ip` VARBINARY(16) NULL COMMENT 'Use INET6_ATON() on write and INET6_NTOA() on read',
  `last_used_ip` VARBINARY(16) NULL COMMENT 'Use INET6_ATON()/INET6_NTOA() - IPv4/IPv6 safe',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'If NULL, treat as non-expiring (use with caution)',
  `revoked_at` TIMESTAMP NULL DEFAULT NULL,
  `revoked_reason` VARCHAR(255) NULL,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'Optional actor id who created the trusted device',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_device_hash` (`user_type`,`user_id`,`device_token_hash`),
  KEY `idx_device_hash` (`device_token_hash`),
  KEY `idx_user_lastused` (`user_type`,`user_id`,`last_used_at`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP verifications table (supports email, authenticator apps, SMS, recovery codes, etc.)
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('admin','standard') NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `otp_hash` CHAR(64) NOT NULL COMMENT 'Hash of the OTP or token (store only hashes)',
  `otp_algo` VARCHAR(16) NOT NULL DEFAULT 'sha256',
  `purpose` VARCHAR(64) NOT NULL COMMENT 'Use cases: login, enrollment, recovery, 2fa, etc.',
  `method` ENUM('email','auth_app','sms','recovery_code') NOT NULL DEFAULT 'email',
  `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  `consumed_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_ip` VARBINARY(16) NULL COMMENT 'INET6_ATON() recommended',
  `request_agent` VARCHAR(512) NULL,
  `reference` VARCHAR(128) NULL COMMENT 'Optional correlation id for requests',
  PRIMARY KEY (`id`),
  KEY `idx_user_purpose_created` (`user_type`,`user_id`,`purpose`,`created_at`),
  KEY `idx_expires_at_otp` (`expires_at`),
  KEY `idx_otp_hash` (`otp_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User security events audit table
CREATE TABLE IF NOT EXISTS `user_security_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('admin','standard') NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `event_type` VARCHAR(64) NOT NULL COMMENT 'e.g. login_success, login_failure, otp_verified, otp_failed, trusted_browser_added, trusted_browser_revoked, logout_all, password_changed',
  `event_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` VARBINARY(16) NULL,
  `user_agent` VARCHAR(512) NULL,
  `metadata` JSON NULL COMMENT 'Structured event payload: device info, reason, actor, trace ids, etc.',
  `source` VARCHAR(64) NULL COMMENT 'Optional source/service that emitted the event',
  PRIMARY KEY (`id`),
  KEY `idx_user_event_time` (`user_type`,`user_id`,`event_type`,`event_at`),
  KEY `idx_event_time` (`event_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Migration: add public_id to trusted_devices to support safe client-side references
-- Date: 2026-08-06

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE;
SET SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

ALTER TABLE trusted_devices
  ADD COLUMN `public_id` CHAR(36) NULL AFTER `id`,
  ADD UNIQUE KEY `ux_public_id` (`public_id`),
  ADD KEY `idx_user_public` (`user_type`,`user_id`,`public_id`);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- End migration


-- Notes: We intentionally avoid foreign keys to application user tables to keep
-- this schema decoupled and to avoid migrations that could fail due to differing
-- user table names/structures between admin and standard users. Application
-- logic should validate existence of `user_id`/`user_type` when needed.

-- Example: ensure 30-day expiry semantics for trusted devices if desired by
-- application: set `expires_at = DATE_ADD(created_at, INTERVAL 30 DAY)` on insert.

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- End of migration
