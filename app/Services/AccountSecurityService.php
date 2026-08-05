<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use PDO;

final class AccountSecurityService
{
    public static function getSettings(int $accountId): array
    {
        self::ensureRow($accountId);
        $pdo = Database::web();
        $stmt = $pdo->prepare(
            'SELECT account_id, totp_enabled, totp_secret, totp_confirmed, ip_lock_enabled, locked_ip, login_notify
             FROM account_security WHERE account_id = ? LIMIT 1'
        );
        $stmt->execute([$accountId]);
        $row = $stmt->fetch() ?: [];

        return [
            'totp_enabled' => (int) ($row['totp_enabled'] ?? 0) === 1,
            'totp_confirmed' => (int) ($row['totp_confirmed'] ?? 0) === 1,
            'totp_secret' => (string) ($row['totp_secret'] ?? ''),
            'ip_lock_enabled' => (int) ($row['ip_lock_enabled'] ?? 0) === 1,
            'locked_ip' => (string) ($row['locked_ip'] ?? ''),
            'login_notify' => (int) ($row['login_notify'] ?? 0) === 1,
        ];
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function changePassword(int $accountId, string $current, string $new, string $confirm): array
    {
        $errors = [];
        if ($new === '' || strlen($new) < 4 || strlen($new) > 16) {
            $errors[] = 'Yeni parola 4–16 karakter olmalı.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Yeni parolalar eşleşmiyor.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $pdo = Database::account();
        $stmt = $pdo->prepare('SELECT password FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        $row = $stmt->fetch();
        if (!$row || !Security::verifyAccountPassword($current, (string) $row['password'])) {
            return ['ok' => false, 'errors' => ['Mevcut parola hatalı.']];
        }

        $hash = Security::hashAccountPassword($new);
        $upd = $pdo->prepare('UPDATE account SET password = ? WHERE id = ?');
        $upd->execute([$hash, $accountId]);

        return ['ok' => true, 'errors' => []];
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function changeSecurityCode(int $accountId, string $password, string $newCode, string $confirmCode): array
    {
        $errors = [];
        if (!preg_match('/^\d{1,6}$/', $newCode)) {
            $errors[] = 'Güvenli şifre en fazla 6 haneli ve sadece sayı olmalı.';
        }
        if ($newCode !== $confirmCode) {
            $errors[] = 'Güvenli şifreler eşleşmiyor.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $pdo = Database::account();
        $stmt = $pdo->prepare('SELECT password FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        $row = $stmt->fetch();
        if (!$row || !Security::verifyAccountPassword($password, (string) $row['password'])) {
            return ['ok' => false, 'errors' => ['Hesap parolası hatalı.']];
        }

        $hash = Security::hashAccountPassword($newCode);
        $upd = $pdo->prepare('UPDATE account SET securitycode = ? WHERE id = ?');
        $upd->execute([$hash, $accountId]);

        return ['ok' => true, 'errors' => []];
    }

    /** @return array{ok:bool, errors:list<string>, secret?:string} */
    public static function enableTotp(int $accountId): array
    {
        self::ensureRow($accountId);
        $secret = Totp::generateSecret();
        $pdo = Database::web();
        $pdo->prepare(
            'UPDATE account_security
             SET totp_secret = ?, totp_enabled = 0, totp_confirmed = 0, updated_at = NOW()
             WHERE account_id = ?'
        )->execute([$secret, $accountId]);

        return ['ok' => true, 'errors' => [], 'secret' => $secret];
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function confirmTotp(int $accountId, string $code): array
    {
        $settings = self::getSettings($accountId);
        if ($settings['totp_secret'] === '') {
            return ['ok' => false, 'errors' => ['Önce 2FA kurulumunu başlat.']];
        }
        if (!Totp::verify($settings['totp_secret'], $code)) {
            return ['ok' => false, 'errors' => ['Doğrulama kodu hatalı.']];
        }

        Database::web()->prepare(
            'UPDATE account_security SET totp_enabled = 1, totp_confirmed = 1, updated_at = NOW() WHERE account_id = ?'
        )->execute([$accountId]);

        return ['ok' => true, 'errors' => []];
    }

    public static function disableTotp(int $accountId, string $password): array
    {
        $pdo = Database::account();
        $stmt = $pdo->prepare('SELECT password FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        $row = $stmt->fetch();
        if (!$row || !Security::verifyAccountPassword($password, (string) $row['password'])) {
            return ['ok' => false, 'errors' => ['Hesap parolası hatalı.']];
        }

        Database::web()->prepare(
            'UPDATE account_security
             SET totp_enabled = 0, totp_confirmed = 0, totp_secret = NULL, updated_at = NOW()
             WHERE account_id = ?'
        )->execute([$accountId]);

        return ['ok' => true, 'errors' => []];
    }

    public static function setIpLock(int $accountId, bool $enabled): array
    {
        self::ensureRow($accountId);
        $ip = $enabled ? Security::clientIp() : null;
        Database::web()->prepare(
            'UPDATE account_security SET ip_lock_enabled = ?, locked_ip = ?, updated_at = NOW() WHERE account_id = ?'
        )->execute([$enabled ? 1 : 0, $ip, $accountId]);

        return ['ok' => true, 'errors' => []];
    }

    public static function setLoginNotify(int $accountId, bool $enabled): array
    {
        self::ensureRow($accountId);
        Database::web()->prepare(
            'UPDATE account_security SET login_notify = ?, updated_at = NOW() WHERE account_id = ?'
        )->execute([$enabled ? 1 : 0, $accountId]);

        return ['ok' => true, 'errors' => []];
    }

    public static function verifyTotpForLogin(int $accountId, string $code): bool
    {
        $settings = self::getSettings($accountId);
        if (!$settings['totp_enabled'] || !$settings['totp_confirmed'] || $settings['totp_secret'] === '') {
            return true;
        }
        return Totp::verify($settings['totp_secret'], $code);
    }

    public static function needsTotp(int $accountId): bool
    {
        $s = self::getSettings($accountId);
        return $s['totp_enabled'] && $s['totp_confirmed'] && $s['totp_secret'] !== '';
    }

    public static function checkIpLock(int $accountId): bool
    {
        $s = self::getSettings($accountId);
        if (!$s['ip_lock_enabled']) {
            return true;
        }
        $locked = $s['locked_ip'];
        if ($locked === '') {
            return true;
        }
        return hash_equals($locked, Security::clientIp());
    }

    private static function ensureRow(int $accountId): void
    {
        $pdo = Database::web();
        $pdo->prepare(
            'INSERT IGNORE INTO account_security (account_id, totp_enabled, totp_confirmed, ip_lock_enabled, login_notify)
             VALUES (?, 0, 0, 0, 0)'
        )->execute([$accountId]);
    }
}
