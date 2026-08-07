<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Theme;
use App\Services\AuthService;
use App\Services\SiteContentService;
use App\Services\WikiService;

final class WikiController
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

        Theme::render('pages/wiki', [
            'authUser' => AuthService::user(),
            'wiki' => WikiService::content(),
            'siteSocials' => SiteContentService::socialLinks(),
            'siteFooterLinks' => $groupedFooter,
            'siteFooter' => SiteContentService::footerMeta(),
        ]);
    }
}
