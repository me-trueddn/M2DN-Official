<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use App\Core\ServerManager;

/**
 * Web paneli hesap işlem logları (DNWeb.account_activity_log).
 */
final class ActivityLogService
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_PASSWORD = 'password_change';
    public const ACTION_SECURITY_CODE = 'security_code_change';
    public const ACTION_2FA_START = '2fa_start';
    public const ACTION_2FA_ENABLE = '2fa_enable';
    public const ACTION_2FA_DISABLE = '2fa_disable';
    public const ACTION_IP_LOCK_ON = 'ip_lock_on';
    public const ACTION_IP_LOCK_OFF = 'ip_lock_off';
    public const ACTION_REGISTER = 'register';
    public const ACTION_BAN = 'ban';
    public const ACTION_UNBAN = 'unban';

    private const LABELS = [
        self::ACTION_LOGIN => 'Panele giriş yapıldı',
        self::ACTION_LOGOUT => 'Panelden çıkış yapıldı',
        self::ACTION_PASSWORD => 'Hesap parolası değiştirildi',
        self::ACTION_SECURITY_CODE => 'Depo / güvenli şifre değiştirildi',
        self::ACTION_2FA_START => '2FA kurulumu başlatıldı',
        self::ACTION_2FA_ENABLE => '2FA aktif edildi',
        self::ACTION_2FA_DISABLE => '2FA kapatıldı',
        self::ACTION_IP_LOCK_ON => 'IP kilidi açıldı',
        self::ACTION_IP_LOCK_OFF => 'IP kilidi kapatıldı',
        self::ACTION_REGISTER => 'Hesap oluşturuldu',
        self::ACTION_BAN => 'Hesap banlandı',
        self::ACTION_UNBAN => 'Ban kaldırıldı',
    ];

    public static function log(
        int $accountId,
        string $action,
        string $detail = '',
        ?string $login = null,
        ?int $actorId = null,
        ?string $actorLogin = null,
        string $evidence = ''
    ): void {
        if ($accountId <= 0 || $action === '') {
            return;
        }

        try {
            $web = Database::web();
            $web->prepare(
                'INSERT INTO account_activity_log
                  (account_id, account_login, action, detail, evidence, actor_account_id, actor_login, ip, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            )->execute([
                $accountId,
                $login ?? '',
                $action,
                self::clip($detail, 500),
                self::clip($evidence, 1000),
                $actorId,
                $actorLogin ?? '',
                Security::clientIp(),
                self::clip((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255),
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }

    public static function label(string $action): string
    {
        return self::LABELS[$action] ?? $action;
    }

    /** @return list<array> */
    public static function forAccount(int $accountId, int $limit = 50): array
    {
        if ($accountId <= 0) {
            return [];
        }
        $limit = max(1, min(200, $limit));

        try {
            $web = Database::web();
            $stmt = $web->prepare(
                "SELECT id, action, detail, evidence, actor_login, ip, created_at
                 FROM account_activity_log
                 WHERE account_id = ?
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $stmt->execute([$accountId]);
            return self::mapRows($stmt->fetchAll() ?: []);
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> */
    public static function gameLoginLogs(int $accountId, int $limit = 30, ?string $serverKey = null): array
    {
        if ($accountId <= 0) {
            return [];
        }
        $limit = max(1, min(100, $limit));
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);

        try {
            $pdo = Database::log($serverKey);
            $stmt = $pdo->prepare(
                "SELECT type, time, channel, account_id, pid, level, job, playtime
                 FROM loginlog
                 WHERE account_id = ?
                 ORDER BY time DESC
                 LIMIT {$limit}"
            );
            $stmt->execute([$accountId]);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $ts = strtotime((string) ($row['time'] ?? ''));
                $type = strtoupper((string) ($row['type'] ?? ''));
                $out[] = [
                    'type' => $type,
                    'type_label' => $type === 'LOGIN' ? 'Oyuna giriş' : ($type === 'LOGOUT' ? 'Oyundan çıkış' : $type),
                    'time' => (string) ($row['time'] ?? ''),
                    'time_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                    'channel' => (int) ($row['channel'] ?? 0),
                    'pid' => (int) ($row['pid'] ?? 0),
                    'level' => (int) ($row['level'] ?? 0),
                    'job' => (int) ($row['job'] ?? 0),
                    'playtime' => (int) ($row['playtime'] ?? 0),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param list<array> $rows */
    private static function mapRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $ts = strtotime((string) ($row['created_at'] ?? ''));
            $action = (string) ($row['action'] ?? '');
            $actor = trim((string) ($row['actor_login'] ?? ''));
            $evidence = trim((string) ($row['evidence'] ?? ''));
            $out[] = [
                'id' => (int) ($row['id'] ?? 0),
                'action' => $action,
                'action_label' => self::label($action),
                'detail' => (string) ($row['detail'] ?? ''),
                'evidence' => $evidence,
                'actor_login' => $actor,
                'ip' => (string) ($row['ip'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'created_label' => $ts ? date('d.m.Y H:i:s', $ts) : '—',
            ];
        }
        return $out;
    }

    private static function clip(string $value, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max);
        }
        return substr($value, 0, $max);
    }
}
