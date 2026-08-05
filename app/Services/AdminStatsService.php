<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\ServerManager;

/**
 * Admin genel bakış istatistikleri.
 * Gerçek çevrimiçi socket tablosu yok — player.last_play penceresi proxy olarak kullanılır.
 */
final class AdminStatsService
{
    /**
     * @return array{
     *   online:int,
     *   online_window_minutes:int,
     *   registrations_today:int,
     *   registrations_yesterday:int,
     *   revenue_coming_soon:bool,
     *   tickets_coming_soon:bool,
     *   chart:array{labels:list<string>, values:list<int>},
     *   recent_registrations:list<array{login:string, create_time:string}>
     * }
     */
    public static function overview(?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? 'main');
        $window = max(1, (int) Config::get('admin.online_window_minutes', 15));

        $online = self::countOnline($window, $serverKey);
        self::recordOnlineSnapshot($serverKey, $online);

        $regsToday = self::countRegistrationsSince('CURDATE()', $serverKey);
        $regsYesterday = self::countRegistrationsBetween(
            'CURDATE() - INTERVAL 1 DAY',
            'CURDATE()',
            $serverKey
        );

        return [
            'online' => $online,
            'online_window_minutes' => $window,
            'registrations_today' => $regsToday,
            'registrations_yesterday' => $regsYesterday,
            'revenue_coming_soon' => true,
            'tickets_coming_soon' => true,
            'chart' => self::onlineChart($serverKey, 24),
            'recent_registrations' => self::recentRegistrations(8, $serverKey),
        ];
    }

    public static function countOnline(int $windowMinutes, ?string $serverKey = null): int
    {
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                "SELECT COUNT(DISTINCT account_id) FROM player
                 WHERE last_play IS NOT NULL
                   AND last_play <> '0000-00-00 00:00:00'
                   AND last_play >= (NOW() - INTERVAL ? MINUTE)"
            );
            $stmt->execute([$windowMinutes]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function countRegistrationsSince(string $sqlExpr, ?string $serverKey = null): int
    {
        try {
            $pdo = Database::account($serverKey);
            // $sqlExpr güvenli sabit ifadeler (CURDATE() vb.)
            $sql = "SELECT COUNT(*) FROM account
                    WHERE create_time IS NOT NULL
                      AND create_time <> '0000-00-00 00:00:00'
                      AND create_time >= {$sqlExpr}";
            return (int) $pdo->query($sql)->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function countRegistrationsBetween(string $fromExpr, string $toExpr, ?string $serverKey = null): int
    {
        try {
            $pdo = Database::account($serverKey);
            $sql = "SELECT COUNT(*) FROM account
                    WHERE create_time IS NOT NULL
                      AND create_time <> '0000-00-00 00:00:00'
                      AND create_time >= {$fromExpr}
                      AND create_time < {$toExpr}";
            return (int) $pdo->query($sql)->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** @return list<array{login:string, create_time:string}> */
    private static function recentRegistrations(int $limit, ?string $serverKey = null): array
    {
        $limit = max(1, min(50, $limit));
        try {
            $pdo = Database::account($serverKey);
            $stmt = $pdo->query(
                "SELECT login, create_time FROM account
                 WHERE create_time IS NOT NULL AND create_time <> '0000-00-00 00:00:00'
                 ORDER BY create_time DESC
                 LIMIT {$limit}"
            );
            $rows = $stmt->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $row) {
                $out[] = [
                    'login' => (string) ($row['login'] ?? ''),
                    'create_time' => (string) ($row['create_time'] ?? ''),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    private static function recordOnlineSnapshot(string $serverKey, int $online): void
    {
        try {
            $web = Database::web();
            $chk = $web->prepare(
                'SELECT id FROM online_snapshots
                 WHERE server_key = ? AND recorded_at >= (NOW() - INTERVAL 5 MINUTE)
                 ORDER BY recorded_at DESC LIMIT 1'
            );
            $chk->execute([$serverKey]);
            $existingId = $chk->fetchColumn();
            if ($existingId) {
                $web->prepare(
                    'UPDATE online_snapshots SET online_count = ?, recorded_at = NOW() WHERE id = ?'
                )->execute([$online, (int) $existingId]);
                return;
            }

            $web->prepare(
                'INSERT INTO online_snapshots (server_key, online_count, recorded_at) VALUES (?, ?, NOW())'
            )->execute([$serverKey, $online]);

            $web->exec('DELETE FROM online_snapshots WHERE recorded_at < (NOW() - INTERVAL 7 DAY)');
        } catch (\Throwable) {
            // tablo yoksa / hata — grafik boş kalabilir
        }
    }

    /**
     * Son N saat — 5 dk slotlara yuvarlanmış ortalama.
     *
     * @return array{labels:list<string>, values:list<int>}
     */
    private static function onlineChart(string $serverKey, int $hours): array
    {
        $hours = max(1, min(168, $hours));
        $buckets = [];

        try {
            $web = Database::web();
            $stmt = $web->prepare(
                'SELECT online_count, recorded_at FROM online_snapshots
                 WHERE server_key = ? AND recorded_at >= (NOW() - INTERVAL ' . $hours . ' HOUR)
                 ORDER BY recorded_at ASC'
            );
            $stmt->execute([$serverKey]);
            $rows = $stmt->fetchAll() ?: [];

            foreach ($rows as $row) {
                $ts = strtotime((string) $row['recorded_at']);
                if (!$ts) {
                    continue;
                }
                // 5 dakikalık slot
                $slot = (int) (floor($ts / 300) * 300);
                if (!isset($buckets[$slot])) {
                    $buckets[$slot] = ['sum' => 0, 'n' => 0];
                }
                $buckets[$slot]['sum'] += (int) $row['online_count'];
                $buckets[$slot]['n']++;
            }
        } catch (\Throwable) {
            // empty
        }

        $labels = [];
        $values = [];
        ksort($buckets);
        foreach ($buckets as $slot => $agg) {
            $labels[] = date('H:i', $slot);
            $values[] = (int) round($agg['sum'] / max(1, $agg['n']));
        }

        if ($labels === []) {
            $now = self::countOnline(
                max(1, (int) Config::get('admin.online_window_minutes', 15)),
                $serverKey
            );
            $labels = [date('H:i')];
            $values = [$now];
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
