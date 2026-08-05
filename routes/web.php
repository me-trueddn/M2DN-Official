<?php

declare(strict_types=1);

use App\Controllers\AdminPanelController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
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
$router->post('/admin/oyuncu/ban', [AdminPanelController::class, 'ban']);
$router->post('/admin/oyuncu/unban', [AdminPanelController::class, 'unban']);
$router->post('/admin/ayarlar/ceza/kaydet', [AdminPanelController::class, 'savePenalty']);
$router->post('/admin/ayarlar/ceza/sil', [AdminPanelController::class, 'deletePenalty']);

$router->get('/kayit', [AuthController::class, 'showRegister']);
$router->post('/kayit', [AuthController::class, 'register']);
$router->get('/giris', [AuthController::class, 'showLogin']);
$router->post('/giris', [AuthController::class, 'login']);
$router->post('/giris/2fa', [AuthController::class, 'twoFactor']);
$router->post('/cikis', [AuthController::class, 'logout']);
$router->get('/cikis', [AuthController::class, 'logout']);

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
