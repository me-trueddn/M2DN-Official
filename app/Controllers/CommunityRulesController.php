<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Theme;
use App\Services\CommunityRulesService;
use App\Services\SiteContentService;

final class CommunityRulesController
{
    public function index(): void
    {
        $footerLinks = SiteContentService::footerLinks(true);
        $groupedFooter = ['server' => [], 'community' => []];
        foreach ($footerLinks as $link) {
            $col = (string) ($link['column_key'] ?? 'community');
            if (!isset($groupedFooter[$col])) {
                $groupedFooter[$col] = [];
            }
            $groupedFooter[$col][] = $link;
        }

        Theme::render('pages/rules', [
            'rules' => CommunityRulesService::list(true),
            'siteFooterLinks' => $groupedFooter,
            'siteSocials' => SiteContentService::socialLinks(true),
            'siteFooter' => SiteContentService::footerMeta(),
        ]);
    }
}
