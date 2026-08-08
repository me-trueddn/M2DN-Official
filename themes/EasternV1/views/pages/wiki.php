<?php
/** @var string $appName */
/** @var string $appVersion */
/** @var array|null $authUser */
/** @var array $rates */
/** @var array $siteBrand */
/** @var array $siteFooter */
/** @var array $siteFooterLinks */
/** @var list<array> $siteSocials */
/** @var list<array> $wikiCategories */
/** @var array<int, array> $wikiPagesByCategory */
/** @var string $wikiMode */
/** @var array|null $wikiCategory */
/** @var array|null $wikiPage */
/** @var string $wikiCurrentSlug */

$appName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'M2DN';
$appVersion = (string) ($appVersion ?? '');
$authUser = is_array($authUser ?? null) ? $authUser : null;
$rates = is_array($rates ?? null) ? $rates : [];
$wikiCategories = isset($wikiCategories) && is_array($wikiCategories)
    ? $wikiCategories
    : \App\Services\WikiCategoryService::tree(true);
$wikiPagesByCategory = isset($wikiPagesByCategory) && is_array($wikiPagesByCategory)
    ? $wikiPagesByCategory
    : \App\Services\WikiPageService::mapByCategory(true);
$wikiMode = isset($wikiMode) && is_string($wikiMode) ? $wikiMode : 'index';
$wikiCategory = is_array($wikiCategory ?? null) ? $wikiCategory : null;
$wikiPage = is_array($wikiPage ?? null) ? $wikiPage : null;
$wikiCurrentSlug = isset($wikiCurrentSlug) && is_string($wikiCurrentSlug) ? $wikiCurrentSlug : '';
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
<?php
  $wikiDocTitle = 'Wiki · Bilgi Bankası';
  if ($wikiMode === 'page' && is_array($wikiPage) && trim((string) ($wikiPage['title'] ?? '')) !== '') {
    $wikiDocTitle = (string) $wikiPage['title'] . ' · Wiki';
  } elseif ($wikiMode === 'page' && is_array($wikiCategory)) {
    $wikiDocTitle = (string) ($wikiCategory['name'] ?? 'Wiki') . ' · Wiki';
  }
