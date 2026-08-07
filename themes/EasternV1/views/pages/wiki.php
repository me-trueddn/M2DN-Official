<?php
/** @var string $appName */
/** @var string $appVersion */
/** @var array|null $authUser */
/** @var array $rates */
/** @var array $siteBrand */
/** @var array $siteFooter */
/** @var array $siteFooterLinks */
/** @var list<array> $siteSocials */
/** @var array $wiki */

$appName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'M2DN';
$appVersion = (string) ($appVersion ?? '');
$authUser = is_array($authUser ?? null) ? $authUser : null;
$rates = is_array($rates ?? null) ? $rates : [];
$wiki = is_array($wiki ?? null) ? $wiki : \App\Services\WikiService::content();
$siteSocials = is_array($siteSocials ?? null) ? $siteSocials : [];
$siteFooterLinks = is_array($siteFooterLinks ?? null) ? $siteFooterLinks : [];
$siteFooter = is_array($siteFooter ?? null) ? $siteFooter : [];
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandLogo = (string) ($siteBrand['logo_url'] ?? asset('img/logo-nav.svg'));
$brandHomeSize = (int) ($siteBrand['home_size'] ?? 48);
$isLoggedIn = $authUser !== null;
$canAdmin = $isLoggedIn && \App\Services\AuthService::canAccessAdmin($authUser);

$exp = (int) ($rates['exp'] ?? 100);
$drop = (int) ($rates['drop'] ?? 50);
$yang = (int) ($rates['yang'] ?? 30);

