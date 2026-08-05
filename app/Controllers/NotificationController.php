<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Services\AuthService;
use App\Services\NotificationService;

final class NotificationController
{
    public function listJson(): void
    {
        $user = AuthService::requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        $accountId = (int) $user['account_id'];
        echo json_encode([
            'ok' => true,
            'unread' => NotificationService::unreadCount($accountId),
            'items' => NotificationService::forAccount($accountId, 30),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function markRead(): void
    {
        $user = AuthService::requireLogin();
        Security::requireCsrf('login');
        header('Content-Type: application/json; charset=utf-8');
        $accountId = (int) $user['account_id'];
        $idRaw = trim((string) ($_POST['id'] ?? $_GET['id'] ?? ''));
        $id = $idRaw !== '' ? (int) $idRaw : null;
        if ($id !== null && $id <= 0) {
            $id = null;
        }
        NotificationService::markRead($accountId, $id);
        echo json_encode([
            'ok' => true,
            'unread' => NotificationService::unreadCount($accountId),
        ], JSON_UNESCAPED_UNICODE);
    }
}
