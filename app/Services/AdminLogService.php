<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;

/**
 * Yönetici panel işlem logları (DNWeb.admin_action_logs).
 */
final class AdminLogService
{
    public const PER_PAGE = 10;

    public static function write(
        ?array $actor,
        string $action,
        string $detail = '',
        ?int $targetAccountId = null,
        ?string $targetLogin = null
    ): void {
        $action = trim($action);
        if ($action === '') {
            return;
        }
        $actorId = (int) ($actor['account_id'] ?? 0);
        $actorLogin = (string) ($actor['login'] ?? '');
        if ($actorId <= 0 && $actorLogin === '') {
            return;
        }

        $tid = $targetAccountId !== null && $targetAccountId > 0 ? $targetAccountId : null;
        $tLogin = trim((string) ($targetLogin ?? ''));
        if ($tid !== null && $tLogin === '') {
            $tLogin = self::loginForAccountId($tid);
        }

        try {
            Database::web()->prepare(
                'INSERT INTO admin_action_logs
                  (actor_account_id, actor_login, target_account_id, target_login, action, detail, ip, created_at)
                 VALUES (?,?,?,?,?,?,?,NOW())'
            )->execute([
                $actorId,
                self::clip($actorLogin, 30),
                $tid,
                self::clip($tLogin, 30),
                self::clip($action, 80),
                self::clip($detail, 1000),
                Security::clientIp(),
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @return array{
     *   rows: list<array>,
     *   total: int,
     *   page: int,
     *   pages: int,
     *   per_page: int,
     *   filter: string
     * }
     */
    public static function list(string $accountFilter = '', int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $accountFilter = trim($accountFilter);
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));

        $empty = [
            'rows' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'per_page' => $perPage,
            'filter' => $accountFilter,
        ];

        try {
            $web = Database::web();
            $where = '1=1';
            $params = [];
            if ($accountFilter !== '') {
                if (ctype_digit($accountFilter)) {
                    $id = (int) $accountFilter;
                    $where .= ' AND (actor_account_id = ? OR target_account_id = ? OR actor_login LIKE ? OR target_login LIKE ?)';
                    $params[] = $id;
                    $params[] = $id;
                    $like = '%' . $accountFilter . '%';
                    $params[] = $like;
                    $params[] = $like;
                } else {
                    $where .= ' AND (actor_login LIKE ? OR target_login LIKE ?)';
                    $like = '%' . $accountFilter . '%';
                    $params[] = $like;
                    $params[] = $like;
                }
            }

            $countStmt = $web->prepare("SELECT COUNT(*) FROM admin_action_logs WHERE {$where}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $web->prepare(
                "SELECT id, actor_account_id, actor_login, target_account_id, target_login, action, detail, ip, created_at
                 FROM admin_action_logs
                 WHERE {$where}
                 ORDER BY id DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $rows = [];
            $needLogin = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $ts = strtotime((string) ($row['created_at'] ?? ''));
                $tid = !empty($row['target_account_id']) ? (int) $row['target_account_id'] : null;
                $tLogin = trim((string) ($row['target_login'] ?? ''));
                $detail = (string) ($row['detail'] ?? '');
                // Eski kayıtlar: hedef alanları boş, detayda "#id · login" olabilir
                if (($tid === null || $tLogin === '') && preg_match('/#(\d+)\s*[·•\-]\s*([^\s·•\-]+)/u', $detail, $m)) {
                    if ($tid === null) {
                        $tid = (int) $m[1];
                    }
                    if ($tLogin === '') {
                        $tLogin = (string) $m[2];
                    }
                }
                if ($tid !== null && $tLogin === '') {
                    $needLogin[$tid] = true;
                }
                $rows[] = [
                    'id' => (int) $row['id'],
                    'actor_account_id' => (int) ($row['actor_account_id'] ?? 0),
                    'actor_login' => (string) ($row['actor_login'] ?? ''),
                    'target_account_id' => $tid,
                    'target_login' => $tLogin,
                    'action' => (string) ($row['action'] ?? ''),
                    'detail' => $detail,
                    'ip' => (string) ($row['ip'] ?? ''),
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'created_label' => $ts ? date('d.m.Y H:i:s', $ts) : '—',
                ];
            }

            if ($needLogin !== []) {
                $map = self::loginsForAccountIds(array_keys($needLogin));
                foreach ($rows as &$r) {
                    $tid = $r['target_account_id'];
                    if ($tid !== null && $r['target_login'] === '' && isset($map[$tid])) {
                        $r['target_login'] = $map[$tid];
                    }
                }
                unset($r);
            }

            return [
                'rows' => $rows,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'filter' => $accountFilter,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }

    private static function loginForAccountId(int $accountId): string
    {
        if ($accountId <= 0) {
            return '';
        }
        $map = self::loginsForAccountIds([$accountId]);

        return $map[$accountId] ?? '';
    }

    /**
     * @param list<int> $ids
     * @return array<int, string>
     */
    private static function loginsForAccountIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = Database::account()->prepare(
                "SELECT id, login FROM account WHERE id IN ({$placeholders})"
            );
            $stmt->execute($ids);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $id = (int) ($row['id'] ?? 0);
                $login = trim((string) ($row['login'] ?? ''));
                if ($id > 0 && $login !== '') {
                    $out[$id] = $login;
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    private static function clip(string $value, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max);
        }
        return substr($value, 0, $max);
    }
}