$wHead = is_array($wiki['head'] ?? null) ? $wiki['head'] : [];
$wIntro = is_array($wiki['intro'] ?? null) ? $wiki['intro'] : [];
$wClassSec = is_array($wiki['classes_section'] ?? null) ? $wiki['classes_section'] : [];
$wClasses = is_array($wiki['classes'] ?? null) ? $wiki['classes'] : [];
$wMapSec = is_array($wiki['maps_section'] ?? null) ? $wiki['maps_section'] : [];
$wMaps = is_array($wiki['maps'] ?? null) ? $wiki['maps'] : [];
$wMonSec = is_array($wiki['monsters_section'] ?? null) ? $wiki['monsters_section'] : [];
$wMonsters = is_array($wiki['monsters'] ?? null) ? $wiki['monsters'] : [];
$wMetSec = is_array($wiki['metins_section'] ?? null) ? $wiki['metins_section'] : [];
$wMetins = is_array($wiki['metins'] ?? null) ? $wiki['metins'] : [];
$wUpSec = is_array($wiki['upgrade_section'] ?? null) ? $wiki['upgrade_section'] : [];
$wUpgrade = is_array($wiki['upgrade'] ?? null) ? $wiki['upgrade'] : [];
$wClan = is_array($wiki['clan'] ?? null) ? $wiki['clan'] : [];
$wFaqSec = is_array($wiki['faq_section'] ?? null) ? $wiki['faq_section'] : [];
$wFaq = is_array($wiki['faq'] ?? null) ? $wiki['faq'] : [];
$introLead = (string) ($wIntro['text'] ?? '');
$introLead = str_replace('M2DN', $appName, $introLead);
$headLead = str_replace('M2DN', $appName, (string) ($wHead['lead'] ?? ''));
$clanText = str_replace('M2DN', $appName, (string) ($wClan['text'] ?? ''));

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
<title>Wiki · Bilgi Bankası | <?= e($appName) ?></title>
<link rel="icon" href="<?= e($brandIcon) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;900&family=Ma+Shan+Zheng&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root{
    --obsidian:#0b0906; --obsidian-2:#161009; --obsidian-3:#1f160d;
    --blood:#8f1c29; --blood-light:#c53347;
    --gold:#c9974a; --gold-light:#eccd8e;
    --jade:#33594a; --jade-light:#4f8a71;
    --parchment:#e9dfc6; --ash:#9a8f80;
    --line:rgba(201,151,74,.15);
    --font-display:'Cinzel', serif; --font-brush:'Ma Shan Zheng', cursive; --font-body:'Inter', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--obsidian); color:var(--parchment); font-family:var(--font-body); min-height:100vh;}
  a{color:inherit; text-decoration:none;} ul{list-style:none;}
  h1,h2,h3{font-family:var(--font-display);}
  ::selection{background:var(--blood); color:var(--gold-light);}
  img{max-width:100%; display:block;}

  .eyebrow{font-family:var(--font-brush); font-size:1.2rem; color:var(--blood-light); display:inline-flex; align-items:center; gap:.5rem; margin-bottom:.5rem;}
  .eyebrow::before{content:""; width:22px; height:1px; background:var(--gold);}
  .container{max-width:1280px; margin:0 auto; padding:0 24px;}

  header#siteHeader{
    position:sticky; top:0; z-index:200;
    background:rgba(11,9,6,.92); backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line); padding:16px 0;
    transition:box-shadow .25s, background .25s;
  }
  header#siteHeader.scrolled{box-shadow:0 8px 30px rgba(0,0,0,.5);}
  nav{display:flex; align-items:center; justify-content:space-between; gap:24px;}
  .brand{display:flex; align-items:center; gap:10px; line-height:0;}
  .brand:hover{opacity:.92;}
  .brand-logo{height:<?= $brandHomeSize ?>px; width:auto; display:block; object-fit:contain; filter:drop-shadow(0 2px 10px rgba(0,0,0,.35));}
  .nav-links{display:flex; gap:30px; font-size:.86rem; font-weight:600; text-transform:uppercase; letter-spacing:.03em;}
  .nav-links a{position:relative; color:var(--ash); transition:color .2s; padding:4px 0;}
  .nav-links a:hover, .nav-links a.active{color:var(--gold-light);}
  .nav-links a::after{
    content:""; position:absolute; left:0; bottom:-2px; width:0; height:1px; background:var(--blood-light);
    transition:width .3s ease;
  }
  .nav-links a:hover::after, .nav-links a.active::after{width:100%;}
  .nav-actions{display:flex; align-items:center; gap:10px;}
  .nav-cta{
    display:flex; align-items:center; gap:8px; padding:10px 20px;
    border:1px solid var(--gold); color:var(--gold-light);
    font-weight:700; font-size:.85rem; letter-spacing:.06em; text-transform:uppercase;
    transition:background .3s, color .3s;
    clip-path:polygon(8px 0,100% 0,100% calc(100% - 8px),calc(100% - 8px) 100%,0 100%,0 8px);
  }
  .nav-cta:hover{background:var(--gold); color:var(--obsidian);}
  .nav-cta.solid{background:linear-gradient(135deg, var(--blood-light), var(--blood)); border-color:transparent; color:var(--parchment);}
  .nav-cta.solid:hover{background:var(--blood); color:var(--parchment);}
  button.nav-cta{cursor:pointer; font-family:inherit; background:transparent;}
  .menu-toggle{display:none; background:none; border:none; color:var(--gold-light); font-size:1.5rem; cursor:pointer;}
  .nav-user{position:relative;}
  .nav-user-btn{display:inline-flex; align-items:center; gap:8px;}
  .nav-user-menu{
    position:absolute; right:0; top:calc(100% + 8px); min-width:200px;
    background:var(--obsidian-2); border:1px solid rgba(201,151,74,.28); padding:8px;
    display:none; z-index:50; box-shadow:0 16px 40px rgba(0,0,0,.45);
  }
  .nav-user-menu.open{display:block;}
  .nav-user-menu a{display:flex; align-items:center; gap:10px; padding:10px 12px; color:var(--parchment); font-size:.82rem;}
  .nav-user-menu a:hover{background:rgba(201,151,74,.08); color:var(--gold-light);}
  .session-timer-home{
    display:inline-flex; align-items:center; gap:6px; margin-right:8px;
    padding:7px 10px; border:1px solid rgba(201,151,74,.28); background:rgba(201,151,74,.1);
    font-size:.72rem; color:var(--gold-light); font-variant-numeric:tabular-nums;
  }
  .session-timer-home.warn{border-color:rgba(197,51,71,.4); color:#e8a0a8; background:rgba(143,28,41,.18);}

  .page-head{padding:50px 0 34px; background:radial-gradient(ellipse 700px 320px at 15% 0%, rgba(143,28,41,.16), transparent);}
  .breadcrumb{font-size:.76rem; color:var(--ash); margin-bottom:12px; text-transform:uppercase; letter-spacing:.08em;}
  .breadcrumb a{color:var(--ash); transition:color .2s;}
  .breadcrumb a:hover{color:var(--gold-light);}
  .breadcrumb span{color:var(--gold-light);}
  .page-head h1{font-size:clamp(1.9rem,3.2vw,2.6rem); margin-bottom:12px;}
  .page-head p{color:var(--ash); max-width:560px; font-size:.94rem; line-height:1.7; margin-bottom:26px;}
  .wiki-search{display:flex; align-items:center; gap:10px; background:var(--obsidian-2); border:1px solid var(--line); padding:14px 18px; max-width:460px;}
  .wiki-search input{flex:1; background:none; border:none; outline:none; color:var(--parchment); font-size:.9rem; font-family:inherit;}
  .wiki-search i{color:var(--gold-light);}

  .wiki-layout{display:grid; grid-template-columns:270px 1fr; gap:48px; padding:10px 0 100px;}
  .toc{position:sticky; top:96px; align-self:start; max-height:calc(100vh - 120px); overflow-y:auto; padding-right:10px; scrollbar-width:thin; scrollbar-color:rgba(201,151,74,.4) transparent;}
  .toc-group{margin-bottom:22px;}
  .toc-group h4{font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; color:var(--gold-light); margin-bottom:10px; padding-left:12px;}
  .toc a{display:flex; align-items:center; gap:10px; padding:8px 12px; font-size:.84rem; color:var(--ash); border-left:2px solid transparent; transition:background .2s, color .2s, border-color .2s;}
  .toc a i{width:16px; text-align:center; font-size:.82rem;}
  .toc a:hover{color:var(--parchment); background:rgba(201,151,74,.05);}
  .toc a.active{color:var(--gold-light); border-left-color:var(--gold); background:linear-gradient(90deg, rgba(143,28,41,.14), transparent);}

  .wiki-content section{padding-bottom:64px; margin-bottom:64px; border-bottom:1px solid var(--line); scroll-margin-top:96px;}
  .wiki-content section:last-child{border-bottom:none;}
  .section-title h2{font-size:1.7rem; margin-bottom:14px;}
  .section-title p{color:var(--ash); font-size:.92rem; line-height:1.75; max-width:700px; margin-bottom:28px;}

  .info-card{background:var(--obsidian-2); border:1px solid var(--line); padding:22px 26px; margin-bottom:16px; clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);}
  .info-card h4{font-size:.95rem; color:var(--gold-light); margin-bottom:8px; display:flex; align-items:center; gap:10px;}
  .info-card p{color:var(--ash); font-size:.86rem; line-height:1.7;}
  .info-grid{display:grid; grid-template-columns:1fr 1fr; gap:16px;}

  .class-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:16px;}
  .wclass-card{background:var(--obsidian-2); border:1px solid var(--line); padding:22px; transition:border-color .25s;}
  .wclass-card:hover{border-color:var(--gold);}
  .wclass-card .top{display:flex; align-items:center; gap:12px; margin-bottom:14px;}
  .wclass-card .ic{width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:1rem;}
  .wclass-card h4{font-size:1rem; color:var(--parchment);}
  .wclass-card .sub{font-size:.7rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em;}
  .wclass-card p{font-size:.8rem; color:var(--ash); line-height:1.6; margin-bottom:14px;}
  .stat-line{display:flex; align-items:center; gap:8px; margin-bottom:6px; font-size:.68rem; color:var(--ash); text-transform:uppercase; letter-spacing:.04em;}
  .stat-line .track{flex:1; height:4px; background:rgba(233,223,198,.1); position:relative;}
  .stat-line .fill{position:absolute; inset:0; background:var(--gold);}
  .stat-line span.w{width:44px; flex-shrink:0;}

  .map-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px;}
  .map-card{position:relative; background:var(--obsidian-2); border:1px solid var(--line); padding:20px; overflow:hidden;}
  .map-card::before{content:""; position:absolute; inset:0; background:radial-gradient(circle at 30% 0%, rgba(143,28,41,.18), transparent 60%); opacity:0; transition:opacity .3s;}
  .map-card:hover::before{opacity:1;}
  .map-card .tag{display:inline-block; font-size:.62rem; text-transform:uppercase; letter-spacing:.06em; padding:3px 9px; margin-bottom:12px; position:relative; z-index:1;}
  .tag.pve{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .tag.pvp{background:rgba(143,28,41,.2); color:var(--blood-light);}
  .tag.metin{background:rgba(201,151,74,.15); color:var(--gold-light);}
  .map-card h4{font-size:1rem; color:var(--parchment); margin-bottom:6px; position:relative; z-index:1;}
  .map-card .lvl{font-size:.76rem; color:var(--ash); margin-bottom:10px; position:relative; z-index:1;}
  .map-card p{font-size:.8rem; color:var(--ash); line-height:1.6; position:relative; z-index:1;}

  table{width:100%; border-collapse:collapse; font-size:.85rem;}
  thead th{text-align:left; padding:12px 14px; color:var(--ash); font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--line); font-weight:600;}
  tbody td{padding:14px; border-bottom:1px solid rgba(201,151,74,.08); color:var(--parchment); vertical-align:middle;}
  tbody tr:hover{background:rgba(201,151,74,.04);}
  .row-mon{display:flex; align-items:center; gap:12px;}
  .row-mon .ic{width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:.85rem; flex-shrink:0;}
  .hp-track{width:90px; height:6px; background:rgba(233,223,198,.08); position:relative;}
  .hp-fill{position:absolute; inset:0; background:linear-gradient(90deg, var(--blood), var(--blood-light));}
  .chip{display:inline-block; font-size:.68rem; padding:3px 9px; background:rgba(201,151,74,.1); color:var(--gold-light); margin:2px 3px 2px 0;}
  .badge-boss{background:rgba(143,28,41,.25); color:var(--blood-light); font-size:.62rem; text-transform:uppercase; letter-spacing:.05em; padding:3px 8px; margin-left:8px;}

  .upgrade-table td.rate-high{color:var(--jade-light); font-weight:700;}
  .upgrade-table td.rate-mid{color:var(--gold-light); font-weight:700;}
  .upgrade-table td.rate-low{color:var(--blood-light); font-weight:700;}

  .metin-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px;}
  .metin-card{background:var(--obsidian-2); border:1px solid var(--line); padding:24px; text-align:center;}
  .metin-card .glyph{font-family:var(--font-brush); font-size:2.4rem; margin-bottom:10px;}
  .metin-card.red .glyph{color:var(--blood-light);}
  .metin-card.black .glyph{color:var(--ash);}
  .metin-card.gold .glyph{color:var(--gold-light);}
  .metin-card h4{font-size:.95rem; color:var(--parchment); margin-bottom:8px;}
  .metin-card p{font-size:.78rem; color:var(--ash); line-height:1.6;}

  .clan-stats{display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;}
  .clan-stat{background:var(--obsidian-2); border:1px solid var(--line); padding:18px; text-align:center;}
  .clan-stat strong{display:block; font-family:var(--font-display); font-size:1.4rem; color:var(--gold-light);}
  .clan-stat span{font-size:.7rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em;}
  .benefit-list{display:grid; grid-template-columns:1fr 1fr; gap:10px 24px;}
  .benefit-list li{display:flex; align-items:flex-start; gap:10px; font-size:.85rem; color:var(--ash); padding:6px 0;}
  .benefit-list li i{color:var(--jade-light); margin-top:3px;}

  .faq-item{border-bottom:1px solid var(--line);}
  .faq-q{display:flex; align-items:center; justify-content:space-between; padding:20px 4px; cursor:pointer; font-size:.92rem; color:var(--parchment); font-weight:600;}
  .faq-q i{color:var(--gold-light); transition:transform .3s; flex-shrink:0; margin-left:14px;}
  .faq-item.open .faq-q i{transform:rotate(45deg);}
  .faq-a{max-height:0; overflow:hidden; transition:max-height .35s ease;}
  .faq-a p{padding:0 4px 20px; font-size:.85rem; color:var(--ash); line-height:1.7; max-width:720px;}

  footer{padding:70px 0 30px; background:var(--obsidian-2); border-top:1px solid rgba(201,151,74,.12);}
  .footer-top{display:flex; justify-content:space-between; flex-wrap:wrap; gap:40px; padding-bottom:50px; border-bottom:1px solid rgba(201,151,74,.1);}
  .footer-brand p{color:var(--ash); max-width:320px; margin-top:14px; font-size:.9rem; line-height:1.7;}
  .footer-brand .brand-logo{height:<?= max(24, (int) round($brandHomeSize * 1.15)) ?>px;}
  .footer-cols{display:flex; gap:70px;}
  .footer-col h4{font-size:.78rem; text-transform:uppercase; letter-spacing:.1em; color:var(--gold-light); margin-bottom:16px;}
  .footer-col a{display:block; color:var(--ash); font-size:.9rem; margin-bottom:10px; transition:color .25s;}
  .footer-col a:hover{color:var(--gold-light);}
  .footer-bottom{display:flex; justify-content:space-between; align-items:center; padding-top:26px; flex-wrap:wrap; gap:16px;}
  .footer-bottom p{color:var(--ash); font-size:.8rem;}
  .ver-tag{opacity:.75; margin-left:8px; font-size:.75rem;}
  .socials{display:flex; gap:14px;}
  .socials a{color:var(--ash); font-size:1.1rem; transition:color .2s;}
  .socials a:hover{color:var(--gold-light);}

  @media (max-width:980px){
    .wiki-layout{grid-template-columns:1fr;}
    .toc{position:static; max-height:none; margin-bottom:20px; display:flex; flex-wrap:wrap; gap:6px;}
    .toc-group{margin-bottom:0;}
    .toc-group h4{display:none;}
    .toc a{border-left:none; border:1px solid var(--line); padding:7px 12px;}
    .nav-links{display:none;}
    .menu-toggle{display:block;}
    .class-grid, .map-grid, .metin-grid{grid-template-columns:repeat(2,1fr);}
    .clan-stats{grid-template-columns:repeat(2,1fr);}
    .benefit-list{grid-template-columns:1fr;}
    .footer-cols{gap:36px; flex-wrap:wrap;}
  }
  @media (max-width:600px){
    .class-grid, .map-grid, .metin-grid, .info-grid{grid-template-columns:1fr;}
    table{font-size:.76rem;}
    .nav-actions .nav-cta span.hide-sm{display:none;}
  }
