<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Security;

/**
 * Google reCAPTCHA v2 + Cloudflare Turnstile.
 * Widget’lar gizli (display:none) modallarda otomatik render olmaz —
 * her iki sağlayıcı da explicit mount + modal açılışında yenileme kullanır.
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
        $isCf = $cfg['provider'] === self::PROVIDER_CLOUDFLARE;

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

  function apiReady() {
    if (!window.M2DN_CAPTCHA) return false;
    if (window.M2DN_CAPTCHA.provider === 'cloudflare') {
      return !!(window.turnstile && typeof window.turnstile.render === 'function');
    }
    return !!(window.grecaptcha && typeof window.grecaptcha.render === 'function');
  }

  function mountEl(el) {
    if (!el || !window.M2DN_CAPTCHA || !window.M2DN_CAPTCHA.siteKey) return;
    if (!isVisible(el)) return;
    if (!apiReady()) return;

    var existing = el.getAttribute('data-widget-id');
    if (existing !== null && existing !== '') {
      try {
        if (window.M2DN_CAPTCHA.provider === 'cloudflare') {
          window.turnstile.reset(existing);
        } else {
          window.grecaptcha.reset(Number(existing));
        }
      } catch (e) {}
      return;
    }

    try {
      // Önceki başarısız denemeden kalan içeriği temizle
      el.innerHTML = '';
      var id;
      if (window.M2DN_CAPTCHA.provider === 'cloudflare') {
        id = window.turnstile.render(el, {
          sitekey: window.M2DN_CAPTCHA.siteKey,
          theme: 'dark',
          appearance: 'always'
        });
      } else {
        id = window.grecaptcha.render(el, {
          sitekey: window.M2DN_CAPTCHA.siteKey,
          theme: 'dark'
        });
      }
      if (id !== undefined && id !== null && id !== '') {
        el.setAttribute('data-widget-id', String(id));
      }
    } catch (e) {
      el.removeAttribute('data-widget-id');
    }
  }

  function refresh(root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('[data-captcha-mount]').forEach(mountEl);
  }

  function refreshSoon(root) {
    refresh(root);
    setTimeout(function () { refresh(root); }, 80);
    setTimeout(function () { refresh(root); }, 250);
    setTimeout(function () { refresh(root); }, 600);
    setTimeout(function () { refresh(root); }, 1200);
  }

  window.m2dnCaptchaRefresh = refreshSoon;

  function bindModals() {
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
      if (modal.getAttribute('data-captcha-bound') === '1') return;
      modal.setAttribute('data-captcha-bound', '1');
      if (typeof MutationObserver === 'undefined') return;
      new MutationObserver(function () {
        if (modal.classList.contains('open')) {
          refreshSoon(modal);
        }
      }).observe(modal, { attributes: true, attributeFilter: ['class'] });
    });
  }

  function onReady() {
    bindModals();
    refreshSoon(document);
  }

  function waitApi(n) {
    if (apiReady()) {
      if (window.M2DN_CAPTCHA.provider !== 'cloudflare' && window.grecaptcha.ready) {
        window.grecaptcha.ready(onReady);
      } else {
        onReady();
      }
      return;
    }
    if ((n || 0) > 100) return;
    setTimeout(function () { waitApi((n || 0) + 1); }, 50);
  }

  window.m2dnRecaptchaOnload = function () { waitApi(0); };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { waitApi(0); });
  } else {
    waitApi(0);
  }
})();
</script>
JS;

        if ($isCf) {
            $script = self::CF_SCRIPT . '?render=explicit';
            return $boot . "\n" . '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '" async defer></script>';
        }

        // onload callback boot'tan ÖNCE tanımlı olmalı
        $script = self::GOOGLE_SCRIPT . '?onload=m2dnRecaptchaOnload&render=explicit';
        return $boot . "\n" . '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '" async defer></script>';
    }

    public static function widgetHtml(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        // Boş mount — gizli modalda otomatik render yok; JS açılışta basar
        return '<div class="captcha-wrap"><div data-captcha-mount></div></div>';
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
