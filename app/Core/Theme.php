<?php

declare(strict_types=1);

namespace App\Core;

final class Theme
{
    public static function active(): string
    {
        $active = (string) Config::get('theme.active', 'EasternV1');
        $path = self::path($active);

        if (!is_dir($path)) {
            $fallback = (string) Config::get('theme.fallback', 'EasternV1');
            if (!is_dir(self::path($fallback))) {
                throw new \RuntimeException("Tema bulunamadı: {$active}");
            }
            return $fallback;
        }

        return $active;
    }

    public static function path(?string $theme = null): string
    {
        $theme = $theme ?: self::active();
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme;
    }

    public static function viewPath(string $view, ?string $theme = null): string
    {
        $view = str_replace(['.', '\\'], ['/', '/'], $view);
        $file = self::path($theme) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("View bulunamadı: {$view}");
        }

        return $file;
    }

    public static function render(string $view, array $data = []): void
    {
        $data['appName'] = Config::get('app.name', 'M2DN');
        $data['appTagline'] = Config::get('app.tagline', 'Metin2 Sunucusu');
        $data['theme'] = self::active();
        $data['themeUrl'] = self::assetUrl();
        $data['rates'] = Config::get('rates', []);
        $data['servers'] = ServerManager::all();
        $data['currentServer'] = ServerManager::current();
        $data['csrf'] = Security::csrfField();

        extract($data, EXTR_SKIP);
        require self::viewPath($view);
    }

    public static function assetUrl(string $path = ''): string
    {
        $theme = self::active();
        $url = '/themes/' . rawurlencode($theme) . '/assets';
        $path = ltrim($path, '/');
        return $path === '' ? $url : $url . '/' . $path;
    }

    public static function info(?string $theme = null): array
    {
        $meta = self::path($theme) . DIRECTORY_SEPARATOR . 'theme.json';
        if (!is_file($meta)) {
            return ['name' => $theme ?: self::active()];
        }
        $json = json_decode((string) file_get_contents($meta), true);
        return is_array($json) ? $json : ['name' => $theme ?: self::active()];
    }

    /** @return list<string> */
    public static function available(): array
    {
        $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'themes';
        if (!is_dir($root)) {
            return [];
        }

        $themes = [];
        foreach (scandir($root) ?: [] as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            if (is_dir($root . DIRECTORY_SEPARATOR . $dir)) {
                $themes[] = $dir;
            }
        }

        return $themes;
    }
}
