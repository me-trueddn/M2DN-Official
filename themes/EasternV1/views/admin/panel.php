<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var array $currentServer */
/** @var array $servers */
/** @var string $csrf */
/** @var array|null $authUser */
/** @var array $stats */
/** @var array $players */
/** @var string $panelSection */
/** @var list<array> $penalties */
/** @var list<array> $activeBans */
/** @var list<string> $panelErrors */
/** @var string|null $panelSuccess */

$appName = $appName ?? 'M2DN';
$appTagline = $appTagline ?? '';
$currentServer = is_array($currentServer ?? null) ? $currentServer : [];
$servers = is_array($servers ?? null) ? $servers : [];
$csrf = $csrf ?? '';
$authUser = is_array($authUser ?? null) ? $authUser : null;
$stats = is_array($stats ?? null) ? $stats : [];
$players = is_array($players ?? null) ? $players : [];
$panelSection = is_string($panelSection ?? null) && $panelSection !== '' ? $panelSection : 'ozet';
$onlineCount = (int) ($stats['online'] ?? 0);
$onlineWindow = (int) ($stats['online_window_minutes'] ?? 15);
$regsToday = (int) ($stats['registrations_today'] ?? 0);
$regsYesterday = (int) ($stats['registrations_yesterday'] ?? 0);
$chartLabels = is_array($stats['chart']['labels'] ?? null) ? $stats['chart']['labels'] : [];
$chartValues = is_array($stats['chart']['values'] ?? null) ? $stats['chart']['values'] : [];
$recentRegs = is_array($stats['recent_registrations'] ?? null) ? $stats['recent_registrations'] : [];
$regsDelta = $regsToday - $regsYesterday;
$playerAccounts = is_array($players['accounts'] ?? null) ? $players['accounts'] : [];
$playerTotal = (int) ($players['total'] ?? 0);
$playerPage = (int) ($players['page'] ?? 1);
$playerPages = (int) ($players['pages'] ?? 1);
$playerQ = (string) ($players['q'] ?? '');
$playerStatus = (string) ($players['status'] ?? '');
$playerPerPage = (int) ($players['per_page'] ?? 10);
$playerPerOptions = is_array($players['per_page_options'] ?? null) ? $players['per_page_options'] : [10, 20, 30, 50, 100];
$penalties = is_array($penalties ?? null) ? $penalties : [];
$activeBans = is_array($activeBans ?? null) ? $activeBans : [];
$panelErrors = is_array($panelErrors ?? null) ? $panelErrors : [];
$panelSuccess = is_string($panelSuccess ?? null) ? $panelSuccess : null;
$settingsOpen = in_array($panelSection, ['ceza-ayarlari'], true);
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

  .filters{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; align-items:center;}
  .filters input, .filters select{background:var(--obsidian); border:1px solid var(--line); padding:9px 12px; color:var(--parchment); font-size:.8rem; outline:none;}
  .filters input:focus, .filters select:focus{border-color:var(--gold);}
  .filters button.btn{cursor:pointer;}
  .badge.active{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .pager{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:18px; flex-wrap:wrap; font-size:.8rem; color:var(--ash);}
  .pager .links{display:flex; gap:8px; flex-wrap:wrap;}
  .pager a, .pager span.cur{padding:7px 12px; border:1px solid var(--line); color:var(--gold-light);}
  .pager span.cur{background:rgba(201,151,74,.12); border-color:var(--gold);}
  .pager a:hover{background:rgba(201,151,74,.08);}
  .pager a.disabled{opacity:.4; pointer-events:none;}

  /* chart */
  .chart-wrap{position:relative;}
  .chart-canvas-box{position:relative; width:100%; height:240px;}
  .chart-legend{display:flex; gap:18px; margin-top:14px; font-size:.75rem; color:var(--ash);}
  .chart-legend span{display:flex; align-items:center; gap:6px;}
  .chart-legend .dot{width:8px; height:8px; border-radius:50%;}
  .coming-soon{font-size:.78rem; color:var(--ash); font-style:italic;}
  .stat-card .coming-soon{margin-top:2px;}

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
  .modal.modal-lg{width:720px; max-height:88vh; overflow:auto;}
  .modal h3{font-size:1.1rem; color:var(--gold-light); margin-bottom:12px;}
  .modal p{font-size:.85rem; color:var(--ash); margin-bottom:20px; line-height:1.6;}
  .modal .modal-actions{display:flex; gap:12px; justify-content:flex-end;}
  .form-row select{width:100%; background:var(--obsidian); border:1px solid var(--line); padding:12px 14px; color:var(--parchment); font-size:.88rem; outline:none; font-family:inherit;}
  .form-row select:focus{border-color:var(--gold);}
  .nav-parent{display:flex; align-items:center; justify-content:space-between; gap:8px; cursor:pointer; user-select:none;}
  .nav-parent .chev{font-size:.65rem; opacity:.7; transition:transform .2s;}
  .nav-parent.open .chev{transform:rotate(90deg);}
  .nav-sub{display:none; padding:0 0 6px 10px;}
  .nav-sub.open{display:block;}
  .nav-sub .nav-item{padding:8px 12px; font-size:.8rem;}
  .flash{margin:0 0 18px; padding:12px 14px; font-size:.85rem; border:1px solid var(--line);}
  .flash.ok{background:rgba(51,89,74,.18); color:var(--jade-light); border-color:rgba(79,138,113,.35);}
  .flash.err{background:rgba(143,28,41,.18); color:#e8a0a8; border-color:rgba(197,51,71,.35);}
  .detail-meta{display:grid; gap:8px; margin-bottom:16px;}
  .detail-meta .row{display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid rgba(201,151,74,.08); font-size:.84rem;}
  .detail-meta .k{color:var(--ash);}
  .detail-meta .v{color:var(--gold-light); font-weight:600; text-align:right; word-break:break-all;}
  .detail-block{margin-top:16px;}
  .detail-block h4{font-size:.78rem; text-transform:uppercase; letter-spacing:.08em; color:var(--ash); margin-bottom:10px;}

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
    <a class="nav-item<?= $panelSection === 'ozet' ? ' active' : '' ?>" data-target="ozet"><i class="fa-solid fa-gauge-high"></i> Genel Bakış</a>

    <div class="nav-group-label">Oyuncular</div>
    <a class="nav-item<?= $panelSection === 'oyuncular' ? ' active' : '' ?>" data-target="oyuncular"><i class="fa-solid fa-users"></i> Oyuncu Yönetimi</a>
    <a class="nav-item" data-target="banlar"><i class="fa-solid fa-gavel"></i> Ban / Mute</a>

    <div class="nav-group-label">İçerik</div>
    <a class="nav-item" data-target="duyurular"><i class="fa-solid fa-bullhorn"></i> Duyurular</a>
    <a class="nav-item" data-target="destekler"><i class="fa-solid fa-headset"></i> Destek Talepleri</a>

    <div class="nav-group-label">Sistem</div>
    <a class="nav-item" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-user"></i> Oyuncu Paneli</a>
    <a class="nav-item" data-target="sunucu"><i class="fa-solid fa-server"></i> Sunucu Kontrol</a>
    <a class="nav-item" data-target="loglar"><i class="fa-solid fa-scroll"></i> Loglar</a>

    <div class="nav-group-label">Ayarlar</div>
    <div class="nav-item nav-parent<?= $settingsOpen ? ' open active' : '' ?>" id="settingsParent" data-parent="settings">
      <span><i class="fa-solid fa-sliders"></i> Site Ayarları</span>
      <i class="fa-solid fa-chevron-right chev"></i>
    </div>
    <div class="nav-sub<?= $settingsOpen ? ' open' : '' ?>" id="settingsSub">
      <a class="nav-item<?= $panelSection === 'ceza-ayarlari' ? ' active' : '' ?>" data-target="ceza-ayarlari"><i class="fa-solid fa-scale-balanced"></i> Ceza Ayarları</a>
    </div>

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

    <?php if ($panelSuccess): ?>
      <div class="flash ok"><?= e($panelSuccess) ?></div>
    <?php endif; ?>
    <?php if ($panelErrors !== []): ?>
      <div class="flash err">
        <?php foreach ($panelErrors as $err): ?>
          <div><?= e((string) $err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- ===================== GENEL BAKIŞ ===================== -->
    <section class="section<?= $panelSection === 'ozet' ? ' active' : '' ?>" id="ozet">
      <div class="grid grid-4" style="margin-bottom:22px;">
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-users"></i></div>
          <strong><?= number_format($onlineCount, 0, ',', '.') ?></strong>
          <span class="lbl">Çevrimiçi Oyuncu</span>
          <span class="delta">Son <?= (int) $onlineWindow ?> dk · last_play</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
          <strong>—</strong>
          <span class="lbl">Günlük Gelir</span>
          <span class="coming-soon">Market ile bağlanacak · yapım aşamasında</span>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-user-plus"></i></div>
          <strong><?= number_format($regsToday, 0, ',', '.') ?></strong>
          <span class="lbl">Yeni Kayıt (bugün)</span>
          <?php if ($regsDelta > 0): ?>
            <span class="delta">Düne göre +<?= (int) $regsDelta ?></span>
          <?php elseif ($regsDelta < 0): ?>
            <span class="delta down">Düne göre <?= (int) $regsDelta ?></span>
          <?php else: ?>
            <span class="delta">Dün: <?= number_format($regsYesterday, 0, ',', '.') ?></span>
          <?php endif; ?>
        </div>
        <div class="card stat-card">
          <div class="icon"><i class="fa-solid fa-ticket"></i></div>
          <strong>—</strong>
          <span class="lbl">Açık Destek Talebi</span>
          <span class="coming-soon">Destek sistemi · yapım aşamasında</span>
        </div>
      </div>

      <div class="grid grid-3">
        <div class="card chart-wrap">
          <div class="card-head">
            <h3>Çevrimiçi Oyuncu Trendi</h3>
            <span style="font-size:.75rem;color:var(--ash);">Son 24 saat · 5 dk örnekleme</span>
          </div>
          <div class="chart-canvas-box">
            <canvas id="onlineChart" aria-label="Çevrimiçi oyuncu grafiği"></canvas>
          </div>
          <div class="chart-legend">
            <span><div class="dot" style="background:#c9974a"></div> Çevrimiçi hesap (last_play)</span>
          </div>
        </div>

        <div class="card">
          <div class="card-head"><h3>Son Kayıtlar</h3></div>
          <?php if ($recentRegs === []): ?>
            <div class="coming-soon">Henüz kayıt yok.</div>
          <?php else: ?>
            <?php foreach ($recentRegs as $reg): ?>
              <?php
                $when = '—';
                $ts = strtotime((string) ($reg['create_time'] ?? ''));
                if ($ts) {
                    $when = date('d.m.Y H:i', $ts);
                }
              ?>
              <div class="feed-item">
                <div class="fi-icon"><i class="fa-solid fa-user-plus"></i></div>
                <div>
                  <div class="fi-text"><b><?= e((string) ($reg['login'] ?? '')) ?></b> kayıt oldu</div>
                  <div class="fi-time"><?= e($when) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ===================== OYUNCU YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'oyuncular' ? ' active' : '' ?>" id="oyuncular">
      <div class="card">
        <div class="card-head">
          <h3>Oyuncu Yönetimi</h3>
          <span style="font-size:.8rem; color:var(--ash);"><?= number_format($playerTotal, 0, ',', '.') ?> kayıtlı hesap</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="oyuncular">
          <input name="q" value="<?= e($playerQ) ?>" placeholder="Hesap, karakter veya e-posta ara..." style="flex:1; min-width:200px;">
          <select name="status">
            <option value=""<?= $playerStatus === '' ? ' selected' : '' ?>>Tüm Durumlar</option>
            <option value="OK"<?= $playerStatus === 'OK' ? ' selected' : '' ?>>Aktif</option>
            <option value="BLOCK"<?= $playerStatus === 'BLOCK' ? ' selected' : '' ?>>Banlı</option>
          </select>
          <select name="per" title="Sayfa başına">
            <?php foreach ($playerPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $playerPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrele</button>
        </form>
        <table>
          <thead><tr><th>Hesap</th><th>E-posta</th><th>Karakter</th><th>Seviye</th><th>IP</th><th>Durum</th><th>İşlemler</th></tr></thead>
          <tbody>
            <?php if ($playerAccounts === []): ?>
              <tr><td colspan="7" style="color:var(--ash);">Kayıt bulunamadı.</td></tr>
            <?php else: ?>
              <?php foreach ($playerAccounts as $acc): ?>
              <tr>
                <td class="row-user">
                  <div class="av"><i class="fa-solid fa-user"></i></div>
                  <div>
                    <div><?= e((string) $acc['login']) ?></div>
                    <div class="meta">Kayıt: <?= e((string) $acc['create_label']) ?><?= (int) ($acc['character_count'] ?? 0) > 1 ? ' · ' . (int) $acc['character_count'] . ' karakter' : '' ?></div>
                  </div>
                </td>
                <td style="font-size:.82rem;word-break:break-all;"><?= e((string) $acc['email']) ?></td>
                <td><?= e((string) $acc['character_name']) ?></td>
                <td><?= $acc['character_level'] !== null && (int) $acc['character_level'] > 0 ? (int) $acc['character_level'] : '—' ?></td>
                <td><?= e((string) $acc['ip']) ?></td>
                <td>
                  <span class="badge <?= e((string) $acc['status_badge']) ?>">
                    <?= e((string) $acc['status_label']) ?>
                  </span>
                </td>
                <td class="actions-cell">
                  <button type="button" title="Detay" data-detail="<?= (int) $acc['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  <?php if ($acc['status'] === 'BLOCK'): ?>
                    <button type="button" title="Banı kaldır"
                      data-unban-id="<?= (int) $acc['id'] ?>"
                      data-unban-login="<?= e((string) $acc['login']) ?>"
                      data-unban-section="oyuncular"><i class="fa-solid fa-lock-open"></i></button>
                  <?php else: ?>
                    <button type="button" title="Banla" class="danger" data-ban-id="<?= (int) $acc['id'] ?>" data-ban-login="<?= e((string) $acc['login']) ?>"><i class="fa-solid fa-gavel"></i></button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <?php
          $mk = static function (int $p, ?int $per = null) use ($playerQ, $playerStatus, $playerPerPage): string {
              $per = $per ?? $playerPerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'oyuncular',
                  'q' => $playerQ !== '' ? $playerQ : null,
                  'status' => $playerStatus !== '' ? $playerStatus : null,
                  'per' => $per !== 10 ? $per : null,
                  'page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $playerPage ?> / <?= (int) $playerPages ?>
            · <?= (int) $playerPerPage ?> kayıt / sayfa
            · Toplam <?= number_format($playerTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $playerPage <= 1 ? 'disabled' : '' ?>" href="<?= e($mk(max(1, $playerPage - 1))) ?>">Önceki</a>
            <?php
              $start = max(1, $playerPage - 2);
              $end = min($playerPages, $playerPage + 2);
              for ($i = $start; $i <= $end; $i++):
            ?>
              <?php if ($i === $playerPage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($mk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $playerPage >= $playerPages ? 'disabled' : '' ?>" href="<?= e($mk(min($playerPages, $playerPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BANLAR ===================== -->
    <section class="section<?= $panelSection === 'banlar' ? ' active' : '' ?>" id="banlar">
      <div class="card">
        <div class="card-head"><h3>Aktif Banlar</h3><span style="font-size:.8rem; color:var(--ash);"><?= count($activeBans) ?> aktif kısıtlama</span></div>
        <table>
          <thead><tr><th>Hesap</th><th>Ceza</th><th>Sebep</th><th>Süre</th><th>Yetkili</th><th>İşlem</th></tr></thead>
          <tbody>
            <?php if ($activeBans === []): ?>
              <tr><td colspan="6" style="color:var(--ash);">Aktif ban yok.</td></tr>
            <?php else: ?>
              <?php foreach ($activeBans as $ban): ?>
              <tr>
                <td><?= e((string) $ban['account_login']) ?></td>
                <td><span class="badge banned"><?= e((string) $ban['penalty_name']) ?></span></td>
                <td style="color:var(--ash); max-width:220px;"><?= e((string) $ban['reason']) ?></td>
                <td><?= e((string) $ban['remaining_label']) ?></td>
                <td><?= e((string) $ban['banned_by_login']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Detay" data-detail="<?= (int) $ban['account_id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  <button type="button" title="Kaldır"
                    data-unban-id="<?= (int) $ban['account_id'] ?>"
                    data-unban-login="<?= e((string) $ban['account_login']) ?>"
                    data-unban-section="banlar"><i class="fa-solid fa-lock-open"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== CEZA AYARLARI ===================== -->
    <section class="section<?= $panelSection === 'ceza-ayarlari' ? ' active' : '' ?>" id="ceza-ayarlari">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Sabit Cezalar</h3><span style="font-size:.8rem;color:var(--ash);">0 gün = süresiz</span></div>
          <table>
            <thead><tr><th>Ceza Adı</th><th>Sebep</th><th>Süre</th><th></th></tr></thead>
            <tbody>
              <?php if ($penalties === []): ?>
                <tr><td colspan="4" style="color:var(--ash);">Henüz ceza tanımlanmamış.</td></tr>
              <?php else: ?>
                <?php foreach ($penalties as $p): ?>
                <tr>
                  <td><?= e((string) $p['name']) ?></td>
                  <td style="color:var(--ash); max-width:240px;"><?= e((string) $p['reason']) ?></td>
                  <td><?= e((string) $p['days_label']) ?></td>
                  <td class="actions-cell">
                    <button type="button" title="Düzenle"
                      data-edit-penalty
                      data-id="<?= (int) $p['id'] ?>"
                      data-name="<?= e((string) $p['name']) ?>"
                      data-reason="<?= e((string) $p['reason']) ?>"
                      data-days="<?= (int) $p['days'] ?>"><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/ayarlar/ceza/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu cezayı silmek istiyor musun?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                      <button type="submit" class="danger" title="Sil"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="penaltyFormTitle">Yeni Ceza</h3></div>
          <form method="post" action="<?= e(url('/admin/ayarlar/ceza/kaydet')) ?>" id="penaltyForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="penaltyId" value="">
            <div class="form-row"><label>Ceza Adı</label><input name="name" id="penaltyName" required maxlength="120" placeholder="Örn: Bot kullanımı"></div>
            <div class="form-row"><label>Sebep</label><input name="reason" id="penaltyReason" required maxlength="500" placeholder="Ban sebebini yaz"></div>
            <div class="form-row"><label>Kaç gün (0 = süresiz)</label><input type="number" name="days" id="penaltyDays" min="0" max="3650" value="1" required></div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="penaltyReset">Temizle</button>
            </div>
          </form>
        </div>
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
    <p><b id="banTarget">—</b> hesabını banlamak üzeresin. Oyuna giriş engellenir; panele girebilir.</p>
    <form method="post" action="<?= e(url('/admin/oyuncu/ban')) ?>" id="banForm">
      <?= $csrf ?>
      <input type="hidden" name="account_id" id="banAccountId" value="">
      <div class="form-row">
        <label>Sabit Ceza</label>
        <select name="penalty_id" id="banPenaltyId" required>
          <option value="">Seç...</option>
          <?php foreach ($penalties as $p): ?>
            <?php if (!empty($p['is_active'])): ?>
              <option value="<?= (int) $p['id'] ?>"><?= e((string) $p['name']) ?> — <?= e((string) $p['days_label']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <label>Kanıt (opsiyonel — link / açıklama)</label>
        <textarea name="evidence" id="banEvidence" placeholder="https://... veya kısa açıklama" style="min-height:72px;"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="banCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm" id="banConfirm">Banla</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ UNBAN MODAL ============ -->
<div class="modal-overlay" id="unbanModal">
  <div class="modal">
    <h3><i class="fa-solid fa-lock-open"></i> Banı Kaldır</h3>
    <p><b id="unbanTarget">—</b> hesabının banını kaldırmak üzeresin. Sebep zorunludur.</p>
    <form method="post" action="<?= e(url('/admin/oyuncu/unban')) ?>" id="unbanForm">
      <?= $csrf ?>
      <input type="hidden" name="account_id" id="unbanAccountId" value="">
      <input type="hidden" name="redirect_section" id="unbanSection" value="oyuncular">
      <div class="form-row">
        <label>Kaldırma sebebi</label>
        <textarea name="reason" id="unbanReason" required minlength="3" maxlength="500" placeholder="Ban neden kaldırılıyor?" style="min-height:80px;"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="unbanCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Banı Kaldır</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ DETAY MODAL ============ -->
<div class="modal-overlay" id="detailModal">
  <div class="modal modal-lg">
    <h3><i class="fa-solid fa-user"></i> <span id="detailTitle">Oyuncu Detayı</span></h3>
    <div id="detailBody" style="color:var(--ash); font-size:.88rem;">Yükleniyor…</div>
    <div class="modal-actions" style="margin-top:18px;">
      <button type="button" class="btn btn-ghost btn-sm" id="detailClose">Kapat</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const navItems = document.querySelectorAll('.nav-item[data-target]');
  const sections = document.querySelectorAll('.section');
  const initialSection = <?= json_encode($panelSection, JSON_UNESCAPED_UNICODE) ?>;
  const playerJsonUrl = <?= json_encode(url('/admin/oyuncu/json'), JSON_UNESCAPED_UNICODE) ?>;
  const settingsParent = document.getElementById('settingsParent');
  const settingsSub = document.getElementById('settingsSub');

  function showSection(target) {
    if (!target) return;
    document.querySelectorAll('.nav-item').forEach(n => {
      if (n.dataset.target) n.classList.toggle('active', n.dataset.target === target);
    });
    if (settingsParent) {
      const isSettings = target === 'ceza-ayarlari';
      settingsParent.classList.toggle('active', isSettings);
      if (isSettings) {
        settingsParent.classList.add('open');
        settingsSub?.classList.add('open');
      }
    }
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

  settingsParent?.addEventListener('click', () => {
    settingsParent.classList.toggle('open');
    settingsSub?.classList.toggle('open');
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
  const banAccountId = document.getElementById('banAccountId');
  document.querySelectorAll('[data-ban-id]').forEach(btn => {
    btn.addEventListener('click', () => {
      banTarget.textContent = btn.dataset.banLogin || '—';
      banAccountId.value = btn.dataset.banId || '';
      document.getElementById('banPenaltyId').value = '';
      document.getElementById('banEvidence').value = '';
      banModal.classList.add('open');
    });
  });
  document.getElementById('banCancel').addEventListener('click', () => banModal.classList.remove('open'));
  banModal.addEventListener('click', (e) => { if (e.target === banModal) banModal.classList.remove('open'); });

  // Unban modal
  const unbanModal = document.getElementById('unbanModal');
  const unbanTarget = document.getElementById('unbanTarget');
  const unbanAccountId = document.getElementById('unbanAccountId');
  const unbanSection = document.getElementById('unbanSection');
  const unbanReason = document.getElementById('unbanReason');
  document.querySelectorAll('[data-unban-id]').forEach(btn => {
    btn.addEventListener('click', () => {
      unbanTarget.textContent = btn.dataset.unbanLogin || '—';
      unbanAccountId.value = btn.dataset.unbanId || '';
      unbanSection.value = btn.dataset.unbanSection || 'oyuncular';
      unbanReason.value = '';
      unbanModal.classList.add('open');
      unbanReason.focus();
    });
  });
  document.getElementById('unbanCancel').addEventListener('click', () => unbanModal.classList.remove('open'));
  unbanModal.addEventListener('click', (e) => { if (e.target === unbanModal) unbanModal.classList.remove('open'); });
  document.getElementById('unbanForm').addEventListener('submit', (e) => {
    if (!unbanReason.value.trim()) {
      e.preventDefault();
      unbanReason.focus();
      alert('Ban kaldırma sebebi zorunludur.');
    }
  });

  // Detail modal
  const detailModal = document.getElementById('detailModal');
  const detailBody = document.getElementById('detailBody');
  const detailTitle = document.getElementById('detailTitle');
  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  function openDetail(id) {
    detailTitle.textContent = 'Oyuncu Detayı';
    detailBody.innerHTML = 'Yükleniyor…';
    detailModal.classList.add('open');
    fetch(playerJsonUrl + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        if (!res.ok || !res.data) {
          detailBody.innerHTML = '<div style="color:var(--blood-light);">' + esc(res.error || 'Yüklenemedi') + '</div>';
          return;
        }
        const a = res.data.account || {};
        const ban = res.data.active_ban;
        const chars = res.data.characters || [];
        const logs = res.data.activity || [];
        detailTitle.textContent = a.login || 'Oyuncu';
        let html = '<div class="detail-meta">';
        html += '<div class="row"><span class="k">E-posta</span><span class="v">' + esc(a.email || '—') + '</span></div>';
        html += '<div class="row"><span class="k">IP</span><span class="v">' + esc(a.ip || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Kayıt</span><span class="v">' + esc(a.create_label || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Durum</span><span class="v"><span class="badge ' + esc(a.status_badge || '') + '">' + esc(a.status_label || '—') + '</span></span></div>';
        html += '<div class="row"><span class="k">Cash</span><span class="v">' + Number(a.cash || 0).toLocaleString('tr-TR') + '</span></div>';
        if (ban) {
          html += '<div class="row"><span class="k">Aktif Ceza</span><span class="v">' + esc(ban.penalty_name) + ' · ' + esc(ban.days_label) + '</span></div>';
          html += '<div class="row"><span class="k">Sebep</span><span class="v">' + esc(ban.reason) + '</span></div>';
          if (ban.evidence) html += '<div class="row"><span class="k">Kanıt</span><span class="v">' + esc(ban.evidence) + '</span></div>';
          html += '<div class="row"><span class="k">Banlayan</span><span class="v">' + esc(ban.banned_by_login) + '</span></div>';
        }
        html += '</div>';

        html += '<div class="detail-block"><h4>Karakterler (' + chars.length + ')</h4>';
        if (!chars.length) html += '<div>Karakter yok.</div>';
        else {
          html += '<table><thead><tr><th>Ad</th><th>Sınıf</th><th>Sv.</th></tr></thead><tbody>';
          chars.forEach(ch => {
            html += '<tr><td>' + esc(ch.name) + '</td><td>' + esc(ch.job_label) + '</td><td>' + esc(ch.level) + '</td></tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';

        html += '<div class="detail-block"><h4>Panel Hesap Kayıtları</h4>';
        if (!logs.length) html += '<div>Henüz kayıt yok.</div>';
        else {
          html += '<table><thead><tr><th>Zaman</th><th>İşlem</th><th>Detay</th><th>Yetkili</th></tr></thead><tbody>';
          logs.slice(0, 25).forEach(log => {
            html += '<tr><td>' + esc(log.created_label) + '</td><td>' + esc(log.action_label) + '</td>';
            let det = log.detail || '—';
            if (log.evidence) det += (det !== '—' ? ' · ' : '') + 'Kanıt: ' + log.evidence;
            html += '<td style="color:var(--ash);">' + esc(det) + '</td>';
            html += '<td>' + esc(log.actor_login || '—') + '</td></tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';
        detailBody.innerHTML = html;
      })
      .catch(() => {
        detailBody.innerHTML = '<div style="color:var(--blood-light);">Detay yüklenemedi.</div>';
      });
  }
  document.querySelectorAll('[data-detail]').forEach(btn => {
    btn.addEventListener('click', () => openDetail(btn.dataset.detail));
  });
  document.getElementById('detailClose').addEventListener('click', () => detailModal.classList.remove('open'));
  detailModal.addEventListener('click', (e) => { if (e.target === detailModal) detailModal.classList.remove('open'); });

  // Penalty form edit
  const penaltyFormTitle = document.getElementById('penaltyFormTitle');
  const penaltyId = document.getElementById('penaltyId');
  const penaltyName = document.getElementById('penaltyName');
  const penaltyReason = document.getElementById('penaltyReason');
  const penaltyDays = document.getElementById('penaltyDays');
  function resetPenaltyForm() {
    penaltyFormTitle.textContent = 'Yeni Ceza';
    penaltyId.value = '';
    penaltyName.value = '';
    penaltyReason.value = '';
    penaltyDays.value = '1';
  }
  document.querySelectorAll('[data-edit-penalty]').forEach(btn => {
    btn.addEventListener('click', () => {
      penaltyFormTitle.textContent = 'Ceza Düzenle';
      penaltyId.value = btn.dataset.id || '';
      penaltyName.value = btn.dataset.name || '';
      penaltyReason.value = btn.dataset.reason || '';
      penaltyDays.value = btn.dataset.days || '0';
      showSection('ceza-ayarlari');
      penaltyName.focus();
    });
  });
  document.getElementById('penaltyReset')?.addEventListener('click', resetPenaltyForm);

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

  (function onlineChart() {
    const canvas = document.getElementById('onlineChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const labels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const values = <?= json_encode($chartValues, JSON_UNESCAPED_UNICODE) ?>;
    new Chart(canvas, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Çevrimiçi',
          data: values,
          borderColor: '#c9974a',
          backgroundColor: 'rgba(201,151,74,.18)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: labels.length > 40 ? 0 : 3,
          pointHoverRadius: 5,
          pointBackgroundColor: '#eccd8e',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#161009',
            titleColor: '#eccd8e',
            bodyColor: '#e9dfc6',
            borderColor: 'rgba(201,151,74,.3)',
            borderWidth: 1,
          }
        },
        scales: {
          x: {
            ticks: { color: '#9a8f80', maxRotation: 0, autoSkip: true, maxTicksLimit: 12 },
            grid: { color: 'rgba(201,151,74,.08)' }
          },
          y: {
            beginAtZero: true,
            ticks: { color: '#9a8f80', precision: 0 },
            grid: { color: 'rgba(201,151,74,.08)' }
          }
        }
      }
    });
  })();
</script>
</body>
</html>
