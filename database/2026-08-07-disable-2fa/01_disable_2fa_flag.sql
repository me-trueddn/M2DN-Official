-- M2DN canlı migrate — 2026-08-07
-- Admin: Hesap 2FA kapatma bayrağı (disable_2fa)
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-disable-2fa/01_disable_2fa_flag.sql
-- Not: Schema::ensure() site açılışında da Admin/Super gruplara ekler; Ready Only'dan çıkarır.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

-- WebPerm ≥ 1 gruplarına (Ready Only hariç) disable_2fa ver
INSERT IGNORE INTO `permission_group_flags` (`group_id`, `flag_key`, `is_enabled`)
SELECT `id`, 'disable_2fa', 1
FROM `permission_groups`
WHERE `web_permission` >= 1
  AND `name` <> 'Ready Only';

-- Ready Only'dan yazma bayrağını kaldır
DELETE pgf
FROM `permission_group_flags` pgf
INNER JOIN `permission_groups` pg ON pg.`id` = pgf.`group_id`
WHERE pg.`name` = 'Ready Only'
  AND pgf.`flag_key` = 'disable_2fa';
