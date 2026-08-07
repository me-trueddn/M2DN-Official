-- M2DN canlı migrate — 2026-08-07
-- Hesaba birden fazla yetki grubu (account_staff_groups composite PK)
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-coklu-yetki-grubu/01_multi_staff_groups.sql
-- Not: Schema::ensure() site açılışında da aynı migrate'i uygular.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

CREATE TABLE IF NOT EXISTS `account_staff_groups__multi` (
  `account_id` INT UNSIGNED NOT NULL,
  `group_id` INT UNSIGNED NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`, `group_id`),
  KEY `idx_asg_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT IGNORE INTO `account_staff_groups__multi` (`account_id`, `group_id`, `updated_at`)
SELECT `account_id`, `group_id`, `updated_at` FROM `account_staff_groups`;

DROP TABLE IF EXISTS `account_staff_groups`;
RENAME TABLE `account_staff_groups__multi` TO `account_staff_groups`;
