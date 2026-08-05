<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var array $currentServer */
/** @var array $servers */
/** @var string $csrf */
/** @var array|null $authUser */
/** @var array $account */
/** @var list<array> $characters */
/** @var array|null $primary */
/** @var int $maxLevel */
/** @var int $characterCount */
/** @var int $totalYang */
/** @var int $openTickets */
/** @var array $security */
/** @var array|null $totpSetup */
/** @var string $searchQuery */
/** @var list<array> $searchResults */
/** @var list<string> $panelErrors */
/** @var string|null $panelSuccess */
/** @var string|null $panelSection */

$appName = $appName ?? 'M2DN';
$appTagline = $appTagline ?? '';
$currentServer = is_array($currentServer ?? null) ? $currentServer : [];
$servers = is_array($servers ?? null) ? $servers : [];
$csrf = $csrf ?? '';
$authUser = is_array($authUser ?? null) ? $authUser : null;
$account = is_array($account ?? null) ? $account : [];
$characters = is_array($characters ?? null) ? $characters : [];
$primary = is_array($primary ?? null) ? $primary : null;
$maxLevel = max(1, (int) ($maxLevel ?? 99));
$characterCount = (int) ($characterCount ?? count($characters));
$totalYang = (int) ($totalYang ?? 0);
$openTickets = (int) ($openTickets ?? 0);
$security = is_array($security ?? null) ? $security : [];
$totpSetup = is_array($totpSetup ?? null) ? $totpSetup : null;
$searchQuery = (string) ($searchQuery ?? '');
$searchResults = is_array($searchResults ?? null) ? $searchResults : [];
$panelErrors = is_array($panelErrors ?? null) ? $panelErrors : [];
$panelSuccess = is_string($panelSuccess ?? null) ? $panelSuccess : null;
$panelSection = is_string($panelSection ?? null) && $panelSection !== '' ? $panelSection : 'ozet';
$totpOn = !empty($security['totp_enabled']);
$ipLockOn = !empty($security['ip_lock_enabled']);
$notifyOn = !empty($security['login_notify']);

$levelPct = 0;
if ($primary) {
    $levelPct = \App\Services\PlayerService::levelProgressPercent(
        (int) $primary['level'],
        $maxLevel,
        (int) ($primary['level_step'] ?? 0)
    );
}

$createLabel = '—';
if (!empty($account['create_time']) && $account['create_time'] !== '0000-00-00 00:00:00') {
    $ts = strtotime((string) $account['create_time']);
    if ($ts) {
        $createLabel = date('d.m.Y', $ts) . "'den beri";
    }
}

