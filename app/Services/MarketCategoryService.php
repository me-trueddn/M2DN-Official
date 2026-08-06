<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Nesne Market kategorileri (DNWeb.market_categories).
 */
final class MarketCategoryService
{
    /** @return list<array{id:int,slug:string,name:string,icon:string,sort_order:int,is_active:bool}> */
    public static function list(bool $activeOnly = false): array
    {
        try {
            $sql = 'SELECT id, slug, name, icon, sort_order, is_active
                    FROM market_categories';
            if ($activeOnly) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            $rows = Database::web()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];
            foreach ($rows as $row) {
                $out[] = self::map($row);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function save(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $icon = trim((string) ($input['icon'] ?? 'fa-solid fa-box'));
        $sort = (int) ($input['sort_order'] ?? 0);
        $active = !empty($input['is_active']) ? 1 : 0;

        if ($name === '') {
            return ['ok' => false, 'errors' => ['Kategori adı zorunlu.']];
        }
        if ($slug === '') {
            $slug = self::slugify($name);
        }
        $slug = self::slugify($slug);
        if ($slug === '' || $slug === 'all') {
            return ['ok' => false, 'errors' => ['Geçersiz kategori kodu (slug). "all" kullanılamaz.']];
        }
        if ($icon === '') {
            $icon = 'fa-solid fa-box';
        }
        if (!str_contains($icon, 'fa-')) {
            return ['ok' => false, 'errors' => ['İkon Font Awesome sınıfı olmalı (örn. fa-solid fa-khanda).']];
        }

        try {
            $web = Database::web();
            $dup = $web->prepare('SELECT id FROM market_categories WHERE slug = ? AND id <> ? LIMIT 1');
            $dup->execute([$slug, $id]);
            if ($dup->fetchColumn()) {
                return ['ok' => false, 'errors' => ['Bu slug zaten kullanılıyor.']];
            }

            if ($id > 0) {
                $web->prepare(
                    'UPDATE market_categories
                     SET slug=?, name=?, icon=?, sort_order=?, is_active=?, updated_at=NOW()
                     WHERE id=?'
                )->execute([$slug, $name, $icon, $sort, $active, $id]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }

            $web->prepare(
                'INSERT INTO market_categories (slug, name, icon, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,NOW(),NOW())'
            )->execute([$slug, $name, $icon, $sort, $active]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM market_categories WHERE id = ?')->execute([$id]);
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
                'UPDATE market_categories SET is_active = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $map = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'İ' => 'i'];
        $value = strtr($value, $map);
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        return trim($value, '-');
    }

    /** @param array<string,mixed> $row */
    private static function map(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'icon' => (string) ($row['icon'] ?? 'fa-solid fa-box'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
        ];
    }
}
