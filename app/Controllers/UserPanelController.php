<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AccountSecurityService;
use App\Services\ActivityLogService;
use App\Services\AnnouncementService;
use App\Services\AuthService;
use App\Services\PenaltyService;
use App\Services\PlayerService;
use App\Services\TicketService;
use App\Services\Totp;

final class UserPanelController
{
    public function index(): void
    {
        $user = AuthService::requireLogin();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $accountId = (int) $user['account_id'];
        PenaltyService::liftExpired();
        $dashboard = PlayerService::dashboard($accountId);
        $security = AccountSecurityService::getSettings($accountId);

        $searchQuery = trim((string) ($_GET['q'] ?? ''));
        $searchResults = [];
        if ($searchQuery !== '') {
            $searchResults = PlayerService::searchOwnedCharacters($accountId, $searchQuery);
        }

        $pendingSecret = (string) ($security['totp_secret'] ?? '');
        $totpSetup = null;
        if ($pendingSecret !== '' && !$security['totp_enabled']) {
            $totpSetup = [
                'secret' => $pendingSecret,
                'uri' => Totp::provisioningUri($pendingSecret, (string) $user['login']),
            ];
        }

        Theme::render('user/panel', [
            'authUser' => $user,
            'account' => $dashboard['account'],
            'characters' => $dashboard['characters'],
            'primary' => $dashboard['primary'],
            'maxLevel' => $dashboard['max_level'],
            'characterCount' => $dashboard['character_count'],
            'totalYang' => $dashboard['total_yang'],
            'openTickets' => $dashboard['open_tickets'],
            'security' => $security,
            'totpSetup' => $totpSetup,
            'activityLogs' => ActivityLogService::forAccount($accountId, 50),
            'activeBan' => PenaltyService::getActiveBan($accountId),
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
            'panelErrors' => Session::flash('panel_errors') ?? [],
            'panelSuccess' => Session::flash('panel_success'),
            'panelSection' => Session::flash('panel_section')
                ?? (isset($_GET['section']) ? (string) $_GET['section'] : null)
                ?? ((int) ($_GET['ticket'] ?? 0) > 0 ? 'destek' : null)
                ?? ($searchQuery !== '' ? 'ozet' : null),
            'ticketCategories' => TicketService::categories(true),
            'userTickets' => TicketService::forAccount($accountId, 50),
            'ticketFileTypes' => TicketService::allowedFileTypes(true),
            'announcements' => AnnouncementService::list(true, 40),
            'overviewAnnouncements' => AnnouncementService::list(true, 5),
        ]);
    }

    public function character(): void
    {
        $user = AuthService::requireLogin();
        $accountId = (int) $user['account_id'];
        $id = (int) ($_GET['id'] ?? 0);

        $character = PlayerService::getOwnedCharacter($accountId, $id);
        if ($character === null) {
            Session::flash('panel_errors', ['Karakter bulunamadı veya bu hesaba ait değil.']);
            redirect('/panel');
        }

        Theme::render('user/character', [
            'authUser' => $user,
            'character' => $character,
            'maxLevel' => (int) $character['max_level'],
        ]);
    }

    public function changePassword(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::changePassword(
            (int) $user['account_id'],
            (string) ($_POST['current_password'] ?? ''),
            (string) ($_POST['new_password'] ?? ''),
            (string) ($_POST['new_password_confirm'] ?? '')
        );

        $this->flashResult($result, 'Parola güncellendi.', 'guvenlik');
        redirect('/panel');
    }

    public function changeSecurityCode(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::changeSecurityCode(
            (int) $user['account_id'],
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['new_securitycode'] ?? ''),
            (string) ($_POST['new_securitycode_confirm'] ?? '')
        );

        $this->flashResult($result, 'Depo / güvenli şifre güncellendi.', 'guvenlik');
        redirect('/panel');
    }

    public function startTotp(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::enableTotp((int) $user['account_id']);
        if ($result['ok'] && !empty($result['secret'])) {
            Session::set('totp_setup_secret', $result['secret']);
            Session::flash('panel_success', '2FA kurulumu başlatıldı. Uygulamadaki kodu girerek onayla.');
        } else {
            Session::flash('panel_errors', $result['errors'] ?: ['2FA başlatılamadı.']);
        }
        Session::flash('panel_section', 'guvenlik');
        redirect('/panel');
    }

    public function confirmTotp(): void
    {
        $user = $this->requirePanelCsrf();
        $accountId = (int) $user['account_id'];
        $result = AccountSecurityService::confirmTotp($accountId, (string) ($_POST['code'] ?? ''));
        if ($result['ok']) {
            Session::forget('totp_setup_secret');
        }
        $this->flashResult($result, 'İki adımlı doğrulama aktif.', 'guvenlik');
        redirect('/panel');
    }

    public function disableTotp(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::disableTotp(
            (int) $user['account_id'],
            (string) ($_POST['password'] ?? '')
        );
        if ($result['ok']) {
            Session::forget('totp_setup_secret');
        }
        $this->flashResult($result, 'İki adımlı doğrulama kapatıldı.', 'guvenlik');
        redirect('/panel');
    }

    public function setIpLock(): void
    {
        $user = $this->requirePanelCsrf();
        $enabled = ((string) ($_POST['enabled'] ?? '')) === '1';
        $result = AccountSecurityService::setIpLock((int) $user['account_id'], $enabled);
        $msg = $enabled
            ? 'IP kilidi açıldı. Giriş yalnızca bu IP ile mümkün.'
            : 'IP kilidi kapatıldı.';
        $this->flashResult($result, $msg, 'guvenlik');
        redirect('/panel');
    }

    public function setLoginNotify(): void
    {
        $user = $this->requirePanelCsrf();
        $enabled = ((string) ($_POST['enabled'] ?? '')) === '1';
        $result = AccountSecurityService::setLoginNotify((int) $user['account_id'], $enabled);
        $msg = $enabled ? 'Giriş bildirimleri açıldı.' : 'Giriş bildirimleri kapatıldı.';
        $this->flashResult($result, $msg, 'guvenlik');
        redirect('/panel');
    }

    /** @return array{account_id:int, login:string, permission:int} */
    private function requirePanelCsrf(): array
    {
        $user = AuthService::requireLogin();
        $name = (string) (\App\Core\Config::get('security.csrf_token_name', 'csrf_token'));
        $token = $_POST[$name] ?? null;
        if (!Security::validateCsrf(is_string($token) ? $token : null)) {
            Session::flash('panel_errors', ['Oturum doğrulaması başarısız. Lütfen tekrar dene.']);
            Session::flash('panel_section', 'guvenlik');
            redirect('/panel');
        }
        return $user;
    }

    /** @param array{ok:bool, errors:list<string>} $result */
    private function flashResult(array $result, string $success, string $section): void
    {
        Session::flash('panel_section', $section);
        if ($result['ok']) {
            Session::flash('panel_success', $success);
            return;
        }
        Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['İşlem başarısız.']);
    }
}
