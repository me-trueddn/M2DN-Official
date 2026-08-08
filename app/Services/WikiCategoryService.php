<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Wiki kategorileri (DNWeb.wiki_categories) — ana / alt kategori.
 */
final class WikiCategoryService
{
    /**
     * @return list<array{
     *   id:int,name:string,slug:string,is_main:bool,parent_id:int|null,parent_name:string|null,
     *   sort_order:int,is_active:bool
     * }>
     */
    public static function list(bool $activeOnly = false): array
    {
        try {
            $sql = 'SELECT c.id, c.name, c.slug, c.is_main, c.parent_id, c.sort_order, c.is_active, c.is_wiki_home,
                           p.name AS parent_name
                    FROM wiki_categories c
                    LEFT JOIN wiki_categories p ON p.id = c.parent_id';
            if ($activeOnly) {
                $sql .= ' WHERE c.is_active = 1';
            }
            $sql .= ' ORDER BY c.is_main DESC, COALESCE(c.parent_id, c.id) ASC, c.sort_order ASC, c.id ASC';
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

    /** @return list<array{id:int,name:string,slug:string}> */
    public static function listMains(bool $activeOnly = true): array
    {
        try {
            $sql = 'SELECT id, name, slug FROM wiki_categories WHERE is_main = 1';
            if ($activeOnly) {
                $sql .= ' AND is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            $rows = Database::web()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $out = [];
            foreach ($rows as $row) {
                $out[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'name' => (string) ($row['name'] ?? ''),
                    'slug' => (string) ($row['slug'] ?? ''),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Public wiki TOC: ana kategoriler + altları.
     *
     * @return list<array{
     *   id:int,name:string,slug:string,sort_order:int,
     *   children:list<array{id:int,name:string,slug:string,sort_order:int}>
     * }>
     */
    public static function tree(bool $activeOnly = true): array
    {
        $rows = self::list($activeOnly);
        $mains = [];
        $childrenByParent = [];
        foreach ($rows as $row) {
            if (!empty($row['is_main'])) {
                $mains[] = [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'slug' => (string) $row['slug'],
                    'sort_order' => (int) $row['sort_order'],
                    'children' => [],
                ];
                continue;
            }
            $pid = (int) ($row['parent_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if (!isset($childrenByParent[$pid])) {
                $childrenByParent[$pid] = [];
            }
            $childrenByParent[$pid][] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'sort_order' => (int) $row['sort_order'],
            ];
        }

        usort($mains, static fn(array $a, array $b): int =>
            ($a['sort_order'] <=> $b['sort_order']) ?: ($a['id'] <=> $b['id'])
        );

        foreach ($mains as &$main) {
            $kids = $childrenByParent[$main['id']] ?? [];
            usort($kids, static fn(array $a, array $b): int =>
                ($a['sort_order'] <=> $b['sort_order']) ?: ($a['id'] <=> $b['id'])
            );
            $main['children'] = $kids;
        }
        unset($main);

        return $mains;
    }

    /** @return list<array{id:int,name:string,slug:string,parent_id:int|null,parent_name:string|null}> */
    public static function listChildren(bool $activeOnly = false): array
    {
        $out = [];
        foreach (self::list($activeOnly) as $row) {
            if (!empty($row['is_main'])) {
                continue;
            }
            $out[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'parent_id' => $row['parent_id'],
                'parent_name' => $row['parent_name'],
            ];
        }
        return $out;
    }

    /**
     * @return array{
     *   id:int,name:string,slug:string,is_main:bool,parent_id:int|null,parent_name:string|null,
     *   sort_order:int,is_active:bool
     * }|null
     */
    public static function findBySlug(string $slug, bool $activeOnly = true): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        try {
            $sql = 'SELECT c.id, c.name, c.slug, c.is_main, c.parent_id, c.sort_order, c.is_active, c.is_wiki_home,
                           p.name AS parent_name
                    FROM wiki_categories c
                    LEFT JOIN wiki_categories p ON p.id = c.parent_id
                    WHERE c.slug = ?';
            if ($activeOnly) {
                $sql .= ' AND c.is_active = 1';
            }
            $sql .= ' LIMIT 1';
            $st = Database::web()->prepare($sql);
            $st->execute([$slug]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function anchorId(int $id): string
    {
        return 'cat-' . max(0, $id);
    }

    public static function pagePath(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '/wiki';
        }
        return '/wiki/' . rawurlencode($slug) . '.html';
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function save(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        $slugIn = trim((string) ($input['slug'] ?? ''));
        $isMain = !empty($input['is_main']);
        $parentId = (int) ($input['parent_id'] ?? 0);
        $sort = (int) ($input['sort_order'] ?? 0);
        $active = !empty($input['is_active']) ? 1 : 0;
        $isHome = !empty($input['is_wiki_home']);

        if ($name === '') {
            return ['ok' => false, 'errors' => ['Kategori adı zorunlu.']];
        }
        if (mb_strlen($name) > 120) {
            return ['ok' => false, 'errors' => ['Kategori adı en fazla 120 karakter olabilir.']];
        }

        $slug = self::slugify($slugIn !== '' ? $slugIn : $name);
        if ($slug === '') {
            return ['ok' => false, 'errors' => ['Geçersiz slug.']];
        }

        try {
            $web = Database::web();

            if ($isMain) {
                $parentId = 0;
                $isHome = false;
            } else {
                if ($parentId <= 0) {
                    return ['ok' => false, 'errors' => ['Normal kategori için ana kategori seçin.']];
                }
                if ($id > 0 && $parentId === $id) {
                    return ['ok' => false, 'errors' => ['Kategori kendisine bağlanamaz.']];
                }
                $parent = $web->prepare(
                    'SELECT id, is_main FROM wiki_categories WHERE id = ? LIMIT 1'
                );
                $parent->execute([$parentId]);
                $prow = $parent->fetch(PDO::FETCH_ASSOC);
                if (!$prow || (int) ($prow['is_main'] ?? 0) !== 1) {
                    return ['ok' => false, 'errors' => ['Seçilen kayıt geçerli bir ana kategori değil.']];
                }
            }

            $slug = self::uniqueSlug($web, $slug, $id);

            if ($id > 0) {
                $cur = $web->prepare('SELECT id, is_main FROM wiki_categories WHERE id = ? LIMIT 1');
                $cur->execute([$id]);
                $existing = $cur->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    return ['ok' => false, 'errors' => ['Kategori bulunamadı.']];
                }
                if ((int) ($existing['is_main'] ?? 0) === 1 && !$isMain) {
                    $childCnt = $web->prepare(
                        'SELECT COUNT(*) FROM wiki_categories WHERE parent_id = ?'
                    );
                    $childCnt->execute([$id]);
                    if ((int) $childCnt->fetchColumn() > 0) {
                        return ['ok' => false, 'errors' => ['Alt kategorisi olan ana kategori normale çevrilemez. Önce altları taşıyın veya silin.']];
                    }
                }

                $web->prepare(
                    'UPDATE wiki_categories
                     SET name=?, slug=?, is_main=?, parent_id=?, sort_order=?, is_active=?, is_wiki_home=?, updated_at=NOW()
                     WHERE id=?'
                )->execute([
                    $name,
                    $slug,
                    $isMain ? 1 : 0,
                    $parentId > 0 ? $parentId : null,
                    $sort,
                    $active,
                    $isHome ? 1 : 0,
                    $id,
                ]);
                if ($isHome) {
                    self::clearOtherHomes($web, $id);
                }
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }

            $web->prepare(
                'INSERT INTO wiki_categories (name, slug, is_main, parent_id, sort_order, is_active, is_wiki_home, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([
                $name,
                $slug,
                $isMain ? 1 : 0,
                $parentId > 0 ? $parentId : null,
                $sort,
                $active,
                $isHome ? 1 : 0,
            ]);
            $newId = (int) $web->lastInsertId();
            if ($isHome && $newId > 0) {
                self::clearOtherHomes($web, $newId);
            }
            return ['ok' => true, 'errors' => [], 'id' => $newId];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    public static function setWikiHome(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $st = $web->prepare(
                'SELECT id, is_main, is_active, slug FROM wiki_categories WHERE id = ? LIMIT 1'
            );
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) ($row['is_main'] ?? 0) === 1) {
                return false;
            }
            $web->prepare(
                'UPDATE wiki_categories SET is_wiki_home = 1, updated_at = NOW() WHERE id = ?'
            )->execute([$id]);
            self::clearOtherHomes($web, $id);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{id:int,name:string,slug:string,is_main:bool,parent_id:int|null,parent_name:string|null,sort_order:int,is_active:bool,is_wiki_home:bool}|null */
    public static function findWikiHome(bool $activeOnly = true): ?array
    {
        try {
            $sql = 'SELECT c.id, c.name, c.slug, c.is_main, c.parent_id, c.sort_order, c.is_active, c.is_wiki_home,
                           p.name AS parent_name
                    FROM wiki_categories c
                    LEFT JOIN wiki_categories p ON p.id = c.parent_id
                    WHERE c.is_wiki_home = 1 AND c.is_main = 0';
            if ($activeOnly) {
                $sql .= ' AND c.is_active = 1';
            }
            $sql .= ' ORDER BY c.id ASC LIMIT 1';
            $row = Database::web()->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $row ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function clearOtherHomes(PDO $web, int $keepId): void
    {
        $web->prepare(
            'UPDATE wiki_categories SET is_wiki_home = 0, updated_at = NOW() WHERE id <> ? AND is_wiki_home = 1'
        )->execute([$keepId]);
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $childCnt = $web->prepare('SELECT COUNT(*) FROM wiki_categories WHERE parent_id = ?');
            $childCnt->execute([$id]);
            if ((int) $childCnt->fetchColumn() > 0) {
                return false;
            }
            $web->prepare('DELETE FROM wiki_categories WHERE id = ?')->execute([$id]);
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
                'UPDATE wiki_categories SET is_active = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $map = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'İ' => 'i'];
        $value = strtr($value, $map);
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');
        if (mb_strlen($value) > 80) {
            $value = rtrim(mb_substr($value, 0, 80), '-');
        }
        return $value;
    }

    private static function uniqueSlug(PDO $web, string $base, int $excludeId): string
    {
        $candidate = $base;
        $n = 2;
        while (true) {
            $st = $web->prepare('SELECT id FROM wiki_categories WHERE slug = ? AND id <> ? LIMIT 1');
            $st->execute([$candidate, $excludeId]);
            if (!$st->fetchColumn()) {
                return $candidate;
            }
            $suffix = '-' . $n;
            $trim = 80 - mb_strlen($suffix);
            $candidate = ($trim > 0 ? rtrim(mb_substr($base, 0, $trim), '-') : 'cat') . $suffix;
            $n++;
            if ($n > 200) {
                return 'cat-' . max(1, $excludeId) . '-' . bin2hex(random_bytes(2));
            }
        }
    }

    /** @param array<string,mixed> $row */
    private static function map(array $row): array
    {
        $parentId = isset($row['parent_id']) && $row['parent_id'] !== null && $row['parent_id'] !== ''
            ? (int) $row['parent_id']
            : null;
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'is_main' => (int) ($row['is_main'] ?? 0) === 1,
            'parent_id' => $parentId,
            'parent_name' => isset($row['parent_name']) && $row['parent_name'] !== null
                ? (string) $row['parent_name']
                : null,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'is_wiki_home' => (int) ($row['is_wiki_home'] ?? 0) === 1,
        ];
    }
}
