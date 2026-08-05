<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

final class AdminPlayerService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];

    /**
     * @return array{
     *   accounts: list<array>,
     *   total: int,
     *   page: int,
     *   per_page: int,
     *   pages: int,
     *   q: string,
     *   status: string,
     *   per_page_options: list<int>
     * }
     */
    public static function listAccounts(
        string $q = '',
        string $status = '',
        int $page = 1,
        int $perPage = 10,
        ?string $serverKey = null
    ): array {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $status = strtoupper(trim($status));
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);

        $where = ['1=1'];
        $params = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $charAccountIds = self::accountIdsByCharacterName($q, $serverKey);
            if ($charAccountIds !== []) {
                $placeholders = implode(',', array_fill(0, count($charAccountIds), '?'));
                $where[] = "(login LIKE ? OR email LIKE ? OR id IN ({$placeholders}))";
                $params[] = $like;
                $params[] = $like;
                foreach ($charAccountIds as $aid) {
                    $params[] = $aid;
                }
            } else {
                $where[] = '(login LIKE ? OR email LIKE ?)';
                $params[] = $like;
                $params[] = $like;
            }
        }

        if ($status === 'OK' || $status === 'BLOCK') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $where);
        $pdo = Database::account($serverKey);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            "SELECT id, login, email, status, ip, create_time, WebPermission, cash
             FROM account
             WHERE {$whereSql}
             ORDER BY id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll() ?: [];

        $accountIds = array_map(static fn(array $r): int => (int) $r['id'], $rows);
        $firstChars = self::firstCharacters($accountIds, $serverKey);
        $staffMeta = PermissionService::staffMetaForAccounts($accountIds);

        $accounts = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $st = strtoupper((string) ($row['status'] ?? ''));
            $char = $firstChars[$id] ?? null;
            $createLabel = '—';
            $ts = strtotime((string) ($row['create_time'] ?? ''));
            if ($ts && (string) $row['create_time'] !== '0000-00-00 00:00:00') {
                $months = [
                    'Jan' => 'Oca', 'Feb' => 'Şub', 'Mar' => 'Mar', 'Apr' => 'Nis',
                    'May' => 'May', 'Jun' => 'Haz', 'Jul' => 'Tem', 'Aug' => 'Ağu',
                    'Sep' => 'Eyl', 'Oct' => 'Eki', 'Nov' => 'Kas', 'Dec' => 'Ara',
                ];
                $createLabel = strtr(date('M Y', $ts), $months);
            }

            $email = trim((string) ($row['email'] ?? ''));
            $webPerm = AuthService::normalizePermission($row['WebPermission'] ?? null);
            $isStaff = $webPerm === AuthService::PERM_ADMIN || $webPerm === AuthService::PERM_SUPER;
            $staff = $staffMeta[$id] ?? null;
            $roleLabel = 'Oyuncu';
            if (!empty($staff['group_name'])) {
                $roleLabel = (string) $staff['group_name'];
            } elseif ($isStaff) {
                $sysId = PermissionService::systemGroupId($webPerm);
                $sysName = $sysId ? PermissionService::groupNameById($sysId) : '';
                $roleLabel = $sysName !== '' ? $sysName : ($webPerm === AuthService::PERM_SUPER ? 'Süper Admin' : 'Yönetici');
            }

            $accounts[] = [
                'id' => $id,
                'login' => (string) ($row['login'] ?? ''),
                'email' => $email !== '' ? $email : '—',
                'status' => $st,
                'status_label' => $st === 'BLOCK' ? 'Banlı' : ($st === 'OK' ? 'Aktif' : $st),
                'status_badge' => $st === 'BLOCK' ? 'banned' : 'active',
                'ip' => (string) ($row['ip'] ?? '') !== '' ? (string) $row['ip'] : '—',
                'create_time' => (string) ($row['create_time'] ?? ''),
                'create_label' => $createLabel,
                'cash' => (int) ($row['cash'] ?? 0),
                'character_name' => $char['name'] ?? '—',
                'character_level' => $char['level'] ?? null,
                'character_count' => $char['count'] ?? 0,
                'web_permission' => $webPerm,
                'role_label' => $roleLabel,
                'role_badge' => $isStaff ? 'pending' : 'closed',
                'staff_group_id' => $staff['group_id'] ?? null,
                'staff_group_name' => $staff['group_name'] ?? ($roleLabel !== 'Oyuncu' ? $roleLabel : null),
            ];
        }

        return [
            'accounts' => $accounts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
            'q' => $q,
            'status' => $status === 'OK' || $status === 'BLOCK' ? $status : '',
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];
    }

    /**
     * Üst çubuk anlık arama (hesap / e-posta / karakter).
     *
     * @return list<array{id:int, login:string, email:string, character_name:string, status_label:string, status_badge:string, role_label:string}>
     */
    public static function searchSuggest(string $q, int $limit = 12, ?string $serverKey = null): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $limit = max(1, min(30, $limit));
        $result = self::listAccounts($q, '', 1, $limit, $serverKey);
        $out = [];
        foreach ($result['accounts'] as $acc) {
            $out[] = [
                'id' => (int) $acc['id'],
                'login' => (string) $acc['login'],
                'email' => (string) $acc['email'],
                'character_name' => (string) ($acc['character_name'] ?? '—'),
                'status_label' => (string) ($acc['status_label'] ?? ''),
                'status_badge' => (string) ($acc['status_badge'] ?? ''),
                'role_label' => (string) ($acc['role_label'] ?? 'Oyuncu'),
            ];
        }
        return $out;
    }

    /**
     * @return array{
     *   account: array,
     *   characters: list<array>,
     *   activity: list<array>,
     *   game_logins: list<array>,
     *   security: array
     * }|null
     */
    public static function accountDetail(int $accountId, ?string $serverKey = null): ?array
    {
        if ($accountId <= 0) {
            return null;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);

        try {
            $pdo = Database::account($serverKey);
            $stmt = $pdo->prepare(
                'SELECT id, login, email, status, ip, create_time, WebPermission, cash, mileage, total_cash
                 FROM account WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $st = strtoupper((string) ($row['status'] ?? ''));
        $ts = strtotime((string) ($row['create_time'] ?? ''));
        $account = [
            'id' => (int) $row['id'],
            'login' => (string) $row['login'],
            'email' => (string) ($row['email'] ?? ''),
            'status' => $st,
            'status_label' => $st === 'BLOCK' ? 'Banlı' : ($st === 'OK' ? 'Aktif' : $st),
            'status_badge' => $st === 'BLOCK' ? 'banned' : 'active',
            'ip' => (string) ($row['ip'] ?? '') !== '' ? (string) $row['ip'] : '—',
            'create_time' => (string) ($row['create_time'] ?? ''),
            'create_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
            'cash' => (int) ($row['cash'] ?? 0),
            'mileage' => (int) ($row['mileage'] ?? 0),
            'web_permission' => AuthService::normalizePermission($row['WebPermission'] ?? null),
        ];

        $dashboard = PlayerService::dashboard($accountId, $serverKey);
        $security = AccountSecurityService::getSettings($accountId);

        return [
            'account' => $account,
            'characters' => $dashboard['characters'],
            'activity' => ActivityLogService::forAccount($accountId, 80),
            'game_logins' => ActivityLogService::gameLoginLogs($accountId, 40, $serverKey),
            'security' => $security,
            'active_ban' => PenaltyService::getActiveBan($accountId),
        ];
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 10;
    }

    /** @return list<int> */
    private static function accountIdsByCharacterName(string $q, ?string $serverKey): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                'SELECT DISTINCT account_id FROM player WHERE name LIKE ? LIMIT 500'
            );
            $stmt->execute(['%' . $q . '%']);
            $ids = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $id = (int) ($row['account_id'] ?? 0);
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
     * @return array<int, array{name:string, level:int, count:int}>
     */
    private static function firstCharacters(array $accountIds, ?string $serverKey): array
    {
        $accountIds = array_values(array_filter(array_map('intval', $accountIds), static fn(int $id): bool => $id > 0));
        if ($accountIds === []) {
            return [];
        }

        try {
            $pdo = Database::player($serverKey);
            $placeholders = implode(',', array_fill(0, count($accountIds), '?'));

            $cntStmt = $pdo->prepare(
                "SELECT account_id, COUNT(*) AS c FROM player WHERE account_id IN ({$placeholders}) GROUP BY account_id"
            );
            $cntStmt->execute($accountIds);
            $counts = [];
            foreach ($cntStmt->fetchAll() ?: [] as $r) {
                $counts[(int) $r['account_id']] = (int) $r['c'];
            }

            $stmt = $pdo->prepare(
                "SELECT p.account_id, p.name, p.level
                 FROM player p
                 INNER JOIN (
                   SELECT account_id, MIN(id) AS min_id
                   FROM player
                   WHERE account_id IN ({$placeholders})
                   GROUP BY account_id
                 ) f ON f.account_id = p.account_id AND f.min_id = p.id"
            );
            $stmt->execute($accountIds);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $r) {
                $aid = (int) $r['account_id'];
                $out[$aid] = [
                    'name' => (string) ($r['name'] ?? ''),
                    'level' => (int) ($r['level'] ?? 1),
                    'count' => $counts[$aid] ?? 1,
                ];
            }

            foreach ($accountIds as $aid) {
                if (!isset($out[$aid])) {
                    $out[$aid] = [
                        'name' => '—',
                        'level' => 0,
                        'count' => $counts[$aid] ?? 0,
                    ];
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
