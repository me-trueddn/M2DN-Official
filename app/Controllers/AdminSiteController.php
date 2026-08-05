<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\SiteContentService;

final class AdminSiteController
{
    private function gate(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
    }

    public function saveRates(): void
    {
        $this->gate();
        SiteContentService::set('rates', 'exp', (string) max(0, (int) ($_POST['exp'] ?? 0)));
        SiteContentService::set('rates', 'drop', (string) max(0, (int) ($_POST['drop'] ?? 0)));
        SiteContentService::set('rates', 'yang', (string) max(0, (int) ($_POST['yang'] ?? 0)));
        SiteContentService::set('rates', 'metin_label', trim((string) ($_POST['metin_label'] ?? 'Yüksek')));
        SiteContentService::set('rates', 'metin_pct', (string) max(0, min(100, (int) ($_POST['metin_pct'] ?? 85))));
        $this->ok('Sunucu oranları kaydedildi.', 'oranlar-ayarlari');
    }

    public function saveChapter(): void
    {
        $this->gate();
        $title = trim((string) ($_POST['title'] ?? ''));
        $date = trim((string) ($_POST['date'] ?? ''));
        $time = trim((string) ($_POST['time'] ?? '20:00'));
        if ($title === '' || $date === '') {
            $this->fail(['Başlık ve tarih zorunlu.'], 'siradaki-bolum');
        }
        $target = $date . ' ' . (preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '20:00') . ':00';
        SiteContentService::set('chapter', 'title', $title);
        SiteContentService::set('chapter', 'target_at', $target);
        $this->ok('Sıradaki bölüm güncellendi.', 'siradaki-bolum');
    }

    public function saveFooterMeta(): void
    {
        $this->gate();
        SiteContentService::set('footer', 'copyright', trim((string) ($_POST['copyright'] ?? '')));
        SiteContentService::set('footer', 'brand_text', trim((string) ($_POST['brand_text'] ?? '')));
        $this->ok('Footer metinleri kaydedildi.', 'footer-ayarlari');
    }

