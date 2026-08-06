<?php

declare(strict_types=1);

/**
 * Tema asset sunucusu:
 *  /themes/{Tema}/assets/...
 *  /themes/{Tema}/nesnemarket/assets/...
 * public/.htaccess veya router.php üzerinden çağrılır.
 */

$theme = $_GET['theme'] ?? '';
$path = $_GET['path'] ?? '';
$module = $_GET['module'] ?? '';
$folder = $_GET['folder'] ?? 'assets';

if (!is_string($theme) || !preg_match('/^[A-Za-z0-9_-]+$/', $theme)) {
    http_response_code(400);
    exit('Geçersiz tema.');
}

if (!is_string($path) || $path === '' || str_contains($path, '..')) {
    http_response_code(400);
    exit('Geçersiz yol.');
}

$module = is_string($module) ? $module : '';
if ($module !== '' && !preg_match('/^[A-Za-z0-9_-]+$/', $module)) {
    http_response_code(400);
    exit('Geçersiz modül.');
}

$folder = is_string($folder) ? $folder : 'assets';
if (!preg_match('/^[A-Za-z0-9_-]+$/', $folder)) {
    http_response_code(400);
    exit('Geçersiz klasör.');
}

$path = str_replace(['\\', "\0"], ['/', ''], $path);
$themeRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme;
if ($module !== '') {
    $baseDir = $themeRoot . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $folder;
} else {
    $baseDir = $themeRoot . DIRECTORY_SEPARATOR . $folder;
}
$file = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

$realBase = realpath($baseDir);
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
