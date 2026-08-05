<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AnnouncementService;
use App\Services\PermissionService;

final class AdminAnnouncementController
{
    public function save(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_ANNOUNCEMENTS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $result = AnnouncementService::save(
            $id > 0 ? $id : null,
            (int) ($_POST['type_id'] ?? 0),
            $title,
            (string) ($_POST['body'] ?? ''),
            !empty($_POST['is_active']),
            $user
        );
        Session::flash('panel_section', 'duyurular');
        if (!empty($result['ok'])) {
            $savedId = (int) ($result['id'] ?? $id);
            Session::flash('panel_success', $id > 0 ? 'Duyuru güncellendi.' : 'Duyuru yayınlandı.');
            AdminLogService::write(
                $user,
                $id > 0 ? 'Duyuru güncellendi' : 'Duyuru yayınlandı',
                self::annDetail($savedId, $title)
            );
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Kayıt başarısız.']);
        }
        redirect('/admin?section=duyurular');
    }

    public function toggle(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_ANNOUNCEMENTS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $active = !empty($_POST['is_active']);
        $existing = AnnouncementService::get($id);
        $ok = AnnouncementService::toggle($id, $active);
        Session::flash('panel_section', 'duyurular');
        if ($ok) {
            Session::flash('panel_success', $active ? 'Duyuru aktif.' : 'Duyuru pasif.');
            AdminLogService::write(
                $user,
                $active ? 'Duyuru aktif' : 'Duyuru pasif',
                self::annDetail($id, (string) ($existing['title'] ?? ''))
            );
        } else {
            Session::flash('panel_errors', ['Durum güncellenemedi.']);
        }
        redirect('/admin?section=duyurular');
    }

    public function delete(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_ANNOUNCEMENTS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $existing = AnnouncementService::get($id);
        $ok = AnnouncementService::delete($id);
        Session::flash('panel_section', 'duyurular');
        if ($ok) {
            Session::flash('panel_success', 'Duyuru silindi.');
            AdminLogService::write(
                $user,
                'Duyuru silindi',
                self::annDetail($id, (string) ($existing['title'] ?? ''))
            );
        } else {
            Session::flash('panel_errors', ['Silinemedi.']);
        }
        redirect('/admin?section=duyurular');
    }

    public function saveType(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $result = AnnouncementService::saveType($id > 0 ? $id : null, $name);
        Session::flash('panel_section', 'duyuru-turleri');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Duyuru türü kaydedildi.');
            $typeId = $id > 0 ? $id : self::findTypeIdByName($name);
            AdminLogService::write(
                $user,
                $id > 0 ? 'Duyuru türü güncellendi' : 'Duyuru türü eklendi',
                self::annDetail($typeId > 0 ? $typeId : $id, $name !== '' ? $name : 'Tür')
            );
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Kayıt başarısız.']);
        }
        redirect('/admin?section=duyuru-turleri');
    }

    public function deleteType(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $name = self::typeNameById($id);
        $ok = AnnouncementService::deleteType($id);
        Session::flash('panel_section', 'duyuru-turleri');
        if ($ok) {
            Session::flash('panel_success', 'Duyuru türü silindi / pasife alındı.');
            AdminLogService::write($user, 'Duyuru türü silindi', self::annDetail($id, $name));
        } else {
            Session::flash('panel_errors', ['Tür silinemedi.']);
        }
        redirect('/admin?section=duyuru-turleri');
    }

    public function toggleType(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $active = !empty($_POST['is_active']);
        $name = self::typeNameById($id);
        AnnouncementService::toggleType($id, $active);
        AdminLogService::write(
            $user,
            'Duyuru türü durumu',
            self::annDetail($id, $name) . ' → ' . ($active ? 'aktif' : 'pasif')
        );
        Session::flash('panel_success', 'Tür güncellendi.');
        Session::flash('panel_section', 'duyuru-turleri');
        redirect('/admin?section=duyuru-turleri');
    }

    private static function annDetail(int $id, string $label): string
    {
        $label = trim($label);
        if ($id > 0 && $label !== '') {
            return '#' . $id . ' · ' . $label;
        }
        if ($id > 0) {
            return '#' . $id;
        }
        return $label !== '' ? $label : '—';
    }

    private static function typeNameById(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        foreach (AnnouncementService::types(false) as $t) {
            if ((int) ($t['id'] ?? 0) === $id) {
                return (string) ($t['name'] ?? '');
            }
        }
        return '';
    }

    private static function findTypeIdByName(string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            return 0;
        }
        foreach (AnnouncementService::types(false) as $t) {
            if ((string) ($t['name'] ?? '') === $name) {
                return (int) ($t['id'] ?? 0);
            }
        }
        return 0;
    }
}
