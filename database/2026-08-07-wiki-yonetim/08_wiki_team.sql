-- M2DN canlı migrate — 2026-08-08
-- 08 — Wiki içerik tipi Takımımız + wiki_team_members
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/08_wiki_team.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

INSERT INTO `wiki_content_types` (`slug`, `name`, `is_active`, `created_at`, `updated_at`)
SELECT 'takimiz', 'Takımımız', 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `wiki_content_types` WHERE `slug` = 'takimiz'
);

CREATE TABLE IF NOT EXISTS `wiki_team_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `wiki_page_id` INT UNSIGNED NOT NULL,
  `nick` VARCHAR(120) NOT NULL,
  `group_key` VARCHAR(40) NOT NULL,
  `role_key` VARCHAR(60) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL DEFAULT '',
  `bio` VARCHAR(500) NOT NULL DEFAULT '',
  `joined_label` VARCHAR(120) NOT NULL DEFAULT '',
  `socials_json` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wiki_team_page` (`wiki_page_id`, `sort_order`, `is_active`),
  CONSTRAINT `fk_wiki_team_page`
    FOREIGN KEY (`wiki_page_id`) REFERENCES `wiki_pages` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
