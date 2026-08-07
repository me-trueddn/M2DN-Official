<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Throwable;

/**
 * Eksik veritabanı / tablo / kolonları oluşturur.
 * Mevcut verilere dokunmaz (IF NOT EXISTS / kolon kontrolü).
 */
final class Schema
{
    private static bool $booted = false;

    public static function ensure(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        try {
            $pdo = self::serverConnection();
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci");
            $pdo->exec("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");

            self::ensureWebDatabase($pdo);
            self::ensureWebTables($pdo);
            self::ensureAccountWebPermission($pdo);
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                throw $e;
            }
            // Canlıda sessiz geç — bağlantı yoksa site yine açılabilir (statik)
            error_log('M2DN Schema::ensure failed: ' . $e->getMessage());
        }
    }

    private static function serverConnection(): PDO
    {
        $cfg = Config::get('web_database');
        $host = (string) ($cfg['host'] ?? '127.0.0.1');
        $port = (int) ($cfg['port'] ?? 3306);
        $user = (string) ($cfg['username'] ?? 'root');
        $pass = (string) ($cfg['password'] ?? '');
        $charset = (string) ($cfg['charset'] ?? 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private static function ensureWebDatabase(PDO $pdo): void
    {
        $db = (string) Config::get('web_database.database', 'DNWeb');
        $safe = str_replace('`', '``', $db);
        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci"
        );
        $pdo->exec("USE `{$safe}`");
    }

    private static function ensureWebTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `settings` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `group_key` VARCHAR(64) NOT NULL DEFAULT 'general',
              `setting_key` VARCHAR(128) NOT NULL,
              `setting_value` MEDIUMTEXT NULL,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_settings_group_key` (`group_key`, `setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `announcements` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(255) NOT NULL,
              `content` MEDIUMTEXT NOT NULL,
              `is_published` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `support_tickets` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_login` VARCHAR(30) NOT NULL,
              `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
              `subject` VARCHAR(255) NOT NULL,
              `category` VARCHAR(64) NOT NULL DEFAULT 'Genel',
              `status` ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_tickets_login` (`account_login`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `web_sessions` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_id` INT UNSIGNED NOT NULL,
              `token_hash` CHAR(64) NOT NULL,
              `ip` VARCHAR(45) NOT NULL DEFAULT '',
              `user_agent_hash` CHAR(64) NOT NULL DEFAULT '',
              `expires_at` DATETIME NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `last_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_token_hash` (`token_hash`),
              KEY `idx_account_id` (`account_id`),
              KEY `idx_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `account_security` (
              `account_id` INT UNSIGNED NOT NULL,
              `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
              `totp_secret` VARCHAR(64) NULL DEFAULT NULL,
              `totp_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
              `ip_lock_enabled` TINYINT(1) NOT NULL DEFAULT 0,
              `locked_ip` VARCHAR(45) NULL DEFAULT NULL,
              `login_notify` TINYINT(1) NOT NULL DEFAULT 0,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `account_consents` (
              `account_id` INT UNSIGNED NOT NULL,
              `rules_accepted` TINYINT(1) NOT NULL DEFAULT 0,
              `rules_accepted_at` DATETIME NULL DEFAULT NULL,
              `rules_revision` INT UNSIGNED NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        self::ensureColumn($pdo, 'account_consents', 'rules_revision', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `rules_accepted_at`');
        self::ensureCommunityRulesRevision($pdo);

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `online_snapshots` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
              `online_count` INT UNSIGNED NOT NULL DEFAULT 0,
              `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_online_server_time` (`server_key`, `recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `account_activity_log` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `action` VARCHAR(64) NOT NULL,
              `detail` VARCHAR(500) NOT NULL DEFAULT '',
              `evidence` VARCHAR(1000) NOT NULL DEFAULT '',
              `actor_account_id` INT UNSIGNED NULL DEFAULT NULL,
              `actor_login` VARCHAR(30) NOT NULL DEFAULT '',
              `ip` VARCHAR(45) NOT NULL DEFAULT '',
              `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_activity_account` (`account_id`, `id`),
              KEY `idx_activity_action` (`action`),
              KEY `idx_activity_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        self::ensureColumn($pdo, 'account_activity_log', 'evidence', "VARCHAR(1000) NOT NULL DEFAULT '' AFTER `detail`");
        self::ensureColumn($pdo, 'account_activity_log', 'actor_account_id', 'INT UNSIGNED NULL DEFAULT NULL AFTER `evidence`');
        self::ensureColumn($pdo, 'account_activity_log', 'actor_login', "VARCHAR(30) NOT NULL DEFAULT '' AFTER `actor_account_id`");

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `penalty_templates` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `reason` VARCHAR(500) NOT NULL,
              `days` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = süresiz',
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `account_bans` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `penalty_id` INT UNSIGNED NULL DEFAULT NULL,
              `penalty_name` VARCHAR(120) NOT NULL DEFAULT '',
              `reason` VARCHAR(500) NOT NULL DEFAULT '',
              `evidence` VARCHAR(1000) NOT NULL DEFAULT '',
              `days` INT UNSIGNED NOT NULL DEFAULT 0,
              `banned_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `banned_by_login` VARCHAR(30) NOT NULL DEFAULT '',
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `lifted_at` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_bans_account_active` (`account_id`, `is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        self::migrateBanUntilToAvailDt($pdo);
        self::dropBannedUntilColumn($pdo);
        self::migratePermanentAvail1938($pdo);

        self::seedPenaltyTemplates($pdo);
        self::ensureSiteContentTables($pdo);
        self::seedSiteContent($pdo);
        self::ensurePermissionAndTicketTables($pdo);
        self::seedPermissionAndTickets($pdo);
        self::ensureAdminActionLogs($pdo);
        self::ensureIpBans($pdo);
        self::ensureAnnouncementTables($pdo);
        self::ensureMailAndNotificationTables($pdo);
        self::ensureMarketCategories($pdo);
        self::ensureMarketItems($pdo);
        self::ensureMarketSalesLogs($pdo);
        self::ensureMarketCoupons($pdo);

        $seed = $pdo->prepare(
            "INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`) VALUES
              ('site', 'name', ?),
              ('site', 'tagline', ?),
              ('site', 'locale', 'tr'),
              ('site', 'theme', ?),
              ('rates', 'exp', ?),
              ('rates', 'drop', ?),
              ('rates', 'yang', ?),
              ('rates', 'metin_label', 'Yüksek'),
              ('rates', 'metin_pct', '85'),
              ('chapter', 'title', 'Yeni harita & boss güncellemesi'),
              ('footer', 'copyright', ?),
              ('footer', 'brand_text', ?),
              ('captcha', 'enabled', '0'),
              ('captcha', 'provider', 'google'),
              ('captcha', 'site_key', ''),
              ('captcha', 'secret_key', '')
             ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`"
        );
        $appName = (string) Config::get('app.name', 'M2DN');
        $year = date('Y');
        $seed->execute([
            $appName,
            (string) Config::get('app.tagline', 'Metin2 Sunucusu'),
            (string) Config::get('theme.active', 'EasternV1'),
            (string) (Config::get('rates.exp') ?? 100),
            (string) (Config::get('rates.drop') ?? 50),
            (string) (Config::get('rates.yang') ?? 30),
            "© {$year} {$appName}. Tüm hakları saklıdır.",
            "{$appName} — oyuncusuyla birlikte büyüyen bağımsız bir Metin2 sunucusu. Resmi Metin2 markasıyla bağlantısı yoktur; hayran yapımı bir projedir.",
        ]);
    }

    private static function ensureSiteContentTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_downloads` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(160) NOT NULL,
              `url` VARCHAR(500) NOT NULL,
              `description` VARCHAR(500) NOT NULL DEFAULT '',
              `pack_type` VARCHAR(64) NOT NULL DEFAULT 'normal',
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_features` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-star',
              `title` VARCHAR(160) NOT NULL,
              `body` VARCHAR(500) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_classes` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `slug` VARCHAR(32) NOT NULL,
              `name` VARCHAR(80) NOT NULL,
              `body` VARCHAR(500) NOT NULL DEFAULT '',
              `rank_glyph` VARCHAR(16) NOT NULL DEFAULT '',
              `glow_color` VARCHAR(20) NOT NULL DEFAULT '#8f1c29',
              `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-star',
              `gif_path` VARCHAR(255) NOT NULL DEFAULT '',
              `stat1_label` VARCHAR(40) NOT NULL DEFAULT '',
              `stat1_value` TINYINT UNSIGNED NOT NULL DEFAULT 80,
              `stat2_label` VARCHAR(40) NOT NULL DEFAULT '',
              `stat2_value` TINYINT UNSIGNED NOT NULL DEFAULT 80,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_site_classes_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_gallery` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(160) NOT NULL DEFAULT '',
              `image_path` VARCHAR(500) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_footer_links` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `column_key` VARCHAR(40) NOT NULL DEFAULT 'community',
              `label` VARCHAR(120) NOT NULL,
              `url` VARCHAR(500) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `site_social_links` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(80) NOT NULL,
              `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-brands fa-link',
              `url` VARCHAR(500) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `community_rules` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `rule_no` INT UNSIGNED NOT NULL DEFAULT 1,
              `title` VARCHAR(200) NOT NULL,
              `detail` MEDIUMTEXT NOT NULL,
              `penalty_1` VARCHAR(200) NOT NULL DEFAULT '',
              `penalty_2` VARCHAR(200) NOT NULL DEFAULT '',
              `penalty_3` VARCHAR(200) NOT NULL DEFAULT '',
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_cr_sort` (`sort_order`, `rule_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
    }

    private static function seedSiteContent(PDO $pdo): void
    {
        if (!(int) $pdo->query('SELECT COUNT(*) FROM site_features')->fetchColumn()) {
            $features = [
                ['fa-solid fa-map-location-dot', 'Özgün Haritalar', 'Orijinal oyunda olmayan, sıfırdan tasarlanmış metin bölgeleri ve gizli alanlar.', 1],
                ['fa-solid fa-shield-halved', 'Sıfır Pay-to-Win', 'Mağazada sadece kozmetik ürünler. Güç her zaman emekle ve stratejiyle kazanılır.', 2],
                ['fa-solid fa-headset', '7/24 GM Desteği', 'Discord üzerinden anında destek, şeffaf yönetim ve haftalık geliştirici günlükleri.', 3],
                ['fa-solid fa-dragon', 'Haftalık Etkinlikler', 'Klan savaşları, ejderha avı ve özel boss rushlar her hafta sonu sizi bekliyor.', 4],
                ['fa-solid fa-lock', 'Gelişmiş Anti-Cheat', 'Özel geliştirilmiş koruma sistemiyle bot ve hileye karşı sıfır tolerans.', 5],
                ['fa-solid fa-users', 'Canlı Topluluk', 'Binlerce aktif oyuncu, düzenli forum içerikleri ve aktif bir klan ekosistemi.', 6],
            ];
            $stmt = $pdo->prepare(
                'INSERT INTO site_features (icon, title, body, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,1,NOW(),NOW())'
            );
            foreach ($features as $f) {
                $stmt->execute($f);
            }
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM site_classes')->fetchColumn()) {
            $classes = [
                ['warrior', 'Savaşçı', 'Ham güç ve çelik disiplini. Ön saflarda durur, düşmanın kalbine yürür.', '壹', '#8f1c29', 'fa-solid fa-khanda', 'img/classes/warrior_m.gif', 'Güç', 92, 'Savunma', 80, 1],
                ['ninja', 'Ninja', 'Gölgelerin çevikliği. Görünmeden vurur, iz bırakmadan kaybolur.', '貳', '#33594a', 'fa-solid fa-wind', 'img/classes/ninja_m.gif', 'Çeviklik', 95, 'Kritik', 88, 2],
                ['sura', 'Sura', 'Karanlığın büyüsüyle konuşur. Ölümü kendi silahına çevirir.', '參', '#4a1f66', 'fa-solid fa-skull', 'img/classes/sura_f.gif', 'Büyü Gücü', 90, 'Can Emme', 84, 3],
                ['shaman', 'Şaman', 'Doğanın dengesini korur. İyileştirir, güçlendirir, savaşı yönlendirir.', '肆', '#1f5a3d', 'fa-solid fa-leaf', 'img/classes/shaman_f.gif', 'İyileştirme', 93, 'Destek', 87, 4],
            ];
            $stmt = $pdo->prepare(
                'INSERT INTO site_classes
                  (slug, name, body, rank_glyph, glow_color, icon, gif_path, stat1_label, stat1_value, stat2_label, stat2_value, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,NOW(),NOW())'
            );
            foreach ($classes as $c) {
                $stmt->execute($c);
            }
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM site_downloads')->fetchColumn()) {
            $pdo->prepare(
                'INSERT INTO site_downloads (title, url, description, pack_type, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,1,1,NOW(),NOW())'
            )->execute([
                'Ana İndirme',
                '#',
                'Resmi istemci paketi',
                'normal',
            ]);
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM site_footer_links')->fetchColumn()) {
            $links = [
                ['server', 'Özellikler', '#ozellikler', 1],
                ['server', 'Sınıflar', '#siniflar', 2],
                ['server', 'Oranlar', '#oranlar', 3],
                ['server', 'Galeri', '#galeri', 4],
                ['community', 'Forum', '#', 1],
                ['community', 'Kurallar', '/kurallar', 2],
                ['community', 'Gizlilik / KVKK', '/gizlilik', 3],
                ['community', 'Destek Talebi', '#', 4],
                ['community', 'Bağış', '#', 5],
            ];
            $stmt = $pdo->prepare(
                'INSERT INTO site_footer_links (column_key, label, url, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,1,NOW(),NOW())'
            );
            foreach ($links as $l) {
                $stmt->execute($l);
            }
        } else {
            // Mevcut "Kurallar" linkini /kurallar yap
            try {
                $pdo->exec(
                    "UPDATE site_footer_links
                     SET url = '/kurallar', updated_at = NOW()
                     WHERE label LIKE '%Kural%'
                       AND url <> '/kurallar'"
                );
            } catch (\Throwable) {
                // ignore
            }
            // Gizlilik / KVKK footer linki
            try {
                $hasPrivacy = (int) $pdo->query(
                    "SELECT COUNT(*) FROM site_footer_links
                     WHERE label LIKE '%Gizlilik%' OR label LIKE '%KVKK%' OR url = '/gizlilik'"
                )->fetchColumn();
                if ($hasPrivacy === 0) {
                    $sort = (int) $pdo->query(
                        "SELECT COALESCE(MAX(sort_order),0)+1 FROM site_footer_links WHERE column_key='community'"
                    )->fetchColumn();
                    $pdo->prepare(
                        'INSERT INTO site_footer_links (column_key, label, url, sort_order, is_active, created_at, updated_at)
                         VALUES (?,?,?,?,1,NOW(),NOW())'
                    )->execute(['community', 'Gizlilik / KVKK', '/gizlilik', $sort]);
                } else {
                    $pdo->exec(
                        "UPDATE site_footer_links
                         SET url = '/gizlilik', updated_at = NOW()
                         WHERE (label LIKE '%Gizlilik%' OR label LIKE '%KVKK%')
                           AND url <> '/gizlilik'"
                    );
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        self::seedCommunityRules($pdo);
        \App\Services\LegalContentService::ensureSeeded();
        if (!(int) $pdo->query('SELECT COUNT(*) FROM site_social_links')->fetchColumn()) {
            $socials = [
                ['Discord', 'fa-brands fa-discord', '#', 1, 1],
                ['YouTube', 'fa-brands fa-youtube', '#', 2, 1],
                ['Instagram', 'fa-brands fa-instagram', '#', 3, 1],
                ['X', 'fa-brands fa-x-twitter', '#', 4, 1],
            ];
            $stmt = $pdo->prepare(
                'INSERT INTO site_social_links (name, icon, url, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,NOW(),NOW())'
            );
            foreach ($socials as $s) {
                $stmt->execute($s);
            }
        }
    }

    private static function seedCommunityRules(PDO $pdo): void
    {
        try {
            $count = (int) $pdo->query('SELECT COUNT(*) FROM community_rules')->fetchColumn();
        } catch (\Throwable) {
            return;
        }
        if ($count > 0) {
            return;
        }
        $defaults = \App\Services\CommunityRulesService::defaults();
        $stmt = $pdo->prepare(
            'INSERT INTO community_rules
              (rule_no, title, detail, penalty_1, penalty_2, penalty_3, sort_order, is_active, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,1,NOW(),NOW())'
        );
        $n = 1;
        foreach ($defaults as $row) {
            $stmt->execute([
                $n,
                $row['title'],
                $row['detail'],
                $row['penalty_1'],
                $row['penalty_2'],
                $row['penalty_3'],
                $n,
            ]);
            $n++;
        }
    }

    private static function ensurePermissionAndTicketTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `permission_groups` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `web_permission` TINYINT NOT NULL DEFAULT 1,
              `is_system` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_pg_web` (`web_permission`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `permission_group_flags` (
              `group_id` INT UNSIGNED NOT NULL,
              `flag_key` VARCHAR(64) NOT NULL,
              `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`group_id`, `flag_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `account_staff_groups` (
              `account_id` INT UNSIGNED NOT NULL,
              `group_id` INT UNSIGNED NOT NULL,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`account_id`),
              KEY `idx_asg_group` (`group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ticket_categories` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `description` VARCHAR(500) NOT NULL DEFAULT '',
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ticket_statuses` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `code` VARCHAR(40) NOT NULL,
              `label` VARCHAR(120) NOT NULL,
              `is_system` TINYINT(1) NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_ticket_status_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ticket_file_types` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `extension` VARCHAR(16) NOT NULL,
              `mime_type` VARCHAR(100) NOT NULL,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_ticket_ext` (`extension`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `tickets` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `public_code` CHAR(11) NOT NULL,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `server_key` VARCHAR(32) NOT NULL DEFAULT 'main',
              `category_id` INT UNSIGNED NOT NULL,
              `status_id` INT UNSIGNED NOT NULL,
              `subject` VARCHAR(200) NOT NULL,
              `body` MEDIUMTEXT NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `closed_at` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_ticket_code` (`public_code`),
              KEY `idx_tickets_account` (`account_id`),
              KEY `idx_tickets_status` (`status_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ticket_messages` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `ticket_id` BIGINT UNSIGNED NOT NULL,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `is_staff` TINYINT(1) NOT NULL DEFAULT 0,
              `body` MEDIUMTEXT NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_tm_ticket` (`ticket_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ticket_attachments` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `ticket_id` BIGINT UNSIGNED NOT NULL,
              `message_id` BIGINT UNSIGNED NULL DEFAULT NULL,
              `original_name` VARCHAR(200) NOT NULL DEFAULT '',
              `stored_path` VARCHAR(500) NOT NULL,
              `mime_detected` VARCHAR(100) NOT NULL DEFAULT '',
              `size_bytes` INT UNSIGNED NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_ta_ticket` (`ticket_id`),
              KEY `idx_ta_message` (`message_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
    }

    private static function seedPermissionAndTickets(PDO $pdo): void
    {
        if (!(int) $pdo->query('SELECT COUNT(*) FROM permission_groups')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO permission_groups (name, web_permission, is_system, created_at, updated_at) VALUES
                  ('Default User', 0, 1, NOW(), NOW()),
                  ('Admin', 1, 1, NOW(), NOW()),
                  ('Ready Only', 1, 1, NOW(), NOW()),
                  ('Super Admin', 2, 1, NOW(), NOW())"
            );
            $adminId = (int) $pdo->query("SELECT id FROM permission_groups WHERE name = 'Admin' AND is_system = 1 LIMIT 1")->fetchColumn();
            $readyId = (int) $pdo->query("SELECT id FROM permission_groups WHERE name = 'Ready Only' AND is_system = 1 LIMIT 1")->fetchColumn();
            $superId = (int) $pdo->query("SELECT id FROM permission_groups WHERE name = 'Super Admin' AND is_system = 1 LIMIT 1")->fetchColumn();
            $flags = [
                'ban', 'player_detail', 'reset_security_code', 'reset_safebox_password', 'disable_2fa',
                'announcements', 'tickets', 'site_settings',
                'menu_oyuncular', 'menu_siralamalar', 'menu_binek', 'menu_gm', 'menu_ip_ban', 'menu_loncalar', 'menu_lonca_savaslari', 'menu_banlar', 'menu_duyurular', 'menu_destekler', 'menu_sunucu', 'menu_yasakli_kelimeler', 'menu_loglar', 'menu_nesne_market',
            ];
            $readyFlags = [
                'read_only', 'player_detail',
                'menu_oyuncular', 'menu_siralamalar', 'menu_binek', 'menu_gm', 'menu_ip_ban', 'menu_loncalar', 'menu_lonca_savaslari', 'menu_banlar', 'menu_duyurular', 'menu_destekler', 'menu_sunucu', 'menu_yasakli_kelimeler', 'menu_loglar', 'menu_nesne_market',
            ];
            $stmt = $pdo->prepare(
                'INSERT INTO permission_group_flags (group_id, flag_key, is_enabled) VALUES (?,?,1)'
            );
            foreach ($flags as $f) {
                if ($adminId > 0) {
                    $stmt->execute([$adminId, $f]);
                }
                if ($superId > 0) {
                    $stmt->execute([$superId, $f]);
                }
            }
            foreach ($readyFlags as $f) {
                if ($readyId > 0) {
                    $stmt->execute([$readyId, $f]);
                }
            }
        }

        // Mevcut kurulumlarda Ready Only grubunu ekle
        self::ensureReadyOnlyGroup($pdo);

        // Mevcut admin gruplarına yeni menü bayraklarını ekle (Ready Only hariç)
        try {
            $adminGroups = $pdo->query(
                "SELECT id FROM permission_groups
                 WHERE web_permission >= 1 AND name <> 'Ready Only'"
            )->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $insFlag = $pdo->prepare(
                'INSERT IGNORE INTO permission_group_flags (group_id, flag_key, is_enabled)
                 VALUES (?, ?, 1)'
            );
            foreach ($adminGroups as $gid) {
                $insFlag->execute([(int) $gid, 'menu_loncalar']);
                $insFlag->execute([(int) $gid, 'menu_lonca_savaslari']);
                $insFlag->execute([(int) $gid, 'menu_binek']);
                $insFlag->execute([(int) $gid, 'menu_yasakli_kelimeler']);
                $insFlag->execute([(int) $gid, 'menu_gm']);
                $insFlag->execute([(int) $gid, 'menu_siralamalar']);
                $insFlag->execute([(int) $gid, 'menu_ip_ban']);
                $insFlag->execute([(int) $gid, 'menu_nesne_market']);
                $insFlag->execute([(int) $gid, 'reset_security_code']);
                $insFlag->execute([(int) $gid, 'reset_safebox_password']);
                $insFlag->execute([(int) $gid, 'disable_2fa']);
            }
        } catch (\Throwable) {
            // ignore
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM ticket_statuses')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO ticket_statuses (code, label, is_system, is_active, sort_order, created_at, updated_at) VALUES
                  ('new', 'Yeni', 1, 1, 1, NOW(), NOW()),
                  ('waiting_player', 'Oyuncudan bilgi bekleniyor', 1, 1, 2, NOW(), NOW()),
                  ('waiting_staff', 'Cevaplandı — dönüş bekleniyor', 1, 1, 3, NOW(), NOW()),
                  ('closed', 'Kapandı / Çözümlendi', 1, 1, 4, NOW(), NOW())"
            );
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM ticket_categories')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO ticket_categories (name, description, sort_order, is_active, created_at, updated_at) VALUES
                  ('Genel', 'Genel sorular ve talepler', 1, 1, NOW(), NOW()),
                  ('Teknik', 'Bağlantı, istemci ve teknik sorunlar', 2, 1, NOW(), NOW()),
                  ('Eşya / Hesap', 'Eşya kaybı, hesap sorunları', 3, 1, NOW(), NOW()),
                  ('Ödeme', 'Cash / bağış / ödeme bildirimleri', 4, 1, NOW(), NOW())"
            );
        }

        if (!(int) $pdo->query('SELECT COUNT(*) FROM ticket_file_types')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO ticket_file_types (extension, mime_type, is_active, created_at) VALUES
                  ('png', 'image/png', 1, NOW()),
                  ('jpg', 'image/jpeg', 1, NOW()),
                  ('jpeg', 'image/jpeg', 1, NOW()),
                  ('webp', 'image/webp', 1, NOW()),
                  ('gif', 'image/gif', 1, NOW()),
                  ('pdf', 'application/pdf', 1, NOW()),
                  ('txt', 'text/plain', 1, NOW())"
            );
        }
    }

    /** Canlı / mevcut DNWeb: Ready Only sistem grubu (WebPerm 1, salt görüntüleme). */
    private static function ensureReadyOnlyGroup(PDO $pdo): void
    {
        try {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM permission_groups WHERE name = 'Ready Only'"
            )->fetchColumn();
            if ($exists > 0) {
                $readyId = (int) $pdo->query(
                    "SELECT id FROM permission_groups WHERE name = 'Ready Only' LIMIT 1"
                )->fetchColumn();
            } else {
                $pdo->exec(
                    "INSERT INTO permission_groups (name, web_permission, is_system, created_at, updated_at)
                     VALUES ('Ready Only', 1, 1, NOW(), NOW())"
                );
                $readyId = (int) $pdo->lastInsertId();
            }
            if ($readyId <= 0) {
                return;
            }
            $readyFlags = [
                'read_only', 'player_detail',
                'menu_oyuncular', 'menu_siralamalar', 'menu_binek', 'menu_gm', 'menu_ip_ban',
                'menu_loncalar', 'menu_lonca_savaslari', 'menu_banlar', 'menu_duyurular',
                'menu_destekler', 'menu_sunucu', 'menu_yasakli_kelimeler', 'menu_loglar', 'menu_nesne_market',
            ];
            $ins = $pdo->prepare(
                'INSERT IGNORE INTO permission_group_flags (group_id, flag_key, is_enabled) VALUES (?, ?, 1)'
            );
            foreach ($readyFlags as $f) {
                $ins->execute([$readyId, $f]);
            }
            // Yazma bayraklarını Ready Only'dan kaldır (yanlışlıkla eklendiyse)
            $pdo->prepare(
                "DELETE FROM permission_group_flags
                 WHERE group_id = ? AND flag_key IN (
                   'ban','announcements','tickets','site_settings',
                   'reset_security_code','reset_safebox_password','disable_2fa'
                 )"
            )->execute([$readyId]);
        } catch (\Throwable) {
            // ignore
        }
    }

    private static function ensureAnnouncementTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `announcement_types` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `announcements` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `type_id` INT UNSIGNED NOT NULL,
              `title` VARCHAR(200) NOT NULL,
              `body` MEDIUMTEXT NOT NULL,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `author_account_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `author_login` VARCHAR(30) NOT NULL DEFAULT '',
              `published_at` DATETIME NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_ann_type` (`type_id`),
              KEY `idx_ann_active` (`is_active`),
              KEY `idx_ann_published` (`published_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        // Eski şema (title/content/is_published) → yeni şema
        self::migrateLegacyAnnouncements($pdo);

        if (!(int) $pdo->query('SELECT COUNT(*) FROM announcement_types')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO announcement_types (name, sort_order, is_active, created_at, updated_at) VALUES
                  ('Planlı Bakım Çalışması', 1, 1, NOW(), NOW()),
                  ('Plansız Kesinti Duyurusu', 2, 1, NOW(), NOW()),
                  ('Genel Duyurular', 3, 1, NOW(), NOW()),
                  ('Atama Duyuruları', 4, 1, NOW(), NOW())"
            );
        }
    }

    private static function migrateLegacyAnnouncements(PDO $pdo): void
    {
        if (!self::tableHasColumn($pdo, 'announcements', 'type_id')) {
            $pdo->exec(
                "ALTER TABLE `announcements`
                  ADD COLUMN `type_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
                  ADD COLUMN `body` MEDIUMTEXT NULL AFTER `title`,
                  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `body`,
                  ADD COLUMN `author_account_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_active`,
                  ADD COLUMN `author_login` VARCHAR(30) NOT NULL DEFAULT '' AFTER `author_account_id`,
                  ADD COLUMN `published_at` DATETIME NULL DEFAULT NULL AFTER `author_login`"
            );
        }

        if (self::tableHasColumn($pdo, 'announcements', 'content')) {
            $pdo->exec('UPDATE `announcements` SET `body` = `content` WHERE `body` IS NULL OR `body` = \'\'');
            $pdo->exec('ALTER TABLE `announcements` DROP COLUMN `content`');
        }

        if (self::tableHasColumn($pdo, 'announcements', 'is_published')) {
            $pdo->exec('UPDATE `announcements` SET `is_active` = `is_published`');
            $pdo->exec(
                'UPDATE `announcements` SET `published_at` = `created_at`
                 WHERE `is_active` = 1 AND `published_at` IS NULL'
            );
            $pdo->exec('ALTER TABLE `announcements` DROP COLUMN `is_published`');
        }

        // body NOT NULL garantisi
        if (self::tableHasColumn($pdo, 'announcements', 'body')) {
            $pdo->exec("UPDATE `announcements` SET `body` = '' WHERE `body` IS NULL");
            try {
                $pdo->exec('ALTER TABLE `announcements` MODIFY `body` MEDIUMTEXT NOT NULL');
            } catch (\Throwable) {
                // ignore if already NOT NULL
            }
        }

        // İndeksler
        self::ensureIndex($pdo, 'announcements', 'idx_ann_type', '(`type_id`)');
        self::ensureIndex($pdo, 'announcements', 'idx_ann_active', '(`is_active`)');
        self::ensureIndex($pdo, 'announcements', 'idx_ann_published', '(`published_at`)');
    }

    private static function tableHasColumn(PDO $pdo, string $table, string $column): bool
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return false;
        }
        $colCheck = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = " . $pdo->quote($webDb) . "
               AND TABLE_NAME = " . $pdo->quote($table) . "
               AND COLUMN_NAME = " . $pdo->quote($column)
        )->fetchColumn();

        return (int) $colCheck > 0;
    }

    private static function ensureIndex(PDO $pdo, string $table, string $index, string $columnsSql): void
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $index)) {
            return;
        }
        $exists = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = " . $pdo->quote($webDb) . "
               AND TABLE_NAME = " . $pdo->quote($table) . "
               AND INDEX_NAME = " . $pdo->quote($index)
        )->fetchColumn();
        if (!(int) $exists) {
            $pdo->exec("ALTER TABLE `{$webDb}`.`{$table}` ADD INDEX `{$index}` {$columnsSql}");
        }
    }

    private static function ensureAdminActionLogs(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `admin_action_logs` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `actor_account_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `actor_login` VARCHAR(30) NOT NULL DEFAULT '',
              `target_account_id` INT UNSIGNED NULL DEFAULT NULL,
              `target_login` VARCHAR(30) NOT NULL DEFAULT '',
              `action` VARCHAR(80) NOT NULL,
              `detail` VARCHAR(1000) NOT NULL DEFAULT '',
              `ip` VARCHAR(45) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_aal_actor` (`actor_account_id`),
              KEY `idx_aal_target` (`target_account_id`),
              KEY `idx_aal_actor_login` (`actor_login`),
              KEY `idx_aal_target_login` (`target_login`),
              KEY `idx_aal_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
    }

    private static function ensureIpBans(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `ip_bans` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `ip` VARCHAR(15) NOT NULL,
              `reason` VARCHAR(500) NOT NULL DEFAULT '',
              `pcbang_ip_id` INT NULL DEFAULT NULL,
              `pcbang_id` INT NOT NULL DEFAULT 0,
              `created_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_ip_bans_ip` (`ip`),
              KEY `idx_ip_bans_pcbang` (`pcbang_ip_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
    }

    private static function ensureMarketCategories(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `market_categories` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `slug` VARCHAR(40) NOT NULL,
              `name` VARCHAR(120) NOT NULL,
              `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-solid fa-box',
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_market_cat_slug` (`slug`),
              KEY `idx_market_cat_sort` (`sort_order`, `is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        if (!(int) $pdo->query('SELECT COUNT(*) FROM market_categories')->fetchColumn()) {
            $pdo->exec(
                "INSERT INTO market_categories (slug, name, icon, sort_order, is_active, created_at, updated_at) VALUES
                  ('silah', 'Silah', 'fa-solid fa-khanda', 1, 1, NOW(), NOW()),
                  ('zirh', 'Zırh', 'fa-solid fa-shirt', 2, 1, NOW(), NOW()),
                  ('binek', 'Binek', 'fa-solid fa-horse', 3, 1, NOW(), NOW()),
                  ('sarf', 'Sarf', 'fa-solid fa-flask', 4, 1, NOW(), NOW()),
                  ('paket', 'Paket', 'fa-solid fa-box-open', 5, 1, NOW(), NOW())"
            );
        }
    }

    private static function ensureMarketItems(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `market_items` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `category_id` INT UNSIGNED NOT NULL,
              `item_code` VARCHAR(32) NOT NULL DEFAULT '',
              `name` VARCHAR(160) NOT NULL,
              `description` TEXT NOT NULL,
              `price` INT UNSIGNED NOT NULL DEFAULT 0,
              `discount_active` TINYINT(1) NOT NULL DEFAULT 0,
              `discount_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
              `image_url` VARCHAR(500) NOT NULL DEFAULT '',
              `duration_type` VARCHAR(16) NOT NULL DEFAULT 'permanent',
              `sort_order` INT NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_market_items_cat` (`category_id`, `is_active`, `sort_order`),
              KEY `idx_market_items_sort` (`sort_order`, `id`),
              KEY `idx_market_items_code` (`item_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        self::ensureColumn($pdo, 'market_items', 'duration_type', "VARCHAR(16) NOT NULL DEFAULT 'permanent' AFTER `image_url`");
        self::ensureColumn($pdo, 'market_items', 'item_code', "VARCHAR(32) NOT NULL DEFAULT '' AFTER `category_id`");
        try {
            $pdo->exec('ALTER TABLE `market_items` ADD KEY `idx_market_items_code` (`item_code`)');
        } catch (\Throwable) {
            // index already exists
        }
    }

    private static function ensureMarketSalesLogs(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `market_sales_logs` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `market_item_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `item_code` VARCHAR(32) NOT NULL DEFAULT '',
              `item_name` VARCHAR(160) NOT NULL DEFAULT '',
              `price` INT UNSIGNED NOT NULL DEFAULT 0,
              `cash_before` INT NOT NULL DEFAULT 0,
              `cash_after` INT NOT NULL DEFAULT 0,
              `safebox_pos` INT NOT NULL DEFAULT -1,
              `player_item_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `ip` VARCHAR(45) NOT NULL DEFAULT '',
              `entry_type` VARCHAR(16) NOT NULL DEFAULT 'purchase',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_msl_account` (`account_id`, `created_at`),
              KEY `idx_msl_item` (`market_item_id`),
              KEY `idx_msl_created` (`created_at`),
              KEY `idx_msl_entry` (`entry_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
        self::ensureColumn($pdo, 'market_sales_logs', 'entry_type', "VARCHAR(16) NOT NULL DEFAULT 'purchase' AFTER `ip`");
    }

    private static function ensureMarketCoupons(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `market_coupon_categories` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `cash_amount` INT UNSIGNED NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_mcc_active` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `market_coupons` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `category_id` INT UNSIGNED NOT NULL,
              `code_hash` CHAR(64) NOT NULL,
              `code_mask` VARCHAR(40) NOT NULL DEFAULT '',
              `used_account_id` INT UNSIGNED NULL DEFAULT NULL,
              `used_account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `created_by_account_id` INT UNSIGNED NULL DEFAULT NULL,
              `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `used_at` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_mc_hash` (`code_hash`),
              KEY `idx_mc_cat` (`category_id`),
              KEY `idx_mc_used` (`used_at`),
              KEY `idx_mc_account` (`used_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );
    }

    private static function ensureMailAndNotificationTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `mail_servers` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(120) NOT NULL,
              `provider` VARCHAR(32) NOT NULL DEFAULT 'custom',
              `host` VARCHAR(190) NOT NULL,
              `port` INT UNSIGNED NOT NULL DEFAULT 587,
              `encryption` VARCHAR(16) NOT NULL DEFAULT 'tls',
              `username` VARCHAR(190) NOT NULL DEFAULT '',
              `password_enc` TEXT NOT NULL,
              `from_email` VARCHAR(190) NOT NULL DEFAULT '',
              `from_name` VARCHAR(120) NOT NULL DEFAULT '',
              `is_active` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_mail_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `mail_templates` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `code` VARCHAR(40) NOT NULL,
              `name` VARCHAR(120) NOT NULL,
              `subject` VARCHAR(200) NOT NULL DEFAULT '',
              `body_html` MEDIUMTEXT NOT NULL,
              `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_mail_tpl_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `mail_logs` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `template_code` VARCHAR(40) NOT NULL DEFAULT '',
              `to_email` VARCHAR(190) NOT NULL DEFAULT '',
              `to_login` VARCHAR(30) NOT NULL DEFAULT '',
              `subject` VARCHAR(200) NOT NULL DEFAULT '',
              `body_html` MEDIUMTEXT NULL,
              `status` VARCHAR(20) NOT NULL DEFAULT 'ok',
              `error` VARCHAR(500) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_ml_created` (`created_at`),
              KEY `idx_ml_to_email` (`to_email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        self::ensureColumn($pdo, 'mail_logs', 'body_html', 'MEDIUMTEXT NULL AFTER `subject`');
        try {
            $pdo->exec('ALTER TABLE `mail_logs` ADD KEY `idx_ml_to_email` (`to_email`)');
        } catch (\Throwable) {
            // index already exists
        }

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `password_resets` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `account_id` INT UNSIGNED NOT NULL,
              `account_login` VARCHAR(30) NOT NULL DEFAULT '',
              `token_hash` CHAR(64) NOT NULL,
              `expires_at` DATETIME NOT NULL,
              `used_at` DATETIME NULL DEFAULT NULL,
              `created_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `created_by_login` VARCHAR(30) NOT NULL DEFAULT '',
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_pr_token` (`token_hash`),
              KEY `idx_pr_account` (`account_id`),
              KEY `idx_pr_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `notifications` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `recipient_account_id` INT UNSIGNED NOT NULL,
              `type` VARCHAR(40) NOT NULL DEFAULT '',
              `title` VARCHAR(200) NOT NULL DEFAULT '',
              `body` VARCHAR(1000) NOT NULL DEFAULT '',
              `link` VARCHAR(500) NOT NULL DEFAULT '',
              `is_read` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_notif_recipient` (`recipient_account_id`, `is_read`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        self::seedMailTemplates($pdo);
    }

    private static function seedMailTemplates(PDO $pdo): void
    {
        $bodies = \App\Services\MailService::defaultBodies();
        $defaults = [
            ['register', 'Kayıt bildirimi', 'Hoş geldin {{login}}', $bodies['register']],
            ['password_reset', 'Şifre sıfırlama', 'Şifre sıfırlama talebi', $bodies['password_reset']],
            ['ban', 'Banlandınız bildirimi', 'Hesabın banlandı', $bodies['ban']],
            ['unban', 'Ban açıldı bildirimi', 'Banın kaldırıldı', $bodies['unban']],
            ['ticket_created', 'Ticket oluştu', 'Yeni destek talebi {{code}}', $bodies['ticket_created']],
            ['ticket_replied', 'Ticket cevaplandı', 'Ticket {{code}} yanıtlandı', $bodies['ticket_replied']],
            ['ticket_closed', 'Ticket kapandı', 'Ticket {{code}} kapatıldı', $bodies['ticket_closed']],
        ];
        $insert = $pdo->prepare(
            'INSERT IGNORE INTO mail_templates (code, name, subject, body_html, is_enabled)
             VALUES (?,?,?,?,0)'
        );
        foreach ($defaults as $row) {
            $insert->execute($row);
        }

        // Kart tabanı v2: eski / açık-arka-planlı / bozuk şablonları senkronla
        try {
            $upd = $pdo->prepare(
                'UPDATE mail_templates SET subject=?, body_html=?, updated_at=NOW() WHERE code=?'
            );
            $subjects = [
                'register' => 'Hoş geldin {{login}}',
                'password_reset' => 'Şifre sıfırlama talebi',
                'ban' => 'Hesabın banlandı',
                'unban' => 'Banın kaldırıldı',
                'ticket_created' => 'Yeni destek talebi {{code}}',
                'ticket_replied' => 'Ticket {{code}} yanıtlandı',
                'ticket_closed' => 'Ticket {{code}} kapatıldı',
            ];
            foreach ($bodies as $code => $body) {
                $stmt = $pdo->prepare('SELECT body_html FROM mail_templates WHERE code=? LIMIT 1');
                $stmt->execute([$code]);
                $current = (string) ($stmt->fetchColumn() ?: '');
                $needsSync = $current === ''
                    || !str_contains($current, 'm2dn-mail-card-v2')
                    || str_contains($current, '#f4efe6')
                    || str_contains($current, '127.0.0.1')
                    || \App\Services\MailService::isBrokenEmailHtml($current);
                if ($needsSync) {
                    $upd->execute([
                        $subjects[$code] ?? 'Bildirim',
                        $body,
                        $code,
                    ]);
                }
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Aktif banların banned_until bilgisini account.availDt'ye taşır (oyun sunucusu buraya bakar).
     */
    private static function migrateBanUntilToAvailDt(PDO $pdo): void
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }

        $colCheck = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = " . $pdo->quote($webDb) . "
               AND TABLE_NAME = 'account_bans'
               AND COLUMN_NAME = 'banned_until'"
        )->fetchColumn();
        if (!(int) $colCheck) {
            return; // zaten migrate edilmiş
        }

        $flag = $pdo->query(
            "SELECT setting_value FROM `{$webDb}`.`settings`
             WHERE group_key='migrations' AND setting_key='ban_availDt_v1' LIMIT 1"
        )->fetchColumn();
        if ((string) $flag === '1') {
            return;
        }

        try {
            $rows = $pdo->query(
                "SELECT account_id, account_login, banned_until, days
                 FROM `{$webDb}`.`account_bans`
                 WHERE is_active = 1"
            )->fetchAll() ?: [];
        } catch (\Throwable) {
            return;
        }

        $servers = Config::get('servers', []);
        if (!is_array($servers)) {
            $servers = [];
        }

        foreach ($rows as $row) {
            $aid = (int) ($row['account_id'] ?? 0);
            $login = (string) ($row['account_login'] ?? '');
            if ($aid <= 0) {
                continue;
            }
            $until = (string) ($row['banned_until'] ?? '');
            $days = (int) ($row['days'] ?? 0);
            // Süreli: banned_until → availDt; süresiz: 10 Kasım 1938
            $avail = \App\Services\PenaltyService::AVAIL_PERMANENT;
            if ($days > 0 && $until !== '' && $until !== '0000-00-00 00:00:00' && strtotime($until)) {
                $avail = date('Y-m-d H:i:s', (int) strtotime($until));
            }

            foreach ($servers as $server) {
                if (!is_array($server) || empty($server['enabled'])) {
                    continue;
                }
                $accountDb = (string) ($server['databases']['account'] ?? 'account');
                if ($accountDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $accountDb)) {
                    continue;
                }
                try {
                    $upd = $pdo->prepare(
                        "UPDATE `{$accountDb}`.`account`
                         SET status = 'BLOCK', availDt = ?
                         WHERE id = ?"
                    );
                    $upd->execute([$avail, $aid]);
                    if ($upd->rowCount() === 0 && $login !== '') {
                        $updLogin = $pdo->prepare(
                            "UPDATE `{$accountDb}`.`account`
                             SET status = 'BLOCK', availDt = ?
                             WHERE login = ?"
                        );
                        $updLogin->execute([$avail, $login]);
                    }
                } catch (\Throwable) {
                    // sunucu DB yoksa geç
                }
            }
        }

        try {
            $pdo->prepare(
                "INSERT INTO `{$webDb}`.`settings` (group_key, setting_key, setting_value)
                 VALUES ('migrations', 'ban_availDt_v1', '1')
                 ON DUPLICATE KEY UPDATE setting_value = '1'"
            )->execute();
        } catch (\Throwable) {
            // ignore
        }
    }

    /** Aktif süresiz banların availDt'sini 1938-11-10 yapar. */
    private static function migratePermanentAvail1938(PDO $pdo): void
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }

        try {
            $flag = $pdo->query(
                "SELECT setting_value FROM `{$webDb}`.`settings`
                 WHERE group_key='migrations' AND setting_key='ban_availDt_permanent_1938' LIMIT 1"
            )->fetchColumn();
            if ((string) $flag === '1') {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $perm = \App\Services\PenaltyService::AVAIL_PERMANENT;
        try {
            $rows = $pdo->query(
                "SELECT account_id, account_login FROM `{$webDb}`.`account_bans`
                 WHERE is_active = 1 AND days = 0"
            )->fetchAll() ?: [];
        } catch (\Throwable) {
            return;
        }

        $servers = Config::get('servers', []);
        if (!is_array($servers)) {
            $servers = [];
        }

        foreach ($rows as $row) {
            $aid = (int) ($row['account_id'] ?? 0);
            $login = (string) ($row['account_login'] ?? '');
            if ($aid <= 0) {
                continue;
            }
            foreach ($servers as $server) {
                if (!is_array($server) || empty($server['enabled'])) {
                    continue;
                }
                $accountDb = (string) ($server['databases']['account'] ?? 'account');
                if ($accountDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $accountDb)) {
                    continue;
                }
                try {
                    $upd = $pdo->prepare(
                        "UPDATE `{$accountDb}`.`account`
                         SET status = 'BLOCK', availDt = ?
                         WHERE id = ?"
                    );
                    $upd->execute([$perm, $aid]);
                    if ($upd->rowCount() === 0 && $login !== '') {
                        $pdo->prepare(
                            "UPDATE `{$accountDb}`.`account`
                             SET status = 'BLOCK', availDt = ?
                             WHERE login = ?"
                        )->execute([$perm, $login]);
                    }
                } catch (\Throwable) {
                    // ignore
                }
            }
        }

        // days=0 ceza şablonlarını süresiz / 1938 sentinel olarak netleştir
        try {
            $pdo->exec(
                "UPDATE `{$webDb}`.`penalty_templates`
                 SET name = IF(name = '' OR name IS NULL, 'Süresiz ban', name),
                     reason = IF(
                       reason IS NULL OR reason = '' OR reason LIKE '%kalıcı ban%',
                       'Süresiz ban — availDt 10.11.1938',
                       reason
                     )
                 WHERE days = 0"
            );
        } catch (\Throwable) {
            // ignore
        }

        try {
            $pdo->prepare(
                "INSERT INTO `{$webDb}`.`settings` (group_key, setting_key, setting_value)
                 VALUES ('migrations', 'ban_availDt_permanent_1938', '1')
                 ON DUPLICATE KEY UPDATE setting_value = '1'"
            )->execute();
        } catch (\Throwable) {
            // ignore
        }
    }

    private static function dropBannedUntilColumn(PDO $pdo): void
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }
        $colCheck = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = " . $pdo->quote($webDb) . "
               AND TABLE_NAME = 'account_bans'
               AND COLUMN_NAME = 'banned_until'"
        )->fetchColumn();
        if (!(int) $colCheck) {
            return;
        }
        try {
            $pdo->exec("ALTER TABLE `{$webDb}`.`account_bans` DROP COLUMN `banned_until`");
        } catch (\Throwable) {
            // ignore
        }
        try {
            $pdo->exec("ALTER TABLE `{$webDb}`.`account_bans` DROP INDEX `idx_bans_until`");
        } catch (\Throwable) {
            // ignore
        }
    }

    private static function ensureAccountWebPermission(PDO $pdo): void
    {
        $servers = Config::get('servers', []);
        if (!is_array($servers)) {
            return;
        }

        foreach ($servers as $server) {
            if (!is_array($server) || empty($server['enabled'])) {
                continue;
            }

            $accountDb = (string) ($server['databases']['account'] ?? 'account');
            if ($accountDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $accountDb)) {
                continue;
            }

            // account DB / tablosu yoksa dokunma (oyun dump'ı ayrı kurulur)
            $dbCheck = $pdo->query(
                "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = " . $pdo->quote($accountDb)
            )->fetchColumn();
            if (!$dbCheck) {
                continue;
            }

            $tableCheck = $pdo->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = " . $pdo->quote($accountDb) . " AND TABLE_NAME = 'account'"
            )->fetchColumn();
            if (!(int) $tableCheck) {
                continue;
            }

            $colCheck = $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = " . $pdo->quote($accountDb) . "
                   AND TABLE_NAME = 'account'
                   AND COLUMN_NAME = 'WebPermission'"
            )->fetchColumn();

            if (!(int) $colCheck) {
                $pdo->exec(
                    "ALTER TABLE `{$accountDb}`.`account`
                     ADD COLUMN `WebPermission` TINYINT NULL DEFAULT 0
                     COMMENT '0=user 1=admin 2=superadmin' AFTER `status`"
                );
            }

            $pdo->exec(
                "UPDATE `{$accountDb}`.`account` SET `WebPermission` = 0 WHERE `WebPermission` IS NULL"
            );
        }
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
    {
        $webDb = (string) (Config::get('web_database.database') ?? 'DNWeb');
        if ($webDb === '' || !preg_match('/^[A-Za-z0-9_]+$/', $webDb)) {
            $webDb = 'DNWeb';
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return;
        }

        $colCheck = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = " . $pdo->quote($webDb) . "
               AND TABLE_NAME = " . $pdo->quote($table) . "
               AND COLUMN_NAME = " . $pdo->quote($column)
        )->fetchColumn();

        if (!(int) $colCheck) {
            $pdo->exec("ALTER TABLE `{$webDb}`.`{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    /** İlk kurulumda mevcut onaylı hesapları mevcut revizyona hizala (zorunlu yeniden kabul tetiklenmesin). */
    private static function ensureCommunityRulesRevision(PDO $pdo): void
    {
        try {
            $stmt = $pdo->prepare(
                "SELECT setting_value FROM settings WHERE group_key = 'community' AND setting_key = 'rules_revision' LIMIT 1"
            );
            $stmt->execute();
            $val = $stmt->fetchColumn();
            if ($val !== false && $val !== null && $val !== '') {
                return;
            }
            $pdo->prepare(
                "INSERT INTO settings (group_key, setting_key, setting_value, updated_at)
                 VALUES ('community', 'rules_revision', '1', NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
            )->execute();
            $pdo->exec(
                'UPDATE account_consents SET rules_revision = 1 WHERE rules_accepted = 1 AND rules_revision = 0'
            );
        } catch (\Throwable) {
            // ignore
        }
    }

    private static function seedPenaltyTemplates(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM penalty_templates')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $defaults = [
            ['Bot kullanımı', 'Otomatik bot / macro kullanımı', 3],
            ['Küfür / taciz', 'Küfür, hakaret veya taciz', 1],
            ['Hile', 'Hile / duvar içi / hız hilesi', 7],
            ['Kalıcı hile', 'Ağır hile ihlali — süresiz ban (availDt: 10.11.1938)', 0],
            ['Ticaret dolandırıcılığı', 'Oyuncu dolandırıcılığı / trade scam', 5],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO penalty_templates (name, reason, days, is_active, created_at, updated_at)
             VALUES (?, ?, ?, 1, NOW(), NOW())'
        );
        foreach ($defaults as $row) {
            $stmt->execute($row);
        }
    }
}
