-- M2DN canlı migrate — 2026-08-07
-- 02/06 — Eski Wiki Yönetimi içeriğini siler (settings: group_key = wiki).
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/02_drop_wiki_content.sql
-- Not: Ayrı wiki tablosu yoktu; içerik JSON olarak settings'te tutuluyordu.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

DELETE FROM `settings`
WHERE `group_key` = 'wiki';
