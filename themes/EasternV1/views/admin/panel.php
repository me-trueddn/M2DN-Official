<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var array $currentServer */
/** @var array $servers */
/** @var string $csrf */
/** @var array|null $authUser */

$appName = $appName ?? 'M2DN';
$appTagline = $appTagline ?? '';
$currentServer = is_array($currentServer ?? null) ? $currentServer : [];
$servers = is_array($servers ?? null) ? $servers : [];
$csrf = $csrf ?? '';
$authUser = is_array($authUser ?? null) ? $authUser : null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Yönetim Paneli | <?= e($appName) ?></title>
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

  .layout{display:grid; grid-template-columns:var(--sidebar-w) 1fr; min-height:100vh;}

  /* ===== SIDEBAR ===== */
  .sidebar{background:var(--obsidian-2); border-right:1px solid var(--line); padding:26px 18px; display:flex; flex-direction:column; gap:6px; position:sticky; top:0; height:100vh; overflow-y:auto;}
  .sidebar-brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; font-size:1.15rem; letter-spacing:.06em; color:var(--gold-light); padding:0 10px 10px; margin-bottom:6px; border-bottom:1px solid var(--line); text-decoration:none;}
  .sidebar-brand img{width:36px; height:36px; flex-shrink:0; display:block;}
  .sidebar-brand span{color:var(--blood-light);}
  .sidebar-brand small{display:block; font-family:var(--font-body); font-weight:600; font-size:.6rem; letter-spacing:.16em; color:var(--blood-light); text-transform:uppercase; margin-top:2px;}
  .sidebar-brand:hover{opacity:.92;}

  .nav-group-label{font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; color:var(--ash); padding:16px 12px 8px;}
  .nav-item{display:flex; align-items:center; gap:12px; padding:11px 12px; color:var(--ash); font-size:.9rem; font-weight:500; border-left:2px solid transparent; transition:background .2s, color .2s, border-color .2s;}
  .nav-item i{width:18px; text-align:center; font-size:.95rem;}
  .nav-item:hover{background:rgba(201,151,74,.06); color:var(--parchment);}
  .nav-item.active{background:linear-gradient(90deg, rgba(143,28,41,.16), transparent); color:var(--gold-light); border-left-color:var(--gold);}
  .nav-item .count{margin-left:auto; background:var(--blood); color:var(--parchment); font-size:.65rem; padding:2px 7px; border-radius:20px; font-weight:700;}

  .sidebar-foot{margin-top:auto; padding-top:18px; border-top:1px solid var(--line);}
  .sidebar-char{display:flex; align-items:center; gap:10px; padding:10px 12px;}
  .avatar-ring{width:38px; height:38px; border-radius:50%; background:conic-gradient(var(--gold), var(--blood), var(--gold)); display:flex; align-items:center; justify-content:center; flex-shrink:0;}
  .avatar-ring i{width:32px; height:32px; border-radius:50%; background:var(--obsidian-2); display:flex; align-items:center; justify-content:center; color:var(--gold-light); font-size:.85rem;}
  .sidebar-char .who{font-size:.82rem; color:var(--parchment); font-weight:600;}
  .sidebar-char .role{font-size:.7rem; color:var(--blood-light);}
  .logout-link{display:flex; align-items:center; gap:10px; padding:10px 12px; color:var(--blood-light); font-size:.85rem; margin-top:6px;}

  /* ===== MAIN ===== */
  .main{padding:26px 34px 60px; max-width:1360px;}
  .topbar{display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:30px; flex-wrap:wrap;}
  .topbar h1{font-size:1.5rem; color:var(--parchment);}
  .topbar .sub{color:var(--ash); font-size:.85rem; margin-top:4px; font-family:var(--font-body);}
  .top-actions{display:flex; align-items:center; gap:16px;}
  .search-box{display:flex; align-items:center; gap:8px; background:var(--obsidian-2); border:1px solid var(--line); padding:9px 14px; font-size:.82rem; color:var(--ash); min-width:240px;}
  .search-box input{background:none; border:none; outline:none; color:var(--parchment); font-size:.82rem; width:100%;}
  .icon-btn{position:relative; width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:var(--obsidian-2); border:1px solid var(--line); color:var(--gold-light); font-size:.9rem;}
  .icon-btn .dot{position:absolute; top:6px; right:7px; width:6px; height:6px; border-radius:50%; background:var(--blood-light);}
  .status-pill{display:flex; align-items:center; gap:8px; padding:8px 14px; background:rgba(51,89,74,.15); border:1px solid rgba(79,138,113,.3); font-size:.78rem; color:var(--jade-light); text-transform:uppercase; letter-spacing:.05em;}
  .status-pill .pulse{width:7px; height:7px; border-radius:50%; background:var(--jade-light); animation:pulse 2s infinite;}
  .session-timer{display:flex; align-items:center; gap:8px; padding:8px 14px; background:rgba(201,151,74,.1); border:1px solid rgba(201,151,74,.28); font-size:.78rem; color:var(--gold-light); letter-spacing:.04em; font-variant-numeric:tabular-nums;}
  .session-timer.warn{background:rgba(143,28,41,.18); border-color:rgba(197,51,71,.4); color:#e8a0a8;}
  .session-timer i{font-size:.75rem; opacity:.85;}
  .session-timer .t{font-weight:700; min-width:3.2em;}
  @keyframes pulse{ 0%{box-shadow:0 0 0 0 rgba(79,138,113,.5);} 70%{box-shadow:0 0 0 8px rgba(79,138,113,0);} 100%{box-shadow:0 0 0 0 rgba(79,138,113,0);} }

  .grid{display:grid; gap:20px;}
  .grid-4{grid-template-columns:repeat(4,1fr);}
  .grid-3{grid-template-columns:2fr 1fr;}
  .grid-2{grid-template-columns:1fr 1fr;}

  .card{background:var(--obsidian-2); border:1px solid var(--line); padding:24px; clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);}
  .card-head{display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:14px; flex-wrap:wrap;}
  .card-head h3{font-size:1rem; color:var(--parchment); font-weight:600; letter-spacing:.02em;}
  .card-head a{font-size:.78rem; color:var(--gold-light);}

  .stat-card{display:flex; flex-direction:column; gap:10px;}
  .stat-card .icon{width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:1rem;}
  .stat-card strong{font-family:var(--font-display); font-size:1.7rem; color:var(--parchment);}
  .stat-card span.lbl{font-size:.78rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em;}
  .stat-card .delta{font-size:.75rem; color:var(--jade-light);}
  .stat-card .delta.down{color:var(--blood-light);}

  .btn{padding:11px 20px; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; display:inline-flex; align-items:center; gap:8px; border:none; transition:transform .2s, background .2s;
    clip-path:polygon(8px 0,100% 0,100% calc(100% - 8px),calc(100% - 8px) 100%,0 100%,0 8px);}
  .btn-primary{background:linear-gradient(135deg, var(--blood-light), var(--blood)); color:var(--parchment);}
  .btn-primary:hover{transform:translateY(-2px);}
  .btn-ghost{background:none; border:1px solid var(--line); color:var(--gold-light);}
  .btn-ghost:hover{background:rgba(201,151,74,.08);}
  .btn-jade{background:rgba(51,89,74,.25); border:1px solid rgba(79,138,113,.4); color:var(--jade-light);}
  .btn-sm{padding:7px 12px; font-size:.68rem;}

  table{width:100%; border-collapse:collapse; font-size:.85rem;}
  thead th{text-align:left; padding:10px 14px; color:var(--ash); font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--line); font-weight:600;}
  tbody td{padding:13px 14px; border-bottom:1px solid rgba(201,151,74,.08); color:var(--parchment); vertical-align:middle;}
  tbody tr:hover{background:rgba(201,151,74,.04);}
  .row-user{display:flex; align-items:center; gap:10px;}
  .row-user .av{width:30px; height:30px; border-radius:50%; background:rgba(201,151,74,.12); display:flex; align-items:center; justify-content:center; color:var(--gold-light); font-size:.78rem; flex-shrink:0;}
  .row-user .meta{font-size:.72rem; color:var(--ash);}
  .actions-cell{display:flex; gap:6px;}
  .actions-cell button{width:30px; height:30px; display:flex; align-items:center; justify-content:center; background:var(--obsidian); border:1px solid var(--line); color:var(--ash); font-size:.75rem; transition:color .2s, border-color .2s;}
  .actions-cell button:hover{color:var(--gold-light); border-color:var(--gold);}
  .actions-cell button.danger:hover{color:var(--blood-light); border-color:var(--blood-light);}

  .badge{display:inline-flex; align-items:center; gap:6px; padding:4px 10px; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600;}
  .badge.online{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .badge.offline{background:rgba(154,143,128,.15); color:var(--ash);}
  .badge.pending{background:rgba(201,151,74,.15); color:var(--gold-light);}
  .badge.banned{background:rgba(143,28,41,.2); color:var(--blood-light);}
  .badge.closed{background:rgba(154,143,128,.15); color:var(--ash);}

  .filters{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px;}
  .filters input, .filters select{background:var(--obsidian); border:1px solid var(--line); padding:9px 12px; color:var(--parchment); font-size:.8rem; outline:none;}
  .filters input:focus, .filters select:focus{border-color:var(--gold);}

  /* chart */
  .chart-wrap{position:relative;}
  .chart-legend{display:flex; gap:18px; margin-top:14px; font-size:.75rem; color:var(--ash);}
  .chart-legend span{display:flex; align-items:center; gap:6px;}
  .chart-legend .dot{width:8px; height:8px; border-radius:50%;}

  /* activity feed */
  .feed-item{display:flex; gap:12px; padding:14px 0; border-bottom:1px solid rgba(201,151,74,.08);}
  .feed-item:last-child{border-bottom:none;}
  .feed-item .fi-icon{width:32px; height:32px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:.78rem;}
  .feed-item .fi-text{font-size:.85rem; color:var(--parchment);}
  .feed-item .fi-time{font-size:.7rem; color:var(--ash); margin-top:3px;}

  /* server control */
  .channel-card{background:var(--obsidian); border:1px solid var(--line); padding:20px; display:flex; align-items:center; justify-content:space-between;}
  .channel-card .ch-name{font-family:var(--font-display); font-size:1.05rem; color:var(--parchment);}
  .channel-card .ch-meta{font-size:.72rem; color:var(--ash); margin-top:4px;}
  .toggle{position:relative; width:44px; height:24px; background:rgba(233,223,198,.1); border-radius:20px; flex-shrink:0; cursor:pointer; transition:background .25s;}
  .toggle::after{content:""; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:var(--ash); transition:transform .25s, background .25s;}
  .toggle.on{background:rgba(79,138,113,.3);}
  .toggle.on::after{transform:translateX(20px); background:var(--jade-light);}

  .form-row{margin-bottom:18px;}
  .form-row label{display:block; font-size:.78rem; color:var(--ash); text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;}
  .form-row input, .form-row textarea{width:100%; background:var(--obsidian); border:1px solid var(--line); padding:12px 14px; color:var(--parchment); font-size:.88rem; outline:none; transition:border-color .2s; font-family:inherit;}
  .form-row textarea{resize:vertical; min-height:100px;}
  .form-row input:focus, .form-row textarea:focus{border-color:var(--gold);}

  .section{display:none;}
  .section.active{display:block;}

  /* modal */
  .modal-overlay{position:fixed; inset:0; background:rgba(0,0,0,.6); backdrop-filter:blur(3px); display:none; align-items:center; justify-content:center; z-index:900;}
  .modal-overlay.open{display:flex;}
  .modal{width:400px; max-width:90vw; background:var(--obsidian-2); border:1px solid var(--gold); padding:28px; clip-path:polygon(12px 0,100% 0,100% calc(100% - 12px),calc(100% - 12px) 100%,0 100%,0 12px);}
  .modal h3{font-size:1.1rem; color:var(--gold-light); margin-bottom:12px;}
  .modal p{font-size:.85rem; color:var(--ash); margin-bottom:20px; line-height:1.6;}
  .modal .modal-actions{display:flex; gap:12px; justify-content:flex-end;}

  @media (max-width:1100px){
    .grid-4{grid-template-columns:repeat(2,1fr);}
    .grid-3{grid-template-columns:1fr;}
  }
  @media (max-width:820px){
    .layout{grid-template-columns:1fr;}
    .sidebar{position:fixed; left:-280px; top:0; width:260px; z-index:200; transition:left .3s; box-shadow:20px 0 40px rgba(0,0,0,.5);}
    .sidebar.open{left:0;}
    .main{padding:20px 18px 60px;}
    .mobile-toggle{display:flex !important;}
    table{font-size:.78rem;}
  }
  .mobile-toggle{display:none; width:38px; height:38px; align-items:center; justify-content:center; background:var(--obsidian-2); border:1px solid var(--line); color:var(--gold-light);}
  @media (max-width:600px){ .grid-4{grid-template-columns:1fr 1fr;} }
</style>
</head>
<body>
<div class="layout">

  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" id="sidebar">
    <a href="<?= e(url('/')) ?>" class="sidebar-brand" aria-label="<?= e($appName) ?> Anasayfa">
      <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="<?= e($appName) ?>">
      <div>M2<span>DN</span><small>Yönetim Paneli</small></div>
    </a>

    <?php if (!empty($servers) && count($servers) > 1): ?>
    <form method="post" action="<?= e(url('/server/select')) ?>" style="padding:0 12px 14px;">
      <?= $csrf ?>
      <input type="hidden" name="redirect" value="/admin">
      <label style="display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--ash);margin-bottom:8px;">Sunucu</label>
      <select name="server" onchange="this.form.submit()" style="width:100%;background:var(--obsidian);border:1px solid var(--line);color:var(--parchment);padding:9px 10px;font-size:.82rem;">
        <?php foreach ($servers as $key => $srv): ?>
          <option value="<?= e($key) ?>" <?= ($currentServer['key'] ?? '') === $key ? 'selected' : '' ?>><?= e($srv['name'] ?? $key) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <?php endif; ?>

    <div class="nav-group-label">Genel</div>
    <a class="nav-item active" data-target="ozet"><i class="fa-solid fa-gauge-high"></i> Genel Bakış</a>

    <div class="nav-group-label">Oyuncular</div>
    <a class="nav-item" data-target="oyuncular"><i class="fa-solid fa-users"></i> Oyuncu Yönetimi</a>
    <a class="nav-item" data-target="banlar"><i class="fa-solid fa-gavel"></i> Ban / Mute <span class="count">14</span></a>

    <div class="nav-group-label">İçerik</div>
    <a class="nav-item" data-target="duyurular"><i class="fa-solid fa-bullhorn"></i> Duyurular</a>
    <a class="nav-item" data-target="destekler"><i class="fa-solid fa-headset"></i> Destek Talepleri <span class="count">6</span></a>

    <div class="nav-group-label">Sistem</div>
    <a class="nav-item" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-user"></i> Oyuncu Paneli</a>
    <a class="nav-item" data-target="sunucu"><i class="fa-solid fa-server"></i> Sunucu Kontrol</a>
    <a class="nav-item" data-target="loglar"><i class="fa-solid fa-scroll"></i> Loglar</a>

    <div class="sidebar-foot">
      <div class="sidebar-char">
        <div class="avatar-ring"><i class="fa-solid fa-crown"></i></div>
        <div>
          <div class="who"><?= e((string) ($authUser['login'] ?? 'Admin')) ?></div>
          <div class="role"><?= ((int)($authUser['permission'] ?? 0) === 2) ? 'Süper Admin' : 'Yönetici' ?></div>
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
          <h1>Yönetim Merkezi</h1>
          <div class="sub"><?= e($currentServer['name'] ?? 'M2DN') ?> · Sunucu, oyuncu ve içerik yönetimi tek ekranda.</div>
        </div>
      </div>
      <div class="top-actions">
        <div class="status-pill"><div class="pulse"></div> Sunucu açık</div>
        <div class="session-timer" id="sessionTimer" title="Oturum süresi" data-expires="<?= (int) ($authUser['session_expires_at'] ?? 0) ?>" data-logout="<?= e(url('/cikis')) ?>">
          <i class="fa-solid fa-hourglass-half"></i>
          <span>Oturum</span>
          <span class="t" id="sessionTimerValue">--:--</span>
        </div>
        <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input placeholder="Oyuncu, IP, karakter ara..."></div>
        <button class="icon-btn"><i class="fa-solid fa-bell"></i><span class="dot"></span></button>
      </div>
    </div>

    <!-- ===================== GENEL BAKIŞ ===================== -->
    <section class="section active" id="ozet">
      <div class="grid grid-4" style="margin-bottom:22px;">
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-users"></i></div>
          <strong>1.247</strong><span class="lbl">Çevrimiçi Oyuncu</span>
          <span class="delta">+8% dün ile kıyasla</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
          <strong>₺12.480</strong><span class="lbl">Günlük Gelir</span>
          <span class="delta">+₺1.120 bugün</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-user-plus"></i></div>
          <strong>86</strong><span class="lbl">Yeni Kayıt (24s)</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-ticket"></i></div>
          <strong>6</strong><span class="lbl">Açık Destek Talebi</span>
          <span class="delta down">2 tanesi 24s+ bekliyor</span>
        </div>
      </div>

      <div class="grid grid-3">
        <div class="card chart-wrap">
          <div class="card-head"><h3>Çevrimiçi Oyuncu Trendi (7 Gün)</h3><a href="#">Raporu indir</a></div>
          <svg viewBox="0 0 600 220" style="width:100%; height:220px;">
            <defs>
              <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#c9974a" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="#c9974a" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <line x1="0" y1="40" x2="600" y2="40" stroke="rgba(201,151,74,.1)"/>
            <line x1="0" y1="100" x2="600" y2="100" stroke="rgba(201,151,74,.1)"/>
            <line x1="0" y1="160" x2="600" y2="160" stroke="rgba(201,151,74,.1)"/>
            <path d="M0,150 L85,120 L170,135 L255,90 L340,105 L425,60 L510,75 L600,40 L600,220 L0,220 Z" fill="url(#areaGrad)"/>
            <path d="M0,150 L85,120 L170,135 L255,90 L340,105 L425,60 L510,75 L600,40" fill="none" stroke="#c9974a" stroke-width="2.5"/>
            <path d="M0,180 L85,170 L170,175 L255,150 L340,160 L425,130 L510,140 L600,120" fill="none" stroke="#8f1c29" stroke-width="2" stroke-dasharray="4 4" opacity=".8"/>
          </svg>
          <div class="chart-legend">
            <span><div class="dot" style="background:#c9974a"></div> Aktif Oyuncu</span>
            <span><div class="dot" style="background:#8f1c29"></div> Yeni Kayıt</span>
          </div>
        </div>

        <div class="card">
          <div class="card-head"><h3>Son Aktiviteler</h3></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-gavel"></i></div><div><div class="fi-text"><b>GolgeAvci</b> 3 gün banlandı</div><div class="fi-time">4 dk önce · Admin_Orhan</div></div></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-bullhorn"></i></div><div><div class="fi-text">Yeni duyuru yayınlandı</div><div class="fi-time">22 dk önce · GM_Aylin</div></div></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-server"></i></div><div><div class="fi-text">Kanal 3 yeniden başlatıldı</div><div class="fi-time">1 saat önce · Sistem</div></div></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-headset"></i></div><div><div class="fi-text">Destek talebi #1042 kapatıldı</div><div class="fi-time">2 saat önce · GM_Baran</div></div></div>
        </div>
      </div>
    </section>

    <!-- ===================== OYUNCU YÖNETİMİ ===================== -->
    <section class="section" id="oyuncular">
      <div class="card">
        <div class="card-head"><h3>Oyuncu Yönetimi</h3><span style="font-size:.8rem; color:var(--ash);">1.842 kayıtlı hesap</span></div>
        <div class="filters">
          <input placeholder="Kullanıcı adı ara..." style="flex:1; min-width:180px;">
          <select><option>Tüm Durumlar</option><option>Çevrimiçi</option><option>Çevrimdışı</option><option>Banlı</option></select>
          <select><option>Tüm Sınıflar</option><option>Savaşçı</option><option>Ninja</option><option>Sura</option><option>Şaman</option></select>
        </div>
        <table>
          <thead><tr><th>Hesap</th><th>Karakter</th><th>Seviye</th><th>IP</th><th>Durum</th><th>İşlemler</th></tr></thead>
          <tbody>
            <tr>
              <td class="row-user"><div class="av"><i class="fa-solid fa-user"></i></div><div><div>karakilic92</div><div class="meta">Kayıt: Oca 2022</div></div></td>
              <td>KaraKilic</td><td>96</td><td>88.240.xx.xx</td>
              <td><span class="badge online">Çevrimiçi</span></td>
              <td class="actions-cell">
                <button title="Detay"><i class="fa-solid fa-eye"></i></button>
                <button title="Eşya ver"><i class="fa-solid fa-box-open"></i></button>
                <button title="Sustur"><i class="fa-solid fa-comment-slash"></i></button>
                <button title="Banla" class="danger" data-ban="karakilic92"><i class="fa-solid fa-gavel"></i></button>
              </td>
            </tr>
            <tr>
              <td class="row-user"><div class="av"><i class="fa-solid fa-user"></i></div><div><div>golgeavci</div><div class="meta">Kayıt: Mar 2023</div></div></td>
              <td>GolgeAvci</td><td>77</td><td>45.112.xx.xx</td>
              <td><span class="badge banned">Banlı</span></td>
              <td class="actions-cell">
                <button title="Detay"><i class="fa-solid fa-eye"></i></button>
                <button title="Eşya ver"><i class="fa-solid fa-box-open"></i></button>
                <button title="Sustur"><i class="fa-solid fa-comment-slash"></i></button>
                <button title="Banı kaldır"><i class="fa-solid fa-lock-open"></i></button>
              </td>
            </tr>
            <tr>
              <td class="row-user"><div class="av"><i class="fa-solid fa-user"></i></div><div><div>yesilnefes</div><div class="meta">Kayıt: Tem 2021</div></div></td>
              <td>YesilNefes</td><td>64</td><td>31.200.xx.xx</td>
              <td><span class="badge offline">Çevrimdışı</span></td>
              <td class="actions-cell">
                <button title="Detay"><i class="fa-solid fa-eye"></i></button>
                <button title="Eşya ver"><i class="fa-solid fa-box-open"></i></button>
                <button title="Sustur"><i class="fa-solid fa-comment-slash"></i></button>
                <button title="Banla" class="danger" data-ban="yesilnefes"><i class="fa-solid fa-gavel"></i></button>
              </td>
            </tr>
            <tr>
              <td class="row-user"><div class="av"><i class="fa-solid fa-user"></i></div><div><div>surahan</div><div class="meta">Kayıt: May 2024</div></div></td>
              <td>SuraHan</td><td>103</td><td>78.190.xx.xx</td>
              <td><span class="badge online">Çevrimiçi</span></td>
              <td class="actions-cell">
                <button title="Detay"><i class="fa-solid fa-eye"></i></button>
                <button title="Eşya ver"><i class="fa-solid fa-box-open"></i></button>
                <button title="Sustur"><i class="fa-solid fa-comment-slash"></i></button>
                <button title="Banla" class="danger" data-ban="surahan"><i class="fa-solid fa-gavel"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== BANLAR ===================== -->
    <section class="section" id="banlar">
      <div class="card">
        <div class="card-head"><h3>Ban / Mute Listesi</h3><span style="font-size:.8rem; color:var(--ash);">14 aktif kısıtlama</span></div>
        <table>
          <thead><tr><th>Hesap</th><th>Tür</th><th>Sebep</th><th>Süre</th><th>Yetkili</th><th>İşlem</th></tr></thead>
          <tbody>
            <tr><td>golgeavci</td><td><span class="badge banned">Ban</span></td><td>Bot kullanımı</td><td>3 gün kaldı</td><td>Admin_Orhan</td><td class="actions-cell"><button title="Kaldır"><i class="fa-solid fa-lock-open"></i></button></td></tr>
            <tr><td>demirkol44</td><td><span class="badge pending">Mute</span></td><td>Küfür / taciz</td><td>12 saat kaldı</td><td>GM_Aylin</td><td class="actions-cell"><button title="Kaldır"><i class="fa-solid fa-lock-open"></i></button></td></tr>
            <tr><td>ateskurdu</td><td><span class="badge banned">Ban</span></td><td>Hile / duvar içi</td><td>Kalıcı</td><td>Admin_Orhan</td><td class="actions-cell"><button title="Kaldır"><i class="fa-solid fa-lock-open"></i></button></td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== DUYURULAR ===================== -->
    <section class="section" id="duyurular">
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Yayınlanan Duyurular</h3></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-dragon"></i></div><div><div class="fi-text">Kızıl Tapınak güncellemesi yayında</div><div class="fi-time">2 gün önce · Yayında</div></div></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-bolt"></i></div><div><div class="fi-text">Hafta sonu x2 EXP etkinliği</div><div class="fi-time">4 gün önce · Yayında</div></div></div>
          <div class="feed-item"><div class="fi-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div><div><div class="fi-text">Planlı bakım tamamlandı</div><div class="fi-time">6 gün önce · Arşivlendi</div></div></div>
        </div>
        <div class="card">
          <div class="card-head"><h3>Yeni Duyuru</h3></div>
          <div class="form-row"><label>Başlık</label><input placeholder="Duyuru başlığı"></div>
          <div class="form-row"><label>İçerik</label><textarea placeholder="Duyuru metnini yaz..."></textarea></div>
          <a class="btn btn-primary" style="width:100%; justify-content:center;">Yayınla</a>
        </div>
      </div>
    </section>

    <!-- ===================== DESTEK TALEPLERİ ===================== -->
    <section class="section" id="destekler">
      <div class="card">
        <div class="card-head"><h3>Destek Talepleri</h3></div>
        <table>
          <thead><tr><th>Oyuncu</th><th>Konu</th><th>Kategori</th><th>Tarih</th><th>Durum</th><th>İşlem</th></tr></thead>
          <tbody>
            <tr><td>karakilic92</td><td>Eşya kayboldu (Ejderha Miğferi)</td><td>Eşya Sorunu</td><td>12 Tem</td><td><span class="badge pending">Bekliyor</span></td><td class="actions-cell"><button title="Yanıtla"><i class="fa-solid fa-reply"></i></button></td></tr>
            <tr><td>demirkol44</td><td>Karakter bağlantı hatası</td><td>Teknik</td><td>11 Tem</td><td><span class="badge pending">Bekliyor</span></td><td class="actions-cell"><button title="Yanıtla"><i class="fa-solid fa-reply"></i></button></td></tr>
            <tr><td>yesilnefes</td><td>Cash Puan yüklenmedi</td><td>Ödeme</td><td>08 Tem</td><td><span class="badge closed">Çözüldü</span></td><td class="actions-cell"><button title="Görüntüle"><i class="fa-solid fa-eye"></i></button></td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== SUNUCU KONTROL ===================== -->
    <section class="section" id="sunucu">
      <div class="grid grid-2" style="margin-bottom:20px;">
        <div class="channel-card">
          <div><div class="ch-name">Kanal 1</div><div class="ch-meta">412 oyuncu · Yük %64</div></div>
          <div class="toggle on" data-toggle></div>
        </div>
        <div class="channel-card">
          <div><div class="ch-name">Kanal 2</div><div class="ch-meta">388 oyuncu · Yük %58</div></div>
          <div class="toggle on" data-toggle></div>
        </div>
        <div class="channel-card">
          <div><div class="ch-name">Kanal 3</div><div class="ch-meta">301 oyuncu · Yük %41</div></div>
          <div class="toggle on" data-toggle></div>
        </div>
        <div class="channel-card">
          <div><div class="ch-name">Kanal 4 (Test)</div><div class="ch-meta">Bakımda</div></div>
          <div class="toggle" data-toggle></div>
        </div>
      </div>
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Sunucu Komutları</h3></div>
          <p style="font-size:.82rem; color:var(--ash); margin-bottom:18px; line-height:1.6;">Toplu işlemler tüm çevrimiçi oyuncuları etkiler. Dikkatli kullan.</p>
          <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a class="btn btn-jade btn-sm"><i class="fa-solid fa-bullhorn"></i> Genel Duyuru Gönder</a>
            <a class="btn btn-ghost btn-sm"><i class="fa-solid fa-rotate"></i> Kanal Yeniden Başlat</a>
            <a class="btn btn-primary btn-sm"><i class="fa-solid fa-triangle-exclamation"></i> Bakım Modu Aç</a>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><h3>Bakım Modu</h3></div>
          <div class="feed-item" style="border:none; padding-top:0;">
            <div class="fi-icon"><i class="fa-solid fa-power-off"></i></div>
            <div><div class="fi-text">Bakım modu kapalı</div><div class="fi-time">Sunucu tüm oyunculara açık</div></div>
          </div>
          <div class="toggle" data-toggle style="margin-top:14px;"></div>
        </div>
      </div>
    </section>

    <!-- ===================== LOGLAR ===================== -->
    <section class="section" id="loglar">
      <div class="card">
        <div class="card-head"><h3>Yönetici Logları</h3></div>
        <table>
          <thead><tr><th>Zaman</th><th>Yetkili</th><th>İşlem</th><th>Hedef</th></tr></thead>
          <tbody>
            <tr><td>14 Tem, 14:22</td><td>Admin_Orhan</td><td>Ban verdi (3 gün)</td><td>golgeavci</td></tr>
            <tr><td>14 Tem, 13:58</td><td>GM_Aylin</td><td>Duyuru yayınladı</td><td>—</td></tr>
            <tr><td>14 Tem, 12:40</td><td>Sistem</td><td>Kanal yeniden başlatıldı</td><td>Kanal 3</td></tr>
            <tr><td>14 Tem, 11:05</td><td>GM_Baran</td><td>Eşya verdi</td><td>karakilic92</td></tr>
            <tr><td>13 Tem, 22:14</td><td>Admin_Orhan</td><td>Susturma kaldırdı</td><td>demirkol44</td></tr>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<!-- ============ BAN MODAL ============ -->
<div class="modal-overlay" id="banModal">
  <div class="modal">
    <h3><i class="fa-solid fa-gavel"></i> Oyuncuyu Banla</h3>
    <p><b id="banTarget">—</b> hesabını banlamak üzeresin. Bu işlem oyuncunun tüm sunucuya erişimini kısıtlar.</p>
    <div class="form-row"><label>Süre</label><input placeholder="Örn: 3 gün, kalıcı"></div>
    <div class="form-row"><label>Sebep</label><input placeholder="Ban sebebini yaz"></div>
    <div class="modal-actions">
      <a class="btn btn-ghost btn-sm" id="banCancel">Vazgeç</a>
      <a class="btn btn-primary btn-sm" id="banConfirm">Banla</a>
    </div>
  </div>
</div>

<script>
  const navItems = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.section');
  navItems.forEach(item => {
    item.addEventListener('click', () => {
      navItems.forEach(n => n.classList.remove('active'));
      item.classList.add('active');
      const target = item.dataset.target;
      sections.forEach(s => s.classList.toggle('active', s.id === target));
      document.getElementById('sidebar').classList.remove('open');
    });
  });

  document.querySelectorAll('[data-toggle]').forEach(t => {
    t.addEventListener('click', () => t.classList.toggle('on'));
  });

  document.getElementById('mobileToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
  });

  // Ban modal
  const banModal = document.getElementById('banModal');
  const banTarget = document.getElementById('banTarget');
  document.querySelectorAll('[data-ban]').forEach(btn => {
    btn.addEventListener('click', () => {
      banTarget.textContent = btn.dataset.ban;
      banModal.classList.add('open');
    });
  });
  document.getElementById('banCancel').addEventListener('click', () => banModal.classList.remove('open'));
  document.getElementById('banConfirm').addEventListener('click', () => banModal.classList.remove('open'));
  banModal.addEventListener('click', (e) => { if (e.target === banModal) banModal.classList.remove('open'); });

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
