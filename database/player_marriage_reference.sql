-- M2DN — player.marriage referans şeması
-- Oyun dump'ında genelde zaten vardır. Panel Evlilikler menüsü bu tabloyu okur/yazar.
-- Boş player DB'de eksikse (nadir) çalıştırılabilir; mevcut satırları silmez.

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

USE `player`;

CREATE TABLE IF NOT EXISTS `marriage` (
  `is_married` TINYINT(4) NOT NULL DEFAULT 0,
  `pid1` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `pid2` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `love_point` INT(11) UNSIGNED DEFAULT NULL,
  `time` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`pid1`, `pid2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
