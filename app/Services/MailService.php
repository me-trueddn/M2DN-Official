<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Security;

/**
 * SMTP mail sunucuları, şablonlar ve gönderim (PHPMailer yok — native SMTP).
 */
final class MailService
{
    private const SMTP_CONNECT_TIMEOUT = 8;
    private const SMTP_IO_TIMEOUT = 8;
    private const SMTP_TOTAL_DEADLINE = 15;

    /** @return array<string, array{label:string, host:string, port:int, encryption:string}> */
    public static function presets(): array
    {
        return [
            'gmail' => ['label' => 'Google (Gmail)', 'host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'],
            'microsoft' => ['label' => 'Microsoft 365 / Outlook', 'host' => 'smtp.office365.com', 'port' => 587, 'encryption' => 'tls'],
            'yandex' => ['label' => 'Yandex', 'host' => 'smtp.yandex.com', 'port' => 465, 'encryption' => 'ssl'],
            'custom' => ['label' => 'Özel sunucu', 'host' => '', 'port' => 587, 'encryption' => 'tls'],
        ];
    }

    /** Bilinen yanlış Yandex hostlerini düzeltir. */
    public static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $map = [
            'smtp.yandex.com.tr' => 'smtp.yandex.com',
            'smtp.yandex.ru.tr' => 'smtp.yandex.com',
            'yandex.com.tr' => 'smtp.yandex.com',
            'smtp.ya.ru' => 'smtp.yandex.com',
        ];
        return $map[$host] ?? $host;
    }

    private static function isYandexProvider(string $provider, string $host = ''): bool
    {
        if ($provider === 'yandex') {
            return true;
        }
        $host = strtolower($host);
        return str_contains($host, 'yandex') || str_contains($host, 'ya.ru');
    }

    /**
     * Yandex/Gmail uygulama şifreleri boşluklu gösterilir; AUTH için boşluklar kaldırılır.
     * Yandex: AUTH kullanıcısı tam e-posta olmalı (gönderen ile aynı).
     *
     * @return array{username:string, password:string, from_email:string}
     */
    private static function normalizeSmtpAuth(string $provider, string $username, string $password, string $fromEmail): array
    {
        $username = trim($username);
        $fromEmail = trim($fromEmail);
        // Uygulama şifreleri: "xxxx xxxx xxxx xxxx" → boşluksuz
        $password = preg_replace('/\s+/', '', trim($password)) ?? trim($password);

        if ($provider === 'yandex') {
            // Yandex SMTP AUTH genelde tam adres ister
            if ($username !== '' && !str_contains($username, '@')) {
                if ($fromEmail !== '' && str_contains($fromEmail, '@')) {
                    $domain = substr($fromEmail, (int) strrpos($fromEmail, '@') + 1);
                    if (preg_match('/^(yandex\.(com|ru|com\.tr|kz|ua|by)|ya\.ru)$/i', $domain)) {
                        $username = $username . '@' . $domain;
                    }
                }
            }
            if ($fromEmail === '' && str_contains($username, '@')) {
                $fromEmail = $username;
            }
            // From, AUTH kullanıcısından farklı olamaz
            if ($fromEmail !== '' && $username !== '' && strcasecmp($fromEmail, $username) !== 0) {
                // from_email farklıysa AUTH'u from ile hizala (Yandex reddeder)
                if (str_contains($fromEmail, '@')) {
                    $username = $fromEmail;
                }
            }
        }

        return [
            'username' => $username,
            'password' => $password,
            'from_email' => $fromEmail,
        ];
    }

    private static function friendlySmtpError(string $message, string $provider): string
    {
        if (stripos($message, '535') !== false || stripos($message, 'authentication failed') !== false) {
            if ($provider === 'yandex') {
                return $message . ' — Yandex/Yandex 360: 1) Host smtp.yandex.com olmalı (smtp.yandex.com.tr değil). 2) id.yandex.com → Uygulama şifresi (Poşta). 3) mail.yandex.com → Ayarlar → Posta istemcileri → IMAP + uygulama şifreleri. Kurumsal @trueddn.com.tr için Yandex 360 admin panelinde bu kullanıcıya SMTP/posta istemcisi izni de gerekir.';
            }
            if ($provider === 'gmail') {
                return $message . ' — Gmail: Google Hesabı → Uygulama şifreleri kullanın (2FA açık olmalı).';
            }
            if ($provider === 'microsoft') {
                return $message . ' — Outlook/Microsoft: Uygulama şifresi veya SMTP AUTH açık bir kutu gerekir.';
            }
        }
        return $message;
    }

    /** @return list<array> */
    public static function servers(): array
    {
        try {
            $rows = Database::web()->query(
                'SELECT id, name, provider, host, port, encryption, username, from_email, from_name, is_active, created_at, updated_at
                 FROM mail_servers ORDER BY is_active DESC, id ASC'
            )->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => (int) $r['id'],
                    'name' => (string) $r['name'],
                    'provider' => (string) $r['provider'],
                    'host' => (string) $r['host'],
                    'port' => (int) $r['port'],
                    'encryption' => (string) $r['encryption'],
                    'username' => (string) $r['username'],
                    'from_email' => (string) $r['from_email'],
                    'from_name' => (string) $r['from_name'],
                    'is_active' => (int) ($r['is_active'] ?? 0) === 1,
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function activeServer(): ?array
    {
        try {
            $stmt = Database::web()->query(
                'SELECT * FROM mail_servers WHERE is_active = 1 ORDER BY id ASC LIMIT 1'
            );
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function serverById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare('SELECT * FROM mail_servers WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{ok:bool, errors:list<string>, id?:int} */
    public static function saveServer(
        ?int $id,
        string $name,
        string $provider,
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $fromEmail,
        string $fromName,
        bool $activate
    ): array {
        $name = trim($name);
        $provider = strtolower(trim($provider));
        $presets = self::presets();
        if (!isset($presets[$provider])) {
            $provider = 'custom';
        }
        if ($provider !== 'custom') {
            $host = $presets[$provider]['host'];
            $port = $presets[$provider]['port'];
            $encryption = $presets[$provider]['encryption'];
        }
        $host = self::normalizeHost($host);
        // Yanlışlıkla custom + yandex host → yandex davranışı
        if ($provider === 'custom' && self::isYandexProvider('custom', $host)) {
            $provider = 'yandex';
            $host = $presets['yandex']['host'];
            $port = $presets['yandex']['port'];
            $encryption = $presets['yandex']['encryption'];
        }
        $encryption = strtolower(trim($encryption));
        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            $encryption = 'tls';
        }
        $username = trim($username);
        $fromEmail = trim($fromEmail);
        $fromName = trim($fromName);
        $password = preg_replace('/\s+/', '', trim($password)) ?? trim($password);
        if (self::isYandexProvider($provider, $host)) {
            $norm = self::normalizeSmtpAuth('yandex', $username, $password !== '' ? $password : 'x', $fromEmail);
            $username = $norm['username'];
            $fromEmail = $norm['from_email'];
            $provider = 'yandex';
        }
        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors[] = 'Sunucu adı zorunlu (max 120).';
        }
        if ($host === '') {
            $errors[] = 'SMTP host zorunlu.';
        }
        if ($port < 1 || $port > 65535) {
            $errors[] = 'Geçersiz port.';
        }
        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli gönderen e-posta zorunlu.';
        }
        if ($provider === 'yandex' && $username !== '' && $fromEmail !== '' && strcasecmp($username, $fromEmail) !== 0) {
            $errors[] = 'Yandex için hesap (kullanıcı) ile gönderen e-posta aynı olmalı.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $web = Database::web();
            if ($activate) {
                $web->exec('UPDATE mail_servers SET is_active = 0');
            }
            if ($id && $id > 0) {
                if ($password !== '') {
                    $web->prepare(
                        'UPDATE mail_servers SET name=?, provider=?, host=?, port=?, encryption=?, username=?, password_enc=?, from_email=?, from_name=?, is_active=?, updated_at=NOW() WHERE id=?'
                    )->execute([
                        $name, $provider, $host, $port, $encryption, $username,
                        Security::encryptSecret($password), $fromEmail, $fromName, $activate ? 1 : 0, $id,
                    ]);
                } else {
                    $web->prepare(
                        'UPDATE mail_servers SET name=?, provider=?, host=?, port=?, encryption=?, username=?, from_email=?, from_name=?, is_active=?, updated_at=NOW() WHERE id=?'
                    )->execute([
                        $name, $provider, $host, $port, $encryption, $username,
                        $fromEmail, $fromName, $activate ? 1 : 0, $id,
                    ]);
                }
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }
            if ($password === '') {
                return ['ok' => false, 'errors' => ['Yeni sunucu için parola zorunlu.']];
            }
            $web->prepare(
                'INSERT INTO mail_servers
                  (name, provider, host, port, encryption, username, password_enc, from_email, from_name, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([
                $name, $provider, $host, $port, $encryption, $username,
                Security::encryptSecret($password), $fromEmail, $fromName, $activate ? 1 : 0,
            ]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Mail sunucusu kaydedilemedi.']];
        }
    }

    public static function deleteServer(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM mail_servers WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function setActiveServer(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            $web = Database::web();
            $web->exec('UPDATE mail_servers SET is_active = 0');
            $web->prepare('UPDATE mail_servers SET is_active = 1, updated_at=NOW() WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return list<array> */
    public static function templates(): array
    {
        try {
            $rows = Database::web()->query(
                'SELECT id, code, name, subject, body_html, is_enabled FROM mail_templates ORDER BY id ASC'
            )->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => (int) $r['id'],
                    'code' => (string) $r['code'],
                    'name' => (string) $r['name'],
                    'subject' => (string) $r['subject'],
                    'body_html' => (string) $r['body_html'],
                    'is_enabled' => (int) ($r['is_enabled'] ?? 0) === 1,
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function template(string $code): ?array
    {
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, code, name, subject, body_html, is_enabled FROM mail_templates WHERE code=? LIMIT 1'
            );
            $stmt->execute([$code]);
            $r = $stmt->fetch();
            if (!$r) {
                return null;
            }
            return [
                'id' => (int) $r['id'],
                'code' => (string) $r['code'],
                'name' => (string) $r['name'],
                'subject' => (string) $r['subject'],
                'body_html' => (string) $r['body_html'],
                'is_enabled' => (int) ($r['is_enabled'] ?? 0) === 1,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Ortak koyu kart kabuğu (kayıt şablonu tabanı).
     *
     * @param array{label:string,href?:string}|null $cta
     * @param array{label:string,value:string}|null $infoBox
     */
    public static function cardShell(
        string $title,
        string $bodyHtml,
        ?array $cta = null,
        ?array $infoBox = null,
        ?string $note = null
    ): string {
        $ctaHtml = '';
        if ($cta !== null && trim((string) ($cta['label'] ?? '')) !== '') {
            $href = (string) ($cta['href'] ?? '{{link}}');
            $label = (string) $cta['label'];
            $ctaHtml = <<<HTML
            <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin:0 auto 25px auto;">
              <tr>
                <td align="center" bgcolor="#d84315" style="border-radius:4px;">
                  <a href="{$href}" target="_blank" style="font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;padding:14px 28px;border:1px solid #ff7d47;display:block;border-radius:4px;text-transform:uppercase;letter-spacing:1px;line-height:1.2;text-align:center;">{$label}</a>
                </td>
              </tr>
            </table>
HTML;
        }

        $infoHtml = '';
        if ($infoBox !== null) {
            $ilabel = (string) ($infoBox['label'] ?? '');
            $ivalue = (string) ($infoBox['value'] ?? '');
            $infoHtml = <<<HTML
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:380px;margin:0 auto 30px auto;background-color:#100d0b;border:1px dashed #4a382a;border-radius:6px;">
              <tr>
                <td align="center" valign="middle" style="padding:15px;text-align:center;">
                  <p style="color:#8c7b6c;font-size:12px;margin:0 0 8px 0;text-transform:uppercase;letter-spacing:1px;">{$ilabel}</p>
                  <span style="font-family:'Courier New',Courier,monospace;font-size:22px;font-weight:700;color:#e5a93c;letter-spacing:3px;display:block;">{$ivalue}</span>
                </td>
              </tr>
            </table>
HTML;
        }

        $noteHtml = '';
        if ($note !== null && trim($note) !== '') {
            $noteHtml = '<p style="color:#6e5e50;font-size:12px;line-height:1.5;margin:0;text-align:center;">' . $note . '</p>';
        }

        return <<<HTML
<!-- m2dn-mail-card-v2 -->
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="padding:40px 10px;margin:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#d1c7bd;">
  <tr>
    <td align="center" valign="top">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:500px;background-color:#181412;border:1px solid #3d2f23;border-radius:8px;margin:0 auto;">
        <tr>
          <td style="background-color:#d84315;height:3px;font-size:1px;line-height:1px;">&nbsp;</td>
        </tr>
        <tr>
          <td align="center" valign="middle" style="padding:24px 20px 16px 20px;background-color:#14100e;border-bottom:1px solid #28201a;">
            <img src="{{logo}}" alt="{{app}}" width="{{logo_width}}" style="max-width:{{logo_width}}px;height:auto;display:block;border:0;margin:0 auto;">
          </td>
        </tr>
        <tr>
          <td align="center" valign="top" style="padding:30px 24px;text-align:center;">
            <h2 style="color:#f3e5ab;font-size:20px;margin:0 0 15px 0;font-weight:600;text-align:center;">{$title}</h2>
            {$bodyHtml}
            {$ctaHtml}
            {$infoHtml}
            {$noteHtml}
          </td>
        </tr>
        <tr>
          <td align="center" valign="middle" style="background-color:#100d0b;padding:18px 24px;text-align:center;border-top:1px solid #221a14;">
            <p style="color:#6e5e50;font-size:11px;margin:0;text-align:center;line-height:1.4;">
              Bu bildirim <strong>{{email}}</strong> adresi için gönderilmiştir.<br>
              &copy; {{app}} - Tüm Hakları Saklıdır.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
HTML;
    }

    /**
     * E-posta istemcileri için varsayılan şablon gövdeleri (ortak kart tabanı).
     *
     * @return array<string, string>
     */
    public static function defaultBodies(): array
    {
        $p = static fn(string $html): string => '<p style="color:#bfae9e;font-size:15px;line-height:1.6;margin:0 0 25px 0;text-align:center;">' . $html . '</p>';

        return [
            'register' => trim(self::cardShell(
                '⚔️ MACERA BAŞLIYOR!',
                $p('Selam <strong style="color:#e5a93c;">{{login}}</strong>, <strong style="color:#e5a93c;">{{app}}</strong> hesabın başarıyla oluşturuldu.<br>Efsanelerin arasına katılmak ve maceraya adım atmak için hemen giriş yapabilirsin!'),
                ['label' => 'HESABINA GİT', 'href' => '{{link}}'],
                null,
                'Bu talebi sen yapmadıysan bu e-postayı dikkate almayabilirsin.'
            )),
            'password_reset' => trim(self::cardShell(
                '🔐 ŞİFRE SIFIRLAMA',
                $p('Merhaba <strong style="color:#e5a93c;">{{login}}</strong>,<br>Hesabın için şifre sıfırlama talebi aldık. Bağlantı <strong style="color:#e5a93c;">20 dakika</strong> geçerlidir.'),
                ['label' => 'ŞİFREYİ SIFIRLA', 'href' => '{{link}}'],
                null,
                'Bu talebi sen yapmadıysan bu e-postayı yok sayabilirsin. Şifren değişmez.'
            )),
            'ban' => trim(self::cardShell(
                '⛔ HESAP BANLANDI',
                $p('Merhaba <strong style="color:#e5a93c;">{{login}}</strong>,<br>Hesabın <strong style="color:#ff7d47;">banlandı</strong>. Oyun ve panele erişimin kısıtlandı.'),
                null,
                ['label' => 'Ban Sebebi', 'value' => '{{reason}}'],
                'İtirazın varsa destek talebi oluşturabilirsin.'
            )),
            'unban' => trim(self::cardShell(
                '✅ BAN KALDIRILDI',
                $p('Merhaba <strong style="color:#e5a93c;">{{login}}</strong>,<br>Hesabındaki ban <strong style="color:#e5a93c;">kaldırıldı</strong>. Tekrar maceraya dönebilirsin!'),
                ['label' => 'PANELE GİT', 'href' => '{{link}}'],
                ['label' => 'Not', 'value' => '{{reason}}'],
                'Kurallara uymaya devam etmeni dileriz.'
            )),
            'ticket_created' => trim(self::cardShell(
                '🎫 YENİ DESTEK TALEBİ',
                $p('Yeni bir destek talebi oluşturuldu.<br>Oyuncu: <strong style="color:#e5a93c;">{{login}}</strong><br>Konu: <strong style="color:#e5a93c;">{{subject}}</strong>'),
                ['label' => 'TALEPİ GÖR', 'href' => '{{link}}'],
                ['label' => 'Ticket Kodu', 'value' => '{{code}}'],
                'En kısa sürede yanıtlanacaktır.'
            )),
            'ticket_replied' => trim(self::cardShell(
                '💬 TALEBİNE YANIT GELDİ',
                $p('Merhaba <strong style="color:#e5a93c;">{{login}}</strong>,<br><strong style="color:#e5a93c;">{{code}}</strong> numaralı destek talebine yeni bir yanıt yazıldı.'),
                ['label' => 'YANITI GÖR', 'href' => '{{link}}'],
                ['label' => 'Ticket', 'value' => '{{code}}'],
                'Konu: {{subject}}'
            )),
            'ticket_closed' => trim(self::cardShell(
                '✔️ TALEP KAPATILDI',
                $p('Merhaba <strong style="color:#e5a93c;">{{login}}</strong>,<br><strong style="color:#e5a93c;">{{code}}</strong> numaralı destek talebin kapatıldı.'),
                ['label' => 'TALEPİ GÖR', 'href' => '{{link}}'],
                ['label' => 'Ticket', 'value' => '{{code}}'],
                'Konu: {{subject}}'
            )),
        ];
    }

    /** Mail içinde kullanılacak mutlak logo URL (yalnızca PNG — SVG e-posta istemcilerinde bozulur). */
    public static function logoUrl(): string
    {
        $pngRel = \App\Core\Theme::assetUrl('img/logo-mail.png');
        $pngFs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR
            . \App\Core\Theme::active() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo-mail.png';

        $logo = is_file($pngFs) ? $pngRel : '';
        if ($logo === '') {
            // PNG yoksa bile SVG'ye düşme — kırık img yerine boş bırakma riski düşük; relative png yolu dene
            $logo = $pngRel !== '' ? $pngRel : '';
        }

        if ($logo === '') {
            return '';
        }

        // Yanlışlıkla SVG kaldıysa yine PNG'ye zorla
        $pathOnly = strtolower((string) (parse_url($logo, PHP_URL_PATH) ?? $logo));
        if (str_ends_with($pathOnly, '.svg')) {
            $logo = $pngRel;
        }

        if (preg_match('#^https?://#i', $logo) === 1) {
            return $logo;
        }
        $base = rtrim((string) Config::get('app.url', ''), '/');
        if ($base === '') {
            return $logo;
        }
        return $base . '/' . ltrim($logo, '/');
    }

    /** Mail logosu genişliği (px). Logo menüsünden ayarlanır. */
    public static function logoWidth(): int
    {
        try {
            $brand = SiteContentService::branding();
            $w = (int) ($brand['mail_size'] ?? 160);
        } catch (\Throwable) {
            $w = 160;
        }

        return max(40, min(320, $w > 0 ? $w : 160));
    }

    /**
     * Şablon HTML içindeki SVG logo src'lerini {{logo}} ile değiştirir (mail-safe PNG).
     */
    public static function replaceSvgLogosInHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        // logo-nav / logo-mark / herhangi .svg img → {{logo}}
        $html = preg_replace(
            '#(<img\b[^>]*\bsrc=["\'])([^"\']*?logo[^"\']*?\.svg[^"\']*)(["\'])#i',
            '$1{{logo}}$3',
            $html
        ) ?? $html;
        $html = preg_replace(
            '#(<img\b[^>]*\bsrc=["\'])([^"\']+\.svg)(["\'])#i',
            '$1{{logo}}$3',
            $html
        ) ?? $html;
        // Zaten çözülmüş mutlak SVG URL'leri (gönderilmiş kopya / önizleme)
        $html = preg_replace(
            '#(src=["\'])(https?://[^"\']+\.svg)(["\'])#i',
            '$1{{logo}}$3',
            $html
        ) ?? $html;

        return $html;
    }

    /** contenteditable'ın parçaladığı / siyah arka planlı bozuk şablonları tespit eder. */
    public static function isBrokenEmailHtml(string $html): bool
    {
        $html = trim($html);
        if ($html === '') {
            return true;
        }
        if (preg_match('/<p>\s*<!DOCTYPE/i', $html) === 1) {
            return true;
        }
        if (preg_match('/<p>\s*<html[\s>]/i', $html) === 1) {
            return true;
        }
        // Tablo açılışı hemen kapanmış (editör hasarı)
        if (preg_match('/<table\b[^>]*>\s*<\/p>/i', $html) === 1) {
            return true;
        }
        // Eski koyu tema + aşırı <p> parçalama
        if (str_contains($html, '#0f0d0b') && substr_count(strtolower($html), '</p>') > 15) {
            return true;
        }
        return false;
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveTemplate(string $code, string $subject, string $bodyHtml, bool $enabled): array
    {
        $code = trim($code);
        $subject = trim($subject);
        if ($code === '' || $subject === '') {
            return ['ok' => false, 'errors' => ['Konu zorunlu.']];
        }
        $bodyHtml = self::normalizeHtmlBody($bodyHtml);
        if (self::isBrokenEmailHtml($bodyHtml)) {
            $defaults = self::defaultBodies();
            if (isset($defaults[$code])) {
                $bodyHtml = $defaults[$code];
            } else {
                return ['ok' => false, 'errors' => ['HTML şablon bozuk. Tam HTML’i HTML modunda yapıştır veya Önizle ile kontrol et.']];
            }
        }
        try {
            Database::web()->prepare(
                'UPDATE mail_templates SET subject=?, body_html=?, is_enabled=?, updated_at=NOW() WHERE code=?'
            )->execute([$subject, $bodyHtml, $enabled ? 1 : 0, $code]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Şablon kaydedilemedi.']];
        }
    }

    /**
     * Görsel editöre HTML kodu metin olarak yapıştırılınca &lt;p&gt; kaydı oluşur.
     * Gönderim/kayıt öncesi gerçek HTML'e çevirir.
     */
    public static function normalizeHtmlBody(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        // UTF-8 BOM
        if (str_starts_with($html, "\xEF\xBB\xBF")) {
            $html = substr($html, 3);
        }
        $guard = 0;
        while ($guard < 3 && preg_match('/&lt;\\/?[a-z!]/i', $html) === 1) {
            $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $html) {
                break;
            }
            $html = $decoded;
            $guard++;
        }
        // NBSP / contenteditable boşluk temizliği (düz metin alternatifini de bozuyordu)
        $html = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $html);
        return self::replaceSvgLogosInHtml($html);
    }

    /** Bozuk kayıt şablonunu varsayılana çeker (Schema / gönderim). */
    public static function repairTemplateIfBroken(string $code, string $bodyHtml): string
    {
        $bodyHtml = self::normalizeHtmlBody($bodyHtml);
        if (!self::isBrokenEmailHtml($bodyHtml)) {
            return $bodyHtml;
        }
        $defaults = self::defaultBodies();
        return $defaults[$code] ?? $bodyHtml;
    }

    /** HTML gövdeden düz metin alternatif (multipart/alternative). */
    private static function htmlToPlain(string $html): string
    {
        $text = $html;
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\s*\/\s*p\s*>/i', "\n\n", $text) ?? $text;
        $text = preg_replace('/<\s*\/\s*div\s*>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\s*\/\s*tr\s*>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\s*a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\s*\/\s*a\s*>/is', '$2 ($1)', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        return trim($text);
    }

    /**
     * @param array<string, string> $vars
     * @param bool $force Şablon kapalı olsa bile gönder (şifre sıfırlama vb. zorunlu mailler)
     * @return array{ok:bool, errors:list<string>}
     */
    public static function sendTemplate(string $code, string $toEmail, string $toLogin, array $vars = [], bool $force = false): array
    {
        $tpl = self::template($code);
        $defaults = self::defaultBodies();
        if ($tpl === null) {
            if (!$force || !isset($defaults[$code])) {
                return ['ok' => false, 'errors' => ['Şablon yok.']];
            }
            $tpl = [
                'code' => $code,
                'subject' => self::defaultSubjects()[$code] ?? 'Bildirim',
                'body_html' => $defaults[$code],
                'is_enabled' => true,
            ];
        }
        if (!$force && !$tpl['is_enabled']) {
            self::logMail(
                $code,
                $toEmail,
                $toLogin,
                (string) ($tpl['subject'] ?? $code),
                'fail',
                'Bildirim şablonu kapalı (Mail → Bildirimler’de Aktif edin)'
            );
            return ['ok' => false, 'errors' => ['Bildirim kapalı.']];
        }
        $vars = array_merge([
            'app' => (string) Config::get('app.name', 'M2DN'),
            'login' => $toLogin,
            'email' => $toEmail,
            'link' => rtrim((string) Config::get('app.url', ''), '/'),
            'logo' => self::logoUrl(),
            'logo_width' => (string) self::logoWidth(),
            'reason' => '',
            'code' => '',
            'subject' => '',
        ], $vars);
        if (trim((string) ($vars['link'] ?? '')) === '') {
            $vars['link'] = rtrim((string) Config::get('app.url', ''), '/');
        } else {
            $linkVal = trim((string) $vars['link']);
            // Bildirimlerde göreli path gelirse mutlak URL yap
            if (str_starts_with($linkVal, '/')) {
                $vars['link'] = rtrim((string) Config::get('app.url', ''), '/') . $linkVal;
            }
        }
        if (trim((string) ($vars['logo'] ?? '')) === '') {
            $vars['logo'] = self::logoUrl();
        } else {
            $logoPath = strtolower((string) (parse_url((string) $vars['logo'], PHP_URL_PATH) ?? (string) $vars['logo']));
            if (str_ends_with($logoPath, '.svg')) {
                $vars['logo'] = self::logoUrl();
            }
        }
        if (trim((string) ($vars['logo_width'] ?? '')) === '' || (int) $vars['logo_width'] <= 0) {
            $vars['logo_width'] = (string) self::logoWidth();
        } else {
            $vars['logo_width'] = (string) max(40, min(320, (int) $vars['logo_width']));
        }
        $subjectTpl = (string) ($tpl['subject'] ?? '');
        if ($subjectTpl === '') {
            $subjectTpl = self::defaultSubjects()[$code] ?? 'Bildirim';
        }
        $subject = self::render($subjectTpl, $vars);
        $rawBody = self::repairTemplateIfBroken($code, (string) ($tpl['body_html'] ?? ''));
        $rawBody = self::replaceSvgLogosInHtml($rawBody);
        if ($rawBody === '' && isset($defaults[$code])) {
            $rawBody = $defaults[$code];
        }
        // DB'deki bozuk şablonu sessizce düzelt
        if ($tpl !== null && isset($tpl['body_html']) && $rawBody !== (string) $tpl['body_html']) {
            try {
                Database::web()->prepare(
                    'UPDATE mail_templates SET body_html=?, updated_at=NOW() WHERE code=?'
                )->execute([$rawBody, $code]);
            } catch (\Throwable) {
                // ignore
            }
        }
        $body = self::normalizeHtmlBody(self::render($rawBody, $vars));
        return self::sendRaw($toEmail, $toLogin, $subject, $body, $code);
    }

    /** @return array<string, string> */
    public static function defaultSubjects(): array
    {
        return [
            'register' => 'Hoş geldin {{login}}',
            'password_reset' => 'Şifre sıfırlama',
            'ban' => 'Hesabın banlandı',
            'unban' => 'Banın kaldırıldı',
            'ticket_created' => 'Yeni destek talebi {{code}}',
            'ticket_replied' => 'Ticket {{code}} yanıtlandı',
            'ticket_closed' => 'Ticket {{code}} kapatıldı',
        ];
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function sendRaw(
        string $toEmail,
        string $toLogin,
        string $subject,
        string $htmlBody,
        string $templateCode = '',
        ?int $serverId = null
    ): array {
        $toEmail = trim($toEmail);
        $htmlBody = self::normalizeHtmlBody($htmlBody);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            self::logMail($templateCode, $toEmail, $toLogin, $subject, 'fail', 'Geçersiz veya boş alıcı e-posta');
            return ['ok' => false, 'errors' => ['Geçersiz alıcı e-posta.']];
        }
        $server = ($serverId !== null && $serverId > 0)
            ? self::serverById($serverId)
            : self::activeServer();
        if ($server === null) {
            self::logMail($templateCode, $toEmail, $toLogin, $subject, 'fail', 'Mail sunucusu bulunamadı');
            return ['ok' => false, 'errors' => ['Mail sunucusu bulunamadı. Önce sunucu kaydedin.']];
        }
        $pass = Security::decryptSecret((string) ($server['password_enc'] ?? ''));
        $provider = strtolower((string) ($server['provider'] ?? 'custom'));
        $host = self::normalizeHost((string) ($server['host'] ?? ''));
        if (self::isYandexProvider($provider, $host)) {
            $provider = 'yandex';
            $host = self::presets()['yandex']['host'];
        }
        $auth = self::normalizeSmtpAuth(
            $provider,
            (string) ($server['username'] ?? ''),
            $pass,
            (string) ($server['from_email'] ?? '')
        );
        try {
            self::smtpSend(
                $host,
                (int) $server['port'],
                (string) $server['encryption'],
                $auth['username'],
                $auth['password'],
                $auth['from_email'] !== '' ? $auth['from_email'] : (string) ($server['from_email'] ?? ''),
                (string) ($server['from_name'] ?? ''),
                $toEmail,
                $subject,
                $htmlBody
            );
            self::logMail($templateCode, $toEmail, $toLogin, $subject, 'ok', '', $htmlBody);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable $e) {
            $msg = self::friendlySmtpError($e->getMessage(), $provider);
            self::logMail($templateCode, $toEmail, $toLogin, $subject, 'fail', $msg, $htmlBody);
            return ['ok' => false, 'errors' => ['Mail gönderilemedi: ' . $msg]];
        }
    }

    /**
     * Son gönderimler (varsayılan top 10). Alıcı e-posta / login araması.
     *
     * @return list<array{
     *   id:int, template_code:string, to_email:string, to_login:string,
     *   subject:string, status:string, error:string, created_at:string, has_body:bool
     * }>
     */
    public static function logs(int $limit = 10, string $q = ''): array
    {
        $limit = max(1, min(100, $limit));
        $q = trim($q);
        try {
            $sql = 'SELECT id, template_code, to_email, to_login, subject, status, error, created_at,
                           (body_html IS NOT NULL AND CHAR_LENGTH(body_html) > 0) AS has_body
                    FROM mail_logs';
            $params = [];
            if ($q !== '') {
                $sql .= ' WHERE to_email LIKE ? OR to_login LIKE ? OR subject LIKE ?';
                $like = '%' . $q . '%';
                $params = [$like, $like, $like];
            }
            $sql .= " ORDER BY id DESC LIMIT {$limit}";
            $stmt = Database::web()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => (int) $r['id'],
                    'template_code' => (string) ($r['template_code'] ?? ''),
                    'to_email' => (string) ($r['to_email'] ?? ''),
                    'to_login' => (string) ($r['to_login'] ?? ''),
                    'subject' => (string) ($r['subject'] ?? ''),
                    'status' => (string) ($r['status'] ?? ''),
                    'error' => (string) ($r['error'] ?? ''),
                    'created_at' => (string) ($r['created_at'] ?? ''),
                    'has_body' => !empty($r['has_body']),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function resend(int $id): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz kayıt.']];
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, template_code, to_email, to_login, subject, body_html FROM mail_logs WHERE id=? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kayıt okunamadı.']];
        }
        if (!$row) {
            return ['ok' => false, 'errors' => ['Gönderim kaydı bulunamadı.']];
        }
        $body = self::normalizeHtmlBody((string) ($row['body_html'] ?? ''));
        if ($body === '') {
            return ['ok' => false, 'errors' => ['Bu kayıtta mail içeriği yok (eski gönderim). Yeni gönderimler tekrarlanabilir.']];
        }
        $code = (string) ($row['template_code'] ?? '');
        if ($code === '') {
            $code = 'resend';
        }
        return self::sendRaw(
            (string) ($row['to_email'] ?? ''),
            (string) ($row['to_login'] ?? ''),
            (string) ($row['subject'] ?? ''),
            $body,
            $code
        );
    }

    /** @param array<string, string> $vars */
    public static function render(string $text, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', static function (array $m) use ($vars): string {
            $k = $m[1];
            return isset($vars[$k]) ? (string) $vars[$k] : '';
        }, $text) ?? $text;
    }

    private static function logMail(
        string $code,
        string $to,
        string $login,
        string $subject,
        string $status,
        string $error,
        string $bodyHtml = ''
    ): void {
        try {
            Database::web()->prepare(
                'INSERT INTO mail_logs (template_code, to_email, to_login, subject, body_html, status, error, created_at)
                 VALUES (?,?,?,?,?,?,?,NOW())'
            )->execute([
                self::clip($code, 40),
                self::clip($to, 190),
                self::clip($login, 30),
                self::clip($subject, 200),
                $bodyHtml !== '' ? self::normalizeHtmlBody($bodyHtml) : null,
                self::clip($status, 20),
                self::clip($error, 500),
            ]);
        } catch (\Throwable) {
            // Eski şema (body_html yok) — fallback
            try {
                Database::web()->prepare(
                    'INSERT INTO mail_logs (template_code, to_email, to_login, subject, status, error, created_at)
                     VALUES (?,?,?,?,?,?,NOW())'
                )->execute([
                    self::clip($code, 40),
                    self::clip($to, 190),
                    self::clip($login, 30),
                    self::clip($subject, 200),
                    self::clip($status, 20),
                    self::clip($error, 500),
                ]);
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    /** Gönderim atlandı / şablon kapalı vb. — Mail → Gönderim listesinde görünür. */
    public static function logSkipped(string $code, string $toEmail, string $toLogin, string $reason): void
    {
        self::logMail($code, $toEmail, $toLogin, $code, 'fail', $reason);
    }

    private static function clip(string $value, int $max): string
    {
        if (mb_strlen($value) <= $max) {
            return $value;
        }
        return mb_substr($value, 0, $max);
    }

    private static function smtpSend(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $htmlBody
    ): void {
        $host = self::normalizeHost($host);
        $deadline = microtime(true) + self::SMTP_TOTAL_DEADLINE;
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            self::SMTP_CONNECT_TIMEOUT,
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if ($fp === false) {
            throw new \RuntimeException(
                "SMTP bağlantı hatası ({$host}:{$port}): " . ($errstr !== '' ? $errstr : 'zaman aşımı/bağlanılamadı')
            );
        }

        try {
            stream_set_timeout($fp, self::SMTP_IO_TIMEOUT);
            stream_set_blocking($fp, true);

            self::smtpExpect($fp, [220], $deadline);
            self::smtpCmd($fp, 'EHLO m2dn.local', [250], $deadline);
            if ($encryption === 'tls') {
                self::smtpCmd($fp, 'STARTTLS', [220], $deadline);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS başarısız.');
                }
                self::smtpCmd($fp, 'EHLO m2dn.local', [250], $deadline);
            }
            if ($username !== '') {
                self::smtpCmd($fp, 'AUTH LOGIN', [334], $deadline);
                self::smtpCmd($fp, base64_encode($username), [334], $deadline);
                self::smtpCmd($fp, base64_encode($password), [235], $deadline);
            }
            self::smtpCmd($fp, 'MAIL FROM:<' . $fromEmail . '>', [250], $deadline);
            self::smtpCmd($fp, 'RCPT TO:<' . $toEmail . '>', [250, 251], $deadline);
            self::smtpCmd($fp, 'DATA', [354], $deadline);

            $htmlBody = self::normalizeHtmlBody($htmlBody);
            $plainBody = self::htmlToPlain($htmlBody);
            if ($plainBody === '') {
                $plainBody = strip_tags($htmlBody) ?: ' ';
            }

            // Gmail / Outlook / Yandex: multipart/alternative bekler; tek parça HTML
            // bazen düz metin gibi gösterilir veya entity kaçışlı HTML etiketleri görünür.
            $boundary = '=_m2dn_' . bin2hex(random_bytes(12));
            $domain = 'localhost';
            if (str_contains($fromEmail, '@')) {
                $domain = substr($fromEmail, (int) strrpos($fromEmail, '@') + 1) ?: 'localhost';
            }
            $messageId = sprintf('<%s@%s>', bin2hex(random_bytes(16)), $domain);

            $fromHeader = $fromName !== ''
                ? sprintf('"%s" <%s>', addcslashes($fromName, '"\\'), $fromEmail)
                : $fromEmail;

            $headers = [
                'Date: ' . date('r'),
                'From: ' . $fromHeader,
                'To: <' . $toEmail . '>',
                'Subject: ' . self::encodeHeader($subject),
                'Message-ID: ' . $messageId,
                'MIME-Version: 1.0',
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            ];

            $parts = [
                '--' . $boundary,
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
                '',
                rtrim(chunk_split(base64_encode($plainBody))),
                '--' . $boundary,
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
                '',
                rtrim(chunk_split(base64_encode($htmlBody))),
                '--' . $boundary . '--',
            ];

            $data = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $parts) . "\r\n.";
            if (fwrite($fp, $data . "\r\n") === false) {
                throw new \RuntimeException('SMTP DATA yazılamadı.');
            }
            self::smtpExpect($fp, [250], $deadline);
            try {
                self::smtpCmd($fp, 'QUIT', [221], $deadline);
            } catch (\Throwable) {
                // QUIT başarısız olsa da mail gitmiş olabilir
            }
        } finally {
            fclose($fp);
        }
    }

    /** @param mixed $fp @param list<int> $ok */
    private static function smtpCmd(mixed $fp, string $cmd, array $ok, float $deadline): void
    {
        self::assertDeadline($deadline);
        if (fwrite($fp, $cmd . "\r\n") === false) {
            throw new \RuntimeException('SMTP komutu yazılamadı.');
        }
        self::smtpExpect($fp, $ok, $deadline);
    }

    /** @param mixed $fp @param list<int> $ok */
    private static function smtpExpect(mixed $fp, array $ok, float $deadline): void
    {
        self::assertDeadline($deadline);
        $line = '';
        while (true) {
            self::assertDeadline($deadline);
            $buf = fgets($fp, 515);
            if ($buf === false) {
                $meta = stream_get_meta_data($fp);
                if (!empty($meta['timed_out'])) {
                    throw new \RuntimeException('SMTP zaman aşımı (yanıt gelmedi).');
                }
                throw new \RuntimeException('SMTP bağlantısı kapandı (yanıt yok).');
            }
            $line = $buf;
            if (isset($buf[3]) && $buf[3] === ' ') {
                break;
            }
        }
        $trimmed = trim($line);
        if ($trimmed === '') {
            throw new \RuntimeException('SMTP boş yanıt (zaman aşımı/hata).');
        }
        $code = (int) substr($trimmed, 0, 3);
        if (!in_array($code, $ok, true)) {
            throw new \RuntimeException('SMTP yanıtı: ' . $trimmed);
        }
    }

    private static function assertDeadline(float $deadline): void
    {
        if (microtime(true) > $deadline) {
            throw new \RuntimeException('SMTP toplam süre limiti aşıldı (' . self::SMTP_TOTAL_DEADLINE . ' sn).');
        }
    }

    private static function encodeHeader(string $value): string
    {
        if (preg_match('/^[\x20-\x7E]*$/', $value)) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
