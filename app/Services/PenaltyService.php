<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

final class PenaltyService
{
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
        } catch (\Throwable $e) {
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
     * Oyuncuyu banla: account.status = BLOCK + ban kaydı + activity log.
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
            $stmt = $acc->prepare('SELECT id, login, status, WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            // Admin/superadmin banlanmasın (güvenlik)
            $perm = AuthService::normalizePermission($row['WebPermission'] ?? null);
            if ($perm === AuthService::PERM_ADMIN || $perm === AuthService::PERM_SUPER) {
                return ['ok' => false, 'errors' => ['Yönetici hesapları banlanamaz.']];
            }

            $days = (int) $tpl['days'];
            $untilSql = $days > 0 ? 'DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY)' : 'NULL';

            $acc->prepare("UPDATE account SET status = 'BLOCK' WHERE id = ?")->execute([$targetAccountId]);

            $web = Database::web();
            // Eski aktif banları kapat
            $web->prepare(
                'UPDATE account_bans SET is_active = 0, lifted_at = NOW() WHERE account_id = ? AND is_active = 1'
            )->execute([$targetAccountId]);

            $web->prepare(
                "INSERT INTO account_bans
                  (account_id, account_login, penalty_id, penalty_name, reason, evidence, days, banned_until,
                   banned_by_id, banned_by_login, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, {$untilSql}, ?, ?, 1, NOW())"
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
            $stmt = $acc->prepare('SELECT id, login FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }

            $acc->prepare("UPDATE account SET status = 'OK' WHERE id = ?")->execute([$targetAccountId]);
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

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Ban kaldırılamadı.']];
        }
    }

    /** @return list<array> */
    public static function listActiveBans(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        try {
            $stmt = Database::web()->query(
                "SELECT id, account_id, account_login, penalty_name, reason, evidence, days,
                        banned_until, banned_by_login, created_at
                 FROM account_bans
                 WHERE is_active = 1
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $days = (int) ($row['days'] ?? 0);
                $until = (string) ($row['banned_until'] ?? '');
                $untilTs = $until !== '' ? strtotime($until) : false;
                $remaining = 'Süresiz';
                if ($days > 0 && $untilTs) {
                    $sec = $untilTs - time();
                    if ($sec <= 0) {
                        $remaining = 'Süresi doldu';
                    } else {
                        $d = (int) floor($sec / 86400);
                        $remaining = $d > 0 ? ($d . ' gün kaldı') : (max(1, (int) ceil($sec / 3600)) . ' saat kaldı');
                    }
                }
                $out[] = [
                    'id' => (int) $row['id'],
                    'account_id' => (int) $row['account_id'],
                    'account_login' => (string) $row['account_login'],
                    'penalty_name' => (string) $row['penalty_name'],
                    'reason' => (string) $row['reason'],
                    'evidence' => (string) ($row['evidence'] ?? ''),
                    'days' => $days,
                    'days_label' => $days === 0 ? 'Süresiz' : ($days . ' gün'),
                    'remaining_label' => $remaining,
                    'banned_by_login' => (string) $row['banned_by_login'],
                    'created_at' => (string) $row['created_at'],
                    'created_label' => ($ts = strtotime((string) $row['created_at'])) ? date('d.m.Y H:i', $ts) : '—',
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function getActiveBan(int $accountId): ?array
    {
        if ($accountId <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, penalty_name, reason, evidence, days, banned_until, banned_by_login, created_at
                 FROM account_bans WHERE account_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $days = (int) ($row['days'] ?? 0);
            $until = (string) ($row['banned_until'] ?? '');
            $untilTs = $until !== '' ? strtotime($until) : false;
            $remaining = 'Süresiz';
            if ($days > 0 && $untilTs) {
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
                'id' => (int) $row['id'],
                'penalty_name' => (string) $row['penalty_name'],
                'reason' => (string) $row['reason'],
                'evidence' => (string) ($row['evidence'] ?? ''),
                'days' => $days,
                'days_label' => $days === 0 ? 'Süresiz' : ($days . ' gün'),
                'remaining_label' => $remaining,
                'banned_until' => $until,
                'banned_until_label' => $untilTs ? date('d.m.Y H:i', $untilTs) : '—',
                'banned_by_login' => (string) $row['banned_by_login'],
                'created_at' => (string) $row['created_at'],
                'created_label' => $createdTs ? date('d.m.Y H:i', $createdTs) : '—',
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /** Süresi dolmuş banları otomatik kaldır. */
    public static function liftExpired(?string $serverKey = null): void
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $web = Database::web();
            $stmt = $web->query(
                'SELECT id, account_id FROM account_bans
                 WHERE is_active = 1 AND banned_until IS NOT NULL AND banned_until <= NOW()'
            );
            $rows = $stmt->fetchAll() ?: [];
            if ($rows === []) {
                return;
            }
            $acc = Database::account($serverKey);
            foreach ($rows as $row) {
                $aid = (int) $row['account_id'];
                $acc->prepare("UPDATE account SET status = 'OK' WHERE id = ? AND status = 'BLOCK'")->execute([$aid]);
                $web->prepare(
                    'UPDATE account_bans SET is_active = 0, lifted_at = NOW() WHERE id = ?'
                )->execute([(int) $row['id']]);
                ActivityLogService::log($aid, ActivityLogService::ACTION_UNBAN, 'Süre dolumu — otomatik ban kaldırma');
            }
        } catch (\Throwable) {
            // ignore
        }
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
