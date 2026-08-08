<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Theme;
use App\Services\AuthService;
use App\Services\SiteContentService;
use App\Services\WikiCategoryService;
use App\Services\WikiPageService;

final class WikiController
{
    public function index(): void
    {
        $home = WikiCategoryService::findWikiHome(true);
        if ($home !== null) {
            $slug = trim((string) ($home['slug'] ?? ''));
            if ($slug !== '') {
                redirect(WikiCategoryService::pagePath($slug));
            }
        }

        $this->renderWiki([
            'wikiMode' => 'index',
            'wikiCategory' => null,
            'wikiPage' => null,
            'wikiCurrentSlug' => '',
        ]);
    }

    public function show(string $slug = ''): void
    {
        $slug = trim($slug);
        // .html route param should not include extension; strip if present
        if (str_ends_with(mb_strtolower($slug), '.html')) {
            $slug = substr($slug, 0, -5);
        }

        $category = WikiCategoryService::findBySlug($slug, true);
        if ($category === null || !empty($category['is_main'])) {
            http_response_code(404);
            echo '404 — Wiki sayfası bulunamadı.';
            return;
        }

        $pages = WikiPageService::mapByCategory(true);
        $page = $pages[(int) $category['id']] ?? null;

        $this->renderWiki([
            'wikiMode' => 'page',
            'wikiCategory' => $category,
            'wikiPage' => $page,
            'wikiCurrentSlug' => (string) ($category['slug'] ?? $slug),
        ]);
    }

    /** @param array<string,mixed> $extra */
    private function renderWiki(array $extra): void
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

        Theme::render('pages/wiki', array_merge([
            'authUser' => AuthService::user(),
            'wikiCategories' => WikiCategoryService::tree(true),
            'wikiPagesByCategory' => WikiPageService::mapByCategory(true),
            'siteSocials' => SiteContentService::socialLinks(),
            'siteFooterLinks' => $groupedFooter,
            'siteFooter' => SiteContentService::footerMeta(),
        ], $extra));
    }
}
