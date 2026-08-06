-- M2DN — Nesne Market (DNWeb)
-- Schema::ensureMarket* ile aynı yapı.
-- Mevcut kurulumlara eklemek için:
--   mysql -u root -p < database/migrate_nesne_market.sql
-- Yeni kurulumda dnweb_full_schema.sql zaten bu tabloları içerir.

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `DNWeb`;

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

-- Eski market_items kurulumlarında eksik kolonlar
-- (kolon zaten varsa hata vermemesi için uygulama Schema::ensureColumn kullanır;
--  elle kurulumda aşağıdaki ALTER'lar isteğe bağlıdır — hata alırsanız yok sayın.)

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

-- Varsayılan kategoriler (tablo boşsa)
INSERT INTO `market_categories` (`slug`, `name`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'silah' AS slug, 'Silah' AS name, 'fa-solid fa-khanda' AS icon, 1 AS sort_order, 1 AS is_active, NOW() AS created_at, NOW() AS updated_at
  UNION ALL SELECT 'zirh', 'Zırh', 'fa-solid fa-shirt', 2, 1, NOW(), NOW()
  UNION ALL SELECT 'binek', 'Binek', 'fa-solid fa-horse', 3, 1, NOW(), NOW()
  UNION ALL SELECT 'sarf', 'Sarf', 'fa-solid fa-flask', 4, 1, NOW(), NOW()
  UNION ALL SELECT 'paket', 'Paket', 'fa-solid fa-box-open', 5, 1, NOW(), NOW()
) AS seed
WHERE (SELECT COUNT(*) FROM `market_categories`) = 0;
