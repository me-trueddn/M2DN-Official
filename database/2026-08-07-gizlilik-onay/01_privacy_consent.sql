-- M2DN canlı migrate — 2026-08-07
-- Gizlilik / KVKK hesap onayı + revizyon
--   mysql -u USER -p DNWeb < database/2026-08-07-gizlilik-onay/01_privacy_consent.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

SET @c1 := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_consents' AND COLUMN_NAME = 'privacy_accepted'
);
SET @s1 := IF(@c1 = 0,
  'ALTER TABLE `account_consents` ADD COLUMN `privacy_accepted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rules_revision`',
  'SELECT 1');
PREPARE stmt FROM @s1; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c2 := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_consents' AND COLUMN_NAME = 'privacy_accepted_at'
);
SET @s2 := IF(@c2 = 0,
  'ALTER TABLE `account_consents` ADD COLUMN `privacy_accepted_at` DATETIME NULL DEFAULT NULL AFTER `privacy_accepted`',
  'SELECT 1');
PREPARE stmt FROM @s2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c3 := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_consents' AND COLUMN_NAME = 'privacy_revision'
);
SET @s3 := IF(@c3 = 0,
  'ALTER TABLE `account_consents` ADD COLUMN `privacy_revision` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `privacy_accepted_at`',
  'SELECT 1');
PREPARE stmt FROM @s3; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`, `updated_at`)
SELECT 'legal', 'privacy_revision', '1', NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `settings` WHERE `group_key` = 'legal' AND `setting_key` = 'privacy_revision' LIMIT 1
);
