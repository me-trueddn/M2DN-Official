<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use PDO;

/**
 * Ana sayfa / site içeriği (DNWeb).
 */
final class SiteContentService
{
    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        try {
            $stmt = Database::web()->prepare(
                'SELECT setting_value FROM settings WHERE group_key = ? AND setting_key = ? LIMIT 1'
            );
            $stmt->execute([$group, $key]);
            $val = $stmt->fetchColumn();
            if ($val === false || $val === null) {
                return $default;
            }
            return (string) $val;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $group, string $key, string $value): bool
    {
        try {
            Database::web()->prepare(
                'INSERT INTO settings (group_key, setting_key, setting_value, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
            )->execute([$group, $key, $value]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{exp:int, drop:int, yang:int, metin_label:string, metin_pct:int} */
    public static function rates(): array
    {
        $cfg = Config::get('rates', []);
        return [
            'exp' => (int) (self::get('rates', 'exp', (string) ($cfg['exp'] ?? 100)) ?? 100),
            'drop' => (int) (self::get('rates', 'drop', (string) ($cfg['drop'] ?? 50)) ?? 50),
            'yang' => (int) (self::get('rates', 'yang', (string) ($cfg['yang'] ?? 30)) ?? 30),
            'metin_label' => (string) (self::get('rates', 'metin_label', 'Yüksek') ?? 'Yüksek'),
            'metin_pct' => (int) (self::get('rates', 'metin_pct', '85') ?? 85),
        ];
    }

    /** @return array{title:string, target_at:string, target_ts:int} */
    public static function nextChapter(): array
    {
        $title = (string) (self::get('chapter', 'title', 'Yeni harita & boss güncellemesi') ?? 'Yeni harita & boss güncellemesi');
        $target = (string) (self::get('chapter', 'target_at', '') ?? '');
        $ts = $target !== '' ? (int) strtotime($target) : 0;
        if ($ts <= 0) {
            // fallback: next Saturday 20:00
            $now = time();
            $dow = (int) date('w', $now);
            $days = (6 - $dow + 7) % 7;
            $base = strtotime(date('Y-m-d 20:00:00', $now));
            if ($days === 0 && $now > $base) {
                $days = 7;
            }
            $ts = (int) strtotime('+' . $days . ' days', $base);
            $target = date('Y-m-d H:i:s', $ts);
        }
        return ['title' => $title, 'target_at' => $target, 'target_ts' => $ts];
    }

    /** @return array{copyright:string, brand_text:string} */
    public static function footerMeta(): array
    {
        $year = date('Y');
        $name = (string) Config::get('app.name', 'M2DN');
        return [
            'copyright' => (string) (self::get('footer', 'copyright', "© {$year} {$name}. Tüm hakları saklıdır.") ?? "© {$year} {$name}. Tüm hakları saklıdır."),
            'brand_text' => (string) (self::get('footer', 'brand_text', "{$name} — oyuncusuyla birlikte büyüyen bağımsız bir Metin2 sunucusu. Resmi Metin2 markasıyla bağlantısı yoktur; hayran yapımı bir projedir.") ?? ''),
        ];
    }

    /** @return list<array> */
    public static function downloads(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_downloads',
            'id, title, url, description, pack_type, sort_order, is_active',
            $onlyActive,
            'sort_order ASC, id ASC'
        );
    }

    /** @return list<array> */
    public static function features(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_features',
            'id, icon, title, body, sort_order, is_active',
            $onlyActive,
            'sort_order ASC, id ASC'
        );
    }

    /** @return list<array> */
    public static function classes(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_classes',
            'id, slug, name, body, rank_glyph, glow_color, icon, gif_path, stat1_label, stat1_value, stat2_label, stat2_value, sort_order, is_active',
            $onlyActive,
            'sort_order ASC, id ASC'
        );
    }

