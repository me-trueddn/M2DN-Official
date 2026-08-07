<?php
/**
 * M2DN — Örnek yapılandırma
 * Kopyalayın:  copy config/config.example.php config/config.php
 * config/config.php git’e eklenmez (.gitignore).
 */

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Genel
    |--------------------------------------------------------------------------
    */
    'app' => [
        'name'       => 'M2DN',
        'tagline'    => 'Metin2 Pvp Sunucusu',
        'url'        => 'http://127.0.0.1:8080',
        // Sürüm: config/version.json (git’te). Buraya yazmayın.
        'timezone'   => 'Europe/Istanbul',
        'locale'     => 'tr',
        'charset'    => 'utf-8',
        'debug'      => true,          // Canlıda false yapın
        'env'        => 'local',       // local | production
    ],

    /*
    |--------------------------------------------------------------------------
    | Tema
    |--------------------------------------------------------------------------
    | themes/ klasöründeki klasör adı. Varsayılan: EasternV1
    | Yeni tema eklemek için themes/YeniTema/ oluşturup burayı değiştirin.
    */
    'theme' => [
        'active'  => 'EasternV1',
        'fallback'=> 'EasternV1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Güvenlik
    |--------------------------------------------------------------------------
    */
    'security' => [
        'session_name'       => 'M2DN_SESS',
        'csrf_token_name'    => 'csrf_token',
        // Oyun hesabı: account.account.password alanında YALNIZCA MD5
        // login, email, social_id vb. alanlar düz metin kalır.
        'account_password'   => 'md5',
        // DNWeb / panel admin gibi web-only şifreler (account tablosu değil)
        'web_password_algo'  => PASSWORD_BCRYPT,
        'web_password_cost'  => 12,
        'max_login_attempts' => 5,
        'lockout_minutes'    => 15,
        // Panel oturumu: her geçerli istekte süre yenilenir (dakika, idle timeout).
        'web_session_ttl'    => 10,
        // Girişten itibaren mutlak üst süre (dakika) — sürekli yenilemeyle sonsuz oturum engeli.
        'web_session_max_ttl'=> 120,
        // Token imzalama — canlıda mutlaka değiştirin
        'app_key'            => 'M2DN-change-me-to-a-long-random-secret-key',
        'force_https'        => false, // Canlıda true yapın
        'cookie_secure'      => false, // HTTPS varsa true
        'cookie_httponly'    => true,
        'cookie_samesite'    => 'Lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Web paneli veritabanı (CMS / hesap paneli)
    |--------------------------------------------------------------------------
    | Site içerikleri, destek talepleri, oy kayıtları vb. burada tutulur.
    */
    'web_database' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'DNWeb',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    /*
    |--------------------------------------------------------------------------
    | Oyun sunucuları
    |--------------------------------------------------------------------------
    | Birden fazla sunucu ekleyebilirsiniz. Her sunucuda aynı DB isimleri
    | kullanılır (account, common, player, log). Bağlantı bilgileri sunucuya
    | göre farklı olabilir.
    |
    | 'default' anahtarı, panelde seçim yokken kullanılacak sunucudur.
    */
    'default_server' => 'main',

    'servers' => [

        'main' => [
            'name'        => 'M2DN - Atlantis',
            'key'         => 'main',
            'enabled'     => true,
            'host'        => '127.0.0.1',
            'port'        => 3306,
            'username'    => 'root',
            'password'    => '',
            'charset'     => 'utf8mb4',
            // Her sunucuda aynı şema — DB adlarını buraya yazın
            'databases'   => [
                'account' => 'account',
                'common'  => 'common',
                'player'  => 'player',
                'log'     => 'log',
            ],
            // Oyun bağlantı bilgileri (site gösterimi)
            'game' => [
                'ip'       => '127.0.0.1',
                'port'     => 13000,
                'channels' => 4,
            ],
        ],

        // İkinci sunucu örneği — kopyalayıp düzenleyin
        // 'second' => [
        //     'name'      => 'M2DN #2',
        //     'key'       => 'second',
        //     'enabled'   => true,
        //     'host'      => '127.0.0.1',
        //     'port'      => 3306,
        //     'username'  => 'root',
        //     'password'  => '',
        //     'charset'   => 'utf8mb4',
        //     'databases' => [
        //         'account' => 'account',
        //         'common'  => 'common',
        //         'player'  => 'player',
        //         'log'     => 'log',
        //     ],
        //     'game' => [
        //         'ip'       => '127.0.0.1',
        //         'port'     => 13000,
        //         'channels' => 4,
        //     ],
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Oranlar / site gösterimi
    |--------------------------------------------------------------------------
    */
    'rates' => [
        'exp'  => 100,
        'drop' => 50,
        'yang' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Oyun limitleri
    |--------------------------------------------------------------------------
    | max_level: null ise player tablosundaki MAX(level) kullanılır (yoksa 99).
    */
    'game_limits' => [
        'max_level' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin panel
    |--------------------------------------------------------------------------
    | online_window_minutes: player.last_play son X dakika içindeyse "çevrimiçi"
    | kabul edilir (oyun sunucusu canlı socket tablosu tutmaz).
    */
    'admin' => [
        'online_window_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nesne Market (web modal + oyun içi CEF)
    |--------------------------------------------------------------------------
    | Oyun istemcisi URL örneği:
    | /nesne-market?mode=ingame&login=LOGIN&aid=ID&pid=PID&char=NAME&ts=UNIX&sig=HMAC
    | sig = hash_hmac('sha256', "{login}|{aid}|{pid}|{ts}", ingame_secret)
    */
    'nesne_market' => [
        'enabled'            => true,
        'ingame_secret'      => 'M2DN-market-change-me',
        'ingame_ttl'         => 120, // saniye
        // Klasik safebox: size = sayfa sayısı, sayfa başına slot
        'safebox_page_size'  => 45,
        'safebox_default_pages' => 1,
    ],

];
