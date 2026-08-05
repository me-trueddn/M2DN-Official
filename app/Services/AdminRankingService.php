<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * Oyuncu sıralaması — player.player (level DESC)
 */
final class AdminRankingService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];

    /**
     * @return array{
     *   players: list<array>,
     *   total: int,
     *   page: int,
     *   pages: int,
     *   per_page: int,
     *   q: string,
     *   per_page_options: list<int>
     * }
     */
    public static function list(string $q = '', int $page = 1, int $perPage = 10, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);
        $empty = [
            'players' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'per_page' => $perPage,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];

        try {
            $pdo = Database::player($serverKey);
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.name LIKE ? OR g.name LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM player p
                 LEFT JOIN guild_member gm ON gm.pid = p.id
                 LEFT JOIN guild g ON g.id = gm.guild_id
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
                "SELECT p.id, p.account_id, p.name, p.job, p.level, p.stamina, p.exp,
                        g.name AS guild_name,
                        COALESCE(pi.empire, 0) AS empire
                 FROM player p
                 LEFT JOIN guild_member gm ON gm.pid = p.id
                 LEFT JOIN guild g ON g.id = gm.guild_id
                 LEFT JOIN player_index pi ON pi.id = p.account_id
                 WHERE {$whereSql}
                 ORDER BY p.level DESC, p.exp DESC, p.id ASC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $players = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $job = (int) ($row['job'] ?? 0);
                $empire = (int) ($row['empire'] ?? 0);
                $guild = trim((string) ($row['guild_name'] ?? ''));
                $players[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'account_id' => (int) ($row['account_id'] ?? 0),
                    'name' => (string) ($row['name'] ?? ''),
                    'job' => $job,
                    'job_label' => PlayerService::jobLabel($job),
                    'level' => (int) ($row['level'] ?? 0),
                    'stamina' => (int) ($row['stamina'] ?? 0),
                    'guild_name' => $guild !== '' ? $guild : '—',
                    'empire' => $empire,
                    'empire_label' => PlayerService::empireLabel($empire),
                ];
            }

            return [
                'players' => $players,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'q' => $q,
                'per_page_options' => self::PER_PAGE_OPTIONS,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 10;
    }
}
