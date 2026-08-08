<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\WikiCategoryService;
use App\Services\WikiContentTypeService;
use App\Services\WikiPageService;

final class AdminWikiController
{
    private function gate(): array
    {
        $user = AuthService::requireAdmin();
        if (!PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_WIKI)
            && !PermissionService::userHasFlag($user, PermissionService::FLAG_WIKI_MANAGE)
        ) {
            Session::flash('panel_errors', ['Wiki yetkin yok.']);
            redirect('/admin');
        }
        Security::requireCsrf('login');
        return $user;
    }

    private function requireManage(array $user, string $section = 'wiki-kategoriler'): void
    {
        if (!PermissionService::userHasFlag($user, PermissionService::FLAG_WIKI_MANAGE)) {
            Session::flash('panel_errors', ['Wiki düzenleme yetkin yok.']);
            Session::flash('panel_section', $section);
            redirect('/admin?section=' . $section);
        }
        PermissionService::denyIfReadOnly($user);
    }

    private function ok(string $msg, string $section = 'wiki-kategoriler'): void
    {
        Session::flash('panel_success', $msg);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    private function fail(array $errors, string $section = 'wiki-kategoriler'): void
    {
        Session::flash('panel_errors', $errors);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    public function saveCategory(): void
    {
        $user = $this->gate();
        $this->requireManage($user);
        $result = WikiCategoryService::save([
            'id' => (int) ($_POST['id'] ?? 0),
            'name' => (string) ($_POST['name'] ?? ''),
            'slug' => (string) ($_POST['slug'] ?? ''),
            'is_main' => !empty($_POST['is_main']),
            'parent_id' => (int) ($_POST['parent_id'] ?? 0),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']),
            'is_wiki_home' => !empty($_POST['is_wiki_home']),
        ]);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.']);
        }
        AdminLogService::write($user, 'Wiki kategori kaydedildi', (string) ($_POST['name'] ?? ''));
        $this->ok('Kategori kaydedildi.');
    }

    public function setHomeCategory(): void
    {
        $user = $this->gate();
        $this->requireManage($user);
        $id = (int) ($_POST['id'] ?? 0);
        if (!WikiCategoryService::setWikiHome($id)) {
            $this->fail(['Wiki ana sayfası yalnızca aktif alt kategori olabilir.']);
        }
        AdminLogService::write($user, 'Wiki ana sayfa seçildi', 'id=' . $id);
        $this->ok('Wiki ana sayfası güncellendi.');
    }

    public function deleteCategory(): void
    {
        $user = $this->gate();
        $this->requireManage($user);
        $id = (int) ($_POST['id'] ?? 0);
        if (!WikiCategoryService::delete($id)) {
            $this->fail(['Kategori silinemedi. Alt kategorisi olan ana kategori önce boşaltılmalı.']);
        }
        AdminLogService::write($user, 'Wiki kategori silindi', 'id=' . $id);
        $this->ok('Kategori silindi.');
    }

    public function toggleCategory(): void
    {
        $user = $this->gate();
        $this->requireManage($user);
        $id = (int) ($_POST['id'] ?? 0);
        $active = ((string) ($_POST['is_active'] ?? '1')) === '1';
        if (!WikiCategoryService::toggle($id, $active)) {
            $this->fail(['Durum güncellenemedi.']);
        }
        $this->ok('Kategori durumu güncellendi.');
    }

    public function saveContentType(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerik-tipleri';
        $this->requireManage($user, $section);
        $result = WikiContentTypeService::save([
            'id' => (int) ($_POST['id'] ?? 0),
            'name' => (string) ($_POST['name'] ?? ''),
            'slug' => (string) ($_POST['slug'] ?? ''),
            'is_active' => !empty($_POST['is_active']),
        ]);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.'], $section);
        }
        AdminLogService::write($user, 'Wiki içerik tipi kaydedildi', (string) ($_POST['name'] ?? ''));
        $this->ok('İçerik tipi kaydedildi.', $section);
    }

    public function deleteContentType(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerik-tipleri';
        $this->requireManage($user, $section);
        $id = (int) ($_POST['id'] ?? 0);
        if (!WikiContentTypeService::delete($id)) {
            $this->fail(['İçerik tipi silinemedi (seed veya kullanımda olabilir).'], $section);
        }
        AdminLogService::write($user, 'Wiki içerik tipi silindi', 'id=' . $id);
        $this->ok('İçerik tipi silindi.', $section);
    }

    public function toggleContentType(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerik-tipleri';
        $this->requireManage($user, $section);
        $id = (int) ($_POST['id'] ?? 0);
        $active = ((string) ($_POST['is_active'] ?? '1')) === '1';
        if (!WikiContentTypeService::toggle($id, $active)) {
            $this->fail(['Durum güncellenemedi.'], $section);
        }
        $this->ok('İçerik tipi durumu güncellendi.', $section);
    }

    public function savePage(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerikler';
        $this->requireManage($user, $section);

        $teamMembers = [];
        $rawTeam = (string) ($_POST['team_members_json'] ?? '');
        if ($rawTeam !== '') {
            $decoded = json_decode($rawTeam, true);
            if (is_array($decoded)) {
                $teamMembers = $decoded;
            }
        }

        $result = WikiPageService::save([
            'id' => (int) ($_POST['id'] ?? 0),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'content_type_id' => (int) ($_POST['content_type_id'] ?? 0),
            'title' => (string) ($_POST['title'] ?? ''),
            'body_html' => (string) ($_POST['body_html'] ?? ''),
            'is_active' => !empty($_POST['is_active']),
            'team_members' => $teamMembers,
        ]);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.'], $section);
        }
        AdminLogService::write($user, 'Wiki içerik kaydedildi', (string) ($_POST['title'] ?? ''));
        $this->ok('İçerik kaydedildi.', $section);
    }

    public function deletePage(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerikler';
        $this->requireManage($user, $section);
        $id = (int) ($_POST['id'] ?? 0);
        if (!WikiPageService::delete($id)) {
            $this->fail(['İçerik silinemedi.'], $section);
        }
        AdminLogService::write($user, 'Wiki içerik silindi', 'id=' . $id);
        $this->ok('İçerik silindi.', $section);
    }

    public function togglePage(): void
    {
        $user = $this->gate();
        $section = 'wiki-icerikler';
        $this->requireManage($user, $section);
        $id = (int) ($_POST['id'] ?? 0);
        $active = ((string) ($_POST['is_active'] ?? '1')) === '1';
        if (!WikiPageService::toggle($id, $active)) {
            $this->fail(['Durum güncellenemedi.'], $section);
        }
        $this->ok('İçerik durumu güncellendi.', $section);
    }

    public function uploadImage(): void
    {
        $user = $this->gate();
        $this->requireManage($user, 'wiki-icerikler');
        header('Content-Type: application/json; charset=utf-8');
        $file = isset($_FILES['image']) && is_array($_FILES['image']) ? $_FILES['image'] : null;
        $result = WikiPageService::storeImageUpload($file);
        if (empty($result['ok'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'errors' => $result['errors'] ?? ['Yükleme başarısız.']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['ok' => true, 'url' => $result['url'] ?? ''], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
