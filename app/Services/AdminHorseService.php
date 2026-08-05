<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * Binek (at) isimleri — player.horse_name
 * horse_name.id = player.id
 */
final class AdminHorseService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];
    public const MAX_NAME_LEN = 24;

    /**
     * @return array{
     *   horses: list<array>,
     *   total: int,
     *   page: int,
     *   per_page: int,
     *   pages: int,
     *   q: string,
     *   per_page_options: list<int>
     * }
     */
    public static function listHorses(string $q = '', int $page = 1, int $perPage = 10, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);

        $empty = [
            'horses' => [],
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

        $where = ['1=1'];
        $params = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $accountIds = self::accountIdsByLogin($q, $serverKey);
            if ($accountIds !== []) {
                $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
                $where[] = "(h.name LIKE ? OR p.name LIKE ? OR p.account_id IN ({$placeholders}))";
                $params[] = $like;
                $params[] = $like;
                foreach ($accountIds as $aid) {
                    $params[] = $aid;
                }
            } else {
                $where[] = '(h.name LIKE ? OR p.name LIKE ?)';
                $params[] = $like;
                $params[] = $like;
            }
        }

        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM horse_name h
                 LEFT JOIN player p ON p.id = h.id
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
                "SELECT h.id, h.name AS horse_name,
                        p.name AS character_name, p.level, p.job, p.account_id,
                        p.horse_level
                 FROM horse_name h
                 LEFT JOIN player p ON p.id = h.id
                 WHERE {$whereSql}
                 ORDER BY h.id DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return $empty;
        }

        $accountIds = [];
        foreach ($rows as $row) {
            $aid = (int) ($row['account_id'] ?? 0);
            if ($aid > 0) {
                $accountIds[$aid] = $aid;
            }
        }
        $logins = self::loginsByAccountIds(array_values($accountIds), $serverKey);

        $horses = [];
        foreach ($rows as $row) {
            $aid = (int) ($row['account_id'] ?? 0);
            $job = (int) ($row['job'] ?? 0);
            $pid = (int) ($row['id'] ?? 0);
            $horses[] = [
                'id' => $pid,
                'horse_name' => (string) ($row['horse_name'] ?? ''),
                'character_name' => (string) ($row['character_name'] ?? '') !== ''
                    ? (string) $row['character_name']
                    : ('#' . $pid),
                'level' => (int) ($row['level'] ?? 0),
                'job' => $job,
                'job_label' => PlayerService::jobLabel($job),
                'account_id' => $aid,
                'account_login' => $logins[$aid] ?? ($aid > 0 ? ('#' . $aid) : '—'),
                'horse_level' => (int) ($row['horse_level'] ?? 0),
                'orphan' => (string) ($row['character_name'] ?? '') === '',
            ];
        }

        return [
            'horses' => $horses,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function rename(int $playerId, string $newName, ?string $serverKey = null): array
    {
        $newName = trim($newName);
        if ($playerId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz binek kaydı.']];
        }
        if ($newName === '' || mb_strlen($newName) > self::MAX_NAME_LEN) {
            return ['ok' => false, 'errors' => ['At adı 1–' . self::MAX_NAME_LEN . ' karakter olmalı.']];
        }
        if (!preg_match('/^[\p{L}\p{N}_\-\s]+$/u', $newName)) {
            return ['ok' => false, 'errors' => ['At adında geçersiz karakter var.']];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $cur = $pdo->prepare('SELECT id, name FROM horse_name WHERE id = ? LIMIT 1');
            $cur->execute([$playerId]);
            $row = $cur->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Binek kaydı bulunamadı.']];
            }
            if ((string) ($row['name'] ?? '') === $newName) {
                return ['ok' => true, 'errors' => []];
            }
            $upd = $pdo->prepare('UPDATE horse_name SET name = ? WHERE id = ? LIMIT 1');
            $upd->execute([$newName, $playerId]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['At adı güncellenemedi.']];
        }
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 10;
    }

    /** @return list<int> */
    private static function accountIdsByLogin(string $q, ?string $serverKey): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        try {
            $pdo = Database::account($serverKey);
            $stmt = $pdo->prepare('SELECT id FROM account WHERE login LIKE ? LIMIT 200');
            $stmt->execute(['%' . $q . '%']);
            $ids = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
            return $ids;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param list<int> $accountIds
     * @return array<int, string>
     */
    private static function loginsByAccountIds(array $accountIds, ?string $serverKey): array
    {
        $accountIds = array_values(array_unique(array_filter(
            array_map('intval', $accountIds),
            static fn(int $id): bool => $id > 0
        )));
        if ($accountIds === []) {
            return [];
        }
        try {
            $pdo = Database::account($serverKey);
            $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
            $stmt = $pdo->prepare("SELECT id, login FROM account WHERE id IN ({$placeholders})");
            $stmt->execute($accountIds);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $out[(int) $row['id']] = (string) ($row['login'] ?? '');
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
