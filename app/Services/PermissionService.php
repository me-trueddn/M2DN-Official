<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Admin yetki grupları ve bayrakları.
 */
final class PermissionService
{
    public const FLAG_BAN = 'ban';
    public const FLAG_PLAYER_DETAIL = 'player_detail';
    public const FLAG_ANNOUNCEMENTS = 'announcements';
    public const FLAG_TICKETS = 'tickets';
    public const FLAG_SITE_SETTINGS = 'site_settings';
    public const FLAG_MENU_OYUNCULAR = 'menu_oyuncular';
    public const FLAG_MENU_SIRALAMALAR = 'menu_siralamalar';
    public const FLAG_MENU_BINEK = 'menu_binek';
    public const FLAG_MENU_GM = 'menu_gm';
    public const FLAG_MENU_IP_BAN = 'menu_ip_ban';
    public const FLAG_MENU_LONCALAR = 'menu_loncalar';
    public const FLAG_MENU_LONCA_SAVASLARI = 'menu_lonca_savaslari';
    public const FLAG_MENU_BANLAR = 'menu_banlar';
    public const FLAG_MENU_DUYURULAR = 'menu_duyurular';
    public const FLAG_MENU_DESTEKLER = 'menu_destekler';
    public const FLAG_MENU_SUNUCU = 'menu_sunucu';
    public const FLAG_MENU_YASAKLI_KELIMELER = 'menu_yasakli_kelimeler';
    public const FLAG_MENU_LOGLAR = 'menu_loglar';
    public const FLAG_MENU_NESNE_MARKET = 'menu_nesne_market';
    public const FLAG_MENU_WIKI = 'menu_wiki';
    public const FLAG_WIKI_MANAGE = 'wiki_manage';
    public const FLAG_RESET_SECURITY_CODE = 'reset_security_code';
    public const FLAG_RESET_SAFEBOX = 'reset_safebox_password';
    public const FLAG_DISABLE_2FA = 'disable_2fa';
    /** Ready Only grubu: menüleri görür, hiçbir şeyi değiştiremez. */
    public const FLAG_READ_ONLY = 'read_only';
    public const GROUP_READY_ONLY = 'Ready Only';

    /** @return array<string, string> */
    public static function flagDefinitions(): array
    {
        return [
            self::FLAG_BAN => 'Oyuncu banlama / ban kaldırma',
            self::FLAG_PLAYER_DETAIL => 'Oyuncu detayı görüntüleme',
            self::FLAG_RESET_SECURITY_CODE => 'Güvenlik kodu sıfırlama',
            self::FLAG_RESET_SAFEBOX => 'Depo şifresi sıfırlama',
            self::FLAG_DISABLE_2FA => 'Hesap 2FA kapatma',
            self::FLAG_READ_ONLY => 'Salt okuma (Ready Only — hiçbir değişiklik yok)',
            self::FLAG_ANNOUNCEMENTS => 'Duyuru işlemleri',
            self::FLAG_TICKETS => 'Destek talebi işlemleri',
            self::FLAG_SITE_SETTINGS => 'Ayarlara erişim',
            self::FLAG_WIKI_MANAGE => 'Wiki kategorileri düzenleme',
            self::FLAG_MENU_OYUNCULAR => 'Menü: Oyuncu Yönetimi',
            self::FLAG_MENU_SIRALAMALAR => 'Menü: Oyuncu Sıralaması',
            self::FLAG_MENU_BINEK => 'Menü: Binek Yönetimi',
            self::FLAG_MENU_GM => 'Menü: GM Yönetimi',
            self::FLAG_MENU_IP_BAN => 'Menü: IP Ban',
            self::FLAG_MENU_LONCALAR => 'Menü: Loncalar',
            self::FLAG_MENU_LONCA_SAVASLARI => 'Menü: Lonca Savaşı',
            self::FLAG_MENU_BANLAR => 'Menü: Ban / Mute',
            self::FLAG_MENU_DUYURULAR => 'Menü: Duyurular',
            self::FLAG_MENU_DESTEKLER => 'Menü: Destek Talepleri',
            self::FLAG_MENU_SUNUCU => 'Menü: Sunucu Yönetimi',
            self::FLAG_MENU_YASAKLI_KELIMELER => 'Menü: Yasaklı Kelimeler',
            self::FLAG_MENU_LOGLAR => 'Menü: Loglar',
            self::FLAG_MENU_NESNE_MARKET => 'Menü: Nesne Market',
            self::FLAG_MENU_WIKI => 'Menü: Wiki',
        ];
    }

