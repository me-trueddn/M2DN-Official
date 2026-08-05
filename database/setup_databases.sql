-- M2DN localhost kurulum
-- Oyun DB'leri + DNWeb (site ayarları)
-- Türkçe karakter: utf8mb4 / utf8mb4_turkish_ci

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `account`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

CREATE DATABASE IF NOT EXISTS `common`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

CREATE DATABASE IF NOT EXISTS `player`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

CREATE DATABASE IF NOT EXISTS `log`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

CREATE DATABASE IF NOT EXISTS `DNWeb`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

USE `DNWeb`;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(64) NOT NULL DEFAULT 'general',
  `setting_key` VARCHAR(128) NOT NULL,
  `setting_value` MEDIUMTEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_group_key` (`group_key`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_login` VARCHAR(30) NOT NULL,
  `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
  `subject` VARCHAR(255) NOT NULL,
  `category` VARCHAR(64) NOT NULL DEFAULT 'Genel',
  `status` ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tickets_login` (`account_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`) VALUES
  ('site', 'name', 'M2DN'),
  ('site', 'tagline', 'Metin2 Pvp Sunucusu'),
  ('site', 'locale', 'tr'),
  ('site', 'theme', 'EasternV1'),
  ('rates', 'exp', '100'),
  ('rates', 'drop', '50'),
  ('rates', 'yang', '30')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
