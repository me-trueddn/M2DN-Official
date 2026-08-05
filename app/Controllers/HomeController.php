<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\Theme;
use App\Services\AuthService;

final class HomeController
{
    public function index(): void
    {
        Theme::render('home', [
            'registerErrors' => Session::flash('register_errors') ?? [],
            'registerOld' => Session::flash('register_old') ?? [],
            'registerSuccess' => Session::flash('register_success'),
            'openRegister' => (bool) Session::flash('open_register'),
            'loginErrors' => Session::flash('login_errors') ?? [],
            'loginOld' => Session::flash('login_old') ?? [],
            'loginSuccess' => Session::flash('login_success'),
            'openLogin' => (bool) Session::flash('open_login'),
            'open2fa' => (bool) Session::flash('open_2fa'),
            'authUser' => AuthService::user(),
        ]);
    }
}
