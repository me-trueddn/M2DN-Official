<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class AnnouncementService
{
    /** @return list<array> */
    public static function types(bool $onlyActive = true): array
    {
        try {
            $sql = 'SELECT id, name, sort_order, is_active, created_at FROM announcement_types';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            return Database::web()->query($sql)->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveType(?int $id, string $name): array
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 120) {
            return ['ok' => false, 'errors' => ['Tür adı zorunlu (max 120).']];
        }
        try {
            $web = Database::web();
            if ($id && $id > 0) {
                $web->prepare('UPDATE announcement_types SET name=?, updated_at=NOW() WHERE id=?')
                    ->execute([$name, $id]);
            } else {
                $sort = (int) $web->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM announcement_types')->fetchColumn();
                $web->prepare(
                    'INSERT INTO announcement_types (name, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,1,NOW(),NOW())'
                )->execute([$name, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Tür kaydedilemedi.']];
        }
    }

    public static function deleteType(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $stmt = $web->prepare('SELECT COUNT(*) FROM announcements WHERE type_id=?');
            $stmt->execute([$id]);
            if ((int) $stmt->fetchColumn() > 0) {
                $web->prepare('UPDATE announcement_types SET is_active=0, updated_at=NOW() WHERE id=?')->execute([$id]);
                return true;
            }
            $web->prepare('DELETE FROM announcement_types WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function toggleType(int $id, bool $active): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('UPDATE announcement_types SET is_active=?, updated_at=NOW() WHERE id=?')
                ->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<array>
     */
    public static function list(bool $onlyActive = false, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        try {
            $sql = "SELECT a.id, a.type_id, a.title, a.body, a.is_active, a.author_login, a.published_at, a.created_at, a.updated_at,
                           t.name AS type_name
                    FROM announcements a
                    LEFT JOIN announcement_types t ON t.id = a.type_id";
            if ($onlyActive) {
                $sql .= ' WHERE a.is_active = 1';
            }
            $sql .= " ORDER BY a.published_at DESC, a.id DESC LIMIT {$limit}";
            return self::mapRows(Database::web()->query($sql)->fetchAll() ?: []);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function get(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT a.*, t.name AS type_name
                 FROM announcements a
                 LEFT JOIN announcement_types t ON t.id = a.type_id
                 WHERE a.id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $mapped = self::mapRows([$row]);
            return $mapped[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array{account_id?:int,login?:string}|null $author
     * @return array{ok:bool, errors:list<string>, id?:int}
     */
    public static function save(
        ?int $id,
        int $typeId,
        string $title,
        string $body,
        bool $isActive,
        ?array $author = null
    ): array {
        $title = trim($title);
        $body = trim($body);
        $errors = [];
        if ($typeId <= 0) {
            $errors[] = 'Duyuru türü seç.';
        }
        if ($title === '' || mb_strlen($title) > 200) {
            $errors[] = 'Başlık zorunlu (max 200).';
        }
        if ($body === '' || $body === '<p><br></p>' || $body === '<p></p>') {
            $errors[] = 'İçerik zorunlu.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $body = self::sanitizeHtml($body);

        try {
            $web = Database::web();
            $typeCheck = $web->prepare('SELECT id FROM announcement_types WHERE id=? AND is_active=1 LIMIT 1');
            $typeCheck->execute([$typeId]);
            if (!$typeCheck->fetch()) {
                // allow inactive type if editing existing
                $any = $web->prepare('SELECT id FROM announcement_types WHERE id=? LIMIT 1');
                $any->execute([$typeId]);
                if (!$any->fetch()) {
                    return ['ok' => false, 'errors' => ['Geçersiz duyuru türü.']];
                }
            }

            if ($id && $id > 0) {
                $web->prepare(
                    'UPDATE announcements
                     SET type_id=?, title=?, body=?, is_active=?, updated_at=NOW(),
                         published_at = CASE WHEN ? = 1 AND (published_at IS NULL OR is_active = 0) THEN NOW() ELSE published_at END
                     WHERE id=?'
                )->execute([$typeId, $title, $body, $isActive ? 1 : 0, $isActive ? 1 : 0, $id]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }

            $web->prepare(
                'INSERT INTO announcements
                  (type_id, title, body, is_active, author_account_id, author_login, published_at, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,IF(?=1,NOW(),NULL),NOW(),NOW())'
            )->execute([
                $typeId,
                $title,
                $body,
                $isActive ? 1 : 0,
                (int) ($author['account_id'] ?? 0),
                (string) ($author['login'] ?? ''),
                $isActive ? 1 : 0,
            ]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable $e) {
            $msg = 'Duyuru kaydedilemedi.';
            if ((bool) \App\Core\Config::get('app.debug', false)) {
                $msg .= ' (' . $e->getMessage() . ')';
            }
            return ['ok' => false, 'errors' => [$msg]];
        }
    }

    public static function toggle(int $id, bool $active): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare(
                'UPDATE announcements SET is_active=?, updated_at=NOW(),
                 published_at = CASE WHEN ? = 1 AND published_at IS NULL THEN NOW() ELSE published_at END
                 WHERE id=?'
            )->execute([$active ? 1 : 0, $active ? 1 : 0, $id]);
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
            Database::web()->prepare('DELETE FROM announcements WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function sanitizeHtml(string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><h1><h2><h3><h4><blockquote><pre><code>'
            . '<a><span><div><table><thead><tbody><tfoot><tr><th><td><hr><sup><sub>';
        $clean = strip_tags($html, $allowed);
        // strip event handlers / javascript: urls
        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/iu', '$1="#"', $clean) ?? $clean;
        return $clean;
    }

    /** @param list<array> $rows */
    private static function mapRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $pub = strtotime((string) ($row['published_at'] ?? $row['created_at'] ?? ''));
            $out[] = [
                'id' => (int) $row['id'],
                'type_id' => (int) ($row['type_id'] ?? 0),
                'type_name' => (string) ($row['type_name'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'body' => (string) ($row['body'] ?? ''),
                'is_active' => (int) ($row['is_active'] ?? 0) === 1,
                'author_login' => (string) ($row['author_login'] ?? ''),
                'published_at' => (string) ($row['published_at'] ?? ''),
                'published_label' => $pub ? date('d.m.Y H:i', $pub) : '—',
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ];
        }
        return $out;
    }
}
