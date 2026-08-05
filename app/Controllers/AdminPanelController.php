<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AdminPlayerService;
use App\Services\AdminStatsService;
use App\Services\AuthService;
use App\Services\PenaltyService;
use App\Services\SiteContentService;

final class AdminPanelController
{
    public function index(): void
    {
        $user = AuthService::requireAdmin();
        PenaltyService::liftExpired();

        $stats = AdminStatsService::overview();

        $q = (string) ($_GET['q'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $page = (int) ($_GET['page'] ?? 1);
        $per = (int) ($_GET['per'] ?? 10);
        $players = AdminPlayerService::listAccounts($q, $status, $page, $per);

        $section = (string) ($_GET['section'] ?? 'ozet');
        if ($q !== '' || $status !== '' || isset($_GET['page']) || isset($_GET['per'])) {
            $section = 'oyuncular';
        }
        $allowed = [
            'ozet', 'oyuncular', 'banlar', 'duyurular', 'destekler', 'sunucu', 'loglar',
            'ceza-ayarlari', 'patch-linkleri', 'ozellikler-ayarlari', 'siniflar-ayarlari',
            'oranlar-ayarlari', 'siradaki-bolum', 'galeri-ayarlari', 'footer-ayarlari',
        ];
        if (!in_array($section, $allowed, true)) {
            $section = 'ozet';
        }

        $flashSection = Session::flash('panel_section');
        if (is_string($flashSection) && in_array($flashSection, $allowed, true)) {
            $section = $flashSection;
        }

        $chapter = SiteContentService::nextChapter();
        $chapterDt = $chapter['target_at'] !== '' ? strtotime($chapter['target_at']) : false;

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
        ]);
    }

    public function player(): void
    {
        $user = AuthService::requireAdmin();
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
        AuthService::requireAdmin();
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

    public function ban(): void
    {
        $user = AuthService::requireAdmin();
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

        $this->flashResult($result, 'Oyuncu banlandı. Oyuna giriş engellendi; panele girebilir.', 'oyuncular');
        redirect('/admin?section=oyuncular');
    }

    public function unban(): void
    {
        $user = AuthService::requireAdmin();
        Security::requireCsrf('login');

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $reason = (string) ($_POST['reason'] ?? '');
        $result = PenaltyService::unbanAccount(
            $accountId,
            $reason,
            ['account_id' => (int) $user['account_id'], 'login' => (string) $user['login']]
        );

        $back = (string) ($_POST['redirect_section'] ?? 'oyuncular');
        if (!in_array($back, ['oyuncular', 'banlar'], true)) {
            $back = 'oyuncular';
        }

        $this->flashResult($result, 'Ban kaldırıldı.', $back);
        redirect('/admin?section=' . $back);
    }

    public function savePenalty(): void
    {
        AuthService::requireAdmin();
        Security::requireCsrf('login');

        $idRaw = (string) ($_POST['id'] ?? '');
        $id = $idRaw !== '' ? (int) $idRaw : null;
        if ($id !== null && $id <= 0) {
            $id = null;
        }

        $result = PenaltyService::saveTemplate(
            $id,
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['reason'] ?? ''),
            (int) ($_POST['days'] ?? 0)
        );

        $this->flashResult($result, 'Ceza şablonu kaydedildi.', 'ceza-ayarlari');
        redirect('/admin?section=ceza-ayarlari');
    }

    public function deletePenalty(): void
    {
        AuthService::requireAdmin();
        Security::requireCsrf('login');

        $result = PenaltyService::deleteTemplate((int) ($_POST['id'] ?? 0));
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
