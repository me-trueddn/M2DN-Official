<?php

declare(strict_types=1);

/**
 * Tema asset sunucusu: /themes/{Tema}/assets/...
 * public/.htaccess veya router.php üzerinden çağrılır.
 */

$theme = $_GET['theme'] ?? '';
$path = $_GET['path'] ?? '';

if (!is_string($theme) || !preg_match('/^[A-Za-z0-9_-]+$/', $theme)) {
    http_response_code(400);
    exit('Geçersiz tema.');
}

if (!is_string($path) || $path === '' || str_contains($path, '..')) {
    http_response_code(400);
    exit('Geçersiz yol.');
}

$path = str_replace(['\\', "\0"], ['/', ''], $path);
$file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

$realBase = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'assets');
$realFile = realpath($file);

if ($realBase === false || $realFile === false || !str_starts_with($realFile, $realBase) || !is_file($realFile)) {
    http_response_code(404);
    exit('Dosya bulunamadı.');
}

$ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
$types = [
    'css'  => 'text/css; charset=utf-8',
    'js'   => 'application/javascript; charset=utf-8',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
    'woff' => 'font/woff',
    'woff2'=> 'font/woff2',
    'ttf'  => 'font/ttf',
    'ico'  => 'image/x-icon',
];

header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('Cache-Control: public, max-age=86400');
readfile($realFile);
