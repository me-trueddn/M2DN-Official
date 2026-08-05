<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Schema;
use App\Core\Security;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'helpers.php';

Config::load(BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

date_default_timezone_set((string) Config::get('app.timezone', 'Europe/Istanbul'));

if (!Config::get('app.debug')) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

// Session cookie CSRF için header'lardan önce başlamalı
Session::start();
Schema::ensure();
Security::applyHeaders();
\App\Services\AuthService::purgeExpired();
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
