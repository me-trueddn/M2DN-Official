<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use PDO;

final class AccountService
{
    /**
     * @return array{ok:bool, errors:list<string>, account_id?:int}
     */
    public static function register(
        string $login,
        string $password,
        string $email,
        string $securityCode,
        bool $acceptRules = false,
        string $passwordConfirm = ''
    ): array {
        $errors = [];

        $login = trim($login);
        $password = trim($password);
        $passwordConfirm = trim($passwordConfirm);
        $email = trim($email);
        $securityCode = trim($securityCode);

        if ($login === '' || !preg_match('/^[a-zA-Z0-9_]{4,16}$/', $login)) {
            $errors[] = 'Kullanıcı adı 4–16 karakter olmalı (harf, rakam, _).';
        }

        if ($password === '' || strlen($password) < 4 || strlen($password) > 16) {
            $errors[] = 'Parola 4–16 karakter olmalı.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Parola ile parola tekrarı eşleşmiyor.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 64) {
            $errors[] = 'Geçerli bir e-posta girin.';
        }

        if ($securityCode === '' || !preg_match('/^\d{1,6}$/', $securityCode)) {
            $errors[] = 'Güvenli şifre en fazla 6 haneli ve sadece sayı olmalı.';
        }

        if (!$acceptRules) {
            $errors[] = 'Kayıt için Topluluk Kurallarını kabul etmelisin.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $pdo = Database::account();

        $check = $pdo->prepare('SELECT id FROM account WHERE login = ? LIMIT 1');
        $check->execute([$login]);
        if ($check->fetch()) {
            return ['ok' => false, 'errors' => ['Bu kullanıcı adı zaten kayıtlı.']];
        }

        $emailCheck = $pdo->prepare('SELECT id FROM account WHERE email = ? LIMIT 1');
        $emailCheck->execute([$email]);
        if ($emailCheck->fetch()) {
            return ['ok' => false, 'errors' => ['Bu e-posta zaten kullanılıyor.']];
        }

        $socialId = self::generateSocialId($pdo);
        $passwordHash = Security::hashAccountPassword($password);
        $securityHash = Security::hashAccountPassword($securityCode);
        $ip = Security::clientIp();

        $stmt = $pdo->prepare(
            "INSERT INTO account
                (login, password, social_id, email, create_time, is_testor, status, WebPermission, securitycode,
                 newsletter, empire, name_checked, availDt, mileage, cash,
                 gold_expire, silver_expire, safebox_expire, autoloot_expire,
                 fish_mind_expire, marriage_fast_expire, money_drop_rate_expire,
                 total_cash, total_mileage, channel_company, ip)
             VALUES
                (?, ?, ?, ?, NOW(), 0, 'OK', 0, ?,
                 0, 0, 0, '0000-00-00 00:00:00', 0, 0,
                 '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00',
                 '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00',
                 0, 0, 'M2DN', ?)"
        );

        $stmt->execute([
            $login,
            $passwordHash,
            $socialId,
            $email,
            $securityHash,
            $ip,
        ]);

        $accountId = (int) $pdo->lastInsertId();
        ActivityLogService::log($accountId, ActivityLogService::ACTION_REGISTER, 'Yeni hesap kaydı', $login);
        AccountConsentService::recordRulesAccepted($accountId);

        try {
            MailService::sendTemplate('register', $email, $login, [
                'login' => $login,
                'email' => $email,
                'link' => rtrim((string) \App\Core\Config::get('app.url', ''), '/'),
            ]);
        } catch (\Throwable) {
            // ignore
        }

        return [
            'ok' => true,
            'errors' => [],
            'account_id' => $accountId,
        ];
    }

    private static function generateSocialId(PDO $pdo): string
    {
        for ($i = 0; $i < 20; $i++) {
            $id = (string) random_int(1000000, 9999999);
            $stmt = $pdo->prepare('SELECT id FROM account WHERE social_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $id;
            }
        }

        return (string) random_int(1000000, 9999999);
    }
}
