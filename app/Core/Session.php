<?php

declare(strict_types=1);

namespace App\Core;

/**
 * PHP oturumu (M2DN_SESS).
 *
 * - Cookie lifetime = 0 → tarayıcı kapanınca oturum düşer.
 * - Login sonrası ID yenilenir; eski ID kısa süre “grace” ile yeni ID’ye yönlendirilir
 *   (çoklu sekme / yarışta oturum kaybını önlemek için).
 */
final class Session
{
    /** Eski session ID ile gelen isteklerin yeni ID’ye taşınabileceği süre (sn). */
    private const REGENERATE_GRACE_SECONDS = 120;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::migrateIfDestroyed();
            return;
        }

        $name = (string) Config::get('security.session_name', 'M2DN_SESS');
        session_name($name);

        $savePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($savePath)) {
            @mkdir($savePath, 0775, true);
        }
        if (is_dir($savePath) && is_writable($savePath)) {
            session_save_path($savePath);
        }

        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_lifetime', '0');

        $params = self::cookieParams();
        session_set_cookie_params($params);

        session_start([
            'use_strict_mode' => true,
            'cookie_lifetime' => 0,
            'cookie_path' => '/',
            'cookie_secure' => $params['secure'],
            'cookie_httponly' => $params['httponly'],
            'cookie_samesite' => $params['samesite'],
        ]);

        self::migrateIfDestroyed();

        // İlk ziyaret: ID’yi burada yenileme — çoklu sekme yarışında oturum kopmasına yol açar.
        // ID yenileme yalnızca login / logout (regenerate) ile yapılır.
        if (!isset($_SESSION['_initiated'])) {
            $_SESSION['_initiated'] = true;
            $_SESSION['_created_at'] = time();
        }
    }

    /**
     * @return array{lifetime:int, path:string, secure:bool, httponly:bool, samesite:string}
     */
    private static function cookieParams(): array
    {
        return [
            'lifetime' => 0,
            'path' => '/',
            'secure' => (bool) Config::get('security.cookie_secure', false),
            'httponly' => (bool) Config::get('security.cookie_httponly', true),
            'samesite' => (string) Config::get('security.cookie_samesite', 'Lax'),
        ];
    }

    /**
     * Login sonrası regenerate: eski oturum hemen silinmez; grace süresi boyunca
     * eski cookie ile gelen istek yeni session ID’ye taşınır.
     */
    public static function regenerate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }

        $data = $_SESSION;
        unset($data['_destroyed'], $data['_new_session_id']);

        $newId = session_create_id();
        if (!is_string($newId) || $newId === '') {
            session_regenerate_id(false);
            return;
        }

        $_SESSION['_destroyed'] = time();
        $_SESSION['_new_session_id'] = $newId;
        session_write_close();

        session_id($newId);
        // session_create_id ile üretilen ID strict mode’da henüz “bilinmiyor” olabilir
        $strict = ini_get('session.use_strict_mode');
        ini_set('session.use_strict_mode', '0');
        $params = self::cookieParams();
        session_start([
            'use_strict_mode' => false,
            'cookie_lifetime' => 0,
            'cookie_path' => '/',
            'cookie_secure' => $params['secure'],
            'cookie_httponly' => $params['httponly'],
            'cookie_samesite' => $params['samesite'],
        ]);
        ini_set('session.use_strict_mode', is_string($strict) ? $strict : '1');

        $_SESSION = $data;
        $_SESSION['_initiated'] = true;
        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = time();
        }
    }

    /**
     * Grace içindeki eski session → yeni session ID’ye taşı.
     */
    private static function migrateIfDestroyed(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        if (empty($_SESSION['_destroyed']) || empty($_SESSION['_new_session_id'])) {
            return;
        }

        $destroyedAt = (int) $_SESSION['_destroyed'];
        $newId = (string) $_SESSION['_new_session_id'];
        $age = time() - $destroyedAt;

        if ($newId === '' || $age > self::REGENERATE_GRACE_SECONDS) {
            // Grace bitti — eski oturumu boşalt
            $_SESSION = [];
            $_SESSION['_initiated'] = true;
            $_SESSION['_created_at'] = time();
            return;
        }

        session_write_close();

        session_id($newId);
        $strict = ini_get('session.use_strict_mode');
        ini_set('session.use_strict_mode', '0');
        $params = self::cookieParams();
        session_start([
            'use_strict_mode' => false,
            'cookie_lifetime' => 0,
            'cookie_path' => '/',
            'cookie_secure' => $params['secure'],
            'cookie_httponly' => $params['httponly'],
            'cookie_samesite' => $params['samesite'],
        ]);
        ini_set('session.use_strict_mode', is_string($strict) ? $strict : '1');

        // Yeni oturum da destroy işaretliyse (nadir) döngüye girme
        if (!empty($_SESSION['_destroyed'])) {
            unset($_SESSION['_destroyed'], $_SESSION['_new_session_id']);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'] ?? '/',
                    'domain' => $params['domain'] ?? '',
                    'secure' => (bool) ($params['secure'] ?? false),
                    'httponly' => (bool) ($params['httponly'] ?? true),
                    'samesite' => (string) ($params['samesite'] ?? 'Lax'),
                ]
            );
        }
        session_destroy();
    }
}
