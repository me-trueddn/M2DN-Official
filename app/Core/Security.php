<?php

declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function csrfToken(): string
    {
        $name = (string) Config::get('security.csrf_token_name', 'csrf_token');
        $token = Session::get($name);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set($name, $token);
        }

        return $token;
    }

    public static function csrfField(): string
    {
        $name = htmlspecialchars((string) Config::get('security.csrf_token_name', 'csrf_token'), ENT_QUOTES, 'UTF-8');
        $token = htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
    }

    public static function validateCsrf(?string $token): bool
    {
        $name = (string) Config::get('security.csrf_token_name', 'csrf_token');
        $sessionToken = Session::get($name);

        if (!is_string($sessionToken) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function requireCsrf(string $failModal = 'login'): void
    {
        $name = (string) Config::get('security.csrf_token_name', 'csrf_token');
        $token = $_POST[$name] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!self::validateCsrf(is_string($token) ? $token : null)) {
            if ($failModal === 'register') {
                Session::flash('register_errors', ['Oturum doğrulaması başarısız. Lütfen tekrar dene.']);
                Session::flash('open_register', true);
            } else {
                Session::flash('login_errors', ['Oturum doğrulaması başarısız. Lütfen tekrar dene.']);
                Session::flash('open_login', true);
            }
            \redirect('/');
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Oyun hesabı şifresi — yalnızca account.account.password için MD5.
     * Diğer kolonlara (login, email, social_id) uygulanmaz.
     */
    public static function hashAccountPassword(string $password): string
    {
        $algo = strtolower((string) Config::get('security.account_password', 'md5'));
        if ($algo !== 'md5') {
            throw new \InvalidArgumentException('Oyun hesap şifresi yalnızca MD5 olmalıdır.');
        }

        return md5($password);
    }

    public static function verifyAccountPassword(string $password, string $storedHash): bool
    {
        if ($storedHash === '') {
            return false;
        }

        return hash_equals(strtolower($storedHash), self::hashAccountPassword($password));
    }

    /** DNWeb / panel admin şifreleri (account tablosu değil). */
    public static function hashWebPassword(string $password): string
    {
        $algo = Config::get('security.web_password_algo', PASSWORD_BCRYPT);
        $options = ['cost' => (int) Config::get('security.web_password_cost', 12)];
        return password_hash($password, $algo, $options);
    }

    public static function verifyWebPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** @deprecated Yerine hashAccountPassword / hashWebPassword kullanın */
    public static function hashPassword(string $password): string
    {
        return self::hashAccountPassword($password);
    }

    /** @deprecated Yerine verifyAccountPassword / verifyWebPassword kullanın */
    public static function verifyPassword(string $password, string $hash): bool
    {
        // account.password MD5 (32 hex) ise oyun hesabı doğrula
        if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            return self::verifyAccountPassword($password, $hash);
        }

        return self::verifyWebPassword($password, $hash);
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Ayar şifreleri için AES-256-CBC (app_key ile). Dönüş: base64(iv).base64(cipher) */
    public static function encryptSecret(string $plain): string
    {
        $key = hash('sha256', (string) Config::get('security.app_key', 'm2dn'), true);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            return '';
        }
        return base64_encode($iv) . '.' . base64_encode($cipher);
    }

    public static function decryptSecret(string $payload): string
    {
        $payload = trim($payload);
        if ($payload === '' || !str_contains($payload, '.')) {
            return '';
        }
        [$ivB64, $cipherB64] = explode('.', $payload, 2);
        $iv = base64_decode($ivB64, true);
        $cipher = base64_decode($cipherB64, true);
        if ($iv === false || $cipher === false || strlen($iv) !== 16) {
            return '';
        }
        $key = hash('sha256', (string) Config::get('security.app_key', 'm2dn'), true);
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return is_string($plain) ? $plain : '';
    }

    public static function applyHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        // Captcha (Google reCAPTCHA + Cloudflare Turnstile) script/iframe/img izinleri
        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "base-uri 'self'; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
            . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; "
            . "script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://www.recaptcha.net https://challenges.cloudflare.com; "
            . "img-src 'self' data: https: http:; "
            . "frame-src 'self' https://www.google.com https://www.recaptcha.net https://challenges.cloudflare.com; "
            . "connect-src 'self' https://www.google.com https://challenges.cloudflare.com; "
            . "frame-ancestors 'self';"
        );

        if (Config::get('security.force_https') && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: https://' . $host . $uri, true, 301);
            exit;
        }
    }
}
