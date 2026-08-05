<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $data = null;

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

    public static function all(): array
    {
        return self::$data ?? [];
    }
}
