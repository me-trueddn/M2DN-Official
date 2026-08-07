<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Security;
use App\Core\Theme;
use App\Services\AuthService;
use App\Services\MarketCategoryService;
use App\Services\MarketPurchaseService;
use App\Services\NesneMarketService;
use App\Services\PlayerService;

final class NesneMarketController
{
    public function index(): void
    {
        if (!NesneMarketService::isEnabled()) {
            http_response_code(404);
            echo 'Nesne Market kapalı.';
            return;
        }

        $mode = strtolower(trim((string) ($_GET['mode'] ?? '')));
        if (isset($_GET['embed']) && (string) $_GET['embed'] === '1') {
            $mode = 'embed';
        }
        if (!in_array($mode, ['embed', 'ingame', 'web'], true)) {
            $mode = 'web';
        }

        $ingame = NesneMarketService::tryIngameAuth();
        if (!empty($ingame['ok'])) {
            $user = $ingame['user'];
            if ($mode === 'web') {
                $mode = 'ingame';
            }
        } else {
            if (!empty($ingame['errors'])) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo implode("\n", $ingame['errors']);
                return;
            }
            $user = AuthService::requireLogin();
        }

        $accountId = (int) $user['account_id'];
        $dashboard = PlayerService::dashboard($accountId);
        $characters = is_array($dashboard['characters'] ?? null) ? $dashboard['characters'] : [];
        if ($characters === []) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            Theme::renderModule('nesnemarket', 'index', [
                'authUser' => $user,
                'account' => $dashboard['account'],
                'marketItems' => [],
                'marketCategories' => [],
                'marketMode' => $mode,
                'marketAssetUrl' => NesneMarketService::assetUrl(),
                'marketBuyUrl' => url('/nesne-market/satin-al'),
                'csrfToken' => Security::csrfToken(),
                'csrfTokenName' => (string) Config::get('security.csrf_token_name', 'csrf_token'),
                'marketBlocked' => 'Oyun içi karakteriniz bulunmadığından Nesne Market açılamadı.',
            ]);
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        Theme::renderModule('nesnemarket', 'index', [
            'authUser' => $user,
            'account' => $dashboard['account'],
            'marketItems' => NesneMarketService::catalog(),
            'marketCategories' => MarketCategoryService::list(true),
            'marketMode' => $mode,
            'marketAssetUrl' => NesneMarketService::assetUrl(),
            'marketBuyUrl' => url('/nesne-market/satin-al'),
            'csrfToken' => Security::csrfToken(),
            'csrfTokenName' => (string) Config::get('security.csrf_token_name', 'csrf_token'),
            'marketBlocked' => null,
        ]);
    }

    public function buy(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (!NesneMarketService::isEnabled()) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'errors' => ['Nesne Market kapalı.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $csrfName = (string) Config::get('security.csrf_token_name', 'csrf_token');
        $token = $_POST[$csrfName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Security::validateCsrf(is_string($token) ? $token : null)) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'errors' => ['Oturum doğrulaması başarısız. Sayfayı yenile.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $user = AuthService::user();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'errors' => ['Giriş gerekli.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        $dashboard = PlayerService::dashboard((int) $user['account_id']);
        $characters = is_array($dashboard['characters'] ?? null) ? $dashboard['characters'] : [];
        if ($characters === []) {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'errors' => ['Oyun içi karakteriniz bulunmadığından Nesne Market açılamadı.'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = MarketPurchaseService::purchase(
            (int) $user['account_id'],
            (string) ($user['login'] ?? ''),
            $itemId,
            $ip
        );

        if (empty($result['ok'])) {
            http_response_code(422);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
