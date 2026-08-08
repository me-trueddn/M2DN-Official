-- M2DN canlı migrate — 2026-08-08
-- 06/06 — Wiki /wiki ana sayfası seçimi (is_wiki_home).
-- Sıra: 06 (05’ten sonra). CREATE TABLE (03) zaten kolon içeriyorsa no-op.
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/06_wiki_home.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

SET @wiki_home_exists := (
  SELECT COUNT(*)
  FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'wiki_categories'
    AND `COLUMN_NAME` = 'is_wiki_home'
);

SET @wiki_home_sql := IF(
  @wiki_home_exists = 0,
  'ALTER TABLE `wiki_categories` ADD COLUMN `is_wiki_home` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`',
  'SELECT 1'
);
PREPARE wiki_home_stmt FROM @wiki_home_sql;
EXECUTE wiki_home_stmt;
DEALLOCATE PREPARE wiki_home_stmt;
