<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AdminLogService;
use App\Services\AdminPlayerService;
use App\Services\AdminStatsService;
use App\Services\AnnouncementService;
use App\Services\AuthService;
use App\Services\CaptchaService;
use App\Services\CommunityRulesService;
use App\Services\LegalContentService;
use App\Services\MailService;
use App\Services\PenaltyService;
use App\Services\PermissionService;
use App\Services\PasswordResetService;
use App\Services\SiteContentService;
use App\Services\TicketService;

final class AdminPanelController
{
    public function index(): void
    {
        $user = AuthService::requireAdmin();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        PenaltyService::liftExpired();

        $stats = AdminStatsService::overview();

        $q = (string) ($_GET['q'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $page = (int) ($_GET['page'] ?? 1);
        $per = (int) ($_GET['per'] ?? 10);
        $players = AdminPlayerService::listAccounts($q, $status, $page, $per);
        $ticketQ = trim((string) ($_GET['ticket_q'] ?? ''));
        $logQ = trim((string) ($_GET['log_q'] ?? ''));
        $logPage = (int) ($_GET['log_page'] ?? 1);
        $mailQ = trim((string) ($_GET['mail_q'] ?? ''));

        $section = (string) ($_GET['section'] ?? 'ozet');
        if ($ticketQ !== '' || isset($_GET['ticket'])) {
            $section = 'destekler';
        } elseif ($logQ !== '' || isset($_GET['log_page']) || $section === 'loglar') {
            if (isset($_GET['log_q']) || isset($_GET['log_page'])) {
                $section = 'loglar';
            }
        } elseif ($q !== '' || $status !== '' || isset($_GET['page']) || isset($_GET['per'])) {
            $section = 'oyuncular';
        } elseif ($mailQ !== '' || isset($_GET['mail_tab'])) {
            $section = 'mail-ayarlari';
        }
        $allowed = [
            'ozet', 'oyuncular', 'banlar', 'duyurular', 'destekler', 'sunucu', 'loglar',
            'ceza-ayarlari', 'patch-linkleri', 'ozellikler-ayarlari', 'siniflar-ayarlari',
            'oranlar-ayarlari', 'siradaki-bolum', 'galeri-ayarlari', 'footer-ayarlari',
            'logo-ayarlari', 'mail-ayarlari', 'yetki-gruplari', 'ticket-ayarlari', 'duyuru-turleri',
            'kurallar-ayarlari', 'captcha-ayarlari', 'gizlilik-ayarlari',
        ];
        if (!in_array($section, $allowed, true)) {
            $section = 'ozet';
        }

        $flashSection = Session::flash('panel_section');
        if (is_string($flashSection) && in_array($flashSection, $allowed, true)) {
            $section = $flashSection;
        }

        $permFlags = [];
        foreach (array_keys(PermissionService::flagDefinitions()) as $flag) {
            $permFlags[$flag] = PermissionService::userHasFlag($user, $flag);
        }

        $settingsSections = [
            'ceza-ayarlari', 'patch-linkleri', 'ozellikler-ayarlari', 'siniflar-ayarlari',
            'oranlar-ayarlari', 'siradaki-bolum', 'galeri-ayarlari', 'footer-ayarlari',
            'logo-ayarlari', 'mail-ayarlari', 'yetki-gruplari', 'ticket-ayarlari', 'duyuru-turleri',
            'kurallar-ayarlari', 'captcha-ayarlari', 'gizlilik-ayarlari',
        ];
        if (in_array($section, $settingsSections, true) && empty($permFlags[PermissionService::FLAG_SITE_SETTINGS])) {
            Session::flash('panel_errors', ['Ayarlara erişim yetkin yok.']);
            $section = 'ozet';
        }

        $menuGate = [
            'oyuncular' => PermissionService::FLAG_MENU_OYUNCULAR,
            'banlar' => PermissionService::FLAG_MENU_BANLAR,
            'duyurular' => PermissionService::FLAG_MENU_DUYURULAR,
            'destekler' => PermissionService::FLAG_MENU_DESTEKLER,
            'sunucu' => PermissionService::FLAG_MENU_SUNUCU,
            'loglar' => PermissionService::FLAG_MENU_LOGLAR,
        ];
        if ($section === 'duyurular'
            && empty($permFlags[PermissionService::FLAG_MENU_DUYURULAR])
            && empty($permFlags[PermissionService::FLAG_ANNOUNCEMENTS])
        ) {
            Session::flash('panel_errors', ['Duyuru yetkin yok.']);
            $section = 'ozet';
        } elseif (isset($menuGate[$section]) && $section !== 'duyurular' && empty($permFlags[$menuGate[$section]])) {
            Session::flash('panel_errors', ['Bu menüye erişim yetkin yok.']);
            $section = 'ozet';
        }

        $chapter = SiteContentService::nextChapter();
        $chapterDt = $chapter['target_at'] !== '' ? strtotime($chapter['target_at']) : false;

        $ticketId = (int) ($_GET['ticket'] ?? 0);
        $activeTicket = null;
        if ($ticketId > 0 && !empty($permFlags[PermissionService::FLAG_TICKETS])) {
            $activeTicket = TicketService::getTicket($ticketId);
        }

        $adminLogs = ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 10, 'filter' => ''];
        if (!empty($permFlags[PermissionService::FLAG_MENU_LOGLAR])) {
            $adminLogs = AdminLogService::list($logQ, $logPage, AdminLogService::PER_PAGE);
        }

        Theme::render('admin/panel', [
            'authUser' => $user,
            'stats' => $stats,
            'players' => $players,
            'panelSection' => $section,
            'penalties' => PenaltyService::listTemplates(),
            'activeBans' => PenaltyService::listActiveBans(100),
            'panelErrors' => Session::flash('panel_errors') ?? [],
            'panelSuccess' => Session::flash('panel_success'),
            'siteDownloads' => SiteContentService::downloads(false),
            'siteFeatures' => SiteContentService::features(false),
            'siteClasses' => SiteContentService::classes(false),
            'siteGallery' => SiteContentService::gallery(false),
            'siteFooterLinks' => SiteContentService::footerLinks(false),
            'siteSocials' => SiteContentService::socialLinks(false),
            'siteFooter' => SiteContentService::footerMeta(),
            'siteRates' => SiteContentService::rates(),
            'siteChapter' => [
                'title' => $chapter['title'],
                'date' => $chapterDt ? date('Y-m-d', $chapterDt) : '',
                'time' => $chapterDt ? date('H:i', $chapterDt) : '20:00',
            ],
            'permFlags' => $permFlags,
            'permFlagDefs' => PermissionService::flagDefinitions(),
            'permissionGroups' => PermissionService::listGroups(),
            'ticketCategories' => TicketService::categories(false),
            'ticketStatuses' => TicketService::statuses(false),
            'ticketFileTypes' => TicketService::allowedFileTypes(false),
            'adminTickets' => !empty($permFlags[PermissionService::FLAG_TICKETS])
                ? TicketService::listAll(100, $ticketQ)
                : [],
            'ticketSearch' => $ticketQ,
            'activeTicket' => $activeTicket,
            'adminLogs' => $adminLogs,
            'announcementTypes' => AnnouncementService::types(false),
            'announcementTypesActive' => AnnouncementService::types(true),
            'announcements' => AnnouncementService::list(false, 80),
            'overviewAnnouncements' => AnnouncementService::list(true, 8),
            'openTicketCount' => TicketService::openCountAll(),
            'mailServers' => !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]) ? MailService::servers() : [],
            'mailPresets' => MailService::presets(),
            'mailTemplates' => !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]) ? MailService::templates() : [],
            'mailLogs' => !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]) ? MailService::logs(10, $mailQ) : [],
            'mailLogSearch' => $mailQ,
            'communityRules' => !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS])
                ? CommunityRulesService::list(false)
                : [],
            'captchaConfig' => CaptchaService::config(),
            'privacyTitle' => LegalContentService::privacyTitle(),
            'privacyHtml' => LegalContentService::privacyHtml(),
            'mailTab' => (static function () use ($mailQ): string {
                $flash = Session::flash('mail_tab');
                if (is_string($flash) && $flash !== '') {
                    return $flash;
                }
                $get = trim((string) ($_GET['mail_tab'] ?? ''));
                if ($mailQ !== '' || $get === 'loglar') {
                    return 'loglar';
                }
                return in_array($get, ['sunucu', 'bildirimler', 'test', 'loglar'], true) ? $get : 'sunucu';
            })(),
            'authPermission' => AuthService::normalizePermission($user['permission'] ?? 0),
        ]);
    }

    public function player(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_PLAYER_DETAIL);
        $id = (int) ($_GET['id'] ?? 0);
        $detail = AdminPlayerService::accountDetail($id);
        if ($detail === null) {
            Session::flash('panel_errors', ['Oyuncu bulunamadı.']);
            Session::flash('panel_section', 'oyuncular');
            redirect('/admin?section=oyuncular');
        }

        Theme::render('admin/player', [
            'authUser' => $user,
            'account' => $detail['account'],
            'characters' => $detail['characters'],
            'activity' => $detail['activity'],
            'gameLogins' => $detail['game_logins'],
            'security' => $detail['security'],
            'activeBan' => $detail['active_ban'] ?? null,
        ]);
    }

    public function playerJson(): void
    {
        PermissionService::requireFlag(PermissionService::FLAG_PLAYER_DETAIL);
        $id = (int) ($_GET['id'] ?? 0);
        $detail = AdminPlayerService::accountDetail($id);
        header('Content-Type: application/json; charset=utf-8');
        if ($detail === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Oyuncu bulunamadı.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['ok' => true, 'data' => $detail], JSON_UNESCAPED_UNICODE);
    }

    public function playerSearch(): void
    {
        $user = AuthService::requireAdmin();
        header('Content-Type: application/json; charset=utf-8');
        $canSearch = PermissionService::userHasFlag($user, PermissionService::FLAG_PLAYER_DETAIL)
            || PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_OYUNCULAR);
        if (!$canSearch) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Yetkin yok.', 'results' => []], JSON_UNESCAPED_UNICODE);
            return;
        }
        $q = trim((string) ($_GET['q'] ?? ''));
        $results = AdminPlayerService::searchSuggest($q, 12);
        echo json_encode(['ok' => true, 'results' => $results, 'q' => $q], JSON_UNESCAPED_UNICODE);
    }

    public function changeEmail(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_PLAYER_DETAIL);
        Security::requireCsrf('login');
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $email = (string) ($_POST['email'] ?? '');
        $result = PasswordResetService::changeEmail($accountId, $email, $user);
        $this->flashResult($result, 'E-posta güncellendi.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function sendPasswordReset(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_PLAYER_DETAIL);
        Security::requireCsrf('login');
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $result = PasswordResetService::adminSendLink($accountId, [
            'account_id' => (int) $user['account_id'],
            'login' => (string) $user['login'],
            'permission' => AuthService::normalizePermission($user['permission'] ?? 0),
        ]);
        $this->flashResult($result, 'Şifre sıfırlama bağlantısı gönderildi.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function setPassword(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_PLAYER_DETAIL);
        Security::requireCsrf('login');
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        $result = PasswordResetService::adminSetPassword($accountId, $password, [
            'account_id' => (int) $user['account_id'],
            'login' => (string) $user['login'],
            'permission' => AuthService::normalizePermission($user['permission'] ?? 0),
        ]);
        $this->flashResult($result, 'Şifre güncellendi.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function ban(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_BAN);
        Security::requireCsrf('login');

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $penaltyId = (int) ($_POST['penalty_id'] ?? 0);
        $evidence = (string) ($_POST['evidence'] ?? '');

        $result = PenaltyService::banAccount(
            $accountId,
            $penaltyId,
            $evidence,
            ['account_id' => (int) $user['account_id'], 'login' => (string) $user['login']]
        );

        if (!empty($result['ok'])) {
            $login = (string) ($_POST['account_login'] ?? '');
            AdminLogService::write($user, 'Ban', 'Ceza #' . $penaltyId . ($evidence !== '' ? ' · Kanıt: ' . $evidence : ''), $accountId, $login !== '' ? $login : null);
        }

        $this->flashResult($result, 'Oyuncu banlandı. Oyuna giriş engellendi; panele girebilir.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function unban(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_BAN);
        Security::requireCsrf('login');

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $reason = (string) ($_POST['reason'] ?? '');
        $result = PenaltyService::unbanAccount(
            $accountId,
            $reason,
            ['account_id' => (int) $user['account_id'], 'login' => (string) $user['login']]
        );

        if (!empty($result['ok'])) {
            $login = (string) ($_POST['account_login'] ?? '');
            AdminLogService::write($user, 'Ban kaldırma', $reason, $accountId, $login !== '' ? $login : null);
        }

        $back = (string) ($_POST['redirect_section'] ?? 'oyuncular');
        if (!in_array($back, ['oyuncular', 'banlar'], true)) {
            $back = 'oyuncular';
        }

        $this->flashResult($result, 'Ban kaldırıldı.', $back);
        redirect('/admin?section=' . $back);
    }

    public function savePenalty(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');

        $idRaw = (string) ($_POST['id'] ?? '');
        $id = $idRaw !== '' ? (int) $idRaw : null;
        if ($id !== null && $id <= 0) {
            $id = null;
        }

        $name = (string) ($_POST['name'] ?? '');
        $result = PenaltyService::saveTemplate(
            $id,
            $name,
            (string) ($_POST['reason'] ?? ''),
            (int) ($_POST['days'] ?? 0)
        );

        if (!empty($result['ok'])) {
            AdminLogService::write($user, $id ? 'Ceza şablonu güncellendi' : 'Ceza şablonu eklendi', $name);
        }

        $this->flashResult($result, 'Ceza şablonu kaydedildi.', 'ceza-ayarlari');
        redirect('/admin?section=ceza-ayarlari');
    }

    public function deletePenalty(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_SITE_SETTINGS);
        Security::requireCsrf('login');

        $id = (int) ($_POST['id'] ?? 0);
        $result = PenaltyService::deleteTemplate($id);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'Ceza şablonu silindi', 'ID #' . $id);
        }
        $this->flashResult($result, 'Ceza şablonu silindi.', 'ceza-ayarlari');
        redirect('/admin?section=ceza-ayarlari');
    }

    /** @param array{ok:bool, errors:list<string>} $result */
    private function flashResult(array $result, string $success, string $section): void
    {
        Session::flash('panel_section', $section);
        if (!empty($result['ok'])) {
            Session::flash('panel_success', $success);
            return;
        }
        Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['İşlem başarısız.']);
    }
}
