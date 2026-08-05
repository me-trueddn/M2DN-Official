<?php

declare(strict_types=1);

use App\Controllers\AdminAccessController;
use App\Controllers\AdminAnnouncementController;
use App\Controllers\AdminMailController;
use App\Controllers\AdminPanelController;
use App\Controllers\AdminSiteController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\NotificationController;
use App\Controllers\PasswordResetController;
use App\Controllers\TicketController;
use App\Controllers\UserPanelController;
use App\Core\Router;
use App\Core\Security;
use App\Core\ServerManager;
use App\Services\AuthService;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index']);
$router->get('/panel', [UserPanelController::class, 'index']);
$router->get('/panel/karakter', [UserPanelController::class, 'character']);
$router->post('/panel/guvenlik/sifre', [UserPanelController::class, 'changePassword']);
$router->post('/panel/guvenlik/depo', [UserPanelController::class, 'changeSecurityCode']);
$router->post('/panel/guvenlik/2fa/baslat', [UserPanelController::class, 'startTotp']);
$router->post('/panel/guvenlik/2fa/onayla', [UserPanelController::class, 'confirmTotp']);
$router->post('/panel/guvenlik/2fa/kapat', [UserPanelController::class, 'disableTotp']);
$router->post('/panel/guvenlik/ip', [UserPanelController::class, 'setIpLock']);
$router->post('/panel/guvenlik/bildirim', [UserPanelController::class, 'setLoginNotify']);
$router->get('/admin', [AdminPanelController::class, 'index']);
$router->get('/admin/oyuncu', [AdminPanelController::class, 'player']);
$router->get('/admin/oyuncu/json', [AdminPanelController::class, 'playerJson']);
$router->get('/admin/oyuncu/ara', [AdminPanelController::class, 'playerSearch']);
$router->post('/admin/oyuncu/ban', [AdminPanelController::class, 'ban']);
$router->post('/admin/oyuncu/unban', [AdminPanelController::class, 'unban']);
$router->post('/admin/oyuncu/email', [AdminPanelController::class, 'changeEmail']);
$router->post('/admin/oyuncu/sifre-link', [AdminPanelController::class, 'sendPasswordReset']);
$router->post('/admin/oyuncu/sifre', [AdminPanelController::class, 'setPassword']);
$router->post('/admin/ayarlar/ceza/kaydet', [AdminPanelController::class, 'savePenalty']);
$router->post('/admin/ayarlar/ceza/sil', [AdminPanelController::class, 'deletePenalty']);

$router->post('/admin/ayarlar/oranlar', [AdminSiteController::class, 'saveRates']);
$router->post('/admin/ayarlar/bolum', [AdminSiteController::class, 'saveChapter']);
$router->post('/admin/ayarlar/footer-meta', [AdminSiteController::class, 'saveFooterMeta']);
$router->post('/admin/ayarlar/patch', [AdminSiteController::class, 'saveDownload']);
$router->post('/admin/ayarlar/patch/sil', [AdminSiteController::class, 'deleteDownload']);
$router->post('/admin/ayarlar/ozellik', [AdminSiteController::class, 'saveFeature']);
$router->post('/admin/ayarlar/sinif', [AdminSiteController::class, 'saveClass']);
$router->post('/admin/ayarlar/galeri', [AdminSiteController::class, 'saveGallery']);
$router->post('/admin/ayarlar/galeri/sil', [AdminSiteController::class, 'deleteGallery']);
$router->post('/admin/ayarlar/footer-link', [AdminSiteController::class, 'saveFooterLink']);
$router->post('/admin/ayarlar/footer-link/sil', [AdminSiteController::class, 'deleteFooterLink']);
$router->post('/admin/ayarlar/sosyal', [AdminSiteController::class, 'saveSocial']);
$router->post('/admin/ayarlar/sosyal/sil', [AdminSiteController::class, 'deleteSocial']);
$router->post('/admin/ayarlar/logo', [AdminSiteController::class, 'saveLogo']);
$router->post('/admin/ayarlar/mail', [AdminMailController::class, 'saveServer']);
$router->post('/admin/ayarlar/mail/sil', [AdminMailController::class, 'deleteServer']);
$router->post('/admin/ayarlar/mail/aktif', [AdminMailController::class, 'activateServer']);
$router->post('/admin/ayarlar/mail/sablon', [AdminMailController::class, 'saveTemplate']);
$router->post('/admin/ayarlar/mail/test', [AdminMailController::class, 'sendTest']);
$router->post('/admin/ayarlar/mail/tekrar', [AdminMailController::class, 'resend']);

