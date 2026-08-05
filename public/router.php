<?php

declare(strict_types=1);

/**
 * PHP built-in server için:
 * php -S localhost:8080 -t public public/router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if (preg_match('#^/themes/([A-Za-z0-9_-]+)/assets/(.+)$#', $uri, $m)) {
    $_GET['theme'] = $m[1];
    $_GET['path'] = $m[2];
    require __DIR__ . '/theme-asset.php';
    return true;
}

$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
