<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\AccountConsentService;
use App\Services\ActivityLogService;
use App\Services\AuthService;
use App\Services\LegalContentService;
use App\Services\SiteContentService;

final class PrivacyController
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

        Theme::render('pages/privacy', [
            'pageTitle' => LegalContentService::privacyTitle(),
            'pageHtml' => LegalContentService::privacyHtml(),
            'siteFooterLinks' => $groupedFooter,
            'siteSocials' => SiteContentService::socialLinks(true),
            'siteFooter' => SiteContentService::footerMeta(),
            'authUser' => AuthService::user(),
        ]);
    }

    public function accept(): void
    {
        $user = AuthService::requireLogin();
        Security::requireCsrf('login');
        $accountId = (int) ($user['account_id'] ?? 0);
        $ok = AccountConsentService::recordPrivacyAccepted($accountId);
        if ($ok) {
            ActivityLogService::log(
                $accountId,
                ActivityLogService::ACTION_PRIVACY_ACCEPT,
                'Gizlilik / KVKK kabul edildi (rev. ' . LegalContentService::privacyRevision() . ')',
                (string) ($user['login'] ?? '')
            );
            Session::flash('login_success', 'Gizlilik Sözleşmesi kabul edildi.');
        } else {
            Session::flash('login_errors', ['Gizlilik onayı kaydedilemedi. Lütfen tekrar dene.']);
        }
        $back = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        if ($back !== '' && str_contains($back, (string) ($_SERVER['HTTP_HOST'] ?? ''))) {
            redirect($this->pathFromUrl($back) ?: '/panel');
        }
        redirect('/panel');
    }

    public function decline(): void
    {
        Security::requireCsrf('login');
        $user = AuthService::user();
        if ($user !== null) {
            ActivityLogService::log(
                (int) ($user['account_id'] ?? 0),
                ActivityLogService::ACTION_PRIVACY_DECLINE,
                'Güncel gizlilik sözleşmesi reddedildi — oturum kapatıldı',
                (string) ($user['login'] ?? '')
            );
        }
        AuthService::logout();
        Session::flash('login_errors', ['Gizlilik Sözleşmesini kabul etmeden siteyi kullanamazsın. Oturum kapatıldı.']);
        Session::flash('open_login', true);
        redirect('/');
    }

    private function pathFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '';
        }
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query) && $query !== '') {
            $path .= '?' . $query;
        }
        return $path;
    }
}
