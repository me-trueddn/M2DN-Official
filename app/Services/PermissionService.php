<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

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
    public const FLAG_RESET_SECURITY_CODE = 'reset_security_code';
    public const FLAG_RESET_SAFEBOX = 'reset_safebox_password';

    /** @return array<string, string> */
    public static function flagDefinitions(): array
    {
        return [
            self::FLAG_BAN => 'Oyuncu banlama / ban kaldırma',
            self::FLAG_PLAYER_DETAIL => 'Oyuncu detayı görüntüleme',
            self::FLAG_RESET_SECURITY_CODE => 'Güvenlik kodu sıfırlama',
            self::FLAG_RESET_SAFEBOX => 'Depo şifresi sıfırlama',
            self::FLAG_ANNOUNCEMENTS => 'Duyuru işlemleri',
            self::FLAG_TICKETS => 'Destek talebi işlemleri',
            self::FLAG_SITE_SETTINGS => 'Ayarlara erişim',
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
     * Hesaba yetki grubu ata + account.WebPermission senkronu.
     * Super Admin (web=2) ataması / mevcut süper hesabı değiştirme: yalnızca süper admin.
     *
     * @param array{account_id?:int,login?:string,permission?:int}|null $actor
     * @return array{ok:bool, errors:list<string>}
     */
    public static function assignAccountGroup(int $accountId, int $groupId, ?array $actor = null): array
    {
        if ($accountId <= 0 || $groupId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz hesap veya grup.']];
        }
        try {
            $web = Database::web();
            $stmt = $web->prepare(
                'SELECT id, name, web_permission FROM permission_groups WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$groupId]);
            $group = $stmt->fetch();
            if (!$group) {
                return ['ok' => false, 'errors' => ['Grup bulunamadı.']];
            }
            $webPerm = (int) $group['web_permission'];
            if (!in_array($webPerm, [AuthService::PERM_USER, AuthService::PERM_ADMIN, AuthService::PERM_SUPER], true)) {
                return ['ok' => false, 'errors' => ['Geçersiz web izni.']];
            }

            $actorPerm = AuthService::normalizePermission($actor['permission'] ?? AuthService::PERM_USER);
            $isActorSuper = $actorPerm === AuthService::PERM_SUPER;

            if ($webPerm === AuthService::PERM_SUPER && !$isActorSuper) {
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

            $web->prepare(
                'INSERT INTO account_staff_groups (account_id, group_id, updated_at)
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), updated_at = NOW()'
            )->execute([$accountId, $groupId]);

            Database::account()->prepare('UPDATE account SET WebPermission = ? WHERE id = ?')
                ->execute([$webPerm, $accountId]);

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Atama başarısız.']];
        }
    }

    /** @param list<int> $accountIds @return array<int, array{group_id:int, group_name:string}> */
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
                 WHERE a.account_id IN ({$placeholders})"
            );
            $stmt->execute($accountIds);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $out[(int) $row['account_id']] = [
                    'group_id' => (int) $row['group_id'],
                    'group_name' => (string) $row['group_name'],
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function groupIdForAccount(int $accountId): ?int
    {
        if ($accountId <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT group_id FROM account_staff_groups WHERE account_id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $id = $stmt->fetchColumn();
            return $id !== false ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Sidebar / profil için yetki grubu adı.
     * Atanmış grup → sistem grubu (web_permission) → sabit yedek.
     */
    public static function groupNameForUser(?array $user): string
    {
        if ($user === null) {
            return 'Oyuncu';
        }
        $accountId = (int) ($user['account_id'] ?? 0);
        $perm = AuthService::normalizePermission($user['permission'] ?? AuthService::PERM_USER);

        $groupId = self::groupIdForAccount($accountId);
        if ($groupId === null) {
            $groupId = self::systemGroupId($perm);
        }
        if ($groupId !== null) {
            $name = self::groupNameById($groupId);
            if ($name !== '') {
                return $name;
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
     * Kullanıcının bayrağı var mı?
     * Super admin: hepsi. User: hiçbiri. Admin/özel: gruba göre.
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

        $accountId = (int) ($user['account_id'] ?? 0);
        $groupId = self::groupIdForAccount($accountId);
        if ($groupId === null) {
            // Varsayılan Admin sistemi grubu
            $groupId = self::systemGroupId(AuthService::PERM_ADMIN);
        }
        if ($groupId === null) {
            return true; // fallback: eski davranış
        }
        $flags = self::flagsForGroup($groupId);
        return !empty($flags[$flag]);
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
            $stmt = Database::web()->prepare(
                'SELECT id FROM permission_groups WHERE web_permission = ? AND is_system = 1 LIMIT 1'
            );
            $stmt->execute([$webPermission]);
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
