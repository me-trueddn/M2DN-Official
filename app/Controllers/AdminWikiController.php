<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\WikiService;

final class AdminWikiController
{
    private function gate(): array
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_WIKI_MANAGE);
        PermissionService::denyIfReadOnly($user);
        Security::requireCsrf('login');
        return $user;
    }

    public function save(): void
    {
        $user = $this->gate();
        $data = WikiService::fromPost($_POST);
        $result = WikiService::save($data);
        if (empty($result['ok'])) {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Wiki kaydedilemedi.']);
            Session::flash('panel_section', 'wiki-yonetim');
            redirect('/admin?section=wiki-yonetim');
        }
        AdminLogService::write($user, 'Wiki içeriği güncellendi', 'Wiki Yönetimi kaydedildi');
        Session::flash('panel_success', 'Wiki içeriği kaydedildi.');
        Session::flash('panel_section', 'wiki-yonetim');
        redirect('/admin?section=wiki-yonetim');
    }

    public function reset(): void
    {
        $user = $this->gate();
        $result = WikiService::resetToDefaults();
        if (empty($result['ok'])) {
            Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['Sıfırlama başarısız.']);
            Session::flash('panel_section', 'wiki-yonetim');
            redirect('/admin?section=wiki-yonetim');
        }
        AdminLogService::write($user, 'Wiki varsayılana döndürüldü', 'Wiki Yönetimi reset');
        Session::flash('panel_success', 'Wiki içeriği varsayılanlara döndürüldü.');
        Session::flash('panel_section', 'wiki-yonetim');
        redirect('/admin?section=wiki-yonetim');
    }
}
