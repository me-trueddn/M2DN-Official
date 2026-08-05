<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\TicketService;

final class AdminAccessController
{
    public function saveGroup(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $flags = [];
        foreach (PermissionService::flagDefinitions() as $key => $_) {
            $flags[$key] = !empty($_POST['flags'][$key]);
        }
        $result = PermissionService::saveGroup(
            $id > 0 ? $id : null,
            (string) ($_POST['name'] ?? ''),
            $flags
        );
        $this->flash($result, 'Yetki grubu kaydedildi.', 'yetki-gruplari');
    }

    public function deleteGroup(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $result = PermissionService::deleteGroup((int) ($_POST['id'] ?? 0));
        $this->flash($result, 'Yetki grubu silindi.', 'yetki-gruplari');
    }

    public function assignGroup(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $result = PermissionService::assignAccountGroup($accountId, $groupId, $user);
        Session::flash('panel_section', 'oyuncular');
        if (!empty($result['ok'])) {
            Session::flash('panel_success', 'Yetki grubu atandı.');
            AdminLogService::write($user, 'Yetki grubu atandı', 'Grup #' . $groupId, $accountId, null);
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Atama başarısız.']);
        }
        redirect('/admin?section=oyuncular');
    }

    public function saveTicketCategory(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $result = TicketService::saveCategory(
            $id > 0 ? $id : null,
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['description'] ?? '')
        );
        $this->flash($result, 'Kategori kaydedildi.', 'ticket-ayarlari');
    }

    public function deleteTicketCategory(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        TicketService::deleteCategory((int) ($_POST['id'] ?? 0));
        AdminLogService::write(AuthService::user(), 'Ticket kategorisi silindi', 'ID #' . (int) ($_POST['id'] ?? 0));
        Session::flash('panel_success', 'Kategori silindi.');
        Session::flash('panel_section', 'ticket-ayarlari');
        redirect('/admin?section=ticket-ayarlari');
    }

    public function saveTicketStatus(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $result = TicketService::saveStatus(
            $id > 0 ? $id : null,
            (string) ($_POST['code'] ?? ''),
            (string) ($_POST['label'] ?? '')
        );
        $this->flash($result, 'Durum kaydedildi.', 'ticket-ayarlari');
    }

    public function saveTicketFileType(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $result = TicketService::saveFileType(
            (string) ($_POST['extension'] ?? ''),
            (string) ($_POST['mime_type'] ?? '')
        );
        $this->flash($result, 'Dosya türü eklendi.', 'ticket-ayarlari');
    }

    public function deleteTicketFileType(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        TicketService::deleteFileType((int) ($_POST['id'] ?? 0));
        AdminLogService::write(AuthService::user(), 'Ticket dosya türü silindi', 'ID #' . (int) ($_POST['id'] ?? 0));
        Session::flash('panel_success', 'Dosya türü silindi.');
        Session::flash('panel_section', 'ticket-ayarlari');
        redirect('/admin?section=ticket-ayarlari');
    }

    public function toggleTicketFileType(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $active = !empty($_POST['is_active']);
        TicketService::toggleFileType($id, $active);
        AdminLogService::write(AuthService::user(), 'Ticket dosya türü durumu', 'ID #' . $id . ' → ' . ($active ? 'aktif' : 'pasif'));
        Session::flash('panel_success', 'Dosya türü güncellendi.');
        Session::flash('panel_section', 'ticket-ayarlari');
        redirect('/admin?section=ticket-ayarlari');
    }

    /** @param array{ok:bool, errors:list<string>} $result */
    private function flash(array $result, string $ok, string $section): void
    {
        Session::flash('panel_section', $section);
        if (!empty($result['ok'])) {
            Session::flash('panel_success', $ok);
            AdminLogService::write(AuthService::user(), $ok, 'Bölüm: ' . $section);
        } else {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['İşlem başarısız.']);
        }
        redirect('/admin?section=' . $section);
    }
}
