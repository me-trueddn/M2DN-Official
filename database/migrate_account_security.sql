-- DNWeb.account_security (Schema::ensure da oluşturur)
CREATE TABLE IF NOT EXISTS `account_security` (
  `account_id` INT UNSIGNED NOT NULL,
  `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `totp_secret` VARCHAR(64) NULL DEFAULT NULL,
  `totp_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_lock_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `locked_ip` VARCHAR(45) NULL DEFAULT NULL,
  `login_notify` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
