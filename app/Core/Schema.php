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
              `banned_until` DATETIME NULL DEFAULT NULL,
              `banned_by_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `banned_by_login` VARCHAR(30) NOT NULL DEFAULT '',
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `lifted_at` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_bans_account_active` (`account_id`, `is_active`),
              KEY `idx_bans_until` (`banned_until`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci"
        );

        self::seedPenaltyTemplates($pdo);

        $seed = $pdo->prepare(
            "INSERT INTO `settings` (`group_key`, `setting_key`, `setting_value`) VALUES
              ('site', 'name', ?),
              ('site', 'tagline', ?),
              ('site', 'locale', 'tr'),
              ('site', 'theme', ?),
              ('rates', 'exp', ?),
              ('rates', 'drop', ?),
              ('rates', 'yang', ?)
             ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`"
        );
        $seed->execute([
            (string) Config::get('app.name', 'M2DN'),
            (string) Config::get('app.tagline', 'Metin2 Sunucusu'),
            (string) Config::get('theme.active', 'EasternV1'),
            (string) (Config::get('rates.exp') ?? 100),
            (string) (Config::get('rates.drop') ?? 50),
            (string) (Config::get('rates.yang') ?? 30),
        ]);
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
            ['Kalıcı hile', 'Ağır hile ihlali — kalıcı ban', 0],
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
