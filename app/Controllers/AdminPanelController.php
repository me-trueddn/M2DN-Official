<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Theme;
use App\Services\AuthService;

final class AdminPanelController
{
    public function index(): void
    {
        $user = AuthService::requireAdmin();

        Theme::render('admin/panel', [
            'authUser' => $user,
        ]);
    }
}