</style>
</head>
<body>

<header id="siteHeader">
  <div class="container">
    <nav>
      <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e($appName) ?> Anasayfa">
        <img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
      </a>
      <ul class="nav-links">
        <li><a href="<?= e(url('/')) ?>">Anasayfa</a></li>
        <li><a href="<?= e(url('/#ozellikler')) ?>">Özellikler</a></li>
        <li><a href="<?= e(url('/#siniflar')) ?>">Sınıflar</a></li>
        <li><a href="<?= e(url('/#oranlar')) ?>">Oranlar</a></li>
        <li><a href="<?= e(url('/wiki')) ?>" class="active">Wiki</a></li>
        <li><a href="<?= e(url('/#galeri')) ?>">Galeri</a></li>
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
          <a class="nav-cta" href="<?= e(url('/giris')) ?>"><i class="fa-solid fa-right-to-bracket"></i> <span class="hide-sm">Giriş</span></a>
          <a class="nav-cta solid" href="<?= e(url('/kayit')) ?>"><i class="fa-solid fa-user-plus"></i> <span class="hide-sm">Kayıt Ol</span></a>
        <?php endif; ?>
      </div>
      <button type="button" class="menu-toggle" id="menuToggle" aria-label="Menü"><i class="fa-solid fa-bars"></i></button>
    </nav>
  </div>
