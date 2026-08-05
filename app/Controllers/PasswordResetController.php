<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Core\Theme;
use App\Services\PasswordResetService;

final class PasswordResetController
{
    public function forgot(): void
    {
        Security::requireCsrf('login');
        $login = (string) ($_POST['login'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $result = PasswordResetService::requestForgot($login, $email);
        Session::flash('open_forgot', true);
        if (!empty($result['ok'])) {
            Session::flash('forgot_success', 'Eşleşme doğruysa sıfırlama bağlantısı e-postana gönderildi.');
        } else {
            Session::flash('forgot_errors', $result['errors'] ?: ['İşlem başarısız.']);
            Session::flash('forgot_old', ['login' => $login, 'email' => $email]);
        }
        redirect('/');
    }

    public function showReset(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        Theme::render('auth/reset_password', [
            'token' => $token,
            'resetErrors' => Session::flash('reset_errors') ?? [],
            'resetSuccess' => Session::flash('reset_success'),
        ]);
    }

    public function reset(): void
    {
        Security::requireCsrf('login');
        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $result = PasswordResetService::consumeToken($token, $password, $confirm);
        if (!empty($result['ok'])) {
            Session::flash('login_success', 'Şifren güncellendi. Yeni şifrenle giriş yapabilirsin.');
            Session::flash('open_login', true);
            redirect('/');
        }
        Session::flash('reset_errors', $result['errors'] ?: ['Şifre sıfırlanamadı.']);
        redirect('/sifre-sifirla?token=' . urlencode($token));
    }
}
