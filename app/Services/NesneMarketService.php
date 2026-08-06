<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use PDO;

/**
 * Nesne Market — web panel + oyun içi (CEF) giriş.
 *
 * Oyun istemcisi örnek URL:
 * /nesne-market?mode=ingame&login=ACCOUNT&aid=1&pid=123&char=KaraKilic&ts=UNIX&sig=HMAC
 * sig = hash_hmac('sha256', "{login}|{aid}|{pid}|{ts}", nesne_market.ingame_secret)
 */
final class NesneMarketService
{
    public static function isEnabled(): bool
    {
        return (bool) Config::get('nesne_market.enabled', true);
    }

    public static function assetUrl(string $path = ''): string
    {
        $theme = (string) Config::get('theme.active', 'EasternV1');
        $base = '/themes/' . rawurlencode($theme) . '/nesnemarket/assets';
        $path = ltrim($path, '/');
        return $path === '' ? $base : $base . '/' . $path;
    }

    /** @return list<array{id:int,name:string,cat:string,icon:string,image:string,price:int,old:?int,ribbon:?string,desc:string}> */
    public static function catalog(): array
    {
        return MarketItemService::catalogRows();
    }

    /**
     * Oyun istemcisinden gelen imzalı isteği doğrula ve oturum aç.
     *
     * @return array{ok:bool, user?:array, character?:?array, errors:list<string>}
     */
    public static function tryIngameAuth(): array
    {
        $login = trim((string) ($_GET['login'] ?? $_GET['account'] ?? ''));
        $aid = (int) ($_GET['aid'] ?? $_GET['account_id'] ?? 0);
        $pid = (int) ($_GET['pid'] ?? $_GET['player_id'] ?? 0);
        $char = trim((string) ($_GET['char'] ?? $_GET['name'] ?? ''));
        $ts = (int) ($_GET['ts'] ?? 0);
        $sig = trim((string) ($_GET['sig'] ?? $_GET['hash'] ?? ''));

        if ($login === '' && $aid <= 0 && $sig === '') {
            return ['ok' => false, 'errors' => []];
        }

        $secret = (string) Config::get('nesne_market.ingame_secret', '');
        if ($secret === '' || $secret === 'change-me') {
            return ['ok' => false, 'errors' => ['Oyun içi market anahtarı yapılandırılmamış.']];
        }

        if ($sig === '' || $ts <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz oyun oturumu.']];
        }

        $ttl = max(30, (int) Config::get('nesne_market.ingame_ttl', 120));
        if (abs(time() - $ts) > $ttl) {
            return ['ok' => false, 'errors' => ['Oyun oturumu süresi doldu. Tekrar aç.']];
        }

        $payload = $login . '|' . $aid . '|' . $pid . '|' . $ts;
        $expected = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expected, $sig)) {
            return ['ok' => false, 'errors' => ['Oyun oturumu doğrulanamadı.']];
        }

        $row = self::findAccount($aid, $login);
        if ($row === null) {
            return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
        }

        $accountId = (int) $row['id'];
        $accountLogin = (string) $row['login'];
        $permission = AuthService::normalizePermission($row['WebPermission'] ?? null);

        $character = self::resolveCharacter($accountId, $pid, $char);

        $current = AuthService::user();
        if ($current === null || (int) $current['account_id'] !== $accountId) {
            AuthService::loginTrusted($accountId, $accountLogin, $permission);
        }

        $user = AuthService::user();
        if ($user === null) {
            return ['ok' => false, 'errors' => ['Oturum açılamadı.']];
        }

        return [
            'ok' => true,
            'errors' => [],
            'user' => $user,
            'character' => $character,
        ];
    }

    /** @return array{id:int,login:string,WebPermission:mixed}|null */
    private static function findAccount(int $aid, string $login): ?array
    {
        try {
            $pdo = Database::account();
            if ($aid > 0) {
                $stmt = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE id = ? LIMIT 1');
                $stmt->execute([$aid]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($row)) {
                    if ($login !== '' && strcasecmp((string) $row['login'], $login) !== 0) {
                        return null;
                    }
                    return $row;
                }
            }
            if ($login !== '') {
                $stmt = $pdo->prepare('SELECT id, login, WebPermission FROM account WHERE login = ? LIMIT 1');
                $stmt->execute([$login]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return is_array($row) ? $row : null;
            }
        } catch (\Throwable) {
            return null;
        }
        return null;
    }

    /** @return array{id:int,name:string,level:int}|null */
    private static function resolveCharacter(int $accountId, int $pid, string $charName): ?array
    {
        try {
            $pdo = Database::player();
            if ($pid > 0) {
                $stmt = $pdo->prepare(
                    'SELECT id, name, level FROM player WHERE id = ? AND account_id = ? LIMIT 1'
                );
                $stmt->execute([$pid, $accountId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($row)) {
                    return [
                        'id' => (int) $row['id'],
                        'name' => (string) $row['name'],
                        'level' => (int) $row['level'],
                    ];
                }
            }
            if ($charName !== '') {
                $stmt = $pdo->prepare(
                    'SELECT id, name, level FROM player WHERE account_id = ? AND name = ? LIMIT 1'
                );
                $stmt->execute([$accountId, $charName]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($row)) {
                    return [
                        'id' => (int) $row['id'],
                        'name' => (string) $row['name'],
                        'level' => (int) $row['level'],
                    ];
                }
            }
        } catch (\Throwable) {
            return null;
        }
        return null;
    }
}