$router->post('/admin/yetki/grup', [AdminAccessController::class, 'saveGroup']);
$router->post('/admin/yetki/grup/sil', [AdminAccessController::class, 'deleteGroup']);
$router->post('/admin/yetki/ata', [AdminAccessController::class, 'assignGroup']);
$router->post('/admin/ticket/kategori', [AdminAccessController::class, 'saveTicketCategory']);
$router->post('/admin/ticket/kategori/sil', [AdminAccessController::class, 'deleteTicketCategory']);
$router->post('/admin/ticket/durum', [AdminAccessController::class, 'saveTicketStatus']);
$router->post('/admin/ticket/dosya-turu', [AdminAccessController::class, 'saveTicketFileType']);
$router->post('/admin/ticket/dosya-turu/sil', [AdminAccessController::class, 'deleteTicketFileType']);
$router->post('/admin/ticket/dosya-turu/toggle', [AdminAccessController::class, 'toggleTicketFileType']);

$router->post('/admin/duyuru/kaydet', [AdminAnnouncementController::class, 'save']);
$router->post('/admin/duyuru/toggle', [AdminAnnouncementController::class, 'toggle']);
$router->post('/admin/duyuru/sil', [AdminAnnouncementController::class, 'delete']);
$router->post('/admin/duyuru-tur/kaydet', [AdminAnnouncementController::class, 'saveType']);
$router->post('/admin/duyuru-tur/sil', [AdminAnnouncementController::class, 'deleteType']);
$router->post('/admin/duyuru-tur/toggle', [AdminAnnouncementController::class, 'toggleType']);

$router->post('/panel/ticket', [TicketController::class, 'create']);
$router->post('/panel/ticket/yanit', [TicketController::class, 'replyUser']);
$router->get('/panel/ticket', [TicketController::class, 'viewUser']);
$router->post('/admin/ticket/yanit', [TicketController::class, 'replyAdmin']);
$router->post('/admin/ticket/kapat', [TicketController::class, 'closeAdmin']);
$router->get('/admin/ticket/json', [TicketController::class, 'adminDetailJson']);

$router->get('/bildirimler/json', [NotificationController::class, 'listJson']);
$router->post('/bildirimler/okundu', [NotificationController::class, 'markRead']);

$router->get('/kayit', [AuthController::class, 'showRegister']);
$router->post('/kayit', [AuthController::class, 'register']);
$router->get('/giris', [AuthController::class, 'showLogin']);
$router->post('/giris', [AuthController::class, 'login']);
$router->post('/giris/2fa', [AuthController::class, 'twoFactor']);
$router->post('/cikis', [AuthController::class, 'logout']);
$router->get('/cikis', [AuthController::class, 'logout']);
$router->post('/sifre-unuttum', [PasswordResetController::class, 'forgot']);
$router->get('/sifre-sifirla', [PasswordResetController::class, 'showReset']);
$router->post('/sifre-sifirla', [PasswordResetController::class, 'reset']);

$router->post('/server/select', static function (): void {
    AuthService::requireLogin();
    Security::requireCsrf('login');
    $key = $_POST['server'] ?? '';
    if (!is_string($key) || $key === '') {
        http_response_code(400);
        exit('Geçersiz istek.');
    }
    try {
        ServerManager::select($key);
    } catch (\InvalidArgumentException) {
        http_response_code(400);
        exit('Sunucu seçilemedi.');
    }
    $back = $_POST['redirect'] ?? '/panel';
    if (!is_string($back) || !str_starts_with($back, '/')) {
        $back = '/panel';
    }
    redirect($back);
});
