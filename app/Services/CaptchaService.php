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
        $siteKeyJson = json_encode($cfg['site_key'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        $providerJson = json_encode($cfg['provider'], JSON_UNESCAPED_UNICODE);

        if ($cfg['provider'] === self::PROVIDER_CLOUDFLARE) {
            // explicit: gizli (display:none) modallarda otomatik render çalışmaz
            $script = self::CF_SCRIPT . '?render=explicit';
            $boot = <<<JS
<script>
window.M2DN_CAPTCHA = { provider: {$providerJson}, siteKey: {$siteKeyJson} };
(function () {
  function isVisible(el) {
    if (!el || !el.isConnected) return false;
    var overlay = el.closest('.modal-overlay');
    if (overlay && !overlay.classList.contains('open')) return false;
    return true;
  }
  function mountEl(el) {
    if (!el || !window.turnstile || !window.M2DN_CAPTCHA || !window.M2DN_CAPTCHA.siteKey) return;
    if (!isVisible(el)) return;
    if (el.getAttribute('data-widget-id')) {
      try { window.turnstile.reset(el.getAttribute('data-widget-id')); } catch (e) {}
      return;
    }
    try {
      var id = window.turnstile.render(el, {
        sitekey: window.M2DN_CAPTCHA.siteKey,
        theme: 'dark',
        appearance: 'always'
      });
      if (id) el.setAttribute('data-widget-id', id);
    } catch (e) {}
  }
  function refresh(root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('[data-captcha-mount]').forEach(mountEl);
  }
  window.m2dnCaptchaRefresh = refresh;
  function onReady() {
    refresh(document);
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
      if (typeof MutationObserver === 'undefined') return;
      new MutationObserver(function () {
        if (modal.classList.contains('open')) {
          setTimeout(function () { refresh(modal); }, 30);
        }
      }).observe(modal, { attributes: true, attributeFilter: ['class'] });
    });
  }
  function waitTurnstile(n) {
    if (window.turnstile && typeof window.turnstile.render === 'function') {
      onReady();
      return;
    }
    if ((n || 0) > 80) return;
    setTimeout(function () { waitTurnstile((n || 0) + 1); }, 50);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { waitTurnstile(0); });
  } else {
    waitTurnstile(0);
  }
})();
</script>
JS;
            return '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '" async defer></script>' . "\n" . $boot;
        }

        $script = self::GOOGLE_SCRIPT;
        $boot = <<<JS
<script>
window.M2DN_CAPTCHA = { provider: {$providerJson}, siteKey: {$siteKeyJson} };
window.m2dnCaptchaRefresh = function (root) {
  if (!window.grecaptcha || !window.M2DN_CAPTCHA) return;
  var scope = root && root.querySelectorAll ? root : document;
  scope.querySelectorAll('.g-recaptcha').forEach(function (el) {
    var overlay = el.closest('.modal-overlay');
    if (overlay && !overlay.classList.contains('open')) return;
    try {
      if (el.getAttribute('data-widget-id')) {
        window.grecaptcha.reset(Number(el.getAttribute('data-widget-id')));
        return;
      }
      if (el.innerHTML.trim() !== '') return;
      var id = window.grecaptcha.render(el, { sitekey: window.M2DN_CAPTCHA.siteKey, theme: 'dark' });
      el.setAttribute('data-widget-id', String(id));
    } catch (e) {}
  });
};
</script>
JS;

        return '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '" async defer></script>' . "\n" . $boot;
    }

    public static function widgetHtml(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        $cfg = self::config();
        if ($cfg['provider'] === self::PROVIDER_CLOUDFLARE) {
            // Mount noktası — render JS ile (gizli modal uyumlu)
            return '<div class="captcha-wrap"><div data-captcha-mount></div></div>';
        }
        $siteKey = htmlspecialchars($cfg['site_key'], ENT_QUOTES, 'UTF-8');

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
