-- M2DN canlı migrate — 2026-08-07
-- Ready Only yetki grubu (WebPermission 1, salt görüntüleme)
-- Çalıştırma:
--   mysql -u USER -p DNWeb < database/2026-08-07-ready-only-yetki/01_ready_only_group.sql
-- Not: Schema::ensure() site açılışında da aynı grubu ekler / bayrakları düzeltir.

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `dnweb`;

INSERT INTO `permission_groups` (`name`, `web_permission`, `is_system`, `created_at`, `updated_at`)
SELECT 'Ready Only', 1, 1, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `permission_groups` WHERE `name` = 'Ready Only' LIMIT 1
);

SET @ready_id := (
  SELECT `id` FROM `permission_groups` WHERE `name` = 'Ready Only' LIMIT 1
);

-- Görüntüleme bayrakları
INSERT IGNORE INTO `permission_group_flags` (`group_id`, `flag_key`, `is_enabled`) VALUES
  (@ready_id, 'read_only', 1),
  (@ready_id, 'player_detail', 1),
  (@ready_id, 'menu_oyuncular', 1),
  (@ready_id, 'menu_siralamalar', 1),
  (@ready_id, 'menu_binek', 1),
  (@ready_id, 'menu_gm', 1),
  (@ready_id, 'menu_ip_ban', 1),
  (@ready_id, 'menu_loncalar', 1),
  (@ready_id, 'menu_lonca_savaslari', 1),
  (@ready_id, 'menu_banlar', 1),
  (@ready_id, 'menu_duyurular', 1),
  (@ready_id, 'menu_destekler', 1),
  (@ready_id, 'menu_sunucu', 1),
  (@ready_id, 'menu_yasakli_kelimeler', 1),
  (@ready_id, 'menu_loglar', 1),
  (@ready_id, 'menu_nesne_market', 1);

-- Yazma bayraklarını kaldır (yanlışlıkla eklendiyse)
DELETE FROM `permission_group_flags`
WHERE `group_id` = @ready_id
  AND `flag_key` IN (
    'ban', 'announcements', 'tickets', 'site_settings',
    'reset_security_code', 'reset_safebox_password', 'disable_2fa'
  );
