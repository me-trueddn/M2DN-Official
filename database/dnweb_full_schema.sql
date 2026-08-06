-- DNWeb tam şema (Schema::ensure ile uyumlu, nihai kolonlar)
-- Sıfırdan kurulum için. Mevcut veriyi silmez (IF NOT EXISTS).
-- Seed / varsayılan içerik: site ilk açılışta Schema::ensure ekler;
--   Nesne Market varsayılan kategorileri bu dosyada da (tablo boşsa) eklenir.

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `DNWeb`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

USE `DNWeb`;

-- ---------------------------------------------------------------------------
-- Ayarlar & oturum
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(64) NOT NULL DEFAULT 'general',
  `setting_key` VARCHAR(128) NOT NULL,
  `setting_value` MEDIUMTEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_group_key` (`group_key`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

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

-- ---------------------------------------------------------------------------
-- Hesap güvenliği / onay / aktivite / ban
-- ---------------------------------------------------------------------------

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

CREATE TABLE IF NOT EXISTS `account_consents` (
  `account_id` INT UNSIGNED NOT NULL,
  `rules_accepted` TINYINT(1) NOT NULL DEFAULT 0,
  `rules_accepted_at` DATETIME NULL DEFAULT NULL,
  `rules_revision` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `online_snapshots` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
  `online_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_online_server_time` (`server_key`, `recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `account_activity_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `action` VARCHAR(64) NOT NULL,
  `detail` VARCHAR(500) NOT NULL DEFAULT '',
  `evidence` VARCHAR(1000) NOT NULL DEFAULT '',
  `actor_account_id` INT UNSIGNED NULL DEFAULT NULL,
  `actor_login` VARCHAR(30) NOT NULL DEFAULT '',
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_account` (`account_id`, `id`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `penalty_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `days` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = süresiz',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `account_bans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `penalty_id` INT UNSIGNED NULL DEFAULT NULL,
  `penalty_name` VARCHAR(120) NOT NULL DEFAULT '',
  `reason` VARCHAR(500) NOT NULL DEFAULT '',
  `evidence` VARCHAR(1000) NOT NULL DEFAULT '',
  `days` INT UNSIGNED NOT NULL DEFAULT 0,
  `banned_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `banned_by_login` VARCHAR(30) NOT NULL DEFAULT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lifted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bans_account_active` (`account_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `ip_bans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(15) NOT NULL,
  `reason` VARCHAR(500) NOT NULL DEFAULT '',
  `pcbang_ip_id` INT NULL DEFAULT NULL,
  `pcbang_id` INT NOT NULL DEFAULT 0,
  `created_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip_bans_ip` (`ip`),
  KEY `idx_ip_bans_pcbang` (`pcbang_ip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `admin_action_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_account_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `actor_login` VARCHAR(30) NOT NULL DEFAULT '',
  `target_account_id` INT UNSIGNED NULL DEFAULT NULL,
  `target_login` VARCHAR(30) NOT NULL DEFAULT '',
  `action` VARCHAR(80) NOT NULL,
  `detail` VARCHAR(1000) NOT NULL DEFAULT '',
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_aal_actor` (`actor_account_id`),
  KEY `idx_aal_target` (`target_account_id`),
  KEY `idx_aal_actor_login` (`actor_login`),
  KEY `idx_aal_target_login` (`target_login`),
  KEY `idx_aal_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Site içerik
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `site_downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `pack_type` VARCHAR(64) NOT NULL DEFAULT 'normal',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `site_features` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-star',
  `title` VARCHAR(160) NOT NULL,
  `body` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `site_classes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(32) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `body` VARCHAR(500) NOT NULL DEFAULT '',
  `rank_glyph` VARCHAR(16) NOT NULL DEFAULT '',
  `glow_color` VARCHAR(20) NOT NULL DEFAULT '#8f1c29',
  `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-star',
  `gif_path` VARCHAR(255) NOT NULL DEFAULT '',
  `stat1_label` VARCHAR(40) NOT NULL DEFAULT '',
  `stat1_value` TINYINT UNSIGNED NOT NULL DEFAULT 80,
  `stat2_label` VARCHAR(40) NOT NULL DEFAULT '',
  `stat2_value` TINYINT UNSIGNED NOT NULL DEFAULT 80,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_classes_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `site_gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL DEFAULT '',
  `image_path` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `site_footer_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `column_key` VARCHAR(40) NOT NULL DEFAULT 'community',
  `label` VARCHAR(120) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `site_social_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-brands fa-link',
  `url` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `community_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_no` INT UNSIGNED NOT NULL DEFAULT 1,
  `title` VARCHAR(200) NOT NULL,
  `detail` MEDIUMTEXT NOT NULL,
  `penalty_1` VARCHAR(200) NOT NULL DEFAULT '',
  `penalty_2` VARCHAR(200) NOT NULL DEFAULT '',
  `penalty_3` VARCHAR(200) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cr_sort` (`sort_order`, `rule_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Yetki grupları
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `permission_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `web_permission` TINYINT NOT NULL DEFAULT 1,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pg_web` (`web_permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `permission_group_flags` (
  `group_id` INT UNSIGNED NOT NULL,
  `flag_key` VARCHAR(64) NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`, `flag_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `account_staff_groups` (
  `account_id` INT UNSIGNED NOT NULL,
  `group_id` INT UNSIGNED NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`),
  KEY `idx_asg_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Destek (yeni sistem) + eski support_tickets (geriye uyumluluk)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ticket_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `ticket_statuses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(40) NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_status_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `ticket_file_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `extension` VARCHAR(16) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_ext` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_code` CHAR(11) NOT NULL,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
  `category_id` INT UNSIGNED NOT NULL,
  `status_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_code` (`public_code`),
  KEY `idx_tickets_account` (`account_id`),
  KEY `idx_tickets_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `ticket_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `is_staff` TINYINT(1) NOT NULL DEFAULT 0,
  `body` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tm_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `message_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `original_name` VARCHAR(200) NOT NULL DEFAULT '',
  `stored_path` VARCHAR(500) NOT NULL,
  `mime_detected` VARCHAR(100) NOT NULL DEFAULT '',
  `size_bytes` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ta_ticket` (`ticket_id`),
  KEY `idx_ta_message` (`message_id`)
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

-- ---------------------------------------------------------------------------
-- Duyurular (nihai şema)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `announcement_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `author_account_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_login` VARCHAR(30) NOT NULL DEFAULT '',
  `published_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ann_type` (`type_id`),
  KEY `idx_ann_active` (`is_active`),
  KEY `idx_ann_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Mail / şifre sıfırlama / bildirim
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `mail_servers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `provider` VARCHAR(32) NOT NULL DEFAULT 'custom',
  `host` VARCHAR(190) NOT NULL,
  `port` INT UNSIGNED NOT NULL DEFAULT 587,
  `encryption` VARCHAR(16) NOT NULL DEFAULT 'tls',
  `username` VARCHAR(190) NOT NULL DEFAULT '',
  `password_enc` TEXT NOT NULL,
  `from_email` VARCHAR(190) NOT NULL DEFAULT '',
  `from_name` VARCHAR(120) NOT NULL DEFAULT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mail_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `mail_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(40) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `subject` VARCHAR(200) NOT NULL DEFAULT '',
  `body_html` MEDIUMTEXT NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mail_tpl_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `mail_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_code` VARCHAR(40) NOT NULL DEFAULT '',
  `to_email` VARCHAR(190) NOT NULL DEFAULT '',
  `to_login` VARCHAR(30) NOT NULL DEFAULT '',
  `subject` VARCHAR(200) NOT NULL DEFAULT '',
  `body_html` MEDIUMTEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'ok',
  `error` VARCHAR(500) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ml_created` (`created_at`),
  KEY `idx_ml_to_email` (`to_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL DEFAULT NULL,
  `created_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_token` (`token_hash`),
  KEY `idx_pr_account` (`account_id`),
  KEY `idx_pr_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient_account_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(40) NOT NULL DEFAULT '',
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `body` VARCHAR(1000) NOT NULL DEFAULT '',
  `link` VARCHAR(500) NOT NULL DEFAULT '',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_recipient` (`recipient_account_id`, `is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `market_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(40) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-box',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_market_cat_slug` (`slug`),
  KEY `idx_market_cat_sort` (`sort_order`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO `market_categories` (`slug`, `name`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'silah' AS slug, 'Silah' AS name, 'fa-solid fa-khanda' AS icon, 1 AS sort_order, 1 AS is_active, NOW() AS created_at, NOW() AS updated_at
  UNION ALL SELECT 'zirh', 'Zırh', 'fa-solid fa-shirt', 2, 1, NOW(), NOW()
  UNION ALL SELECT 'binek', 'Binek', 'fa-solid fa-horse', 3, 1, NOW(), NOW()
  UNION ALL SELECT 'sarf', 'Sarf', 'fa-solid fa-flask', 4, 1, NOW(), NOW()
  UNION ALL SELECT 'paket', 'Paket', 'fa-solid fa-box-open', 5, 1, NOW(), NOW()
) AS seed
WHERE (SELECT COUNT(*) FROM `market_categories`) = 0;

CREATE TABLE IF NOT EXISTS `market_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `item_code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(160) NOT NULL,
  `description` TEXT NOT NULL,
  `price` INT UNSIGNED NOT NULL DEFAULT 0,
  `discount_active` TINYINT(1) NOT NULL DEFAULT 0,
  `discount_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `image_url` VARCHAR(500) NOT NULL DEFAULT '',
  `duration_type` VARCHAR(16) NOT NULL DEFAULT 'permanent',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_market_items_cat` (`category_id`, `is_active`, `sort_order`),
  KEY `idx_market_items_sort` (`sort_order`, `id`),
  KEY `idx_market_items_code` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS `market_sales_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `account_login` VARCHAR(30) NOT NULL DEFAULT '',
  `market_item_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `item_code` VARCHAR(32) NOT NULL DEFAULT '',
  `item_name` VARCHAR(160) NOT NULL DEFAULT '',
  `price` INT UNSIGNED NOT NULL DEFAULT 0,
  `cash_before` INT NOT NULL DEFAULT 0,
  `cash_after` INT NOT NULL DEFAULT 0,
  `safebox_pos` INT NOT NULL DEFAULT -1,
  `player_item_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msl_account` (`account_id`, `created_at`),
  KEY `idx_msl_item` (`market_item_id`),
  KEY `idx_msl_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