    public function saveDownload(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $result = SiteContentService::saveDownload(
            $id > 0 ? $id : null,
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['url'] ?? ''),
            (string) ($_POST['description'] ?? ''),
            (string) ($_POST['pack_type'] ?? 'normal')
        );
        $this->fromResult($result, 'İndirme linki kaydedildi.', 'patch-linkleri');
    }

    public function deleteDownload(): void
    {
        $this->gate();
        SiteContentService::deleteDownload((int) ($_POST['id'] ?? 0));
        $this->ok('İndirme linki silindi.', 'patch-linkleri');
    }

    public function saveFeature(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $result = SiteContentService::saveFeature(
            $id > 0 ? $id : null,
            (string) ($_POST['icon'] ?? ''),
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['body'] ?? '')
        );
        $this->fromResult($result, 'Özellik kaydedildi.', 'ozellikler-ayarlari');
    }

    public function saveClass(): void
    {
        $this->gate();
        $result = SiteContentService::saveClass((int) ($_POST['id'] ?? 0), $_POST);
        $this->fromResult($result, 'Sınıf kaydedildi.', 'siniflar-ayarlari');
    }

    public function saveGallery(): void
    {
        $this->gate();

        $title = (string) ($_POST['title'] ?? '');
        $path = trim((string) ($_POST['image_path'] ?? ''));

        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file((string) $_FILES['image']['tmp_name'])) {
            $uploaded = $this->storeGalleryUpload($_FILES['image']);
            if ($uploaded === null) {
                $this->fail(['Görsel yüklenemedi (jpg/png/webp/gif, max 5MB).'], 'galeri-ayarlari');
            }
            $path = $uploaded;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $result = SiteContentService::saveGalleryItem($id > 0 ? $id : null, $title, $path);
        $this->fromResult($result, 'Galeri kaydı eklendi.', 'galeri-ayarlari');
    }

    public function deleteGallery(): void
    {
        $this->gate();
        SiteContentService::deleteGallery((int) ($_POST['id'] ?? 0));
        $this->ok('Galeri kaydı silindi.', 'galeri-ayarlari');
    }

    public function saveFooterLink(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $result = SiteContentService::saveFooterLink(
            $id > 0 ? $id : null,
            (string) ($_POST['column_key'] ?? 'community'),
            (string) ($_POST['label'] ?? ''),
            (string) ($_POST['url'] ?? '')
        );
        $this->fromResult($result, 'Footer linki kaydedildi.', 'footer-ayarlari');
    }

    public function deleteFooterLink(): void
    {
        $this->gate();
        SiteContentService::deleteFooterLink((int) ($_POST['id'] ?? 0));
        $this->ok('Footer linki silindi.', 'footer-ayarlari');
    }

    public function saveSocial(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $result = SiteContentService::saveSocial(
            $id > 0 ? $id : null,
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['icon'] ?? ''),
            (string) ($_POST['url'] ?? ''),
            !empty($_POST['is_active'])
        );
        $this->fromResult($result, 'Sosyal medya kaydedildi.', 'footer-ayarlari');
    }

    public function deleteSocial(): void
    {
        $this->gate();
        SiteContentService::deleteSocial((int) ($_POST['id'] ?? 0));
        $this->ok('Sosyal medya silindi.', 'footer-ayarlari');
    }

    public function saveLogo(): void
    {
        $this->gate();

        SiteContentService::set('logo', 'home_size', (string) max(16, min(160, (int) ($_POST['home_size'] ?? 48))));
        SiteContentService::set('logo', 'user_size', (string) max(16, min(120, (int) ($_POST['user_size'] ?? 36))));
        SiteContentService::set('logo', 'admin_size', (string) max(16, min(120, (int) ($_POST['admin_size'] ?? 36))));

        if (!empty($_POST['remove_logo'])) {
            SiteContentService::clearBrandFile('logo');
        }
        if (!empty($_POST['remove_icon'])) {
            SiteContentService::clearBrandFile('icon');
        }

        if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file((string) $_FILES['logo']['tmp_name'])) {
            $uploaded = $this->storeBrandingUpload($_FILES['logo'], 'logo');
            if ($uploaded === null) {
                $this->fail(['Logo yüklenemedi (png/jpg/webp/gif/svg, max 5MB).'], 'logo-ayarlari');
            }
            $old = (string) (SiteContentService::get('logo', 'logo_path', '') ?? '');
            SiteContentService::set('logo', 'logo_path', $uploaded);
            if ($old !== '' && $old !== $uploaded && str_starts_with($old, '/uploads/branding/')) {
                $full = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $old);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }

        if (!empty($_FILES['icon']['tmp_name']) && is_uploaded_file((string) $_FILES['icon']['tmp_name'])) {
            $uploaded = $this->storeBrandingUpload($_FILES['icon'], 'icon');
            if ($uploaded === null) {
                $this->fail(['İkon yüklenemedi (png/jpg/webp/gif/svg/ico, max 2MB).'], 'logo-ayarlari');
            }
            $old = (string) (SiteContentService::get('logo', 'icon_path', '') ?? '');
            SiteContentService::set('logo', 'icon_path', $uploaded);
            if ($old !== '' && $old !== $uploaded && str_starts_with($old, '/uploads/branding/')) {
                $full = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $old);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }

        $this->ok('Logo ayarları kaydedildi.', 'logo-ayarlari');
    }

    public function saveCaptcha(): void
    {
        $this->gate();
        $result = \App\Services\CaptchaService::save(
            !empty($_POST['enabled']),
            (string) ($_POST['provider'] ?? 'google'),
            (string) ($_POST['site_key'] ?? ''),
            (string) ($_POST['secret_key'] ?? '')
        );
        $this->fromResult($result, 'Captcha ayarları kaydedildi.', 'captcha-ayarlari');
    }

    public function savePrivacy(): void
    {
        $this->gate();
        $result = \App\Services\LegalContentService::savePrivacy(
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['body'] ?? '')
        );
        $this->fromResult($result, 'Gizlilik / KVKK sayfası kaydedildi.', 'gizlilik-ayarlari');
    }

    public function saveCommunityRule(): void
    {
        $this->gate();
        $idRaw = trim((string) ($_POST['id'] ?? ''));
        $id = $idRaw !== '' ? (int) $idRaw : null;
        if ($id !== null && $id <= 0) {
            $id = null;
        }
        $result = \App\Services\CommunityRulesService::save(
            $id,
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['detail'] ?? ''),
            (string) ($_POST['penalty_1'] ?? ''),
            (string) ($_POST['penalty_2'] ?? ''),
            (string) ($_POST['penalty_3'] ?? ''),
            (int) ($_POST['sort_order'] ?? 0),
            !empty($_POST['is_active'])
        );
        $this->fromResult($result, $id ? 'Kural güncellendi.' : 'Kural eklendi.', 'kurallar-ayarlari');
    }

    public function deleteCommunityRule(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !\App\Services\CommunityRulesService::delete($id)) {
            $this->fail(['Kural silinemedi.'], 'kurallar-ayarlari');
        }
        $this->ok('Kural silindi.', 'kurallar-ayarlari');
    }

    public function renumberCommunityRules(): void
    {
        $this->gate();
        \App\Services\CommunityRulesService::renumber();
        $this->ok('Madde numaraları yenilendi.', 'kurallar-ayarlari');
    }

    /** @param array{ok:bool, errors:list<string>} $result */
    private function fromResult(array $result, string $success, string $section): void
    {
        if (!empty($result['ok'])) {
            $this->ok($success, $section);
        }
        $this->fail($result['errors'] !== [] ? $result['errors'] : ['İşlem başarısız.'], $section);
    }

    private function ok(string $msg, string $section): void
    {
        $labels = [
            'oranlar-ayarlari' => 'Sunucu oranları güncellendi',
            'siradaki-bolum' => 'Sıradaki bölüm güncellendi',
            'footer-ayarlari' => 'Footer / sosyal ayar değişti',
            'patch-linkleri' => 'Patch linki kaydedildi',
            'ozellikler-ayarlari' => 'Özellik kaydedildi',
            'siniflar-ayarlari' => 'Sınıf kaydedildi',
            'galeri-ayarlari' => 'Galeri güncellendi',
            'logo-ayarlari' => 'Logo ayarları güncellendi',
            'captcha-ayarlari' => 'Captcha ayarları güncellendi',
            'gizlilik-ayarlari' => 'Gizlilik / KVKK güncellendi',
            'kurallar-ayarlari' => 'Topluluk kuralları güncellendi',
        ];
        AdminLogService::write(AuthService::user(), $labels[$section] ?? ('Ayar: ' . $section), $msg);
        Session::flash('panel_success', $msg);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    /** @param list<string> $errors */
    private function fail(array $errors, string $section): void
    {
        Session::flash('panel_errors', $errors);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    /** @param array $file */
    private function storeGalleryUpload(array $file): ?string
    {
        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || $size <= 0 || $size > 5 * 1024 * 1024) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            return null;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }
        $name = 'g_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }
        return '/uploads/gallery/' . $name;
    }

    /** @param array $file */
    private function storeBrandingUpload(array $file, string $kind): ?string
    {
        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        $max = $kind === 'icon' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
        if ($tmp === '' || $size <= 0 || $size > $max) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'application/octet-stream' => null,
        ];
        $ext = $map[$mime] ?? null;
        if ($ext === null) {
            $orig = strtolower((string) ($file['name'] ?? ''));
            if (str_ends_with($orig, '.svg') && ($mime === 'image/svg+xml' || $mime === 'text/plain' || $mime === 'application/octet-stream')) {
                $ext = 'svg';
            } elseif (str_ends_with($orig, '.ico')) {
                $ext = 'ico';
            } else {
                return null;
            }
        }
        if ($kind === 'icon' && !in_array($ext, ['png', 'jpg', 'webp', 'gif', 'svg', 'ico'], true)) {
            return null;
        }
        if ($kind === 'logo' && !in_array($ext, ['png', 'jpg', 'webp', 'gif', 'svg'], true)) {
            return null;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'branding';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }
        $name = ($kind === 'icon' ? 'icon_' : 'logo_') . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }
        return '/uploads/branding/' . $name;
    }
}
