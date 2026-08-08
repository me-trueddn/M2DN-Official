<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Wiki Takımımız üyeleri (DNWeb.wiki_team_members).
 */
final class WikiTeamService
{
    public const SLUG_TAKIMIZ = 'takimiz';

    /** @return array<string, array{label:string, filter:string, color:string}> */
    public static function groups(): array
    {
        return [
            'community' => [
                'label' => 'Community Managers',
                'filter' => 'YÖNETİM',
                'color' => 'mgmt',
            ],
            'developer' => [
                'label' => 'Developer Leaders',
                'filter' => 'GELİŞTİRME',
                'color' => 'dev',
            ],
            'team' => [
                'label' => 'Team Leader',
                'filter' => 'GM EKİBİ',
                'color' => 'gm',
            ],
            'support' => [
                'label' => 'Support Leaders',
                'filter' => 'DESTEK',
                'color' => 'support',
            ],
        ];
    }

    /**
     * role_key => [label, default group_key]
     *
     * @return array<string, array{label:string, group:string}>
     */
    public static function roles(): array
    {
        return [
            'community_managers' => ['label' => 'Community Managers', 'group' => 'community'],
            'developer_leaders' => ['label' => 'Developer Leaders', 'group' => 'developer'],
            'game_developers' => ['label' => 'Game Developers', 'group' => 'developer'],
            'web_developers' => ['label' => 'Web Developers', 'group' => 'developer'],
            'client_developers' => ['label' => 'Client Developers', 'group' => 'developer'],
            'team_leader' => ['label' => 'Team Leader', 'group' => 'team'],
            'game_admin' => ['label' => 'Game Admin', 'group' => 'team'],
            'game_master' => ['label' => 'Game Master', 'group' => 'team'],
            'trial_game_master' => ['label' => 'Trial Game Master', 'group' => 'team'],
            'support_leaders' => ['label' => 'Support Leaders', 'group' => 'support'],
            'support_moderator' => ['label' => 'Support Moderatör', 'group' => 'support'],
            'wiki_moderator' => ['label' => 'Wiki Moderatör', 'group' => 'support'],
            'board_moderator' => ['label' => 'Board Moderatör', 'group' => 'support'],
        ];
    }

