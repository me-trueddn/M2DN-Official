<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * IP ban — player.pcbang_ip + DNWeb.ip_bans (sebep / meta)
 */
final class AdminIpBanService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];
    public const DEFAULT_PCBANG_ID = 0;

    /**
     * @return array{
     *   bans: list<array>,
     *   total: int,
     *   page: int,
     *   pages: int,
     *   per_page: int,
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
            'bans' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'per_page' => $perPage,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];

        try {
            $pdo = Database::player($serverKey);
            $web = Database::web();
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.ip LIKE ? OR CAST(p.pcbang_id AS CHAR) LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM pcbang_ip p WHERE {$whereSql}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT p.id, p.pcbang_id, p.ip
                 FROM pcbang_ip p
                 WHERE {$whereSql}
                 ORDER BY p.id DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll() ?: [];

            $metaByIp = self::metaByIps(array_map(
                static fn(array $r): string => (string) ($r['ip'] ?? ''),
                $rows
            ), $web);

            $bans = [];
            foreach ($rows as $row) {
                $ip = (string) ($row['ip'] ?? '');
                $meta = $metaByIp[$ip] ?? null;
                $ts = $meta ? strtotime((string) ($meta['created_at'] ?? '')) : false;
                $bans[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'pcbang_id' => (int) ($row['pcbang_id'] ?? 0),
                    'ip' => $ip,
                    'reason' => (string) ($meta['reason'] ?? ''),
                    'created_by_login' => (string) ($meta['created_by_login'] ?? ''),
                    'created_at' => (string) ($meta['created_at'] ?? ''),
                    'created_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                ];
            }

            return [
                'bans' => $bans,
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

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function add(
        string $ip,
        string $reason = '',
        int $pcbangId = self::DEFAULT_PCBANG_ID,
        ?array $actor = null,
        ?string $serverKey = null
    ): array {
        $ip = trim($ip);
        $reason = trim($reason);
        $pcbangId = max(0, $pcbangId);

        $errors = [];
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $errors[] = 'Geçerli bir IP adresi girin.';
        }
        if (strlen($ip) > 15) {
            $errors[] = 'IP en fazla 15 karakter olmalı.';
        }
        if (mb_strlen($reason) > 500) {
            $errors[] = 'Sebep en fazla 500 karakter.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $web = Database::web();

            $dup = $pdo->prepare('SELECT id FROM pcbang_ip WHERE ip = ? LIMIT 1');
            $dup->execute([$ip]);
            if ($dup->fetch()) {
                return ['ok' => false, 'errors' => ['Bu IP zaten listede.']];
            }

            $pdo->prepare('INSERT INTO pcbang_ip (pcbang_id, ip) VALUES (?, ?)')->execute([$pcbangId, $ip]);
            $id = (int) $pdo->lastInsertId();

            $web->prepare(
                'INSERT INTO ip_bans (ip, reason, pcbang_ip_id, pcbang_id, created_by_id, created_by_login, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                   reason = VALUES(reason),
                   pcbang_ip_id = VALUES(pcbang_ip_id),
                   pcbang_id = VALUES(pcbang_id),
                   created_by_id = VALUES(created_by_id),
                   created_by_login = VALUES(created_by_login),
                   created_at = NOW()'
            )->execute([
                $ip,
                $reason,
                $id > 0 ? $id : null,
                $pcbangId,
                (int) ($actor['id'] ?? 0),
                (string) ($actor['login'] ?? ''),
            ]);

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['IP eklenemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function delete(int $id, ?string $serverKey = null): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz kayıt.']];
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $web = Database::web();

            $stmt = $pdo->prepare('SELECT id, ip FROM pcbang_ip WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['IP kaydı bulunamadı.']];
            }
            $ip = (string) ($row['ip'] ?? '');

            $pdo->prepare('DELETE FROM pcbang_ip WHERE id = ? LIMIT 1')->execute([$id]);
            if ($ip !== '') {
                $web->prepare('DELETE FROM ip_bans WHERE ip = ? LIMIT 1')->execute([$ip]);
            } else {
                $web->prepare('DELETE FROM ip_bans WHERE pcbang_ip_id = ? LIMIT 1')->execute([$id]);
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['IP silinemedi.']];
        }
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 20;
    }

    /**
     * @param list<string> $ips
     * @return array<string, array>
     */
    private static function metaByIps(array $ips, \PDO $web): array
    {
        $ips = array_values(array_unique(array_filter(array_map('strval', $ips), static fn(string $ip): bool => $ip !== '')));
        if ($ips === []) {
            return [];
        }
        try {
            $placeholders = implode(',', array_fill(0, count($ips), '?'));
            $stmt = $web->prepare(
                "SELECT ip, reason, created_by_login, created_at
                 FROM ip_bans WHERE ip IN ({$placeholders})"
            );
            $stmt->execute($ips);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $out[(string) $row['ip']] = $row;
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
