<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Security;
use App\Core\Theme;

function e(?string $value): string
{
    return Security::e($value);
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function theme_view(string $view, array $data = []): void
{
    Theme::render($view, $data);
}

function asset(string $path = ''): string
{
    return Theme::assetUrl($path);
}

/**
 * Aynı origin relative URL — localhost / 127.0.0.1 cookie kopmasını önler.
 */
function url(string $path = ''): string
{
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return '/';
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return '/' . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    $base = rtrim((string) Config::get('app.url', ''), '/');
    $rel = url($path);
    if ($rel === '/') {
        return $base !== '' ? $base . '/' : '/';
    }
    return ($base !== '' ? $base : '') . $rel;
}

/** Public wiki alt kategori sayfası: /wiki/{slug}.html */
function wiki_url(string $slug): string
{
    return url(\App\Services\WikiCategoryService::pagePath($slug));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}
