<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Nesne Market ürünleri (DNWeb.market_items).
 */
final class MarketItemService
{
    /**
     * @return list<array{
     *   id:int,category_id:int,item_code:string,category_slug:string,category_name:string,
     *   name:string,description:string,price:int,discount_active:bool,
     *   discount_percent:int,image_url:string,duration_type:string,
     *   sort_order:int,is_active:bool,sale_price:int,old_price:?int
     * }>
     */
    public static function list(bool $activeOnly = false, string $search = '', int $categoryId = 0): array
    {
        try {
            $sql = 'SELECT i.id, i.category_id, i.item_code, i.name, i.description, i.price,
                           i.discount_active, i.discount_percent, i.image_url,
                           i.duration_type, i.sort_order, i.is_active,
                           c.slug AS category_slug, c.name AS category_name
                    FROM market_items i
                    LEFT JOIN market_categories c ON c.id = i.category_id';
            $where = [];
            $params = [];
            if ($activeOnly) {
                $where[] = 'i.is_active = 1';
            }
            if ($categoryId > 0) {
                $where[] = 'i.category_id = ?';
                $params[] = $categoryId;
            }
            $search = trim($search);
            if ($search !== '') {
                $where[] = '(i.name LIKE ? OR i.item_code LIKE ?)';
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
            }
            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY i.sort_order ASC, i.id ASC';

            if ($params === []) {
                $rows = Database::web()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } else {
                $stmt = Database::web()->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            $out = [];
            foreach ($rows as $row) {
                $out[] = self::map($row);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function nextSortOrder(): int
    {
        try {
            $max = (int) Database::web()->query('SELECT COALESCE(MAX(sort_order), 0) FROM market_items')->fetchColumn();
            return $max + 1;
        } catch (\Throwable) {
            return 1;
        }
    }

    /** @return array{id:int,category_id:int,item_code:string,name:string,description:string,price:int,discount_active:bool,discount_percent:int,image_url:string,duration_type:string,sort_order:int,is_active:bool,sale_price:int,old_price:?int}|null */
    public static function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT i.id, i.category_id, i.item_code, i.name, i.description, i.price,
                        i.discount_active, i.discount_percent, i.image_url,
                        i.duration_type, i.sort_order, i.is_active,
                        c.slug AS category_slug, c.name AS category_name
                 FROM market_items i
                 LEFT JOIN market_categories c ON c.id = i.category_id
                 WHERE i.id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function save(array $input, ?array $uploadFile = null): array
    {
        $id = (int) ($input['id'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $itemCode = self::normalizeItemCode((string) ($input['item_code'] ?? ''));
        $name = trim((string) ($input['name'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $price = (int) ($input['price'] ?? 0);
        $discountActive = !empty($input['discount_active']) ? 1 : 0;
        $discountPercent = (int) ($input['discount_percent'] ?? 0);
        $imageUrl = trim((string) ($input['image_url'] ?? ''));
        $durationType = strtolower(trim((string) ($input['duration_type'] ?? 'permanent')));
        $sort = (int) ($input['sort_order'] ?? 0);
        $active = !empty($input['is_active']) ? 1 : 0;

        if ($itemCode === '') {
            return ['ok' => false, 'errors' => ['Item kodu zorunlu.']];
        }
        if ($name === '') {
            return ['ok' => false, 'errors' => ['Ürün adı zorunlu.']];
        }
        if ($categoryId <= 0) {
            return ['ok' => false, 'errors' => ['Kategori seçilmeli.']];
        }
        if ($price < 0) {
            return ['ok' => false, 'errors' => ['Fiyat geçersiz.']];
        }
        if (!in_array($durationType, ['permanent', 'timed'], true)) {
            return ['ok' => false, 'errors' => ['Süre tipi geçersiz (Süreli / Süresiz).']];
        }
        if ($discountPercent < 0 || $discountPercent > 100) {
            return ['ok' => false, 'errors' => ['İndirim %0–100 arasında olmalı.']];
        }
        if ($discountActive && $discountPercent <= 0) {
            return ['ok' => false, 'errors' => ['İndirim aktifken yüzde girilmeli.']];
        }
        if (!$discountActive) {
            $discountPercent = 0;
        }

        try {
            $web = Database::web();
            $cat = $web->prepare('SELECT id FROM market_categories WHERE id = ? LIMIT 1');
            $cat->execute([$categoryId]);
            if (!$cat->fetchColumn()) {
                return ['ok' => false, 'errors' => ['Seçilen kategori bulunamadı.']];
            }

            $dup = $web->prepare('SELECT id FROM market_items WHERE item_code = ? AND id <> ? LIMIT 1');
            $dup->execute([$itemCode, $id]);
            if ($dup->fetchColumn()) {
                return ['ok' => false, 'errors' => ['Bu item kodu zaten kayıtlı.']];
            }

            $existingImage = '';
            if ($id > 0) {
                $cur = $web->prepare('SELECT image_url FROM market_items WHERE id = ? LIMIT 1');
                $cur->execute([$id]);
                $existingImage = (string) ($cur->fetchColumn() ?: '');
            }

            $uploaded = self::storeUpload($uploadFile);
            if ($uploaded !== null) {
                if ($existingImage !== '' && str_starts_with($existingImage, '/uploads/market/')) {
                    self::deleteLocalImage($existingImage);
                }
                $imageUrl = $uploaded;
            } elseif ($imageUrl === '' && $id > 0) {
                $imageUrl = $existingImage;
            }

            if ($imageUrl !== '' && !self::isAllowedImageUrl($imageUrl)) {
                return ['ok' => false, 'errors' => ['Geçersiz görsel URL. http(s) veya /uploads/... kullanın.']];
            }

            if ($id > 0) {
                $web->prepare(
                    'UPDATE market_items
                     SET category_id=?, item_code=?, name=?, description=?, price=?,
                         discount_active=?, discount_percent=?, image_url=?,
                         duration_type=?, sort_order=?, is_active=?, updated_at=NOW()
                     WHERE id=?'
                )->execute([
                    $categoryId, $itemCode, $name, $description, $price,
                    $discountActive, $discountPercent, $imageUrl,
                    $durationType, $sort, $active, $id,
                ]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }

            if ($sort <= 0) {
                $sort = self::nextSortOrder();
            }

            $web->prepare(
                'INSERT INTO market_items
                 (category_id, item_code, name, description, price, discount_active, discount_percent,
                  image_url, duration_type, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([
                $categoryId, $itemCode, $name, $description, $price,
                $discountActive, $discountPercent, $imageUrl,
                $durationType, $sort, $active,
            ]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $stmt = $web->prepare('SELECT image_url FROM market_items WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $image = (string) ($stmt->fetchColumn() ?: '');
            $web->prepare('DELETE FROM market_items WHERE id = ?')->execute([$id]);
            if ($image !== '' && str_starts_with($image, '/uploads/market/')) {
                self::deleteLocalImage($image);
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function toggle(int $id, bool $active): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare(
                'UPDATE market_items SET is_active = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Market UI için katalog satırları.
     *
     * @return list<array{id:int,code:string,name:string,cat:string,icon:string,image:string,price:int,old:?int,ribbon:?string,desc:string,duration:string}>
     */
    public static function catalogRows(): array
    {
        $rows = self::list(true);
        $out = [];
        foreach ($rows as $row) {
            $sale = (int) $row['sale_price'];
            $old = $row['old_price'];
            $ribbon = null;
            if (!empty($row['discount_active']) && (int) $row['discount_percent'] > 0) {
                $ribbon = 'sale';
            }
            $duration = ((string) $row['duration_type'] === 'timed') ? 'timed' : 'permanent';
            $out[] = [
                'id' => (int) $row['id'],
                'code' => (string) $row['item_code'],
                'name' => (string) $row['name'],
                'cat' => (string) $row['category_slug'],
                'icon' => 'fa-box',
                'image' => (string) $row['image_url'],
                'price' => $sale,
                'old' => $old,
                'ribbon' => $ribbon,
                'desc' => (string) $row['description'],
                'duration' => $duration,
            ];
        }
        return $out;
    }

    public static function normalizeItemCode(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', '', $value) ?? '';
        return mb_substr($value, 0, 32);
    }

    /** @param array<string,mixed> $row */
    private static function map(array $row): array
    {
        $price = (int) ($row['price'] ?? 0);
        $discActive = (int) ($row['discount_active'] ?? 0) === 1;
        $discPct = max(0, min(100, (int) ($row['discount_percent'] ?? 0)));
        $sale = $price;
        $old = null;
        if ($discActive && $discPct > 0) {
            $sale = (int) max(0, (int) round($price * (100 - $discPct) / 100));
            $old = $price;
        }
        $duration = strtolower((string) ($row['duration_type'] ?? 'permanent'));
        if ($duration !== 'timed') {
            $duration = 'permanent';
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'item_code' => (string) ($row['item_code'] ?? ''),
            'category_slug' => (string) ($row['category_slug'] ?? ''),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'price' => $price,
            'discount_active' => $discActive,
            'discount_percent' => $discPct,
            'image_url' => (string) ($row['image_url'] ?? ''),
            'duration_type' => $duration,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'sale_price' => $sale,
            'old_price' => $old,
        ];
    }

    private static function isAllowedImageUrl(string $url): bool
    {
        if (str_starts_with($url, '/uploads/')) {
            return !str_contains($url, '..');
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return strlen($url) <= 500;
        }
        return false;
    }

    /** @param array<string,mixed>|null $file */
    private static function storeUpload(?array $file): ?string
    {
        if ($file === null || empty($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
            return null;
        }
        $tmp = (string) $file['tmp_name'];
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 3 * 1024 * 1024) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            return null;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'market';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }
        $name = 'item_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }
        return '/uploads/market/' . $name;
    }

    private static function deleteLocalImage(string $publicPath): void
    {
        $base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
        $full = $base . str_replace('/', DIRECTORY_SEPARATOR, $publicPath);
        $realBase = realpath($base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'market');
        $realFile = realpath($full);
        if ($realBase === false || $realFile === false) {
            return;
        }
        if (!str_starts_with($realFile, $realBase) || !is_file($realFile)) {
            return;
        }
        @unlink($realFile);
    }
}
