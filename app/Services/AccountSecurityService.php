<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use App\Core\ServerManager;
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

        ActivityLogService::log($accountId, ActivityLogService::ACTION_PASSWORD, 'Parola güncellendi');

        return ['ok' => true, 'errors' => []];
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function changeSecurityCode(int $accountId, string $password, string $newCode, string $confirmCode): array
    {
        $errors = [];
        if (!preg_match('/^\d{1,6}$/', $newCode)) {
            $errors[] = 'Güvenlik kodu en fazla 6 haneli ve sadece sayı olmalı.';
        }
        if ($newCode !== $confirmCode) {
            $errors[] = 'Güvenlik kodları eşleşmiyor.';
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

        ActivityLogService::log($accountId, ActivityLogService::ACTION_SECURITY_CODE, 'Güvenlik kodu güncellendi');

        return ['ok' => true, 'errors' => []];
    }

    /**
     * Oyuncu: oyun deposu şifresi (player.safebox.password) — account.securitycode ile ayrıdır.
     *
     * @return array{ok:bool, errors:list<string>}
     */
    public static function changeSafeboxPassword(int $accountId, string $password, string $newCode, string $confirmCode, ?string $serverKey = null): array
    {
        $errors = [];
        if (!preg_match('/^\d{1,6}$/', $newCode)) {
            $errors[] = 'Depo şifresi en fazla 6 haneli ve sadece sayı olmalı.';
        }
        if ($newCode !== $confirmCode) {
            $errors[] = 'Depo şifreleri eşleşmiyor.';
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

        $result = self::writeSafeboxPassword($accountId, $newCode, $serverKey);
        if (empty($result['ok'])) {
            return $result;
        }

        ActivityLogService::log($accountId, ActivityLogService::ACTION_SAFEBOX_PASSWORD, 'Depo şifresi güncellendi');

        return ['ok' => true, 'errors' => []];
    }

    /**
     * Admin: account.securitycode (kayıt / güvenlik kodu) sıfırlar.
     *
     * @param array{account_id?:int,login?:string,permission?:int} $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function adminSetSecurityCode(int $targetAccountId, string $newCode, array $actor): array
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? AuthService::PERM_USER);
        if ($actorPerm < AuthService::PERM_ADMIN) {
            return ['ok' => false, 'errors' => ['Güvenlik kodu sıfırlamak için admin yetkisi gerekir.']];
        }

        $newCode = trim($newCode);
        if (!preg_match('/^\d{1,6}$/', $newCode)) {
            return ['ok' => false, 'errors' => ['Güvenlik kodu 1–6 haneli ve sadece sayı olmalı.']];
        }
        if ($targetAccountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }

        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            $targetPerm = AuthService::normalizePermission($row['WebPermission'] ?? AuthService::PERM_USER);
            if ($targetPerm > $actorPerm) {
                return ['ok' => false, 'errors' => ['Daha yetkili hesabın güvenlik kodunu sıfırlayamazsın.']];
            }

            $hash = Security::hashAccountPassword($newCode);
            $pdo->prepare('UPDATE account SET securitycode = ? WHERE id = ?')->execute([$hash, $targetAccountId]);

            $actorId = (int) ($actor['account_id'] ?? 0);
            $actorLogin = (string) ($actor['login'] ?? '');
            ActivityLogService::log(
                $targetAccountId,
                ActivityLogService::ACTION_SECURITY_CODE,
                'Güvenlik kodu admin tarafından sıfırlandı',
                (string) $row['login'],
                $actorId > 0 ? $actorId : null,
                $actorLogin !== '' ? $actorLogin : null
            );
            AdminLogService::write(
                $actor,
                'Güvenlik kodu sıfırlandı',
                '#' . $targetAccountId . ' · ' . (string) $row['login'],
                $targetAccountId,
                (string) $row['login']
            );

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Güvenlik kodu güncellenemedi.']];
        }
    }

    /**
     * Admin: player.safebox.password (oyun deposu) sıfırlar.
     *
     * @param array{account_id?:int,login?:string,permission?:int} $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function adminSetSafeboxPassword(int $targetAccountId, string $newCode, array $actor, ?string $serverKey = null): array
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? AuthService::PERM_USER);
        if ($actorPerm < AuthService::PERM_ADMIN) {
            return ['ok' => false, 'errors' => ['Depo şifresi sıfırlamak için admin yetkisi gerekir.']];
        }

        $newCode = trim($newCode);
        if (!preg_match('/^\d{1,6}$/', $newCode)) {
            return ['ok' => false, 'errors' => ['Depo şifresi 1–6 haneli ve sadece sayı olmalı.']];
        }
        if ($targetAccountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }

        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            $targetPerm = AuthService::normalizePermission($row['WebPermission'] ?? AuthService::PERM_USER);
            if ($targetPerm > $actorPerm) {
                return ['ok' => false, 'errors' => ['Daha yetkili hesabın depo şifresini sıfırlayamazsın.']];
            }

            $write = self::writeSafeboxPassword($targetAccountId, $newCode, $serverKey);
            if (empty($write['ok'])) {
                return $write;
            }

            $actorId = (int) ($actor['account_id'] ?? 0);
            $actorLogin = (string) ($actor['login'] ?? '');
            ActivityLogService::log(
                $targetAccountId,
                ActivityLogService::ACTION_SAFEBOX_PASSWORD,
                'Depo şifresi admin tarafından sıfırlandı',
                (string) $row['login'],
                $actorId > 0 ? $actorId : null,
                $actorLogin !== '' ? $actorLogin : null
            );
            AdminLogService::write(
                $actor,
                'Depo şifresi sıfırlandı',
                '#' . $targetAccountId . ' · ' . (string) $row['login'],
                $targetAccountId,
                (string) $row['login']
            );

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Depo şifresi güncellenemedi.']];
        }
    }

    /**
     * player.safebox.password yazar (yoksa satır oluşturur). Değer MD5 hash olarak saklanır.
     *
     * @return array{ok:bool, errors:list<string>}
     */
    private static function writeSafeboxPassword(int $accountId, string $plainPassword, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $pages = max(1, (int) (\App\Core\Config::get('nesne_market.safebox_default_pages', 1)));
        $hash = Security::hashAccountPassword($plainPassword);

        try {
            $player = Database::player($serverKey);
            self::ensureSafeboxPasswordColumn($player);

            $stmt = $player->prepare('SELECT account_id FROM safebox WHERE account_id = ? LIMIT 1');
            $stmt->execute([$accountId]);
            if ($stmt->fetch()) {
                $player->prepare('UPDATE safebox SET password = ? WHERE account_id = ?')
                    ->execute([$hash, $accountId]);
            } else {
                $player->prepare('INSERT INTO safebox (account_id, size, password, gold) VALUES (?, ?, ?, 0)')
                    ->execute([$accountId, $pages, $hash]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Depo kaydı güncellenemedi.']];
        }
    }

    /** MD5 (32 karakter) sığması için password kolonunu genişletir. */
    private static function ensureSafeboxPasswordColumn(PDO $player): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        try {
            $col = $player->query("SHOW COLUMNS FROM safebox LIKE 'password'")->fetch();
            $type = strtolower((string) ($col['Type'] ?? ''));
            if (preg_match('/varchar\((\d+)\)/', $type, $m) && (int) $m[1] < 32) {
                $player->exec('ALTER TABLE `safebox` MODIFY `password` VARCHAR(32) NOT NULL DEFAULT \'\'');
            }
        } catch (\Throwable) {
            // ignore — yazma denemesi yine de yapılır
        }
        $done = true;
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

        ActivityLogService::log($accountId, ActivityLogService::ACTION_2FA_START, '2FA kurulum anahtarı oluşturuldu');

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

        ActivityLogService::log($accountId, ActivityLogService::ACTION_2FA_ENABLE, 'İki adımlı doğrulama aktif');

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

        ActivityLogService::log($accountId, ActivityLogService::ACTION_2FA_DISABLE, 'İki adımlı doğrulama kapatıldı');

        return ['ok' => true, 'errors' => []];
    }

    /**
     * Admin: hesap 2FA (TOTP) kapatır. WebPerm ≥ 1 + disable_2fa bayrağı gerekir (controller).
     *
     * @param array{account_id?:int,login?:string,permission?:int} $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function adminDisableTotp(int $targetAccountId, array $actor): array
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? AuthService::PERM_USER);
        if ($actorPerm < AuthService::PERM_ADMIN) {
            return ['ok' => false, 'errors' => ['2FA kapatmak için admin yetkisi gerekir.']];
        }
        if ($targetAccountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }
        if (!PermissionService::canOperateOnAccount($actor, $targetAccountId)) {
            return ['ok' => false, 'errors' => ['Bu hesap üzerinde 2FA kapatamazsın.']];
        }

        try {
            $pdo = Database::account();
            $stmt = $pdo->prepare('SELECT id, login FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            self::ensureRow($targetAccountId);
            $settings = self::getSettings($targetAccountId);
            $wasActive = !empty($settings['totp_enabled'])
                || $settings['totp_secret'] !== ''
                || !empty($settings['totp_confirmed']);
            if (!$wasActive) {
                return ['ok' => false, 'errors' => ['Bu hesapta aktif veya kurulumda 2FA yok.']];
            }

            Database::web()->prepare(
                'UPDATE account_security
                 SET totp_enabled = 0, totp_confirmed = 0, totp_secret = NULL, updated_at = NOW()
                 WHERE account_id = ?'
            )->execute([$targetAccountId]);

            $actorId = (int) ($actor['account_id'] ?? 0);
            $actorLogin = (string) ($actor['login'] ?? '');
            ActivityLogService::log(
                $targetAccountId,
                ActivityLogService::ACTION_2FA_DISABLE,
                '2FA admin tarafından kapatıldı',
                (string) $row['login'],
                $actorId > 0 ? $actorId : null,
                $actorLogin !== '' ? $actorLogin : null
            );
            AdminLogService::write(
                $actor,
                '2FA kapatıldı',
                '#' . $targetAccountId . ' · ' . (string) $row['login'],
                $targetAccountId,
                (string) $row['login']
            );

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['2FA kapatılamadı.']];
        }
    }

    public static function setIpLock(int $accountId, bool $enabled): array
    {
        self::ensureRow($accountId);
        $ip = $enabled ? Security::clientIp() : null;
        Database::web()->prepare(
            'UPDATE account_security SET ip_lock_enabled = ?, locked_ip = ?, updated_at = NOW() WHERE account_id = ?'
        )->execute([$enabled ? 1 : 0, $ip, $accountId]);

        ActivityLogService::log(
            $accountId,
            $enabled ? ActivityLogService::ACTION_IP_LOCK_ON : ActivityLogService::ACTION_IP_LOCK_OFF,
            $enabled ? ('Kilitlenen IP: ' . (string) $ip) : 'IP kilidi kaldırıldı'
        );

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
