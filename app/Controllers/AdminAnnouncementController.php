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
        $result = AnnouncementService::save(
            $id > 0 ? $id : null,
            (int) ($_POST['type_id'] ?? 0),
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['body'] ?? ''),
            !empty($_POST['is_active']),
            $user
        );
        Session::flash('panel_section', 'duyurular');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', $id > 0 ? 'Duyuru güncellendi.' : 'Duyuru yayınlandı.');
            AdminLogService::write($user, $id > 0 ? 'Duyuru güncellendi' : 'Duyuru yayınlandı', (string) ($_POST['title'] ?? ''));
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
        $ok = AnnouncementService::toggle($id, $active);
        Session::flash('panel_section', 'duyurular');
        if ($ok) {
            Session::flash('panel_success', $active ? 'Duyuru aktif.' : 'Duyuru pasif.');
            AdminLogService::write($user, $active ? 'Duyuru aktif' : 'Duyuru pasif', 'ID #' . $id);
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
        $ok = AnnouncementService::delete($id);
        Session::flash('panel_section', 'duyurular');
        if ($ok) {
            Session::flash('panel_success', 'Duyuru silindi.');
            AdminLogService::write($user, 'Duyuru silindi', 'ID #' . $id);
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
        $result = AnnouncementService::saveType($id > 0 ? $id : null, (string) ($_POST['name'] ?? ''));
        Session::flash('panel_section', 'duyuru-turleri');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Duyuru türü kaydedildi.');
            AdminLogService::write($user, 'Duyuru türü kaydedildi', (string) ($_POST['name'] ?? ''));
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
        $ok = AnnouncementService::deleteType($id);
        Session::flash('panel_section', 'duyuru-turleri');
        if ($ok) {
            Session::flash('panel_success', 'Duyuru türü silindi / pasife alındı.');
            AdminLogService::write($user, 'Duyuru türü silindi', 'ID #' . $id);
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
        AnnouncementService::toggleType($id, $active);
        AdminLogService::write($user, 'Duyuru türü durumu', 'ID #' . $id . ' → ' . ($active ? 'aktif' : 'pasif'));
        Session::flash('panel_success', 'Tür güncellendi.');
        Session::flash('panel_section', 'duyuru-turleri');
        redirect('/admin?section=duyuru-turleri');
    }
}
