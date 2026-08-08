-- M2DN canlı migrate — 2026-08-08
-- 04/06 — Wiki içerik tipleri + sayfalar (Basit metin).
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/04_wiki_content.sql
-- Alternatif: Schema::ensure() site açılışında tablo + seed oluşturur.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

CREATE TABLE IF NOT EXISTS `wiki_content_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(40) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wiki_ctype_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO `wiki_content_types` (`slug`, `name`, `is_active`, `created_at`, `updated_at`)
SELECT 'basit-metin', 'Basit metin', 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `wiki_content_types` WHERE `slug` = 'basit-metin'
);

CREATE TABLE IF NOT EXISTS `wiki_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `content_type_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `body_html` MEDIUMTEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wiki_page_category` (`category_id`),
  KEY `idx_wiki_page_type` (`content_type_id`),
  KEY `idx_wiki_page_active` (`is_active`),
  CONSTRAINT `fk_wiki_page_category`
    FOREIGN KEY (`category_id`) REFERENCES `wiki_categories` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wiki_page_type`
    FOREIGN KEY (`content_type_id`) REFERENCES `wiki_content_types` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
