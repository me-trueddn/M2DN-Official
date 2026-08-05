<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use PDO;

/**
 * Web paneli oturumu.
 * Token her girişte yenilenir; düz token sadece PHP session'da tutulur.
 * DNWeb.web_sessions içinde yalnızca hash saklanır — cookie'den sahte kullanıcı üretilemez.
 */
final class AuthService
{
    public const PERM_USER = 0;
    public const PERM_ADMIN = 1;
    public const PERM_SUPER = 2;

    /**
     * @return array{ok:bool, errors:list<string>, permission?:int, needs_2fa?:bool}
     */
    public static function login(string $login, string $password): array
    {
        $login = trim($login);
        $password = trim($password);

        if ($login === '' || $password === '') {
            return ['ok' => false, 'errors' => ['Kullanıcı adı ve parola zorunlu.']];
        }

        $pdo = Database::account();
        $stmt = $pdo->prepare(
            'SELECT id, login, password, status, WebPermission FROM account WHERE login = ? LIMIT 1'
        );
        $stmt->execute([$login]);
        $row = $stmt->fetch();

        if (!$row || !Security::verifyAccountPassword($password, (string) $row['password'])) {
            return ['ok' => false, 'errors' => ['Kullanıcı adı veya parola hatalı.']];
        }

        if (strtoupper((string) ($row['status'] ?? '')) === 'BLOCK') {
            // Banlı hesap panele girebilir; oyuna giremez (status BLOCK)
        } elseif (strtoupper((string) ($row['status'] ?? '')) !== 'OK') {
            return ['ok' => false, 'errors' => ['Hesabın aktif değil.']];
        }

        $accountId = (int) $row['id'];
        $permission = self::normalizePermission($row['WebPermission'] ?? null);

        if (!AccountSecurityService::checkIpLock($accountId)) {
            return ['ok' => false, 'errors' => ['IP kilidi aktif. Bu IP ile giriş yapılamaz.']];
        }

        if (AccountSecurityService::needsTotp($accountId)) {
            Session::regenerate();
            Session::forget('auth');
            Session::set('pending_2fa', [
                'account_id' => $accountId,
                'login' => (string) $row['login'],
                'permission' => $permission,
                'expires_at' => time() + 300,
            ]);
            return ['ok' => false, 'errors' => [], 'needs_2fa' => true];
        }

        self::establishSession($accountId, (string) $row['login'], $permission);

        return ['ok' => true, 'errors' => [], 'permission' => $permission];
    }

    /**
     * @return array{ok:bool, errors:list<string>, permission?:int}
     */
    public static function completeTwoFactor(string $code): array
    {
        $pending = Session::get('pending_2fa');
        if (!is_array($pending) || empty($pending['account_id'])) {
            return ['ok' => false, 'errors' => ['2FA oturumu bulunamadı. Tekrar giriş yap.']];
        }
        if ((int) ($pending['expires_at'] ?? 0) < time()) {
            Session::forget('pending_2fa');
            return ['ok' => false, 'errors' => ['2FA süresi doldu. Tekrar giriş yap.']];
        }

        $accountId = (int) $pending['account_id'];
        if (!AccountSecurityService::verifyTotpForLogin($accountId, $code)) {
            return ['ok' => false, 'errors' => ['Doğrulama kodu hatalı.']];
        }

        $login = (string) $pending['login'];
        $permission = (int) $pending['permission'];
        Session::forget('pending_2fa');
        self::establishSession($accountId, $login, $permission);

        return ['ok' => true, 'errors' => [], 'permission' => $permission];
    }

