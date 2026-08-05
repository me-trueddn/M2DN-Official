<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\Theme;
use App\Services\AnnouncementService;
use App\Services\AuthService;
use App\Services\SiteContentService;

final class HomeController
{
    public function index(): void
    {
        $footerLinks = SiteContentService::footerLinks();
        $groupedFooter = ['server' => [], 'community' => []];
        foreach ($footerLinks as $link) {
            $col = (string) ($link['column_key'] ?? 'community');
            if (!isset($groupedFooter[$col])) {
                $groupedFooter[$col] = [];
            }
            $groupedFooter[$col][] = $link;
        }

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
            'openForgot' => (bool) Session::flash('open_forgot'),
            'forgotErrors' => Session::flash('forgot_errors') ?? [],
            'forgotOld' => Session::flash('forgot_old') ?? [],
            'forgotSuccess' => Session::flash('forgot_success'),
            'authUser' => AuthService::user(),
            'siteFeatures' => SiteContentService::features(),
            'siteClasses' => SiteContentService::classes(),
            'siteDownloads' => SiteContentService::downloads(),
            'siteGallery' => SiteContentService::gallery(),
            'siteSocials' => SiteContentService::socialLinks(),
            'siteFooterLinks' => $groupedFooter,
            'siteFooter' => SiteContentService::footerMeta(),
            'nextChapter' => SiteContentService::nextChapter(),
            'homeAnnouncement' => AnnouncementService::list(true, 1)[0] ?? null,
        ]);
    }
}
