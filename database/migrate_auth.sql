-- WebPermission + oturum tabloları
SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- account.WebPermission: 0=user, 1=admin, 2=superadmin
-- Kolon yoksa ekle (MariaDB)
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'account' AND TABLE_NAME = 'account' AND COLUMN_NAME = 'WebPermission'
);

SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `account`.`account` ADD COLUMN `WebPermission` TINYINT NULL DEFAULT 0 COMMENT ''0=user 1=admin 2=superadmin'' AFTER `status`',
  'SELECT ''WebPermission already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `account`.`account` SET `WebPermission` = 0 WHERE `WebPermission` IS NULL;
UPDATE `account`.`account` SET `WebPermission` = 2 WHERE `login` = 'trueddn';

USE `DNWeb`;

CREATE TABLE IF NOT EXISTS `web_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent_hash` CHAR(64) NOT NULL DEFAULT '',
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token_hash` (`token_hash`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