    private static function establishSession(int $accountId, string $login, int $permission): void
    {
        // Aynı hesabın eski web tokenlerini temizle
        self::revokeAccountSessions($accountId);
        self::purgeExpired();
        Session::regenerate();

        $token = bin2hex(random_bytes(32));
        $tokenHash = self::hashToken($token);
        $ttl = self::ttlMinutes();
        $expiresAt = null; // MySQL NOW() ile yazılacak
        $ip = Security::clientIp();
        $uaHash = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        $web = Database::web();
        $ins = $web->prepare(
            'INSERT INTO web_sessions (account_id, token_hash, ip, user_agent_hash, expires_at, created_at, last_seen_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ' . $ttl . ' MINUTE), NOW(), NOW())'
        );
        $ins->execute([$accountId, $tokenHash, $ip, $uaHash]);

        $expStmt = $web->prepare('SELECT expires_at FROM web_sessions WHERE token_hash = ? LIMIT 1');
        $expStmt->execute([$tokenHash]);
        $expiresAt = (string) ($expStmt->fetchColumn() ?: '');
        $expiresTs = strtotime($expiresAt) ?: (time() + $ttl * 60);

        Session::set('auth', [
            'account_id' => $accountId,
            'login' => $login,
            'token' => $token,
            'permission' => $permission,
            'issued_at' => time(),
            'expires_at' => $expiresTs,
        ]);

        Session::forget('simulate_user');
        Session::forget('simulate_token');
        Session::forget('user_id');
        Session::forget('pending_2fa');

        ActivityLogService::log($accountId, ActivityLogService::ACTION_LOGIN, 'Web paneli oturumu açıldı', $login);
    }

    public static function logout(): void
    {
        $auth = Session::get('auth');
        $accountId = 0;
        $login = '';
        $tokenHash = null;

        if (is_array($auth)) {
            $accountId = (int) ($auth['account_id'] ?? 0);
            $login = (string) ($auth['login'] ?? '');
            if (!empty($auth['token'])) {
                $tokenHash = self::hashToken((string) $auth['token']);
            }
        }

        if ($accountId > 0) {
            ActivityLogService::log($accountId, ActivityLogService::ACTION_LOGOUT, 'Web paneli oturumu kapatıldı', $login);
        }

        try {
            $web = Database::web();
            // Aktif token
            if ($tokenHash !== null) {
                $web->prepare('DELETE FROM web_sessions WHERE token_hash = ?')->execute([$tokenHash]);
            }
            // Hesaba ait kalan tüm tokenler
            if ($accountId > 0) {
                $web->prepare('DELETE FROM web_sessions WHERE account_id = ?')->execute([$accountId]);
            }
            // Süresi geçmiş / orphan kayıtlar
            self::purgeExpired();
        } catch (\Throwable) {
            // DB yoksa yine session temizlensin
        }

        Session::forget('auth');
        Session::forget('pending_2fa');
        Session::regenerate();
    }

