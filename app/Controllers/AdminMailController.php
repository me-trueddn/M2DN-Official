<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AdminLogService;
use App\Services\AuthService;
use App\Services\MailService;
use App\Services\PermissionService;

final class AdminMailController
{
    private function gate(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');
    }

    public function saveServer(): void
    {
        $this->gate();
        $idRaw = trim((string) ($_POST['id'] ?? ''));
        $id = $idRaw !== '' ? (int) $idRaw : null;
        if ($id !== null && $id <= 0) {
            $id = null;
        }
        $result = MailService::saveServer(
            $id,
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['provider'] ?? 'custom'),
            (string) ($_POST['host'] ?? ''),
            (int) ($_POST['port'] ?? 587),
            (string) ($_POST['encryption'] ?? 'tls'),
            (string) ($_POST['username'] ?? ''),
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['from_email'] ?? ''),
            (string) ($_POST['from_name'] ?? ''),
            !empty($_POST['activate'])
        );
        $this->fromResult($result, $id ? 'Mail sunucusu güncellendi.' : 'Mail sunucusu eklendi.', 'mail-ayarlari');
    }

    public function deleteServer(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !MailService::deleteServer($id)) {
            $this->fail(['Sunucu silinemedi.'], 'mail-ayarlari');
        }
        AdminLogService::write(AuthService::user(), 'Mail sunucusu silindi', '#' . $id);
        $this->ok('Mail sunucusu silindi.', 'mail-ayarlari');
    }

    public function activateServer(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || !MailService::setActiveServer($id)) {
            $this->fail(['Aktif sunucu seçilemedi.'], 'mail-ayarlari');
        }
        AdminLogService::write(AuthService::user(), 'Aktif mail sunucusu değiştirildi', '#' . $id);
        $this->ok('Aktif mail sunucusu güncellendi.', 'mail-ayarlari');
    }

    public function saveTemplate(): void
    {
        $this->gate();
        $result = MailService::saveTemplate(
            (string) ($_POST['code'] ?? ''),
            (string) ($_POST['subject'] ?? ''),
            (string) ($_POST['body_html'] ?? ''),
            !empty($_POST['is_enabled'])
        );
        $this->fromResult($result, 'Bildirim şablonu kaydedildi.', 'mail-ayarlari');
    }

    public function sendTest(): void
    {
        $this->gate();
        $to = trim((string) ($_POST['to_email'] ?? ''));
        $user = AuthService::user();
        $login = (string) ($user['login'] ?? 'test');
        if ($to === '') {
            $to = (string) ($_POST['username'] ?? '');
        }
        // Test mailinde de {{logo}}/{{app}}/{{email}} doldur
        $testHtml = MailService::cardShell(
            '✉️ TEST MAİLİ',
            '<p style="color:#bfae9e;font-size:15px;line-height:1.6;margin:0 0 25px 0;text-align:center;">Bu bir test mailidir.<br>Hesap: <strong style="color:#e5a93c;">' . htmlspecialchars($login) . '</strong></p>',
            ['label' => 'SİTEYE GİT', 'href' => rtrim((string) \App\Core\Config::get('app.url', ''), '/') ?: '#'],
            null,
            'Mail sunucusu ayarların çalışıyor.'
        );
        $testHtml = MailService::render($testHtml, [
            'app' => (string) \App\Core\Config::get('app.name', 'M2DN'),
            'email' => $to,
            'login' => $login,
            'logo' => MailService::logoUrl(),
            'link' => rtrim((string) \App\Core\Config::get('app.url', ''), '/'),
        ]);
        $result = MailService::sendRaw(
            $to,
            $login,
            'M2DN test maili',
            $testHtml,
            'test'
        );
        $_POST['mail_tab'] = 'loglar';
        $this->fromResult($result, 'Test maili gönderildi.', 'mail-ayarlari');
    }

    public function resend(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $result = MailService::resend($id);
        $_POST['mail_tab'] = 'loglar';
        if (!empty($result['ok'])) {
            AdminLogService::write(AuthService::user(), 'Mail tekrar gönderildi', '#' . $id);
        }
        $this->fromResult($result, 'Mail tekrar gönderildi.', 'mail-ayarlari');
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
        AdminLogService::write(AuthService::user(), 'Mail ayarı', $msg);
        Session::flash('panel_success', $msg);
        Session::flash('panel_section', $section);
        $tab = trim((string) ($_POST['mail_tab'] ?? ''));
        if ($tab !== '') {
            Session::flash('mail_tab', $tab);
        }
        redirect('/admin?section=' . $section);
    }

    /** @param list<string> $errors */
    private function fail(array $errors, string $section): void
    {
        Session::flash('panel_errors', $errors);
        Session::flash('panel_section', $section);
        $tab = trim((string) ($_POST['mail_tab'] ?? ''));
        if ($tab !== '') {
            Session::flash('mail_tab', $tab);
        }
        redirect('/admin?section=' . $section);
    }
}
