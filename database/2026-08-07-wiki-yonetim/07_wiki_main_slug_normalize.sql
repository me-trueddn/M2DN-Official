-- M2DN canlı patch — ana kategori slug’larını main-{id} yap (çoklu alt kategori güvenliği)
-- İsteğe bağlı: Schema::ensure / site açılışı da aynı backfill’i uygular.
-- Çalıştırma (05/06 sonrası veya mevcut kuruluma):
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/07_wiki_main_slug_normalize.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

UPDATE `wiki_categories`
SET `slug` = CONCAT('main-', `id`)
WHERE `is_main` = 1
  AND (`slug` = '' OR `slug` IS NULL OR `slug` NOT LIKE 'main-%' OR `slug` <> CONCAT('main-', `id`));