    /**
     * Oturumu sunucu tarafında doğrula (DB token + süre + hesap).
     * Cookie/session'daki alanlara tek başına güvenilmez.
     *
     * @return array{account_id:int, login:string, permission:int, session_expires_at:int}|null
     */
    public static function user(): ?array
    {
        $auth = Session::get('auth');
        if (!is_array($auth)) {
            return null;
        }

        $accountId = (int) ($auth['account_id'] ?? 0);
        $token = (string) ($auth['token'] ?? '');
        $login = (string) ($auth['login'] ?? '');

        if ($accountId <= 0 || $token === '' || strlen($token) !== 64 || !ctype_xdigit($token)) {
            self::logout();
            return null;
        }

        $tokenHash = self::hashToken($token);
        $web = Database::web();
        $ttl = self::ttlMinutes();
        $maxTtl = self::maxTtlMinutes();

        // Süre kontrolü MySQL NOW() ile — PHP/MySQL saat farkında yanlış logout olmasın
        $stmt = $web->prepare(
            'SELECT id, account_id, expires_at, created_at,
                    (expires_at > NOW()) AS is_alive,
                    (created_at > (NOW() - INTERVAL ' . $maxTtl . ' MINUTE)) AS within_max
             FROM web_sessions WHERE token_hash = ? LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $session = $stmt->fetch();

        if (!$session || (int) $session['account_id'] !== $accountId) {
            self::logout();
            return null;
        }

        if (!(int) ($session['is_alive'] ?? 0) || !(int) ($session['within_max'] ?? 0)) {
            $web->prepare('DELETE FROM web_sessions WHERE id = ?')->execute([(int) $session['id']]);
            self::purgeExpired();
            self::logout();
            return null;
        }

        // İzin her seferinde account tablosundan — session'daki permission spoof edilemez
        $acc = Database::account();
        $q = $acc->prepare('SELECT id, login, status, WebPermission FROM account WHERE id = ? LIMIT 1');
        $q->execute([$accountId]);
        $row = $q->fetch();
        if (!$row) {
            self::logout();
            return null;
        }

        $status = strtoupper((string) ($row['status'] ?? ''));
        if ($status !== 'OK' && $status !== 'BLOCK') {
            self::logout();
            return null;
        }

        if ((string) $row['login'] !== $login) {
            self::logout();
            return null;
        }

        $permission = self::normalizePermission($row['WebPermission'] ?? null);

        // Sliding: idle TTL yenile, mutlak tavanı aşma (rowCount'a güvenme — aynı saniyede 0 dönebilir)
        $web->prepare(
            'UPDATE web_sessions
             SET last_seen_at = NOW(),
                 expires_at = LEAST(
                   DATE_ADD(NOW(), INTERVAL ' . $ttl . ' MINUTE),
                   DATE_ADD(created_at, INTERVAL ' . $maxTtl . ' MINUTE)
                 )
             WHERE id = ? AND token_hash = ?'
        )->execute([(int) $session['id'], $tokenHash]);

        $fresh = $web->prepare(
            'SELECT expires_at FROM web_sessions WHERE id = ? AND token_hash = ? LIMIT 1'
        );
        $fresh->execute([(int) $session['id'], $tokenHash]);
        $freshRow = $fresh->fetch();
        if (!$freshRow) {
            self::logout();
            return null;
        }

        $expiresTs = strtotime((string) $freshRow['expires_at']) ?: (time() + $ttl * 60);
        $createdAt = strtotime((string) $session['created_at']) ?: time();

        $auth['permission'] = $permission;
        $auth['expires_at'] = $expiresTs;
        $auth['issued_at'] = (int) ($auth['issued_at'] ?? $createdAt);
        Session::set('auth', $auth);

        return [
            'account_id' => $accountId,
            'login' => (string) $row['login'],
            'permission' => $permission,
            'session_expires_at' => $expiresTs,
        ];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function canAccessAdmin(?array $user = null): bool
    {
        $user ??= self::user();
        if ($user === null) {
            return false;
        }
        $p = (int) $user['permission'];
        return $p === self::PERM_ADMIN || $p === self::PERM_SUPER;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if ($user === null) {
            Session::flash('login_errors', ['Oturumun sona erdi. Lütfen tekrar giriş yap.']);
            Session::flash('open_login', true);
            redirect('/');
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if (!self::canAccessAdmin($user)) {
            Session::flash('login_errors', ['Bu panele erişim yetkin yok.']);
            Session::flash('open_login', true);
            redirect('/');
        }
        return $user;
    }

    public static function normalizePermission(mixed $value): int
    {
        if ($value === null || $value === '') {
            return self::PERM_USER;
        }
        $p = (int) $value;
        if ($p === self::PERM_ADMIN || $p === self::PERM_SUPER) {
            return $p;
        }
        return self::PERM_USER;
    }

    private static function ttlMinutes(): int
    {
        return max(1, (int) Config::get('security.web_session_ttl', 10));
    }

    private static function maxTtlMinutes(): int
    {
        $max = max(1, (int) Config::get('security.web_session_max_ttl', 120));
        return max(self::ttlMinutes(), $max);
    }

    private static function hashToken(string $token): string
    {
        $key = (string) Config::get('security.app_key', 'M2DN');
        return hash_hmac('sha256', $token, $key);
    }

    private static function revokeAccountSessions(int $accountId): void
    {
        $web = Database::web();
        $web->prepare('DELETE FROM web_sessions WHERE account_id = ?')->execute([$accountId]);
    }

    /** Süresi dolmuş tüm web tokenlerini sil. */
    public static function purgeExpired(): void
    {
        try {
            Database::web()->exec('DELETE FROM web_sessions WHERE expires_at < NOW()');
        } catch (\Throwable) {
            // ignore
        }
    }
}
