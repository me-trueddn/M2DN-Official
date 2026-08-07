<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AccountSecurityService;
use App\Services\ActivityLogService;
use App\Services\AdminRankingService;
use App\Services\AnnouncementService;
use App\Services\AuthService;
use App\Services\GuildWarService;
use App\Services\MarketCouponService;
use App\Services\MarriageService;
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

        $logPage = max(1, (int) ($_GET['log_page'] ?? 1));
        $activityPage = ActivityLogService::forAccountPaged($accountId, $logPage, 10);

        $rankQ = trim((string) ($_GET['rank_q'] ?? ''));
        $rankPage = (int) ($_GET['rank_page'] ?? 1);
        $rankPer = (int) ($_GET['rank_per'] ?? 10);
        $rankings = AdminRankingService::list($rankQ, $rankPage, $rankPer);

        $pendingSecret = (string) ($security['totp_secret'] ?? '');
        $totpSetup = null;
        if ($pendingSecret !== '' && !$security['totp_enabled']) {
            $issuer = (string) (\App\Core\Config::get('app.name', 'M2DN') ?: 'M2DN');
            $totpSetup = [
                'secret' => $pendingSecret,
                'uri' => Totp::provisioningUri($pendingSecret, (string) $user['login'], $issuer),
            ];
        }

        $panelSection = Session::flash('panel_section')
            ?? (isset($_GET['section']) ? (string) $_GET['section'] : null)
            ?? ((int) ($_GET['ticket'] ?? 0) > 0 ? 'destek' : null)
            ?? ($searchQuery !== '' ? 'ozet' : null);
        if (isset($_GET['log_page'])) {
            $panelSection = 'kayitlar';
        } elseif ($rankQ !== '' || isset($_GET['rank_page']) || isset($_GET['rank_per'])) {
            $panelSection = 'siralamalar';
        }
        $allowedSections = [
            'ozet', 'duyurular', 'karakterler', 'evlilikler', 'kayitlar', 'lonca-savaslari', 'siralamalar', 'destek', 'guvenlik',
        ];
        if (!is_string($panelSection) || !in_array($panelSection, $allowedSections, true)) {
            $panelSection = 'ozet';
        }

        $marriageQ = trim((string) ($_GET['marriage_q'] ?? ''));
        $marriagePage = (int) ($_GET['marriage_page'] ?? 1);
        $marriagePer = (int) ($_GET['marriage_per'] ?? 20);
        if ($marriageQ !== '' || isset($_GET['marriage_page']) || isset($_GET['marriage_per'])) {
            $panelSection = 'evlilikler';
        }
        $marriages = MarriageService::list($marriageQ, $marriagePage, $marriagePer);

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
            'activityLogs' => $activityPage['rows'],
            'activityMeta' => $activityPage,
            'activityLogsModal' => ActivityLogService::forAccount($accountId, 50),
            'activeBan' => PenaltyService::getActiveBan($accountId),
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
            'panelErrors' => Session::flash('panel_errors') ?? [],
            'panelSuccess' => Session::flash('panel_success'),
            'panelSection' => $panelSection,
            'ticketCategories' => TicketService::categories(true),
            'userTickets' => TicketService::forAccount($accountId, 50),
            'ticketFileTypes' => TicketService::allowedFileTypes(true),
            'announcements' => AnnouncementService::list(true, 40),
            'overviewAnnouncements' => AnnouncementService::list(true, 5),
            'guildWars' => GuildWarService::listActive(),
            'guildWarHistory' => GuildWarService::listHistory(40),
            'guildWarBoard' => GuildWarService::leaderboard(30),
            'rankings' => $rankings,
            'marriages' => $marriages,
        ]);
    }

    /** Lonca savaş / lonca kartı (oyuncu paneli — salt okunur). */
    public function guildPublicJson(): void
    {
        AuthService::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        header('Content-Type: application/json; charset=utf-8');
        $card = GuildWarService::publicGuildCard($id);
        if ($card === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Lonca bulunamadı.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['ok' => true, 'data' => $card], JSON_UNESCAPED_UNICODE);
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

        $this->flashResult($result, 'Güvenlik kodu güncellendi.', 'guvenlik');
        redirect('/panel');
    }

    public function changeSafeboxPassword(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::changeSafeboxPassword(
            (int) $user['account_id'],
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['new_safebox_password'] ?? ''),
            (string) ($_POST['new_safebox_password_confirm'] ?? '')
        );

        $this->flashResult($result, 'Depo şifresi güncellendi.', 'guvenlik');
        redirect('/panel');
    }

    public function startTotp(): void
    {
        $user = $this->requirePanelCsrf();
        $result = AccountSecurityService::enableTotp((int) $user['account_id']);
        if ($result['ok'] && !empty($result['secret'])) {
            Session::set('totp_setup_secret', $result['secret']);
            Session::flash('panel_success', '2FA kurulumu başlatıldı. QR kodu tara veya anahtarı gir, ardından kodu onayla.');
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

    public function redeemCoupon(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $user = AuthService::user();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'errors' => ['Giriş gerekli.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $name = (string) (Config::get('security.csrf_token_name', 'csrf_token'));
        $token = $_POST[$name] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Security::validateCsrf(is_string($token) ? $token : null)) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'errors' => ['Oturum doğrulaması başarısız. Sayfayı yenile.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $code = (string) ($_POST['code'] ?? '');
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $result = MarketCouponService::redeem(
            (int) $user['account_id'],
            (string) ($user['login'] ?? ''),
            $code,
            $ip
        );
        if (empty($result['ok'])) {
            http_response_code(422);
        } else {
            $amount = (int) ($result['amount'] ?? 0);
            $result['message'] = number_format($amount, 0, ',', '.') . ' Elmas hesabına eklendi.';
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
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