    /** @return list<array> */
    public static function listGroups(): array
    {
        try {
            $rows = Database::web()->query(
                'SELECT id, name, web_permission, is_system, created_at
                 FROM permission_groups ORDER BY web_permission ASC, id ASC'
            )->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $out[] = [
                    'id' => $id,
                    'name' => (string) $row['name'],
                    'web_permission' => (int) $row['web_permission'],
                    'is_system' => (int) ($row['is_system'] ?? 0) === 1,
                    'flags' => self::flagsForGroup($id),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string, bool> */
    public static function flagsForGroup(int $groupId): array
    {
        $defs = self::flagDefinitions();
        $flags = array_fill_keys(array_keys($defs), false);
        if ($groupId <= 0) {
            return $flags;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT flag_key, is_enabled FROM permission_group_flags WHERE group_id = ?'
            );
            $stmt->execute([$groupId]);
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $k = (string) ($row['flag_key'] ?? '');
                if (isset($flags[$k])) {
                    $flags[$k] = (int) ($row['is_enabled'] ?? 0) === 1;
                }
            }
        } catch (\Throwable) {
            // ignore
        }
        return $flags;
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveGroup(?int $id, string $name, array $enabledFlags): array
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 120) {
            return ['ok' => false, 'errors' => ['Yetki tanımı zorunlu (max 120).']];
        }

        try {
            $web = Database::web();
            if ($id !== null && $id > 0) {
                $stmt = $web->prepare('SELECT id, is_system, web_permission FROM permission_groups WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) {
                    return ['ok' => false, 'errors' => ['Grup bulunamadı.']];
                }
                // Sistem gruplarında ad güncellenebilir ama web_permission sabit
                $web->prepare('UPDATE permission_groups SET name = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$name, $id]);
                $groupId = $id;
                // User(0) ve Super(2) bayrakları anlamsız; Admin(1) ve özel gruplar düzenlenir
                if ((int) $row['web_permission'] === 0) {
                    return ['ok' => true, 'errors' => [], 'id' => $groupId];
                }
            } else {
                $web->prepare(
                    'INSERT INTO permission_groups (name, web_permission, is_system, created_at, updated_at)
                     VALUES (?, 1, 0, NOW(), NOW())'
                )->execute([$name]);
                $groupId = (int) $web->lastInsertId();
            }

            self::syncFlags($groupId, $enabledFlags);
            return ['ok' => true, 'errors' => [], 'id' => $groupId];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Grup kaydedilemedi.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function deleteGroup(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz grup.']];
        }
        try {
            $web = Database::web();
            $stmt = $web->prepare('SELECT is_system FROM permission_groups WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                return ['ok' => false, 'errors' => ['Grup bulunamadı.']];
            }
            if ((int) ($row['is_system'] ?? 0) === 1) {
                return ['ok' => false, 'errors' => ['Sistem grupları silinemez.']];
            }
            $web->prepare('DELETE FROM permission_group_flags WHERE group_id = ?')->execute([$id]);
            $web->prepare('DELETE FROM account_staff_groups WHERE group_id = ?')->execute([$id]);
            $web->prepare('DELETE FROM permission_groups WHERE id = ?')->execute([$id]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Grup silinemedi.']];
        }
    }

    /**
     * Hesaba bir veya daha fazla yetki grubu ata + account.WebPermission senkronu.
     * - WebPerm 1 grupları birleştirilebilir (bayraklar OR).
     * - Süper Admin (web=2): yalnızca tek grup; diğerleri temizlenir.
     * - Default User (web=0): tek grup, WebPermission=0.
     *
     * @param list<int> $groupIds
     * @param array{account_id?:int,login?:string,permission?:int}|null $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function assignAccountGroups(int $accountId, array $groupIds, ?array $actor = null): array
    {
        if ($accountId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap.']];
        }
        $groupIds = array_values(array_unique(array_filter(array_map('intval', $groupIds), static fn(int $id): bool => $id > 0)));
        if ($groupIds === []) {
            return ['ok' => false, 'errors' => ['En az bir yetki grubu seçin.']];
        }

        try {
            $web = Database::web();
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $stmt = $web->prepare(
                "SELECT id, name, web_permission FROM permission_groups WHERE id IN ({$placeholders})"
            );
            $stmt->execute($groupIds);
            $groups = $stmt->fetchAll() ?: [];
            if (count($groups) !== count($groupIds)) {
                return ['ok' => false, 'errors' => ['Geçersiz yetki grubu seçildi.']];
            }

            $hasSuper = false;
            $hasUser = false;
            $hasAdmin = false;
            foreach ($groups as $g) {
                $wp = AuthService::normalizePermission($g['web_permission'] ?? 0);
                if ($wp === AuthService::PERM_SUPER) {
                    $hasSuper = true;
                } elseif ($wp === AuthService::PERM_USER) {
                    $hasUser = true;
                } else {
                    $hasAdmin = true;
                }
            }

            // Süper Admin: tek rol
            if ($hasSuper) {
                if (count($groupIds) > 1) {
                    return ['ok' => false, 'errors' => ['Süper Admin tek başına atanır; başka grup seçilemez.']];
                }
            }
            // Default User (0) başka gruplarla karışmaz
            if ($hasUser && ($hasAdmin || $hasSuper)) {
                return ['ok' => false, 'errors' => ['Oyuncu (Default User) grubu diğer yetki gruplarıyla birlikte seçilemez.']];
            }

            $actorPerm = AuthService::normalizePermission($actor['permission'] ?? AuthService::PERM_USER);
            $isActorSuper = $actorPerm === AuthService::PERM_SUPER;

            if ($hasSuper && !$isActorSuper) {
                return ['ok' => false, 'errors' => ['Süper Admin atamasını yalnızca Süper Admin yapabilir.']];
            }

            $accStmt = Database::account()->prepare('SELECT WebPermission FROM account WHERE id = ? LIMIT 1');
            $accStmt->execute([$accountId]);
            $accRow = $accStmt->fetch();
            if (!$accRow) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $targetPerm = AuthService::normalizePermission($accRow['WebPermission'] ?? null);
            if ($targetPerm === AuthService::PERM_SUPER && !$isActorSuper) {
                return ['ok' => false, 'errors' => ['Süper Admin hesabını yalnızca Süper Admin değiştirebilir.']];
            }

            $webPerm = $hasSuper
                ? AuthService::PERM_SUPER
                : ($hasUser ? AuthService::PERM_USER : AuthService::PERM_ADMIN);

            $web->beginTransaction();
            try {
                $web->prepare('DELETE FROM account_staff_groups WHERE account_id = ?')->execute([$accountId]);
                $ins = $web->prepare(
                    'INSERT INTO account_staff_groups (account_id, group_id, updated_at) VALUES (?, ?, NOW())'
                );
                foreach ($groupIds as $gid) {
                    $ins->execute([$accountId, $gid]);
                }
                Database::account()->prepare('UPDATE account SET WebPermission = ? WHERE id = ?')
                    ->execute([$webPerm, $accountId]);
                $web->commit();
            } catch (\Throwable $e) {
                if ($web->inTransaction()) {
                    $web->rollBack();
                }
                throw $e;
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Atama başarısız.']];
        }
    }

    /**
     * @param array{account_id?:int,login?:string,permission?:int}|null $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function assignAccountGroup(int $accountId, int $groupId, ?array $actor = null): array
    {
        return self::assignAccountGroups($accountId, [$groupId], $actor);
    }

    /**
     * @param list<int> $accountIds
     * @return array<int, array{group_id:int, group_ids:list<int>, group_name:string, group_names:list<string>}>
     */
    public static function staffMetaForAccounts(array $accountIds): array
    {
        $accountIds = array_values(array_unique(array_filter(array_map('intval', $accountIds))));
        if ($accountIds === []) {
            return [];
        }
        try {
            $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
            $stmt = Database::web()->prepare(
                "SELECT a.account_id, a.group_id, g.name AS group_name
                 FROM account_staff_groups a
                 INNER JOIN permission_groups g ON g.id = a.group_id
                 WHERE a.account_id IN ({$placeholders})
                 ORDER BY g.web_permission DESC, g.name ASC, a.group_id ASC"
            );
            $stmt->execute($accountIds);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $aid = (int) $row['account_id'];
                $gid = (int) $row['group_id'];
                $gname = (string) $row['group_name'];
                if (!isset($out[$aid])) {
                    $out[$aid] = [
                        'group_id' => $gid,
                        'group_ids' => [],
                        'group_name' => $gname,
                        'group_names' => [],
                    ];
                }
                $out[$aid]['group_ids'][] = $gid;
                $out[$aid]['group_names'][] = $gname;
                $out[$aid]['group_name'] = implode(' · ', $out[$aid]['group_names']);
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<int> */
    public static function groupIdsForAccount(int $accountId): array
    {
        if ($accountId <= 0) {
            return [];
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT group_id FROM account_staff_groups WHERE account_id = ? ORDER BY group_id ASC'
            );
            $stmt->execute([$accountId]);
            $ids = [];
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $id) {
                $ids[] = (int) $id;
            }
            return $ids;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function groupIdForAccount(int $accountId): ?int
    {
        $ids = self::groupIdsForAccount($accountId);
        return $ids[0] ?? null;
    }

    /**
     * Sidebar / profil için yetki grubu adı(ları).
     * Atanmış gruplar → sistem grubu (web_permission) → sabit yedek.
     */
    public static function groupNameForUser(?array $user): string
    {
        if ($user === null) {
            return 'Oyuncu';
        }
        $accountId = (int) ($user['account_id'] ?? 0);
        $perm = AuthService::normalizePermission($user['permission'] ?? AuthService::PERM_USER);

        $groupIds = self::groupIdsForAccount($accountId);
        if ($groupIds === []) {
            $sys = self::systemGroupId($perm);
            if ($sys !== null) {
                $groupIds = [$sys];
            }
        }
        if ($groupIds !== []) {
            $names = [];
            foreach ($groupIds as $gid) {
                $name = self::groupNameById($gid);
                if ($name !== '') {
                    $names[] = $name;
                }
            }
            if ($names !== []) {
                return implode(' · ', $names);
            }
        }

        return match ($perm) {
            AuthService::PERM_SUPER => 'Süper Admin',
            AuthService::PERM_ADMIN => 'Yönetici',
            default => 'Oyuncu',
        };
    }

    public static function groupNameById(int $groupId): string
    {
        if ($groupId <= 0) {
            return '';
        }
        try {
            $stmt = Database::web()->prepare('SELECT name FROM permission_groups WHERE id = ? LIMIT 1');
            $stmt->execute([$groupId]);
            $name = $stmt->fetchColumn();
            return is_string($name) ? trim($name) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Atanmış tüm grupların bayrak birleşimi (OR).
     *
     * @return array<string, bool>
     */
    public static function mergedFlagsForUser(?array $user): array
    {
        $defs = self::flagDefinitions();
        $flags = array_fill_keys(array_keys($defs), false);
        if ($user === null) {
            return $flags;
        }
        $perm = (int) ($user['permission'] ?? 0);
        if ($perm === AuthService::PERM_SUPER) {
            return array_fill_keys(array_keys($defs), true);
        }
        if ($perm !== AuthService::PERM_ADMIN) {
            return $flags;
        }

        $accountId = (int) ($user['account_id'] ?? 0);
        $groupIds = self::groupIdsForAccount($accountId);
        if ($groupIds === []) {
            $fallback = self::systemGroupId(AuthService::PERM_ADMIN);
            if ($fallback !== null) {
                $groupIds = [$fallback];
            }
        }
        if ($groupIds === []) {
            // fallback: eski davranış — tüm bayraklar açık
            return array_fill_keys(array_keys($defs), true);
        }

        foreach ($groupIds as $gid) {
            foreach (self::flagsForGroup($gid) as $key => $on) {
                if ($on) {
                    $flags[$key] = true;
                }
            }
        }
        return $flags;
    }

    /**
     * Kullanıcının bayrağı var mı?
     * Super admin: hepsi. User: hiçbiri. Admin: atanmış grupların birleşimi.
     */
    public static function userHasFlag(?array $user, string $flag): bool
    {
        if ($user === null) {
            return false;
        }
        $perm = (int) ($user['permission'] ?? 0);
        if ($perm === AuthService::PERM_SUPER) {
            return true;
        }
        if ($perm !== AuthService::PERM_ADMIN) {
            return false;
        }
        $flags = self::mergedFlagsForUser($user);
        return !empty($flags[$flag]);
    }

    /** Ready Only: read_only bayrağı var ve yazma bayrağı yoksa. */
    public static function isReadOnly(?array $user): bool
    {
        if ($user === null) {
            return false;
        }
        if ((int) ($user['permission'] ?? 0) === AuthService::PERM_SUPER) {
            return false;
        }
        $flags = self::mergedFlagsForUser($user);
        if (empty($flags[self::FLAG_READ_ONLY])) {
            return false;
        }
        $writeFlags = [
            self::FLAG_BAN,
            self::FLAG_ANNOUNCEMENTS,
            self::FLAG_TICKETS,
            self::FLAG_SITE_SETTINGS,
            self::FLAG_WIKI_MANAGE,
            self::FLAG_RESET_SECURITY_CODE,
            self::FLAG_RESET_SAFEBOX,
            self::FLAG_DISABLE_2FA,
        ];
        foreach ($writeFlags as $wf) {
            if (!empty($flags[$wf])) {
                return false;
            }
        }
        return true;
    }

    /**
     * WebPerm 1, WebPerm 2 hedef üzerinde işlem yapamaz.
     * Süper admin herkese işlem yapabilir.
     */
    public static function canOperateOnAccount(array $actor, int $targetAccountId): bool
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? 0);
        if ($actorPerm === AuthService::PERM_SUPER) {
            return true;
        }
        if ($targetAccountId <= 0) {
            return false;
        }
        try {
            $stmt = Database::account()->prepare('SELECT WebPermission FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$targetAccountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return false;
            }
            $targetPerm = AuthService::normalizePermission($row['WebPermission'] ?? null);
            return $targetPerm <= $actorPerm && $targetPerm < AuthService::PERM_SUPER;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function canOperateOnPermission(array $actor, int $targetWebPermission): bool
    {
        $actorPerm = AuthService::normalizePermission($actor['permission'] ?? 0);
        if ($actorPerm === AuthService::PERM_SUPER) {
            return true;
        }
        $targetPerm = AuthService::normalizePermission($targetWebPermission);
        return $targetPerm <= $actorPerm && $targetPerm < AuthService::PERM_SUPER;
    }

    /** Admin POST işlemleri — Ready Only engeli. */
    public static function denyIfReadOnly(?array $user = null): void
    {
        $user = $user ?? AuthService::user();
        if ($user === null || !self::isReadOnly($user)) {
            return;
        }
        \App\Core\Session::flash('panel_errors', ['Ready Only: Bu hesapta işlem yapılamaz (salt görüntüleme).']);
        \App\Core\Session::flash('panel_section', 'ozet');
        redirect('/admin');
    }

    public static function requireFlag(string $flag): array
    {
        $user = AuthService::requireAdmin();
        if (!self::userHasFlag($user, $flag)) {
            \App\Core\Session::flash('panel_errors', ['Bu işlem için yetkin yok.']);
            \App\Core\Session::flash('panel_section', 'ozet');
            redirect('/admin');
        }
        return $user;
    }

    public static function systemGroupId(int $webPermission): ?int
    {
        try {
            // web_permission=1 için birden fazla sistem grubu olabilir (Admin + Ready Only) → Admin öncelikli
            $stmt = Database::web()->prepare(
                'SELECT id FROM permission_groups
                 WHERE web_permission = ? AND is_system = 1
                 ORDER BY CASE WHEN name = \'Admin\' THEN 0 WHEN name = ? THEN 2 ELSE 1 END, id ASC
                 LIMIT 1'
            );
            $stmt->execute([$webPermission, self::GROUP_READY_ONLY]);
            $id = $stmt->fetchColumn();
            return $id !== false ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param array<string, mixed> $enabledFlags */
    private static function syncFlags(int $groupId, array $enabledFlags): void
    {
        $web = Database::web();
        $defs = self::flagDefinitions();
        $stmt = $web->prepare(
            'INSERT INTO permission_group_flags (group_id, flag_key, is_enabled)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)'
        );
        foreach ($defs as $key => $_) {
            $on = !empty($enabledFlags[$key]);
            $stmt->execute([$groupId, $key, $on ? 1 : 0]);
        }
    }
}
