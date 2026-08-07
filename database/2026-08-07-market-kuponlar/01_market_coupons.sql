-- M2DN canlı migrate — 2026-08-07
-- Market kuponları (kategori + kod hash) + satış log entry_type
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-market-kuponlar/01_market_coupons.sql
-- Not: Schema::ensure() site açılışında da tabloları / kolonu ekler.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

CREATE TABLE IF NOT EXISTS `market_coupon_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `cash_amount` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mcc_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `market_coupons` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `code_hash` CHAR(64) NOT NULL,
  `code_mask` VARCHAR(40) NOT NULL DEFAULT '',
  `used_account_id` INT UNSIGNED NULL DEFAULT NULL,
  `used_account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `created_by_account_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mc_hash` (`code_hash`),
  KEY `idx_mc_cat` (`category_id`),
  KEY `idx_mc_used` (`used_at`),
  KEY `idx_mc_account` (`used_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Satış loglarına kupon / satış ayrımı
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'market_sales_logs'
    AND COLUMN_NAME = 'entry_type'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `market_sales_logs` ADD COLUMN `entry_type` VARCHAR(16) NOT NULL DEFAULT ''purchase'' AFTER `ip`, ADD KEY `idx_msl_entry` (`entry_type`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
