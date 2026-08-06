-- M2DN — Depo şifresi MD5 için kolon genişliği
-- player.safebox.password varsayılanı varchar(6); MD5 32 karakterdir.
-- Panel / admin depo sıfırlama da ilk yazımda kolon yoksa otomatik genişletir.
-- Elle:
--   mysql -u root -p < database/migrate_safebox_password_md5.sql

SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `player`;

ALTER TABLE `safebox`
  MODIFY `password` VARCHAR(32) NOT NULL DEFAULT '';