    /**
     * @return list<array{
     *   id:int,wiki_page_id:int,nick:string,group_key:string,role_key:string,
     *   image_url:string,bio:string,joined_label:string,socials:list<array{label:string,url:string}>,
     *   sort_order:int,is_active:bool,group_label:string,role_label:string,badge_color:string
     * }>
     */
    public static function listByPage(int $pageId, bool $activeOnly = false): array
    {
        if ($pageId <= 0) {
            return [];
        }
        try {
            $sql = 'SELECT id, wiki_page_id, nick, group_key, role_key, image_url, bio, joined_label,
                           socials_json, sort_order, is_active
                    FROM wiki_team_members
                    WHERE wiki_page_id = ?';
            if ($activeOnly) {
                $sql .= ' AND is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            $st = Database::web()->prepare($sql);
            $st->execute([$pageId]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
     * Sayfa üyelerini tamamen yeniler (transaction).
     *
     * @param list<array<string,mixed>> $members
     * @return array{ok:bool, errors:list<string>}
     */
    public static function replaceForPage(int $pageId, array $members): array
    {
        if ($pageId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz sayfa.']];
        }

        $normalized = [];
        $sort = 0;
        foreach ($members as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $item = self::normalizeMember($raw, $sort);
            if ($item === null) {
                continue;
            }
            if (!empty($item['errors'])) {
                return ['ok' => false, 'errors' => $item['errors']];
            }
            $normalized[] = $item['row'];
            $sort++;
        }

        try {
            $web = Database::web();
            $web->beginTransaction();
            $web->prepare('DELETE FROM wiki_team_members WHERE wiki_page_id = ?')->execute([$pageId]);
            $ins = $web->prepare(
                'INSERT INTO wiki_team_members
                 (wiki_page_id, nick, group_key, role_key, image_url, bio, joined_label, socials_json, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
            );
            foreach ($normalized as $row) {
                $ins->execute([
                    $pageId,
                    $row['nick'],
                    $row['group_key'],
                    $row['role_key'],
                    $row['image_url'],
                    $row['bio'],
                    $row['joined_label'],
                    $row['socials_json'],
                    $row['sort_order'],
                    $row['is_active'],
                ]);
            }
            $web->commit();
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            try {
                Database::web()->rollBack();
            } catch (\Throwable) {
            }
            return ['ok' => false, 'errors' => ['Takım üyeleri kaydedilemedi.']];
        }
    }

    public static function sanitizeImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '/uploads/wiki/') || preg_match('#^https?://#i', $url) === 1) {
            return mb_substr($url, 0, 500);
        }
        return '';
    }

    /**
     * @param array<string,mixed> $raw
     * @return array{row:array<string,mixed>, errors:list<string>}|null
     */
    private static function normalizeMember(array $raw, int $fallbackSort): ?array
    {
        $nick = trim((string) ($raw['nick'] ?? ''));
        if ($nick === '') {
            // Boş satır atla
            if (
                trim((string) ($raw['role_key'] ?? '')) === ''
                && trim((string) ($raw['group_key'] ?? '')) === ''
                && trim((string) ($raw['image_url'] ?? '')) === ''
            ) {
                return null;
            }
            return ['row' => [], 'errors' => ['Takım üyesi nick zorunlu.']];
        }
        if (mb_strlen($nick) > 120) {
            return ['row' => [], 'errors' => ['Nick en fazla 120 karakter olabilir.']];
        }

        $roles = self::roles();
        $groups = self::groups();
        $roleKey = trim((string) ($raw['role_key'] ?? ''));
        if ($roleKey === '' || !isset($roles[$roleKey])) {
            return ['row' => [], 'errors' => ['Geçersiz tip kartı (rol).']];
        }

        $groupKey = trim((string) ($raw['group_key'] ?? ''));
        if ($groupKey === '' || !isset($groups[$groupKey])) {
            $groupKey = $roles[$roleKey]['group'];
        }

        $image = self::sanitizeImageUrl((string) ($raw['image_url'] ?? ''));
        $bio = trim((string) ($raw['bio'] ?? ''));
        if (mb_strlen($bio) > 500) {
            $bio = mb_substr($bio, 0, 500);
        }
        $joined = trim((string) ($raw['joined_label'] ?? ''));
        if (mb_strlen($joined) > 120) {
            $joined = mb_substr($joined, 0, 120);
        }

        $socials = [];
        $rawSocials = $raw['socials'] ?? null;
        if (is_string($rawSocials) && $rawSocials !== '') {
            $decoded = json_decode($rawSocials, true);
            $rawSocials = is_array($decoded) ? $decoded : [];
        }
        if (is_array($rawSocials)) {
            foreach ($rawSocials as $s) {
                if (!is_array($s)) {
                    continue;
                }
                $label = trim((string) ($s['label'] ?? ''));
                $url = trim((string) ($s['url'] ?? ''));
                if ($url === '' || !preg_match('#^https?://#i', $url)) {
                    continue;
                }
                if ($label === '') {
                    $label = 'Link';
                }
                $socials[] = [
                    'label' => mb_substr($label, 0, 40),
                    'url' => mb_substr($url, 0, 300),
                ];
                if (count($socials) >= 6) {
                    break;
                }
            }
        }

        $sort = isset($raw['sort_order']) ? (int) $raw['sort_order'] : $fallbackSort;
        $active = !isset($raw['is_active']) || !empty($raw['is_active']) ? 1 : 0;

        return [
            'row' => [
                'nick' => $nick,
                'group_key' => $groupKey,
                'role_key' => $roleKey,
                'image_url' => $image,
                'bio' => $bio,
                'joined_label' => $joined,
                'socials_json' => $socials === [] ? null : json_encode($socials, JSON_UNESCAPED_UNICODE),
                'sort_order' => $sort,
                'is_active' => $active,
            ],
            'errors' => [],
        ];
    }

    /** @param array<string,mixed> $row */
    private static function map(array $row): array
    {
        $groupKey = (string) ($row['group_key'] ?? '');
        $roleKey = (string) ($row['role_key'] ?? '');
        $groups = self::groups();
        $roles = self::roles();
        $socials = [];
        $json = (string) ($row['socials_json'] ?? '');
        if ($json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $s) {
                    if (!is_array($s)) {
                        continue;
                    }
                    $socials[] = [
                        'label' => (string) ($s['label'] ?? 'Link'),
                        'url' => (string) ($s['url'] ?? ''),
                    ];
                }
            }
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'wiki_page_id' => (int) ($row['wiki_page_id'] ?? 0),
            'nick' => (string) ($row['nick'] ?? ''),
            'group_key' => $groupKey,
            'role_key' => $roleKey,
            'image_url' => (string) ($row['image_url'] ?? ''),
            'bio' => (string) ($row['bio'] ?? ''),
            'joined_label' => (string) ($row['joined_label'] ?? ''),
            'socials' => $socials,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'group_label' => (string) ($groups[$groupKey]['label'] ?? $groupKey),
            'role_label' => (string) ($roles[$roleKey]['label'] ?? $roleKey),
            'badge_color' => (string) ($groups[$groupKey]['color'] ?? 'mgmt'),
        ];
    }
}
