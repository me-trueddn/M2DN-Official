-- M2DN canlı migrate — 2026-08-07
-- Satış loglarında kupon hash araması (coupon_hash)
--   mysql -u USER -p DNWeb < database/2026-08-07-market-kuponlar/02_sales_log_coupon_hash.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'market_sales_logs'
    AND COLUMN_NAME = 'coupon_hash'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `market_sales_logs` ADD COLUMN `coupon_hash` CHAR(64) NULL DEFAULT NULL AFTER `entry_type`, ADD KEY `idx_msl_coupon_hash` (`coupon_hash`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
