-- account.WebPermission kolonu
-- 0 = kullanıcı, 1 = admin, 2 = superadmin
-- Schema::ensureAccountWebPermission ile aynı tanım

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'account'
    AND TABLE_NAME = 'account'
    AND COLUMN_NAME = 'WebPermission'
);

SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `account`.`account`
     ADD COLUMN `WebPermission` TINYINT NULL DEFAULT 0
     COMMENT ''0=user 1=admin 2=superadmin'' AFTER `status`',
  'SELECT ''WebPermission already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `account`.`account`
SET `WebPermission` = 0
WHERE `WebPermission` IS NULL;
