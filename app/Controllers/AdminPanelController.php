<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AdminBanwordService;
use App\Services\AdminGameLogService;
use App\Services\AdminGmService;
use App\Services\AdminGuildService;
use App\Services\AdminHorseService;
use App\Services\AdminIpBanService;
use App\Services\GuildWarService;
use App\Services\AdminLogService;
use App\Services\AdminPlayerService;
use App\Services\AdminRankingService;
use App\Services\AdminStatsService;
use App\Services\AnnouncementService;
use App\Services\AccountSecurityService;
use App\Services\AuthService;
use App\Services\CaptchaService;
use App\Services\CommunityRulesService;
use App\Services\LegalContentService;
use App\Services\MailService;
use App\Services\MarriageService;
use App\Services\PenaltyService;
use App\Services\PermissionService;
use App\Services\PasswordResetService;
use App\Services\SiteContentService;
use App\Services\TicketService;
use App\Services\WikiCategoryService;
use App\Services\WikiContentTypeService;
use App\Services\WikiPageService;

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
        $adminsOnly = isset($_GET['admins']) && (string) $_GET['admins'] === '1';
        $guildQ = trim((string) ($_GET['guild_q'] ?? ''));
        $guildPage = (int) ($_GET['guild_page'] ?? 1);
        $guildPer = (int) ($_GET['guild_per'] ?? 10);
        $horseQ = trim((string) ($_GET['horse_q'] ?? ''));
        $horsePage = (int) ($_GET['horse_page'] ?? 1);
        $horsePer = (int) ($_GET['horse_per'] ?? 10);
        $banwordQ = trim((string) ($_GET['banword_q'] ?? ''));
        $banwordPage = (int) ($_GET['banword_page'] ?? 1);
        $banwordPer = (int) ($_GET['banword_per'] ?? 20);
        $gmQ = trim((string) ($_GET['gm_q'] ?? ''));
        $gmPage = (int) ($_GET['gm_page'] ?? 1);
        $gmPer = (int) ($_GET['gm_per'] ?? 20);
        $rankQ = trim((string) ($_GET['rank_q'] ?? ''));
        $rankPage = (int) ($_GET['rank_page'] ?? 1);
        $rankPer = (int) ($_GET['rank_per'] ?? 10);
        $ipBanQ = trim((string) ($_GET['ipban_q'] ?? ''));
        $ipBanPage = (int) ($_GET['ipban_page'] ?? 1);
        $ipBanPer = (int) ($_GET['ipban_per'] ?? 20);
        $marketItemQ = trim((string) ($_GET['market_item_q'] ?? ''));
        $marketItemCat = (int) ($_GET['market_item_cat'] ?? 0);
        $marketSaleQ = trim((string) ($_GET['market_sale_q'] ?? ''));
        $marketSalePage = (int) ($_GET['market_sale_page'] ?? 1);
        $couponQ = trim((string) ($_GET['coupon_q'] ?? ''));
        $couponStatus = trim((string) ($_GET['coupon_status'] ?? ''));
        $couponCat = (int) ($_GET['coupon_cat'] ?? 0);
        $couponPage = (int) ($_GET['coupon_page'] ?? 1);
        $marriageQ = trim((string) ($_GET['marriage_q'] ?? ''));
        $marriagePage = (int) ($_GET['marriage_page'] ?? 1);
        $marriagePer = (int) ($_GET['marriage_per'] ?? 20);
        $players = AdminPlayerService::listAccounts($q, $status, $page, $per, null, $adminsOnly);
        $ticketQ = trim((string) ($_GET['ticket_q'] ?? ''));
        $logQ = trim((string) ($_GET['log_q'] ?? ''));
        $logPage = (int) ($_GET['log_page'] ?? 1);
        $logTab = trim((string) ($_GET['log_tab'] ?? 'yonetici'));
        $gameLogTable = trim((string) ($_GET['game_log'] ?? ''));
        $mailQ = trim((string) ($_GET['mail_q'] ?? ''));

        $section = (string) ($_GET['section'] ?? 'ozet');
        if ($ticketQ !== '' || isset($_GET['ticket'])) {
            $section = 'destekler';
        } elseif ($logQ !== '' || isset($_GET['log_page']) || isset($_GET['log_tab']) || isset($_GET['game_log']) || $section === 'loglar') {
            if (isset($_GET['log_q']) || isset($_GET['log_page']) || isset($_GET['log_tab']) || isset($_GET['game_log'])) {
                $section = 'loglar';
            }
        } elseif ($guildQ !== '' || isset($_GET['guild_page']) || isset($_GET['guild_per'])) {
            $section = 'loncalar';
        } elseif ($horseQ !== '' || isset($_GET['horse_page']) || isset($_GET['horse_per'])) {
            $section = 'binek';
        } elseif ($banwordQ !== '' || isset($_GET['banword_page']) || isset($_GET['banword_per'])) {
            $section = 'yasakli-kelimeler';
        } elseif ($gmQ !== '' || isset($_GET['gm_page']) || isset($_GET['gm_per'])) {
            $section = 'gm';
        } elseif ($rankQ !== '' || isset($_GET['rank_page']) || isset($_GET['rank_per'])) {
            $section = 'siralamalar';
        } elseif ($ipBanQ !== '' || isset($_GET['ipban_page']) || isset($_GET['ipban_per'])) {
            $section = 'ip-ban';
        } elseif ($marketItemQ !== '' || isset($_GET['market_item_cat']) || isset($_GET['market_item_q'])) {
            $section = 'nesne-market-urunler';
        } elseif ($marketSaleQ !== '' || isset($_GET['market_sale_page']) || isset($_GET['market_sale_q'])) {
            $section = 'nesne-market-satis-loglari';
        } elseif ($couponQ !== '' || $couponStatus !== '' || isset($_GET['coupon_cat']) || isset($_GET['coupon_page']) || isset($_GET['coupon_q'])) {
            $section = 'nesne-market-kuponlar';
        } elseif ($marriageQ !== '' || isset($_GET['marriage_page']) || isset($_GET['marriage_per'])) {
            $section = 'evlilikler';
        } elseif ($q !== '' || $status !== '' || $adminsOnly || isset($_GET['page']) || isset($_GET['per']) || isset($_GET['admins'])) {
            $section = 'oyuncular';
        } elseif ($mailQ !== '' || isset($_GET['mail_tab'])) {
            $section = 'mail-ayarlari';
        }
        $allowed = [
            'ozet', 'oyuncular', 'evlilikler', 'siralamalar', 'binek', 'gm', 'ip-ban', 'loncalar', 'lonca-savaslari', 'banlar', 'duyurular', 'destekler', 'sunucu', 'yasakli-kelimeler', 'loglar',
            'ceza-ayarlari', 'patch-linkleri', 'ozellikler-ayarlari', 'siniflar-ayarlari',
            'oranlar-ayarlari', 'siradaki-bolum', 'galeri-ayarlari', 'footer-ayarlari',
            'logo-ayarlari', 'mail-ayarlari', 'yetki-gruplari', 'ticket-ayarlari', 'duyuru-turleri',
            'kurallar-ayarlari', 'captcha-ayarlari', 'gizlilik-ayarlari', 'nesne-market-kategoriler', 'nesne-market-urunler', 'nesne-market-satis-loglari', 'nesne-market-kuponlar', 'wiki-kategoriler', 'wiki-icerik-tipleri', 'wiki-icerikler',
        ];
        if ($section === 'wiki-yonetim') {
            $section = 'wiki-kategoriler';
        }
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
            'evlilikler' => PermissionService::FLAG_MENU_OYUNCULAR,
            'siralamalar' => PermissionService::FLAG_MENU_SIRALAMALAR,
            'binek' => PermissionService::FLAG_MENU_BINEK,
            'gm' => PermissionService::FLAG_MENU_GM,
            'ip-ban' => PermissionService::FLAG_MENU_IP_BAN,
            'loncalar' => PermissionService::FLAG_MENU_LONCALAR,
            'lonca-savaslari' => PermissionService::FLAG_MENU_LONCA_SAVASLARI,
            'banlar' => PermissionService::FLAG_MENU_BANLAR,
            'duyurular' => PermissionService::FLAG_MENU_DUYURULAR,
            'destekler' => PermissionService::FLAG_MENU_DESTEKLER,
            'sunucu' => PermissionService::FLAG_MENU_SUNUCU,
            'yasakli-kelimeler' => PermissionService::FLAG_MENU_YASAKLI_KELIMELER,
            'loglar' => PermissionService::FLAG_MENU_LOGLAR,
            'nesne-market-kategoriler' => PermissionService::FLAG_MENU_NESNE_MARKET,
            'nesne-market-urunler' => PermissionService::FLAG_MENU_NESNE_MARKET,
            'nesne-market-satis-loglari' => PermissionService::FLAG_MENU_NESNE_MARKET,
            'nesne-market-kuponlar' => PermissionService::FLAG_MENU_NESNE_MARKET,
            'wiki-kategoriler' => PermissionService::FLAG_MENU_WIKI,
            'wiki-icerik-tipleri' => PermissionService::FLAG_MENU_WIKI,
            'wiki-icerikler' => PermissionService::FLAG_MENU_WIKI,
        ];
        if ($section === 'duyurular'
            && empty($permFlags[PermissionService::FLAG_MENU_DUYURULAR])
            && empty($permFlags[PermissionService::FLAG_ANNOUNCEMENTS])
        ) {
            Session::flash('panel_errors', ['Duyuru yetkin yok.']);
            $section = 'ozet';
        } elseif (in_array($section, ['nesne-market-kategoriler', 'nesne-market-urunler', 'nesne-market-satis-loglari', 'nesne-market-kuponlar'], true)
            && empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
            && empty($permFlags[PermissionService::FLAG_SITE_SETTINGS])
        ) {
            Session::flash('panel_errors', ['Nesne Market yetkin yok.']);
            $section = 'ozet';
        } elseif (in_array($section, ['wiki-kategoriler', 'wiki-icerik-tipleri', 'wiki-icerikler'], true)
            && empty($permFlags[PermissionService::FLAG_MENU_WIKI])
            && empty($permFlags[PermissionService::FLAG_WIKI_MANAGE])
        ) {
            Session::flash('panel_errors', ['Wiki yetkin yok.']);
            $section = 'ozet';
        } elseif (isset($menuGate[$section]) && $section !== 'duyurular'
            && !in_array($section, ['nesne-market-kategoriler', 'nesne-market-urunler', 'nesne-market-satis-loglari', 'nesne-market-kuponlar', 'wiki-kategoriler', 'wiki-icerik-tipleri', 'wiki-icerikler'], true)
            && empty($permFlags[$menuGate[$section]])
        ) {
            Session::flash('panel_errors', ['Bu menüye erişim yetkin yok.']);
            $section = 'ozet';
        }

        $guilds = ['guilds' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 10, 'q' => '', 'per_page_options' => AdminGuildService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_LONCALAR])) {
            $guilds = AdminGuildService::listGuilds($guildQ, $guildPage, $guildPer);
        }

        $horses = ['horses' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 10, 'q' => '', 'per_page_options' => AdminHorseService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_BINEK])) {
            $horses = AdminHorseService::listHorses($horseQ, $horsePage, $horsePer);
        }

        $banwords = ['words' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => '', 'per_page_options' => AdminBanwordService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_YASAKLI_KELIMELER])) {
            $banwords = AdminBanwordService::list($banwordQ, $banwordPage, $banwordPer);
        }

        $gms = ['gms' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => '', 'per_page_options' => AdminGmService::PER_PAGE_OPTIONS, 'authorities' => AdminGmService::AUTHORITY_LABELS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_GM])) {
            $gms = AdminGmService::list($gmQ, $gmPage, $gmPer);
        }

        $rankings = ['players' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 10, 'q' => '', 'per_page_options' => AdminRankingService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_SIRALAMALAR])) {
            $rankings = AdminRankingService::list($rankQ, $rankPage, $rankPer);
        }

        $ipBans = ['bans' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => '', 'per_page_options' => AdminIpBanService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_IP_BAN])) {
            $ipBans = AdminIpBanService::list($ipBanQ, $ipBanPage, $ipBanPer);
        }

        $marriages = ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => '', 'per_page_options' => MarriageService::PER_PAGE_OPTIONS];
        if (!empty($permFlags[PermissionService::FLAG_MENU_OYUNCULAR])) {
            $marriages = MarriageService::list($marriageQ, $marriagePage, $marriagePer);
        }

        $gameLogTables = [];
        $gameLogs = ['table' => '', 'label' => '', 'columns' => [], 'rows' => [], 'error' => null];
        if (!empty($permFlags[PermissionService::FLAG_MENU_LOGLAR])) {
            $gameLogTables = AdminGameLogService::availableTables();
            if ($gameLogTable === '' && $gameLogTables !== []) {
                $gameLogTable = (string) ($gameLogTables[0]['key'] ?? 'loginlog');
            }
            if ($gameLogTable !== '') {
                $gameLogs = AdminGameLogService::latest($gameLogTable);
            }
        }
        if (!in_array($logTab, ['yonetici', 'oyun'], true)) {
            $logTab = 'yonetici';
        }

        $guildWars = [];
        $guildWarHistory = [];
        $guildWarBoard = [];
        if (!empty($permFlags[PermissionService::FLAG_MENU_LONCA_SAVASLARI])) {
            $guildWars = GuildWarService::listActive();
            $guildWarHistory = GuildWarService::listHistory(40);
            $guildWarBoard = GuildWarService::leaderboard(30);
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
            'guilds' => $guilds,
            'horses' => $horses,
            'banwords' => $banwords,
            'gms' => $gms,
            'rankings' => $rankings,
            'ipBans' => $ipBans,
            'marriages' => $marriages,
            'gameLogTables' => $gameLogTables,
            'gameLogs' => $gameLogs,
            'logTab' => $logTab,
            'guildWars' => $guildWars,
            'guildWarHistory' => $guildWarHistory,
            'guildWarBoard' => $guildWarBoard,
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
            'wikiCategories' => (!empty($permFlags[PermissionService::FLAG_MENU_WIKI])
                || !empty($permFlags[PermissionService::FLAG_WIKI_MANAGE]))
                ? WikiCategoryService::list(false)
                : [],
            'wikiMainCategories' => (!empty($permFlags[PermissionService::FLAG_MENU_WIKI])
                || !empty($permFlags[PermissionService::FLAG_WIKI_MANAGE]))
                ? WikiCategoryService::listMains(false)
                : [],
            'wikiChildCategories' => (!empty($permFlags[PermissionService::FLAG_MENU_WIKI])
                || !empty($permFlags[PermissionService::FLAG_WIKI_MANAGE]))
                ? WikiCategoryService::listChildren(false)
                : [],
            'wikiContentTypes' => (!empty($permFlags[PermissionService::FLAG_MENU_WIKI])
                || !empty($permFlags[PermissionService::FLAG_WIKI_MANAGE]))
                ? WikiContentTypeService::list(false)
                : [],
            'wikiPages' => (!empty($permFlags[PermissionService::FLAG_MENU_WIKI])
                || !empty($permFlags[PermissionService::FLAG_WIKI_MANAGE]))
                ? WikiPageService::list(false)
                : [],
            'marketCategories' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketCategoryService::list(false)
                : [],
            'marketItems' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketItemService::list(false, $marketItemQ, $marketItemCat)
                : [],
            'marketItemNextSort' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketItemService::nextSortOrder()
                : 1,
            'marketItemQ' => $marketItemQ,
            'marketItemCat' => $marketItemCat,
            'marketSales' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketPurchaseService::salesLogs($marketSaleQ, $marketSalePage, 20)
                : ['logs' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => ''],
            'marketCouponCategories' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketCouponService::listCategories(false)
                : [],
            'marketCoupons' => (!empty($permFlags[PermissionService::FLAG_MENU_NESNE_MARKET])
                || !empty($permFlags[PermissionService::FLAG_SITE_SETTINGS]))
                ? \App\Services\MarketCouponService::listCoupons($couponQ, $couponStatus, $couponCat, $couponPage, 30)
                : ['coupons' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 30, 'q' => '', 'status' => '', 'category_id' => 0],
            'couponGeneratedCodes' => Session::flash('coupon_generated_codes') ?? [],
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
            'empireChanges' => $detail['empire_changes'] ?? [],
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
        $security = $detail['security'] ?? [];
        $secretSet = (string) ($security['totp_secret'] ?? '') !== '';
        unset($security['totp_secret']);
        $security['totp_secret_set'] = $secretSet;
        $detail['security'] = $security;
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

    public function guildJson(): void
    {
        $user = AuthService::requireAdmin();
        $canGuilds = PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_LONCALAR);
        $canWars = PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_LONCA_SAVASLARI);
        if (!$canGuilds && !$canWars) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Yetkin yok.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $id = (int) ($_GET['id'] ?? 0);
        $detail = AdminGuildService::guildDetail($id);
        header('Content-Type: application/json; charset=utf-8');
        if ($detail === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Lonca bulunamadı.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['ok' => true, 'data' => $detail], JSON_UNESCAPED_UNICODE);
    }

    public function renameGuild(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_LONCALAR);
        Security::requireCsrf('login');
        $guildId = (int) ($_POST['guild_id'] ?? 0);
        $name = (string) ($_POST['name'] ?? '');
        $result = AdminGuildService::rename($guildId, $name);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'Lonca adı değişti', 'Lonca #' . $guildId . ' → ' . trim($name));
        }
        $this->flashResult($result, 'Lonca adı güncellendi.', 'loncalar');
        redirect('/admin?section=loncalar');
    }

    public function renameHorse(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_BINEK);
        Security::requireCsrf('login');
        $playerId = (int) ($_POST['player_id'] ?? 0);
        $name = (string) ($_POST['name'] ?? '');
        $result = AdminHorseService::rename($playerId, $name);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'Binek adı değişti', 'Player #' . $playerId . ' → ' . trim($name));
        }
        $this->flashResult($result, 'At adı güncellendi.', 'binek');
        redirect('/admin?section=binek');
    }

    public function changeGuildMaster(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_LONCALAR);
        Security::requireCsrf('login');
        $guildId = (int) ($_POST['guild_id'] ?? 0);
        $masterPid = (int) ($_POST['master_pid'] ?? 0);
        $result = AdminGuildService::changeMaster($guildId, $masterPid);
        if (!empty($result['ok'])) {
            AdminLogService::write(
                $user,
                'Lonca ustası değişti',
                'Lonca #' . $guildId . ' · yeni usta pid=' . $masterPid
            );
        }
        $this->flashResult($result, 'Lonca ustası güncellendi.', 'loncalar');
        redirect('/admin?section=loncalar');
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

    public function setSecurityCode(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_RESET_SECURITY_CODE);
        Security::requireCsrf('login');
        $perm = AuthService::normalizePermission($user['permission'] ?? 0);
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $code = (string) ($_POST['securitycode'] ?? '');
        $result = AccountSecurityService::adminSetSecurityCode($accountId, $code, [
            'account_id' => (int) $user['account_id'],
            'login' => (string) $user['login'],
            'permission' => $perm,
        ]);
        $this->flashResult($result, 'Güvenlik kodu güncellendi.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function setSafeboxPassword(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_RESET_SAFEBOX);
        Security::requireCsrf('login');
        $perm = AuthService::normalizePermission($user['permission'] ?? 0);
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $code = (string) ($_POST['safebox_password'] ?? '');
        $result = AccountSecurityService::adminSetSafeboxPassword($accountId, $code, [
            'account_id' => (int) $user['account_id'],
            'login' => (string) $user['login'],
            'permission' => $perm,
        ]);
        $this->flashResult($result, 'Depo şifresi güncellendi.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function disable2fa(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_DISABLE_2FA);
        Security::requireCsrf('login');
        $perm = AuthService::normalizePermission($user['permission'] ?? 0);
        $accountId = (int) ($_POST['account_id'] ?? 0);
        $result = AccountSecurityService::adminDisableTotp($accountId, [
            'account_id' => (int) $user['account_id'],
            'login' => (string) $user['login'],
            'permission' => $perm,
        ]);
        $this->flashResult($result, '2FA kapatıldı.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function addBanword(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_YASAKLI_KELIMELER);
        Security::requireCsrf('login');
        $word = (string) ($_POST['word'] ?? '');
        $result = AdminBanwordService::add($word);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'Yasaklı kelime eklendi', trim(mb_strtolower($word)));
        }
        $this->flashResult($result, 'Yasaklı kelime eklendi.', 'yasakli-kelimeler');
        redirect('/admin?section=yasakli-kelimeler');
    }

    public function deleteBanword(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_YASAKLI_KELIMELER);
        Security::requireCsrf('login');
        $word = (string) ($_POST['word'] ?? '');
        $result = AdminBanwordService::delete($word);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'Yasaklı kelime silindi', trim($word));
        }
        $this->flashResult($result, 'Yasaklı kelime silindi.', 'yasakli-kelimeler');
        redirect('/admin?section=yasakli-kelimeler');
    }

    public function addGm(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_GM);
        Security::requireCsrf('login');
        $result = AdminGmService::add(
            (string) ($_POST['account'] ?? ''),
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['authority'] ?? 'PLAYER'),
            (string) ($_POST['contact_ip'] ?? ''),
            (string) ($_POST['server_ip'] ?? 'ALL')
        );
        if (!empty($result['ok'])) {
            AdminLogService::write(
                $user,
                'GM eklendi',
                trim((string) ($_POST['account'] ?? '')) . ' / ' . trim((string) ($_POST['name'] ?? ''))
            );
        }
        $this->flashResult($result, 'GM eklendi.', 'gm');
        redirect('/admin?section=gm');
    }

    public function updateGm(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_GM);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $result = AdminGmService::update(
            $id,
            (string) ($_POST['account'] ?? ''),
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['authority'] ?? 'PLAYER'),
            (string) ($_POST['contact_ip'] ?? ''),
            (string) ($_POST['server_ip'] ?? 'ALL')
        );
        if (!empty($result['ok'])) {
            AdminLogService::write(
                $user,
                'GM güncellendi',
                '#' . $id . ' · ' . trim((string) ($_POST['account'] ?? '')) . ' / ' . (string) ($_POST['authority'] ?? '')
            );
        }
        $this->flashResult($result, 'GM güncellendi.', 'gm');
        redirect('/admin?section=gm');
    }

    public function deleteGm(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_GM);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $result = AdminGmService::delete($id);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'GM silindi', 'mID #' . $id);
        }
        $this->flashResult($result, 'GM silindi.', 'gm');
        redirect('/admin?section=gm');
    }

    public function addIpBan(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_IP_BAN);
        Security::requireCsrf('login');
        $result = AdminIpBanService::add(
            (string) ($_POST['ip'] ?? ''),
            (string) ($_POST['reason'] ?? ''),
            (int) ($_POST['pcbang_id'] ?? AdminIpBanService::DEFAULT_PCBANG_ID),
            $user
        );
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'IP ban eklendi', trim((string) ($_POST['ip'] ?? '')));
        }
        $this->flashResult($result, 'IP eklendi.', 'ip-ban');
        redirect('/admin?section=ip-ban');
    }

    public function deleteIpBan(): void
    {
        $user = PermissionService::requireFlag(PermissionService::FLAG_MENU_IP_BAN);
        Security::requireCsrf('login');
        $id = (int) ($_POST['id'] ?? 0);
        $result = AdminIpBanService::delete($id);
        if (!empty($result['ok'])) {
            AdminLogService::write($user, 'IP ban silindi', 'pcbang_ip #' . $id);
        }
        $this->flashResult($result, 'IP silindi.', 'ip-ban');
        redirect('/admin?section=ip-ban');
    }

    public function divorce(): void
    {
        $user = AuthService::requireAdmin();
        if (!PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_OYUNCULAR)) {
            Session::flash('panel_errors', ['Bu işlem için yetkin yok.']);
            Session::flash('panel_section', 'ozet');
            redirect('/admin');
        }
        Security::requireCsrf('login');

        $pid1 = (int) ($_POST['pid1'] ?? 0);
        $pid2 = (int) ($_POST['pid2'] ?? 0);
        $result = MarriageService::divorce($pid1, $pid2);

        if (!empty($result['ok'])) {
            $n1 = (string) ($result['name1'] ?? ('#' . $pid1));
            $n2 = (string) ($result['name2'] ?? ('#' . $pid2));
            AdminLogService::write(
                $user,
                'Evlilik bitirildi',
                $n1 . ' ↔ ' . $n2,
                (int) ($result['account_id1'] ?? 0) ?: null,
                $n1
            );
            $this->flashResult(['ok' => true, 'errors' => []], 'Evlilik sonlandırıldı.', 'evlilikler');
        } else {
            $msg = (string) ($result['error'] ?? 'İşlem başarısız.');
            $this->flashResult(['ok' => false, 'errors' => [$msg]], '', 'evlilikler');
        }
        redirect('/admin?section=evlilikler');
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

    /** @param array{ok:bool, errors:list<string>, mail?:array{ok:bool, errors:list<string>}} $result */
    private function flashResult(array $result, string $success, string $section): void
    {
        Session::flash('panel_section', $section);
        if (!empty($result['ok'])) {
            $msg = $success;
            $mail = $result['mail'] ?? null;
            if (is_array($mail) && empty($mail['ok'])) {
                $mailErr = (string) (($mail['errors'][0] ?? '') ?: 'Ban bildirimi gönderilemedi.');
                $msg .= ' · Uyarı: ' . $mailErr . ' (Mail → Gönderim)';
            }
            Session::flash('panel_success', $msg);
            return;
        }
        Session::flash('panel_errors', $result['errors'] !== [] ? $result['errors'] : ['İşlem başarısız.']);
    }
}
