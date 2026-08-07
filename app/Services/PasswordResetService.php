<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Security;

final class PasswordResetService
{
    public const TTL_MINUTES = 20;

    /**
     * @param array{account_id?:int,login?:string}|null $actor
     * @return array{ok:bool, errors:list<string>, token?:string}
     */
    public static function createToken(int $accountId, string $login, ?array $actor = null): array
    {
        if ($accountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }
        try {
            self::purgeExpired();
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + self::TTL_MINUTES * 60);
            Database::web()->prepare(
                'INSERT INTO password_resets
                  (account_id, account_login, token_hash, expires_at, created_by_id, created_by_login, created_at)
                 VALUES (?,?,?,?,?,?,NOW())'
            )->execute([
                $accountId,
                mb_substr($login, 0, 30),
                $hash,
                $expires,
                (int) ($actor['account_id'] ?? 0),
                mb_substr((string) ($actor['login'] ?? ''), 0, 30),
            ]);
            return ['ok' => true, 'errors' => [], 'token' => $token];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Sıfırlama kodu oluşturulamadı.']];
        }
    }

    /**
     * Unuttum: login + email eşleşmeli, yalnızca web=0.
     * @return array{ok:bool, errors:list<string>}
     */
    public static function requestForgot(string $login, string $email): array
    {
        $login = trim($login);
        $email = trim($email);
        if ($login === '' || $email === '') {
            return ['ok' => false, 'errors' => ['Hesap adı ve e-posta zorunlu.']];
        }
        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare(
                'SELECT id, login, email, WebPermission FROM account WHERE login = ? LIMIT 1'
            );
            $stmt->execute([$login]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap ve e-posta eşleşmedi.']];
            }
            $dbEmail = trim((string) ($row['email'] ?? ''));
            if (strcasecmp($dbEmail, $email) !== 0) {
                return ['ok' => false, 'errors' => ['Hesap ve e-posta eşleşmedi.']];
            }
            $perm = AuthService::normalizePermission($row['WebPermission'] ?? 0);
            if ($perm !== AuthService::PERM_USER) {
                return ['ok' => false, 'errors' => ['Yetkili hesaplar bu formu kullanamaz. Yönetici üzerinden sıfırlama isteyin.']];
            }
            $accountId = (int) $row['id'];
            $tokenResult = self::createToken($accountId, (string) $row['login']);
            if (empty($tokenResult['ok']) || empty($tokenResult['token'])) {
                return ['ok' => false, 'errors' => $tokenResult['errors'] ?: ['İşlem başarısız.']];
            }
            $base = rtrim((string) Config::get('app.url', ''), '/');
            $link = $base . '/sifre-sifirla?token=' . urlencode((string) $tokenResult['token']);
            // Şablon kapalı olsa bile kartlı şablonla gönder (düz HTML fallback yok)
            MailService::sendTemplate('password_reset', $dbEmail, (string) $row['login'], [
                'login' => (string) $row['login'],
                'email' => $dbEmail,
                'link' => $link,
            ], true);
            AdminLogService::write(
                ['account_id' => 0, 'login' => 'sistem'],
                'Şifre sıfırlama talebi',
                '#' . $accountId . ' · ' . (string) $row['login'],
                $accountId,
                (string) $row['login']
            );
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['İşlem şu an yapılamıyor.']];
        }
    }

    /**
     * Admin sıfırlama linki gönderir (web>=1).
     * @param array{account_id:int,login:string,permission:int} $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function adminSendLink(int $targetAccountId, array $actor): array
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? 0);
        if ($actorPerm < AuthService::PERM_ADMIN) {
            return ['ok' => false, 'errors' => ['Yetkin yok.']];
        }
        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login, email, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $targetPerm = AuthService::normalizePermission($row['WebPermission'] ?? 0);
            if ($targetPerm === AuthService::PERM_SUPER && $actorPerm !== AuthService::PERM_SUPER) {
                return ['ok' => false, 'errors' => ['Süper admin için link gönderemezsin.']];
            }
            $email = trim((string) ($row['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'errors' => ['Hesabın geçerli e-postası yok.']];
            }
            $tokenResult = self::createToken($targetAccountId, (string) $row['login'], $actor);
            if (empty($tokenResult['ok']) || empty($tokenResult['token'])) {
                return ['ok' => false, 'errors' => $tokenResult['errors'] ?: ['Kod oluşturulamadı.']];
            }
            $base = rtrim((string) Config::get('app.url', ''), '/');
            $link = $base . '/sifre-sifirla?token=' . urlencode((string) $tokenResult['token']);
            $mail = MailService::sendTemplate('password_reset', $email, (string) $row['login'], [
                'login' => (string) $row['login'],
                'link' => $link,
                'email' => $email,
            ], true);
            if (empty($mail['ok'])) {
                return ['ok' => false, 'errors' => $mail['errors'] ?: ['Mail gönderilemedi.']];
            }
            AdminLogService::write(
                $actor,
                'Şifre sıfırlama linki gönderildi',
                '#' . $targetAccountId . ' · ' . (string) $row['login'],
                $targetAccountId,
                (string) $row['login']
            );
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['İşlem başarısız.']];
        }
    }

    /**
     * Super admin doğrudan yeni şifre belirler.
     * @param array{account_id:int,login:string,permission:int} $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function adminSetPassword(int $targetAccountId, string $newPassword, array $actor): array
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? 0);
        if ($actorPerm !== AuthService::PERM_SUPER) {
            return ['ok' => false, 'errors' => ['Yalnızca Süper Admin doğrudan şifre sıfırlayabilir.']];
        }
        $newPassword = trim($newPassword);
        if (strlen($newPassword) < 4 || strlen($newPassword) > 16) {
            return ['ok' => false, 'errors' => ['Yeni parola 4–16 karakter olmalı.']];
        }
        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $hash = Security::hashAccountPassword($newPassword);
            $pdo->prepare('UPDATE account SET password = ? WHERE id = ?')->execute([$hash, $targetAccountId]);
            AdminLogService::write(
                $actor,
                'Şifre sıfırlandı (doğrudan)',
                '#' . $targetAccountId . ' · ' . (string) $row['login'],
                $targetAccountId,
                (string) $row['login']
            );
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Şifre güncellenemedi.']];
        }
    }

    /**
     * Token ile yeni şifre.
     * @return array{ok:bool, errors:list<string>}
     */
    public static function consumeToken(string $token, string $newPassword, string $confirm): array
    {
        $token = trim($token);
        $newPassword = trim($newPassword);
        if ($token === '') {
            return ['ok' => false, 'errors' => ['Geçersiz bağlantı.']];
        }
        if ($newPassword !== $confirm) {
            return ['ok' => false, 'errors' => ['Parolalar eşleşmiyor.']];
        }
        if (strlen($newPassword) < 4 || strlen($newPassword) > 16) {
            return ['ok' => false, 'errors' => ['Parola 4–16 karakter olmalı.']];
        }
        self::purgeExpired();
        try {
            $hash = hash('sha256', $token);
            $web = Database::web();
            $stmt = $web->prepare(
                'SELECT id, account_id, account_login, expires_at, used_at
                 FROM password_resets WHERE token_hash = ? LIMIT 1'
            );
            $stmt->execute([$hash]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Bağlantı geçersiz veya kullanılmış.']];
            }
            if (!empty($row['used_at'])) {
                return ['ok' => false, 'errors' => ['Bu bağlantı daha önce kullanılmış.']];
            }
            if (strtotime((string) $row['expires_at']) < time()) {
                $web->prepare('DELETE FROM password_resets WHERE id=?')->execute([(int) $row['id']]);
                return ['ok' => false, 'errors' => ['Bağlantının süresi dolmuş.']];
            }
            $accountId = (int) $row['account_id'];
            $pdo = Database::account();
            $acc = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE id = ? LIMIT 1');
            $acc->execute([$accountId]);
            $account = $acc->fetch();
            if (!$account) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            // Yetkili hesaplar (web>=1) unuttum akışıyla gelmemeli; admin linkiyle gelebilirler
            $pwdHash = Security::hashAccountPassword($newPassword);
            $pdo->prepare('UPDATE account SET password = ? WHERE id = ?')->execute([$pwdHash, $accountId]);
            $web->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([(int) $row['id']]);
            $web->prepare('DELETE FROM password_resets WHERE account_id = ? AND used_at IS NULL')->execute([$accountId]);
            AdminLogService::write(
                ['account_id' => $accountId, 'login' => (string) $account['login']],
                'Şifre sıfırlandı (bağlantı)',
                '#' . $accountId . ' · ' . (string) $account['login'],
                $accountId,
                (string) $account['login']
            );
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Şifre sıfırlanamadı.']];
        }
    }

    public static function purgeExpired(): void
    {
        try {
            Database::web()->exec(
                'DELETE FROM password_resets WHERE used_at IS NOT NULL OR expires_at < NOW()'
            );
        } catch (\Throwable) {
            // ignore
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function changeEmail(int $accountId, string $newEmail, array $actor): array
    {
        $newEmail = trim($newEmail);
        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($newEmail) > 64) {
            return ['ok' => false, 'errors' => ['Geçerli bir e-posta gir.']];
        }
        if (!PermissionService::canOperateOnAccount($actor, $accountId)) {
            return ['ok' => false, 'errors' => ['Bu hesapta işlem yapamazsın (Yetki yetersiz / Not Perm).']];
        }
        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login, email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $dup = $pdo->prepare('SELECT id FROM account WHERE email = ? AND id <> ? LIMIT 1');
            $dup->execute([$newEmail, $accountId]);
            if ($dup->fetch()) {
                return ['ok' => false, 'errors' => ['Bu e-posta başka bir hesapta kayıtlı.']];
            }
            $old = (string) ($row['email'] ?? '');
            $pdo->prepare('UPDATE account SET email = ? WHERE id = ?')->execute([$newEmail, $accountId]);
            AdminLogService::write(
                $actor,
                'E-posta değiştirildi',
                '#' . $accountId . ' · ' . (string) $row['login'] . ' · ' . $old . ' → ' . $newEmail,
                $accountId,
                (string) $row['login']
            );
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['E-posta güncellenemedi.']];
        }
    }
}