</header>

<section class="page-head">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= e(url('/')) ?>">Anasayfa</a>
      <i class="fa-solid fa-angle-right" style="font-size:.6rem; margin:0 4px;"></i>
      <span>Wiki</span>
    </div>
    <div class="eyebrow"><?= e((string) ($wHead['eyebrow'] ?? '典籍 · Bilgi Bankası')) ?></div>
    <h1><?= e((string) ($wHead['title'] ?? 'Bilgi Bankası')) ?></h1>
    <p><?= e($headLead !== '' ? $headLead : ((string) ($wHead['lead'] ?? ''))) ?></p>
    <div class="wiki-search"><i class="fa-solid fa-magnifying-glass"></i><input id="wikiSearch" type="search" placeholder="<?= e((string) ($wHead['search_placeholder'] ?? 'Ara...')) ?>" autocomplete="off"></div>
  </div>
</section>

<div class="container">
  <div class="wiki-layout">
    <aside class="toc" id="toc">
      <div class="toc-group">
        <h4>Başlangıç</h4>
        <a href="#giris" data-target="giris"><i class="fa-solid fa-book-open"></i> Giriş &amp; Temel Bilgiler</a>
      </div>
      <div class="toc-group">
        <h4>Karakter</h4>
        <a href="#siniflar" data-target="siniflar"><i class="fa-solid fa-khanda"></i> Sınıflar</a>
      </div>
      <div class="toc-group">
        <h4>Dünya</h4>
        <a href="#haritalar" data-target="haritalar"><i class="fa-solid fa-map-location-dot"></i> Haritalar</a>
        <a href="#canavarlar" data-target="canavarlar"><i class="fa-solid fa-skull"></i> Canavarlar &amp; Boss'lar</a>
        <a href="#metinler" data-target="metinler"><i class="fa-solid fa-gem"></i> Metin Taşları</a>
      </div>
      <div class="toc-group">
        <h4>Sistemler</h4>
        <a href="#esya" data-target="esya"><i class="fa-solid fa-hammer"></i> Eşya Yükseltme</a>
        <a href="#klan" data-target="klan"><i class="fa-solid fa-flag"></i> Lonca Sistemi</a>
      </div>
      <div class="toc-group">
        <h4>Yardım</h4>
        <a href="#sss" data-target="sss"><i class="fa-solid fa-circle-question"></i> Sıkça Sorulan Sorular</a>
      </div>
    </aside>

    <div class="wiki-content">
      <section id="giris">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wIntro['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wIntro['title'] ?? 'Giriş')) ?></h2>
          <p><?= e($introLead) ?></p>
        </div>
        <div class="info-grid">
          <?php foreach ((is_array($wIntro['cards'] ?? null) ? $wIntro['cards'] : []) as $ci => $card): ?>
            <?php
              $cardText = (string) ($card['text'] ?? '');
              if ($ci === 0 && !empty($wIntro['use_live_rates'])) {
                  $cardText = 'EXP x' . $exp . ', Drop x' . $drop . ', Yang x' . $yang . '. Oranlar oyunun ilerleyen bölümlerinde (60+ seviye) otomatik olarak kademeli artar. Güncel oranlar anasayfada da görünür.';
              }
            ?>
            <div class="info-card">
              <h4><i class="<?= e((string) ($card['icon'] ?? 'fa-solid fa-circle-info')) ?>"></i> <?= e((string) ($card['title'] ?? '')) ?></h4>
              <p><?= e($cardText) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="siniflar">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wClassSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wClassSec['title'] ?? 'Sınıflar')) ?></h2>
          <p><?= e((string) ($wClassSec['text'] ?? '')) ?></p>
        </div>
        <div class="class-grid">
          <?php foreach ($wClasses as $cl): ?>
            <div class="wclass-card">
              <div class="top"><div class="ic"><i class="<?= e((string) ($cl['icon'] ?? 'fa-solid fa-khanda')) ?>"></i></div><div><h4><?= e((string) ($cl['name'] ?? '')) ?></h4><div class="sub"><?= e((string) ($cl['sub'] ?? '')) ?></div></div></div>
              <p><?= e((string) ($cl['text'] ?? '')) ?></p>
              <?php foreach ((is_array($cl['stats'] ?? null) ? $cl['stats'] : []) as $st): ?>
                <div class="stat-line"><span class="w"><?= e((string) ($st['label'] ?? '')) ?></span><div class="track"><div class="fill" style="width:<?= (int) ($st['pct'] ?? 0) ?>%"></div></div></div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="haritalar">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wMapSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wMapSec['title'] ?? 'Haritalar')) ?></h2>
          <p><?= e((string) ($wMapSec['text'] ?? '')) ?></p>
        </div>
        <div class="map-grid">
          <?php foreach ($wMaps as $mp): ?>
            <div class="map-card"><span class="tag <?= e((string) ($mp['tag_class'] ?? 'pve')) ?>"><?= e((string) ($mp['tag'] ?? '')) ?></span><h4><?= e((string) ($mp['title'] ?? '')) ?></h4><div class="lvl"><?= e((string) ($mp['level'] ?? '')) ?></div><p><?= e((string) ($mp['text'] ?? '')) ?></p></div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="canavarlar">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wMonSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wMonSec['title'] ?? 'Canavarlar')) ?></h2>
          <p><?= e((string) ($wMonSec['text'] ?? '')) ?></p>
        </div>
        <table>
          <thead><tr><th>Canavar</th><th>Seviye</th><th>Harita</th><th>Can</th><th>Bilinen Düşenler</th></tr></thead>
          <tbody>
            <?php foreach ($wMonsters as $mo): ?>
              <tr>
                <td class="row-mon"><div class="ic"><i class="<?= e((string) ($mo['icon'] ?? 'fa-solid fa-paw')) ?>"></i></div><?= e((string) ($mo['name'] ?? '')) ?><?php if (trim((string) ($mo['boss_badge'] ?? '')) !== ''): ?> <span class="badge-boss"><?= e((string) $mo['boss_badge']) ?></span><?php endif; ?></td>
                <td><?= e((string) ($mo['level'] ?? '')) ?></td>
                <td><?= e((string) ($mo['map'] ?? '')) ?></td>
                <td><div class="hp-track"><div class="hp-fill" style="width:<?= (int) ($mo['hp_pct'] ?? 50) ?>%"></div></div></td>
                <td><?php foreach ((is_array($mo['drops'] ?? null) ? $mo['drops'] : []) as $d): ?><span class="chip"><?= e((string) $d) ?></span><?php endforeach; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <section id="metinler">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wMetSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wMetSec['title'] ?? 'Metin Taşları')) ?></h2>
          <p><?= e((string) ($wMetSec['text'] ?? '')) ?></p>
        </div>
        <div class="metin-grid">
          <?php foreach ($wMetins as $me): ?>
            <div class="metin-card <?= e((string) ($me['style'] ?? 'red')) ?>"><div class="glyph"><?= e((string) ($me['glyph'] ?? '')) ?></div><h4><?= e((string) ($me['title'] ?? '')) ?></h4><p><?= e((string) ($me['text'] ?? '')) ?></p></div>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="esya">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wUpSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wUpSec['title'] ?? 'Eşya Yükseltme')) ?></h2>
          <p><?= e((string) ($wUpSec['text'] ?? '')) ?></p>
        </div>
        <table class="upgrade-table">
          <thead><tr><th>Seviye</th><th>Başarı Oranı</th><th>Gerekli Materyal</th><th>Kırılma Riski</th></tr></thead>
          <tbody>
            <?php foreach ($wUpgrade as $up): ?>
              <tr><td><?= e((string) ($up['level'] ?? '')) ?></td><td class="<?= e((string) ($up['rate_class'] ?? 'rate-mid')) ?>"><?= e((string) ($up['rate'] ?? '')) ?></td><td><?= e((string) ($up['material'] ?? '')) ?></td><td><?= e((string) ($up['risk'] ?? '')) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <section id="klan">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wClan['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wClan['title'] ?? 'Lonca Sistemi')) ?></h2>
          <p><?= e($clanText) ?></p>
        </div>
        <div class="clan-stats">
          <?php foreach ((is_array($wClan['stats'] ?? null) ? $wClan['stats'] : []) as $st): ?>
            <div class="clan-stat"><strong><?= e((string) ($st['value'] ?? '')) ?></strong><span><?= e((string) ($st['label'] ?? '')) ?></span></div>
          <?php endforeach; ?>
        </div>
        <ul class="benefit-list">
          <?php foreach ((is_array($wClan['benefits'] ?? null) ? $wClan['benefits'] : []) as $ben): ?>
            <li><i class="fa-solid fa-circle-check"></i> <?= e((string) $ben) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section id="sss">
        <div class="section-title">
          <div class="eyebrow"><?= e((string) ($wFaqSec['eyebrow'] ?? '')) ?></div>
          <h2><?= e((string) ($wFaqSec['title'] ?? 'SSS')) ?></h2>
        </div>
        <div id="faqList">
          <?php foreach ($wFaq as $fi => $fq): ?>
            <div class="faq-item<?= $fi === 0 ? ' open' : '' ?>">
              <div class="faq-q"><?= e((string) ($fq['q'] ?? '')) ?> <i class="fa-solid fa-plus"></i></div>
              <div class="faq-a"><p><?= e((string) ($fq['a'] ?? '')) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </div>
</div>


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
          <a href="<?= e(url('/wiki')) ?>">Wiki</a>
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
      <p><?= e((string) ($siteFooter['copyright'] ?? ('© ' . date('Y') . ' ' . $appName . '. Tüm hakları saklıdır.'))) ?><?= $appVersion !== '' ? '<span class="ver-tag">v' . e($appVersion) . '</span>' : '' ?></p>
      <div class="socials">
        <?php foreach ($siteSocials as $soc): ?>
          <a href="<?= e((string) ($soc['url'] ?? '#')) ?>" title="<?= e((string) ($soc['name'] ?? '')) ?>" <?= str_starts_with((string) ($soc['url'] ?? ''), 'http') ? 'target="_blank" rel="noopener"' : '' ?>><i class="<?= e((string) ($soc['icon'] ?? 'fa-brands fa-link')) ?>"></i></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</footer>

<script>
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', () => {
    header?.classList.toggle('scrolled', window.scrollY > 40);
  });

  const toggle = document.getElementById('menuToggle');
  const navLinks = document.querySelector('.nav-links');
  toggle?.addEventListener('click', () => {
    if (!navLinks) return;
    const open = navLinks.style.display === 'flex';
    navLinks.style.display = open ? 'none' : 'flex';
    if (!open) {
      navLinks.style.cssText += 'position:fixed;top:70px;left:0;right:0;background:#0b0906;flex-direction:column;padding:24px;gap:20px;border-top:1px solid rgba(201,151,74,.15);z-index:199;';
    } else {
      navLinks.style.cssText = '';
    }
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

  document.querySelectorAll('.faq-item').forEach(item => {
    const q = item.querySelector('.faq-q');
    const a = item.querySelector('.faq-a');
    if (!q || !a) return;
    if (item.classList.contains('open')) a.style.maxHeight = a.scrollHeight + 'px';
    q.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('open');
        const ia = i.querySelector('.faq-a');
        if (ia) ia.style.maxHeight = '0';
      });
      if (!isOpen) {
        item.classList.add('open');
        a.style.maxHeight = a.scrollHeight + 'px';
      }
    });
  });

  const sections = document.querySelectorAll('.wiki-content section');
  const tocLinks = document.querySelectorAll('.toc a');
  const spy = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        tocLinks.forEach(l => l.classList.remove('active'));
        const link = document.querySelector(`.toc a[data-target="${entry.target.id}"]`);
        if (link) link.classList.add('active');
      }
    });
  }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
  sections.forEach(s => spy.observe(s));

  document.getElementById('wikiSearch')?.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const q = e.target.value.toLowerCase().trim();
    if (!q) return;
    const match = Array.from(document.querySelectorAll('.wiki-content h2, .wiki-content h4')).find(el =>
      el.textContent.toLowerCase().includes(q)
    );
    if (match) match.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
</script>
</body>
</html>