    /** @return list<array> */
    public static function gallery(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_gallery',
            'id, title, image_path, sort_order, is_active',
            $onlyActive,
            'sort_order ASC, id ASC'
        );
    }

    /** @return list<array> */
    public static function footerLinks(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_footer_links',
            'id, column_key, label, url, sort_order, is_active',
            $onlyActive,
            'column_key ASC, sort_order ASC, id ASC'
        );
    }

    /** @return list<array> */
    public static function socialLinks(bool $onlyActive = true): array
    {
        return self::fetchRows(
            'site_social_links',
            'id, name, icon, url, sort_order, is_active',
            $onlyActive,
            'sort_order ASC, id ASC'
        );
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveDownload(?int $id, string $title, string $url, string $description, string $packType): array
    {
        $title = trim($title);
        $url = trim($url);
        $description = trim($description);
        $packType = trim($packType) !== '' ? trim($packType) : 'normal';
        if ($title === '' || $url === '') {
            return ['ok' => false, 'errors' => ['Başlık ve link zorunlu.']];
        }
        try {
            $pdo = Database::web();
            if ($id && $id > 0) {
                $pdo->prepare(
                    'UPDATE site_downloads SET title=?, url=?, description=?, pack_type=?, updated_at=NOW() WHERE id=?'
                )->execute([$title, $url, $description, $packType, $id]);
            } else {
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM site_downloads')->fetchColumn();
                $pdo->prepare(
                    'INSERT INTO site_downloads (title, url, description, pack_type, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,?,1,NOW(),NOW())'
                )->execute([$title, $url, $description, $packType, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    public static function deleteDownload(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM site_downloads WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveFeature(?int $id, string $icon, string $title, string $body): array
    {
        $icon = trim($icon) ?: 'fa-solid fa-star';
        $title = trim($title);
        $body = trim($body);
        if ($title === '' || $body === '') {
            return ['ok' => false, 'errors' => ['Başlık ve metin zorunlu.']];
        }
        try {
            $pdo = Database::web();
            if ($id && $id > 0) {
                $pdo->prepare('UPDATE site_features SET icon=?, title=?, body=?, updated_at=NOW() WHERE id=?')
                    ->execute([$icon, $title, $body, $id]);
            } else {
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM site_features')->fetchColumn();
                $pdo->prepare(
                    'INSERT INTO site_features (icon, title, body, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,1,NOW(),NOW())'
                )->execute([$icon, $title, $body, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt başarısız.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveClass(int $id, array $data): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz sınıf.']];
        }
        try {
            Database::web()->prepare(
                'UPDATE site_classes SET
                  name=?, body=?, rank_glyph=?, glow_color=?, icon=?, gif_path=?,
                  stat1_label=?, stat1_value=?, stat2_label=?, stat2_value=?, updated_at=NOW()
                 WHERE id=?'
            )->execute([
                trim((string) ($data['name'] ?? '')),
                trim((string) ($data['body'] ?? '')),
                trim((string) ($data['rank_glyph'] ?? '')),
                trim((string) ($data['glow_color'] ?? '#8f1c29')),
                trim((string) ($data['icon'] ?? 'fa-solid fa-star')),
                trim((string) ($data['gif_path'] ?? '')),
                trim((string) ($data['stat1_label'] ?? '')),
                max(0, min(100, (int) ($data['stat1_value'] ?? 0))),
                trim((string) ($data['stat2_label'] ?? '')),
                max(0, min(100, (int) ($data['stat2_value'] ?? 0))),
                $id,
            ]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Sınıf kaydedilemedi.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveGalleryItem(?int $id, string $title, string $imagePath): array
    {
        $title = trim($title);
        $imagePath = trim($imagePath);
        if ($imagePath === '') {
            return ['ok' => false, 'errors' => ['Görsel yolu zorunlu.']];
        }
        try {
            $pdo = Database::web();
            if ($id && $id > 0) {
                $pdo->prepare('UPDATE site_gallery SET title=?, image_path=?, updated_at=NOW() WHERE id=?')
                    ->execute([$title, $imagePath, $id]);
            } else {
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM site_gallery')->fetchColumn();
                $pdo->prepare(
                    'INSERT INTO site_gallery (title, image_path, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,1,NOW(),NOW())'
                )->execute([$title, $imagePath, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Galeri kaydı başarısız.']];
        }
    }

    public static function deleteGallery(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM site_gallery WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveFooterLink(?int $id, string $columnKey, string $label, string $url): array
    {
        $columnKey = trim($columnKey) ?: 'community';
        $label = trim($label);
        $url = trim($url);
        if ($label === '' || $url === '') {
            return ['ok' => false, 'errors' => ['Etiket ve URL zorunlu.']];
        }
        try {
            $pdo = Database::web();
            if ($id && $id > 0) {
                $pdo->prepare('UPDATE site_footer_links SET column_key=?, label=?, url=?, updated_at=NOW() WHERE id=?')
                    ->execute([$columnKey, $label, $url, $id]);
            } else {
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM site_footer_links')->fetchColumn();
                $pdo->prepare(
                    'INSERT INTO site_footer_links (column_key, label, url, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,1,NOW(),NOW())'
                )->execute([$columnKey, $label, $url, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Link kaydedilemedi.']];
        }
    }

    public static function deleteFooterLink(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM site_footer_links WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveSocial(?int $id, string $name, string $icon, string $url, bool $active): array
    {
        $name = trim($name);
        $icon = trim($icon) ?: 'fa-brands fa-link';
        $url = trim($url);
        if ($name === '' || $url === '') {
            return ['ok' => false, 'errors' => ['Ad ve URL zorunlu.']];
        }
        try {
            $pdo = Database::web();
            if ($id && $id > 0) {
                $pdo->prepare('UPDATE site_social_links SET name=?, icon=?, url=?, is_active=?, updated_at=NOW() WHERE id=?')
                    ->execute([$name, $icon, $url, $active ? 1 : 0, $id]);
            } else {
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM site_social_links')->fetchColumn();
                $pdo->prepare(
                    'INSERT INTO site_social_links (name, icon, url, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,?,?,NOW(),NOW())'
                )->execute([$name, $icon, $url, $sort, $active ? 1 : 0]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Sosyal link kaydedilemedi.']];
        }
    }

    public static function deleteSocial(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM site_social_links WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function toggleActive(string $table, int $id, bool $active): bool
    {
        $allowed = ['site_downloads', 'site_features', 'site_gallery', 'site_footer_links', 'site_social_links', 'site_classes'];
        if (!in_array($table, $allowed, true) || $id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare("UPDATE {$table} SET is_active=?, updated_at=NOW() WHERE id=?")
                ->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<array>
     */
    private static function fetchRows(string $table, string $cols, bool $onlyActive, string $order): array
    {
        try {
            $sql = "SELECT {$cols} FROM {$table}";
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY ' . $order;
            return Database::web()->query($sql)->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }
}