?>
<title><?= e($wikiDocTitle) ?> | <?= e($appName) ?></title>
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
  .toc-group h4 a.toc-main{
    display:inline; padding:0; border:0; color:inherit; font:inherit; letter-spacing:inherit; text-transform:inherit;
    background:none; cursor:pointer; user-select:none;
  }
  .toc-group h4 a.toc-main:hover{color:var(--gold-light); background:none;}
  .toc a:not(.toc-main){display:flex; align-items:center; gap:10px; padding:8px 12px; font-size:.84rem; color:var(--ash); border-left:2px solid transparent; transition:background .2s, color .2s, border-color .2s; cursor:pointer; user-select:none;}
  .toc a:not(.toc-main) i{width:16px; text-align:center; font-size:.82rem;}
  .toc a:not(.toc-main):hover{color:var(--parchment); background:rgba(201,151,74,.05);}
  .toc a:not(.toc-main).active{color:var(--gold-light); border-left-color:var(--gold); background:linear-gradient(90deg, rgba(143,28,41,.14), transparent);}
  .toc a.toc-main.active{color:var(--gold);}

  .wiki-content section{padding-bottom:64px; margin-bottom:64px; border-bottom:1px solid var(--line); scroll-margin-top:96px;}
  .wiki-content section:last-child{border-bottom:none;}
  .section-title h2{font-size:1.7rem; margin-bottom:14px;}
  .section-title p{color:var(--ash); font-size:.92rem; line-height:1.75; max-width:700px; margin-bottom:28px;}
  .wiki-empty{color:var(--ash); font-size:.9rem; line-height:1.7; padding:18px 0;}
  .wiki-child{background:var(--obsidian-2); border:1px solid var(--line); padding:20px 22px; margin-bottom:14px; scroll-margin-top:96px;}
  .wiki-child h3{font-size:1.05rem; color:var(--gold-light); margin-bottom:8px;}
  .wiki-child p{color:var(--ash); font-size:.86rem; line-height:1.65;}
  .wiki-child-grid{display:grid; grid-template-columns:1fr 1fr; gap:14px;}

  .wiki-prose{color:var(--ash); font-size:.92rem; line-height:1.75; max-width:760px;}
  .wiki-prose:has(.wiki-class-row){max-width:none;}
  .wiki-prose h2,.wiki-prose h3,.wiki-prose h4{color:var(--parchment); margin:1.1em 0 .5em; font-family:var(--font-display);}
  .wiki-prose p{margin:0 0 .85em;}
  .wiki-prose a{color:var(--gold-light);}
  .wiki-prose ul,.wiki-prose ol{margin:0 0 1em 1.2em;}
  .wiki-prose li{margin:.25em 0;}
  .wiki-prose img{max-width:100%; height:auto; margin:.8em 0; border:1px solid var(--line);}
  .wiki-prose table{width:100%; border-collapse:collapse; margin:1em 0; font-size:.86rem;}
  .wiki-prose th,.wiki-prose td{border:1px solid var(--line); padding:8px 10px; color:var(--parchment);}

  .wiki-class-row{
    display:flex !important;
    flex-direction:row !important;
    flex-wrap:wrap;
    align-items:flex-start;
    justify-content:center;
    gap:22px;
    margin:1.5em auto 1.7em;
    width:100%;
  }
  .wiki-class-card{
    position:relative;
    box-sizing:border-box;
    flex:0 0 260px;
    width:260px;
    height:440px;
    padding:30px 26px;
    display:flex; flex-direction:column; justify-content:flex-end;
    overflow:hidden;
    border:1px solid rgba(201,151,74,.18);
    background:var(--obsidian);
    transition:transform .4s ease, border-color .4s;
  }
  .wiki-class-card:hover{transform:translateY(-8px); border-color:var(--gold);}
  .wiki-class-glow{
    position:absolute; inset:0; pointer-events:none;
    opacity:.5;
    background:radial-gradient(circle at 50% 20%, var(--glow-color, var(--blood)), transparent 65%);
    transition:opacity .4s;
  }
  .wiki-class-card:hover .wiki-class-glow{opacity:.85;}
  .wiki-class-visual{
    position:absolute; left:0; right:0; top:0; height:58%;
    display:flex; align-items:flex-end; justify-content:center;
    z-index:1; overflow:hidden; pointer-events:none;
  }
  .wiki-class-visual.is-empty{display:none;}
  .wiki-prose .wiki-class-visual img{
    max-height:100%; max-width:78%; width:auto; height:auto; margin:0; border:0;
    object-fit:contain; object-position:center bottom; display:block;
    filter:drop-shadow(0 8px 18px rgba(0,0,0,.45));
  }
  .wiki-class-body{position:relative; z-index:2;}
  .wiki-prose .wiki-class-icon{
    display:block; font-size:1.35rem; color:var(--gold-light); margin-bottom:10px;
  }
  .wiki-prose .wiki-class-card h3{
    margin:0 0 8px; font-size:1.3rem; color:var(--parchment);
    text-transform:none; letter-spacing:0; font-weight:600;
  }
  .wiki-prose .wiki-class-card p{
    margin:0; font-size:.85rem; color:var(--ash); line-height:1.6; max-width:none;
  }

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
    .toc-group h4{display:block; padding:0; margin:0; letter-spacing:0; text-transform:none; font-size:inherit;}
    .toc-group h4 a.toc-main{
      display:flex; align-items:center; gap:10px; padding:7px 12px; font-size:.84rem; color:var(--gold-light);
      border:1px solid var(--line); letter-spacing:.06em; text-transform:uppercase; font-weight:600; font-size:.72rem;
    }
    .toc a:not(.toc-main){border-left:none; border:1px solid var(--line); padding:7px 12px;}
    .nav-links{display:none;}
    .menu-toggle{display:block;}
    .wiki-child-grid{grid-template-columns:1fr;}
    .footer-cols{gap:36px; flex-wrap:wrap;}
  }
  @media (max-width:600px){
    .nav-actions .nav-cta span.hide-sm{display:none;}
    .wiki-class-row{justify-content:center;}
    .wiki-class-card{flex-basis:100%; width:100%; max-width:320px; height:400px;}
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
      <?php if ($wikiMode === 'page'): ?>
        <a href="<?= e(url('/wiki')) ?>">Wiki</a>
        <i class="fa-solid fa-angle-right" style="font-size:.6rem; margin:0 4px;"></i>
        <span><?= e((string) ($wikiCategory['name'] ?? 'Sayfa')) ?></span>
      <?php else: ?>
        <span>Wiki</span>
      <?php endif; ?>
    </div>
    <?php if ($wikiMode === 'page'): ?>
      <div class="eyebrow"><?= e((string) ($wikiCategory['parent_name'] ?? 'Wiki')) ?></div>
      <h1><?= e((string) (
        (is_array($wikiPage) && trim((string) ($wikiPage['title'] ?? '')) !== '')
          ? (string) $wikiPage['title']
          : (string) ($wikiCategory['name'] ?? 'Sayfa')
      )) ?></h1>
      <p><?= e($appName) ?> bilgi bankası.</p>
    <?php else: ?>
      <div class="eyebrow">典籍 · Bilgi Bankası</div>
      <h1>Bilgi Bankası</h1>
      <p><?= e($appName) ?> wiki — soldan veya kartlardan bir sayfa seçin.</p>
      <div class="wiki-search"><i class="fa-solid fa-magnifying-glass"></i><input id="wikiSearch" type="search" placeholder="Kategori ara..." autocomplete="off"></div>
    <?php endif; ?>
  </div>
</section>

<div class="container">
  <div class="wiki-layout">
    <aside class="toc" id="toc">
      <?php if ($wikiCategories === []): ?>
        <div class="toc-group">
          <h4>Wiki</h4>
        </div>
      <?php else: ?>
        <?php foreach ($wikiCategories as $main): ?>
          <?php
            $mainId = (int) ($main['id'] ?? 0);
            $mainAnchor = \App\Services\WikiCategoryService::anchorId($mainId);
            $children = is_array($main['children'] ?? null) ? $main['children'] : [];
            $mainHref = $wikiMode === 'page'
              ? url('/wiki') . '#' . $mainAnchor
              : '#' . $mainAnchor;
          ?>
          <div class="toc-group">
            <h4><a href="<?= e($mainHref) ?>" class="toc-main"><?= e((string) ($main['name'] ?? '')) ?></a></h4>
            <?php foreach ($children as $child): ?>
              <?php
                $childSlug = (string) ($child['slug'] ?? '');
                $childHref = $childSlug !== '' ? wiki_url($childSlug) : url('/wiki');
                $isActive = $wikiMode === 'page' && $wikiCurrentSlug !== '' && $wikiCurrentSlug === $childSlug;
              ?>
              <a href="<?= e($childHref) ?>" class="<?= $isActive ? 'active' : '' ?>"><i class="fa-solid fa-file-lines"></i> <?= e((string) ($child['name'] ?? '')) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </aside>

    <div class="wiki-content">
      <?php if ($wikiMode === 'page'): ?>
        <?php
          $pageBody = is_array($wikiPage) ? (string) ($wikiPage['body_html'] ?? '') : '';
          $typeSlug = is_array($wikiPage) ? (string) ($wikiPage['content_type_slug'] ?? '') : '';
        ?>
        <section>
          <p style="margin-bottom:18px;"><a href="<?= e(url('/wiki')) ?>" style="color:var(--gold-light);font-size:.85rem;"><i class="fa-solid fa-arrow-left"></i> Wiki’ye dön</a></p>
          <?php if ($pageBody !== '' && ($typeSlug === '' || $typeSlug === 'basit-metin')): ?>
            <div class="wiki-prose"><?= $pageBody ?></div>
          <?php elseif ($pageBody !== ''): ?>
            <p class="wiki-empty">Bu içerik tipi henüz desteklenmiyor.</p>
          <?php else: ?>
            <p class="wiki-empty">Bu sayfa için henüz içerik eklenmedi.</p>
          <?php endif; ?>
        </section>
      <?php elseif ($wikiCategories === []): ?>
        <section id="wiki-empty">
          <div class="section-title">
            <div class="eyebrow">Wiki</div>
            <h2>Bilgi Bankası</h2>
            <p class="wiki-empty">Henüz kategori yok.</p>
          </div>
        </section>
      <?php else: ?>
        <?php foreach ($wikiCategories as $main): ?>
          <?php
            $mainId = (int) ($main['id'] ?? 0);
            $mainAnchor = \App\Services\WikiCategoryService::anchorId($mainId);
            $children = is_array($main['children'] ?? null) ? $main['children'] : [];
          ?>
          <section id="<?= e($mainAnchor) ?>">
            <div class="section-title">
              <div class="eyebrow">Kategori</div>
              <h2><?= e((string) ($main['name'] ?? '')) ?></h2>
            </div>
            <?php if ($children === []): ?>
              <p class="wiki-empty">Bu bölüm için henüz alt sayfa yok.</p>
            <?php else: ?>
              <div class="wiki-child-grid">
                <?php foreach ($children as $child): ?>
                  <?php
                    $childId = (int) ($child['id'] ?? 0);
                    $childSlug = (string) ($child['slug'] ?? '');
                    $page = $wikiPagesByCategory[$childId] ?? null;
                    $cardTitle = is_array($page) && trim((string) ($page['title'] ?? '')) !== ''
                      ? (string) $page['title']
                      : (string) ($child['name'] ?? '');
                    $href = $childSlug !== '' ? wiki_url($childSlug) : url('/wiki');
                  ?>
                  <a class="wiki-child" href="<?= e($href) ?>" style="display:block;color:inherit;text-decoration:none;">
                    <h3><?= e($cardTitle) ?></h3>
                    <p><?= is_array($page) ? 'Sayfayı aç' : 'İçerik yakında eklenecek.' ?></p>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
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

  const spyTargets = document.querySelectorAll('.wiki-content section, .wiki-content .wiki-child');
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
  spyTargets.forEach(s => spy.observe(s));

  document.getElementById('wikiSearch')?.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const q = e.target.value.toLowerCase().trim();
    if (!q) return;
    const match = Array.from(document.querySelectorAll('.wiki-content h2, .wiki-content h3')).find(el =>
      el.textContent.toLowerCase().includes(q)
    );
    if (match) match.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
</script>
</body>
</html>