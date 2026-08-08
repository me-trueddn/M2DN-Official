<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Wiki sayfaları (DNWeb.wiki_pages) — alt kategori başına bir içerik.
 */
final class WikiPageService
{
    /**
     * @return list<array{
     *   id:int,category_id:int,category_name:string,content_type_id:int,content_type_name:string,
     *   content_type_slug:string,title:string,body_html:string,is_active:bool
     * }>
     */
    public static function list(bool $activeOnly = false): array
    {
        try {
            $sql = 'SELECT p.id, p.category_id, p.content_type_id, p.title, p.body_html, p.is_active,
                           c.name AS category_name, t.name AS content_type_name, t.slug AS content_type_slug
                    FROM wiki_pages p
                    INNER JOIN wiki_categories c ON c.id = p.category_id
                    INNER JOIN wiki_content_types t ON t.id = p.content_type_id';
            if ($activeOnly) {
                $sql .= ' WHERE p.is_active = 1';
            }
            $sql .= ' ORDER BY c.sort_order ASC, p.id ASC';
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

    /**
     * category_id => page (aktif).
     *
     * @return array<int, array{
     *   id:int,category_id:int,category_name:string,content_type_id:int,content_type_name:string,
     *   content_type_slug:string,title:string,body_html:string,is_active:bool
     * }>
     */
    public static function mapByCategory(bool $activeOnly = true): array
    {
        $out = [];
        foreach (self::list($activeOnly) as $page) {
            $out[(int) $page['category_id']] = $page;
        }
        return $out;
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function save(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $typeId = (int) ($input['content_type_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $body = (string) ($input['body_html'] ?? '');
        $active = !empty($input['is_active']) ? 1 : 0;
        /** @var list<array<string,mixed>>|null $teamMembers */
        $teamMembers = isset($input['team_members']) && is_array($input['team_members'])
            ? $input['team_members']
            : null;

        if ($categoryId <= 0) {
            return ['ok' => false, 'errors' => ['Kategori seçin.']];
        }
        if ($typeId <= 0) {
            return ['ok' => false, 'errors' => ['İçerik tipi seçin.']];
        }
        if ($title === '') {
            return ['ok' => false, 'errors' => ['Başlık zorunlu.']];
        }
        if (mb_strlen($title) > 200) {
            return ['ok' => false, 'errors' => ['Başlık en fazla 200 karakter olabilir.']];
        }

        try {
            $web = Database::web();

            $cat = $web->prepare(
                'SELECT id, is_main FROM wiki_categories WHERE id = ? LIMIT 1'
            );
            $cat->execute([$categoryId]);
            $crow = $cat->fetch(PDO::FETCH_ASSOC);
            if (!$crow) {
                return ['ok' => false, 'errors' => ['Kategori bulunamadı.']];
            }

            $type = WikiContentTypeService::find($typeId);
            if (!$type || !$type['is_active']) {
                return ['ok' => false, 'errors' => ['Geçersiz veya pasif içerik tipi.']];
            }

            $isTeam = ($type['slug'] ?? '') === WikiContentTypeService::SLUG_TAKIMIZ
                || ($type['slug'] ?? '') === WikiTeamService::SLUG_TAKIMIZ;

            if ($isTeam) {
                $body = '';
                if ($teamMembers === null || $teamMembers === []) {
                    return ['ok' => false, 'errors' => ['Takımımız için en az bir üye ekleyin.']];
                }
            } else {
                $body = self::sanitizeHtml($body);
                if (trim(strip_tags($body)) === '' && !str_contains($body, '<img')) {
                    return ['ok' => false, 'errors' => ['İçerik boş olamaz.']];
                }
            }

            $dup = $web->prepare(
                'SELECT id FROM wiki_pages WHERE category_id = ? AND id <> ? LIMIT 1'
            );
            $dup->execute([$categoryId, $id]);
            if ($dup->fetchColumn()) {
                return ['ok' => false, 'errors' => ['Bu kategoride zaten bir içerik var.']];
            }

            if ($id > 0) {
                $web->prepare(
                    'UPDATE wiki_pages
                     SET category_id=?, content_type_id=?, title=?, body_html=?, is_active=?, updated_at=NOW()
                     WHERE id=?'
                )->execute([$categoryId, $typeId, $title, $body, $active, $id]);
                $pageId = $id;
            } else {
                $web->prepare(
                    'INSERT INTO wiki_pages (category_id, content_type_id, title, body_html, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,?,NOW(),NOW())'
                )->execute([$categoryId, $typeId, $title, $body, $active]);
                $pageId = (int) $web->lastInsertId();
            }

            if ($isTeam && $pageId > 0) {
                $teamResult = WikiTeamService::replaceForPage($pageId, $teamMembers ?? []);
                if (empty($teamResult['ok'])) {
                    return ['ok' => false, 'errors' => $teamResult['errors'] ?? ['Takım üyeleri kaydedilemedi.']];
                }
            }

            return ['ok' => true, 'errors' => [], 'id' => $pageId];
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
            $pdo = Database::web();
            try {
                $pdo->prepare('DELETE FROM wiki_team_members WHERE wiki_page_id = ?')->execute([$id]);
            } catch (\Throwable) {
            }
            $pdo->prepare('DELETE FROM wiki_pages WHERE id = ?')->execute([$id]);
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
                'UPDATE wiki_pages SET is_active = ?, updated_at = NOW() WHERE id = ?'
            )->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>, url?:string}
     */
    public static function storeImageUpload(?array $file): array
    {
        if ($file === null || empty($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
            return ['ok' => false, 'errors' => ['Dosya yüklenmedi.']];
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            return ['ok' => false, 'errors' => ['Görsel en fazla 5 MB olabilir.']];
        }
        $tmp = (string) $file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            return ['ok' => false, 'errors' => ['Yalnızca JPG, PNG, WEBP, GIF kabul edilir.']];
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wiki';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'errors' => ['Yükleme klasörü oluşturulamadı.']];
        }
        $name = 'wiki_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'errors' => ['Dosya kaydedilemedi.']];
        }
        return ['ok' => true, 'errors' => [], 'url' => '/uploads/wiki/' . $name];
    }

    public static function sanitizeHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $allowed = [
            'p' => ['class'],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => ['class'],
            'u' => [],
            's' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'h1' => [],
            'h2' => [],
            'h3' => ['class'],
            'h4' => [],
            'blockquote' => [],
            'pre' => [],
            'code' => [],
            'a' => ['href', 'title', 'target', 'rel'],
            'span' => ['class'],
            'div' => ['class', 'style'],
            'table' => [],
            'thead' => [],
            'tbody' => [],
            'tfoot' => [],
            'tr' => [],
            'th' => [],
            'td' => [],
            'hr' => [],
            'sup' => [],
            'sub' => [],
            'img' => ['src', 'alt', 'loading'],
        ];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="wiki-sanitize-root">' . $html . '</div>',
            LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementById('wiki-sanitize-root');
        if ($root === null) {
            return '';
        }

        self::sanitizeDomChildren($root, $allowed);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }
        return $out;
    }

    /**
     * @param array<string, list<string>> $allowed
     */
    private static function sanitizeDomChildren(\DOMNode $parent, array $allowed): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof \DOMText || $child instanceof \DOMCdataSection) {
                continue;
            }
            if ($child instanceof \DOMComment) {
                $parent->removeChild($child);
                continue;
            }
            if (!($child instanceof \DOMElement)) {
                $parent->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button', 'textarea', 'select'], true)) {
                $parent->removeChild($child);
                continue;
            }
            if (!isset($allowed[$tag])) {
                foreach (iterator_to_array($child->childNodes) as $gc) {
                    $parent->insertBefore($gc, $child);
                }
                $parent->removeChild($child);
                self::sanitizeDomChildren($parent, $allowed);
                return;
            }

            self::sanitizeElementAttrs($child, $allowed[$tag]);
            if ($child->parentNode === null) {
                continue;
            }
            self::sanitizeDomChildren($child, $allowed);
        }
    }

    /** @param list<string> $allowedAttrs */
    private static function sanitizeElementAttrs(\DOMElement $el, array $allowedAttrs): void
    {
        $tag = strtolower($el->tagName);
        $toRemove = [];
        if ($el->hasAttributes()) {
            foreach (iterator_to_array($el->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowedAttrs, true)) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                $value = trim((string) $attr->value);
                if (($name === 'href' || $name === 'src') && preg_match('#^\s*javascript:#iu', $value) === 1) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                if ($name === 'src') {
                    $ok = str_starts_with($value, '/uploads/wiki/')
                        || preg_match('#^https?://#i', $value) === 1;
                    if (!$ok) {
                        $toRemove[] = $attr->name;
                    }
                    continue;
                }
                if ($name === 'href') {
                    $ok = $value === '#'
                        || str_starts_with($value, '/')
                        || preg_match('#^(https?:|mailto:)#i', $value) === 1;
                    if (!$ok) {
                        $toRemove[] = $attr->name;
                    }
                    continue;
                }
                if ($name === 'class' && preg_match('/[^a-zA-Z0-9_\-\s]/', $value) === 1) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                if ($name === 'style' && preg_match('/^--glow-color\s*:\s*#[0-9a-fA-F]{3,8}\s*;?\s*$/', $value) !== 1) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                if ($name === 'target' && !in_array($value, ['_blank', '_self'], true)) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                if ($name === 'rel' && preg_match('/[^a-zA-Z0-9_\-\s]/', $value) === 1) {
                    $toRemove[] = $attr->name;
                    continue;
                }
                if ($name === 'loading' && $value !== 'lazy' && $value !== 'eager') {
                    $toRemove[] = $attr->name;
                }
            }
        }
        foreach ($toRemove as $name) {
            $el->removeAttribute($name);
        }
        if ($tag === 'img') {
            $src = trim($el->getAttribute('src'));
            if ($src === '') {
                $el->parentNode?->removeChild($el);
                return;
            }
            if (!$el->hasAttribute('loading')) {
                $el->setAttribute('loading', 'lazy');
            }
            if (!$el->hasAttribute('alt')) {
                $el->setAttribute('alt', '');
            }
        }
        if ($tag === 'a' && $el->getAttribute('target') === '_blank' && trim($el->getAttribute('rel')) === '') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /** @param array<string,mixed> $row */
    private static function map(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'content_type_id' => (int) ($row['content_type_id'] ?? 0),
            'content_type_name' => (string) ($row['content_type_name'] ?? ''),
            'content_type_slug' => (string) ($row['content_type_slug'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'body_html' => (string) ($row['body_html'] ?? ''),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
        ];
    }
}
