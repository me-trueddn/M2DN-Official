<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var array $rates */
/** @var array $currentServer */
/** @var string $csrf */
/** @var mixed $registerErrors */
/** @var mixed $registerOld */
/** @var mixed $registerSuccess */
/** @var mixed $openRegister */
/** @var mixed $loginErrors */
/** @var mixed $loginOld */
/** @var mixed $loginSuccess */
/** @var mixed $openLogin */
/** @var mixed $open2fa */
/** @var mixed $openForgot */
/** @var mixed $forgotErrors */
/** @var mixed $forgotOld */
/** @var mixed $forgotSuccess */
/** @var array|null $authUser */
/** @var string $appVersion */
/** @var list<array> $siteFeatures */
/** @var list<array> $siteClasses */
/** @var list<array> $siteDownloads */
/** @var list<array> $siteGallery */
/** @var list<array> $siteSocials */
/** @var array $siteFooterLinks */
/** @var array $siteFooter */
/** @var array $nextChapter */
/** @var array|null $homeAnnouncement */
/** @var array $siteBrand */

$registerErrors = is_array($registerErrors ?? null) ? $registerErrors : [];
$registerOld = is_array($registerOld ?? null) ? $registerOld : [];
$registerSuccess = is_string($registerSuccess ?? null) ? $registerSuccess : null;
$openRegister = !empty($openRegister) || $registerErrors !== [] || $registerSuccess !== null;

$loginErrors = is_array($loginErrors ?? null) ? $loginErrors : [];
$loginOld = is_array($loginOld ?? null) ? $loginOld : [];
$loginSuccess = is_string($loginSuccess ?? null) ? $loginSuccess : null;
$open2fa = !empty($open2fa);
$forgotErrors = is_array($forgotErrors ?? null) ? $forgotErrors : [];
$forgotOld = is_array($forgotOld ?? null) ? $forgotOld : [];
$forgotSuccess = is_string($forgotSuccess ?? null) ? $forgotSuccess : null;
$openForgot = !empty($openForgot) || $forgotErrors !== [] || $forgotSuccess !== null;
$openLogin = (!$open2fa && !$openForgot) && (!empty($openLogin) || $loginErrors !== [] || $loginSuccess !== null);
$authUser = is_array($authUser ?? null) ? $authUser : null;
$appVersion = (string) ($appVersion ?? '1.10.2');
$siteFeatures = is_array($siteFeatures ?? null) ? $siteFeatures : [];
$siteClasses = is_array($siteClasses ?? null) ? $siteClasses : [];
$siteDownloads = is_array($siteDownloads ?? null) ? $siteDownloads : [];
$siteGallery = is_array($siteGallery ?? null) ? $siteGallery : [];
$siteSocials = is_array($siteSocials ?? null) ? $siteSocials : [];
$siteFooterLinks = is_array($siteFooterLinks ?? null) ? $siteFooterLinks : [];
$siteFooter = is_array($siteFooter ?? null) ? $siteFooter : [];
$nextChapter = is_array($nextChapter ?? null) ? $nextChapter : [];
$homeAnnouncement = is_array($homeAnnouncement ?? null) ? $homeAnnouncement : null;
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandLogo = (string) ($siteBrand['logo_url'] ?? asset('img/logo-nav.svg'));
$brandHomeSize = (int) ($siteBrand['home_size'] ?? 48);
$isLoggedIn = $authUser !== null;
$canAdmin = $isLoggedIn && \App\Services\AuthService::canAccessAdmin($authUser);
$captchaEnabled = !empty($captchaEnabled);
$captchaWidget = isset($captchaWidget) && is_string($captchaWidget) ? $captchaWidget : '';
$captchaScripts = isset($captchaScripts) && is_string($captchaScripts) ? $captchaScripts : '';

