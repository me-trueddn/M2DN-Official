-- M2DN canlı migrate — 2026-08-08
-- 05/06 — Wiki kategorilerine URL slug kolonu (eski tablo yükseltmesi).
-- Sıra: 05 (03’ten sonra). CREATE TABLE (03) zaten slug içeriyorsa bu dosya no-op.
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/05_wiki_category_slug.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

SET @wiki_slug_exists := (
  SELECT COUNT(*)
  FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'wiki_categories'
    AND `COLUMN_NAME` = 'slug'
);

SET @wiki_slug_sql := IF(
  @wiki_slug_exists = 0,
  'ALTER TABLE `wiki_categories` ADD COLUMN `slug` VARCHAR(80) NOT NULL DEFAULT '''' AFTER `name`',
  'SELECT 1'
);
PREPARE wiki_slug_stmt FROM @wiki_slug_sql;
EXECUTE wiki_slug_stmt;
DEALLOCATE PREPARE wiki_slug_stmt;

UPDATE `wiki_categories`
SET `slug` = CONCAT('cat-', `id`)
WHERE `slug` = '' OR `slug` IS NULL;

SET @wiki_slug_uq := (
  SELECT COUNT(*)
  FROM `information_schema`.`STATISTICS`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'wiki_categories'
    AND `INDEX_NAME` = 'uq_wiki_cat_slug'
);

SET @wiki_slug_uq_sql := IF(
  @wiki_slug_uq = 0,
  'ALTER TABLE `wiki_categories` ADD UNIQUE KEY `uq_wiki_cat_slug` (`slug`)',
  'SELECT 1'
);
PREPARE wiki_slug_uq_stmt FROM @wiki_slug_uq_sql;
EXECUTE wiki_slug_uq_stmt;
DEALLOCATE PREPARE wiki_slug_uq_stmt;
