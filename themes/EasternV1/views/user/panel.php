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
/** @var list<array> $activityLogs */
/** @var array $activityMeta */
/** @var list<array> $activityLogsModal */
/** @var array|null $activeBan */
/** @var list<array> $ticketCategories */
/** @var list<array> $userTickets */
/** @var list<array> $ticketFileTypes */
/** @var list<array> $announcements */
/** @var list<array> $overviewAnnouncements */
/** @var list<array> $guildWars */
/** @var list<array> $guildWarHistory */
/** @var list<array> $guildWarBoard */
/** @var array $rankings */
/** @var array $marriages */
/** @var array $siteBrand */
/** @var string $appVersion */

$appName = $appName ?? 'M2DN';
$appTagline = $appTagline ?? '';
$appVersion = isset($appVersion) && is_string($appVersion) && $appVersion !== '' ? $appVersion : '2.4.0';
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
$activityLogs = is_array($activityLogs ?? null) ? $activityLogs : [];
$activityMeta = is_array($activityMeta ?? null) ? $activityMeta : [];
$activityLogPage = max(1, (int) ($activityMeta['page'] ?? 1));
$activityLogPages = max(1, (int) ($activityMeta['pages'] ?? 1));
$activityLogTotal = (int) ($activityMeta['total'] ?? count($activityLogs));
$activityLogPerPage = (int) ($activityMeta['per_page'] ?? 10);
$activityLogsModal = is_array($activityLogsModal ?? null) ? $activityLogsModal : $activityLogs;
$activeBan = is_array($activeBan ?? null) ? $activeBan : null;
$ticketCategories = is_array($ticketCategories ?? null) ? $ticketCategories : [];
$userTickets = is_array($userTickets ?? null) ? $userTickets : [];
$ticketFileTypes = is_array($ticketFileTypes ?? null) ? $ticketFileTypes : [];
$announcements = is_array($announcements ?? null) ? $announcements : [];
$overviewAnnouncements = is_array($overviewAnnouncements ?? null) ? $overviewAnnouncements : [];
$latestAnnouncement = $announcements[0] ?? null;
$pastAnnouncements = array_slice($announcements, 1);
$latestOverviewAnn = $overviewAnnouncements[0] ?? null;
$pastOverviewAnn = array_slice($overviewAnnouncements, 1);
$guildWars = is_array($guildWars ?? null) ? $guildWars : [];
$guildWarHistory = is_array($guildWarHistory ?? null) ? $guildWarHistory : [];
$guildWarBoard = is_array($guildWarBoard ?? null) ? $guildWarBoard : [];
$rankings = is_array($rankings ?? null) ? $rankings : [];
$rankRows = is_array($rankings['players'] ?? null) ? $rankings['players'] : [];
$rankTotal = (int) ($rankings['total'] ?? 0);
$rankPage = (int) ($rankings['page'] ?? 1);
$rankPages = max(1, (int) ($rankings['pages'] ?? 1));
$rankQ = (string) ($rankings['q'] ?? '');
$rankPerPage = (int) ($rankings['per_page'] ?? 10);
$rankPerOptions = is_array($rankings['per_page_options'] ?? null) ? $rankings['per_page_options'] : [10, 20, 30, 50, 100];
$marriages = is_array($marriages ?? null) ? $marriages : [];
$marriageRows = is_array($marriages['rows'] ?? null) ? $marriages['rows'] : [];
$marriageTotal = (int) ($marriages['total'] ?? 0);
$marriagePage = (int) ($marriages['page'] ?? 1);
$marriagePages = max(1, (int) ($marriages['pages'] ?? 1));
$marriageQ = (string) ($marriages['q'] ?? '');
$marriagePerPage = (int) ($marriages['per_page'] ?? 20);
$marriagePerOptions = is_array($marriages['per_page_options'] ?? null) ? $marriages['per_page_options'] : [10, 20, 30, 50, 100];
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandUserSize = (int) ($siteBrand['user_size'] ?? 36);
$totpOn = !empty($security['totp_enabled']);
$ipLockOn = !empty($security['ip_lock_enabled']);
$notifyOn = !empty($security['login_notify']);
$isBanned = !empty($account['is_banned']) || strtoupper((string) ($account['status'] ?? '')) === 'BLOCK';
$statusLabel = (string) ($account['status_label'] ?? ($isBanned ? 'Banlı' : 'Aktif'));
$statusBadge = (string) ($account['status_badge'] ?? ($isBanned ? 'banned' : 'online'));

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
<link rel="icon" href="<?= e($brandIcon) ?>">
<link rel="shortcut icon" href="<?= e($brandIcon) ?>">
<link rel="apple-touch-icon" href="<?= e($brandIcon) ?>">
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
  .sidebar-brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; font-size:1.15rem; letter-spacing:.06em; color:var(--gold-light); padding:0 10px 10px; margin-bottom:6px; border-bottom:1px solid var(--line); text-decoration:none;}
  .sidebar-brand img{width:<?= $brandUserSize ?>px; height:<?= $brandUserSize ?>px; object-fit:contain; flex-shrink:0; display:block;}
  .sidebar-brand span{color:var(--blood-light);}
  .sidebar-brand small{display:block; font-family:var(--font-body); font-weight:600; font-size:.6rem; letter-spacing:.16em; color:var(--blood-light); text-transform:uppercase; margin-top:2px;}
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
  .icon-btn{position:relative; width:38px; height:38px; display:flex; align-items:center; justify-content:center; background:var(--obsidian-2); border:1px solid var(--line); color:var(--gold-light); font-size:.9rem; cursor:pointer;}
  .icon-btn .dot{position:absolute; top:6px; right:7px; width:6px; height:6px; border-radius:50%; background:var(--blood-light); display:none;}
  .icon-btn.has-unread .dot{display:block;}
  .icon-btn.empty-bell{color:var(--ash); opacity:.75;}
  .notif-wrap{position:relative;}
  .notif-drop{display:none; position:absolute; right:0; top:calc(100% + 8px); width:340px; max-height:420px; overflow:auto; background:var(--obsidian-2); border:1px solid var(--line); z-index:80; box-shadow:0 12px 40px rgba(0,0,0,.45);}
  .notif-drop.open{display:block;}
  .notif-drop .notif-head{display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-bottom:1px solid var(--line); font-size:.8rem; color:var(--ash);}
  .notif-drop .notif-item{display:block; width:100%; text-align:left; padding:12px 14px; border:0; border-bottom:1px solid rgba(201,151,74,.08); background:transparent; color:inherit; cursor:pointer; font:inherit;}
  .notif-drop .notif-item:hover{background:rgba(201,151,74,.06);}
  .notif-drop .notif-item.unread{background:rgba(201,151,74,.04);}
  .notif-drop .notif-item .t{font-size:.88rem; color:var(--gold-light); font-weight:600; margin-bottom:4px;}
  .notif-drop .notif-item .b{font-size:.78rem; color:var(--ash); margin-bottom:4px;}
  .notif-drop .notif-item .d{font-size:.7rem; color:var(--ash); opacity:.8;}
  .notif-drop .notif-empty{padding:28px 16px; text-align:center; color:var(--ash); font-size:.85rem;}
  .notif-modal-body{font-size:.9rem; color:var(--ash); line-height:1.5;}
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
  .filters{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; align-items:center;}
  .filters input, .filters select{background:var(--obsidian); border:1px solid var(--line); padding:9px 12px; color:var(--parchment); font-size:.8rem; outline:none; font-family:inherit;}
  .filters input:focus, .filters select:focus{border-color:var(--gold);}
  .filters button.btn{cursor:pointer;}

  /* table */
  table{width:100%; border-collapse:collapse; font-size:.85rem;}
  thead th{text-align:left; padding:10px 14px; color:var(--ash); font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--line); font-weight:600;}
  tbody td{padding:14px; border-bottom:1px solid rgba(201,151,74,.08); color:var(--parchment);}
  tbody tr:hover{background:rgba(201,151,74,.04);}
  .actions-cell{white-space:nowrap;}
  .actions-cell button{background:none;border:1px solid var(--line);color:var(--gold-light);width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;margin-right:4px;}
  .actions-cell button:hover{background:rgba(201,151,74,.08);}
  .row-class{display:flex; align-items:center; gap:10px;}
  .row-class i{width:26px; height:26px; display:flex; align-items:center; justify-content:center; background:rgba(201,151,74,.1); color:var(--gold-light); font-size:.75rem;}

  .badge{display:inline-flex; align-items:center; gap:6px; padding:4px 10px; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600;}
  .badge.online{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .badge.offline{background:rgba(154,143,128,.15); color:var(--ash);}
  .badge.pending{background:rgba(201,151,74,.15); color:var(--gold-light);}
  .badge.closed{background:rgba(154,143,128,.15); color:var(--ash);}
  .badge.banned{background:rgba(143,28,41,.2); color:var(--blood-light);}
  .badge.ok{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .char-click{cursor:pointer; transition:background .15s;}
  .char-click:hover{background:rgba(201,151,74,.06);}
  .vote-item.char-click{text-decoration:none; color:inherit;}
  .row-user{display:flex; align-items:center; gap:10px;}
  .row-user .av{width:32px;height:32px;display:flex;align-items:center;justify-content:center;background:rgba(201,151,74,.1);color:var(--gold-light);font-size:.8rem;flex-shrink:0;}
  .row-user .meta{font-size:.72rem;color:var(--ash);margin-top:2px;}
  .guild-tabs{display:flex; gap:8px; flex-wrap:wrap; margin:4px 0 14px;}
  .guild-tabs button{padding:8px 14px; background:var(--obsidian); border:1px solid var(--line); color:var(--ash); cursor:pointer; font:inherit; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;}
  .guild-tabs button.active{color:var(--gold-light); border-color:rgba(201,151,74,.45); background:rgba(201,151,74,.08);}
  .guild-pane{display:none; overflow:auto;}
  .guild-pane.active{display:block;}
  .guild-link{background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;font-weight:600;}
  .guild-link:hover{text-decoration:underline;}

  .modal-overlay{position:fixed; inset:0; background:rgba(0,0,0,.6); backdrop-filter:blur(3px); display:none; align-items:center; justify-content:center; z-index:900; padding:18px;}
  .modal-overlay.open{display:flex;}
  .modal{width:680px; max-width:100%; max-height:88vh; overflow:auto; background:var(--obsidian-2); border:1px solid var(--gold); padding:26px; clip-path:polygon(12px 0,100% 0,100% calc(100% - 12px),calc(100% - 12px) 100%,0 100%,0 12px);}
  .modal.market-modal{
    width:min(1040px, 96vw); height:min(800px, 92vh); max-height:92vh;
    padding:0; overflow:hidden; display:flex; flex-direction:column;
  }
  .modal.market-modal iframe{flex:1; width:100%; height:100%; border:0; background:#050403;}
  .modal h3{font-size:1.1rem; color:var(--gold-light); margin-bottom:14px;}
  .modal .modal-actions{display:flex; gap:12px; justify-content:flex-end; margin-top:18px;}
  .detail-meta{display:grid; gap:6px; margin-bottom:14px;}
  .detail-meta .row{display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid rgba(201,151,74,.08); font-size:.84rem;}
  .detail-meta .k{color:var(--ash);}
  .detail-meta .v{color:var(--gold-light); font-weight:600; text-align:right; word-break:break-all;}
  .detail-block{margin-top:16px;}
  .detail-block h4{font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:var(--ash); margin-bottom:10px;}
  .ban-box{margin-top:14px; padding:14px; border:1px solid rgba(197,51,71,.35); background:rgba(143,28,41,.12);}
  .ban-box h4{color:var(--blood-light); margin-bottom:10px; font-size:.85rem;}
  .modal-pager{display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:12px; flex-wrap:wrap; font-size:.78rem; color:var(--ash);}
  .modal-pager .links{display:flex; gap:8px; align-items:center; flex-wrap:wrap;}
  .modal-pager button{padding:6px 11px; border:1px solid var(--line); background:var(--obsidian); color:var(--gold-light); font-size:.74rem; cursor:pointer;}
  .modal-pager button:hover:not(:disabled){background:rgba(201,151,74,.08);}
  .modal-pager button:disabled{opacity:.4; cursor:not-allowed;}
  .modal-pager .cur{padding:6px 11px; border:1px solid var(--gold); background:rgba(201,151,74,.12); color:var(--gold-light);}

  .pager{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:18px; flex-wrap:wrap; font-size:.8rem; color:var(--ash);}
  .pager .links{display:flex; gap:8px; flex-wrap:wrap;}
  .pager a, .pager span.cur{padding:7px 12px; border:1px solid var(--line); color:var(--gold-light);}
  .pager span.cur{background:rgba(201,151,74,.12); border-color:var(--gold);}
  .pager a:hover{background:rgba(201,151,74,.08);}
  .pager a.disabled{opacity:.4; pointer-events:none;}

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
  .form-row input,
  .form-row select,
  .form-row textarea{width:100%; background:var(--obsidian); border:1px solid var(--line); padding:12px 14px; color:var(--parchment); font-size:.88rem; outline:none; transition:border-color .2s; font-family:inherit; border-radius:0; appearance:none; -webkit-appearance:none;}
  .form-row select{
    background-image:linear-gradient(45deg,transparent 50%,var(--gold) 50%),linear-gradient(135deg,var(--gold) 50%,transparent 50%);
    background-position:calc(100% - 18px) calc(50% - 3px),calc(100% - 12px) calc(50% - 3px);
    background-size:6px 6px,6px 6px;
    background-repeat:no-repeat;
    padding-right:36px;
  }
  .form-row textarea{resize:vertical; min-height:100px;}
  .form-row input:focus,
  .form-row select:focus,
  .form-row textarea:focus{border-color:var(--gold);}
  .form-row input[type="file"]{padding:10px 12px; color:var(--ash); cursor:pointer;}
  .form-row input[type="file"]::file-selector-button{
    background:var(--obsidian-3, #1f160d); border:1px solid var(--line); color:var(--gold-light);
    padding:8px 12px; margin-right:12px; cursor:pointer; font-family:inherit; font-size:.78rem;
  }
  .ticket-msg{border-bottom:1px solid var(--line); padding:12px 0;}
  .ticket-msg:last-child{border-bottom:none;}
  .ticket-msg .who{font-size:.75rem; color:var(--ash); margin-bottom:4px;}
  .ticket-msg .who.staff{color:var(--gold-light);}
  .ticket-msg .body{white-space:pre-wrap; font-size:.9rem; line-height:1.5;}
  .ticket-modal-meta{font-size:.82rem; color:var(--ash); margin-bottom:14px; line-height:1.5;}
  .ticket-modal-msgs{max-height:280px; overflow:auto; border:1px solid var(--line); padding:12px 14px; margin-bottom:14px; background:var(--obsidian);}
  .modal.modal-ticket{width:720px;}
  .security-row{display:flex; align-items:center; justify-content:space-between; padding:16px 0; border-bottom:1px solid rgba(201,151,74,.08);}
  .security-row:last-child{border-bottom:none;}
  .security-row .t{font-size:.9rem; color:var(--parchment); font-weight:600;}
  .security-row .d{font-size:.78rem; color:var(--ash); margin-top:3px;}
  .panel-alert{padding:12px 14px; margin-bottom:18px; font-size:.85rem; border:1px solid var(--line);}
  .panel-alert.ok{background:rgba(51,89,74,.18); color:var(--jade-light); border-color:rgba(79,138,113,.35);}
  .panel-alert.err{background:rgba(143,28,41,.18); color:#e8a0a8; border-color:rgba(197,51,71,.35);}
  .panel-alert ul{margin:6px 0 0 18px; list-style:disc;}
  .ann-card{
    border:1px solid var(--line); border-left:3px solid var(--gold);
    padding:0; margin-bottom:16px; background:linear-gradient(180deg, rgba(201,151,74,.06), rgba(11,9,6,.4));
    overflow:hidden;
  }
  .ann-card .ann-head{
    padding:14px 18px 12px; border-bottom:1px solid rgba(201,151,74,.12);
    background:rgba(11,9,6,.25);
  }
  .ann-card .ann-meta{
    display:flex; flex-wrap:wrap; gap:8px 12px; align-items:center;
    margin-bottom:10px; font-size:.72rem; color:var(--ash); letter-spacing:.02em;
  }
  .ann-card .ann-meta .ann-type{
    display:inline-flex; align-items:center; gap:6px;
    padding:3px 9px; border:1px solid rgba(201,151,74,.28);
    background:rgba(201,151,74,.1); color:var(--gold-light);
    font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em;
  }
  .ann-card .ann-title{
    font-family:var(--font-display); font-size:1.28rem; font-weight:700;
    line-height:1.3; letter-spacing:.03em; color:var(--gold-light); margin:0;
  }
  .ann-card .ann-body{
    padding:16px 18px 18px; font-size:.9rem; line-height:1.7; color:var(--parchment);
  }
  .ann-body p{margin:0 0 .75em;}
  .ann-body p:last-child{margin-bottom:0;}
  .ann-body ul,.ann-body ol{margin:0 0 .75em 1.25em; list-style:disc;}
  .ann-body ol{list-style:decimal;}
  .ann-body table{width:100%; border-collapse:collapse; margin:.6em 0;}
  .ann-body th,.ann-body td{border:1px solid var(--line); padding:6px 8px;}
  .ann-body a{color:var(--gold-light); text-decoration:underline; text-underline-offset:2px;}
  .ann-body h1,.ann-body h2,.ann-body h3{
    font-family:var(--font-display); color:var(--gold-light); margin:.5em 0 .35em; font-size:1.05rem;
  }
  .ann-past{margin-top:14px; border:1px solid var(--line); background:rgba(11,9,6,.25);}
  .ann-past-toggle{
    width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:12px 14px; background:transparent; border:none; color:var(--gold-light);
    font-size:.84rem; font-weight:600; cursor:pointer; font-family:inherit; text-align:left;
  }
  .ann-past-toggle:hover{background:rgba(201,151,74,.06);}
  .ann-past-toggle .chev{transition:transform .2s; font-size:.75rem; opacity:.8;}
  .ann-past.open .ann-past-toggle .chev{transform:rotate(180deg);}
  .ann-past-list{display:none; border-top:1px solid var(--line);}
  .ann-past.open .ann-past-list{display:block;}
  .ann-past-item{
    width:100%; display:flex; align-items:center; gap:12px; padding:12px 14px;
    background:transparent; border:none; border-bottom:1px solid rgba(201,151,74,.08);
    color:inherit; cursor:pointer; font-family:inherit; text-align:left;
  }
  .ann-past-item:last-child{border-bottom:none;}
  .ann-past-item:hover{background:rgba(201,151,74,.07);}
  .ann-past-item .ann-type{
    flex-shrink:0; padding:3px 8px; border:1px solid rgba(201,151,74,.28);
    background:rgba(201,151,74,.1); color:var(--gold-light);
    font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em;
  }
  .ann-past-item .ann-past-title{flex:1; font-size:.9rem; color:var(--parchment); font-weight:600;}
  .ann-past-item .ann-past-date{font-size:.72rem; color:var(--ash); white-space:nowrap;}
  .ann-past-item .ann-past-go{color:var(--gold-light); opacity:.7; font-size:.8rem;}
  .modal.modal-ann{width:640px;}
  .modal.modal-ann .ann-modal-meta{display:flex; flex-wrap:wrap; gap:8px 12px; margin-bottom:12px; font-size:.75rem; color:var(--ash);}
  .modal.modal-ann .ann-modal-meta .ann-type{
    padding:3px 9px; border:1px solid rgba(201,151,74,.28); background:rgba(201,151,74,.1);
    color:var(--gold-light); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em;
  }
  .modal.modal-ann .ann-modal-body{font-size:.9rem; line-height:1.7; color:var(--parchment); max-height:50vh; overflow:auto;}
  .modal.modal-ann .ann-modal-body p{margin:0 0 .7em;}
  .modal.modal-ann .ann-modal-body a{color:var(--gold-light);}
  .search-wrap{position:relative; min-width:220px;}
  .search-box{width:100%;}
  .search-drop{position:absolute; top:calc(100% + 6px); left:0; right:0; background:var(--obsidian-2); border:1px solid var(--line); z-index:40; max-height:260px; overflow:auto;}
  .search-drop a{display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:.82rem; border-bottom:1px solid rgba(201,151,74,.08);}
  .search-drop a:hover{background:rgba(201,151,74,.08);}
  .search-drop .empty{padding:12px; font-size:.8rem; color:var(--ash);}
  .secret-box{font-family:ui-monospace,monospace; letter-spacing:.08em; background:var(--obsidian); border:1px solid var(--line); padding:10px 12px; font-size:.85rem; word-break:break-all; margin:8px 0 14px;}
  .totp-qr-wrap{display:flex; flex-direction:column; align-items:flex-start; gap:12px; margin:10px 0 14px;}
  .totp-qr{
    width:180px; height:180px; padding:10px; background:#fff; border:1px solid var(--line);
    display:flex; align-items:center; justify-content:center;
  }
  .totp-qr img{width:160px; height:160px; display:block;}
  .totp-qr-hint{font-size:.75rem; color:var(--ash); line-height:1.45; max-width:280px;}
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
      <img src="<?= e($brandIcon) ?>" alt="<?= e($appName) ?>">
      <div>M2<span>DN</span><small>Kullanıcı Otomasyonu</small></div>
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
    <a class="nav-item<?= $panelSection === 'siralamalar' ? ' active' : '' ?>" data-target="siralamalar"><i class="fa-solid fa-ranking-star"></i> Oyuncu Sıralaması</a>
    <a class="nav-item<?= $panelSection === 'duyurular' ? ' active' : '' ?>" data-target="duyurular"><i class="fa-solid fa-bullhorn"></i> Duyurular</a>
    <a class="nav-item<?= $panelSection === 'karakterler' ? ' active' : '' ?>" data-target="karakterler"><i class="fa-solid fa-khanda"></i> Karakterlerim</a>
    <a class="nav-item<?= $panelSection === 'evlilikler' ? ' active' : '' ?>" data-target="evlilikler"><i class="fa-solid fa-heart"></i> Evlilikler</a>
    <a class="nav-item<?= $panelSection === 'kayitlar' ? ' active' : '' ?>" data-target="kayitlar"><i class="fa-solid fa-clock-rotate-left"></i> Hesap Kayıtları</a>
    <a class="nav-item<?= $panelSection === 'lonca-savaslari' ? ' active' : '' ?>" data-target="lonca-savaslari"><i class="fa-solid fa-crosshairs"></i> Lonca Savaşları</a>
    <a class="nav-item" href="<?= e(url('/nesne-market')) ?>" data-open-market><i class="fa-solid fa-store"></i> Nesne Market</a>

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
            <?= e(\App\Services\PermissionService::groupNameForUser($authUser)) ?> · v<?= e((string) ($appVersion ?? '1.10.2')) ?>
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
                <button type="button" data-open-char="<?= (int) $sr['id'] ?>" style="display:flex;align-items:center;gap:10px;width:100%;background:none;border:none;color:inherit;padding:10px 12px;cursor:pointer;text-align:left;font:inherit;">
                  <i class="fa-solid <?= e($sr['job_icon']) ?>" style="color:var(--gold-light);"></i>
                  <span><?= e($sr['name']) ?> · Sv. <?= (int) $sr['level'] ?> · <?= e($sr['job_label']) ?></span>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="notif-wrap">
          <button type="button" class="icon-btn empty-bell" id="notifBellBtn" aria-label="Bildirimler" aria-expanded="false">
            <i class="fa-solid fa-bell-slash" id="notifBellIcon"></i><span class="dot"></span>
          </button>
          <div class="notif-drop" id="notifDrop" role="dialog" aria-label="Bildirimler">
            <div class="notif-head">
              <span>Bildirimler</span>
              <button type="button" class="btn btn-ghost btn-sm" id="notifMarkAll" style="padding:4px 8px;font-size:.68rem;">Tümünü okundu</button>
            </div>
            <div id="notifList"><div class="notif-empty">Yükleniyor…</div></div>
          </div>
        </div>
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
      <div class="char-banner char-click" data-open-char="<?= (int) $primary['id'] ?>" role="button" tabindex="0" title="Detay">
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
            <span>Durum <b style="color:<?= $isBanned ? 'var(--blood-light)' : 'var(--jade-light)' ?>"><?= e($statusLabel) ?></b></span>
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
        <div class="quick-actions" onclick="event.stopPropagation()">
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
          <span class="lbl">Elmas</span>
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
            <button type="button" class="vote-item char-click" data-open-char="<?= (int) $ch['id'] ?>" style="width:100%; text-align:left; background:none; border:none; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid rgba(201,151,74,.08);">
              <div>
                <div class="name"><i class="fa-solid <?= e($ch['job_icon']) ?>" style="margin-right:6px;color:var(--gold-light);"></i><?= e($ch['name']) ?></div>
                <div class="cooldown"><?= e($ch['job_label']) ?> · Sv. <?= (int) $ch['level'] ?><?= !empty($ch['guild']) ? ' · ' . e($ch['guild']) : '' ?></div>
              </div>
              <span class="badge <?= e($statusBadge) ?>"><?= e($statusLabel) ?></span>
            </button>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="card-head"><h3>Hesap Özeti</h3></div>
          <button type="button" class="security-row char-click" data-open-account style="width:100%; text-align:left; background:none; border:none; display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer; padding:12px 0; border-bottom:1px solid rgba(201,151,74,.08); color:inherit;">
            <div><div class="t">Kullanıcı Adı</div><div class="d"><?= e((string) ($account['login'] ?? '')) ?></div></div>
            <span class="badge <?= e($statusBadge) ?>"><?= e($statusLabel) ?></span>
          </button>
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
          <?php if ($isBanned && $activeBan): ?>
          <div class="security-row">
            <div>
              <div class="t">Ban Sebebi</div>
              <div class="d"><?= e((string) $activeBan['penalty_name']) ?> · <?= e((string) $activeBan['days_label']) ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card" style="margin-top:22px;">
        <div class="card-head">
          <h3>Duyurular</h3>
          <a href="#duyurular" data-jump="duyurular">Tümü</a>
        </div>
        <?php if ($latestOverviewAnn === null): ?>
          <p style="color:var(--ash);font-size:.88rem;">Aktif duyuru yok.</p>
        <?php else: ?>
          <?php $ann = $latestOverviewAnn; ?>
          <article class="ann-card">
            <div class="ann-head">
              <div class="ann-meta">
                <span class="ann-type"><?= e((string) ($ann['type_name'] ?: 'Duyuru')) ?></span>
                <span><?= e((string) $ann['published_label']) ?></span>
              </div>
              <h4 class="ann-title"><?= e((string) $ann['title']) ?></h4>
            </div>
            <div class="ann-body"><?= \App\Services\AnnouncementService::sanitizeHtml((string) $ann['body']) ?></div>
          </article>
          <?php if ($pastOverviewAnn !== []): ?>
          <div class="ann-past">
            <button type="button" class="ann-past-toggle" data-ann-past-toggle>
              <span>Geçmiş duyurular (<?= count($pastOverviewAnn) ?>)</span>
              <i class="fa-solid fa-chevron-down chev"></i>
            </button>
            <div class="ann-past-list">
              <?php foreach ($pastOverviewAnn as $ann): ?>
                <button type="button" class="ann-past-item" data-open-ann="<?= (int) $ann['id'] ?>">
                  <span class="ann-type"><?= e((string) ($ann['type_name'] ?: 'Duyuru')) ?></span>
                  <span class="ann-past-title"><?= e((string) $ann['title']) ?></span>
                  <span class="ann-past-date"><?= e((string) $ann['published_label']) ?></span>
                  <i class="fa-solid fa-up-right-from-square ann-past-go"></i>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== OYUNCU SIRALAMASI ===================== -->
    <section class="section<?= $panelSection === 'siralamalar' ? ' active' : '' ?>" id="siralamalar">
      <div class="card">
        <div class="card-head">
          <h3>Oyuncu Sıralaması</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem;color:var(--ash);"><?= number_format($rankTotal, 0, ',', '.') ?> karakter · level DESC</span>
            <?php
              $rankRefreshQs = http_build_query(array_filter([
                  'section' => 'siralamalar',
                  'rank_q' => $rankQ !== '' ? $rankQ : null,
                  'rank_per' => $rankPerPage !== 10 ? $rankPerPage : null,
                  'rank_page' => $rankPage > 1 ? $rankPage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/panel?' . $rankRefreshQs)) ?>"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Sunucudaki karakterler level sırasına göre listelenir. Detayda karakter adı, job, level, stamina, lonca ve bayrak bilgisi görünür.
        </p>
        <form class="filters" method="get" action="<?= e(url('/panel')) ?>">
          <input type="hidden" name="section" value="siralamalar">
          <input name="rank_q" value="<?= e($rankQ) ?>" placeholder="Karakter veya lonca ara..." style="flex:1;min-width:200px;">
          <select name="rank_per">
            <?php foreach ($rankPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $rankPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Karakter</th>
              <th>Job</th>
              <th>Level</th>
              <th>Stamina</th>
              <th>Lonca</th>
              <th>Bayrak</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($rankRows === []): ?>
              <tr><td colspan="8" style="color:var(--ash);">Kayıt yok.</td></tr>
            <?php else: ?>
              <?php
                $rankBase = ($rankPage - 1) * $rankPerPage;
                foreach ($rankRows as $ri => $rp):
              ?>
              <tr>
                <td><?= $rankBase + $ri + 1 ?></td>
                <td><?= e((string) $rp['name']) ?></td>
                <td><?= e((string) $rp['job_label']) ?></td>
                <td><?= (int) $rp['level'] ?></td>
                <td><?= (int) $rp['stamina'] ?></td>
                <td><?= e((string) $rp['guild_name']) ?></td>
                <td><?= e((string) $rp['empire_label']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Detay"
                    data-rank-detail
                    data-rank-name="<?= e((string) $rp['name']) ?>"
                    data-rank-job="<?= e((string) $rp['job_label']) ?>"
                    data-rank-level="<?= (int) $rp['level'] ?>"
                    data-rank-stamina="<?= (int) $rp['stamina'] ?>"
                    data-rank-guild="<?= e((string) $rp['guild_name']) ?>"
                    data-rank-empire="<?= e((string) $rp['empire_label']) ?>"><i class="fa-solid fa-eye"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $rmk = static function (int $p) use ($rankQ, $rankPerPage): string {
              $qs = http_build_query(array_filter([
                  'section' => 'siralamalar',
                  'rank_q' => $rankQ !== '' ? $rankQ : null,
                  'rank_per' => $rankPerPage !== 10 ? $rankPerPage : null,
                  'rank_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/panel' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>Sayfa <?= (int) $rankPage ?> / <?= (int) $rankPages ?> · Toplam <?= number_format($rankTotal, 0, ',', '.') ?></div>
          <div class="links">
            <a class="<?= $rankPage <= 1 ? 'disabled' : '' ?>" href="<?= e($rmk(max(1, $rankPage - 1))) ?>">Önceki</a>
            <?php
              $rStart = max(1, $rankPage - 2);
              $rEnd = min($rankPages, $rankPage + 2);
              for ($i = $rStart; $i <= $rEnd; $i++):
            ?>
              <?php if ($i === $rankPage): ?><span class="cur"><?= $i ?></span><?php else: ?><a href="<?= e($rmk($i)) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $rankPage >= $rankPages ? 'disabled' : '' ?>" href="<?= e($rmk(min($rankPages, $rankPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== DUYURULAR ===================== -->
    <section class="section<?= $panelSection === 'duyurular' ? ' active' : '' ?>" id="duyurular">
      <div class="card">
        <div class="card-head">
          <h3>Sunucu Duyuruları</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= count($announcements) ?> aktif</span>
        </div>
        <?php if ($latestAnnouncement === null): ?>
          <p style="color:var(--ash);font-size:.9rem;">Henüz yayınlanmış duyuru yok.</p>
        <?php else: ?>
          <?php $ann = $latestAnnouncement; ?>
          <article class="ann-card">
            <div class="ann-head">
              <div class="ann-meta">
                <span class="ann-type"><?= e((string) ($ann['type_name'] ?: 'Duyuru')) ?></span>
                <span><?= e((string) $ann['published_label']) ?></span>
                <?php if (!empty($ann['author_login'])): ?>
                  <span>· <?= e((string) $ann['author_login']) ?></span>
                <?php endif; ?>
              </div>
              <h4 class="ann-title"><?= e((string) $ann['title']) ?></h4>
            </div>
            <div class="ann-body"><?= \App\Services\AnnouncementService::sanitizeHtml((string) $ann['body']) ?></div>
          </article>
          <?php if ($pastAnnouncements !== []): ?>
          <div class="ann-past open">
            <button type="button" class="ann-past-toggle" data-ann-past-toggle>
              <span>Geçmiş duyurular (<?= count($pastAnnouncements) ?>)</span>
              <i class="fa-solid fa-chevron-down chev"></i>
            </button>
            <div class="ann-past-list">
              <?php foreach ($pastAnnouncements as $ann): ?>
                <button type="button" class="ann-past-item" data-open-ann="<?= (int) $ann['id'] ?>">
                  <span class="ann-type"><?= e((string) ($ann['type_name'] ?: 'Duyuru')) ?></span>
                  <span class="ann-past-title"><?= e((string) $ann['title']) ?></span>
                  <span class="ann-past-date"><?= e((string) $ann['published_label']) ?></span>
                  <i class="fa-solid fa-up-right-from-square ann-past-go"></i>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== LONCA SAVAŞLARI ===================== -->
    <section class="section<?= $panelSection === 'lonca-savaslari' ? ' active' : '' ?>" id="lonca-savaslari">
      <div class="card">
        <div class="card-head">
          <h3>Lonca Savaşları</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem;color:var(--ash);"><?= count($guildWars) ?> aktif · <?= count($guildWarHistory) ?> geçmiş · salt görüntüleme</span>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/panel?section=lonca-savaslari')) ?>" title="Yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Canlı savaşlar, geçmiş sonuçlar, ganimet ve lonca sıralaması. Lonca adına tıklayarak savaş istatistiklerini görebilirsin.
        </p>

        <div class="guild-tabs" id="userWarTabs">
          <button type="button" class="active" data-war-tab="active">Aktif (<?= count($guildWars) ?>)</button>
          <button type="button" data-war-tab="history">Geçmiş (<?= count($guildWarHistory) ?>)</button>
          <button type="button" data-war-tab="board">Sıralama</button>
        </div>

        <div class="guild-pane active" data-war-pane="active">
          <table>
            <thead>
              <tr>
                <th>Lonca A</th>
                <th></th>
                <th>Lonca B</th>
                <th>Tür</th>
                <th>Skor</th>
                <th>Ladder</th>
                <th>Ganimet</th>
                <th>Bahis</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($guildWars === []): ?>
                <tr><td colspan="8" style="color:var(--ash);">Şu an aktif lonca savaşı yok.</td></tr>
              <?php else: ?>
                <?php foreach ($guildWars as $w): ?>
                <tr>
                  <td class="row-user">
                    <div class="av"><i class="fa-solid fa-shield"></i></div>
                    <div>
                      <button type="button" class="guild-link" data-guild-card="<?= (int) $w['from_id'] ?>"><?= e((string) $w['from_name']) ?></button>
                      <div class="meta">Sv.<?= (int) $w['from_level'] ?> · <?= (int) ($w['from_win'] ?? 0) ?>/<?= (int) ($w['from_draw'] ?? 0) ?>/<?= (int) ($w['from_loss'] ?? 0) ?></div>
                    </div>
                  </td>
                  <td style="text-align:center;color:var(--blood-light);font-weight:700;letter-spacing:.08em;">VS</td>
                  <td class="row-user">
                    <div class="av"><i class="fa-solid fa-shield"></i></div>
                    <div>
                      <button type="button" class="guild-link" data-guild-card="<?= (int) $w['to_id'] ?>"><?= e((string) $w['to_name']) ?></button>
                      <div class="meta">Sv.<?= (int) $w['to_level'] ?> · <?= (int) ($w['to_win'] ?? 0) ?>/<?= (int) ($w['to_draw'] ?? 0) ?>/<?= (int) ($w['to_loss'] ?? 0) ?></div>
                    </div>
                  </td>
                  <td><span class="badge ok"><?= e((string) $w['war_type_label']) ?></span></td>
                  <td style="font-family:var(--font-display);color:var(--gold-light);"><?= e((string) $w['score_label']) ?></td>
                  <td style="font-size:.82rem;">
                    <?= number_format((int) $w['from_ladder'], 0, ',', '.') ?>
                    <span style="color:var(--ash);"> / </span>
                    <?= number_format((int) $w['to_ladder'], 0, ',', '.') ?>
                  </td>
                  <td><?= e((string) ($w['warprice_label'] ?? '—')) ?></td>
                  <td><?= (int) ($w['bet_total'] ?? 0) > 0 ? number_format((int) $w['bet_total'], 0, ',', '.') : '—' ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="guild-pane" data-war-pane="history">
          <table>
            <thead>
              <tr>
                <th>Tarih</th>
                <th>Maç</th>
                <th>Tür</th>
                <th>Skor</th>
                <th>Kazanan</th>
                <th>Ganimet</th>
                <th>Bahis</th>
                <th>Durum</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($guildWarHistory === []): ?>
                <tr><td colspan="8" style="color:var(--ash);">Geçmiş savaş kaydı yok.</td></tr>
              <?php else: ?>
                <?php foreach ($guildWarHistory as $w): ?>
                <tr>
                  <td style="font-size:.82rem;white-space:nowrap;"><?= e((string) ($w['reserved_label'] ?? '—')) ?></td>
                  <td>
                    <button type="button" class="guild-link" data-guild-card="<?= (int) $w['from_id'] ?>"><?= e((string) $w['from_name']) ?></button>
                    <span style="color:var(--ash);"> vs </span>
                    <button type="button" class="guild-link" data-guild-card="<?= (int) $w['to_id'] ?>"><?= e((string) $w['to_name']) ?></button>
                  </td>
                  <td><span class="badge ok"><?= e((string) $w['war_type_label']) ?></span></td>
                  <td style="color:var(--gold-light);"><?= e((string) $w['score_label']) ?></td>
                  <td><?= e((string) (($w['winner_name'] ?? '') !== '' ? $w['winner_name'] : '—')) ?></td>
                  <td><?= e((string) ($w['warprice_label'] ?? '—')) ?></td>
                  <td><?= (int) ($w['bet_total'] ?? 0) > 0 ? number_format((int) $w['bet_total'], 0, ',', '.') : '—' ?></td>
                  <td style="font-size:.8rem;color:var(--ash);"><?= e((string) ($w['status_label'] ?? '—')) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="guild-pane" data-war-pane="board">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Lonca</th>
                <th>Usta</th>
                <th>Savaş</th>
                <th>G</th>
                <th>B</th>
                <th>M</th>
                <th>Galibiyet %</th>
                <th>Ladder</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($guildWarBoard === []): ?>
                <tr><td colspan="9" style="color:var(--ash);">Henüz savaş kaydı olan lonca yok.</td></tr>
              <?php else: ?>
                <?php foreach ($guildWarBoard as $row): ?>
                <tr>
                  <td><?= (int) $row['rank'] ?></td>
                  <td>
                    <button type="button" class="guild-link" data-guild-card="<?= (int) $row['id'] ?>"><?= e((string) $row['name']) ?></button>
                    <div class="meta" style="font-size:.72rem;color:var(--ash);">Sv.<?= (int) $row['level'] ?></div>
                  </td>
                  <td><?= e((string) $row['master_name']) ?></td>
                  <td><?= (int) $row['wars'] ?></td>
                  <td style="color:var(--gold-light);"><?= (int) $row['win'] ?></td>
                  <td><?= (int) $row['draw'] ?></td>
                  <td><?= (int) $row['loss'] ?></td>
                  <td><?= e(number_format((float) $row['win_rate'], 1, ',', '.')) ?>%</td>
                  <td><?= number_format((int) $row['ladder_point'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
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
          <thead><tr><th>Karakter</th><th>Sınıf</th><th>Seviye</th><th>Eş</th><th>Klan</th><th>Yang</th><th>Oyun Süresi</th><th>Durum</th></tr></thead>
          <tbody>
            <?php foreach ($characters as $ch): ?>
            <tr class="char-click" data-open-char="<?= (int) $ch['id'] ?>" style="cursor:pointer;">
              <td class="row-class">
                <i class="fa-solid <?= e($ch['job_icon']) ?>"></i> <?= e($ch['name']) ?>
              </td>
              <td><?= e($ch['job_label']) ?></td>
              <td><?= (int) $ch['level'] ?> / <?= (int) $maxLevel ?></td>
              <td><?= !empty($ch['married']) && !empty($ch['spouse_name']) ? e((string) $ch['spouse_name']) : 'Bekar' ?></td>
              <td><?= e($ch['guild'] ?? '—') ?></td>
              <td><?= number_format((int) $ch['gold'], 0, ',', '.') ?></td>
              <td><?= e($ch['playtime_label']) ?></td>
              <td><span class="badge <?= e($statusBadge) ?>"><?= e($statusLabel) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== EVLİLİKLER ===================== -->
    <section class="section<?= $panelSection === 'evlilikler' ? ' active' : '' ?>" id="evlilikler">
      <div class="card">
        <div class="card-head">
          <h3>Evlilikler</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= number_format($marriageTotal, 0, ',', '.') ?> kayıt · salt görüntüleme</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/panel')) ?>" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
          <input type="hidden" name="section" value="evlilikler">
          <input name="marriage_q" value="<?= e($marriageQ) ?>" placeholder="Karakter adı ara..." style="flex:1; min-width:180px;">
          <select name="marriage_per" title="Sayfa başına">
            <?php foreach ($marriagePerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $marriagePerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrele</button>
        </form>
        <table>
          <thead><tr><th>Karakter 1</th><th>Karakter 2</th><th>Aşk Puanı</th><th>Tarih</th></tr></thead>
          <tbody>
            <?php if ($marriageRows === []): ?>
              <tr><td colspan="4" style="color:var(--ash);">Evlilik kaydı yok.</td></tr>
            <?php else: ?>
              <?php foreach ($marriageRows as $mr): ?>
              <tr>
                <td>
                  <div><?= e((string) $mr['name1']) ?></div>
                  <div style="font-size:.72rem;color:var(--ash);">Sv. <?= (int) $mr['level1'] ?> · <?= e((string) $mr['job_label1']) ?></div>
                </td>
                <td>
                  <div><?= e((string) $mr['name2']) ?></div>
                  <div style="font-size:.72rem;color:var(--ash);">Sv. <?= (int) $mr['level2'] ?> · <?= e((string) $mr['job_label2']) ?></div>
                </td>
                <td><?= $mr['love_point'] !== null ? number_format((int) $mr['love_point'], 0, ',', '.') : '—' ?></td>
                <td><?= e((string) $mr['time_label']) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $mkUserMarriage = static function (int $p) use ($marriageQ, $marriagePerPage): string {
              $qs = http_build_query(array_filter([
                  'section' => 'evlilikler',
                  'marriage_q' => $marriageQ !== '' ? $marriageQ : null,
                  'marriage_per' => $marriagePerPage !== 20 ? $marriagePerPage : null,
                  'marriage_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/panel' . ($qs !== '' ? '?' . $qs : '?section=evlilikler'));
          };
        ?>
        <?php if ($marriagePages > 1 || $marriageTotal > $marriagePerPage): ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $marriagePage ?> / <?= (int) $marriagePages ?>
            · Toplam <?= number_format($marriageTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $marriagePage <= 1 ? 'disabled' : '' ?>" href="<?= e($mkUserMarriage(max(1, $marriagePage - 1))) ?>">Önceki</a>
            <?php
              $umStart = max(1, $marriagePage - 2);
              $umEnd = min($marriagePages, $marriagePage + 2);
              for ($i = $umStart; $i <= $umEnd; $i++):
            ?>
              <?php if ($i === $marriagePage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($mkUserMarriage($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $marriagePage >= $marriagePages ? 'disabled' : '' ?>" href="<?= e($mkUserMarriage(min($marriagePages, $marriagePage + 1))) ?>">Sonraki</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== HESAP KAYITLARI ===================== -->
    <section class="section<?= $panelSection === 'kayitlar' ? ' active' : '' ?>" id="kayitlar">
      <div class="card">
        <div class="card-head">
          <h3>Hesap Kayıtları</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= number_format($activityLogTotal, 0, ',', '.') ?> kayıt · sayfa başına <?= (int) $activityLogPerPage ?></span>
        </div>
        <?php if ($activityLogs === []): ?>
          <div style="color:var(--ash); font-size:.9rem; padding:8px 0;">Henüz kayıt yok. Panele giriş ve güvenlik işlemleri burada listelenir.</div>
        <?php else: ?>
        <table>
          <thead><tr><th>Zaman</th><th>İşlem</th><th>Detay / Kanıt</th><th>Yetkili</th></tr></thead>
          <tbody>
            <?php foreach ($activityLogs as $log): ?>
            <tr>
              <td><?= e((string) $log['created_label']) ?></td>
              <td><?= e((string) $log['action_label']) ?></td>
              <td style="color:var(--ash);">
                <?php
                  $det = (string) ($log['detail'] ?? '');
                  $ev = (string) ($log['evidence'] ?? '');
                  if ($det === '' && $ev === '') {
                      echo '—';
                  } else {
                      echo e($det !== '' ? $det : '');
                      if ($ev !== '') {
                          echo ($det !== '' ? '<br>' : '') . '<span style="opacity:.85;">Kanıt: ' . e($ev) . '</span>';
                      }
                  }
                ?>
              </td>
              <td><?= e((string) (($log['actor_login'] ?? '') !== '' ? $log['actor_login'] : '—')) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php
          $logMk = static function (int $p): string {
              $qs = http_build_query(array_filter([
                  'section' => 'kayitlar',
                  'log_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/panel' . ($qs !== '' ? '?' . $qs : '?section=kayitlar'));
          };
        ?>
        <?php if ($activityLogPages > 1 || $activityLogTotal > $activityLogPerPage): ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $activityLogPage ?> / <?= (int) $activityLogPages ?>
            · Toplam <?= number_format($activityLogTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $activityLogPage <= 1 ? 'disabled' : '' ?>" href="<?= e($logMk(max(1, $activityLogPage - 1))) ?>">Önceki</a>
            <?php
              $lStart = max(1, $activityLogPage - 2);
              $lEnd = min($activityLogPages, $activityLogPage + 2);
              for ($i = $lStart; $i <= $lEnd; $i++):
            ?>
              <?php if ($i === $activityLogPage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($logMk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $activityLogPage >= $activityLogPages ? 'disabled' : '' ?>" href="<?= e($logMk(min($activityLogPages, $activityLogPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== DESTEK ===================== -->
    <section class="section<?= ($panelSection ?? '') === 'destek' ? ' active' : '' ?>" id="destek">
      <?php
        $acceptExt = implode(',', array_map(static fn($t) => '.' . $t['extension'], $ticketFileTypes));
      ?>
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Destek Taleplerim</h3></div>
          <table>
            <thead><tr><th>Kod</th><th>Konu</th><th>Kategori</th><th>Tarih</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($userTickets === []): ?>
                <tr><td colspan="6" style="color:var(--ash);">Henüz ticket yok.</td></tr>
              <?php else: ?>
                <?php foreach ($userTickets as $t): ?>
                <tr>
                  <td><code><?= e((string) $t['public_code']) ?></code></td>
                  <td><?= e((string) $t['subject']) ?></td>
                  <td><?= e((string) $t['category_name']) ?></td>
                  <td><?= e((string) $t['created_label']) ?></td>
                  <td><span class="badge <?= ($t['status_code'] ?? '') === 'closed' ? 'closed' : 'pending' ?>"><?= e((string) $t['status_label']) ?></span></td>
                  <td class="actions-cell">
                    <button type="button" title="Görüntüle" data-ticket-view="<?= (int) $t['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3>Ticket Oluştur</h3></div>
          <form method="post" action="<?= e(url('/panel/ticket')) ?>" enctype="multipart/form-data">
            <?= $csrf ?>
            <div class="form-row">
              <label>Kategori</label>
              <select name="category_id" required>
                <option value="">Seç...</option>
                <?php foreach ($ticketCategories as $cat): ?>
                  <option value="<?= (int) $cat['id'] ?>"><?= e((string) $cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row"><label>Konu başlığı</label><input name="subject" required maxlength="200" placeholder="Kısaca sorunu özetle"></div>
            <div class="form-row"><label>Açıklama</label><textarea name="body" required style="min-height:100px;" placeholder="Detaylı anlat..."></textarea></div>
            <div class="form-row">
              <label>Dosya (opsiyonel)</label>
              <input type="file" name="attachment"<?= $acceptExt !== '' ? ' accept="' . e($acceptExt) . '"' : '' ?>>
              <?php if ($ticketFileTypes !== []): ?>
                <div style="font-size:.72rem;color:var(--ash);margin-top:6px;">
                  İzinli: <?= e(implode(', ', array_map(static fn($t) => '.' . $t['extension'], $ticketFileTypes))) ?> · max 5MB
                </div>
              <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Oluştur</button>
          </form>
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
          <div class="card-head"><h3>Güvenlik Kodu</h3></div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:14px;">Kayıt sırasında belirlenen güvenlik kodu. Hesap parolanı doğrula, ardından yeni 1–6 haneli kodu gir.</p>
          <form method="post" action="<?= e(url('/panel/guvenlik/guvenlik-kodu')) ?>" autocomplete="off">
            <?= $csrf ?>
            <div class="form-row"><label for="sec-pass">Hesap Parolası</label><input id="sec-pass" name="password" type="password" maxlength="16" required></div>
            <div class="form-row"><label for="sec-new">Yeni Güvenlik Kodu</label><input id="sec-new" name="new_securitycode" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <div class="form-row"><label for="sec-new2">Tekrar</label><input id="sec-new2" name="new_securitycode_confirm" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <button type="submit" class="btn btn-primary btn-block">Güvenlik Kodunu Güncelle</button>
          </form>

          <div style="border-top:1px solid var(--line); margin:22px 0 16px;"></div>
          <div class="card-head" style="padding:0;margin-bottom:10px;"><h3 style="font-size:1rem;">Depo Şifresi</h3></div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:14px;">Oyun içi depo (safebox) şifresi. Güvenlik kodundan ayrıdır.</p>
          <form method="post" action="<?= e(url('/panel/guvenlik/depo')) ?>" autocomplete="off">
            <?= $csrf ?>
            <div class="form-row"><label for="box-pass">Hesap Parolası</label><input id="box-pass" name="password" type="password" maxlength="16" required></div>
            <div class="form-row"><label for="box-new">Yeni Depo Şifresi</label><input id="box-new" name="new_safebox_password" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <div class="form-row"><label for="box-new2">Tekrar</label><input id="box-new2" name="new_safebox_password_confirm" type="text" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required></div>
            <button type="submit" class="btn btn-primary btn-block">Depo Şifresini Güncelle</button>
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
            <?php
              $totpUri = (string) ($totpSetup['uri'] ?? '');
              $totpSecret = (string) ($totpSetup['secret'] ?? '');
              $totpQrSrc = $totpUri !== ''
                  ? 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&ecc=M&margin=8&data=' . rawurlencode($totpUri)
                  : '';
            ?>
            <div style="font-size:.8rem;color:var(--ash);margin:8px 0;">Authenticator uygulamasıyla QR kodu tara. Olmazsa anahtarı elle gir, ardından 6 haneli kodu onayla:</div>
            <?php if ($totpQrSrc !== ''): ?>
            <div class="totp-qr-wrap">
              <div class="totp-qr">
                <img src="<?= e($totpQrSrc) ?>" alt="2FA QR kodu" width="160" height="160" loading="lazy">
              </div>
              <div class="totp-qr-hint">Google Authenticator, Authy vb. ile tara. QR görünmezse aşağıdaki anahtarı kullan.</div>
            </div>
            <?php endif; ?>
            <div class="secret-box"><?= e($totpSecret) ?></div>
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

<?php
  $modalChars = [];
  foreach ($characters as $ch) {
      $modalChars[(string) (int) $ch['id']] = [
          'id' => (int) $ch['id'],
          'name' => (string) $ch['name'],
          'job_label' => (string) $ch['job_label'],
          'job_icon' => (string) $ch['job_icon'],
          'level' => (int) $ch['level'],
          'guild' => (string) ($ch['guild'] ?? ''),
          'empire_label' => (string) ($ch['empire_label'] ?? ''),
          'gold' => (int) ($ch['gold'] ?? 0),
          'playtime_label' => (string) ($ch['playtime_label'] ?? '—'),
          'married' => !empty($ch['married']),
          'spouse_name' => (string) ($ch['spouse_name'] ?? ''),
      ];
  }
  $modalPayload = [
      'account' => [
          'login' => (string) ($account['login'] ?? ''),
          'email' => (string) ($account['email'] ?? ''),
          'status_label' => $statusLabel,
          'status_badge' => $statusBadge,
          'is_banned' => $isBanned,
          'create_label' => $createLabel,
      ],
      'ban' => $activeBan,
      'activity' => array_map(static function (array $log): array {
          return [
              'created_label' => (string) ($log['created_label'] ?? ''),
              'action_label' => (string) ($log['action_label'] ?? ''),
              'detail' => (string) ($log['detail'] ?? ''),
              'evidence' => (string) ($log['evidence'] ?? ''),
              'actor_login' => (string) ($log['actor_login'] ?? ''),
          ];
      }, $activityLogsModal),
      'characters' => $modalChars,
      'max_level' => (int) $maxLevel,
  ];
  $annModalMap = [];
  foreach ($announcements as $annRow) {
      $aid = (string) (int) $annRow['id'];
      $annModalMap[$aid] = [
          'id' => (int) $annRow['id'],
          'title' => (string) $annRow['title'],
          'type_name' => (string) ($annRow['type_name'] ?: 'Duyuru'),
          'published_label' => (string) $annRow['published_label'],
          'author_login' => (string) ($annRow['author_login'] ?? ''),
          'body' => \App\Services\AnnouncementService::sanitizeHtml((string) $annRow['body']),
      ];
  }
?>

<div class="modal-overlay" id="annModal">
  <div class="modal modal-ann">
    <h3 id="annModalTitle">Duyuru</h3>
    <div class="ann-modal-meta" id="annModalMeta"></div>
    <div class="ann-modal-body" id="annModalBody"></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost btn-sm" id="annModalClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="accountModal">
  <div class="modal">
    <h3><i class="fa-solid fa-user"></i> <span id="accountModalTitle">Hesap Detayı</span></h3>
    <div id="accountModalBody"></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost btn-sm" id="accountModalClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="ticketModal">
  <div class="modal modal-ticket">
    <h3 id="ticketModalTitle">Ticket</h3>
    <div class="ticket-modal-meta" id="ticketModalMeta"></div>
    <div class="ticket-modal-msgs" id="ticketModalMessages"></div>
    <div id="ticketModalNote" class="ticket-modal-meta" style="display:none;"></div>
    <div id="ticketModalReplyWrap" style="display:none;">
      <form method="post" action="<?= e(url('/panel/ticket/yanit')) ?>" enctype="multipart/form-data" id="ticketReplyForm">
        <?= $csrf ?>
        <input type="hidden" name="ticket_id" id="ticketReplyId" value="">
        <div class="form-row"><label>Yeni yanıt</label><textarea name="body" id="ticketReplyBody" required placeholder="Yanıtını yaz..."></textarea></div>
        <div class="form-row">
          <label>Dosya (opsiyonel)</label>
          <input type="file" name="attachment" id="ticketReplyFile"<?= !empty($acceptExt) ? ' accept="' . e($acceptExt) . '"' : '' ?>>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Yanıt Gönder</button>
      </form>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost btn-sm" id="ticketModalClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="notifDetailModal">
  <div class="modal">
    <h3 id="notifDetailTitle">Bildirim</h3>
    <div class="notif-modal-body" id="notifDetailBody"></div>
    <div class="modal-actions" style="margin-top:18px;display:flex;gap:8px;flex-wrap:wrap;">
      <a class="btn btn-primary btn-sm" id="notifDetailGo" href="#" style="display:none;">Git</a>
      <button type="button" class="btn btn-ghost btn-sm" id="notifDetailClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="guildCardModal">
  <div class="modal" style="width:760px;">
    <h3><i class="fa-solid fa-shield"></i> <span id="guildCardTitle">Lonca</span></h3>
    <div id="guildCardBody" style="color:var(--ash);font-size:.88rem;">Yükleniyor…</div>
    <div class="modal-actions">
      <button type="button" class="btn btn-ghost btn-sm" id="guildCardClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="rankDetailModal">
  <div class="modal">
    <h3><i class="fa-solid fa-ranking-star"></i> <span id="rankDetailTitle">Karakter</span></h3>
    <div id="rankDetailBody" class="detail-meta" style="color:var(--ash);font-size:.88rem;"></div>
    <div class="modal-actions" style="margin-top:18px;">
      <button type="button" class="btn btn-ghost btn-sm" id="rankDetailClose">Kapat</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="marketModal" aria-hidden="true">
  <div class="modal market-modal" role="dialog" aria-label="Nesne Market">
    <iframe id="marketFrame" title="Nesne Market" src="about:blank" allow="same-origin"></iframe>
  </div>
</div>

<script>
  const navItems = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.section');
  const initialSection = <?= json_encode($panelSection, JSON_UNESCAPED_UNICODE) ?>;
  const modalData = <?= json_encode($modalPayload, JSON_UNESCAPED_UNICODE) ?>;
  const annModalMap = <?= json_encode($annModalMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const ticketJsonUrl = <?= json_encode(url('/panel/ticket'), JSON_UNESCAPED_UNICODE) ?>;
  const openTicketId = <?= (int) ($_GET['ticket'] ?? 0) ?>;
  const panelIndexUrl = <?= json_encode(url('/panel'), JSON_UNESCAPED_UNICODE) ?>;
  const csrfToken = <?= json_encode(\App\Core\Security::csrfToken(), JSON_UNESCAPED_UNICODE) ?>;
  const notifListUrl = <?= json_encode(url('/bildirimler/json'), JSON_UNESCAPED_UNICODE) ?>;
  const notifReadUrl = <?= json_encode(url('/bildirimler/okundu'), JSON_UNESCAPED_UNICODE) ?>;
  const guildPublicJsonUrl = <?= json_encode(url('/panel/lonca/json'), JSON_UNESCAPED_UNICODE) ?>;
  const marketEmbedUrl = <?= json_encode(url('/nesne-market?embed=1'), JSON_UNESCAPED_UNICODE) ?>;

  const escHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function openGuildCard(id) {
    const modal = document.getElementById('guildCardModal');
    const body = document.getElementById('guildCardBody');
    const title = document.getElementById('guildCardTitle');
    if (!modal || !body) return;
    title.textContent = 'Lonca';
    body.innerHTML = 'Yükleniyor…';
    modal.classList.add('open');
    fetch(guildPublicJsonUrl + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        if (!res.ok || !res.data) {
          body.innerHTML = '<div style="color:var(--blood-light);">' + escHtml(res.error || 'Yüklenemedi') + '</div>';
          return;
        }
        const g = res.data;
        const ws = g.war_stats || {};
        title.textContent = g.name || 'Lonca';
        let html = '<div class="detail-meta">';
        html += '<div class="row"><span class="k">Usta</span><span class="v">' + escHtml(g.master_name || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Seviye</span><span class="v">' + escHtml(String(g.level || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">Üye</span><span class="v">' + escHtml(String(g.member_count || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">Ladder</span><span class="v">' + Number(g.ladder_point || 0).toLocaleString('tr-TR') + '</span></div>';
        html += '<div class="row"><span class="k">Toplam savaş</span><span class="v">' + escHtml(String(ws.wars || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">Galibiyet</span><span class="v">' + escHtml(String(ws.win || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">Beraberlik</span><span class="v">' + escHtml(String(ws.draw || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">Mağlubiyet</span><span class="v">' + escHtml(String(ws.loss || 0)) + '</span></div>';
        html += '<div class="row"><span class="k">G / B / M</span><span class="v">' + escHtml(ws.record_label || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Galibiyet %</span><span class="v">' + escHtml(String(ws.win_rate || 0)) + '%</span></div>';
        html += '</div>';
        const recent = g.recent_wars || [];
        html += '<div class="detail-block"><h4>Son savaşlar</h4>';
        if (!recent.length) {
          html += '<div style="color:var(--ash);">Kayıt yok.</div>';
        } else {
          html += '<table><thead><tr><th>Tarih</th><th>Rakip</th><th>Tür</th><th>Skor</th><th>Ganimet</th><th>Sonuç</th></tr></thead><tbody>';
          recent.forEach(w => {
            const gid = Number(g.id || 0);
            const opponent = Number(w.from_id) === gid ? (w.to_name || '—') : (w.from_name || '—');
            html += '<tr>';
            html += '<td style="white-space:nowrap;font-size:.8rem;">' + escHtml(w.reserved_label || '—') + '</td>';
            html += '<td>' + escHtml(opponent) + '</td>';
            html += '<td>' + escHtml(w.war_type_label || '—') + '</td>';
            html += '<td style="color:var(--gold-light);">' + escHtml(w.score_label || '—') + '</td>';
            html += '<td>' + escHtml(w.warprice_label || '—') + '</td>';
            html += '<td>' + escHtml(w.status_label || '—') + '</td>';
            html += '</tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';
        body.innerHTML = html;
      })
      .catch(() => {
        body.innerHTML = '<div style="color:var(--blood-light);">Detay yüklenemedi.</div>';
      });
  }

  document.querySelectorAll('[data-guild-card]').forEach(btn => {
    btn.addEventListener('click', () => openGuildCard(btn.dataset.guildCard));
  });
  document.getElementById('guildCardClose')?.addEventListener('click', () => document.getElementById('guildCardModal')?.classList.remove('open'));
  document.getElementById('guildCardModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) e.currentTarget.classList.remove('open');
  });

  const rankDetailModal = document.getElementById('rankDetailModal');
  const rankDetailBody = document.getElementById('rankDetailBody');
  const rankDetailTitle = document.getElementById('rankDetailTitle');
  document.querySelectorAll('[data-rank-detail]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!rankDetailModal || !rankDetailBody) return;
      rankDetailTitle.textContent = btn.dataset.rankName || 'Karakter';
      let html = '';
      html += '<div class="row"><span class="k">Karakter</span><span class="v">' + escHtml(btn.dataset.rankName || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Job</span><span class="v">' + escHtml(btn.dataset.rankJob || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Level</span><span class="v">' + escHtml(btn.dataset.rankLevel || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Stamina</span><span class="v">' + escHtml(btn.dataset.rankStamina || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Lonca</span><span class="v">' + escHtml(btn.dataset.rankGuild || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Bayrak</span><span class="v">' + escHtml(btn.dataset.rankEmpire || '—') + '</span></div>';
      rankDetailBody.innerHTML = html;
      rankDetailModal.classList.add('open');
    });
  });
  document.getElementById('rankDetailClose')?.addEventListener('click', () => rankDetailModal?.classList.remove('open'));
  rankDetailModal?.addEventListener('click', (e) => { if (e.target === rankDetailModal) rankDetailModal.classList.remove('open'); });

  (function marketModal() {
    const overlay = document.getElementById('marketModal');
    const frame = document.getElementById('marketFrame');
    if (!overlay || !frame) return;

    const openMarket = () => {
      frame.setAttribute('src', marketEmbedUrl);
      overlay.classList.add('open');
      overlay.setAttribute('aria-hidden', 'false');
      document.getElementById('sidebar')?.classList.remove('open');
    };
    const closeMarket = () => {
      overlay.classList.remove('open');
      overlay.setAttribute('aria-hidden', 'true');
      frame.setAttribute('src', 'about:blank');
    };

    document.querySelectorAll('[data-open-market]').forEach((el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        openMarket();
      });
    });
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeMarket();
    });
    window.addEventListener('message', (ev) => {
      if (ev && ev.data && ev.data.type === 'm2dn-market-close') closeMarket();
    });
  })();

  document.getElementById('userWarTabs')?.querySelectorAll('[data-war-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.getAttribute('data-war-tab');
      const root = document.getElementById('lonca-savaslari');
      if (!root) return;
      root.querySelectorAll('[data-war-tab]').forEach(b => b.classList.toggle('active', b === btn));
      root.querySelectorAll('[data-war-pane]').forEach(p => {
        p.classList.toggle('active', p.getAttribute('data-war-pane') === tab);
      });
    });
  });

  function navigatePanelSection(target) {
    if (!target) return;
    window.location.assign(panelIndexUrl + '?section=' + encodeURIComponent(target));
  }

  function showSection(target) {
    if (!target) return;
    navItems.forEach(n => n.classList.toggle('active', n.dataset.target === target));
    sections.forEach(s => s.classList.toggle('active', s.id === target));
    document.getElementById('sidebar')?.classList.remove('open');
  }

  if (initialSection) showSection(initialSection);

  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      const target = item.dataset.target;
      if (!target) return;
      // Admin panele giden gerçek linklere dokunma
      if (item.getAttribute('href') && item.getAttribute('href') !== '#') {
        const href = item.getAttribute('href') || '';
        if (href.indexOf('/admin') !== -1 || href.indexOf('/cikis') !== -1) return;
      }
      e.preventDefault();
      if (target === initialSection) {
        window.location.reload();
        return;
      }
      navigatePanelSection(target);
    });
  });

  document.querySelectorAll('[data-jump]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      navigatePanelSection(el.dataset.jump);
    });
  });

  document.querySelectorAll('[data-ann-past-toggle]').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.ann-past')?.classList.toggle('open'));
  });
  (function annHistoryModal() {
    const overlay = document.getElementById('annModal');
    const titleEl = document.getElementById('annModalTitle');
    const metaEl = document.getElementById('annModalMeta');
    const bodyEl = document.getElementById('annModalBody');
    const closeBtn = document.getElementById('annModalClose');
    const escHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const openAnn = (id) => {
      const row = annModalMap[String(id)];
      if (!row || !overlay) return;
      titleEl.textContent = row.title || 'Duyuru';
      let meta = '<span class="ann-type">' + escHtml(row.type_name || 'Duyuru') + '</span>';
      meta += '<span>' + escHtml(row.published_label || '') + '</span>';
      if (row.author_login) meta += '<span>· ' + escHtml(row.author_login) + '</span>';
      metaEl.innerHTML = meta;
      bodyEl.innerHTML = row.body || '';
      overlay.classList.add('open');
    };
    const closeAnn = () => overlay?.classList.remove('open');
    document.querySelectorAll('[data-open-ann]').forEach(btn => {
      btn.addEventListener('click', () => openAnn(btn.dataset.openAnn));
    });
    closeBtn?.addEventListener('click', closeAnn);
    overlay?.addEventListener('click', (e) => { if (e.target === overlay) closeAnn(); });
  })();

  document.getElementById('mobileToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
  });

  (function accountModal() {
    const overlay = document.getElementById('accountModal');
    const titleEl = document.getElementById('accountModalTitle');
    const bodyEl = document.getElementById('accountModalBody');
    const closeBtn = document.getElementById('accountModalClose');
    if (!overlay || !bodyEl) return;

    const LOGS_PER_PAGE = 5;
    let currentCharId = null;
    let activityPage = 1;

    function esc(s) {
      return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function activityTableHtml(logs, page) {
      const total = logs.length;
      const pages = Math.max(1, Math.ceil(total / LOGS_PER_PAGE));
      page = Math.min(Math.max(1, page), pages);
      activityPage = page;
      const start = (page - 1) * LOGS_PER_PAGE;
      const slice = logs.slice(start, start + LOGS_PER_PAGE);

      let html = '<table><thead><tr><th>Zaman</th><th>İşlem</th><th>Detay</th><th>Yetkili</th></tr></thead><tbody>';
      slice.forEach(log => {
        let det = log.detail || '';
        if (log.evidence) det += (det ? ' · ' : '') + 'Kanıt: ' + log.evidence;
        html += '<tr><td>' + esc(log.created_label) + '</td><td>' + esc(log.action_label) + '</td><td style="color:var(--ash)">' + esc(det || '—') + '</td><td>' + esc(log.actor_login || '—') + '</td></tr>';
      });
      html += '</tbody></table>';

      if (pages > 1) {
        html += '<div class="modal-pager">';
        html += '<div>' + total + ' kayıt · Sayfa ' + page + ' / ' + pages + '</div>';
        html += '<div class="links">';
        html += '<button type="button" data-act-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>Önceki</button>';
        const from = Math.max(1, page - 2);
        const to = Math.min(pages, page + 2);
        for (let i = from; i <= to; i++) {
          if (i === page) html += '<span class="cur">' + i + '</span>';
          else html += '<button type="button" data-act-page="' + i + '">' + i + '</button>';
        }
        html += '<button type="button" data-act-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + '>Sonraki</button>';
        html += '</div></div>';
      }
      return html;
    }

    function render(charId, page) {
      currentCharId = charId;
      if (page == null) activityPage = 1;
      else activityPage = page;

      const acc = modalData.account || {};
      const ban = modalData.ban;
      const logs = modalData.activity || [];
      const char = charId ? (modalData.characters[String(charId)] || null) : null;
      titleEl.textContent = char ? char.name : (acc.login || 'Hesap Detayı');

      let html = '<div class="detail-meta">';
      if (char) {
        html += '<div class="row"><span class="k">Sınıf</span><span class="v">' + esc(char.job_label) + '</span></div>';
        html += '<div class="row"><span class="k">Seviye</span><span class="v">' + esc(char.level) + ' / ' + esc(modalData.max_level) + '</span></div>';
        html += '<div class="row"><span class="k">Klan</span><span class="v">' + esc(char.guild || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Krallık</span><span class="v">' + esc(char.empire_label || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Yang</span><span class="v">' + Number(char.gold || 0).toLocaleString('tr-TR') + '</span></div>';
        html += '<div class="row"><span class="k">Oyun Süresi</span><span class="v">' + esc(char.playtime_label || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Eş</span><span class="v">' + esc(char.married && char.spouse_name ? char.spouse_name : 'Bekar') + '</span></div>';
      } else {
        html += '<div class="row"><span class="k">Hesap</span><span class="v">' + esc(acc.login || '—') + '</span></div>';
        html += '<div class="row"><span class="k">E-posta</span><span class="v">' + esc(acc.email || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Üyelik</span><span class="v">' + esc(acc.create_label || '—') + '</span></div>';
      }
      html += '<div class="row"><span class="k">Durum</span><span class="v"><span class="badge ' + esc(acc.status_badge || '') + '">' + esc(acc.status_label || '—') + '</span></span></div>';
      html += '</div>';

      if (acc.is_banned && ban) {
        html += '<div class="ban-box"><h4><i class="fa-solid fa-gavel"></i> Hesap Banı</h4><div class="detail-meta" style="margin:0">';
        html += '<div class="row"><span class="k">Ceza</span><span class="v">' + esc(ban.penalty_name) + '</span></div>';
        html += '<div class="row"><span class="k">Sebep</span><span class="v">' + esc(ban.reason) + '</span></div>';
        html += '<div class="row"><span class="k">Süre</span><span class="v">' + esc(ban.days_label) + (ban.remaining_label ? ' · ' + esc(ban.remaining_label) : '') + '</span></div>';
        if (ban.banned_until_label && ban.banned_until_label !== '—') {
          html += '<div class="row"><span class="k">Bitiş</span><span class="v">' + esc(ban.banned_until_label) + '</span></div>';
        }
        html += '<div class="row"><span class="k">Kanıt</span><span class="v">' + esc(ban.evidence || '—') + '</span></div>';
        html += '</div><div style="font-size:.75rem;color:var(--ash);margin-top:8px;">Bu ban hesaba aittir; tüm karakterler için geçerlidir.</div></div>';
      }

      html += '<div class="detail-block"><h4>Hesap Aktiviteleri</h4>';
      if (!logs.length) {
        html += '<div style="color:var(--ash);font-size:.85rem;">Henüz kayıt yok.</div>';
      } else {
        html += activityTableHtml(logs, activityPage);
      }
      html += '</div>';

      bodyEl.innerHTML = html;
      overlay.classList.add('open');

      bodyEl.querySelectorAll('[data-act-page]').forEach(btn => {
        btn.addEventListener('click', () => {
          const p = parseInt(btn.getAttribute('data-act-page') || '1', 10);
          if (!p) return;
          render(currentCharId, p);
        });
      });
    }

    document.querySelectorAll('[data-open-char]').forEach(el => {
      el.addEventListener('click', () => render(el.getAttribute('data-open-char')));
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          render(el.getAttribute('data-open-char'));
        }
      });
    });
    document.querySelectorAll('[data-open-account]').forEach(el => {
      el.addEventListener('click', () => render(null));
    });
    closeBtn?.addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });
  })();

  (function ticketModal() {
    const overlay = document.getElementById('ticketModal');
    const titleEl = document.getElementById('ticketModalTitle');
    const metaEl = document.getElementById('ticketModalMeta');
    const msgsEl = document.getElementById('ticketModalMessages');
    const noteEl = document.getElementById('ticketModalNote');
    const replyWrap = document.getElementById('ticketModalReplyWrap');
    const replyId = document.getElementById('ticketReplyId');
    const replyBody = document.getElementById('ticketReplyBody');
    const closeBtn = document.getElementById('ticketModalClose');
    if (!overlay) return;

    function esc(s) {
      return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function openTicket(id) {
      if (!id) return;
      titleEl.textContent = 'Yükleniyor…';
      metaEl.textContent = '';
      msgsEl.innerHTML = '';
      noteEl.style.display = 'none';
      replyWrap.style.display = 'none';
      overlay.classList.add('open');
      showSection('destek');

      fetch(ticketJsonUrl + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
          if (!res.ok || !res.ticket) {
            titleEl.textContent = 'Ticket';
            msgsEl.innerHTML = '<div style="color:var(--blood-light);">' + esc(res.error || 'Yüklenemedi') + '</div>';
            return;
          }
          const t = res.ticket;
          titleEl.innerHTML = '<i class="fa-solid fa-headset"></i> ' + esc(t.public_code || 'Ticket');
          metaEl.innerHTML = esc(t.subject || '') + ' · ' + esc(t.category_name || '')
            + ' · <span class="badge ' + (t.status_code === 'closed' ? 'closed' : 'pending') + '">' + esc(t.status_label || '') + '</span>';

          let html = '';
          (t.messages || []).forEach(m => {
            html += '<div class="ticket-msg">';
            html += '<div class="who' + (m.is_staff ? ' staff' : '') + '">';
            html += (m.is_staff ? 'Yetkili' : 'Sen') + ' · ' + esc(m.account_login) + ' · ' + esc(m.created_label || '');
            html += '</div><div class="body">' + esc(m.body || '') + '</div>';
            if (m.attachment) {
              html += '<a href="' + esc(m.attachment.path) + '" target="_blank" style="color:var(--gold-light);font-size:.8rem;"><i class="fa-solid fa-paperclip"></i> ' + esc(m.attachment.name) + '</a>';
            }
            html += '</div>';
          });
          msgsEl.innerHTML = html || '<div style="color:var(--ash);">Mesaj yok.</div>';

          if (res.closed) {
            noteEl.style.display = 'block';
            noteEl.textContent = 'Bu ticket kapatıldı. Yeni yanıt yazılamaz.';
            replyWrap.style.display = 'none';
          } else if (res.can_reply) {
            noteEl.style.display = 'none';
            replyWrap.style.display = 'block';
            replyId.value = String(t.id || '');
            if (replyBody) replyBody.value = '';
          } else {
            noteEl.style.display = 'block';
            noteEl.textContent = 'Mevcut ticket içeriği değiştirilemez. Yetkili cevapladıktan sonra buradan yeni yanıt yazabilirsin.';
            replyWrap.style.display = 'none';
          }
        })
        .catch(() => {
          titleEl.textContent = 'Ticket';
          msgsEl.innerHTML = '<div style="color:var(--blood-light);">Bağlantı hatası.</div>';
        });
    }

    document.querySelectorAll('[data-ticket-view]').forEach(btn => {
      btn.addEventListener('click', () => openTicket(btn.getAttribute('data-ticket-view')));
    });
    closeBtn?.addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });

    if (openTicketId > 0) openTicket(openTicketId);
  })();

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

  (function notificationsBell() {
    const btn = document.getElementById('notifBellBtn');
    const icon = document.getElementById('notifBellIcon');
    const drop = document.getElementById('notifDrop');
    const list = document.getElementById('notifList');
    const markAll = document.getElementById('notifMarkAll');
    const detailModal = document.getElementById('notifDetailModal');
    const detailTitle = document.getElementById('notifDetailTitle');
    const detailBody = document.getElementById('notifDetailBody');
    const detailGo = document.getElementById('notifDetailGo');
    const detailClose = document.getElementById('notifDetailClose');
    if (!btn || !drop || !list) return;

    const escLocal = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const setBellState = (unread, hasAny) => {
      btn.classList.toggle('has-unread', unread > 0);
      btn.classList.toggle('empty-bell', !hasAny);
      if (icon) icon.className = hasAny ? 'fa-solid fa-bell' : 'fa-solid fa-bell-slash';
    };

    const render = (items, unread) => {
      setBellState(unread, items.length > 0);
      if (!items.length) {
        list.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-bell-slash" style="display:block;font-size:1.4rem;margin-bottom:8px;"></i>Bildirim yok</div>';
        return;
      }
      list.innerHTML = items.map(it => (
        '<button type="button" class="notif-item' + (it.is_read ? '' : ' unread') + '" data-nid="' + escLocal(String(it.id)) + '" data-title="' + escLocal(it.title) + '" data-body="' + escLocal(it.body || '') + '" data-link="' + escLocal(it.link || '') + '">'
        + '<div class="t">' + escLocal(it.title) + '</div>'
        + (it.body ? '<div class="b">' + escLocal(it.body) + '</div>' : '')
        + '<div class="d">' + escLocal(it.created_label || '') + '</div>'
        + '</button>'
      )).join('');
    };

    const load = () => fetch(notifListUrl, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        if (!res.ok) return;
        render(res.items || [], Number(res.unread || 0));
      })
      .catch(() => {});

    const markRead = (id) => {
      const body = new URLSearchParams();
      body.set('csrf_token', csrfToken);
      if (id) body.set('id', String(id));
      return fetch(notifReadUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrfToken },
        body: body.toString(),
      }).then(r => r.json()).catch(() => null);
    };

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = drop.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) load();
    });
    document.addEventListener('click', () => drop.classList.remove('open'));
    drop.addEventListener('click', (e) => e.stopPropagation());
    markAll?.addEventListener('click', () => { markRead(null).then(() => load()); });

    list.addEventListener('click', (e) => {
      const item = e.target.closest('.notif-item');
      if (!item) return;
      const id = item.dataset.nid;
      const title = item.dataset.title || 'Bildirim';
      const bodyTxt = item.dataset.body || '';
      const link = item.dataset.link || '';
      markRead(id).then(() => load());
      drop.classList.remove('open');
      if (detailTitle) detailTitle.textContent = title;
      if (detailBody) detailBody.textContent = bodyTxt || 'Detay yok.';
      if (detailGo) {
        if (link) { detailGo.href = link; detailGo.style.display = ''; }
        else { detailGo.style.display = 'none'; }
      }
      detailModal?.classList.add('open');
    });
    detailClose?.addEventListener('click', () => detailModal?.classList.remove('open'));
    detailModal?.addEventListener('click', (e) => { if (e.target === detailModal) detailModal.classList.remove('open'); });
    detailGo?.addEventListener('click', () => detailModal?.classList.remove('open'));

    load();
    setInterval(load, 60000);
  })();
</script>
</body>
</html>
