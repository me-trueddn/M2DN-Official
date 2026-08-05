<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * GM listesi — common.gmlist
 */
final class AdminGmService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];

    /** @var list<string> */
    public const AUTHORITIES = [
        'IMPLEMENTOR',
        'HIGH_WIZARD',
        'GOD',
        'LOW_WIZARD',
        'PLAYER',
    ];

    /** @var array<string, string> */
    public const AUTHORITY_LABELS = [
        'IMPLEMENTOR' => 'IMPLEMENTOR',
        'HIGH_WIZARD' => 'HIGH_WIZARD',
        'GOD' => 'GOD',
        'LOW_WIZARD' => 'LOW_WIZARD',
        'PLAYER' => 'PLAYER',
    ];

    /**
     * @return array{
     *   gms: list<array>,
     *   total: int,
     *   page: int,
     *   pages: int,
     *   per_page: int,
     *   q: string,
     *   per_page_options: list<int>,
     *   authorities: array<string,string>
     * }
     */
    public static function list(string $q = '', int $page = 1, int $perPage = 20, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);
        $empty = [
            'gms' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'per_page' => $perPage,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
            'authorities' => self::AUTHORITY_LABELS,
        ];

        try {
            $pdo = Database::common($serverKey);
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(mAccount LIKE ? OR mName LIKE ? OR mContactIP LIKE ? OR mServerIP LIKE ? OR mAuthority LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM gmlist WHERE {$whereSql}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT mID, mAccount, mName, mContactIP, mServerIP, mAuthority
                 FROM gmlist
                 WHERE {$whereSql}
                 ORDER BY mID ASC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $gms = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $gms[] = self::mapRow($row);
            }

            return [
                'gms' => $gms,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'q' => $q,
                'per_page_options' => self::PER_PAGE_OPTIONS,
                'authorities' => self::AUTHORITY_LABELS,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }

    /** @return array|null */
    public static function get(int $id, ?string $serverKey = null): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::common($serverKey);
            $stmt = $pdo->prepare(
                'SELECT mID, mAccount, mName, mContactIP, mServerIP, mAuthority FROM gmlist WHERE mID = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? self::mapRow($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function add(
        string $account,
        string $name,
        string $authority,
        string $contactIp = '',
        string $serverIp = 'ALL',
        ?string $serverKey = null
    ): array {
        $data = self::normalizeInput($account, $name, $authority, $contactIp, $serverIp);
        if ($data['errors'] !== []) {
            return ['ok' => false, 'errors' => $data['errors']];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::common($serverKey);
            $dup = $pdo->prepare('SELECT mID FROM gmlist WHERE mAccount = ? AND mName = ? LIMIT 1');
            $dup->execute([$data['account'], $data['name']]);
            if ($dup->fetch()) {
                return ['ok' => false, 'errors' => ['Bu hesap + karakter zaten GM listesinde.']];
            }
            $pdo->prepare(
                'INSERT INTO gmlist (mAccount, mName, mContactIP, mServerIP, mAuthority)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $data['account'],
                $data['name'],
                $data['contact_ip'],
                $data['server_ip'],
                $data['authority'],
            ]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['GM eklenemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function update(
        int $id,
        string $account,
        string $name,
        string $authority,
        string $contactIp = '',
        string $serverIp = 'ALL',
        ?string $serverKey = null
    ): array {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz GM kaydı.']];
        }
        $data = self::normalizeInput($account, $name, $authority, $contactIp, $serverIp);
        if ($data['errors'] !== []) {
            return ['ok' => false, 'errors' => $data['errors']];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::common($serverKey);
            $cur = $pdo->prepare('SELECT mID FROM gmlist WHERE mID = ? LIMIT 1');
            $cur->execute([$id]);
            if (!$cur->fetch()) {
                return ['ok' => false, 'errors' => ['GM kaydı bulunamadı.']];
            }
            $dup = $pdo->prepare('SELECT mID FROM gmlist WHERE mAccount = ? AND mName = ? AND mID <> ? LIMIT 1');
            $dup->execute([$data['account'], $data['name'], $id]);
            if ($dup->fetch()) {
                return ['ok' => false, 'errors' => ['Bu hesap + karakter başka bir kayıtta var.']];
            }
            $pdo->prepare(
                'UPDATE gmlist
                 SET mAccount = ?, mName = ?, mContactIP = ?, mServerIP = ?, mAuthority = ?
                 WHERE mID = ? LIMIT 1'
            )->execute([
                $data['account'],
                $data['name'],
                $data['contact_ip'],
                $data['server_ip'],
                $data['authority'],
                $id,
            ]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['GM güncellenemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function delete(int $id, ?string $serverKey = null): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz GM kaydı.']];
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::common($serverKey);
            $stmt = $pdo->prepare('DELETE FROM gmlist WHERE mID = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->rowCount() < 1) {
                return ['ok' => false, 'errors' => ['GM kaydı bulunamadı.']];
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['GM silinemedi.']];
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
     * @return array{account:string,name:string,authority:string,contact_ip:string,server_ip:string,errors:list<string>}
     */
    private static function normalizeInput(
        string $account,
        string $name,
        string $authority,
        string $contactIp,
        string $serverIp
    ): array {
        $account = trim($account);
        $name = trim($name);
        $authority = strtoupper(trim($authority));
        $contactIp = trim($contactIp);
        $serverIp = trim($serverIp);
        if ($serverIp === '') {
            $serverIp = 'ALL';
        }

        $errors = [];
        if ($account === '' || mb_strlen($account) > 32) {
            $errors[] = 'Hesap adı 1–32 karakter olmalı.';
        }
        if ($name === '' || mb_strlen($name) > 32) {
            $errors[] = 'Karakter adı 1–32 karakter olmalı.';
        }
        if (!in_array($authority, self::AUTHORITIES, true)) {
            $errors[] = 'Geçersiz yetki (mAuthority).';
        }
        if (mb_strlen($contactIp) > 16) {
            $errors[] = 'Contact IP en fazla 16 karakter.';
        }
        if (mb_strlen($serverIp) > 16) {
            $errors[] = 'Server IP en fazla 16 karakter.';
        }
        if ($contactIp !== '' && !self::isIpOrEmpty($contactIp)) {
            $errors[] = 'Contact IP geçersiz.';
        }
        if ($serverIp !== 'ALL' && !self::isIpOrEmpty($serverIp)) {
            $errors[] = 'Server IP geçersiz (veya ALL).';
        }

        return [
            'account' => $account,
            'name' => $name,
            'authority' => $authority,
            'contact_ip' => $contactIp,
            'server_ip' => $serverIp,
            'errors' => $errors,
        ];
    }

    private static function isIpOrEmpty(string $value): bool
    {
        if ($value === '' || strtoupper($value) === 'ALL') {
            return true;
        }
        return (bool) filter_var($value, FILTER_VALIDATE_IP);
    }

    /** @param array $row */
    private static function mapRow(array $row): array
    {
        $auth = (string) ($row['mAuthority'] ?? 'PLAYER');
        return [
            'id' => (int) ($row['mID'] ?? 0),
            'account' => (string) ($row['mAccount'] ?? ''),
            'name' => (string) ($row['mName'] ?? ''),
            'contact_ip' => (string) ($row['mContactIP'] ?? ''),
            'server_ip' => (string) ($row['mServerIP'] ?? 'ALL'),
            'authority' => $auth,
            'authority_label' => self::AUTHORITY_LABELS[$auth] ?? $auth,
        ];
    }
}
