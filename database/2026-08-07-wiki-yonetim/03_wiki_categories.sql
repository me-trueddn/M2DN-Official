-- M2DN canlı migrate — 2026-08-07
-- 03/06 — Wiki kategorileri tablosu (ana / alt; slug + is_wiki_home dahil).
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/03_wiki_categories.sql
-- Alternatif: Schema::ensure() site açılışında tabloyu oluşturur.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

CREATE TABLE IF NOT EXISTS `wiki_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(80) NOT NULL DEFAULT '',
  `is_main` TINYINT(1) NOT NULL DEFAULT 0,
  `parent_id` INT UNSIGNED NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_wiki_home` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wiki_cat_slug` (`slug`),
  KEY `idx_wiki_cat_parent` (`parent_id`),
  KEY `idx_wiki_cat_sort` (`sort_order`, `is_active`, `is_main`),
  CONSTRAINT `fk_wiki_cat_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `wiki_categories` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
