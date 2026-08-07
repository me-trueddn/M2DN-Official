<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;

/**
 * Market kuponları — kategori (Elmas count), kod üretimi (SHA-256), kullanım.
 * Kod formatı: XXX-YYY-ZZZ-DDD-FFF-RRR-AAA (7×3 alfanümerik).
 */
final class MarketCouponService
{
    public const CODE_SEGMENTS = 7;
    public const CODE_SEGMENT_LEN = 3;
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** @return list<array{id:int,name:string,cash_amount:int,is_active:bool,sort_order:int,coupon_count:int,unused_count:int,created_at:string}> */
    public static function listCategories(bool $activeOnly = false): array
    {
        try {
            $web = Database::web();
            $sql = 'SELECT c.id, c.name, c.cash_amount, c.is_active, c.sort_order, c.created_at,
                           COUNT(k.id) AS coupon_count,
                           SUM(CASE WHEN k.id IS NOT NULL AND k.used_at IS NULL THEN 1 ELSE 0 END) AS unused_count
                    FROM market_coupon_categories c
                    LEFT JOIN market_coupons k ON k.category_id = c.id';
            if ($activeOnly) {
                $sql .= ' WHERE c.is_active = 1';
            }
            $sql .= ' GROUP BY c.id ORDER BY c.sort_order ASC, c.id ASC';
            $rows = $web->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];
            foreach ($rows as $row) {
                $out[] = self::mapCategory($row);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function findCategory(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, name, cash_amount, is_active, sort_order, created_at, updated_at
                 FROM market_coupon_categories WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? self::mapCategory($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array{id?:int,name?:string,cash_amount?:int,is_active?:bool,sort_order?:int} $data
     * @return array{ok:bool, errors:list<string>, id?:int}
     */
    public static function saveCategory(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $cash = (int) ($data['cash_amount'] ?? 0);
        $active = !empty($data['is_active']);
        $sort = (int) ($data['sort_order'] ?? 0);

        if ($name === '' || mb_strlen($name) > 120) {
            return ['ok' => false, 'errors' => ['Kategori adı zorunlu (max 120).']];
        }
        if ($cash < 1 || $cash > 100000000) {
            return ['ok' => false, 'errors' => ['Count (Elmas) 1–100.000.000 arası olmalı.']];
        }

        try {
            $web = Database::web();
            if ($id > 0) {
                $web->prepare(
                    'UPDATE market_coupon_categories
                     SET name = ?, cash_amount = ?, is_active = ?, sort_order = ?, updated_at = NOW()
                     WHERE id = ?'
                )->execute([$name, $cash, $active ? 1 : 0, $sort, $id]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }
            $web->prepare(
                'INSERT INTO market_coupon_categories (name, cash_amount, is_active, sort_order, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())'
            )->execute([$name, $cash, $active ? 1 : 0, $sort]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kategori kaydedilemedi.']];
        }
    }

    public static function deleteCategory(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz kategori.']];
        }
        try {
            $web = Database::web();
            $stmt = $web->prepare('SELECT COUNT(*) FROM market_coupons WHERE category_id = ?');
            $stmt->execute([$id]);
            $cnt = (int) $stmt->fetchColumn();
            if ($cnt > 0) {
                return ['ok' => false, 'errors' => ['Bu kategoride kupon var. Önce kuponları sil.']];
            }
            $web->prepare('DELETE FROM market_coupon_categories WHERE id = ?')->execute([$id]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kategori silinemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>, codes?:list<string>, created?:int}
     */
    public static function generate(int $categoryId, int $quantity, array $actor = []): array
    {
        if ($categoryId <= 0) {
            return ['ok' => false, 'errors' => ['Kategori seç.']];
        }
        if ($quantity < 1 || $quantity > 500) {
            return ['ok' => false, 'errors' => ['Adet 1–500 arası olmalı.']];
        }
        $cat = self::findCategory($categoryId);
        if ($cat === null || empty($cat['is_active'])) {
            return ['ok' => false, 'errors' => ['Kategori bulunamadı veya pasif.']];
        }

        $actorId = (int) ($actor['account_id'] ?? 0);
        $actorLogin = (string) ($actor['login'] ?? '');
        $web = Database::web();
        $plainCodes = [];

        try {
            $web->beginTransaction();
            $ins = $web->prepare(
                'INSERT INTO market_coupons
                 (category_id, code_hash, code_mask, created_by_account_id, created_by_login, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            $attempts = 0;
            $maxAttempts = $quantity * 40 + 100;
            while (count($plainCodes) < $quantity && $attempts < $maxAttempts) {
                $attempts++;
                $code = self::generatePlainCode();
                $hash = self::hashCode($code);
                $mask = self::maskCode($code);
                try {
                    $ins->execute([$categoryId, $hash, $mask, $actorId > 0 ? $actorId : null, $actorLogin]);
                    $plainCodes[] = $code;
                } catch (PDOException $e) {
                    // unique collision — retry
                    if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                        throw $e;
                    }
                }
            }
            if (count($plainCodes) < $quantity) {
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Yeterli benzersiz kod üretilemedi. Tekrar dene.']];
            }
            $web->commit();
            return [
                'ok' => true,
                'errors' => [],
                'codes' => $plainCodes,
                'created' => count($plainCodes),
            ];
        } catch (\Throwable) {
            if ($web->inTransaction()) {
                $web->rollBack();
            }
            return ['ok' => false, 'errors' => ['Kupon oluşturulamadı.']];
        }
    }

    /**
     * @return array{
     *   coupons:list<array>,
     *   total:int, page:int, pages:int, per_page:int,
     *   q:string, status:string, category_id:int
     * }
     */
    public static function listCoupons(
        string $q = '',
        string $status = '',
        int $categoryId = 0,
        int $page = 1,
        int $perPage = 30
    ): array {
        $page = max(1, $page);
        $perPage = max(10, min(100, $perPage));
        $q = trim($q);
        $status = strtolower(trim($status));
        if (!in_array($status, ['', 'unused', 'used'], true)) {
            $status = '';
        }

        try {
            $web = Database::web();
            $where = [];
            $params = [];
            if ($q !== '') {
                $normalized = self::normalizeCode($q);
                $hashMatch = self::isValidFormat($normalized) ? self::hashCode($normalized) : null;
                if ($hashMatch !== null) {
                    $where[] = '(k.code_hash = ? OR k.code_mask LIKE ? OR k.used_account_login LIKE ? OR CAST(k.id AS CHAR) = ?)';
                    $like = '%' . $q . '%';
                    $params[] = $hashMatch;
                    $params[] = $like;
                    $params[] = $like;
                    $params[] = $q;
                } else {
                    $where[] = '(k.code_mask LIKE ? OR k.used_account_login LIKE ? OR CAST(k.id AS CHAR) = ?)';
                    $like = '%' . $q . '%';
                    $params[] = $like;
                    $params[] = $like;
                    $params[] = $q;
                }
            }
            if ($status === 'unused') {
                $where[] = 'k.used_at IS NULL';
            } elseif ($status === 'used') {
                $where[] = 'k.used_at IS NOT NULL';
            }
            if ($categoryId > 0) {
                $where[] = 'k.category_id = ?';
                $params[] = $categoryId;
            }
            $sqlWhere = $where === [] ? '' : (' WHERE ' . implode(' AND ', $where));

            $countStmt = $web->prepare(
                'SELECT COUNT(*) FROM market_coupons k' . $sqlWhere
            );
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $web->prepare(
                'SELECT k.id, k.category_id, k.code_mask, k.used_account_id, k.used_account_login,
                        k.created_at, k.used_at, k.created_by_login,
                        c.name AS category_name, c.cash_amount
                 FROM market_coupons k
                 INNER JOIN market_coupon_categories c ON c.id = k.category_id'
                . $sqlWhere . '
                 ORDER BY k.id DESC
                 LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $coupons = [];
            foreach ($rows as $row) {
                $used = !empty($row['used_at']);
                $coupons[] = [
                    'id' => (int) $row['id'],
                    'category_id' => (int) $row['category_id'],
                    'category_name' => (string) $row['category_name'],
                    'cash_amount' => (int) $row['cash_amount'],
                    'code_mask' => (string) $row['code_mask'],
                    'is_used' => $used,
                    'status_label' => $used ? 'Kullanıldı' : 'Kullanılmadı',
                    'used_account_id' => (int) ($row['used_account_id'] ?? 0),
                    'used_account_login' => (string) ($row['used_account_login'] ?? ''),
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'used_at' => (string) ($row['used_at'] ?? ''),
                    'created_by_login' => (string) ($row['created_by_login'] ?? ''),
                ];
            }
            return [
                'coupons' => $coupons,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'q' => $q,
                'status' => $status,
                'category_id' => $categoryId,
            ];
        } catch (\Throwable) {
            return [
                'coupons' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => $perPage,
                'q' => $q, 'status' => $status, 'category_id' => $categoryId,
            ];
        }
    }

    /**
     * @param list<int> $ids
     * @return array{ok:bool, errors:list<string>, deleted?:int}
     */
    public static function deleteMany(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $i): bool => $i > 0)));
        if ($ids === []) {
            return ['ok' => false, 'errors' => ['Silinecek kupon seçilmedi.']];
        }
        try {
            $web = Database::web();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            // Kullanılmış kuponlar da silinebilir (admin temizliği)
            $stmt = $web->prepare('DELETE FROM market_coupons WHERE id IN (' . $placeholders . ')');
            $stmt->execute($ids);
            return ['ok' => true, 'errors' => [], 'deleted' => $stmt->rowCount()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kuponlar silinemedi.']];
        }
    }

    /**
     * Oyuncu kupon aktif eder → account.cash += kategori count.
     *
     * @return array{ok:bool, errors:list<string>, cash?:int, cash_before?:int, cash_after?:int, amount?:int}
     */
    public static function redeem(int $accountId, string $accountLogin, string $plainCode, string $ip = ''): array
    {
        if ($accountId <= 0) {
            return ['ok' => false, 'errors' => ['Oturum geçersiz.']];
        }
        $plainCode = self::normalizeCode($plainCode);
        if (!self::isValidFormat($plainCode)) {
            return ['ok' => false, 'errors' => ['Geçersiz kupon kodu formatı.']];
        }
        $hash = self::hashCode($plainCode);

        $acc = Database::account();
        $web = Database::web();

        try {
            $web->beginTransaction();

            $cStmt = $web->prepare(
                'SELECT k.id, k.category_id, k.code_mask, k.used_at, c.name AS category_name, c.cash_amount, c.is_active
                 FROM market_coupons k
                 INNER JOIN market_coupon_categories c ON c.id = k.category_id
                 WHERE k.code_hash = ?
                 LIMIT 1
                 FOR UPDATE'
            );
            $cStmt->execute([$hash]);
            $coupon = $cStmt->fetch(PDO::FETCH_ASSOC);
            if (!$coupon) {
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Kupon kodu bulunamadı.']];
            }
            if (!empty($coupon['used_at'])) {
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Bu kupon daha önce kullanılmış.']];
            }
            if ((int) ($coupon['is_active'] ?? 0) !== 1) {
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Bu kupon kategorisi pasif.']];
            }

            $amount = (int) ($coupon['cash_amount'] ?? 0);
            if ($amount < 1) {
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Kupon değeri geçersiz.']];
            }

            $couponId = (int) $coupon['id'];
            $catName = (string) ($coupon['category_name'] ?? 'Kupon');
            $mask = (string) ($coupon['code_mask'] ?? '');

            $acc->beginTransaction();
            $cashStmt = $acc->prepare('SELECT cash FROM account WHERE id = ? LIMIT 1 FOR UPDATE');
            $cashStmt->execute([$accountId]);
            $cashRow = $cashStmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($cashRow)) {
                $acc->rollBack();
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $cashBefore = (int) ($cashRow['cash'] ?? 0);
            $cashAfter = $cashBefore + $amount;
            $upd = $acc->prepare('UPDATE account SET cash = ? WHERE id = ? AND cash = ?');
            $upd->execute([$cashAfter, $accountId, $cashBefore]);
            if ($upd->rowCount() !== 1) {
                $acc->rollBack();
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Bakiye güncellenemedi. Tekrar dene.']];
            }

            $mark = $web->prepare(
                'UPDATE market_coupons
                 SET used_account_id = ?, used_account_login = ?, used_at = NOW()
                 WHERE id = ? AND used_at IS NULL'
            );
            $mark->execute([$accountId, $accountLogin, $couponId]);
            if ($mark->rowCount() !== 1) {
                $acc->rollBack();
                $web->rollBack();
                return ['ok' => false, 'errors' => ['Kupon işaretlenemedi (eşzamanlı kullanım).']];
            }

            $web->prepare(
                'INSERT INTO market_sales_logs
                 (account_id, account_login, market_item_id, item_code, item_name, price,
                  cash_before, cash_after, safebox_pos, player_item_id, ip, entry_type, coupon_hash, created_at)
                 VALUES (?, ?, 0, ?, ?, ?, ?, ?, -1, 0, ?, \'coupon\', ?, NOW())'
            )->execute([
                $accountId,
                $accountLogin,
                'KUPON',
                'Kupon · ' . $catName . ' (+' . number_format($amount, 0, ',', '.') . ' Elmas) · ' . $mask,
                $amount,
                $cashBefore,
                $cashAfter,
                $ip,
                $hash,
            ]);

            $acc->commit();
            $web->commit();

            ActivityLogService::log(
                $accountId,
                ActivityLogService::ACTION_MARKET_COUPON,
                $catName . ' · +' . number_format($amount, 0, ',', '.') . ' Elmas · ' . $mask
                . ' · önce: ' . number_format($cashBefore, 0, ',', '.')
                . ' → sonra: ' . number_format($cashAfter, 0, ',', '.'),
                $accountLogin
            );

            return [
                'ok' => true,
                'errors' => [],
                'cash' => $cashAfter,
                'cash_before' => $cashBefore,
                'cash_after' => $cashAfter,
                'amount' => $amount,
            ];
        } catch (\Throwable) {
            if ($acc->inTransaction()) {
                $acc->rollBack();
            }
            if ($web->inTransaction()) {
                $web->rollBack();
            }
            return ['ok' => false, 'errors' => ['Kupon aktif edilemedi.']];
        }
    }

    public static function normalizeCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/\s+/', '', $code) ?? '';
        return $code;
    }

    public static function isValidFormat(string $normalized): bool
    {
        $parts = explode('-', $normalized);
        if (count($parts) !== self::CODE_SEGMENTS) {
            return false;
        }
        foreach ($parts as $p) {
            if (strlen($p) !== self::CODE_SEGMENT_LEN || !preg_match('/^[A-Z0-9]+$/', $p)) {
                return false;
            }
        }
        return true;
    }

    public static function hashCode(string $normalized): string
    {
        return hash('sha256', $normalized);
    }

    public static function maskCode(string $normalized): string
    {
        $parts = explode('-', $normalized);
        if (count($parts) !== self::CODE_SEGMENTS) {
            return '***';
        }
        $mid = array_fill(0, self::CODE_SEGMENTS - 2, '***');
        return $parts[0] . '-' . implode('-', $mid) . '-' . $parts[self::CODE_SEGMENTS - 1];
    }

    public static function generatePlainCode(): string
    {
        $parts = [];
        $max = strlen(self::ALPHABET) - 1;
        for ($s = 0; $s < self::CODE_SEGMENTS; $s++) {
            $chunk = '';
            for ($i = 0; $i < self::CODE_SEGMENT_LEN; $i++) {
                $chunk .= self::ALPHABET[random_int(0, $max)];
            }
            $parts[] = $chunk;
        }
        return implode('-', $parts);
    }

    /** @param array<string,mixed> $row */
    private static function mapCategory(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'cash_amount' => (int) ($row['cash_amount'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'coupon_count' => (int) ($row['coupon_count'] ?? 0),
            'unused_count' => (int) ($row['unused_count'] ?? 0),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }
}
