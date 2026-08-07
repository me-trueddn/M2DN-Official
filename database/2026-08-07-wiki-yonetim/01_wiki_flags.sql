-- M2DN canlı migrate — 2026-08-07
-- Admin: Wiki Yönetimi menü + düzenleme bayrakları
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/01_wiki_flags.sql
-- Not: Schema::ensure() site açılışında da Admin/Super gruplara ekler; Ready Only yalnızca görür, wiki_manage almaz.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

-- WebPerm ≥ 1 gruplarına (Ready Only hariç) menu_wiki + wiki_manage
INSERT IGNORE INTO `permission_group_flags` (`group_id`, `flag_key`, `is_enabled`)
SELECT `id`, 'menu_wiki', 1
FROM `permission_groups`
WHERE `web_permission` >= 1
  AND `name` <> 'Ready Only';

INSERT IGNORE INTO `permission_group_flags` (`group_id`, `flag_key`, `is_enabled`)
SELECT `id`, 'wiki_manage', 1
FROM `permission_groups`
WHERE `web_permission` >= 1
  AND `name` <> 'Ready Only';

-- Ready Only: menüyü görsün, düzenlemesin
INSERT IGNORE INTO `permission_group_flags` (`group_id`, `flag_key`, `is_enabled`)
SELECT `id`, 'menu_wiki', 1
FROM `permission_groups`
WHERE `name` = 'Ready Only';

DELETE pgf
FROM `permission_group_flags` pgf
INNER JOIN `permission_groups` pg ON pg.`id` = pgf.`group_id`
WHERE pg.`name` = 'Ready Only'
  AND pgf.`flag_key` = 'wiki_manage';
