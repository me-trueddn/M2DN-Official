<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Security;

/**
 * Google reCAPTCHA v2 + Cloudflare Turnstile.
 * Endpoint / script URL'leri sabittir; sadece key'ler ayarlardan gelir.
 */
final class CaptchaService
{
    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_CLOUDFLARE = 'cloudflare';

    private const GOOGLE_SCRIPT = 'https://www.google.com/recaptcha/api.js';
    private const GOOGLE_VERIFY = 'https://www.google.com/recaptcha/api/siteverify';
    private const CF_SCRIPT = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    private const CF_VERIFY = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @return array{
     *   enabled:bool,
     *   provider:string,
     *   site_key:string,
     *   secret_key:string,
     *   ready:bool,
     *   providers:array<string,array{label:string,script:string,verify:string}>
     * }
     */
    public static function config(): array
    {
        $provider = strtolower(trim((string) (SiteContentService::get('captcha', 'provider', self::PROVIDER_GOOGLE) ?? self::PROVIDER_GOOGLE)));
        if (!in_array($provider, [self::PROVIDER_GOOGLE, self::PROVIDER_CLOUDFLARE], true)) {
            $provider = self::PROVIDER_GOOGLE;
        }
        $enabled = ((string) (SiteContentService::get('captcha', 'enabled', '0') ?? '0')) === '1';
        $siteKey = trim((string) (SiteContentService::get('captcha', 'site_key', '') ?? ''));
        $secretKey = trim((string) (SiteContentService::get('captcha', 'secret_key', '') ?? ''));

        return [
            'enabled' => $enabled,
            'provider' => $provider,
            'site_key' => $siteKey,
            'secret_key' => $secretKey,
            'ready' => $enabled && $siteKey !== '' && $secretKey !== '',
            'providers' => [
                self::PROVIDER_GOOGLE => [
                    'label' => 'Google reCAPTCHA v2',
                    'script' => self::GOOGLE_SCRIPT,
                    'verify' => self::GOOGLE_VERIFY,
                ],
                self::PROVIDER_CLOUDFLARE => [
                    'label' => 'Cloudflare Turnstile',
                    'script' => self::CF_SCRIPT,
                    'verify' => self::CF_VERIFY,
                ],
            ],
        ];
    }

    public static function isEnabled(): bool
    {
        $cfg = self::config();
        return !empty($cfg['ready']);
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function save(bool $enabled, string $provider, string $siteKey, string $secretKey): array
    {
        $provider = strtolower(trim($provider));
        if (!in_array($provider, [self::PROVIDER_GOOGLE, self::PROVIDER_CLOUDFLARE], true)) {
            return ['ok' => false, 'errors' => ['Geçersiz captcha sağlayıcı.']];
        }
        $siteKey = trim($siteKey);
        $secretKey = trim($secretKey);
        if ($enabled && ($siteKey === '' || $secretKey === '')) {
            return ['ok' => false, 'errors' => ['Captcha aktifken Site Key ve Secret Key zorunlu.']];
        }

        SiteContentService::set('captcha', 'enabled', $enabled ? '1' : '0');
        SiteContentService::set('captcha', 'provider', $provider);
        SiteContentService::set('captcha', 'site_key', $siteKey);
        SiteContentService::set('captcha', 'secret_key', $secretKey);

        return ['ok' => true, 'errors' => []];
    }

    public static function scriptTags(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        $cfg = self::config();
        $script = $cfg['provider'] === self::PROVIDER_CLOUDFLARE ? self::CF_SCRIPT : self::GOOGLE_SCRIPT;
        $async = $cfg['provider'] === self::PROVIDER_CLOUDFLARE ? ' async defer' : ' async defer';

        return '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"' . $async . '></script>';
    }

    public static function widgetHtml(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        $cfg = self::config();
        $siteKey = htmlspecialchars($cfg['site_key'], ENT_QUOTES, 'UTF-8');
        if ($cfg['provider'] === self::PROVIDER_CLOUDFLARE) {
            return '<div class="captcha-wrap"><div class="cf-turnstile" data-sitekey="' . $siteKey . '" data-theme="dark"></div></div>';
        }

        return '<div class="captcha-wrap"><div class="g-recaptcha" data-sitekey="' . $siteKey . '" data-theme="dark"></div></div>';
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function verifyRequest(): array
    {
        if (!self::isEnabled()) {
            return ['ok' => true, 'errors' => []];
        }
        $cfg = self::config();
        $token = '';
        if ($cfg['provider'] === self::PROVIDER_CLOUDFLARE) {
            $token = trim((string) ($_POST['cf-turnstile-response'] ?? ''));
        } else {
            $token = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
        }
        return self::verifyToken($token);
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function verifyToken(string $token): array
    {
        if (!self::isEnabled()) {
            return ['ok' => true, 'errors' => []];
        }
        if ($token === '') {
            return ['ok' => false, 'errors' => ['Lütfen robot doğrulamasını tamamlayın.']];
        }

        $cfg = self::config();
        $endpoint = $cfg['provider'] === self::PROVIDER_CLOUDFLARE ? self::CF_VERIFY : self::GOOGLE_VERIFY;
        $payload = [
            'secret' => $cfg['secret_key'],
            'response' => $token,
            'remoteip' => Security::clientIp(),
        ];

        $raw = self::httpPost($endpoint, $payload);
        if ($raw === null) {
            return ['ok' => false, 'errors' => ['Doğrulama servisine ulaşılamadı. Tekrar deneyin.']];
        }
        $json = json_decode($raw, true);
        if (!is_array($json) || empty($json['success'])) {
            return ['ok' => false, 'errors' => ['Robot doğrulaması başarısız. Tekrar deneyin.']];
        }

        return ['ok' => true, 'errors' => []];
    }

    /** @param array<string,string> $fields */
    private static function httpPost(string $url, array $fields): ?string
    {
        $body = http_build_query($fields);
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            $out = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($out === false || $code < 200 || $code >= 300) {
                return null;
            }
            return (string) $out;
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);
        $out = @file_get_contents($url, false, $ctx);
        return $out === false ? null : (string) $out;
    }
}