$mediaUrl = static function (string $path): string {
    $path = trim($path);
    if ($path === '' || $path === '#') {
        return '#';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
        return $path;
    }
    return asset($path);
};
$footerHref = static function (string $url): string {
    $url = trim($url);
    if ($url === '' || $url === '#') {
        return '#';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:')) {
        return $url;
    }
    if (str_starts_with($url, '#')) {
        return url('/') . $url;
    }
    return url($url);
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($appName) ?> | <?= e($appTagline) ?></title>
<link rel="icon" href="<?= e($brandIcon) ?>">
<link rel="shortcut icon" href="<?= e($brandIcon) ?>">
<link rel="apple-touch-icon" href="<?= e($brandIcon) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;900&family=Ma+Shan+Zheng&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root{
    --obsidian:#0b0906;
    --obsidian-2:#161009;
    --obsidian-3:#1f160d;
    --blood:#8f1c29;
    --blood-light:#c53347;
    --gold:#c9974a;
    --gold-light:#eccd8e;
    --jade:#33594a;
    --parchment:#e9dfc6;
    --ash:#9a8f80;
    --font-display:'Cinzel', serif;
    --font-brush:'Ma Shan Zheng', cursive;
    --font-body:'Inter', sans-serif;
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    background:var(--obsidian);
    color:var(--parchment);
    font-family:var(--font-body);
    overflow-x:hidden;
    position:relative;
  }

  /* subtle grain texture */
  body::before{
    content:"";
    position:fixed; inset:0;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events:none;
    z-index:1000;
    mix-blend-mode:overlay;
  }

  ::selection{background:var(--blood); color:var(--gold-light);}

  a{color:inherit; text-decoration:none;}
  ul{list-style:none;}
  img{max-width:100%; display:block;}

  .eyebrow{
    font-family:var(--font-brush);
    font-size:1.4rem;
    color:var(--blood-light);
    letter-spacing:.05em;
    display:inline-flex;
    align-items:center;
    gap:.6rem;
    margin-bottom:.6rem;
  }
  .eyebrow::before, .eyebrow::after{
    content:"";
    width:28px; height:1px;
    background:linear-gradient(90deg, transparent, var(--gold));
  }
  .eyebrow::after{transform:scaleX(-1);}

  h1,h2,h3{font-family:var(--font-display); letter-spacing:.02em;}

  .container{max-width:1180px; margin:0 auto; padding:0 24px;}

  /* ---------- NAV ---------- */
  header{
    position:fixed; top:0; left:0; right:0; z-index:500;
    padding:22px 0;
    transition:padding .35s ease, background .35s ease, box-shadow .35s ease;
    background:linear-gradient(180deg, rgba(11,9,6,.85), transparent);
  }
  header.scrolled{
    padding:12px 0;
    background:rgba(11,9,6,.92);
    backdrop-filter:blur(10px);
    box-shadow:0 8px 30px rgba(0,0,0,.5);
    border-bottom:1px solid rgba(201,151,74,.15);
  }
  nav{display:flex; align-items:center; justify-content:space-between;}
  .brand{
    display:flex; align-items:center; gap:0;
    font-family:var(--font-display); font-weight:900; font-size:1.4rem;
    letter-spacing:.12em; color:var(--gold-light);
    line-height:0;
  }
  .brand:hover{opacity:.92;}
  .brand-logo{
    height:<?= $brandHomeSize ?>px; width:auto; display:block; object-fit:contain;
    filter:drop-shadow(0 2px 10px rgba(0,0,0,.35));
  }
  .brand-mark{
    height:42px; width:42px; display:block;
    filter:drop-shadow(0 2px 8px rgba(0,0,0,.35));
  }
  .footer-brand .brand-logo{height:<?= max(24, (int) round($brandHomeSize * 1.15)) ?>px;}
  .footer-brand .brand{margin-bottom:4px;}

  .nav-links{display:flex; gap:34px; font-size:.92rem; font-weight:600; letter-spacing:.03em; text-transform:uppercase;}
  .nav-links a{position:relative; padding:4px 0; color:var(--ash); transition:color .25s;}
  .nav-links a:hover, .nav-links a:focus-visible{color:var(--gold-light);}
  .nav-links a::after{
    content:""; position:absolute; left:0; bottom:-2px; width:0; height:1px; background:var(--blood-light);
    transition:width .3s ease;
  }
  .nav-links a:hover::after{width:100%;}

  .nav-cta{
    display:flex; align-items:center; gap:8px;
    padding:10px 20px;
    border:1px solid var(--gold);
    color:var(--gold-light);
    font-weight:700; font-size:.85rem; letter-spacing:.06em; text-transform:uppercase;
    transition:background .3s, color .3s;
    clip-path:polygon(8px 0,100% 0,100% calc(100% - 8px),calc(100% - 8px) 100%,0 100%,0 8px);
  }
  .nav-cta:hover{background:var(--gold); color:var(--obsidian);}
  .nav-cta.solid{
    background:linear-gradient(135deg, var(--blood-light), var(--blood));
    border-color:transparent; color:var(--parchment);
  }
  .nav-cta.solid:hover{background:var(--blood); color:var(--parchment);}
  .nav-actions{display:flex; align-items:center; gap:10px;}
  button.nav-cta{cursor:pointer; font-family:inherit; background:transparent;}

  .menu-toggle{display:none; background:none; border:none; color:var(--gold-light); font-size:1.5rem; cursor:pointer;}

  /* ---------- REGISTER MODAL ---------- */
  .modal-overlay{
    position:fixed; inset:0; z-index:900;
    background:rgba(0,0,0,.65); backdrop-filter:blur(4px);
    display:none; align-items:center; justify-content:center;
    padding:20px;
  }
  .modal-overlay.open{display:flex;}
  .modal-card{
    width:100%; max-width:420px;
    background:var(--obsidian-2);
    border:1px solid rgba(201,151,74,.25);
    padding:28px 26px 22px;
    clip-path:polygon(14px 0,100% 0,100% calc(100% - 14px),calc(100% - 14px) 100%,0 100%,0 14px);
    position:relative;
    max-height:min(92vh, 640px); overflow-y:auto;
    animation:modalIn .28s ease;
  }
  @keyframes modalIn{
    from{opacity:0; transform:translateY(12px) scale(.98);}
    to{opacity:1; transform:none;}
  }
  .modal-close{
    position:absolute; top:14px; right:14px;
    width:34px; height:34px; border:1px solid rgba(201,151,74,.2);
    background:var(--obsidian); color:var(--gold-light);
    display:flex; align-items:center; justify-content:center; cursor:pointer;
  }
  .modal-close:hover{border-color:var(--gold); color:var(--parchment);}
  .modal-card .eyebrow{margin-bottom:6px;}
  .modal-card h2{font-size:1.4rem; color:var(--parchment); margin-bottom:6px;}
  .modal-card .sub{color:var(--ash); font-size:.88rem; line-height:1.55; margin-bottom:18px;}
  .modal-form .form-row{margin-bottom:14px;}
  .modal-form label{
    display:block; font-size:.7rem; text-transform:uppercase; letter-spacing:.08em;
    color:var(--ash); margin-bottom:7px;
  }
  .modal-form input{
    width:100%; background:var(--obsidian); border:1px solid rgba(201,151,74,.15);
    padding:11px 13px; color:var(--parchment); font-size:.9rem; outline:none; font-family:inherit;
  }
  .modal-form input:focus{border-color:var(--gold);}
  .modal-form .hint{font-size:.7rem; color:var(--ash); margin-top:5px;}
  .modal-form .captcha-wrap{margin:4px 0 16px; min-height:78px;}
  .modal-form .rules-accept{display:flex;align-items:flex-start;gap:10px;margin:4px 0 16px;font-size:.85rem;color:var(--ash);line-height:1.45;}
  .modal-form .rules-accept input{width:auto;margin-top:3px;flex-shrink:0;}
  .modal-form .rules-accept a{color:var(--gold-light);text-decoration:underline;}
  .modal-form .btn{
    width:100%; justify-content:center; margin-top:6px; border:none; cursor:pointer; font-family:inherit;
  }
  .modal-alert{
    padding:10px 12px; font-size:.82rem; line-height:1.45; margin-bottom:12px;
    border:1px solid rgba(201,151,74,.15);
  }
  .modal-alert.error{background:rgba(143,28,41,.18); border-color:rgba(197,51,71,.35);}
  .modal-alert.success{background:rgba(51,89,74,.2); border-color:rgba(79,138,113,.35); color:#4f8a71;}
  .modal-alert ul{margin:4px 0 0 16px;}
  body.modal-open{overflow:hidden;}
  .modal-card.modal-ann{
    max-width:560px;
  }
  .modal-card.modal-ann h2{
    font-family:var(--font-display); font-size:1.35rem; color:var(--gold-light);
    line-height:1.3; margin:8px 0 14px;
  }
  .modal-ann-meta{
    display:flex; flex-wrap:wrap; gap:8px 12px; align-items:center;
    margin-bottom:6px; font-size:.72rem; color:var(--ash);
  }
  .modal-ann-meta .ann-type{
    padding:3px 9px; border:1px solid rgba(201,151,74,.28); background:rgba(201,151,74,.1);
    color:var(--gold-light); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em;
  }
  .modal-ann-body{
    font-size:.9rem; line-height:1.7; color:var(--parchment);
    max-height:min(48vh, 360px); overflow:auto; margin-bottom:18px;
    padding-top:12px; border-top:1px solid rgba(201,151,74,.12);
  }
  .modal-ann-body p{margin:0 0 .7em;}
  .modal-ann-body p:last-child{margin-bottom:0;}
  .modal-ann-body a{color:var(--gold-light);}
  .modal-ann-body ul,.modal-ann-body ol{margin:0 0 .7em 1.2em; list-style:disc;}
  .modal-ann-body ol{list-style:decimal;}
  .modal-ann-body table{width:100%; border-collapse:collapse; margin:.5em 0;}
  .modal-ann-body th,.modal-ann-body td{border:1px solid rgba(201,151,74,.2); padding:6px 8px;}
  .modal-ann-actions{display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;}
  .modal-ann-actions .btn{min-width:110px; justify-content:center;}

  /* ---------- HERO ---------- */
  .hero{
    position:relative;
    min-height:100vh;
    display:flex;
    align-items:center;
    padding:140px 0 80px;
    background:
      radial-gradient(ellipse 900px 600px at 78% 20%, rgba(143,28,41,.22), transparent 60%),
      radial-gradient(ellipse 700px 500px at 15% 85%, rgba(51,89,74,.18), transparent 60%),
      var(--obsidian);
    overflow:hidden;
  }

  .dragon-wrap{
    position:absolute;
    right:-8%; top:50%; transform:translateY(-50%);
    width:min(60vw, 780px);
    opacity:.9;
    pointer-events:none;
    filter:drop-shadow(0 0 40px rgba(197,51,71,.15));
  }
  .dragon-path{
    fill:none;
    stroke:url(#dragonGrad);
    stroke-width:2.4;
    stroke-linecap:round;
    stroke-dasharray:3400;
    stroke-dashoffset:3400;
    animation:draw 3.4s cubic-bezier(.65,0,.35,1) forwards .3s;
  }
  .dragon-fill{
    fill:rgba(201,151,74,.05);
    stroke:none;
  }
  @keyframes draw{ to{stroke-dashoffset:0;} }

  .ember{
    position:absolute; bottom:-10px;
    width:4px; height:4px; border-radius:50%;
    background:var(--blood-light);
    box-shadow:0 0 8px 2px rgba(197,51,71,.6);
    animation:rise linear infinite;
    opacity:0;
  }
  @keyframes rise{
    0%{opacity:0; transform:translateY(0) translateX(0);}
    10%{opacity:.8;}
    90%{opacity:.4;}
    100%{opacity:0; transform:translateY(-100vh) translateX(30px);}
  }

  .hero-content{position:relative; z-index:2; max-width:660px;}
  .hero-content h1{
    font-size:clamp(2.8rem, 6vw, 4.6rem);
    line-height:1.05;
    color:var(--parchment);
    text-shadow:0 4px 30px rgba(0,0,0,.6);
    margin-bottom:22px;
  }
  .hero-content h1 em{
    font-style:normal;
    color:var(--blood-light);
    background:linear-gradient(180deg, var(--gold-light), var(--blood-light));
    -webkit-background-clip:text;
    background-clip:text;
    -webkit-text-fill-color:transparent;
  }
  .hero-content p{
    font-size:1.08rem;
    color:var(--ash);
    max-width:480px;
    margin-bottom:36px;
    line-height:1.7;
  }

  .hero-ctas{display:flex; gap:18px; flex-wrap:wrap; margin-bottom:56px;}
  .btn{
    padding:16px 30px;
    font-weight:700; font-size:.9rem; letter-spacing:.05em; text-transform:uppercase;
    display:inline-flex; align-items:center; gap:10px;
    transition:transform .25s, box-shadow .25s, background .25s;
    clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);
    border:none; cursor:pointer; font-family:inherit; color:inherit; background:transparent;
  }
  .btn-primary{background:linear-gradient(135deg, var(--blood-light), var(--blood)); color:var(--parchment); box-shadow:0 10px 30px rgba(143,28,41,.35);}
  .btn-primary:hover{transform:translateY(-3px); box-shadow:0 14px 36px rgba(143,28,41,.5);}
  .btn-ghost{border:1px solid rgba(201,151,74,.5); color:var(--gold-light);}
  .btn-ghost:hover{background:rgba(201,151,74,.1); transform:translateY(-3px);}

  .hero-stats{
    display:flex; gap:0; flex-wrap:wrap;
    border-top:1px solid rgba(201,151,74,.2);
    padding-top:26px;
  }
  .hero-stats div{padding-right:40px; margin-right:40px; border-right:1px solid rgba(201,151,74,.15);}
  .hero-stats div:last-child{border-right:none;}
  .hero-stats strong{
    display:block; font-family:var(--font-display); font-size:1.7rem; color:var(--gold-light);
  }
  .hero-stats span{font-size:.78rem; color:var(--ash); text-transform:uppercase; letter-spacing:.08em;}

  .scroll-cue{
    position:absolute; bottom:34px; left:50%; transform:translateX(-50%);
    display:flex; flex-direction:column; align-items:center; gap:8px;
    color:var(--ash); font-size:.72rem; letter-spacing:.15em; text-transform:uppercase;
    z-index:2;
  }
  .scroll-cue .line{width:1px; height:36px; background:linear-gradient(var(--gold), transparent); animation:pulse 2s ease-in-out infinite;}
  @keyframes pulse{ 0%,100%{opacity:.3;} 50%{opacity:1;} }

  /* ---------- SECTION SHARED ---------- */
  section{position:relative; padding:120px 0;}
  .section-head{max-width:640px; margin-bottom:64px;}
  .section-head h2{font-size:clamp(2rem, 3.4vw, 2.8rem); color:var(--parchment);}
  .section-head p{color:var(--ash); margin-top:16px; font-size:1.02rem; line-height:1.7;}

  /* divider */
  .divider{
    display:flex; align-items:center; justify-content:center; gap:16px;
    color:var(--gold); font-family:var(--font-brush); font-size:1.6rem;
    margin:0 auto;
  }
  .divider::before, .divider::after{content:""; height:1px; width:120px; background:linear-gradient(90deg, transparent, rgba(201,151,74,.5), transparent);}

  /* ---------- FEATURES ---------- */
  .features-grid{
    display:grid; grid-template-columns:repeat(3, 1fr); gap:1px;
    background:rgba(201,151,74,.15);
    border:1px solid rgba(201,151,74,.15);
  }
  .feature{
    background:var(--obsidian-2);
    padding:40px 34px;
    transition:background .3s;
  }
  .feature:hover{background:var(--obsidian-3);}
  .feature i{font-size:1.5rem; color:var(--blood-light); margin-bottom:18px;}
  .feature h3{font-size:1.15rem; color:var(--gold-light); margin-bottom:10px; font-family:var(--font-display); font-weight:600;}
  .feature p{color:var(--ash); font-size:.92rem; line-height:1.65;}

  /* ---------- CLASSES ---------- */
  .classes{background:var(--obsidian-2); border-top:1px solid rgba(201,151,74,.12); border-bottom:1px solid rgba(201,151,74,.12);}
  .class-grid{display:grid; grid-template-columns:repeat(4, 1fr); gap:22px;}
  .class-card{
    position:relative;
    height:440px;
    padding:30px 26px;
    display:flex; flex-direction:column; justify-content:flex-end;
    overflow:hidden;
    border:1px solid rgba(201,151,74,.18);
    background:var(--obsidian);
    transition:transform .4s ease, border-color .4s;
  }
  .class-card:hover{transform:translateY(-8px); border-color:var(--gold);}
  .class-card .glow{
    position:absolute; inset:0;
    opacity:.5;
    background:radial-gradient(circle at 50% 20%, var(--glow-color, var(--blood)), transparent 65%);
    transition:opacity .4s;
  }
  .class-card:hover .glow{opacity:.85;}
  .class-card .rank{
    font-family:var(--font-brush); font-size:2.6rem; color:rgba(233,223,198,.12);
    position:absolute; top:20px; right:24px; z-index:1; pointer-events:none;
  }
  .class-card .class-visual{
    position:absolute; left:0; right:0; top:0; height:58%;
    display:flex; align-items:flex-end; justify-content:center;
    z-index:1; pointer-events:none; overflow:hidden;
  }
  .class-card .class-visual img{
    max-height:100%; max-width:78%; width:auto; height:auto;
    object-fit:contain; object-position:center bottom; display:block;
    filter:drop-shadow(0 8px 18px rgba(0,0,0,.45));
  }
  .class-card i.class-icon{font-size:1.35rem; color:var(--gold-light); margin-bottom:10px; position:relative; z-index:2;}
  .class-card h3{position:relative; z-index:2; font-size:1.3rem; color:var(--parchment); margin-bottom:8px;}
  .class-card p{position:relative; z-index:2; font-size:.85rem; color:var(--ash); line-height:1.6; margin-bottom:18px;}
  .nav-user{position:relative;}
  .nav-user-btn{display:inline-flex; align-items:center; gap:8px;}
  .nav-user-menu{
    position:absolute; right:0; top:calc(100% + 8px); min-width:200px;
    background:var(--obsidian-2); border:1px solid rgba(201,151,74,.28); padding:8px;
    display:none; z-index:50; box-shadow:0 16px 40px rgba(0,0,0,.45);
  }
  .nav-user-menu.open{display:block;}
  .nav-user-menu a{
    display:flex; align-items:center; gap:10px; padding:10px 12px; color:var(--parchment);
    font-size:.82rem; text-decoration:none;
  }
  .nav-user-menu a:hover{background:rgba(201,151,74,.08); color:var(--gold-light);}
  .session-timer-home{
    display:inline-flex; align-items:center; gap:6px; margin-right:8px;
    padding:7px 10px; border:1px solid rgba(201,151,74,.28); background:rgba(201,151,74,.1);
    font-size:.72rem; color:var(--gold-light); font-variant-numeric:tabular-nums;
  }
  .session-timer-home.warn{border-color:rgba(197,51,71,.4); color:#e8a0a8; background:rgba(143,28,41,.18);}
  .download-list{display:flex; flex-direction:column; gap:14px; align-items:center; margin-top:22px;}
  .download-list .dl-item{text-align:center;}
  .download-list .dl-meta{font-size:.78rem; color:var(--ash); margin-top:6px;}
  .gallery-item{position:relative; overflow:hidden; min-height:160px; background:var(--obsidian-2); border:1px solid rgba(201,151,74,.12); display:flex; align-items:flex-end; justify-content:center;}
  .gallery-item img{position:absolute; inset:0; width:100%; height:100%; object-fit:cover;}
  .gallery-item span{position:relative; z-index:1; width:100%; padding:10px 12px; background:linear-gradient(transparent, rgba(0,0,0,.75)); color:var(--parchment); font-size:.82rem; text-align:center;}
  .ver-tag{opacity:.75; margin-left:8px; font-size:.75rem;}
  .stat-row{position:relative; z-index:2; display:flex; align-items:center; gap:10px; margin-bottom:8px; font-size:.72rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em;}
  .stat-row .track{flex:1; height:4px; background:rgba(233,223,198,.1); position:relative; overflow:hidden;}
  .stat-row .fill{position:absolute; inset:0; width:0; background:var(--gold); transition:width 1.2s ease;}
  .stat-row span{width:56px; flex-shrink:0;}

  /* ---------- RATES ---------- */
  .rates{display:grid; grid-template-columns:1.1fr .9fr; gap:70px; align-items:center;}
  .rate-item{margin-bottom:30px;}
  .rate-item .label{display:flex; justify-content:space-between; font-size:.85rem; text-transform:uppercase; letter-spacing:.05em; color:var(--ash); margin-bottom:10px;}
  .rate-item .label b{color:var(--gold-light); font-family:var(--font-display); font-size:1rem;}
  .rate-bar{height:8px; background:rgba(233,223,198,.08); position:relative; clip-path:polygon(4px 0,100% 0,100% calc(100% - 4px),calc(100% - 4px) 100%,0 100%,0 4px);}
  .rate-fill{position:absolute; inset:0; width:0; background:linear-gradient(90deg, var(--jade), var(--gold)); transition:width 1.6s cubic-bezier(.16,1,.3,1);}

  .countdown-card{
    border:1px solid rgba(201,151,74,.25);
    padding:44px 36px;
    background:linear-gradient(160deg, rgba(143,28,41,.08), rgba(51,89,74,.06));
    text-align:center;
  }
  .countdown-card .eyebrow{justify-content:center;}
  .countdown-card h3{font-size:1.5rem; color:var(--parchment); margin-bottom:26px;}
  .countdown{display:flex; justify-content:center; gap:18px;}
  .countdown div{display:flex; flex-direction:column; align-items:center;}
  .countdown strong{font-family:var(--font-display); font-size:2.1rem; color:var(--gold-light);}
  .countdown span{font-size:.68rem; color:var(--ash); text-transform:uppercase; letter-spacing:.1em; margin-top:4px;}
  .countdown .sep{font-size:2.1rem; color:rgba(201,151,74,.3); align-self:flex-start; padding-top:2px;}

  /* ---------- GALLERY ---------- */
  .gallery-grid{display:grid; grid-template-columns:repeat(4, 1fr); grid-auto-rows:180px; gap:14px;}
  .gallery-grid .g1{grid-column:span 2; grid-row:span 2;}
  .gallery-item{
    position:relative; overflow:hidden;
    background:linear-gradient(145deg, var(--obsidian-3), var(--obsidian-2));
    border:1px solid rgba(201,151,74,.14);
    display:flex; align-items:flex-end; padding:18px;
  }
  .gallery-item::before{
    content:""; position:absolute; inset:0;
    background:radial-gradient(circle at 30% 20%, rgba(197,51,71,.25), transparent 60%);
    opacity:0; transition:opacity .4s;
  }
  .gallery-item:hover::before{opacity:1;}
  .gallery-item span{position:relative; z-index:2; font-family:var(--font-display); font-size:.82rem; letter-spacing:.06em; color:var(--gold-light); text-transform:uppercase;}
  .gallery-item i{position:absolute; top:18px; right:18px; color:rgba(233,223,198,.25); font-size:1.3rem;}

  /* ---------- CTA BANNER ---------- */
  .cta-banner{
    background:linear-gradient(120deg, var(--blood), #6d151f 60%, var(--obsidian));
    padding:80px 0;
    text-align:center;
  }
  .cta-banner h2{font-size:clamp(1.9rem, 3.6vw, 2.6rem); color:var(--parchment); margin-bottom:18px;}
  .cta-banner p{color:rgba(233,223,198,.75); margin-bottom:36px; font-size:1.02rem;}
  .cta-banner .btn-primary{background:var(--gold); color:var(--obsidian); box-shadow:none;}
  .cta-banner .btn-primary:hover{background:var(--gold-light);}

  /* ---------- FOOTER ---------- */
  footer{padding:70px 0 30px; background:var(--obsidian-2); border-top:1px solid rgba(201,151,74,.12);}
  .footer-top{display:flex; justify-content:space-between; flex-wrap:wrap; gap:40px; padding-bottom:50px; border-bottom:1px solid rgba(201,151,74,.1);}
  .footer-brand p{color:var(--ash); max-width:320px; margin-top:14px; font-size:.9rem; line-height:1.7;}
  .footer-cols{display:flex; gap:70px;}
  .footer-col h4{font-size:.78rem; text-transform:uppercase; letter-spacing:.1em; color:var(--gold-light); margin-bottom:16px;}
  .footer-col a{display:block; color:var(--ash); font-size:.9rem; margin-bottom:10px; transition:color .25s;}
  .footer-col a:hover{color:var(--gold-light);}
  .footer-bottom{display:flex; justify-content:space-between; align-items:center; padding-top:26px; flex-wrap:wrap; gap:16px;}
  .footer-bottom p{color:var(--ash); font-size:.8rem;}
  .socials{display:flex; gap:14px;}
  .socials a{width:38px; height:38px; border:1px solid rgba(201,151,74,.25); display:flex; align-items:center; justify-content:center; color:var(--gold-light); transition:background .25s, transform .25s;}
  .socials a:hover{background:var(--blood); transform:translateY(-3px);}

  /* ---------- RESPONSIVE ---------- */
  @media (max-width:980px){
    .nav-links, .nav-actions{display:none;}
    .menu-toggle{display:block;}
    .features-grid{grid-template-columns:repeat(2,1fr);}
    .class-grid{grid-template-columns:repeat(2,1fr);}
    .rates{grid-template-columns:1fr; gap:50px;}
    .gallery-grid{grid-template-columns:repeat(2,1fr);}
    .gallery-grid .g1{grid-column:span 2;}
    .dragon-wrap{opacity:.35; width:90vw; right:-20%;}
  }
  @media (max-width:600px){
    .features-grid{grid-template-columns:1fr;}
    .class-grid{grid-template-columns:1fr;}
    .hero-stats div{padding-right:24px; margin-right:24px;}
    .footer-cols{gap:36px; flex-wrap:wrap;}
  }

  @media (prefers-reduced-motion: reduce){
    *{animation:none !important; transition:none !important;}
  }
</style>
</head>
<body>

<!-- ================= NAV ================= -->
<header id="siteHeader">
  <div class="container">
    <nav>
      <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e($appName) ?> Anasayfa">
        <img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
      </a>
      <ul class="nav-links">
        <li><a href="#anasayfa">Anasayfa</a></li>
        <li><a href="#ozellikler">Özellikler</a></li>
        <li><a href="#siniflar">Sınıflar</a></li>
        <li><a href="#oranlar">Oranlar</a></li>
        <li><a href="#galeri">Galeri</a></li>
      </ul>
      <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
          <div class="session-timer-home" id="sessionTimer" title="Oturum süresi" data-expires="<?= (int) ($authUser['session_expires_at'] ?? 0) ?>" data-logout="<?= e(url('/cikis')) ?>">
            <i class="fa-solid fa-hourglass-half"></i>
            <span id="sessionTimerValue">--:--</span>
          </div>
          <div class="nav-user" id="navUser">
            <button type="button" class="nav-cta solid nav-user-btn" id="navUserBtn">
              <i class="fa-solid fa-user"></i> <?= e((string) ($authUser['login'] ?? 'Oyuncu')) ?>
              <i class="fa-solid fa-chevron-down" style="font-size:.65rem;opacity:.8;"></i>
            </button>
            <div class="nav-user-menu" id="navUserMenu">
              <a href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-gauge-high"></i> Panele geç</a>
              <?php if ($canAdmin): ?>
                <a href="<?= e(url('/admin')) ?>"><i class="fa-solid fa-screwdriver-wrench"></i> Admin Panel</a>
              <?php endif; ?>
              <a href="<?= e(url('/cikis')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Çıkış</a>
            </div>
          </div>
        <?php else: ?>
          <button type="button" class="nav-cta" id="openLoginModal"><i class="fa-solid fa-right-to-bracket"></i> Giriş</button>
          <button type="button" class="nav-cta solid" id="openRegisterModal"><i class="fa-solid fa-user-plus"></i> Kayıt Ol</button>
        <?php endif; ?>
      </div>
      <button class="menu-toggle" id="menuToggle" aria-label="Menü"><i class="fa-solid fa-bars"></i></button>
    </nav>
  </div>
</header>

<!-- ================= HERO ================= -->
<section class="hero" id="anasayfa">
  <div class="dragon-wrap" aria-hidden="true">
    <svg viewBox="0 0 600 900" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="dragonGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#eccd8e"/>
          <stop offset="55%" stop-color="#c9974a"/>
          <stop offset="100%" stop-color="#8f1c29"/>
        </linearGradient>
      </defs>
      <path class="dragon-fill" d="M470 40c-30 20-40 55-25 80 10 16-4 22-16 14-28-18-60-10-72 16-9 20 4 30 18 26 20-6 30 8 18 24-20 26-14 58 12 70 18 8 20-8 10-20-14-16 0-30 18-22 30 14 58-4 60-34 2-24-16-34-32-24-18 10-30-6-16-22 20-22 10-52-16-60-20-6-24 8-14 20 10 14-4 22-16 10-16-16-40-10-46 10-6 20 10 30 24 20 16-12 32 2 20 20-22 34 4 68 40 66" stroke="none"/>
      <path class="dragon-path" d="M480 60
        C 440 90 430 140 460 175
        C 490 210 470 260 425 260
        C 380 260 360 300 385 335
        C 410 370 385 410 340 405
        C 300 400 270 430 285 465
        C 300 500 270 535 225 525
        C 185 516 155 545 165 580
        C 175 615 150 650 110 640
        C 75 632 45 655 55 690
        C 63 718 45 745 20 760
        M480 60 C 500 45 525 48 535 68 C 545 90 530 108 508 102
        M480 60 L 450 40 M480 60 L 470 30
        M425 260 c 20 -8 20 -30 2 -34
        M340 405 c 20 -6 20 -28 2 -32
        M225 525 c 20 -6 20 -28 2 -32
        M110 640 c 18 -8 16 -30 -2 -34" />
    </svg>
  </div>
  <div class="container">
    <div class="hero-content">
      <div class="eyebrow">傳說 · Bin yıllık M2DN</div>
      <h1>Kılıcını kuşan,<br><em>M2DN</em>'yi yeniden yaz.</h1>
      <p>El yapımı haritalar, sıfır pay-to-win ekonomi ve gece gündüz nabız gibi atan bir topluluk. Metin taşları çağırıyor — sıra sende.</p>
      <div class="hero-ctas">
        <a href="#indir" class="btn btn-primary"><i class="fa-solid fa-download"></i> Oyunu İndir</a>
        <?php if ($isLoggedIn): ?>
          <a href="<?= e(url($canAdmin ? '/admin' : '/panel')) ?>" class="btn btn-ghost"><i class="fa-solid fa-gauge-high"></i> <?= $canAdmin ? 'Admin Panel' : 'Panele Geç' ?></a>
        <?php else: ?>
          <button type="button" class="btn btn-ghost" id="openLoginModalHero"><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap</button>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div><strong style="font-size:1.05rem;">Açık</strong><span>Sunucu Durumu</span></div>
        <div><strong class="counter" data-target="<?= (int)($rates['exp'] ?? 100) ?>">0</strong><span>Exp Oranı</span></div>
        <div><strong class="counter" data-target="<?= (int)($rates['drop'] ?? 50) ?>">0</strong><span>Drop Oranı</span></div>
        <div><strong>2026</strong><span>Kuruluş Yılı</span></div>
      </div>
    </div>
  </div>
  <div class="scroll-cue"><span>Keşfet</span><div class="line"></div></div>
</section>

<!-- ================= FEATURES ================= -->
<section id="ozellikler">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">要素 · Neden M2DN</div>
      <h2>Sunucu değil, bir dünya kurduk.</h2>
      <p>Klasik Metin2 ruhunu koruyup üzerine kendi hikayemizi yazdık. Her güncelleme bir bölüm, her etkinlik bir M2DN macerası.</p>
    </div>
  </div>
  <div class="container">
    <div class="features-grid">
      <?php if ($siteFeatures === []): ?>
        <div class="feature"><i class="fa-solid fa-star"></i><h3>Özellikler</h3><p>Site ayarlarından özellik ekleyebilirsin.</p></div>
      <?php else: ?>
        <?php foreach ($siteFeatures as $feat): ?>
          <div class="feature">
            <i class="<?= e((string) ($feat['icon'] ?? 'fa-solid fa-star')) ?>"></i>
            <h3><?= e((string) ($feat['title'] ?? '')) ?></h3>
            <p><?= e((string) ($feat['body'] ?? '')) ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================= CLASSES ================= -->
<section class="classes" id="siniflar">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">選ぶ · Yolunu Seç</div>
      <h2>Dört yol, tek M2DN.</h2>
      <p>Her sınıfın kendi dövüş felsefesi var. Hangisi seni çağırıyor?</p>
    </div>
    <div class="class-grid">
      <?php foreach ($siteClasses as $cls): ?>
      <div class="class-card" style="--glow-color:<?= e((string) ($cls['glow_color'] ?? '#8f1c29')) ?>">
        <div class="glow"></div>
        <div class="rank"><?= e((string) ($cls['rank_glyph'] ?? '')) ?></div>
        <?php if (!empty($cls['gif_path'])): ?>
          <div class="class-visual"><img src="<?= e($mediaUrl((string) $cls['gif_path'])) ?>" alt="<?= e((string) ($cls['name'] ?? '')) ?>" loading="lazy"></div>
        <?php endif; ?>
        <i class="<?= e((string) ($cls['icon'] ?? 'fa-solid fa-star')) ?> class-icon"></i>
        <h3><?= e((string) ($cls['name'] ?? '')) ?></h3>
        <p><?= e((string) ($cls['body'] ?? '')) ?></p>
        <div class="stat-row"><span><?= e((string) ($cls['stat1_label'] ?? '')) ?></span><div class="track"><div class="fill" data-w="<?= (int) ($cls['stat1_value'] ?? 0) ?>"></div></div></div>
        <div class="stat-row"><span><?= e((string) ($cls['stat2_label'] ?? '')) ?></span><div class="track"><div class="fill" data-w="<?= (int) ($cls['stat2_value'] ?? 0) ?>"></div></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= RATES / COUNTDOWN ================= -->
<section id="oranlar">
  <div class="container">
    <div class="rates">
      <div>
        <div class="eyebrow">比率 · Sunucu Oranları</div>
        <h2 style="margin-bottom:34px; font-size:clamp(1.8rem,3vw,2.4rem);">Dengeli, şeffaf, adil.</h2>

        <div class="rate-item">
          <div class="label"><span>Tecrübe (EXP)</span><b>x<?= (int)($rates['exp'] ?? 100) ?></b></div>
          <div class="rate-bar"><div class="rate-fill" data-w="<?= min(100, max(5, (int) round(((int)($rates['exp'] ?? 100)) / 2))) ?>"></div></div>
        </div>
        <div class="rate-item">
          <div class="label"><span>Eşya Düşme (Drop)</span><b>x<?= (int)($rates['drop'] ?? 50) ?></b></div>
          <div class="rate-bar"><div class="rate-fill" data-w="<?= min(100, max(5, (int)($rates['drop'] ?? 50))) ?>"></div></div>
        </div>
        <div class="rate-item">
          <div class="label"><span>Yang Kazancı</span><b>x<?= (int)($rates['yang'] ?? 30) ?></b></div>
          <div class="rate-bar"><div class="rate-fill" data-w="<?= min(100, max(5, (int)($rates['yang'] ?? 30))) ?>"></div></div>
        </div>
        <div class="rate-item">
          <div class="label"><span>Metin Taşı Yoğunluğu</span><b><?= e((string)($rates['metin_label'] ?? 'Yüksek')) ?></b></div>
          <div class="rate-bar"><div class="rate-fill" data-w="<?= (int)($rates['metin_pct'] ?? 85) ?>"></div></div>
        </div>
      </div>

      <div class="countdown-card">
        <div class="eyebrow">更新 · Sıradaki Bölüm</div>
        <h3><?= e((string) ($nextChapter['title'] ?? 'Yakında')) ?></h3>
        <div class="countdown" id="countdown" data-target="<?= (int) ($nextChapter['target_ts'] ?? 0) ?>">
          <div><strong id="cd-days">00</strong><span>Gün</span></div>
          <div class="sep">:</div>
          <div><strong id="cd-hours">00</strong><span>Saat</span></div>
          <div class="sep">:</div>
          <div><strong id="cd-mins">00</strong><span>Dk</span></div>
          <div class="sep">:</div>
          <div><strong id="cd-secs">00</strong><span>Sn</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= GALLERY ================= -->
<section id="galeri">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">画廊 · Dünyadan Kareler</div>
      <h2>M2DN dünyasından görüntüler.</h2>
    </div>
    <div class="gallery-grid">
      <?php if ($siteGallery === []): ?>
        <div class="gallery-item g1"><i class="fa-solid fa-image" style="position:relative;z-index:1;color:var(--gold-light);font-size:1.4rem;margin:auto;"></i><span>Galeri yakında</span></div>
      <?php else: ?>
        <?php foreach ($siteGallery as $g): ?>
          <div class="gallery-item">
            <img src="<?= e($mediaUrl((string) ($g['image_path'] ?? ''))) ?>" alt="<?= e((string) ($g['title'] ?? '')) ?>" loading="lazy">
            <span><?= e((string) (($g['title'] ?? '') !== '' ? $g['title'] : '—')) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================= CTA BANNER ================= -->
<section class="cta-banner" id="indir">
  <div class="container">
    <h2>M2DN şimdi başlıyor.</h2>
    <p>Karakterini yarat, klanını kur, adını tarihe yaz.</p>
    <div class="download-list">
      <?php if ($siteDownloads === []): ?>
        <a href="#" class="btn btn-primary"><i class="fa-solid fa-download"></i> Ücretsiz İndir</a>
      <?php else: ?>
        <?php foreach ($siteDownloads as $dl): ?>
          <div class="dl-item">
            <a href="<?= e((string) ($dl['url'] ?? '#')) ?>" class="btn btn-primary" <?= str_starts_with((string)($dl['url'] ?? ''), 'http') ? 'target="_blank" rel="noopener"' : '' ?>>
              <i class="fa-solid fa-download"></i> <?= e((string) ($dl['title'] ?? 'İndir')) ?>
            </a>
            <div class="dl-meta">
              <?= e((string) ($dl['pack_type'] ?? 'normal')) ?>
              <?= !empty($dl['description']) ? ' · ' . e((string) $dl['description']) : '' ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e($appName) ?> Anasayfa">
          <img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
        </a>
        <p><?= e((string) ($siteFooter['brand_text'] ?? ($appName . ' — bağımsız Metin2 sunucusu.'))) ?></p>
      </div>
      <div class="footer-cols">
        <div class="footer-col">
          <h4>Sunucu</h4>
          <?php foreach (($siteFooterLinks['server'] ?? []) as $fl): ?>
            <a href="<?= e($footerHref((string) ($fl['url'] ?? '#'))) ?>"><?= e((string) ($fl['label'] ?? '')) ?></a>
          <?php endforeach; ?>
        </div>
        <div class="footer-col">
          <h4>Topluluk</h4>
          <?php foreach (($siteFooterLinks['community'] ?? []) as $fl): ?>
            <a href="<?= e($footerHref((string) ($fl['url'] ?? '#'))) ?>"><?= e((string) ($fl['label'] ?? '')) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p><?= e((string) ($siteFooter['copyright'] ?? ('© ' . date('Y') . ' ' . $appName . '. Tüm hakları saklıdır.'))) ?><span class="ver-tag">v<?= e($appVersion) ?></span></p>
      <div class="socials">
        <?php foreach ($siteSocials as $soc): ?>
          <a href="<?= e((string) ($soc['url'] ?? '#')) ?>" title="<?= e((string) ($soc['name'] ?? '')) ?>" <?= str_starts_with((string)($soc['url'] ?? ''), 'http') ? 'target="_blank" rel="noopener"' : '' ?>><i class="<?= e((string) ($soc['icon'] ?? 'fa-brands fa-link')) ?>"></i></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</footer>

<!-- ================= LOGIN MODAL ================= -->
<?php if ($homeAnnouncement): ?>
<div class="modal-overlay" id="homeAnnModal" role="dialog" aria-modal="true" aria-labelledby="homeAnnTitle">
  <div class="modal-card modal-ann">
    <button type="button" class="modal-close" id="closeHomeAnnModal" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
    <div class="eyebrow">Sunucu Duyurusu</div>
    <div class="modal-ann-meta">
      <span class="ann-type"><?= e((string) ($homeAnnouncement['type_name'] ?: 'Duyuru')) ?></span>
      <span><?= e((string) $homeAnnouncement['published_label']) ?></span>
    </div>
    <h2 id="homeAnnTitle"><?= e((string) $homeAnnouncement['title']) ?></h2>
    <div class="modal-ann-body"><?= \App\Services\AnnouncementService::sanitizeHtml((string) $homeAnnouncement['body']) ?></div>
    <div class="modal-ann-actions">
      <button type="button" class="btn btn-ghost" id="homeAnnDismissClose">Kapat</button>
      <button type="button" class="btn btn-primary" id="homeAnnDismissRead">Okudum</button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="modal-overlay<?= $openLogin ? ' open' : '' ?>" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
  <div class="modal-card">
    <button type="button" class="modal-close" id="closeLoginModal" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
    <div class="eyebrow">登錄 · Hesap</div>
    <h2 id="loginTitle">Giriş Yap</h2>
    <p class="sub">Kullanıcı adı ve parolan ile panele bağlan.</p>

    <?php if ($loginSuccess): ?>
      <div class="modal-alert success"><?= e($loginSuccess) ?></div>
    <?php endif; ?>
    <?php if (!$open2fa && $loginErrors !== []): ?>
      <div class="modal-alert error">
        <ul>
          <?php foreach ($loginErrors as $err): ?>
            <li><?= e((string) $err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="modal-form" method="post" action="<?= e(url('/giris')) ?>" autocomplete="off">
      <?= $csrf ?>
      <div class="form-row">
        <label for="login-user">Kullanıcı Adı</label>
        <input id="login-user" name="login" type="text" maxlength="16" required
               value="<?= e((string) ($loginOld['login'] ?? '')) ?>">
      </div>
      <div class="form-row">
        <label for="login-pass">Parola</label>
        <input id="login-pass" name="password" type="password" maxlength="16" required>
      </div>
      <?php if ($captchaEnabled): ?><?= $captchaWidget ?><?php endif; ?>
      <p style="margin:-4px 0 14px;text-align:right;font-size:.82rem;">
        <button type="button" id="openForgotModal" style="background:none;border:0;color:var(--gold-light,#e8c078);cursor:pointer;font:inherit;text-decoration:underline;padding:0;">Parolamı unuttum</button>
      </p>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap</button>
    </form>
  </div>
</div>

<div class="modal-overlay<?= $openForgot ? ' open' : '' ?>" id="forgotModal" role="dialog" aria-modal="true" aria-labelledby="forgotTitle">
  <div class="modal-card">
    <button type="button" class="modal-close" id="closeForgotModal" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
    <div class="eyebrow">重置 · Şifre</div>
    <h2 id="forgotTitle">Parolamı Unuttum</h2>
    <p class="sub">Hesap adı ve kayıtlı e-posta eşleşirse sıfırlama bağlantısı gönderilir (20 dk).</p>

    <?php if ($forgotSuccess): ?>
      <div class="modal-alert success"><?= e($forgotSuccess) ?></div>
    <?php endif; ?>
    <?php if ($forgotErrors !== []): ?>
      <div class="modal-alert error">
        <ul>
          <?php foreach ($forgotErrors as $err): ?>
            <li><?= e((string) $err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="modal-form" method="post" action="<?= e(url('/sifre-unuttum')) ?>" autocomplete="off">
      <?= $csrf ?>
      <div class="form-row">
        <label for="forgot-login">Hesap adı</label>
        <input id="forgot-login" name="login" type="text" maxlength="16" required
               value="<?= e((string) ($forgotOld['login'] ?? '')) ?>">
      </div>
      <div class="form-row">
        <label for="forgot-email">E-posta</label>
        <input id="forgot-email" name="email" type="email" maxlength="64" required
               value="<?= e((string) ($forgotOld['email'] ?? '')) ?>">
      </div>
      <?php if ($captchaEnabled): ?><?= $captchaWidget ?><?php endif; ?>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-envelope"></i> Sıfırlama bağlantısı gönder</button>
    </form>
  </div>
</div>

<!-- ================= 2FA MODAL ================= -->
<div class="modal-overlay<?= $open2fa ? ' open' : '' ?>" id="twoFaModal" role="dialog" aria-modal="true" aria-labelledby="twoFaTitle">
  <div class="modal-card">
    <button type="button" class="modal-close" id="closeTwoFaModal" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
    <div class="eyebrow">安全 · 2FA</div>
    <h2 id="twoFaTitle">Doğrulama Kodu</h2>
    <p class="sub">Authenticator uygulamanızdaki 6 haneli kodu gir.</p>

    <?php if ($open2fa && $loginErrors !== []): ?>
      <div class="modal-alert error">
        <ul>
          <?php foreach ($loginErrors as $err): ?>
            <li><?= e((string) $err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="modal-form" method="post" action="<?= e(url('/giris/2fa')) ?>" autocomplete="off">
      <?= $csrf ?>
      <div class="form-row">
        <label for="twofa-code">Kod</label>
        <input id="twofa-code" name="code" type="text" inputmode="numeric" pattern="\d{6}" maxlength="6" required placeholder="000000" autofocus>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-shield-halved"></i> Doğrula</button>
    </form>
  </div>
</div>

<!-- ================= REGISTER MODAL ================= -->
<div class="modal-overlay<?= $openRegister ? ' open' : '' ?>" id="registerModal" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
  <div class="modal-card">
    <button type="button" class="modal-close" id="closeRegisterModal" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
    <div class="eyebrow">登錄 · Yeni Hesap</div>
    <h2 id="registerTitle">Kayıt Ol</h2>
    <p class="sub">Kullanıcı adı, parola ve e-posta ile hesabını oluştur.</p>

    <?php if ($registerSuccess): ?>
      <div class="modal-alert success"><?= e($registerSuccess) ?></div>
    <?php endif; ?>
    <?php if ($registerErrors !== []): ?>
      <div class="modal-alert error">
        Kayıt tamamlanamadı:
        <ul>
          <?php foreach ($registerErrors as $err): ?>
            <li><?= e((string) $err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="modal-form" method="post" action="<?= e(url('/kayit')) ?>" autocomplete="off">
      <?= $csrf ?>
      <div class="form-row">
        <label for="reg-login">Kullanıcı Adı</label>
        <input id="reg-login" name="login" type="text" maxlength="16" required
               value="<?= e((string) ($registerOld['login'] ?? '')) ?>">
      </div>
      <div class="form-row">
        <label for="reg-password">Parola</label>
        <input id="reg-password" name="password" type="password" maxlength="16" required>
      </div>
      <div class="form-row">
        <label for="reg-email">E-posta</label>
        <input id="reg-email" name="email" type="email" maxlength="64" required
               value="<?= e((string) ($registerOld['email'] ?? '')) ?>">
      </div>
      <div class="form-row">
        <label for="reg-security">Güvenli Şifre</label>
        <input id="reg-security" name="securitycode" type="text" inputmode="numeric"
               pattern="\d{1,6}" maxlength="6" required>
      </div>
      <label class="rules-accept">
        <input type="checkbox" name="accept_rules" value="1" required<?= !empty($registerOld['accept_rules']) ? ' checked' : '' ?>>
        <span><a href="<?= e(url('/kurallar')) ?>" target="_blank" rel="noopener">Topluluk Kurallarını</a> okudum ve kabul ediyorum.</span>
      </label>
      <?php if ($captchaEnabled): ?><?= $captchaWidget ?><?php endif; ?>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Hesap Oluştur</button>
    </form>
  </div>
</div>

<script>
  // Sticky header
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 40);
  });

  // Mobile menu (simple toggle of nav-links display)
  const toggle = document.getElementById('menuToggle');
  const navLinks = document.querySelector('.nav-links');
  toggle.addEventListener('click', () => {
    const open = navLinks.style.display === 'flex';
    navLinks.style.display = open ? 'none' : 'flex';
    navLinks.style.cssText += open ? '' : 'position:fixed;top:70px;left:0;right:0;background:#0b0906;flex-direction:column;padding:24px;gap:20px;border-top:1px solid rgba(201,151,74,.15);';
  });

  // Login + Register + 2FA modals
  const loginModal = document.getElementById('loginModal');
  const registerModal = document.getElementById('registerModal');
  const twoFaModal = document.getElementById('twoFaModal');
  const forgotModal = document.getElementById('forgotModal');
  const homeAnnModal = document.getElementById('homeAnnModal');
  const homeAnnId = <?= json_encode($homeAnnouncement ? (int) $homeAnnouncement['id'] : 0) ?>;
  const homeAnnStorageKey = 'm2dn_home_ann_read';
  const openLoginBtn = document.getElementById('openLoginModal');
  const openLoginHero = document.getElementById('openLoginModalHero');
  const closeLoginBtn = document.getElementById('closeLoginModal');
  const openRegisterBtn = document.getElementById('openRegisterModal');
  const closeRegisterBtn = document.getElementById('closeRegisterModal');
  const closeTwoFaBtn = document.getElementById('closeTwoFaModal');
  const openForgotBtn = document.getElementById('openForgotModal');
  const closeForgotBtn = document.getElementById('closeForgotModal');

  function anyAuthModalOpen() {
    return !!(loginModal?.classList.contains('open') || registerModal?.classList.contains('open') || twoFaModal?.classList.contains('open') || forgotModal?.classList.contains('open'));
  }
  function hideAllModals() {
    loginModal?.classList.remove('open');
    registerModal?.classList.remove('open');
    twoFaModal?.classList.remove('open');
    forgotModal?.classList.remove('open');
    homeAnnModal?.classList.remove('open');
    document.body.classList.remove('modal-open');
  }
  function dismissHomeAnn() {
    if (homeAnnId > 0) {
      try { localStorage.setItem(homeAnnStorageKey, String(homeAnnId)); } catch (e) {}
    }
    homeAnnModal?.classList.remove('open');
    if (!anyAuthModalOpen()) document.body.classList.remove('modal-open');
  }
  function showHomeAnnIfNeeded() {
    if (!homeAnnModal || homeAnnId <= 0 || anyAuthModalOpen()) return;
    let readId = '';
    try { readId = localStorage.getItem(homeAnnStorageKey) || ''; } catch (e) {}
    if (String(homeAnnId) === String(readId)) return;
    homeAnnModal.classList.add('open');
    document.body.classList.add('modal-open');
  }
  function showLoginModal() {
    if (!loginModal) return;
    hideAllModals();
    loginModal.classList.add('open');
    document.body.classList.add('modal-open');
    const first = document.getElementById('login-user');
    if (first) setTimeout(() => first.focus(), 50);
  }
  function showRegisterModal() {
    if (!registerModal) return;
    hideAllModals();
    registerModal.classList.add('open');
    document.body.classList.add('modal-open');
    const first = document.getElementById('reg-login');
    if (first) setTimeout(() => first.focus(), 50);
  }
  function showTwoFaModal() {
    hideAllModals();
    if (!twoFaModal) return;
    twoFaModal.classList.add('open');
    document.body.classList.add('modal-open');
    const first = document.getElementById('twofa-code');
    if (first) setTimeout(() => first.focus(), 50);
  }
  function showForgotModal() {
    if (!forgotModal) return;
    hideAllModals();
    forgotModal.classList.add('open');
    document.body.classList.add('modal-open');
    const first = document.getElementById('forgot-login');
    if (first) setTimeout(() => first.focus(), 50);
  }

  openLoginBtn?.addEventListener('click', showLoginModal);
  openLoginHero?.addEventListener('click', showLoginModal);
  closeLoginBtn?.addEventListener('click', hideAllModals);
  openRegisterBtn?.addEventListener('click', showRegisterModal);
  closeRegisterBtn?.addEventListener('click', hideAllModals);
  closeTwoFaBtn?.addEventListener('click', hideAllModals);
  openForgotBtn?.addEventListener('click', showForgotModal);
  closeForgotBtn?.addEventListener('click', hideAllModals);
  document.getElementById('closeHomeAnnModal')?.addEventListener('click', dismissHomeAnn);
  document.getElementById('homeAnnDismissClose')?.addEventListener('click', dismissHomeAnn);
  document.getElementById('homeAnnDismissRead')?.addEventListener('click', dismissHomeAnn);

  loginModal?.addEventListener('click', (e) => { if (e.target === loginModal) hideAllModals(); });
  registerModal?.addEventListener('click', (e) => { if (e.target === registerModal) hideAllModals(); });
  twoFaModal?.addEventListener('click', (e) => { if (e.target === twoFaModal) hideAllModals(); });
  forgotModal?.addEventListener('click', (e) => { if (e.target === forgotModal) hideAllModals(); });
  homeAnnModal?.addEventListener('click', (e) => { if (e.target === homeAnnModal) dismissHomeAnn(); });
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (homeAnnModal?.classList.contains('open') && !anyAuthModalOpen()) {
      dismissHomeAnn();
      return;
    }
    hideAllModals();
  });

  const navUserBtn = document.getElementById('navUserBtn');
  const navUserMenu = document.getElementById('navUserMenu');
  navUserBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    navUserMenu?.classList.toggle('open');
  });
  document.addEventListener('click', () => navUserMenu?.classList.remove('open'));

  (function sessionCountdown() {
    const el = document.getElementById('sessionTimer');
    const valueEl = document.getElementById('sessionTimerValue');
    if (!el || !valueEl) return;
    const expires = parseInt(el.dataset.expires || '0', 10);
    const logoutUrl = el.dataset.logout || '/cikis';
    if (!expires) { valueEl.textContent = '--:--'; return; }
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
      const left = Math.max(0, expires - Math.floor(Date.now() / 1000));
      valueEl.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
      el.classList.toggle('warn', left <= 60);
      if (left <= 0) { window.location.href = logoutUrl; return; }
      setTimeout(tick, 1000);
    };
    tick();
  })();

  <?php if ($open2fa): ?>
  showTwoFaModal();
  <?php elseif ($openForgot): ?>
  showForgotModal();
  <?php elseif ($openLogin): ?>
  showLoginModal();
  <?php elseif ($openRegister): ?>
  showRegisterModal();
  <?php else: ?>
  showHomeAnnIfNeeded();
  <?php endif; ?>

  // Ember particles
  const heroEl = document.querySelector('.hero');
  for (let i = 0; i < 26; i++) {
    const e = document.createElement('div');
    e.className = 'ember';
    e.style.left = Math.random() * 100 + '%';
    e.style.animationDuration = (6 + Math.random() * 8) + 's';
    e.style.animationDelay = (Math.random() * 10) + 's';
    heroEl.appendChild(e);
  }

  // Counter animation on view
  const counters = document.querySelectorAll('.counter');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = +el.dataset.target;
        let cur = 0;
        const step = Math.max(1, target / 60);
        const tick = () => {
          cur += step;
          if (cur >= target) { el.textContent = target.toLocaleString('tr-TR'); return; }
          el.textContent = Math.floor(cur).toLocaleString('tr-TR');
          requestAnimationFrame(tick);
        };
        tick();
        counterObserver.unobserve(el);
      }
    });
  }, { threshold: 0.6 });
  counters.forEach(c => counterObserver.observe(c));

  // Fill bars on view (class cards + rate bars)
  const fills = document.querySelectorAll('.fill, .rate-fill');
  const fillObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        el.style.width = el.dataset.w + '%';
        fillObserver.unobserve(el);
      }
    });
  }, { threshold: 0.4 });
  fills.forEach(f => fillObserver.observe(f));

  // Countdown from site settings target
  const cdEl = document.getElementById('countdown');
  const cdTargetMs = (parseInt(cdEl?.dataset.target || '0', 10) || 0) * 1000;
  function updateCountdown() {
    const now = Date.now();
    let diff = Math.max(0, cdTargetMs - now);
    const d = Math.floor(diff / (1000*60*60*24));
    const h = Math.floor((diff / (1000*60*60)) % 24);
    const m = Math.floor((diff / (1000*60)) % 60);
    const s = Math.floor((diff / 1000) % 60);
    const set = (id, v) => { const n = document.getElementById(id); if (n) n.textContent = String(v).padStart(2,'0'); };
    set('cd-days', d); set('cd-hours', h); set('cd-mins', m); set('cd-secs', s);
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);
</script>
<?php if ($captchaEnabled && $captchaScripts !== ''): ?>
<?= $captchaScripts ?>
<?php endif; ?>

</body>
</html>
