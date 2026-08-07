<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AccountService;
use App\Services\AuthService;
use App\Services\CaptchaService;

final class AuthController
{
    public function showRegister(): void
    {
        Session::flash('open_register', true);
        redirect('/');
    }

    public function showLogin(): void
    {
        Session::flash('open_login', true);
        redirect('/');
    }

    public function register(): void
    {
        Security::requireCsrf('register');

        $login = (string) ($_POST['login'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $securityCode = (string) ($_POST['securitycode'] ?? '');
        $acceptRules = !empty($_POST['accept_rules']);

        Session::flash('open_register', true);
        Session::flash('register_old', [
            'login' => $login,
            'email' => $email,
            'accept_rules' => $acceptRules ? '1' : '',
        ]);

        $captcha = CaptchaService::verifyRequest();
        if (empty($captcha['ok'])) {
            Session::flash('register_errors', $captcha['errors'] ?: ['Doğrulama başarısız.']);
            redirect('/');
        }

        $result = AccountService::register($login, $password, $email, $securityCode, $acceptRules, $passwordConfirm);

        if (!$result['ok']) {
            Session::flash('register_errors', $result['errors']);
            redirect('/');
        }

        Session::flash('register_success', 'Hesabın oluşturuldu. Artık giriş yapabilirsin.');
        redirect('/');
    }

    public function login(): void
    {
        Security::requireCsrf('login');

        $login = (string) ($_POST['login'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $captcha = CaptchaService::verifyRequest();
        if (empty($captcha['ok'])) {
            Session::flash('login_errors', $captcha['errors'] ?: ['Doğrulama başarısız.']);
            Session::flash('login_old', ['login' => $login]);
            Session::flash('open_login', true);
            redirect('/');
        }

        $result = AuthService::login($login, $password);

        if (!empty($result['needs_2fa'])) {
            Session::flash('open_2fa', true);
            redirect('/');
        }

        if (!$result['ok']) {
            Session::flash('login_errors', $result['errors']);
            Session::flash('login_old', ['login' => $login]);
            Session::flash('open_login', true);
            redirect('/');
        }

        // Varsayılan: oyuncu paneli (admin yetkisi panel içinden)
        redirect('/panel');
    }

    public function twoFactor(): void
    {
        Security::requireCsrf('login');

        $code = (string) ($_POST['code'] ?? '');
        $result = AuthService::completeTwoFactor($code);

        if (!$result['ok']) {
            Session::flash('login_errors', $result['errors']);
            Session::flash('open_2fa', true);
            redirect('/');
        }

        redirect('/panel');
    }

    public function logout(): void
    {
        AuthService::logout();
        Session::flash('login_success', 'Çıkış yapıldı.');
        redirect('/');
    }
}
