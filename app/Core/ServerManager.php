<?php

declare(strict_types=1);

namespace App\Core;

final class ServerManager
{
    public static function all(bool $onlyEnabled = true): array
    {
        $servers = Config::get('servers', []);

        if (!$onlyEnabled) {
            return $servers;
        }

        return array_filter($servers, static fn(array $s): bool => !empty($s['enabled']));
    }

    public static function get(string $key): ?array
    {
        $servers = Config::get('servers', []);
        return $servers[$key] ?? null;
    }

    public static function defaultKey(): string
    {
        return (string) Config::get('default_server', 'main');
    }

    public static function default(): ?array
    {
        return self::get(self::defaultKey());
    }

    public static function current(): array
    {
        $key = Session::get('selected_server') ?: self::defaultKey();
        $server = self::get($key);

        if ($server === null || empty($server['enabled'])) {
            $server = self::default();
            $key = self::defaultKey();
        }

        if ($server === null) {
            throw new \RuntimeException('Aktif sunucu tanımlı değil. config.php içindeki servers bölümünü kontrol edin.');
        }

        $server['key'] = $key;
        return $server;
    }

    public static function select(string $key): void
    {
        $server = self::get($key);
        if ($server === null || empty($server['enabled'])) {
            throw new \InvalidArgumentException('Geçersiz sunucu seçimi.');
        }
        Session::set('selected_server', $key);
    }

    public static function databaseNames(string $key): array
    {
        $server = self::get($key);
        return $server['databases'] ?? [];
    }
}
