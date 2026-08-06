<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * player.marriage — evlilik listesi ve bitirme.
 */
final class MarriageService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];

    /**
     * @return array{
     *   rows: list<array>,
     *   total: int,
     *   page: int,
     *   per_page: int,
     *   pages: int,
     *   q: string,
     *   per_page_options: list<int>
     * }
     */
    public static function list(string $q = '', int $page = 1, int $perPage = 20, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);

        $empty = [
            'rows' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => $perPage,
            'pages' => 1,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];

        try {
            $pdo = Database::player($serverKey);
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['(m.is_married = 1 OR m.is_married IS NULL)'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p1.name LIKE ? OR p2.name LIKE ? OR CAST(m.pid1 AS CHAR) = ? OR CAST(m.pid2 AS CHAR) = ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $q;
            $params[] = $q;
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM marriage m
                 LEFT JOIN player p1 ON p1.id = m.pid1
                 LEFT JOIN player p2 ON p2.id = m.pid2
                 WHERE {$whereSql}"
            );
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT m.is_married, m.pid1, m.pid2, m.love_point, m.time,
                        p1.name AS name1, p1.level AS level1, p1.job AS job1, p1.account_id AS account_id1,
                        p2.name AS name2, p2.level AS level2, p2.job AS job2, p2.account_id AS account_id2
                 FROM marriage m
                 LEFT JOIN player p1 ON p1.id = m.pid1
                 LEFT JOIN player p2 ON p2.id = m.pid2
                 WHERE {$whereSql}
                 ORDER BY m.time DESC, m.pid1 ASC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $raw = $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return $empty;
        }

        $rows = [];
        foreach ($raw as $row) {
            $rows[] = self::mapRow($row);
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];
    }

    /**
     * Evliliği bitir (satırı sil). Admin WebPermission >= 1.
     *
     * @return array{ok: bool, error?: string, name1?: string, name2?: string, account_id1?: int, account_id2?: int}
     */
    public static function divorce(int $pid1, int $pid2, ?string $serverKey = null): array
    {
        if ($pid1 <= 0 || $pid2 <= 0) {
            return ['ok' => false, 'error' => 'Geçersiz karakter.'];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);

        try {
            $pdo = Database::player($serverKey);
            $find = $pdo->prepare(
                'SELECT m.pid1, m.pid2, p1.name AS name1, p2.name AS name2,
                        p1.account_id AS account_id1, p2.account_id AS account_id2
                 FROM marriage m
                 LEFT JOIN player p1 ON p1.id = m.pid1
                 LEFT JOIN player p2 ON p2.id = m.pid2
                 WHERE (m.pid1 = ? AND m.pid2 = ?) OR (m.pid1 = ? AND m.pid2 = ?)
                 LIMIT 1'
            );
            $find->execute([$pid1, $pid2, $pid2, $pid1]);
            $row = $find->fetch();
            if (!$row) {
                return ['ok' => false, 'error' => 'Evlilik kaydı bulunamadı.'];
            }

            $del = $pdo->prepare(
                'DELETE FROM marriage WHERE (pid1 = ? AND pid2 = ?) OR (pid1 = ? AND pid2 = ?)'
            );
            $del->execute([
                (int) $row['pid1'],
                (int) $row['pid2'],
                (int) $row['pid2'],
                (int) $row['pid1'],
            ]);

            if ($del->rowCount() < 1) {
                return ['ok' => false, 'error' => 'Evlilik silinemedi.'];
            }

            return [
                'ok' => true,
                'name1' => (string) ($row['name1'] ?? ('#' . (int) $row['pid1'])),
                'name2' => (string) ($row['name2'] ?? ('#' . (int) $row['pid2'])),
                'account_id1' => (int) ($row['account_id1'] ?? 0),
                'account_id2' => (int) ($row['account_id2'] ?? 0),
            ];
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Veritabanı hatası.'];
        }
    }

    /**
     * Karakter listesine eş bilgisi ekler.
     *
     * @param list<array> $characters
     * @return list<array>
     */
    public static function attachSpouses(array $characters, ?string $serverKey = null): array
    {
        if ($characters === []) {
            return $characters;
        }

        $ids = [];
        foreach ($characters as $ch) {
            $id = (int) ($ch['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return $characters;
        }

        $map = self::spousesByPlayerIds($ids, $serverKey);
        foreach ($characters as &$ch) {
            $id = (int) ($ch['id'] ?? 0);
            $spouse = $map[$id] ?? null;
            $ch['married'] = $spouse !== null;
            $ch['spouse'] = $spouse;
            $ch['spouse_name'] = $spouse['name'] ?? null;
            $ch['spouse_id'] = $spouse['id'] ?? null;
        }
        unset($ch);

        return $characters;
    }

    /**
     * Tek karakter için eş bilgisi.
     *
     * @return array{id:int,name:string,level:int,job:int,job_label:string,account_id:int}|null
     */
    public static function spouseForPlayer(int $playerId, ?string $serverKey = null): ?array
    {
        if ($playerId <= 0) {
            return null;
        }
        $map = self::spousesByPlayerIds([$playerId], $serverKey);
        return $map[$playerId] ?? null;
    }

    /**
     * @param list<int> $playerIds
     * @return array<int, array{id:int,name:string,level:int,job:int,job_label:string,account_id:int}>
     */
    public static function spousesByPlayerIds(array $playerIds, ?string $serverKey = null): array
    {
        $playerIds = array_values(array_unique(array_filter(array_map('intval', $playerIds), static fn(int $id): bool => $id > 0)));
        if ($playerIds === []) {
            return [];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);

        try {
            $pdo = Database::player($serverKey);
            $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
            $sql = "SELECT m.pid1, m.pid2,
                           p1.name AS name1, p1.level AS level1, p1.job AS job1, p1.account_id AS account_id1,
                           p2.name AS name2, p2.level AS level2, p2.job AS job2, p2.account_id AS account_id2
                    FROM marriage m
                    LEFT JOIN player p1 ON p1.id = m.pid1
                    LEFT JOIN player p2 ON p2.id = m.pid2
                    WHERE (m.is_married = 1 OR m.is_married IS NULL)
                      AND (m.pid1 IN ({$placeholders}) OR m.pid2 IN ({$placeholders}))";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($playerIds, $playerIds));
            $rows = $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        $wanted = array_flip($playerIds);
        foreach ($rows as $row) {
            $pid1 = (int) ($row['pid1'] ?? 0);
            $pid2 = (int) ($row['pid2'] ?? 0);
            if (isset($wanted[$pid1]) && !isset($out[$pid1])) {
                $out[$pid1] = [
                    'id' => $pid2,
                    'name' => (string) (($row['name2'] ?? '') !== '' ? $row['name2'] : ('#' . $pid2)),
                    'level' => (int) ($row['level2'] ?? 0),
                    'job' => (int) ($row['job2'] ?? 0),
                    'job_label' => PlayerService::jobLabel((int) ($row['job2'] ?? 0)),
                    'account_id' => (int) ($row['account_id2'] ?? 0),
                ];
            }
            if (isset($wanted[$pid2]) && !isset($out[$pid2])) {
                $out[$pid2] = [
                    'id' => $pid1,
                    'name' => (string) (($row['name1'] ?? '') !== '' ? $row['name1'] : ('#' . $pid1)),
                    'level' => (int) ($row['level1'] ?? 0),
                    'job' => (int) ($row['job1'] ?? 0),
                    'job_label' => PlayerService::jobLabel((int) ($row['job1'] ?? 0)),
                    'account_id' => (int) ($row['account_id1'] ?? 0),
                ];
            }
        }

        return $out;
    }

    private static function mapRow(array $row): array
    {
        $pid1 = (int) ($row['pid1'] ?? 0);
        $pid2 = (int) ($row['pid2'] ?? 0);
        $time = (int) ($row['time'] ?? 0);
        $job1 = (int) ($row['job1'] ?? 0);
        $job2 = (int) ($row['job2'] ?? 0);

        return [
            'pid1' => $pid1,
            'pid2' => $pid2,
            'is_married' => (int) ($row['is_married'] ?? 1),
            'love_point' => $row['love_point'] !== null ? (int) $row['love_point'] : null,
            'time' => $time,
            'time_label' => $time > 0 ? date('d.m.Y H:i', $time) : '—',
            'name1' => (string) (($row['name1'] ?? '') !== '' ? $row['name1'] : ('#' . $pid1)),
            'name2' => (string) (($row['name2'] ?? '') !== '' ? $row['name2'] : ('#' . $pid2)),
            'level1' => (int) ($row['level1'] ?? 0),
            'level2' => (int) ($row['level2'] ?? 0),
            'job1' => $job1,
            'job2' => $job2,
            'job_label1' => PlayerService::jobLabel($job1),
            'job_label2' => PlayerService::jobLabel($job2),
            'account_id1' => (int) ($row['account_id1'] ?? 0),
            'account_id2' => (int) ($row['account_id2'] ?? 0),
        ];
    }

    private static function normalizePerPage(int $perPage): int
    {
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return 20;
        }
        return $perPage;
    }
}