$lastPlayLabel = '—';
if ($primary && !empty($primary['last_play']) && $primary['last_play'] !== '0000-00-00 00:00:00') {
    $ts = strtotime((string) $primary['last_play']);
    if ($ts) {
        $lastPlayLabel = date('d.m.Y H:i', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oyuncu Paneli | <?= e($appName) ?></title>
<link rel="icon" href="<?= e(asset('img/logo-mark.svg')) ?>" type="image/svg+xml">
<link rel="shortcut icon" href="<?= e(asset('img/logo-mark.svg')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('img/logo-mark.svg')) ?>">
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
    --sidebar-w:264px;
    --font-display:'Cinzel', serif; --font-brush:'Ma Shan Zheng', cursive; --font-body:'Inter', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--obsidian); color:var(--parchment); font-family:var(--font-body); min-height:100vh;}
  a{color:inherit; text-decoration:none;} ul{list-style:none;} button{font-family:inherit; cursor:pointer;}
  h1,h2,h3{font-family:var(--font-display);}
  ::selection{background:var(--blood); color:var(--gold-light);}
  input, select, textarea{font-family:inherit;}

  .eyebrow{font-family:var(--font-brush); font-size:1.1rem; color:var(--blood-light); display:inline-flex; align-items:center; gap:.5rem;}
  .eyebrow::before{content:""; width:20px; height:1px; background:var(--gold);}

  /* ===== LAYOUT ===== */
  .layout{display:grid; grid-template-columns:var(--sidebar-w) 1fr; min-height:100vh;}

  /* ===== SIDEBAR ===== */
  .sidebar{
    background:var(--obsidian-2); border-right:1px solid var(--line);
    padding:26px 18px; display:flex; flex-direction:column; gap:6px;
    position:sticky; top:0; height:100vh; overflow-y:auto;
  }
  .sidebar-brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; font-size:1.15rem; letter-spacing:.06em; color:var(--gold-light); padding:0 10px 26px; margin-bottom:10px; border-bottom:1px solid var(--line); text-decoration:none;}
  .sidebar-brand img{width:36px; height:36px; flex-shrink:0; display:block;}
  .sidebar-brand span{color:var(--blood-light);}
  .sidebar-brand:hover{opacity:.92;}

  .nav-group-label{font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; color:var(--ash); padding:18px 12px 8px;}
  .nav-item{
    display:flex; align-items:center; gap:12px; padding:11px 12px;
    color:var(--ash); font-size:.9rem; font-weight:500; border-left:2px solid transparent;
    transition:background .2s, color .2s, border-color .2s;
  }
  .nav-item i{width:18px; text-align:center; font-size:.95rem;}
  .nav-item:hover{background:rgba(201,151,74,.06); color:var(--parchment);}
  .nav-item.active{background:linear-gradient(90deg, rgba(143,28,41,.16), transparent); color:var(--gold-light); border-left-color:var(--gold);}

  .sidebar-foot{margin-top:auto; padding-top:18px; border-top:1px solid var(--line);}
  .sidebar-char{display:flex; align-items:center; gap:10px; padding:10px 12px;}
  .avatar-ring{width:38px; height:38px; border-radius:50%; background:conic-gradient(var(--gold), var(--blood), var(--gold)); display:flex; align-items:center; justify-content:center; flex-shrink:0;}
  .avatar-ring i{width:32px; height:32px; border-radius:50%; background:var(--obsidian-2); display:flex; align-items:center; justify-content:center; color:var(--gold-light); font-size:.85rem;}
  .sidebar-char .who{font-size:.82rem; color:var(--parchment); font-weight:600;}
  .sidebar-char .role{font-size:.7rem; color:var(--ash);}
  .logout-link{display:flex; align-items:center; gap:10px; padding:10px 12px; color:var(--blood-light); font-size:.85rem; margin-top:6px;}

  /* ===== MAIN ===== */
  .main{padding:26px 34px 60px; max-width:1280px;}

  .topbar{display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:30px; flex-wrap:wrap;}
  .topbar h1{font-size:1.5rem; color:var(--parchment);}
  .topbar .sub{color:var(--ash); font-size:.85rem; margin-top:4px; font-family:var(--font-body);}
  .top-actions{display:flex; align-items:center; gap:16px;}
  .search-box{display:flex; align-items:center; gap:8px; background:var(--obsidian-2); border:1px solid var(--line); padding:9px 14px; font-size:.82rem; color:var(--ash); min-width:220px;}
  .search-box input{background:none; border:none; outline:none; color:var(--parchment); font-size:.82rem; width:100%;}
  .icon-btn{position:relative; width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:var(--obsidian-2); border:1px solid var(--line); color:var(--gold-light); font-size:.9rem;}
  .icon-btn .dot{position:absolute; top:6px; right:7px; width:6px; height:6px; border-radius:50%; background:var(--blood-light);}
  .status-pill{display:flex; align-items:center; gap:8px; padding:8px 14px; background:rgba(51,89,74,.15); border:1px solid rgba(79,138,113,.3); font-size:.78rem; color:var(--jade-light); text-transform:uppercase; letter-spacing:.05em;}
  .status-pill .pulse{width:7px; height:7px; border-radius:50%; background:var(--jade-light); box-shadow:0 0 0 0 rgba(79,138,113,.6); animation:pulse 2s infinite;}
  .session-timer{display:flex; align-items:center; gap:8px; padding:8px 14px; background:rgba(201,151,74,.1); border:1px solid rgba(201,151,74,.28); font-size:.78rem; color:var(--gold-light); letter-spacing:.04em; font-variant-numeric:tabular-nums;}
  .session-timer.warn{background:rgba(143,28,41,.18); border-color:rgba(197,51,71,.4); color:#e8a0a8;}
  .session-timer i{font-size:.75rem; opacity:.85;}
  .session-timer .t{font-weight:700; min-width:3.2em;}
  @keyframes pulse{ 0%{box-shadow:0 0 0 0 rgba(79,138,113,.5);} 70%{box-shadow:0 0 0 8px rgba(79,138,113,0);} 100%{box-shadow:0 0 0 0 rgba(79,138,113,0);} }

  /* ===== CARDS ===== */
  .grid{display:grid; gap:20px;}
  .grid-4{grid-template-columns:repeat(4,1fr);}
  .grid-3{grid-template-columns:2fr 1fr;}
  .grid-2{grid-template-columns:1fr 1fr;}

  .card{
    background:var(--obsidian-2); border:1px solid var(--line); padding:24px;
    clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);
  }
  .card-head{display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;}
  .card-head h3{font-size:1rem; color:var(--parchment); font-weight:600; letter-spacing:.02em;}
  .card-head a{font-size:.78rem; color:var(--gold-light);}

  .stat-card{display:flex; flex-direction:column; gap:10px;}
  .stat-card .icon{width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:1rem;}
  .stat-card strong{font-family:var(--font-display); font-size:1.7rem; color:var(--parchment);}
  .stat-card span.lbl{font-size:.78rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em;}
  .stat-card .delta{font-size:.75rem; color:var(--jade-light);}
  .stat-card .delta.down{color:var(--blood-light);}

  /* character overview card */
  .char-banner{
    display:flex; align-items:center; gap:22px; padding:26px;
    background:radial-gradient(ellipse 500px 200px at 15% 30%, rgba(143,28,41,.18), transparent), var(--obsidian-2);
    border:1px solid var(--line);
    clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);
    margin-bottom:22px;
  }
  .char-portrait{width:110px; height:110px; border-radius:50%; background:conic-gradient(var(--gold), var(--blood-light), var(--jade), var(--gold)); display:flex; align-items:center; justify-content:center; flex-shrink:0; padding:3px;}
  .char-portrait-inner{width:100%; height:100%; border-radius:50%; background:var(--obsidian); overflow:hidden; display:flex; align-items:center; justify-content:center;}
  .char-portrait-inner img{width:100%; height:100%; object-fit:cover; object-position:center top; display:block;}
  .char-portrait-inner i{font-size:1.8rem; color:var(--gold-light);}
  .char-info h2{font-size:1.3rem; color:var(--parchment);}
  .char-info .meta{display:flex; gap:16px; margin-top:8px; flex-wrap:wrap;}
  .char-info .meta span{font-size:.78rem; color:var(--ash);}
  .char-info .meta span b{color:var(--gold-light); font-weight:700;}
  .exp-bar{margin-top:14px; width:320px; max-width:100%;}
  .exp-bar .track{height:8px; background:rgba(233,223,198,.08); position:relative; clip-path:polygon(4px 0,100% 0,100% calc(100% - 4px),calc(100% - 4px) 100%,0 100%,0 4px);}
  .exp-bar .fill{position:absolute; inset:0; width:0; background:linear-gradient(90deg, var(--jade), var(--gold));}
  .char-empty{padding:28px; border:1px dashed var(--line); color:var(--ash); font-size:.9rem; margin-bottom:22px;}
  .exp-bar .lbl{display:flex; justify-content:space-between; font-size:.7rem; color:var(--ash); margin-top:6px; text-transform:uppercase; letter-spacing:.05em;}
  .char-banner .quick-actions{margin-left:auto; display:flex; flex-direction:column; gap:10px;}

  .btn{padding:11px 20px; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; display:inline-flex; align-items:center; gap:8px; border:none; transition:transform .2s, background .2s; text-decoration:none; cursor:pointer;
    clip-path:polygon(8px 0,100% 0,100% calc(100% - 8px),calc(100% - 8px) 100%,0 100%,0 8px);}
  .btn-primary{background:linear-gradient(135deg, var(--blood-light), var(--blood)); color:var(--parchment);}
  .btn-primary:hover{transform:translateY(-2px);}
  .btn-ghost{background:none; border:1px solid var(--line); color:var(--gold-light);}
  .btn-ghost:hover{background:rgba(201,151,74,.08);}
  .btn-sm{padding:7px 14px; font-size:.72rem;}

  /* table */
  table{width:100%; border-collapse:collapse; font-size:.85rem;}
  thead th{text-align:left; padding:10px 14px; color:var(--ash); font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--line); font-weight:600;}
  tbody td{padding:14px; border-bottom:1px solid rgba(201,151,74,.08); color:var(--parchment);}
  tbody tr:hover{background:rgba(201,151,74,.04);}
  .row-class{display:flex; align-items:center; gap:10px;}
  .row-class i{width:26px; height:26px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:.75rem;}

  .badge{display:inline-flex; align-items:center; gap:6px; padding:4px 10px; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600;}
  .badge.online{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .badge.offline{background:rgba(154,143,128,.15); color:var(--ash);}
  .badge.pending{background:rgba(201,151,74,.15); color:var(--gold-light);}
  .badge.closed{background:rgba(154,143,128,.15); color:var(--ash);}

  /* shop grid */
  .shop-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:18px;}
  .shop-item{background:var(--obsidian); border:1px solid var(--line); padding:20px; text-align:center; transition:border-color .25s, transform .25s;}
  .shop-item:hover{border-color:var(--gold); transform:translateY(-4px);}
  .shop-item .thumb{width:56px; height:56px; margin:0 auto 14px; background:radial-gradient(circle, rgba(201,151,74,.18), transparent 70%); display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:var(--gold-light);}
  .shop-item h4{font-size:.88rem; color:var(--parchment); margin-bottom:6px; font-family:var(--font-display);}
  .shop-item .price{font-size:.85rem; color:var(--blood-light); font-weight:700; margin-bottom:14px;}

  /* vote list */
  .vote-item{display:flex; align-items:center; justify-content:space-between; padding:16px 4px; border-bottom:1px solid rgba(201,151,74,.08);}
  .vote-item:last-child{border-bottom:none;}
  .vote-item .name{font-weight:600; color:var(--parchment); font-size:.9rem;}
  .vote-item .cooldown{font-size:.72rem; color:var(--ash); margin-top:2px;}

  /* toggle switch */
  .toggle{position:relative; width:44px; height:24px; background:rgba(233,223,198,.1); border-radius:20px; flex-shrink:0; cursor:pointer; transition:background .25s;}
  .toggle::after{content:""; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:var(--ash); transition:transform .25s, background .25s;}
  .toggle.on{background:rgba(79,138,113,.3);}
  .toggle.on::after{transform:translateX(20px); background:var(--jade-light);}

  /* forms */
  .form-row{margin-bottom:18px;}
  .form-row label{display:block; font-size:.78rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;}
  .form-row input{width:100%; background:var(--obsidian); border:1px solid var(--line); padding:12px 14px; color:var(--parchment); font-size:.88rem; outline:none; transition:border-color .2s;}
  .form-row input:focus{border-color:var(--gold);}
  .security-row{display:flex; align-items:center; justify-content:space-between; padding:16px 0; border-bottom:1px solid rgba(201,151,74,.08);}
  .security-row:last-child{border-bottom:none;}
  .security-row .t{font-size:.9rem; color:var(--parchment); font-weight:600;}
  .security-row .d{font-size:.78rem; color:var(--ash); margin-top:3px;}
  .panel-alert{padding:12px 14px; margin-bottom:18px; font-size:.85rem; border:1px solid var(--line);}
  .panel-alert.ok{background:rgba(51,89,74,.18); color:var(--jade-light); border-color:rgba(79,138,113,.35);}
  .panel-alert.err{background:rgba(143,28,41,.18); color:#e8a0a8; border-color:rgba(197,51,71,.35);}
  .panel-alert ul{margin:6px 0 0 18px; list-style:disc;}
  .search-wrap{position:relative; min-width:220px;}
  .search-box{width:100%;}
  .search-drop{position:absolute; top:calc(100% + 6px); left:0; right:0; background:var(--obsidian-2); border:1px solid var(--line); z-index:40; max-height:260px; overflow:auto;}
  .search-drop a{display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:.82rem; border-bottom:1px solid rgba(201,151,74,.08);}
  .search-drop a:hover{background:rgba(201,151,74,.08);}
  .search-drop .empty{padding:12px; font-size:.8rem; color:var(--ash);}
  .secret-box{font-family:ui-monospace,monospace; letter-spacing:.08em; background:var(--obsidian); border:1px solid var(--line); padding:10px 12px; font-size:.85rem; word-break:break-all; margin:8px 0 14px;}
  .btn-block{width:100%; justify-content:center;}
  button.btn{border:none; display:inline-flex; align-items:center; gap:8px;}
  button.toggle-btn{background:none; border:none; padding:0;}

  .section{display:none;}
  .section.active{display:block;}

  @media (max-width:1100px){
    .grid-4{grid-template-columns:repeat(2,1fr);}
    .grid-3{grid-template-columns:1fr;}
    .shop-grid{grid-template-columns:repeat(2,1fr);}
    .char-banner{flex-wrap:wrap;}
    .char-banner .quick-actions{margin-left:0;}
  }
  @media (max-width:820px){
    .layout{grid-template-columns:1fr;}
    .sidebar{position:fixed; left:-280px; top:0; width:260px; z-index:200; transition:left .3s; box-shadow:20px 0 40px rgba(0,0,0,.5);}
    .sidebar.open{left:0;}
    .main{padding:20px 18px 60px;}
    .mobile-toggle{display:flex !important;}
  }
  .mobile-toggle{display:none; width:38px; height:38px; align-items:center; justify-content:center; background:var(--obsidian-2); border:1px solid var(--line); color:var(--gold-light);}
  @media (max-width:600px){
    .grid-4{grid-template-columns:1fr 1fr;}
    .shop-grid{grid-template-columns:1fr 1fr;}
  }
</style>
</head>
<body>
<div class="layout">

  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" id="sidebar">
    <a href="<?= e(url('/')) ?>" class="sidebar-brand" aria-label="<?= e($appName) ?> Anasayfa">
      <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="<?= e($appName) ?>">
      M2<span>DN</span>
    </a>

    <?php if (!empty($servers) && count($servers) > 1): ?>
    <form method="post" action="<?= e(url('/server/select')) ?>" style="padding:0 12px 14px;">
      <?= $csrf ?>
      <input type="hidden" name="redirect" value="/panel">
      <label style="display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--ash);margin-bottom:8px;">Sunucu</label>
      <select name="server" onchange="this.form.submit()" style="width:100%;background:var(--obsidian);border:1px solid var(--line);color:var(--parchment);padding:9px 10px;font-size:.82rem;">
        <?php foreach ($servers as $key => $srv): ?>
          <option value="<?= e($key) ?>" <?= ($currentServer['key'] ?? '') === $key ? 'selected' : '' ?>><?= e($srv['name'] ?? $key) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <?php endif; ?>

    <div class="nav-group-label">Genel</div>
    <a class="nav-item<?= $panelSection === 'ozet' ? ' active' : '' ?>" data-target="ozet"><i class="fa-solid fa-gauge-high"></i> Genel Bakış</a>
    <a class="nav-item<?= $panelSection === 'karakterler' ? ' active' : '' ?>" data-target="karakterler"><i class="fa-solid fa-khanda"></i> Karakterlerim</a>

    <div class="nav-group-label">Oyun</div>
    <a class="nav-item" data-target="magaza"><i class="fa-solid fa-store"></i> Mağaza</a>
    <a class="nav-item" data-target="oyver"><i class="fa-solid fa-thumbs-up"></i> Oy Ver &amp; Kazan</a>

    <div class="nav-group-label">Hesap</div>
    <a class="nav-item" data-target="destek"><i class="fa-solid fa-headset"></i> Destek Talepleri</a>
    <a class="nav-item<?= $panelSection === 'guvenlik' ? ' active' : '' ?>" data-target="guvenlik"><i class="fa-solid fa-shield-halved"></i> Hesap Güvenliği</a>

    <?php if (\App\Services\AuthService::canAccessAdmin($authUser)): ?>
    <div class="nav-group-label">Yönetim</div>
    <a class="nav-item" href="<?= e(url('/admin')) ?>"><i class="fa-solid fa-screwdriver-wrench"></i> Admin Panel</a>
    <?php endif; ?>

    <div class="sidebar-foot">
      <div class="sidebar-char">
        <div class="avatar-ring"><i class="fa-solid fa-user"></i></div>
        <div>
          <div class="who"><?= e((string) ($authUser['login'] ?? 'Oyuncu')) ?></div>
          <div class="role">
            <?php
              $p = (int) ($authUser['permission'] ?? 0);
              echo $p === 2 ? 'Süper Admin' : ($p === 1 ? 'Yönetici' : 'Oyuncu');
            ?>
          </div>
        </div>
      </div>
      <a href="<?= e(url('/cikis')) ?>" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <main class="main">

    <div class="topbar">
      <div style="display:flex; align-items:center; gap:14px;">
        <button class="mobile-toggle" id="mobileToggle"><i class="fa-solid fa-bars"></i></button>
        <div>
          <h1>Hoş geldin, <?= e((string) ($authUser['login'] ?? 'Oyuncu')) ?></h1>
          <div class="sub"><?= e($currentServer['name'] ?? 'M2DN') ?> · Hesabına ve karakterine genel bir bakış.</div>
        </div>
      </div>
      <div class="top-actions">
        <?php if (\App\Services\AuthService::canAccessAdmin($authUser)): ?>
          <a href="<?= e(url('/admin')) ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-screwdriver-wrench"></i> Admin Panel</a>
        <?php endif; ?>
        <div class="status-pill"><div class="pulse"></div> Sunucu açık</div>
        <div class="session-timer" id="sessionTimer" title="Oturum süresi" data-expires="<?= (int) ($authUser['session_expires_at'] ?? 0) ?>" data-logout="<?= e(url('/cikis')) ?>">
          <i class="fa-solid fa-hourglass-half"></i>
          <span>Oturum</span>
          <span class="t" id="sessionTimerValue">--:--</span>
        </div>
        <div class="search-wrap">
          <form class="search-box" method="get" action="<?= e(url('/panel')) ?>" role="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="q" value="<?= e($searchQuery) ?>" placeholder="Karakter ara..." autocomplete="off" minlength="2">
          </form>
          <?php if ($searchQuery !== ''): ?>
          <div class="search-drop">
            <?php if ($searchResults === []): ?>
              <div class="empty">Hesabında “<?= e($searchQuery) ?>” ile eşleşen karakter yok.</div>
            <?php else: ?>
              <?php foreach ($searchResults as $sr): ?>
                <a href="<?= e(url('/panel/karakter?id=' . (int) $sr['id'])) ?>">
                  <i class="fa-solid <?= e($sr['job_icon']) ?>" style="color:var(--gold-light);"></i>
                  <span><?= e($sr['name']) ?> · Sv. <?= (int) $sr['level'] ?> · <?= e($sr['job_label']) ?></span>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <button class="icon-btn" type="button" aria-label="Bildirimler"><i class="fa-solid fa-bell"></i><span class="dot"></span></button>
      </div>
    </div>

    <?php if ($panelSuccess): ?>
      <div class="panel-alert ok"><?= e($panelSuccess) ?></div>
    <?php endif; ?>
    <?php if ($panelErrors !== []): ?>
      <div class="panel-alert err">
        <ul>
          <?php foreach ($panelErrors as $err): ?>
            <li><?= e((string) $err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- ===================== GENEL BAKIŞ ===================== -->
    <section class="section<?= $panelSection === 'ozet' ? ' active' : '' ?>" id="ozet">

      <?php if ($primary): ?>
      <div class="char-banner">
        <div class="char-portrait">
          <div class="char-portrait-inner">
            <?php if (!empty($primary['job_gif'])): ?>
              <img src="<?= e((string) $primary['job_gif']) ?>" alt="<?= e($primary['job_label']) ?>" loading="lazy">
            <?php else: ?>
              <i class="fa-solid <?= e($primary['job_icon']) ?>"></i>
            <?php endif; ?>
          </div>
        </div>
        <div class="char-info">
          <h2><?= e($primary['name']) ?> <span style="color:var(--ash); font-weight:400; font-size:.85rem;">— <?= e($primary['job_label']) ?></span></h2>
          <div class="meta">
            <span>Seviye <b><?= (int) $primary['level'] ?></b> / <?= (int) $maxLevel ?></span>
            <?php if (!empty($primary['guild'])): ?>
              <span>Klan <b><?= e($primary['guild']) ?></b></span>
            <?php else: ?>
              <span>Klan <b>—</b></span>
            <?php endif; ?>
            <span>Krallık <b><?= e($primary['empire_label']) ?></b></span>
            <span>Oyun Süresi <b><?= e($primary['playtime_label']) ?></b></span>
          </div>
          <div class="exp-bar">
            <div class="track"><div class="fill" style="width:<?= (int) $levelPct ?>%"></div></div>
            <div class="lbl">
              <span>Seviye <?= (int) $primary['level'] ?></span>
              <span>%<?= (int) $levelPct ?></span>
              <span>Maks <?= (int) $maxLevel ?></span>
            </div>
          </div>
        </div>
        <div class="quick-actions">
          <a class="btn btn-primary btn-sm" href="#karakterler" data-jump="karakterler"><i class="fa-solid fa-users"></i> Karakterler</a>
          <a class="btn btn-ghost btn-sm" href="#destek" data-jump="destek"><i class="fa-solid fa-headset"></i> Destek Aç</a>
        </div>
      </div>
      <?php else: ?>
      <div class="char-empty">
        Bu hesaba bağlı karakter bulunamadı. Oyuna girip karakter oluşturduğunda burada görünecek.
      </div>
      <?php endif; ?>

      <div class="grid grid-4" style="margin-bottom:22px;">
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-coins"></i></div>
          <strong><?= number_format($primary ? (int) $primary['gold'] : $totalYang, 0, ',', '.') ?></strong>
          <span class="lbl">Yang<?= $primary ? '' : ' (Toplam)' ?></span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-gem"></i></div>
          <strong><?= number_format((int) ($account['cash'] ?? 0), 0, ',', '.') ?></strong>
          <span class="lbl">Oyun Parası (Cash)</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-khanda"></i></div>
          <strong><?= (int) $characterCount ?></strong>
          <span class="lbl">Toplam Karakter</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-ticket"></i></div>
          <strong><?= (int) $openTickets ?></strong>
          <span class="lbl">Açık Destek Talebi</span>
        </div>
      </div>

      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Karakterlerim</h3><a href="#karakterler" data-jump="karakterler">Tümü</a></div>
          <?php if ($characters === []): ?>
            <div style="color:var(--ash); font-size:.85rem;">Henüz karakter yok.</div>
          <?php else: ?>
            <?php foreach (array_slice($characters, 0, 4) as $ch): ?>
            <a class="vote-item" href="<?= e(url('/panel/karakter?id=' . (int) $ch['id'])) ?>" style="color:inherit;">
              <div>
                <div class="name"><i class="fa-solid <?= e($ch['job_icon']) ?>" style="margin-right:6px;color:var(--gold-light);"></i><?= e($ch['name']) ?></div>
                <div class="cooldown"><?= e($ch['job_label']) ?> · Sv. <?= (int) $ch['level'] ?><?= !empty($ch['guild']) ? ' · ' . e($ch['guild']) : '' ?></div>
              </div>
              <span class="badge offline">Sv. <?= (int) $ch['level'] ?></span>
            </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="card-head"><h3>Hesap Özeti</h3></div>
          <div class="security-row">
            <div><div class="t">Kullanıcı Adı</div><div class="d"><?= e((string) ($account['login'] ?? '')) ?></div></div>
            <span class="badge <?= strtoupper((string)($account['status'] ?? '')) === 'OK' ? 'online' : 'offline' ?>"><?= e((string) ($account['status'] ?? '—')) ?></span>
          </div>
          <div class="security-row">
            <div><div class="t">E-posta</div><div class="d"><?= e((string) ($account['email'] ?? '—')) ?></div></div>
          </div>
          <div class="security-row">
            <div><div class="t">Son Oyun</div><div class="d"><?= e($lastPlayLabel) ?></div></div>
          </div>
          <div class="security-row">
            <div><div class="t">Üyelik</div><div class="d"><?= e($createLabel) ?></div></div>
          </div>
          <?php if ((int) ($account['mileage'] ?? 0) > 0): ?>
          <div class="security-row">
            <div><div class="t">Mileage</div><div class="d"><?= number_format((int) $account['mileage'], 0, ',', '.') ?></div></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ===================== KARAKTERLER ===================== -->
    <section class="section<?= $panelSection === 'karakterler' ? ' active' : '' ?>" id="karakterler">
      <div class="card">
        <div class="card-head"><h3>Karakterlerim</h3><span style="font-size:.8rem;color:var(--ash);"><?= (int) $characterCount ?> karakter · Maks seviye <?= (int) $maxLevel ?></span></div>
        <?php if ($characters === []): ?>
          <div style="color:var(--ash); font-size:.9rem; padding:8px 0;">Bu hesaba bağlı karakter yok.</div>
        <?php else: ?>
        <table>
          <thead><tr><th>Karakter</th><th>Sınıf</th><th>Seviye</th><th>Klan</th><th>Yang</th><th>Oyun Süresi</th></tr></thead>
          <tbody>
            <?php foreach ($characters as $ch): ?>
            <tr>
              <td class="row-class">
                <a href="<?= e(url('/panel/karakter?id=' . (int) $ch['id'])) ?>" style="display:inline-flex;align-items:center;gap:10px;color:inherit;">
                  <i class="fa-solid <?= e($ch['job_icon']) ?>"></i> <?= e($ch['name']) ?>
                </a>
              </td>
              <td><?= e($ch['job_label']) ?></td>
              <td><?= (int) $ch['level'] ?> / <?= (int) $maxLevel ?></td>
              <td><?= e($ch['guild'] ?? '—') ?></td>
              <td><?= number_format((int) $ch['gold'], 0, ',', '.') ?></td>
              <td><?= e($ch['playtime_label']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== MAĞAZA ===================== -->
    <section class="section" id="magaza">
      <div class="card">
        <div class="card-head"><h3>Cash Mağaza</h3><span style="font-size:.8rem; color:var(--ash);">Bakiye: <b style="color:var(--gold-light);">3.450 Puan</b></span></div>
        <div class="shop-grid">
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-hat-wizard"></i></div><h4>Ejderha Miğferi</h4><div class="price">450 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-wand-sparkles"></i></div><h4>Şans Tılsımı x5</h4><div class="price">180 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-horse"></i></div><h4>Kızıl Binek</h4><div class="price">900 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-shirt"></i></div><h4>M2DN Zırh Kostümü</h4><div class="price">650 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-flask"></i></div><h4>Deneyim İksiri</h4><div class="price">120 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-gem"></i></div><h4>+500 Cash Puan</h4><div class="price">₺99</div><a class="btn btn-ghost btn-sm">Yükle</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-paw"></i></div><h4>Ruh Yoldaşı</h4><div class="price">720 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
          <div class="shop-item"><div class="thumb"><i class="fa-solid fa-box"></i></div><h4>Gizem Sandığı</h4><div class="price">250 Puan</div><a class="btn btn-ghost btn-sm">Satın Al</a></div>
        </div>
      </div>
    </section>

    <!-- ===================== OY VER ===================== -->
    <section class="section" id="oyver">
      <div class="card">
        <div class="card-head"><h3>Oy Ver &amp; Kazan</h3><span style="font-size:.8rem; color:var(--ash);">Her oy +50 Cash Puan</span></div>
        <div class="vote-item"><div><div class="name">Metin2Top100</div><div class="cooldown">Son oy: 6 saat önce</div></div><a class="btn btn-ghost btn-sm">18:22'de hazır</a></div>
        <div class="vote-item"><div><div class="name">Oyunsunucular.net</div><div class="cooldown">Kullanılabilir</div></div><a class="btn btn-primary btn-sm">Oy Ver</a></div>
        <div class="vote-item"><div><div class="name">MTliste</div><div class="cooldown">Kullanılabilir</div></div><a class="btn btn-primary btn-sm">Oy Ver</a></div>
        <div class="vote-item"><div><div class="name">M2Rank</div><div class="cooldown">Son oy: 20 saat önce</div></div><a class="btn btn-ghost btn-sm">2:10'da hazır</a></div>
      </div>
    </section>

    <!-- ===================== DESTEK ===================== -->
    <section class="section" id="destek">
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Destek Taleplerim</h3></div>
          <table>
            <thead><tr><th>Konu</th><th>Kategori</th><th>Tarih</th><th>Durum</th></tr></thead>
            <tbody>
              <tr><td>Eşya kayboldu (Ejderha Miğferi)</td><td>Eşya Sorunu</td><td>12 Tem</td><td><span class="badge pending">Yanıt Bekliyor</span></td></tr>
              <tr><td>Karakter bağlantı hatası</td><td>Teknik</td><td>08 Tem</td><td><span class="badge closed">Çözüldü</span></td></tr>
              <tr><td>Cash Puan yüklenmedi</td><td>Ödeme</td><td>02 Tem</td><td><span class="badge closed">Çözüldü</span></td></tr>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3>Yeni Talep Oluştur</h3></div>
          <div class="form-row"><label>Konu</label><input placeholder="Kısaca sorunu özetle"></div>
          <div class="form-row"><label>Kategori</label><input placeholder="Eşya / Teknik / Ödeme"></div>
          <a class="btn btn-primary" style="width:100%; justify-content:center;">Talep Gönder</a>
        </div>
      </div>
    </section>

    <!-- ===================== GÜVENLİK ===================== -->
    <section class="section<?= $panelSection === 'guvenlik' ? ' active' : '' ?>" id="guvenlik">
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Şifre Değiştir</h3></div>
          <form method="post" action="<?= e(url('/panel/guvenlik/sifre')) ?>" autocomplete="off">
            <?= $csrf ?>
            <div class="form-row"><label for="cur-pass">Mevcut Şifre</label><input id="cur-pass" name="current_password" type="password" maxlength="16" required placeholder="••••••••"></div>
            <div class="form-row"><label for="new-pass">Yeni Şifre</label><input id="new-pass" name="new_password" type="password" maxlength="16" required placeholder="••••••••"></div>
            <div class="form-row"><label for="new-pass2">Yeni Şifre (Tekrar)</label><input id="new-pass2" name="new_password_confirm" type="password" maxlength="16" required placeholder="••••••••"></div>
            <button type="submit" class="btn btn-primary btn-block">Şifreyi Güncelle</button>
          </form>
        </div>

        <div class="card">
          <div class="card-head"><h3>Depo / Güvenli Şifre</h3></div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:14px;">Depo şifresini sıfırlamak için hesap parolanı doğrula, ardından yeni 6 haneli kodu gir.</p>
          <form method="post" action="<?= e(url('/panel/guvenlik/depo')) ?>" autocomplete="off">
            <?= $csrf ?>
            <div class="form-row"><label for="sec-pass">Hesap Parolası</label><input id="sec-pass" name="password" type="password" maxlength="16" required></div>
            <div class="form-row"><label for="sec-new">Yeni Güvenli Şifre</label><input id="sec-new" name="new_securitycode" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <div class="form-row"><label for="sec-new2">Tekrar</label><input id="sec-new2" name="new_securitycode_confirm" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <button type="submit" class="btn btn-primary btn-block">Güvenli Şifreyi Güncelle</button>
          </form>
        </div>

        <div class="card">
          <div class="card-head"><h3>Güvenlik Ayarları</h3></div>

          <div class="security-row">
            <div>
              <div class="t">İki Adımlı Doğrulama</div>
              <div class="d"><?= $totpOn ? 'Aktif — girişte uygulama kodu gerekir' : 'Kapalı — Google Authenticator vb. ile açabilirsin' ?></div>
            </div>
            <span class="badge <?= $totpOn ? 'online' : 'offline' ?>"><?= $totpOn ? 'Açık' : 'Kapalı' ?></span>
          </div>

          <?php if ($totpOn): ?>
            <form method="post" action="<?= e(url('/panel/guvenlik/2fa/kapat')) ?>" autocomplete="off" style="margin-bottom:18px;">
              <?= $csrf ?>
              <div class="form-row"><label for="dis-2fa-pass">Kapatmak için hesap parolası</label><input id="dis-2fa-pass" name="password" type="password" maxlength="16" required></div>
              <button type="submit" class="btn btn-ghost btn-block">2FA’yı Kapat</button>
            </form>
          <?php elseif ($totpSetup): ?>
            <div style="font-size:.8rem;color:var(--ash);margin:8px 0;">Authenticator uygulamasına bu anahtarı ekle, ardından 6 haneli kodu onayla:</div>
            <div class="secret-box"><?= e($totpSetup['secret']) ?></div>
            <form method="post" action="<?= e(url('/panel/guvenlik/2fa/onayla')) ?>" autocomplete="off" style="margin-bottom:18px;">
              <?= $csrf ?>
              <div class="form-row"><label for="totp-code">Doğrulama Kodu</label><input id="totp-code" name="code" type="text" inputmode="numeric" pattern="\d{6}" maxlength="6" required placeholder="000000"></div>
              <button type="submit" class="btn btn-primary btn-block">2FA’yı Onayla</button>
            </form>
          <?php else: ?>
            <form method="post" action="<?= e(url('/panel/guvenlik/2fa/baslat')) ?>" style="margin-bottom:18px;">
              <?= $csrf ?>
              <button type="submit" class="btn btn-primary btn-block">2FA Kurulumunu Başlat</button>
            </form>
          <?php endif; ?>

          <div class="security-row">
            <div>
              <div class="t">Giriş Bildirimleri</div>
              <div class="d">Yeni girişte e-posta gönder (hazırlık)</div>
            </div>
            <form method="post" action="<?= e(url('/panel/guvenlik/bildirim')) ?>">
              <?= $csrf ?>
              <input type="hidden" name="enabled" value="<?= $notifyOn ? '0' : '1' ?>">
              <button type="submit" class="toggle<?= $notifyOn ? ' on' : '' ?> toggle-btn" aria-label="Giriş bildirimleri"></button>
            </form>
          </div>

          <div class="security-row">
            <div>
              <div class="t">IP Kilidi</div>
              <div class="d"><?= $ipLockOn ? 'Kilitli IP: ' . e((string) ($security['locked_ip'] ?? '')) : 'Kapalı — açınca mevcut IP kaydedilir' ?></div>
            </div>
            <form method="post" action="<?= e(url('/panel/guvenlik/ip')) ?>">
              <?= $csrf ?>
              <input type="hidden" name="enabled" value="<?= $ipLockOn ? '0' : '1' ?>">
              <button type="submit" class="toggle<?= $ipLockOn ? ' on' : '' ?> toggle-btn" aria-label="IP kilidi"></button>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>
</div>

<script>
  const navItems = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.section');
  const initialSection = <?= json_encode($panelSection, JSON_UNESCAPED_UNICODE) ?>;

  function showSection(target) {
    if (!target) return;
    navItems.forEach(n => n.classList.toggle('active', n.dataset.target === target));
    sections.forEach(s => s.classList.toggle('active', s.id === target));
    document.getElementById('sidebar').classList.remove('open');
  }

  if (initialSection) showSection(initialSection);

  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      const target = item.dataset.target;
      if (!target) return;
      e.preventDefault();
      showSection(target);
    });
  });

  document.querySelectorAll('[data-jump]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      showSection(el.dataset.jump);
    });
  });

  document.getElementById('mobileToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
  });

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
      const m = Math.floor(left / 60);
      const s = left % 60;
      valueEl.textContent = pad(m) + ':' + pad(s);
      el.classList.toggle('warn', left <= 60);
      if (left <= 0) {
        window.location.href = logoutUrl;
        return;
      }
      setTimeout(tick, 1000);
    };
    tick();
  })();
</script>
</body>
</html>
