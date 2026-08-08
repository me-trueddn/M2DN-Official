<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Wiki içerik tipleri (DNWeb.wiki_content_types).
 */
final class WikiContentTypeService
{
    public const SLUG_BASIT_METIN = 'basit-metin';

    /** @return list<array{id:int,slug:string,name:string,is_active:bool}> */
    public static function list(bool $activeOnly = false): array
    {
        try {
            $sql = 'SELECT id, slug, name, is_active FROM wiki_content_types';
            if ($activeOnly) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY id ASC';
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

    /** @return array{id:int,slug:string,name:string,is_active:bool}|null */
    public static function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $st = Database::web()->prepare(
                'SELECT id, slug, name, is_active FROM wiki_content_types WHERE id = ? LIMIT 1'
            );
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{id:int,slug:string,name:string,is_active:bool}|null */
    public static function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        try {
            $st = Database::web()->prepare(
                'SELECT id, slug, name, is_active FROM wiki_content_types WHERE slug = ? LIMIT 1'
            );
            $st->execute([$slug]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function save(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $active = !empty($input['is_active']) ? 1 : 0;

        if ($name === '') {
            return ['ok' => false, 'errors' => ['İçerik tipi adı zorunlu.']];
        }
        if (mb_strlen($name) > 120) {
            return ['ok' => false, 'errors' => ['Ad en fazla 120 karakter olabilir.']];
        }
        if ($slug === '') {
            $slug = self::slugify($name);
        }
        $slug = self::slugify($slug);
        if ($slug === '' || mb_strlen($slug) > 40) {
            return ['ok' => false, 'errors' => ['Geçersiz slug.']];
        }

        try {
            $web = Database::web();
            $dup = $web->prepare('SELECT id FROM wiki_content_types WHERE slug = ? AND id <> ? LIMIT 1');
            $dup->execute([$slug, $id]);
            if ($dup->fetchColumn()) {
                return ['ok' => false, 'errors' => ['Bu slug zaten kullanılıyor.']];
            }

            if ($id > 0) {
                $web->prepare(
                    'UPDATE wiki_content_types SET slug=?, name=?, is_active=?, updated_at=NOW() WHERE id=?'
                )->execute([$slug, $name, $active, $id]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }

            $web->prepare(
                'INSERT INTO wiki_content_types (slug, name, is_active, created_at, updated_at)
                 VALUES (?,?,?,NOW(),NOW())'
            )->execute([$slug, $name, $active]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    public static function toggle(int $id, bool $active): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare(
                'UPDATE wiki_content_types SET is_active = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $used = $web->prepare('SELECT COUNT(*) FROM wiki_pages WHERE content_type_id = ?');
            $used->execute([$id]);
            if ((int) $used->fetchColumn() > 0) {
                return false;
            }
            $row = self::find($id);
            if ($row && $row['slug'] === self::SLUG_BASIT_METIN) {
                return false;
            }
            $web->prepare('DELETE FROM wiki_content_types WHERE id = ?')->execute([$id]);
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
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
        ];
    }
}
