<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $data = null;
    private static ?string $appVersion = null;

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Config dosyası bulunamadı: ' . $path);
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new \RuntimeException('Config dosyası dizi döndürmelidir.');
        }

        self::$data = $config;
        self::$appVersion = null;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$data === null) {
            throw new \RuntimeException('Config henüz yüklenmedi.');
        }

        $segments = explode('.', $key);
        $value = self::$data;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Uygulama sürümü — config/version.json (git’te tutulur; config.php değil).
     */
    public static function version(): string
    {
        if (self::$appVersion !== null) {
            return self::$appVersion;
        }

        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.json';
        if (is_file($file)) {
            try {
                $raw = file_get_contents($file);
                $json = is_string($raw) ? json_decode($raw, true) : null;
                $ver = is_array($json) ? trim((string) ($json['version'] ?? '')) : '';
                if ($ver !== '') {
                    return self::$appVersion = $ver;
                }
            } catch (\Throwable) {
                // fallback below
            }
        }

        // Eski kurulumlar / geçiş: config app.version
        try {
            $legacy = trim((string) self::get('app.version', ''));
            if ($legacy !== '') {
                return self::$appVersion = $legacy;
            }
        } catch (\Throwable) {
            // ignore
        }

        return self::$appVersion = '0.0.0';
    }

    public static function all(): array
    {
        return self::$data ?? [];
    }
}
