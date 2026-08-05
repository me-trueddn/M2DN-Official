<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Çoklu sunucu + çoklu DB bağlantı yöneticisi.
 * Her sunucu: account, common, player, log (aynı şema).
 * Ayrıca web paneli için ayrı bir bağlantı.
 */
final class Database
{
    /** @var array<string, array<string, PDO>> serverKey => dbAlias => PDO */
    private static array $serverConnections = [];

    private static ?PDO $web = null;

    public static function web(): PDO
    {
        if (self::$web instanceof PDO) {
            return self::$web;
        }

        $cfg = Config::get('web_database');
        self::$web = self::connect(
            $cfg['host'],
            (int) $cfg['port'],
            $cfg['database'],
            $cfg['username'],
            $cfg['password'],
            $cfg['charset'] ?? 'utf8mb4'
        );

        return self::$web;
    }

    /**
     * Belirli bir sunucunun belirli DB'sine bağlan.
     * Örnek: Database::server('main', 'account')
     */
    public static function server(?string $serverKey = null, string $dbAlias = 'account'): PDO
    {
        $serverKey = $serverKey ?: (string) Config::get('default_server', 'main');
        $servers = Config::get('servers', []);

        if (!isset($servers[$serverKey]) || empty($servers[$serverKey]['enabled'])) {
            throw new \InvalidArgumentException("Sunucu bulunamadı veya kapalı: {$serverKey}");
        }

        if (isset(self::$serverConnections[$serverKey][$dbAlias])) {
            return self::$serverConnections[$serverKey][$dbAlias];
        }

        $server = $servers[$serverKey];
        $databases = $server['databases'] ?? [];

        if (!isset($databases[$dbAlias])) {
            throw new \InvalidArgumentException("DB alias bulunamadı: {$dbAlias} (sunucu: {$serverKey})");
        }

        $pdo = self::connect(
            $server['host'],
            (int) $server['port'],
            $databases[$dbAlias],
            $server['username'],
            $server['password'],
            $server['charset'] ?? 'utf8mb4'
        );

        self::$serverConnections[$serverKey][$dbAlias] = $pdo;

        return $pdo;
    }

    public static function account(?string $serverKey = null): PDO
    {
        return self::server($serverKey, 'account');
    }

    public static function common(?string $serverKey = null): PDO
    {
        return self::server($serverKey, 'common');
    }

    public static function player(?string $serverKey = null): PDO
    {
        return self::server($serverKey, 'player');
    }

    public static function log(?string $serverKey = null): PDO
    {
        return self::server($serverKey, 'log');
    }

    private static function connect(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
        string $charset
    ): PDO {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
            // Türkçe karakter desteği
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci");
            $pdo->exec("SET CHARACTER SET utf8mb4");
            $pdo->exec("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        } catch (PDOException $e) {
            if (Config::get('app.debug')) {
                throw $e;
            }
            throw new PDOException('Veritabanı bağlantısı kurulamadı.');
        }

        return $pdo;
    }

    public static function disconnectAll(): void
    {
        self::$serverConnections = [];
        self::$web = null;
    }
}
