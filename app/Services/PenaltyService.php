<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

final class PenaltyService
{
    /** Metin2: boş / açık hesap availDt */
    private const AVAIL_CLEAR = '0000-00-00 00:00:00';

    /** Süresiz ban sentinel (10 Kasım 1938) — oyun/panel otomatik açmaz */
    public const AVAIL_PERMANENT = '1938-11-10 00:00:00';

    /** @return list<array> */
    public static function listTemplates(bool $onlyActive = false): array
    {
        try {
            $web = Database::web();
            $sql = 'SELECT id, name, reason, days, is_active, created_at FROM penalty_templates';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY days ASC, id ASC';
            $rows = $web->query($sql)->fetchAll() ?: [];
            return array_map([self::class, 'mapTemplate'], $rows);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function getTemplate(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, name, reason, days, is_active, created_at FROM penalty_templates WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? self::mapTemplate($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveTemplate(?int $id, string $name, string $reason, int $days): array
    {
        $name = trim($name);
        $reason = trim($reason);
        $days = max(0, $days);
        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors[] = 'Ceza adı zorunlu (max 120).';
        }
        if ($reason === '' || mb_strlen($reason) > 500) {
            $errors[] = 'Sebep zorunlu (max 500).';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $web = Database::web();
            if ($id !== null && $id > 0) {
                $web->prepare(
                    'UPDATE penalty_templates SET name = ?, reason = ?, days = ?, updated_at = NOW() WHERE id = ?'
                )->execute([$name, $reason, $days, $id]);
            } else {
                $web->prepare(
                    'INSERT INTO penalty_templates (name, reason, days, is_active, created_at, updated_at)
                     VALUES (?, ?, ?, 1, NOW(), NOW())'
                )->execute([$name, $reason, $days]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Ceza kaydedilemedi.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function deleteTemplate(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz ceza.']];
        }
        try {
            Database::web()->prepare('DELETE FROM penalty_templates WHERE id = ?')->execute([$id]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Silinemedi.']];
        }
    }

    /**
     * Ban: account.status=BLOCK + account.availDt=bitiş (oyun buraya bakar).
     * DNWeb.account_bans: sadece sebep / açıklama / kanıt meta.
     *
     * @param array{account_id:int, login:string} $admin
     * @return array{ok:bool, errors:list<string>}
     */
    public static function banAccount(
        int $targetAccountId,
        int $penaltyId,
        string $evidence,
        array $admin,
        ?string $serverKey = null
    ): array {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $evidence = trim($evidence);
        $tpl = self::getTemplate($penaltyId);
        if ($tpl === null || !$tpl['is_active']) {
            return ['ok' => false, 'errors' => ['Geçerli bir ceza seç.']];
        }
        if ($targetAccountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }
        if ($targetAccountId === (int) ($admin['account_id'] ?? 0)) {
            return ['ok' => false, 'errors' => ['Kendini banlayamazsın.']];
        }

        try {
            $acc = Database::account($serverKey);
            $stmt = $acc->prepare('SELECT id, login, email, status, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            $perm = AuthService::normalizePermission($row['WebPermission'] ?? null);
            if ($perm === AuthService::PERM_ADMIN || $perm === AuthService::PERM_SUPER) {
                return ['ok' => false, 'errors' => ['Yönetici hesapları banlanamaz.']];
            }

            $days = (int) $tpl['days'];
            // Süreli: availDt = şimdi + gün. Süresiz: 10 Kasım 1938 (sentinel)
            if ($days > 0) {
                $acc->prepare(
                    "UPDATE account SET status = 'BLOCK', availDt = DATE_ADD(NOW(), INTERVAL {$days} DAY) WHERE id = ?"
                )->execute([$targetAccountId]);
            } else {
                $acc->prepare(
                    "UPDATE account SET status = 'BLOCK', availDt = ? WHERE id = ?"
                )->execute([self::AVAIL_PERMANENT, $targetAccountId]);
            }

            $web = Database::web();
            $web->prepare(
                'UPDATE account_bans SET is_active = 0, lifted_at = NOW() WHERE account_id = ? AND is_active = 1'
            )->execute([$targetAccountId]);

            $web->prepare(
                'INSERT INTO account_bans
                  (account_id, account_login, penalty_id, penalty_name, reason, evidence, days,
                   banned_by_id, banned_by_login, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())'
            )->execute([
                $targetAccountId,
                (string) $row['login'],
                (int) $tpl['id'],
                (string) $tpl['name'],
                (string) $tpl['reason'],
                mb_substr($evidence, 0, 1000),
                $days,
                (int) $admin['account_id'],
                (string) $admin['login'],
            ]);

            $detail = sprintf(
                'Ceza: %s · Sebep: %s · Süre: %s',
                $tpl['name'],
                $tpl['reason'],
                $days === 0 ? 'Süresiz' : ($days . ' gün')
            );
            if ($evidence !== '') {
                $detail .= ' · Kanıt: ' . $evidence;
            }

            ActivityLogService::log(
                $targetAccountId,
                ActivityLogService::ACTION_BAN,
                $detail,
                (string) $row['login'],
                (int) $admin['account_id'],
                (string) $admin['login'],
                $evidence
            );

            $email = trim((string) ($row['email'] ?? ''));
            if ($email !== '') {
                MailService::sendTemplate('ban', $email, (string) $row['login'], [
                    'login' => (string) $row['login'],
                    'reason' => (string) $tpl['reason'],
                    'email' => $email,
                ]);
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Ban uygulanamadı.']];
        }
    }

    /**
     * @param array{account_id:int, login:string} $admin
     * @return array{ok:bool, errors:list<string>}
     */
    public static function unbanAccount(
        int $targetAccountId,
        string $reason,
        array $admin,
        ?string $serverKey = null
    ): array {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $reason = trim($reason);
        if ($targetAccountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }
        if ($reason === '') {
            return ['ok' => false, 'errors' => ['Ban kaldırma sebebi zorunludur.']];
        }
        if (mb_strlen($reason) > 500) {
            return ['ok' => false, 'errors' => ['Sebep en fazla 500 karakter olabilir.']];
        }

        try {
            $acc = Database::account($serverKey);
            $stmt = $acc->prepare('SELECT id, login, email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            $acc->prepare(
                "UPDATE account SET status = 'OK', availDt = ? WHERE id = ?"
            )->execute([self::AVAIL_CLEAR, $targetAccountId]);

            Database::web()->prepare(
                'UPDATE account_bans SET is_active = 0, lifted_at = NOW() WHERE account_id = ? AND is_active = 1'
            )->execute([$targetAccountId]);

            ActivityLogService::log(
                $targetAccountId,
                ActivityLogService::ACTION_UNBAN,
                'Ban kaldırıldı · Sebep: ' . $reason,
                (string) $row['login'],
                (int) $admin['account_id'],
                (string) $admin['login'],
                $reason
            );

            $email = trim((string) ($row['email'] ?? ''));
            if ($email !== '') {
                MailService::sendTemplate('unban', $email, (string) $row['login'], [
                    'login' => (string) $row['login'],
                    'reason' => $reason,
                    'email' => $email,
                ]);
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Ban kaldırılamadı.']];
        }
    }

    /** @return list<array> */
    public static function listActiveBans(int $limit = 100, ?string $serverKey = null): array
    {
        $limit = max(1, min(500, $limit));
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $stmt = Database::web()->query(
                "SELECT id, account_id, account_login, penalty_name, reason, evidence, days,
                        banned_by_login, created_at
                 FROM account_bans
                 WHERE is_active = 1
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $rows = $stmt->fetchAll() ?: [];
            $availMap = self::availDtForAccounts(
                array_map(static fn(array $r): int => (int) $r['account_id'], $rows),
                $serverKey
            );
            $out = [];
            foreach ($rows as $row) {
                $aid = (int) $row['account_id'];
                $days = (int) ($row['days'] ?? 0);
                $until = $availMap[$aid] ?? self::AVAIL_CLEAR;
                $out[] = self::formatBanRow($row, $until, $days);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function getActiveBan(int $accountId, ?string $serverKey = null): ?array
    {
        if ($accountId <= 0) {
            return null;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, account_id, penalty_name, reason, evidence, days, banned_by_login, created_at
                 FROM account_bans WHERE account_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $days = (int) ($row['days'] ?? 0);
            $availMap = self::availDtForAccounts([$accountId], $serverKey);
            $until = $availMap[$accountId] ?? self::AVAIL_CLEAR;
            return self::formatBanRow($row, $until, $days);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Süresi dolmuş banları kaldırır: account.availDt <= NOW() ve status=BLOCK.
     * Süresiz sentinel (1938-11-10) ve 0000-00-00 dokunulmaz.
     */
    public static function liftExpired(?string $serverKey = null): void
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $acc = Database::account($serverKey);
            $perm = self::AVAIL_PERMANENT;
            $stmt = $acc->prepare(
                "SELECT id, login FROM account
                 WHERE status = 'BLOCK'
                   AND availDt IS NOT NULL
                   AND availDt > '0000-00-00 00:00:00'
                   AND availDt <> ?
                   AND availDt <= NOW()"
            );
            $stmt->execute([$perm]);
            $rows = $stmt->fetchAll() ?: [];
            if ($rows === []) {
                return;
            }
            $web = Database::web();
            foreach ($rows as $row) {
                $aid = (int) $row['id'];
                $login = (string) ($row['login'] ?? '');
                $acc->prepare(
                    "UPDATE account SET status = 'OK', availDt = ? WHERE id = ?"
                )->execute([self::AVAIL_CLEAR, $aid]);
                $web->prepare(
                    'UPDATE account_bans SET is_active = 0, lifted_at = NOW()
                     WHERE is_active = 1 AND (account_id = ? OR account_login = ?)'
                )->execute([$aid, $login]);
                ActivityLogService::log($aid, ActivityLogService::ACTION_UNBAN, 'Süre dolumu — otomatik ban kaldırma (availDt)', $login);
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @param list<int> $accountIds
     * @return array<int, string> account_id => availDt
     */
    private static function availDtForAccounts(array $accountIds, ?string $serverKey): array
    {
        $accountIds = array_values(array_unique(array_filter($accountIds, static fn(int $id): bool => $id > 0)));
        if ($accountIds === []) {
            return [];
        }
        try {
            $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
            $stmt = Database::account($serverKey)->prepare(
                "SELECT id, availDt FROM account WHERE id IN ({$placeholders})"
            );
            $stmt->execute($accountIds);
            $map = [];
            foreach ($stmt->fetchAll() ?: [] as $r) {
                $map[(int) $r['id']] = (string) ($r['availDt'] ?? self::AVAIL_CLEAR);
            }
            return $map;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param array<string, mixed> $row */
    private static function formatBanRow(array $row, string $until, int $days): array
    {
        $isPermanent = $days === 0 || self::isPermanentAvail($until);
        $untilTs = self::parseAvailTs($until);
        $remaining = 'Süresiz';
        if (!$isPermanent && $untilTs) {
            $sec = $untilTs - time();
            if ($sec <= 0) {
                $remaining = 'Süresi doldu';
            } else {
                $d = (int) floor($sec / 86400);
                $remaining = $d > 0 ? ($d . ' gün kaldı') : (max(1, (int) ceil($sec / 3600)) . ' saat kaldı');
            }
        }
        $createdTs = strtotime((string) ($row['created_at'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'account_id' => (int) ($row['account_id'] ?? 0),
            'account_login' => (string) ($row['account_login'] ?? ''),
            'penalty_name' => (string) ($row['penalty_name'] ?? ''),
            'reason' => (string) ($row['reason'] ?? ''),
            'evidence' => (string) ($row['evidence'] ?? ''),
            'days' => $days,
            'days_label' => $isPermanent ? 'Süresiz' : ($days . ' gün'),
            'remaining_label' => $remaining,
            'banned_until' => (!$isPermanent && $untilTs) ? date('Y-m-d H:i:s', $untilTs) : '',
            'banned_until_label' => $isPermanent
                ? 'Süresiz (10.11.1938)'
                : ($untilTs ? date('d.m.Y H:i', $untilTs) : '—'),
            'banned_by_login' => (string) ($row['banned_by_login'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'created_label' => $createdTs ? date('d.m.Y H:i', $createdTs) : '—',
        ];
    }

    public static function isPermanentAvail(string $until): bool
    {
        $until = trim($until);
        if ($until === '' || str_starts_with($until, '0000-00-00')) {
            return false;
        }
        return str_starts_with($until, '1938-11-10');
    }

    private static function parseAvailTs(string $until): int|false
    {
        $until = trim($until);
        if ($until === '' || $until === self::AVAIL_CLEAR || str_starts_with($until, '0000-00-00')) {
            return false;
        }
        if (self::isPermanentAvail($until)) {
            return false; // UI'da süre hesabı yapma
        }
        return strtotime($until);
    }

    private static function mapTemplate(array $row): array
    {
        $days = (int) ($row['days'] ?? 0);
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'reason' => (string) ($row['reason'] ?? ''),
            'days' => $days,
            'days_label' => $days === 0 ? 'Süresiz' : ($days . ' gün'),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
