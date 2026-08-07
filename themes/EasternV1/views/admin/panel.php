<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var array $currentServer */
/** @var array $servers */
/** @var string $csrf */
/** @var array|null $authUser */
/** @var array $stats */
/** @var array $players */
/** @var array $guilds */
/** @var array $horses */
/** @var array $banwords */
/** @var array $gms */
/** @var array $rankings */
/** @var array $ipBans */
/** @var array $marriages */
/** @var list<array{key:string,label:string}> $gameLogTables */
/** @var array{table:string,label:string,columns:list<string>,rows:list<array>,error:?string} $gameLogs */
/** @var string $logTab */
/** @var list<array> $guildWars */
/** @var list<array> $guildWarHistory */
/** @var list<array> $guildWarBoard */
/** @var list<array> $communityRules */
/** @var string $privacyTitle */
/** @var string $privacyHtml */
/** @var array $captchaConfig */
/** @var list<array> $marketCategories */
/** @var list<array> $marketItems */
/** @var int $marketItemNextSort */
/** @var string $marketItemQ */
/** @var int $marketItemCat */
/** @var string $panelSection */
/** @var list<array> $penalties */
/** @var list<array> $activeBans */
/** @var list<string> $panelErrors */
/** @var string|null $panelSuccess */
/** @var list<array> $siteDownloads */
/** @var list<array> $siteFeatures */
/** @var list<array> $siteClasses */
/** @var list<array> $siteGallery */
/** @var list<array> $siteFooterLinks */
/** @var list<array> $siteSocials */
/** @var array $siteFooter */
/** @var array $siteRates */
/** @var array $siteChapter */
/** @var string $appVersion */
/** @var array $siteBrand */
/** @var array<string, bool> $permFlags */
/** @var array<string, string> $permFlagDefs */
/** @var list<array> $permissionGroups */
/** @var list<array> $ticketCategories */
/** @var list<array> $ticketStatuses */
/** @var list<array> $ticketFileTypes */
/** @var list<array> $adminTickets */
/** @var array|null $activeTicket */
/** @var string $ticketSearch */
/** @var array{rows:list<array>,total:int,page:int,pages:int,per_page:int,filter:string} $adminLogs */
/** @var list<array> $announcementTypes */
/** @var list<array> $announcementTypesActive */
/** @var list<array> $announcements */
/** @var list<array> $overviewAnnouncements */
/** @var int $openTicketCount */
/** @var list<array> $mailServers */
/** @var array<string, array{label:string, host:string, port:int, encryption:string}> $mailPresets */
/** @var list<array> $mailTemplates */
/** @var list<array> $mailLogs */
/** @var string $mailLogSearch */
/** @var string $mailTab */
/** @var int $authPermission */

$appName = $appName ?? 'M2DN';
$appTagline = $appTagline ?? '';
$appVersion = (string) ($appVersion ?? '2.0.0');
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
$playerAdminsOnly = !empty($players['admins_only']);
$playerPerPage = (int) ($players['per_page'] ?? 10);
$playerPerOptions = is_array($players['per_page_options'] ?? null) ? $players['per_page_options'] : [10, 20, 30, 50, 100];
$guilds = is_array($guilds ?? null) ? $guilds : [];
$guildRows = is_array($guilds['guilds'] ?? null) ? $guilds['guilds'] : [];
$guildTotal = (int) ($guilds['total'] ?? 0);
$guildPage = (int) ($guilds['page'] ?? 1);
$guildPages = (int) ($guilds['pages'] ?? 1);
$guildQ = (string) ($guilds['q'] ?? '');
$guildPerPage = (int) ($guilds['per_page'] ?? 10);
$guildPerOptions = is_array($guilds['per_page_options'] ?? null) ? $guilds['per_page_options'] : [10, 20, 30, 50, 100];
$horses = is_array($horses ?? null) ? $horses : [];
$horseRows = is_array($horses['horses'] ?? null) ? $horses['horses'] : [];
$horseTotal = (int) ($horses['total'] ?? 0);
$horsePage = (int) ($horses['page'] ?? 1);
$horsePages = (int) ($horses['pages'] ?? 1);
$horseQ = (string) ($horses['q'] ?? '');
$horsePerPage = (int) ($horses['per_page'] ?? 10);
$horsePerOptions = is_array($horses['per_page_options'] ?? null) ? $horses['per_page_options'] : [10, 20, 30, 50, 100];
$banwords = is_array($banwords ?? null) ? $banwords : [];
$banwordRows = is_array($banwords['words'] ?? null) ? $banwords['words'] : [];
$banwordTotal = (int) ($banwords['total'] ?? 0);
$banwordPage = (int) ($banwords['page'] ?? 1);
$banwordPages = (int) ($banwords['pages'] ?? 1);
$banwordQ = (string) ($banwords['q'] ?? '');
$banwordPerPage = (int) ($banwords['per_page'] ?? 20);
$banwordPerOptions = is_array($banwords['per_page_options'] ?? null) ? $banwords['per_page_options'] : [10, 20, 30, 50, 100];
$gms = is_array($gms ?? null) ? $gms : [];
$gmRows = is_array($gms['gms'] ?? null) ? $gms['gms'] : [];
$gmTotal = (int) ($gms['total'] ?? 0);
$gmPage = (int) ($gms['page'] ?? 1);
$gmPages = (int) ($gms['pages'] ?? 1);
$gmQ = (string) ($gms['q'] ?? '');
$gmPerPage = (int) ($gms['per_page'] ?? 20);
$gmPerOptions = is_array($gms['per_page_options'] ?? null) ? $gms['per_page_options'] : [10, 20, 30, 50, 100];
$gmAuthorities = is_array($gms['authorities'] ?? null) ? $gms['authorities'] : \App\Services\AdminGmService::AUTHORITY_LABELS;
$rankings = is_array($rankings ?? null) ? $rankings : [];
$rankRows = is_array($rankings['players'] ?? null) ? $rankings['players'] : [];
$rankTotal = (int) ($rankings['total'] ?? 0);
$rankPage = (int) ($rankings['page'] ?? 1);
$rankPages = (int) ($rankings['pages'] ?? 1);
$rankQ = (string) ($rankings['q'] ?? '');
$rankPerPage = (int) ($rankings['per_page'] ?? 10);
$rankPerOptions = is_array($rankings['per_page_options'] ?? null) ? $rankings['per_page_options'] : [10, 20, 30, 50, 100];
$ipBans = is_array($ipBans ?? null) ? $ipBans : [];
$ipBanRows = is_array($ipBans['bans'] ?? null) ? $ipBans['bans'] : [];
$ipBanTotal = (int) ($ipBans['total'] ?? 0);
$ipBanPage = (int) ($ipBans['page'] ?? 1);
$ipBanPages = (int) ($ipBans['pages'] ?? 1);
$ipBanQ = (string) ($ipBans['q'] ?? '');
$ipBanPerPage = (int) ($ipBans['per_page'] ?? 20);
$ipBanPerOptions = is_array($ipBans['per_page_options'] ?? null) ? $ipBans['per_page_options'] : [10, 20, 30, 50, 100];
$marriages = is_array($marriages ?? null) ? $marriages : [];
$marriageRows = is_array($marriages['rows'] ?? null) ? $marriages['rows'] : [];
$marriageTotal = (int) ($marriages['total'] ?? 0);
$marriagePage = (int) ($marriages['page'] ?? 1);
$marriagePages = (int) ($marriages['pages'] ?? 1);
$marriageQ = (string) ($marriages['q'] ?? '');
$marriagePerPage = (int) ($marriages['per_page'] ?? 20);
$marriagePerOptions = is_array($marriages['per_page_options'] ?? null) ? $marriages['per_page_options'] : [10, 20, 30, 50, 100];
$gameLogTables = is_array($gameLogTables ?? null) ? $gameLogTables : [];
$gameLogs = is_array($gameLogs ?? null) ? $gameLogs : ['table' => '', 'label' => '', 'columns' => [], 'rows' => [], 'error' => null];
$gameLogColumns = is_array($gameLogs['columns'] ?? null) ? $gameLogs['columns'] : [];
$gameLogRows = is_array($gameLogs['rows'] ?? null) ? $gameLogs['rows'] : [];
$logTab = in_array(($logTab ?? 'yonetici'), ['yonetici', 'oyun'], true) ? (string) $logTab : 'yonetici';
$guildWars = is_array($guildWars ?? null) ? $guildWars : [];
$penalties = is_array($penalties ?? null) ? $penalties : [];
$activeBans = is_array($activeBans ?? null) ? $activeBans : [];
$panelErrors = is_array($panelErrors ?? null) ? $panelErrors : [];
$panelSuccess = is_string($panelSuccess ?? null) ? $panelSuccess : null;
$siteDownloads = is_array($siteDownloads ?? null) ? $siteDownloads : [];
$siteFeatures = is_array($siteFeatures ?? null) ? $siteFeatures : [];
$siteClasses = is_array($siteClasses ?? null) ? $siteClasses : [];
$siteGallery = is_array($siteGallery ?? null) ? $siteGallery : [];
$siteFooterLinks = is_array($siteFooterLinks ?? null) ? $siteFooterLinks : [];
$siteSocials = is_array($siteSocials ?? null) ? $siteSocials : [];
$siteFooter = is_array($siteFooter ?? null) ? $siteFooter : [];
$siteRates = is_array($siteRates ?? null) ? $siteRates : [];
$siteChapter = is_array($siteChapter ?? null) ? $siteChapter : [];
$permFlags = is_array($permFlags ?? null) ? $permFlags : [];
$permFlagDefs = is_array($permFlagDefs ?? null) ? $permFlagDefs : [];
$permissionGroups = is_array($permissionGroups ?? null) ? $permissionGroups : [];
$ticketCategories = is_array($ticketCategories ?? null) ? $ticketCategories : [];
$ticketStatuses = is_array($ticketStatuses ?? null) ? $ticketStatuses : [];
$ticketFileTypes = is_array($ticketFileTypes ?? null) ? $ticketFileTypes : [];
$adminTickets = is_array($adminTickets ?? null) ? $adminTickets : [];
$activeTicket = is_array($activeTicket ?? null) ? $activeTicket : null;
$ticketSearch = is_string($ticketSearch ?? null) ? $ticketSearch : '';
$adminLogs = is_array($adminLogs ?? null) ? $adminLogs : ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 10, 'filter' => ''];
$adminLogRows = is_array($adminLogs['rows'] ?? null) ? $adminLogs['rows'] : [];
$adminLogTotal = (int) ($adminLogs['total'] ?? 0);
$adminLogPage = (int) ($adminLogs['page'] ?? 1);
$adminLogPages = max(1, (int) ($adminLogs['pages'] ?? 1));
$adminLogFilter = (string) ($adminLogs['filter'] ?? '');
$announcementTypes = is_array($announcementTypes ?? null) ? $announcementTypes : [];
$announcementTypesActive = is_array($announcementTypesActive ?? null) ? $announcementTypesActive : [];
$announcements = is_array($announcements ?? null) ? $announcements : [];
$overviewAnnouncements = is_array($overviewAnnouncements ?? null) ? $overviewAnnouncements : [];
$latestOverviewAnn = $overviewAnnouncements[0] ?? null;
$pastOverviewAnn = array_slice($overviewAnnouncements, 1);
$openTicketCount = (int) ($openTicketCount ?? 0);
if (!isset($mailServers) || !is_array($mailServers)) {
    $mailServers = [];
}
if (!isset($mailPresets) || !is_array($mailPresets)) {
    $mailPresets = [];
}
if (!isset($mailTemplates) || !is_array($mailTemplates)) {
    $mailTemplates = [];
}
if (!isset($mailLogs) || !is_array($mailLogs)) {
    $mailLogs = [];
}
$mailLogSearch = isset($mailLogSearch) && is_string($mailLogSearch) ? $mailLogSearch : '';
$mailTabRaw = isset($mailTab) && is_string($mailTab) ? $mailTab : '';
$mailTab = in_array($mailTabRaw, ['sunucu', 'bildirimler', 'test', 'loglar'], true) ? $mailTabRaw : 'sunucu';
$authPermission = (int) (isset($authPermission) ? $authPermission : ($authUser['permission'] ?? 0));if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandLogo = (string) ($siteBrand['logo_url'] ?? asset('img/logo-nav.svg'));
$brandAdminSize = (int) ($siteBrand['admin_size'] ?? 36);
$can = static function (string $flag) use ($permFlags): bool {
    return !empty($permFlags[$flag]);
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Yönetim Paneli | <?= e($appName) ?></title>
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

  .layout{display:grid; grid-template-columns:var(--sidebar-w) 1fr; min-height:100vh;}

  /* ===== SIDEBAR ===== */
  .sidebar{background:var(--obsidian-2); border-right:1px solid var(--line); padding:26px 18px; display:flex; flex-direction:column; gap:6px; position:sticky; top:0; height:100vh; overflow-y:auto;
    scrollbar-width:thin;
    scrollbar-color:rgba(201,151,74,.45) rgba(11,9,6,.55);
  }
  .sidebar::-webkit-scrollbar{width:8px;}
  .sidebar::-webkit-scrollbar-track{
    background:rgba(11,9,6,.55);
    border-left:1px solid rgba(201,151,74,.12);
  }
  .sidebar::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg, rgba(201,151,74,.55), rgba(143,28,41,.45));
    border:2px solid rgba(11,9,6,.4);
    border-radius:6px;
  }
  .sidebar::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg, rgba(236,205,142,.7), rgba(197,51,71,.55));
  }
  .sidebar-brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; font-size:1.15rem; letter-spacing:.06em; color:var(--gold-light); padding:0 10px 10px; margin-bottom:6px; border-bottom:1px solid var(--line); text-decoration:none;}
  .sidebar-brand img{width:<?= $brandAdminSize ?>px; height:<?= $brandAdminSize ?>px; object-fit:contain; flex-shrink:0; display:block;}
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
  .search-wrap{position:relative; min-width:260px;}
  .search-box{display:flex; align-items:center; gap:8px; background:var(--obsidian-2); border:1px solid var(--line); padding:9px 14px; font-size:.82rem; color:var(--ash); width:100%;}
  .search-box input{background:none; border:none; outline:none; color:var(--parchment); font-size:.82rem; width:100%;}
  .search-drop{display:none; position:absolute; top:calc(100% + 6px); left:0; right:0; background:var(--obsidian-2); border:1px solid var(--line); z-index:80; max-height:340px; overflow:auto; box-shadow:0 16px 40px rgba(0,0,0,.45);}
  .search-drop.open{display:block;}
  .search-drop .empty{padding:12px 14px; font-size:.8rem; color:var(--ash);}
  .search-drop button{
    display:flex; flex-direction:column; align-items:flex-start; gap:3px; width:100%;
    padding:11px 14px; background:none; border:none; border-bottom:1px solid rgba(201,151,74,.08);
    color:inherit; cursor:pointer; text-align:left; font:inherit;
  }
  .search-drop button:last-child{border-bottom:none;}
  .search-drop button:hover{background:rgba(201,151,74,.08);}
  .search-drop .s-login{font-size:.88rem; color:var(--gold-light); font-weight:600;}
  .search-drop .s-meta{font-size:.72rem; color:var(--ash); display:flex; flex-wrap:wrap; gap:6px 10px;}
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
  .mail-tabs{display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;}
  .mail-tabs button{padding:8px 14px; background:var(--obsidian); border:1px solid var(--line); color:var(--ash); cursor:pointer; font:inherit; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;}
  .mail-tabs button.active{color:var(--gold-light); border-color:rgba(201,151,74,.45); background:rgba(201,151,74,.08);}
  .mail-pane{display:none;}
  .mail-pane.active{display:block;}
  .guild-tabs{display:flex; gap:8px; flex-wrap:wrap; margin:4px 0 14px;}
  .guild-tabs button{padding:8px 14px; background:var(--obsidian); border:1px solid var(--line); color:var(--ash); cursor:pointer; font:inherit; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;}
  .guild-tabs button.active{color:var(--gold-light); border-color:rgba(201,151,74,.45); background:rgba(201,151,74,.08);}
  .guild-pane{display:none; max-height:52vh; overflow:auto;}
  .guild-pane.active{display:block;}
  .guild-comment{border:1px solid rgba(201,151,74,.12); padding:10px 12px; margin-bottom:10px; background:rgba(11,9,6,.35);}
  .guild-comment .meta{display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; font-size:.75rem; color:var(--ash); margin-bottom:6px;}
  .guild-comment .badge-notice{display:inline-flex; padding:2px 8px; font-size:.68rem; background:rgba(143,28,41,.2); color:#e8a0a8;}
  .guild-comment .body{color:var(--parchment); font-size:.88rem; white-space:pre-wrap; word-break:break-word;}
  .html-editor{min-height:160px; padding:12px; background:var(--obsidian); border:1px solid var(--line); color:var(--parchment); outline:none;}
  .html-toolbar{display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;}
  .html-toolbar button{padding:6px 10px; background:var(--obsidian); border:1px solid var(--line); color:var(--gold-light); cursor:pointer; font-size:.75rem;}
  .mail-tpl-wrap{border:1px solid var(--line); background:var(--obsidian); margin-top:6px;}
  .mail-tpl-toolbar{display:flex; flex-wrap:wrap; gap:6px; padding:10px; background:var(--obsidian-2); border-bottom:1px solid var(--line); align-items:center;}
  .mail-tpl-toolbar button,.mail-tpl-toolbar label.mail-tool{
    min-width:32px; height:32px; padding:0 8px; display:inline-flex; align-items:center; justify-content:center;
    background:var(--obsidian); border:1px solid var(--line); color:var(--gold-light); cursor:pointer; font:inherit; font-size:.78rem;
  }
  .mail-tpl-toolbar button:hover,.mail-tpl-toolbar label.mail-tool:hover{background:rgba(201,151,74,.14); border-color:rgba(201,151,74,.4);}
  .mail-tpl-toolbar button.active-mode{background:rgba(201,151,74,.18); border-color:rgba(201,151,74,.45);}
  .mail-tpl-toolbar .sep{width:1px; align-self:stretch; background:var(--line); margin:0 2px;}
  .mail-tpl-toolbar input[type="color"]{width:28px; height:28px; padding:0; border:1px solid var(--line); background:transparent; cursor:pointer;}
  .mail-tpl-toolbar select.mail-var{
    height:32px; background:var(--obsidian); border:1px solid var(--line); color:var(--gold-light);
    font:inherit; font-size:.72rem; padding:0 8px; max-width:160px;
  }
  .mail-tpl-editor{
    min-height:200px; max-height:420px; overflow:auto; padding:14px; color:var(--parchment); outline:none; line-height:1.55;
  }
  .mail-tpl-editor:empty:before{content:attr(data-placeholder); color:var(--ash);}
  .mail-tpl-editor.html-mode{display:none;}
  .mail-tpl-editor a{color:var(--gold-light);}
  .mail-tpl-editor table{width:100%; border-collapse:collapse; margin:.5em 0;}
  .mail-tpl-editor th,.mail-tpl-editor td{border:1px solid var(--line); padding:6px 8px;}
  .mail-tpl-source{
    display:none; width:100%; min-height:220px; max-height:420px; border:none; border-top:1px solid var(--line);
    background:#0e0c09; color:#d6c8a8; font-family:Consolas,Monaco,monospace; font-size:.82rem; line-height:1.45; padding:14px; resize:vertical;
  }
  .mail-tpl-source.open{display:block;}
  .mail-preview-frame{width:100%; min-height:320px; max-height:60vh; border:1px solid var(--line); background:#fff; border-radius:0;}
  .mail-preview-meta{font-size:.82rem; color:var(--ash); margin-bottom:12px; padding:10px 12px; background:var(--obsidian); border:1px solid var(--line);}
  .mail-preview-meta b{color:var(--gold-light); font-weight:600;}
  .detail-ops{margin-top:18px; padding-top:14px; border-top:1px solid var(--line);}
  .detail-ops > h4{margin:0 0 14px; font-size:.9rem; color:var(--parchment);}
  .detail-ops .ops-block{
    border:1px solid var(--line);
    background:rgba(11,9,6,.35);
    padding:14px 16px;
    margin-bottom:12px;
  }
  .detail-ops .ops-block:last-child{margin-bottom:0;}
  .detail-ops .ops-block.ops-block-denied{
    border-color:rgba(197,51,71,.35);
    background:rgba(143,28,41,.12);
  }
  .detail-ops .ops-block.ops-block-denied .ops-title{color:var(--blood-light);}
  .detail-ops .ops-block .ops-title{
    font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;
    color:var(--ash); margin:0 0 10px; font-weight:600;
  }
  .detail-ops .ops-block .form-row{margin-bottom:10px;}
  .detail-ops .ops-block .form-row:last-of-type{margin-bottom:12px;}
  .detail-ops .ops-block form{display:flex; flex-direction:column; gap:0; align-items:stretch;}
  .detail-ops .ops-block .btn{align-self:flex-start;}
  .detail-ops .ops-block .ops-inline{
    display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;
  }
  .detail-ops .ops-block .ops-inline .form-row{margin:0; flex:1; min-width:160px;}
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
  .mail-server-actions{flex-wrap:wrap; justify-content:flex-end; max-width:140px;}
  .mail-server-actions form{display:inline-flex; margin:0;}
  .mail-server-actions .btn-sm{width:30px; min-width:30px; padding:0; font-size:.75rem;}

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
  .filters .filter-check{
    display:inline-flex; align-items:center; gap:8px;
    font-size:.8rem; color:var(--ash); cursor:pointer; user-select:none;
    padding:8px 10px; border:1px solid var(--line); background:var(--obsidian);
  }
  .filters .filter-check input{width:auto; margin:0; accent-color:var(--gold); cursor:pointer;}
  .filters .filter-check:has(input:checked){border-color:rgba(201,151,74,.45); color:var(--gold-light);}
  .panel-select{
    appearance:none; -webkit-appearance:none; -moz-appearance:none;
    background-color:var(--obsidian);
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23c9974a' d='M1.4.6 6 5.2 10.6.6 12 2 6 8 0 2z'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 10px center;
    border:1px solid var(--line);
    padding:8px 30px 8px 12px;
    color:var(--parchment);
    font:inherit;
    font-size:.8rem;
    outline:none;
    cursor:pointer;
    min-width:150px;
    transition:border-color .2s;
  }
  .panel-select:hover,.panel-select:focus{border-color:var(--gold);}
  .panel-select option{background:var(--obsidian-2); color:var(--parchment);}
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
  .modal.modal-lg,
  .modal.modal-lg #detailBody{
    scrollbar-width:thin;
    scrollbar-color:rgba(201,151,74,.45) rgba(11,9,6,.55);
  }
  .modal.modal-lg::-webkit-scrollbar,
  .modal.modal-lg #detailBody::-webkit-scrollbar{width:10px; height:10px;}
  .modal.modal-lg::-webkit-scrollbar-track,
  .modal.modal-lg #detailBody::-webkit-scrollbar-track{
    background:rgba(11,9,6,.55);
    border-left:1px solid rgba(201,151,74,.12);
  }
  .modal.modal-lg::-webkit-scrollbar-thumb,
  .modal.modal-lg #detailBody::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg, rgba(201,151,74,.55), rgba(143,28,41,.45));
    border:2px solid rgba(11,9,6,.4);
    border-radius:6px;
  }
  .modal.modal-lg::-webkit-scrollbar-thumb:hover,
  .modal.modal-lg #detailBody::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg, rgba(236,205,142,.7), rgba(197,51,71,.55));
  }
  .modal h3{font-size:1.1rem; color:var(--gold-light); margin-bottom:12px;}
  .modal p{font-size:.85rem; color:var(--ash); margin-bottom:20px; line-height:1.6;}
  .modal .modal-actions{display:flex; gap:12px; justify-content:flex-end;}
  .form-row select{width:100%; background:var(--obsidian); border:1px solid var(--line); padding:12px 14px; color:var(--parchment); font-size:.88rem; outline:none; font-family:inherit;}
  .icon-pick-wrap{margin-top:10px;}
  .icon-pick-toggle{display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--ash);cursor:pointer;text-transform:none;letter-spacing:0;margin:0;}
  .icon-pick-toggle input{width:auto;}
  .icon-pick-preview{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid var(--line);background:var(--obsidian);color:var(--gold-light);font-size:1.1rem;flex-shrink:0;}
  .icon-pick-row{display:flex;gap:10px;align-items:center;}
  .icon-pick-row input{flex:1;}
  .icon-pick-grid{display:none;grid-template-columns:repeat(8,1fr);gap:6px;max-height:220px;overflow:auto;padding:10px;margin-top:10px;border:1px solid var(--line);background:rgba(11,9,6,.35);}
  .icon-pick-grid.open{display:grid;}
  .icon-pick-grid button{aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:var(--obsidian);border:1px solid var(--line);color:var(--ash);cursor:pointer;font-size:1rem;padding:0;}
  .icon-pick-grid button:hover{color:var(--gold-light);border-color:rgba(201,151,74,.45);}
  .icon-pick-grid button.active{color:var(--gold-light);border-color:var(--gold);background:rgba(201,151,74,.12);}
  .icon-pick-search{margin-top:8px;display:none;}
  .icon-pick-search.open{display:block;}
  .wiki-icon-pick{margin-bottom:4px;}
  .wiki-icon-pick .icon-pick-grid{grid-template-columns:repeat(10,minmax(0,1fr));}
  .form-row select:focus{border-color:var(--gold);}
  .flags-table{width:100%; border-collapse:collapse; margin-top:8px;}
  .flags-table th, .flags-table td{padding:10px 12px; border-bottom:1px solid var(--line); text-align:left; vertical-align:middle;}
  .flags-table th{font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--ash); font-weight:600;}
  .flags-table td{font-size:.85rem; color:var(--parchment);}
  .flags-table .flag-check{width:44px; text-align:center;}
  .flags-table input[type="checkbox"]{width:16px; height:16px; margin:0; accent-color:var(--gold); cursor:pointer; flex:none;}
  .flags-table label.flag-row{display:contents; cursor:pointer;}
  .flags-table tr:hover td{background:rgba(201,151,74,.04);}
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
  .ann-body ul,.ann-body ol{margin:0 0 .75em 1.25em;}
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
  .modal.modal-ann{width:640px; max-width:96vw;}
  .modal.modal-ann .ann-modal-meta{display:flex; flex-wrap:wrap; gap:8px 12px; margin-bottom:12px; font-size:.75rem; color:var(--ash);}
  .modal.modal-ann .ann-modal-meta .ann-type{
    padding:3px 9px; border:1px solid rgba(201,151,74,.28); background:rgba(201,151,74,.1);
    color:var(--gold-light); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em;
  }
  .modal.modal-ann .ann-modal-body{font-size:.9rem; line-height:1.7; color:var(--parchment); max-height:50vh; overflow:auto;}
  .modal.modal-ann .ann-modal-body p{margin:0 0 .7em;}
  .modal.modal-ann .ann-modal-body a{color:var(--gold-light);}
  #annEditorWrap{border:1px solid var(--line); background:var(--obsidian);}
  .ann-toolbar{display:flex; flex-wrap:wrap; gap:6px; padding:10px; background:var(--obsidian-2); border-bottom:1px solid var(--line);}
  .ann-toolbar button,.ann-toolbar label.ann-tool{
    display:inline-flex; align-items:center; justify-content:center; gap:4px;
    min-width:32px; height:32px; padding:0 8px; border:1px solid var(--line);
    background:var(--obsidian); color:var(--gold-light); font-size:.78rem; cursor:pointer;
  }
  .ann-toolbar button:hover,.ann-toolbar label.ann-tool:hover{background:rgba(201,151,74,.14); border-color:rgba(201,151,74,.4);}
  .ann-toolbar button b{font-weight:800;}
  .ann-toolbar button > i:not([class]){font-style:italic;}
  .ann-toolbar button u{text-decoration:underline;}
  .ann-toolbar button s{text-decoration:line-through;}
  .ann-toolbar .fa-solid{font-style:normal;}
  .ann-toolbar input[type="color"]{width:28px; height:28px; padding:0; border:1px solid var(--line); background:transparent; cursor:pointer;}
  .ann-toolbar .sep{width:1px; align-self:stretch; background:var(--line); margin:0 2px;}
  #annEditor{
    min-height:240px; max-height:480px; overflow:auto; padding:14px 16px;
    color:var(--parchment); font-size:.9rem; line-height:1.65; outline:none; background:var(--obsidian);
  }
  #annEditor:empty:before{content:attr(data-placeholder); color:var(--ash);}
  #annEditor table{width:100%; border-collapse:collapse; margin:.5em 0;}
  #annEditor th,#annEditor td{border:1px solid var(--line); padding:6px 8px;}
  #annEditor a{color:var(--gold-light);}
  #annHtmlPanel{display:none; width:100%; min-height:200px; border:none; border-top:1px solid var(--line);
    background:#120e08; color:var(--parchment); padding:12px; font-family:ui-monospace,Consolas,monospace; font-size:.8rem; resize:vertical;}
  #annHtmlPanel.open{display:block;}
  #annEditor.html-mode{display:none;}
  #privacyEditorWrap{border:1px solid var(--line); background:var(--obsidian); margin-top:8px;}
  #privacyEditor{
    min-height:380px; max-height:640px; overflow:auto; padding:14px 16px;
    color:var(--parchment); font-size:.9rem; line-height:1.65; outline:none; background:var(--obsidian);
  }
  #privacyEditor:empty:before{content:attr(data-placeholder); color:var(--ash);}
  #privacyEditor table{width:100%; border-collapse:collapse; margin:.5em 0;}
  #privacyEditor th,#privacyEditor td{border:1px solid var(--line); padding:6px 8px;}
  #privacyEditor a{color:var(--gold-light);}
  #privacyEditor h2{font-family:var(--font-display); font-size:1rem; color:var(--gold-light); margin:1em 0 .4em;}
  #privacyEditor h3{font-size:.92rem; color:var(--parchment); margin:.85em 0 .35em;}
  #privacyHtmlPanel{display:none; width:100%; min-height:360px; border:none; border-top:1px solid var(--line);
    background:#120e08; color:var(--parchment); padding:12px; font-family:ui-monospace,Consolas,monospace; font-size:.8rem; resize:vertical;}
  #privacyHtmlPanel.open{display:block;}
  #privacyEditor.html-mode{display:none;}
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
      <img src="<?= e($brandIcon) ?>" alt="<?= e($appName) ?>">
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
    <?php if ($can('menu_oyuncular')): ?>
    <a class="nav-item<?= $panelSection === 'oyuncular' ? ' active' : '' ?>" data-target="oyuncular"><i class="fa-solid fa-users"></i> Oyuncu Yönetimi</a>
    <a class="nav-item<?= $panelSection === 'evlilikler' ? ' active' : '' ?>" data-target="evlilikler"><i class="fa-solid fa-heart"></i> Evlilikler</a>
    <?php endif; ?>
    <?php if ($can('menu_siralamalar')): ?>
    <a class="nav-item<?= $panelSection === 'siralamalar' ? ' active' : '' ?>" data-target="siralamalar"><i class="fa-solid fa-ranking-star"></i> Oyuncu Sıralaması</a>
    <?php endif; ?>
    <?php if ($can('menu_binek')): ?>
    <a class="nav-item<?= $panelSection === 'binek' ? ' active' : '' ?>" data-target="binek"><i class="fa-solid fa-horse"></i> Binek Yönetimi</a>
    <?php endif; ?>
    <?php if ($can('menu_gm')): ?>
    <a class="nav-item<?= $panelSection === 'gm' ? ' active' : '' ?>" data-target="gm"><i class="fa-solid fa-user-shield"></i> GM Yönetimi</a>
    <?php endif; ?>
    <?php if ($can('menu_ip_ban')): ?>
    <a class="nav-item<?= $panelSection === 'ip-ban' ? ' active' : '' ?>" data-target="ip-ban"><i class="fa-solid fa-ban"></i> IP Ban</a>
    <?php endif; ?>
    <?php if ($can('menu_loncalar')): ?>
    <a class="nav-item<?= $panelSection === 'loncalar' ? ' active' : '' ?>" data-target="loncalar"><i class="fa-solid fa-shield"></i> Loncalar</a>
    <?php endif; ?>
    <?php if ($can('menu_lonca_savaslari')): ?>
    <a class="nav-item<?= $panelSection === 'lonca-savaslari' ? ' active' : '' ?>" data-target="lonca-savaslari"><i class="fa-solid fa-crosshairs"></i> Lonca Savaşı</a>
    <?php endif; ?>
    <?php if ($can('menu_banlar')): ?>
    <a class="nav-item<?= $panelSection === 'banlar' ? ' active' : '' ?>" data-target="banlar"><i class="fa-solid fa-gavel"></i> Ban / Mute</a>
    <?php endif; ?>

    <div class="nav-group-label">İçerik</div>
    <?php if ($can('menu_duyurular') || $can('announcements')): ?>
    <a class="nav-item<?= $panelSection === 'duyurular' ? ' active' : '' ?>" data-target="duyurular"><i class="fa-solid fa-bullhorn"></i> Duyurular</a>
    <?php endif; ?>

    <?php if ($can('menu_destekler')): ?>
    <a class="nav-item<?= $panelSection === 'destekler' ? ' active' : '' ?>" data-target="destekler"><i class="fa-solid fa-headset"></i> Destek Talepleri</a>
    <?php endif; ?>

    <div class="nav-group-label">Sunucu işlemleri</div>
    <a class="nav-item" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-user"></i> Oyuncu Paneli</a>
    <?php if ($can('menu_sunucu')): ?>
    <a class="nav-item<?= $panelSection === 'sunucu' ? ' active' : '' ?>" data-target="sunucu"><i class="fa-solid fa-server"></i> Sunucu Yönetimi</a>
    <?php endif; ?>
    <?php if ($can('menu_yasakli_kelimeler')): ?>
    <a class="nav-item<?= $panelSection === 'yasakli-kelimeler' ? ' active' : '' ?>" data-target="yasakli-kelimeler"><i class="fa-solid fa-comment-slash"></i> Yasaklı Kelimeler</a>
    <?php endif; ?>
    <?php if ($can('menu_loglar')): ?>
    <a class="nav-item<?= $panelSection === 'loglar' ? ' active' : '' ?>" data-target="loglar"><i class="fa-solid fa-scroll"></i> Loglar</a>
    <?php endif; ?>

    <?php if ($can('menu_nesne_market') || $can('site_settings')): ?>
    <div class="nav-group-label">Nesne Market</div>
    <a class="nav-item<?= $panelSection === 'nesne-market-kategoriler' ? ' active' : '' ?>" data-target="nesne-market-kategoriler"><i class="fa-solid fa-layer-group"></i> Kategoriler</a>
    <a class="nav-item<?= $panelSection === 'nesne-market-urunler' ? ' active' : '' ?>" data-target="nesne-market-urunler"><i class="fa-solid fa-box-open"></i> Ürünler</a>
    <a class="nav-item<?= $panelSection === 'nesne-market-satis-loglari' ? ' active' : '' ?>" data-target="nesne-market-satis-loglari"><i class="fa-solid fa-receipt"></i> Satış Logları</a>
    <a class="nav-item<?= $panelSection === 'nesne-market-kuponlar' ? ' active' : '' ?>" data-target="nesne-market-kuponlar"><i class="fa-solid fa-ticket"></i> Market Kuponları</a>
    <?php endif; ?>

    <?php if ($can('menu_wiki') || $can('wiki_manage')): ?>
    <div class="nav-group-label">Wiki</div>
    <a class="nav-item<?= $panelSection === 'wiki-yonetim' ? ' active' : '' ?>" data-target="wiki-yonetim"><i class="fa-solid fa-book-open"></i> Wiki Yönetimi</a>
    <?php endif; ?>

    <?php if ($can('site_settings')): ?>
    <div class="nav-group-label">Ayarlar</div>
    <a class="nav-item<?= $panelSection === 'patch-linkleri' ? ' active' : '' ?>" data-target="patch-linkleri"><i class="fa-solid fa-download"></i> Patch Linkleri</a>
    <a class="nav-item<?= $panelSection === 'ozellikler-ayarlari' ? ' active' : '' ?>" data-target="ozellikler-ayarlari"><i class="fa-solid fa-star"></i> Özellikler</a>
    <a class="nav-item<?= $panelSection === 'siniflar-ayarlari' ? ' active' : '' ?>" data-target="siniflar-ayarlari"><i class="fa-solid fa-khanda"></i> Sınıflar</a>
    <a class="nav-item<?= $panelSection === 'oranlar-ayarlari' ? ' active' : '' ?>" data-target="oranlar-ayarlari"><i class="fa-solid fa-percent"></i> Sunucu Oranları</a>
    <a class="nav-item<?= $panelSection === 'siradaki-bolum' ? ' active' : '' ?>" data-target="siradaki-bolum"><i class="fa-solid fa-clock"></i> Sıradaki Bölüm</a>
    <a class="nav-item<?= $panelSection === 'galeri-ayarlari' ? ' active' : '' ?>" data-target="galeri-ayarlari"><i class="fa-solid fa-images"></i> Galeri</a>
    <a class="nav-item<?= $panelSection === 'logo-ayarlari' ? ' active' : '' ?>" data-target="logo-ayarlari"><i class="fa-solid fa-image"></i> Logo</a>
    <a class="nav-item<?= $panelSection === 'captcha-ayarlari' ? ' active' : '' ?>" data-target="captcha-ayarlari"><i class="fa-solid fa-robot"></i> Captcha</a>
    <a class="nav-item<?= $panelSection === 'mail-ayarlari' ? ' active' : '' ?>" data-target="mail-ayarlari"><i class="fa-solid fa-envelope"></i> Mail</a>
    <a class="nav-item<?= $panelSection === 'footer-ayarlari' ? ' active' : '' ?>" data-target="footer-ayarlari"><i class="fa-solid fa-shoe-prints"></i> Footer / Border</a>
    <a class="nav-item<?= $panelSection === 'ceza-ayarlari' ? ' active' : '' ?>" data-target="ceza-ayarlari"><i class="fa-solid fa-scale-balanced"></i> Ceza Ayarları</a>
    <a class="nav-item<?= $panelSection === 'kurallar-ayarlari' ? ' active' : '' ?>" data-target="kurallar-ayarlari"><i class="fa-solid fa-book"></i> Topluluk Kuralları</a>
    <a class="nav-item<?= $panelSection === 'gizlilik-ayarlari' ? ' active' : '' ?>" data-target="gizlilik-ayarlari"><i class="fa-solid fa-file-contract"></i> Gizlilik / KVKK</a>
    <a class="nav-item<?= $panelSection === 'yetki-gruplari' ? ' active' : '' ?>" data-target="yetki-gruplari"><i class="fa-solid fa-shield-halved"></i> Yetki Grupları</a>
    <a class="nav-item<?= $panelSection === 'ticket-ayarlari' ? ' active' : '' ?>" data-target="ticket-ayarlari"><i class="fa-solid fa-ticket"></i> Ticket Ayarları</a>
    <a class="nav-item<?= $panelSection === 'duyuru-turleri' ? ' active' : '' ?>" data-target="duyuru-turleri"><i class="fa-solid fa-tags"></i> Duyuru Türleri</a>
    <?php endif; ?>

    <div class="sidebar-foot">
      <div class="sidebar-char">
        <div class="avatar-ring"><i class="fa-solid fa-crown"></i></div>
        <div>
          <div class="who"><?= e((string) ($authUser['login'] ?? 'Admin')) ?></div>
          <div class="role"><?= e(\App\Services\PermissionService::groupNameForUser($authUser)) ?> · v<?= e($appVersion) ?></div>
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
        <?php if ($can('player_detail') || $can('menu_oyuncular')): ?>
        <div class="search-wrap" id="topSearchWrap">
          <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="topSearchInput" type="search" autocomplete="off" placeholder="Hesap, e-posta veya karakter ara..." minlength="2">
          </div>
          <div class="search-drop" id="topSearchDrop" role="listbox" aria-label="Arama sonuçları"></div>
        </div>
        <?php endif; ?>
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
          <strong><?= number_format($openTicketCount, 0, ',', '.') ?></strong>
          <span class="lbl">Açık Destek Talebi</span>
          <span class="delta">Kapalı olmayan ticketler</span>
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

      <div class="card" style="margin-top:22px;">
        <div class="card-head">
          <h3>Personel Duyuruları</h3>
          <?php if ($can('menu_duyurular') || $can('announcements')): ?>
            <a href="#" data-target="duyurular" data-jump-section="duyurular" style="font-size:.8rem;color:var(--gold-light);">Tümünü yönet</a>
          <?php endif; ?>
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
                <?php if ($ann['author_login'] !== ''): ?>
                  <span>· <?= e((string) $ann['author_login']) ?></span>
                <?php endif; ?>
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

    <!-- ===================== OYUNCU YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'oyuncular' ? ' active' : '' ?>" id="oyuncular">
      <div class="card">
        <div class="card-head">
          <h3>Oyuncu Yönetimi</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= number_format($playerTotal, 0, ',', '.') ?> kayıtlı hesap · canlı DB</span>
            <?php
              $refreshQs = http_build_query(array_filter([
                  'section' => 'oyuncular',
                  'q' => $playerQ !== '' ? $playerQ : null,
                  'status' => $playerStatus !== '' ? $playerStatus : null,
                  'admins' => $playerAdminsOnly ? '1' : null,
                  'per' => $playerPerPage !== 10 ? $playerPerPage : null,
                  'page' => $playerPage > 1 ? $playerPage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $refreshQs)) ?>" title="Veritabanından yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="oyuncular">
          <input name="q" value="<?= e($playerQ) ?>" placeholder="Hesap, karakter veya e-posta ara..." style="flex:1; min-width:200px;">
          <select name="status">
            <option value=""<?= $playerStatus === '' ? ' selected' : '' ?>>Tüm Durumlar</option>
            <option value="OK"<?= $playerStatus === 'OK' ? ' selected' : '' ?>>Aktif</option>
            <option value="BLOCK"<?= $playerStatus === 'BLOCK' ? ' selected' : '' ?>>Banlı</option>
          </select>
          <label class="filter-check" title="WebPermission ≥ 1 (Admin / Süper Admin)">
            <input type="checkbox" name="admins" value="1"<?= $playerAdminsOnly ? ' checked' : '' ?>>
            Yöneticileri göster
          </label>
          <select name="per" title="Sayfa başına">
            <?php foreach ($playerPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $playerPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrele</button>
        </form>
        <table>
          <thead><tr><th>Hesap</th><th>E-posta</th><th>Karakter</th><th>Seviye</th><th>IP</th><th>Rol</th><th>Durum</th><th>İşlemler</th></tr></thead>
          <tbody>
            <?php if ($playerAccounts === []): ?>
              <tr><td colspan="8" style="color:var(--ash);">Kayıt bulunamadı.</td></tr>
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
                  <span class="badge <?= e((string) ($acc['role_badge'] ?? 'closed')) ?>"><?= e((string) ($acc['role_label'] ?? 'Oyuncu')) ?></span>
                </td>
                <td>
                  <span class="badge <?= e((string) $acc['status_badge']) ?>">
                    <?= e((string) $acc['status_label']) ?>
                  </span>
                </td>
                <td class="actions-cell">
                  <?php if ($can('player_detail')): ?>
                  <button type="button" title="Detay" data-detail="<?= (int) $acc['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  <?php endif; ?>
                  <?php if ($can('site_settings')): ?>
                  <?php
                    $targetIsSuper = ((int) ($acc['web_permission'] ?? 0) === 2);
                    $canAssignPerm = !$targetIsSuper || ((int) ($authUser['permission'] ?? 0) === 2);
                  ?>
                  <?php if ($canAssignPerm): ?>
                  <button type="button" title="Yetki grubu"
                    data-perm-id="<?= (int) $acc['id'] ?>"
                    data-perm-login="<?= e((string) $acc['login']) ?>"
                    data-perm-group="<?= (int) ($acc['staff_group_id'] ?? 0) ?>"
                    data-perm-groups="<?= e(json_encode(array_values(array_map('intval', is_array($acc['staff_group_ids'] ?? null) ? $acc['staff_group_ids'] : ((($acc['staff_group_id'] ?? null) ? [(int) $acc['staff_group_id']] : [])))), JSON_UNESCAPED_UNICODE) ?: '[]') ?>"
                  ><i class="fa-solid fa-shield-halved"></i></button>
                  <?php endif; ?>
                  <?php endif; ?>
                  <?php if ($can('ban')): ?>
                  <?php if ($acc['status'] === 'BLOCK'): ?>
                    <button type="button" title="Banı kaldır"
                      data-unban-id="<?= (int) $acc['id'] ?>"
                      data-unban-login="<?= e((string) $acc['login']) ?>"
                      data-unban-section="oyuncular"><i class="fa-solid fa-lock-open"></i></button>
                  <?php else: ?>
                    <button type="button" title="Banla" class="danger" data-ban-id="<?= (int) $acc['id'] ?>" data-ban-login="<?= e((string) $acc['login']) ?>"><i class="fa-solid fa-gavel"></i></button>
                  <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <?php
          $mk = static function (int $p, ?int $per = null) use ($playerQ, $playerStatus, $playerPerPage, $playerAdminsOnly): string {
              $per = $per ?? $playerPerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'oyuncular',
                  'q' => $playerQ !== '' ? $playerQ : null,
                  'status' => $playerStatus !== '' ? $playerStatus : null,
                  'admins' => $playerAdminsOnly ? '1' : null,
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

    <!-- ===================== EVLİLİKLER ===================== -->
    <section class="section<?= $panelSection === 'evlilikler' ? ' active' : '' ?>" id="evlilikler">
      <div class="card">
        <div class="card-head">
          <h3>Evlilikler</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= number_format($marriageTotal, 0, ',', '.') ?> kayıt · player.marriage</span>
            <?php
              $refreshMarriageQs = http_build_query(array_filter([
                  'section' => 'evlilikler',
                  'marriage_q' => $marriageQ !== '' ? $marriageQ : null,
                  'marriage_per' => $marriagePerPage !== 20 ? $marriagePerPage : null,
                  'marriage_page' => $marriagePage > 1 ? $marriagePage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $refreshMarriageQs)) ?>" title="Yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="evlilikler">
          <input name="marriage_q" value="<?= e($marriageQ) ?>" placeholder="Karakter adı veya PID ara..." style="flex:1; min-width:200px;">
          <select name="marriage_per" title="Sayfa başına">
            <?php foreach ($marriagePerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $marriagePerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrele</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>Karakter 1</th>
              <th>Karakter 2</th>
              <th>Aşk Puanı</th>
              <th>Tarih</th>
              <th>İşlemler</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($marriageRows === []): ?>
              <tr><td colspan="5" style="color:var(--ash);">Evlilik kaydı yok.</td></tr>
            <?php else: ?>
              <?php foreach ($marriageRows as $mr): ?>
              <tr>
                <td>
                  <div><?= e((string) $mr['name1']) ?></div>
                  <div class="meta" style="font-size:.72rem;color:var(--ash);">PID <?= (int) $mr['pid1'] ?> · Sv. <?= (int) $mr['level1'] ?> · <?= e((string) $mr['job_label1']) ?></div>
                </td>
                <td>
                  <div><?= e((string) $mr['name2']) ?></div>
                  <div class="meta" style="font-size:.72rem;color:var(--ash);">PID <?= (int) $mr['pid2'] ?> · Sv. <?= (int) $mr['level2'] ?> · <?= e((string) $mr['job_label2']) ?></div>
                </td>
                <td><?= $mr['love_point'] !== null ? number_format((int) $mr['love_point'], 0, ',', '.') : '—' ?></td>
                <td><?= e((string) $mr['time_label']) ?></td>
                <td class="actions-cell">
                  <form method="post" action="<?= e(url('/admin/evlilik/bitir')) ?>" style="display:inline;" onsubmit="return confirm('Bu evliliği sonlandırmak istediğinize emin misiniz?');">
                    <?= $csrf ?>
                    <input type="hidden" name="pid1" value="<?= (int) $mr['pid1'] ?>">
                    <input type="hidden" name="pid2" value="<?= (int) $mr['pid2'] ?>">
                    <button type="submit" class="danger" title="Evliliği bitir"><i class="fa-solid fa-heart-crack"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $mkMarriage = static function (int $p, ?int $per = null) use ($marriageQ, $marriagePerPage): string {
              $per = $per ?? $marriagePerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'evlilikler',
                  'marriage_q' => $marriageQ !== '' ? $marriageQ : null,
                  'marriage_per' => $per !== 20 ? $per : null,
                  'marriage_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $marriagePage ?> / <?= (int) $marriagePages ?>
            · <?= (int) $marriagePerPage ?> kayıt / sayfa
            · Toplam <?= number_format($marriageTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $marriagePage <= 1 ? 'disabled' : '' ?>" href="<?= e($mkMarriage(max(1, $marriagePage - 1))) ?>">Önceki</a>
            <?php
              $mStart = max(1, $marriagePage - 2);
              $mEnd = min($marriagePages, $marriagePage + 2);
              for ($i = $mStart; $i <= $mEnd; $i++):
            ?>
              <?php if ($i === $marriagePage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($mkMarriage($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $marriagePage >= $marriagePages ? 'disabled' : '' ?>" href="<?= e($mkMarriage(min($marriagePages, $marriagePage + 1))) ?>">Sonraki</a>
          </div>
        </div>
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
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $rankRefreshQs)) ?>"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
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
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
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

    <!-- ===================== BİNEK YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'binek' ? ' active' : '' ?>" id="binek">
      <div class="card">
        <div class="card-head">
          <h3>Binek Yönetimi</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= number_format($horseTotal, 0, ',', '.') ?> kayıt · <code>horse_name</code></span>
            <?php
              $horseRefreshQs = http_build_query(array_filter([
                  'section' => 'binek',
                  'horse_q' => $horseQ !== '' ? $horseQ : null,
                  'horse_per' => $horsePerPage !== 10 ? $horsePerPage : null,
                  'horse_page' => $horsePage > 1 ? $horsePage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $horseRefreshQs)) ?>" title="Yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          At adı <code>horse_name</code> tablosunda tutulur; <code>id</code> karakterin <code>player.id</code> değeriyle eşleşir.
        </p>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="binek">
          <input name="horse_q" value="<?= e($horseQ) ?>" placeholder="At adı, karakter veya hesap ara..." style="flex:1; min-width:200px;">
          <select name="horse_per" title="Sayfa başına">
            <?php foreach ($horsePerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $horsePerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>At adı</th>
              <th>Karakter</th>
              <th>Hesap</th>
              <th>At Sv.</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($horseRows === []): ?>
              <tr><td colspan="5" style="color:var(--ash);">Binek kaydı bulunamadı.</td></tr>
            <?php else: ?>
              <?php foreach ($horseRows as $h): ?>
              <tr>
                <td class="row-user">
                  <div class="av"><i class="fa-solid fa-horse"></i></div>
                  <div>
                    <div><?= e((string) $h['horse_name']) ?></div>
                    <div class="meta">Player ID #<?= (int) $h['id'] ?></div>
                  </div>
                </td>
                <td>
                  <div><?= e((string) $h['character_name']) ?></div>
                  <div class="meta" style="font-size:.72rem;color:var(--ash);">
                    <?= e((string) ($h['job_label'] ?? '')) ?>
                    <?= (int) ($h['level'] ?? 0) > 0 ? ' · Sv.' . (int) $h['level'] : '' ?>
                    <?= !empty($h['orphan']) ? ' · karakter yok' : '' ?>
                  </div>
                </td>
                <td>
                  <?php if ((int) ($h['account_id'] ?? 0) > 0): ?>
                    <div><?= e((string) $h['account_login']) ?></div>
                    <div class="meta" style="font-size:.72rem;color:var(--ash);">Acc #<?= (int) $h['account_id'] ?></div>
                  <?php else: ?>
                    <span style="color:var(--ash);">—</span>
                  <?php endif; ?>
                </td>
                <td><?= (int) ($h['horse_level'] ?? 0) ?></td>
                <td class="actions-cell">
                  <button type="button" title="At adını değiştir"
                    data-horse-rename="<?= (int) $h['id'] ?>"
                    data-horse-name="<?= e((string) $h['horse_name']) ?>"
                    data-horse-char="<?= e((string) $h['character_name']) ?>"><i class="fa-solid fa-pen"></i></button>
                  <?php if ((int) ($h['account_id'] ?? 0) > 0 && $can('player_detail')): ?>
                    <a class="btn btn-ghost btn-sm" style="padding:6px 8px;" href="<?= e(url('/admin/oyuncu?id=' . (int) $h['account_id'])) ?>" title="Hesap detayı"><i class="fa-solid fa-eye"></i></a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $hmk = static function (int $p, ?int $per = null) use ($horseQ, $horsePerPage): string {
              $per = $per ?? $horsePerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'binek',
                  'horse_q' => $horseQ !== '' ? $horseQ : null,
                  'horse_per' => $per !== 10 ? $per : null,
                  'horse_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $horsePage ?> / <?= (int) $horsePages ?>
            · <?= (int) $horsePerPage ?> kayıt / sayfa
            · Toplam <?= number_format($horseTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $horsePage <= 1 ? 'disabled' : '' ?>" href="<?= e($hmk(max(1, $horsePage - 1))) ?>">Önceki</a>
            <?php
              $hStart = max(1, $horsePage - 2);
              $hEnd = min($horsePages, $horsePage + 2);
              for ($i = $hStart; $i <= $hEnd; $i++):
            ?>
              <?php if ($i === $horsePage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($hmk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $horsePage >= $horsePages ? 'disabled' : '' ?>" href="<?= e($hmk(min($horsePages, $horsePage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== GM YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'gm' ? ' active' : '' ?>" id="gm">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><h3>GM Ekle</h3></div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Kayıtlar <code>common.gmlist</code> tablosuna yazılır. <code>mAuthority</code> değerleri veritabanı enum’u ile aynıdır.
        </p>
        <form method="post" action="<?= e(url('/admin/gm/ekle')) ?>" class="filters" style="align-items:flex-end;flex-wrap:wrap;">
          <?= $csrf ?>
          <div class="form-row" style="margin:0;min-width:140px;"><label>Hesap (mAccount)</label><input name="account" maxlength="32" required placeholder="login"></div>
          <div class="form-row" style="margin:0;min-width:140px;"><label>Karakter (mName)</label><input name="name" maxlength="32" required placeholder="karakter adı"></div>
          <div class="form-row" style="margin:0;min-width:160px;">
            <label>Yetki (mAuthority)</label>
            <select name="authority" required>
              <?php foreach ($gmAuthorities as $akey => $alabel): ?>
                <option value="<?= e((string) $akey) ?>"<?= $akey === 'LOW_WIZARD' ? ' selected' : '' ?>><?= e((string) $alabel) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row" style="margin:0;min-width:120px;"><label>Contact IP</label><input name="contact_ip" maxlength="16" placeholder="boş = hepsi"></div>
          <div class="form-row" style="margin:0;min-width:120px;"><label>Server IP</label><input name="server_ip" maxlength="16" value="ALL"></div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Ekle</button>
        </form>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>GM Listesi</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem;color:var(--ash);"><?= number_format($gmTotal, 0, ',', '.') ?> kayıt · <code>gmlist</code></span>
            <?php
              $gmRefreshQs = http_build_query(array_filter([
                  'section' => 'gm',
                  'gm_q' => $gmQ !== '' ? $gmQ : null,
                  'gm_per' => $gmPerPage !== 20 ? $gmPerPage : null,
                  'gm_page' => $gmPage > 1 ? $gmPage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $gmRefreshQs)) ?>"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="gm">
          <input name="gm_q" value="<?= e($gmQ) ?>" placeholder="Hesap, karakter, IP veya yetki ara..." style="flex:1;min-width:200px;">
          <select name="gm_per">
            <?php foreach ($gmPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $gmPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Hesap</th>
              <th>Karakter</th>
              <th>Contact IP</th>
              <th>Server IP</th>
              <th>Yetki</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($gmRows === []): ?>
              <tr><td colspan="7" style="color:var(--ash);">GM kaydı yok.</td></tr>
            <?php else: ?>
              <?php foreach ($gmRows as $gm): ?>
              <tr>
                <td>#<?= (int) $gm['id'] ?></td>
                <td><?= e((string) $gm['account']) ?></td>
                <td><?= e((string) $gm['name']) ?></td>
                <td style="font-size:.82rem;color:var(--ash);"><?= e((string) ($gm['contact_ip'] !== '' ? $gm['contact_ip'] : '—')) ?></td>
                <td style="font-size:.82rem;"><?= e((string) $gm['server_ip']) ?></td>
                <td>
                  <form method="post" action="<?= e(url('/admin/gm/guncelle')) ?>" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <?= $csrf ?>
                    <input type="hidden" name="id" value="<?= (int) $gm['id'] ?>">
                    <input type="hidden" name="account" value="<?= e((string) $gm['account']) ?>">
                    <input type="hidden" name="name" value="<?= e((string) $gm['name']) ?>">
                    <input type="hidden" name="contact_ip" value="<?= e((string) $gm['contact_ip']) ?>">
                    <input type="hidden" name="server_ip" value="<?= e((string) $gm['server_ip']) ?>">
                    <select name="authority" class="panel-select" onchange="this.form.submit()" title="mAuthority">
                      <?php foreach ($gmAuthorities as $akey => $alabel): ?>
                        <option value="<?= e((string) $akey) ?>"<?= ((string) $gm['authority'] === (string) $akey) ? ' selected' : '' ?>><?= e((string) $alabel) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-gm-edit="<?= (int) $gm['id'] ?>"
                    data-gm-account="<?= e((string) $gm['account']) ?>"
                    data-gm-name="<?= e((string) $gm['name']) ?>"
                    data-gm-contact="<?= e((string) $gm['contact_ip']) ?>"
                    data-gm-server="<?= e((string) $gm['server_ip']) ?>"
                    data-gm-authority="<?= e((string) $gm['authority']) ?>"><i class="fa-solid fa-pen"></i></button>
                  <form method="post" action="<?= e(url('/admin/gm/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu GM kaydı silinsin mi?');">
                    <?= $csrf ?>
                    <input type="hidden" name="id" value="<?= (int) $gm['id'] ?>">
                    <button type="submit" title="Sil"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $gmk = static function (int $p) use ($gmQ, $gmPerPage): string {
              $qs = http_build_query(array_filter([
                  'section' => 'gm',
                  'gm_q' => $gmQ !== '' ? $gmQ : null,
                  'gm_per' => $gmPerPage !== 20 ? $gmPerPage : null,
                  'gm_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>Sayfa <?= (int) $gmPage ?> / <?= (int) $gmPages ?> · Toplam <?= number_format($gmTotal, 0, ',', '.') ?></div>
          <div class="links">
            <a class="<?= $gmPage <= 1 ? 'disabled' : '' ?>" href="<?= e($gmk(max(1, $gmPage - 1))) ?>">Önceki</a>
            <?php
              $gmStart = max(1, $gmPage - 2);
              $gmEnd = min($gmPages, $gmPage + 2);
              for ($i = $gmStart; $i <= $gmEnd; $i++):
            ?>
              <?php if ($i === $gmPage): ?><span class="cur"><?= $i ?></span><?php else: ?><a href="<?= e($gmk($i)) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $gmPage >= $gmPages ? 'disabled' : '' ?>" href="<?= e($gmk(min($gmPages, $gmPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== IP BAN ===================== -->
    <section class="section<?= $panelSection === 'ip-ban' ? ' active' : '' ?>" id="ip-ban">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><h3>IP Ekle</h3></div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          IP adresleri <code>player.pcbang_ip</code> tablosuna yazılır. Sebep ve ekleyen bilgisi <code>DNWeb.ip_bans</code> içinde tutulur.
        </p>
        <form method="post" action="<?= e(url('/admin/ip-ban/ekle')) ?>" class="filters" style="align-items:flex-end;flex-wrap:wrap;">
          <?= $csrf ?>
          <div class="form-row" style="margin:0;min-width:160px;"><label>IP</label><input name="ip" maxlength="15" required placeholder="1.2.3.4"></div>
          <div class="form-row" style="margin:0;min-width:220px;flex:1;"><label>Sebep</label><input name="reason" maxlength="500" placeholder="Ban sebebi (opsiyonel)"></div>
          <div class="form-row" style="margin:0;min-width:100px;"><label>pcbang_id</label><input name="pcbang_id" type="number" min="0" value="0"></div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Ekle</button>
        </form>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>IP Ban Listesi</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= number_format($ipBanTotal, 0, ',', '.') ?> kayıt</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="ip-ban">
          <input name="ipban_q" value="<?= e($ipBanQ) ?>" placeholder="IP ara..." style="flex:1;min-width:200px;">
          <select name="ipban_per">
            <?php foreach ($ipBanPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $ipBanPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>IP</th>
              <th>pcbang_id</th>
              <th>Sebep</th>
              <th>Ekleyen</th>
              <th>Tarih</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($ipBanRows === []): ?>
              <tr><td colspan="7" style="color:var(--ash);">IP kaydı yok.</td></tr>
            <?php else: ?>
              <?php foreach ($ipBanRows as $ban): ?>
              <tr>
                <td>#<?= (int) $ban['id'] ?></td>
                <td><?= e((string) $ban['ip']) ?></td>
                <td><?= (int) $ban['pcbang_id'] ?></td>
                <td style="color:var(--ash);font-size:.82rem;"><?= e((string) ($ban['reason'] !== '' ? $ban['reason'] : '—')) ?></td>
                <td><?= e((string) ($ban['created_by_login'] !== '' ? $ban['created_by_login'] : '—')) ?></td>
                <td style="white-space:nowrap;font-size:.8rem;"><?= e((string) $ban['created_label']) ?></td>
                <td class="actions-cell">
                  <form method="post" action="<?= e(url('/admin/ip-ban/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu IP listeden silinsin mi?');">
                    <?= $csrf ?>
                    <input type="hidden" name="id" value="<?= (int) $ban['id'] ?>">
                    <button type="submit" title="Sil"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $ibk = static function (int $p) use ($ipBanQ, $ipBanPerPage): string {
              $qs = http_build_query(array_filter([
                  'section' => 'ip-ban',
                  'ipban_q' => $ipBanQ !== '' ? $ipBanQ : null,
                  'ipban_per' => $ipBanPerPage !== 20 ? $ipBanPerPage : null,
                  'ipban_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>Sayfa <?= (int) $ipBanPage ?> / <?= (int) $ipBanPages ?> · Toplam <?= number_format($ipBanTotal, 0, ',', '.') ?></div>
          <div class="links">
            <a class="<?= $ipBanPage <= 1 ? 'disabled' : '' ?>" href="<?= e($ibk(max(1, $ipBanPage - 1))) ?>">Önceki</a>
            <?php
              $ibStart = max(1, $ipBanPage - 2);
              $ibEnd = min($ipBanPages, $ipBanPage + 2);
              for ($i = $ibStart; $i <= $ibEnd; $i++):
            ?>
              <?php if ($i === $ipBanPage): ?><span class="cur"><?= $i ?></span><?php else: ?><a href="<?= e($ibk($i)) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $ipBanPage >= $ipBanPages ? 'disabled' : '' ?>" href="<?= e($ibk(min($ipBanPages, $ipBanPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== LONCALAR ===================== -->
    <section class="section<?= $panelSection === 'loncalar' ? ' active' : '' ?>" id="loncalar">
      <div class="card">
        <div class="card-head">
          <h3>Loncalar</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= number_format($guildTotal, 0, ',', '.') ?> lonca · canlı DB</span>
            <?php
              $guildRefreshQs = http_build_query(array_filter([
                  'section' => 'loncalar',
                  'guild_q' => $guildQ !== '' ? $guildQ : null,
                  'guild_per' => $guildPerPage !== 10 ? $guildPerPage : null,
                  'guild_page' => $guildPage > 1 ? $guildPage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $guildRefreshQs)) ?>" title="Veritabanından yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="loncalar">
          <input name="guild_q" value="<?= e($guildQ) ?>" placeholder="Lonca adına göre ara..." style="flex:1; min-width:200px;">
          <select name="guild_per" title="Sayfa başına">
            <?php foreach ($guildPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $guildPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>Lonca</th>
              <th>Usta</th>
              <th>Lonca Sv.</th>
              <th>Usta Rütbe</th>
              <th>Üye</th>
              <th>Ladder</th>
              <th>Savaş G/B/M</th>
              <th>Yang</th>
              <th>İşlemler</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($guildRows === []): ?>
              <tr><td colspan="9" style="color:var(--ash);">Lonca bulunamadı.</td></tr>
            <?php else: ?>
              <?php foreach ($guildRows as $g): ?>
              <tr>
                <td class="row-user">
                  <div class="av"><i class="fa-solid fa-shield"></i></div>
                  <div>
                    <div><?= e((string) $g['name']) ?></div>
                    <div class="meta">ID #<?= (int) $g['id'] ?></div>
                  </div>
                </td>
                <td>
                  <div><?= e((string) $g['master_name']) ?></div>
                  <div class="meta" style="font-size:.72rem;color:var(--ash);"><?= e((string) ($g['master_job_label'] ?? '')) ?><?= (int) ($g['master_level'] ?? 0) > 0 ? ' · Sv.' . (int) $g['master_level'] : '' ?></div>
                </td>
                <td><?= (int) $g['level'] ?></td>
                <td>
                  <div><?= e((string) ($g['master_grade_label'] ?? '—')) ?></div>
                  <?php if ((int) ($g['master_grade'] ?? 0) > 0): ?>
                    <div class="meta" style="font-size:.72rem;color:var(--ash);">Grade <?= (int) $g['master_grade'] ?></div>
                  <?php endif; ?>
                </td>
                <td><?= (int) $g['member_count'] ?></td>
                <td><?= number_format((int) $g['ladder_point'], 0, ',', '.') ?></td>
                <td style="font-size:.82rem;" title="Galibiyet / Beraberlik / Mağlubiyet">
                  <?= e((string) $g['record_label']) ?>
                  <?php if ((int) ($g['wars'] ?? 0) > 0): ?>
                    <div class="meta" style="font-size:.7rem;color:var(--ash);"><?= (int) $g['wars'] ?> savaş</div>
                  <?php endif; ?>
                </td>
                <td><?= number_format((int) $g['gold'], 0, ',', '.') ?></td>
                <td class="actions-cell">
                  <button type="button" title="Detay" data-guild-detail="<?= (int) $g['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  <button type="button" title="Ad değiştir"
                    data-guild-rename="<?= (int) $g['id'] ?>"
                    data-guild-name="<?= e((string) $g['name']) ?>"><i class="fa-solid fa-pen"></i></button>
                  <button type="button" title="Usta değiştir"
                    data-guild-master="<?= (int) $g['id'] ?>"
                    data-guild-name="<?= e((string) $g['name']) ?>"
                    data-guild-master-pid="<?= (int) $g['master'] ?>"><i class="fa-solid fa-crown"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $gmk = static function (int $p, ?int $per = null) use ($guildQ, $guildPerPage): string {
              $per = $per ?? $guildPerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'loncalar',
                  'guild_q' => $guildQ !== '' ? $guildQ : null,
                  'guild_per' => $per !== 10 ? $per : null,
                  'guild_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $guildPage ?> / <?= (int) $guildPages ?>
            · <?= (int) $guildPerPage ?> kayıt / sayfa
            · Toplam <?= number_format($guildTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $guildPage <= 1 ? 'disabled' : '' ?>" href="<?= e($gmk(max(1, $guildPage - 1))) ?>">Önceki</a>
            <?php
              $gStart = max(1, $guildPage - 2);
              $gEnd = min($guildPages, $guildPage + 2);
              for ($i = $gStart; $i <= $gEnd; $i++):
            ?>
              <?php if ($i === $guildPage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($gmk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $guildPage >= $guildPages ? 'disabled' : '' ?>" href="<?= e($gmk(min($guildPages, $guildPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== LONCA SAVAŞI ===================== -->
    <section class="section<?= $panelSection === 'lonca-savaslari' ? ' active' : '' ?>" id="lonca-savaslari">
      <?php
        $guildWars = isset($guildWars) && is_array($guildWars) ? $guildWars : [];
        $guildWarHistory = isset($guildWarHistory) && is_array($guildWarHistory) ? $guildWarHistory : [];
        $guildWarBoard = isset($guildWarBoard) && is_array($guildWarBoard) ? $guildWarBoard : [];
      ?>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head">
          <h3>Lonca Savaşı</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= count($guildWars) ?> aktif · <?= count($guildWarHistory) ?> geçmiş · salt okunur</span>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=lonca-savaslari')) ?>" title="Yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Canlı savaşlar <code>guild_war</code>, geçmiş / ganimet <code>guild_war_reservation</code>, bahisler <code>guild_war_bet</code> tablolarından okunur. Lonca skoru <code>guild.win / draw / loss</code>.
        </p>

        <div class="guild-tabs" id="adminWarTabs">
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
                      <button type="button" data-guild-detail="<?= (int) $w['from_id'] ?>" style="background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;font-weight:600;"><?= e((string) $w['from_name']) ?></button>
                      <div class="meta">Sv.<?= (int) $w['from_level'] ?> · <?= (int) ($w['from_win'] ?? 0) ?>/<?= (int) ($w['from_draw'] ?? 0) ?>/<?= (int) ($w['from_loss'] ?? 0) ?> · Usta: <?= e((string) $w['from_master_name']) ?></div>
                    </div>
                  </td>
                  <td style="text-align:center;color:var(--blood-light);font-weight:700;letter-spacing:.08em;">VS</td>
                  <td class="row-user">
                    <div class="av"><i class="fa-solid fa-shield"></i></div>
                    <div>
                      <button type="button" data-guild-detail="<?= (int) $w['to_id'] ?>" style="background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;font-weight:600;"><?= e((string) $w['to_name']) ?></button>
                      <div class="meta">Sv.<?= (int) $w['to_level'] ?> · <?= (int) ($w['to_win'] ?? 0) ?>/<?= (int) ($w['to_draw'] ?? 0) ?>/<?= (int) ($w['to_loss'] ?? 0) ?> · Usta: <?= e((string) $w['to_master_name']) ?></div>
                    </div>
                  </td>
                  <td><span class="badge ok"><?= e((string) $w['war_type_label']) ?></span></td>
                  <td style="font-family:var(--font-display);color:var(--gold-light);"><?= e((string) $w['score_label']) ?></td>
                  <td style="font-size:.82rem;">
                    <?= number_format((int) $w['from_ladder'], 0, ',', '.') ?>
                    <span style="color:var(--ash);"> / </span>
                    <?= number_format((int) $w['to_ladder'], 0, ',', '.') ?>
                  </td>
                  <td><?= e((string) ($w['warprice_label'] ?? ((int) $w['warprice'] > 0 ? number_format((int) $w['warprice'], 0, ',', '.') : '—'))) ?></td>
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
                    <button type="button" data-guild-detail="<?= (int) $w['from_id'] ?>" style="background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;"><?= e((string) $w['from_name']) ?></button>
                    <span style="color:var(--ash);"> vs </span>
                    <button type="button" data-guild-detail="<?= (int) $w['to_id'] ?>" style="background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;"><?= e((string) $w['to_name']) ?></button>
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
                    <button type="button" data-guild-detail="<?= (int) $row['id'] ?>" style="background:none;border:none;color:var(--gold-light);cursor:pointer;padding:0;font:inherit;font-weight:600;"><?= e((string) $row['name']) ?></button>
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

    <!-- ===================== TOPLULUK KURALLARI ===================== -->
    <section class="section<?= $panelSection === 'kurallar-ayarlari' ? ' active' : '' ?>" id="kurallar-ayarlari">
      <?php
        $communityRules = isset($communityRules) && is_array($communityRules) ? $communityRules : [];
      ?>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head">
          <h3>Topluluk Kuralları</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= count($communityRules) ?> madde · <a href="<?= e(url('/kurallar')) ?>" target="_blank" style="color:var(--gold-light);">Sayfayı aç</a></span>
        </div>
        <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Public sayfa: <code>/kurallar</code> · Footer “Kurallar” linki bu adrese bağlıdır.</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
          <form method="post" action="<?= e(url('/admin/ayarlar/kurallar/numara')) ?>" style="display:inline;"><?= $csrf ?><button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-down-1-9"></i> Numaraları yenile</button></form>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Kural</th>
                <th>1. İhlal</th>
                <th>2. İhlal</th>
                <th>3. İhlal</th>
                <th>Durum</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if ($communityRules === []): ?>
                <tr><td colspan="7" style="color:var(--ash);">Henüz kural yok.</td></tr>
              <?php else: ?>
                <?php foreach ($communityRules as $cr):
                  $detailPreview = (string) $cr['detail'];
                  $detailShort = mb_strlen($detailPreview) > 120 ? mb_substr($detailPreview, 0, 120) . '…' : $detailPreview;
                ?>
                <tr>
                  <td><?= (int) $cr['rule_no'] ?></td>
                  <td style="max-width:220px;">
                    <strong><?= e((string) $cr['title']) ?></strong>
                    <div style="font-size:.72rem;color:var(--ash);margin-top:4px;max-height:3.2em;overflow:hidden;"><?= e($detailShort) ?></div>
                  </td>
                  <td style="font-size:.78rem;"><?= e((string) $cr['penalty_1']) ?></td>
                  <td style="font-size:.78rem;"><?= e((string) $cr['penalty_2']) ?></td>
                  <td style="font-size:.78rem;"><?= e((string) $cr['penalty_3']) ?></td>
                  <td><?= !empty($cr['is_active']) ? '<span class="badge ok">Aktif</span>' : '<span class="badge ban">Pasif</span>' ?></td>
                  <td class="actions-cell">
                    <button type="button" title="Düzenle" data-edit-rule data-id="<?= (int) $cr['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/ayarlar/kurallar/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu kural silinsin mi?');">
                      <?= $csrf ?><input type="hidden" name="id" value="<?= (int) $cr['id'] ?>">
                      <button type="submit" class="danger" title="Sil"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <script type="application/json" id="communityRulesJson"><?= json_encode(array_values(array_map(static function (array $r): array {
            return [
                'id' => (int) $r['id'],
                'title' => (string) $r['title'],
                'detail' => (string) $r['detail'],
                'penalty_1' => (string) $r['penalty_1'],
                'penalty_2' => (string) $r['penalty_2'],
                'penalty_3' => (string) $r['penalty_3'],
                'sort_order' => (int) $r['sort_order'],
                'is_active' => !empty($r['is_active']),
            ];
        }, $communityRules)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
      </div>
      <div class="card">
        <div class="card-head"><h3 id="ruleFormTitle">Yeni Kural</h3></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/kurallar')) ?>" id="ruleForm">
          <?= $csrf ?>
          <input type="hidden" name="id" id="ruleId" value="">
          <div class="form-row"><label>Kural başlığı</label><input name="title" id="ruleTitle" required maxlength="200"></div>
          <div class="form-row"><label>Detay</label><textarea name="detail" id="ruleDetail" required style="min-height:140px;" placeholder="Madde madde açıklama…"></textarea></div>
          <div class="grid grid-3">
            <div class="form-row"><label>1. İhlal</label><input name="penalty_1" id="ruleP1" required maxlength="200"></div>
            <div class="form-row"><label>2. İhlal</label><input name="penalty_2" id="ruleP2" required maxlength="200"></div>
            <div class="form-row"><label>3. İhlal</label><input name="penalty_3" id="ruleP3" required maxlength="200"></div>
          </div>
          <div class="grid grid-2">
            <div class="form-row"><label>Sıra</label><input type="number" name="sort_order" id="ruleSort" value="0" min="0"></div>
            <div class="form-row"><label><input type="checkbox" name="is_active" id="ruleActive" value="1" checked> Aktif</label></div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
            <button type="button" class="btn btn-ghost btn-sm" id="ruleReset">Temizle</button>
          </div>
        </form>
      </div>
    </section>

    <!-- ===================== GİZLİLİK / KVKK ===================== -->
    <section class="section<?= $panelSection === 'gizlilik-ayarlari' ? ' active' : '' ?>" id="gizlilik-ayarlari">
      <?php
        $privacyTitle = isset($privacyTitle) && is_string($privacyTitle) ? $privacyTitle : 'Gizlilik Sözleşmesi ve KVKK';
        $privacyHtml = isset($privacyHtml) && is_string($privacyHtml) ? $privacyHtml : '';
      ?>
      <div class="card" style="max-width:960px;">
        <div class="card-head">
          <h3>Gizlilik Sözleşmesi ve KVKK</h3>
          <a href="<?= e(url('/gizlilik')) ?>" target="_blank" style="font-size:.8rem;color:var(--gold-light);">Sayfayı aç</a>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Public sayfa: <code>/gizlilik</code> · Footer “Gizlilik / KVKK” linki bu adrese bağlıdır. İçeriği editörle düzenleyebilirsiniz.
        </p>
        <form method="post" action="<?= e(url('/admin/ayarlar/gizlilik')) ?>" id="privacyForm">
          <?= $csrf ?>
          <div class="form-row"><label>Sayfa başlığı</label><input name="title" id="privacyTitle" required maxlength="200" value="<?= e($privacyTitle) ?>"></div>
          <div class="form-row">
            <label>İçerik</label>
            <div id="privacyEditorWrap">
              <div class="ann-toolbar" id="privacyToolbar" role="toolbar" aria-label="Metin araçları">
                <button type="button" data-pcmd="bold" title="Kalın"><b>B</b></button>
                <button type="button" data-pcmd="italic" title="İtalik"><i>I</i></button>
                <button type="button" data-pcmd="underline" title="Altı çizili"><u>U</u></button>
                <span class="sep"></span>
                <button type="button" data-pcmd="formatBlock" data-value="h2" title="Bölüm başlığı">H2</button>
                <button type="button" data-pcmd="formatBlock" data-value="h3" title="Alt başlık">H3</button>
                <button type="button" data-pcmd="formatBlock" data-value="p" title="Paragraf">P</button>
                <span class="sep"></span>
                <button type="button" data-pcmd="insertUnorderedList" title="Madde listesi"><i class="fa-solid fa-list-ul"></i></button>
                <button type="button" data-pcmd="insertOrderedList" title="Numaralı liste"><i class="fa-solid fa-list-ol"></i></button>
                <span class="sep"></span>
                <button type="button" data-pcmd="createLink" title="Link"><i class="fa-solid fa-link"></i></button>
                <button type="button" data-pcmd="removeFormat" title="Biçimi temizle"><i class="fa-solid fa-eraser"></i></button>
                <button type="button" data-pcmd="toggleHtml" title="HTML kaynak" id="privacyToggleHtml"><i class="fa-solid fa-code"></i></button>
              </div>
              <div id="privacyEditor" contenteditable="true" data-placeholder="Gizlilik metnini buraya yaz…"></div>
              <textarea id="privacyHtmlPanel" spellcheck="false" aria-label="HTML kaynak"></textarea>
            </div>
            <textarea name="body" id="privacyBody" hidden><?= e($privacyHtml) ?></textarea>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/gizlilik')) ?>" target="_blank">Önizle</a>
          </div>
        </form>
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
            <div class="form-row"><label>Kaç gün (0 = süresiz → 10.11.1938)</label><input type="number" name="days" id="penaltyDays" min="0" max="3650" value="1" required></div>
            <p style="font-size:.72rem;color:var(--ash);margin:-6px 0 10px;">0 gün = süresiz ban. Oyun hesabında <code>availDt</code> sabiti: 10 Kasım 1938.</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="penaltyReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== YETKİ GRUPLARI ===================== -->
    <section class="section<?= $panelSection === 'yetki-gruplari' ? ' active' : '' ?>" id="yetki-gruplari">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Yetki Grupları</h3><span style="font-size:.8rem;color:var(--ash);">web değeri sistemde sabit</span></div>
          <table>
            <thead><tr><th>ID</th><th>Yetki Tanımı</th><th>Web</th><th>Tür</th><th></th></tr></thead>
            <tbody>
              <?php if ($permissionGroups === []): ?>
                <tr><td colspan="5" style="color:var(--ash);">Grup yok.</td></tr>
              <?php else: ?>
                <?php foreach ($permissionGroups as $g): ?>
                <tr>
                  <td><code>#<?= (int) $g['id'] ?></code></td>
                  <td><?= e((string) $g['name']) ?></td>
                  <td><code><?= (int) $g['web_permission'] ?></code></td>
                  <td><?= !empty($g['is_system']) ? 'Sistem' : 'Özel' ?></td>
                  <td class="actions-cell">
                    <?php if ((int) $g['web_permission'] !== 0): ?>
                    <button type="button" title="Düzenle"
                      data-edit-group
                      data-id="<?= (int) $g['id'] ?>"
                      data-name="<?= e((string) $g['name']) ?>"
                      data-web="<?= (int) $g['web_permission'] ?>"
                      data-system="<?= !empty($g['is_system']) ? '1' : '0' ?>"
                      data-flags="<?= e(json_encode($g['flags'] ?? [], JSON_UNESCAPED_UNICODE)) ?>"
                    ><i class="fa-solid fa-pen"></i></button>
                    <?php endif; ?>
                    <?php if (empty($g['is_system'])): ?>
                    <form method="post" action="<?= e(url('/admin/yetki/grup/sil')) ?>" style="display:inline;" onsubmit="return confirm('Grup silinsin mi?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                      <button type="submit" class="danger" title="Sil"><i class="fa-solid fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="groupFormTitle">Yeni Yetki Grubu</h3></div>
          <form method="post" action="<?= e(url('/admin/yetki/grup')) ?>" id="groupForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="groupId" value="">
            <div class="form-row"><label>Yetki Tanımı</label><input name="name" id="groupName" required maxlength="120" placeholder="Örn: Destek Ekibi"></div>
            <div class="form-row"><label>Web İzni</label>
              <input type="text" id="groupWeb" value="1" disabled>
              <div style="font-size:.75rem;color:var(--ash);margin-top:6px;">Yeni gruplar her zaman web=1. Default User=0 ve Super Admin=2 değiştirilemez.</div>
            </div>
            <div class="form-row" id="groupFlagsWrap">
              <label>İşlemler</label>
              <table class="flags-table">
                <thead>
                  <tr>
                    <th class="flag-check">
                      <input type="checkbox" id="flagsSelectAll" title="Tümünü seç / kaldır">
                    </th>
                    <th>Yetki</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($permFlagDefs as $fkey => $flabel): ?>
                  <tr>
                    <td class="flag-check">
                      <input type="checkbox" name="flags[<?= e($fkey) ?>]" value="1" data-flag="<?= e($fkey) ?>" id="flag_<?= e($fkey) ?>">
                    </td>
                    <td><label for="flag_<?= e($fkey) ?>" style="cursor:pointer;"><?= e($flabel) ?></label></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <p class="flags-hint">Yeni grupta varsayılan: pasif. Super Admin tüm yetkilere sahiptir. Üstteki kutu ile tümünü seçebilirsin.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="groupReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== TICKET AYARLARI ===================== -->
    <section class="section<?= $panelSection === 'ticket-ayarlari' ? ' active' : '' ?>" id="ticket-ayarlari">
      <div class="grid grid-3">
        <div class="card">
          <div class="card-head"><h3>Ticket Kategorileri</h3></div>
          <table>
            <thead><tr><th>Ad</th><th>Açıklama</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ticketCategories as $cat): ?>
              <tr>
                <td><?= e((string) $cat['name']) ?></td>
                <td style="color:var(--ash);font-size:.8rem;"><?= e((string) $cat['description']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-edit-tcat
                    data-id="<?= (int) $cat['id'] ?>"
                    data-name="<?= e((string) $cat['name']) ?>"
                    data-description="<?= e((string) $cat['description']) ?>"
                  ><i class="fa-solid fa-pen"></i></button>
                  <form method="post" action="<?= e(url('/admin/ticket/kategori/sil')) ?>" style="display:inline;" onsubmit="return confirm('Silinsin mi?');">
                    <?= $csrf ?><input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?= e(url('/admin/ticket/kategori')) ?>" id="tcatForm" style="margin-top:14px;">
            <?= $csrf ?>
            <input type="hidden" name="id" id="tcatId" value="">
            <div class="form-row"><label>Kategori</label><input name="name" id="tcatName" required maxlength="120"></div>
            <div class="form-row"><label>Açıklama</label><textarea name="description" id="tcatDesc" maxlength="500" style="min-height:60px;"></textarea></div>
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
          </form>
        </div>
        <div class="card">
          <div class="card-head"><h3>Durumlar</h3></div>
          <table>
            <thead><tr><th>Kod</th><th>Etiket</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ticketStatuses as $st): ?>
              <tr>
                <td><code><?= e((string) $st['code']) ?></code></td>
                <td><?= e((string) $st['label']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-edit-tstat
                    data-id="<?= (int) $st['id'] ?>"
                    data-code="<?= e((string) $st['code']) ?>"
                    data-label="<?= e((string) $st['label']) ?>"
                    data-system="<?= !empty($st['is_system']) ? '1' : '0' ?>"
                  ><i class="fa-solid fa-pen"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?= e(url('/admin/ticket/durum')) ?>" id="tstatForm" style="margin-top:14px;">
            <?= $csrf ?>
            <input type="hidden" name="id" id="tstatId" value="">
            <div class="form-row"><label>Kod</label><input name="code" id="tstatCode" required maxlength="40" placeholder="ornek_durum"></div>
            <div class="form-row"><label>Etiket</label><input name="label" id="tstatLabel" required maxlength="120"></div>
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
          </form>
        </div>
        <div class="card">
          <div class="card-head"><h3>İzinli Dosya Türleri</h3></div>
          <table>
            <thead><tr><th>Uzantı</th><th>MIME</th><th>Aktif</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($ticketFileTypes as $ft): ?>
              <tr>
                <td>.<?= e((string) $ft['extension']) ?></td>
                <td style="font-size:.75rem;color:var(--ash);"><?= e((string) $ft['mime_type']) ?></td>
                <td><?= !empty($ft['is_active']) ? 'Evet' : 'Hayır' ?></td>
                <td class="actions-cell">
                  <form method="post" action="<?= e(url('/admin/ticket/dosya-turu/toggle')) ?>" style="display:inline;">
                    <?= $csrf ?>
                    <input type="hidden" name="id" value="<?= (int) $ft['id'] ?>">
                    <input type="hidden" name="is_active" value="<?= !empty($ft['is_active']) ? '0' : '1' ?>">
                    <button type="submit" title="Aktif/Pasif"><?= !empty($ft['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                  </form>
                  <form method="post" action="<?= e(url('/admin/ticket/dosya-turu/sil')) ?>" style="display:inline;" onsubmit="return confirm('Silinsin mi?');">
                    <?= $csrf ?><input type="hidden" name="id" value="<?= (int) $ft['id'] ?>">
                    <button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?= e(url('/admin/ticket/dosya-turu')) ?>" style="margin-top:14px;">
            <?= $csrf ?>
            <div class="form-row"><label>Uzantı</label><input name="extension" required maxlength="16" placeholder="png"></div>
            <div class="form-row"><label>MIME</label><input name="mime_type" required maxlength="100" placeholder="image/png"></div>
            <button type="submit" class="btn btn-primary btn-sm">Ekle</button>
          </form>
          <p style="font-size:.75rem;color:var(--ash);margin-top:12px;line-height:1.5;">Yüklemede uzantı + gerçek MIME kontrol edilir; .exe’yi .png diye yüklemek engellenir.</p>
        </div>
      </div>
    </section>

    <!-- ===================== PATCH LİNKLERİ ===================== -->
    <section class="section<?= $panelSection === 'patch-linkleri' ? ' active' : '' ?>" id="patch-linkleri">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>İndirme Linkleri</h3><a href="<?= e(url('/#indir')) ?>" target="_blank">Ana sayfada gör</a></div>
          <table>
            <thead><tr><th>Ad</th><th>Tür</th><th>URL</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($siteDownloads as $dl): ?>
              <tr>
                <td><?= e((string) $dl['title']) ?><div style="font-size:.72rem;color:var(--ash);"><?= e((string) $dl['description']) ?></div></td>
                <td><?= e((string) $dl['pack_type']) ?></td>
                <td style="font-size:.75rem;word-break:break-all;color:var(--ash);"><?= e((string) $dl['url']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-edit-download
                    data-id="<?= (int) $dl['id'] ?>"
                    data-title="<?= e((string) $dl['title']) ?>"
                    data-url="<?= e((string) $dl['url']) ?>"
                    data-description="<?= e((string) $dl['description']) ?>"
                    data-pack="<?= e((string) $dl['pack_type']) ?>"><i class="fa-solid fa-pen"></i></button>
                  <form method="post" action="<?= e(url('/admin/ayarlar/patch/sil')) ?>" style="display:inline;" onsubmit="return confirm('Silinsin mi?');"><?= $csrf ?><input type="hidden" name="id" value="<?= (int)$dl['id'] ?>"><button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button></form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="downloadFormTitle">Yeni Link</h3></div>
          <form method="post" action="<?= e(url('/admin/ayarlar/patch')) ?>" id="downloadForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="downloadId" value="">
            <div class="form-row"><label>Link adı</label><input name="title" id="downloadTitle" required placeholder="Mega Otopack"></div>
            <div class="form-row"><label>URL</label><input name="url" id="downloadUrl" required placeholder="https://..."></div>
            <div class="form-row"><label>Açıklama</label><input name="description" id="downloadDescription" placeholder="Dosya upload / kısa not"></div>
            <div class="form-row"><label>Paket türü</label>
              <select name="pack_type" id="downloadPack">
                <option value="normal">Normal Pack</option>
                <option value="otopack">Otopack</option>
                <option value="lite">Lite</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="downloadReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== ÖZELLİKLER ===================== -->
    <section class="section<?= $panelSection === 'ozellikler-ayarlari' ? ' active' : '' ?>" id="ozellikler-ayarlari">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Özellik Kartları</h3></div>
          <table>
            <thead><tr><th>Başlık</th><th>İkon</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($siteFeatures as $f): ?>
              <tr>
                <td><?= e((string)$f['title']) ?><div style="font-size:.75rem;color:var(--ash);"><?= e((string)$f['body']) ?></div></td>
                <td style="font-size:.75rem;"><?= e((string)$f['icon']) ?></td>
                <td class="actions-cell">
                  <button type="button" data-edit-feature
                    data-id="<?= (int)$f['id'] ?>" data-icon="<?= e((string)$f['icon']) ?>"
                    data-title="<?= e((string)$f['title']) ?>" data-body="<?= e((string)$f['body']) ?>"><i class="fa-solid fa-pen"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="featureFormTitle">Özellik Düzenle / Ekle</h3></div>
          <form method="post" action="<?= e(url('/admin/ayarlar/ozellik')) ?>" id="featureForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="featureId" value="">
            <div class="form-row"><label>İkon (FA class)</label><input name="icon" id="featureIcon" value="fa-solid fa-star"></div>
            <div class="form-row"><label>Başlık</label><input name="title" id="featureTitle" required></div>
            <div class="form-row"><label>Metin</label><textarea name="body" id="featureBody" required style="min-height:80px;"></textarea></div>
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== SINIFLAR ===================== -->
    <section class="section<?= $panelSection === 'siniflar-ayarlari' ? ' active' : '' ?>" id="siniflar-ayarlari">
      <?php foreach ($siteClasses as $cls): ?>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><h3><?= e((string)$cls['name']) ?></h3><span style="font-size:.8rem;color:var(--ash);"><?= e((string)$cls['slug']) ?></span></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/sinif')) ?>" class="grid grid-2">
          <?= $csrf ?>
          <input type="hidden" name="id" value="<?= (int)$cls['id'] ?>">
          <div>
            <div class="form-row"><label>Ad</label><input name="name" value="<?= e((string)$cls['name']) ?>" required></div>
            <div class="form-row"><label>Açıklama</label><textarea name="body" style="min-height:70px;"><?= e((string)$cls['body']) ?></textarea></div>
            <div class="form-row"><label>GIF yolu</label>
              <select name="gif_path">
                <?php
                  $gifs = [
                    'img/classes/warrior_m.gif' => 'Savaşçı Erkek',
                    'img/classes/warrior_f.gif' => 'Savaşçı Kız',
                    'img/classes/ninja_m.gif' => 'Ninja Erkek',
                    'img/classes/ninja_f.gif' => 'Ninja Kız',
                    'img/classes/sura_m.gif' => 'Sura Erkek',
                    'img/classes/sura_f.gif' => 'Sura Kız',
                    'img/classes/shaman_m.gif' => 'Şaman Erkek',
                    'img/classes/shaman_f.gif' => 'Şaman Kız',
                  ];
                  $cur = (string) ($cls['gif_path'] ?? '');
                ?>
                <?php foreach ($gifs as $path => $label): ?>
                  <option value="<?= e($path) ?>"<?= $cur === $path ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div>
            <div class="form-row"><label>İkon</label><input name="icon" value="<?= e((string)$cls['icon']) ?>"></div>
            <div class="form-row"><label>Glow rengi</label><input name="glow_color" value="<?= e((string)$cls['glow_color']) ?>"></div>
            <div class="form-row"><label>Rank glifi</label><input name="rank_glyph" value="<?= e((string)$cls['rank_glyph']) ?>"></div>
            <div class="form-row"><label>Stat 1</label><input name="stat1_label" value="<?= e((string)$cls['stat1_label']) ?>" style="margin-bottom:8px;"><input type="number" name="stat1_value" min="0" max="100" value="<?= (int)$cls['stat1_value'] ?>"></div>
            <div class="form-row"><label>Stat 2</label><input name="stat2_label" value="<?= e((string)$cls['stat2_label']) ?>" style="margin-bottom:8px;"><input type="number" name="stat2_value" min="0" max="100" value="<?= (int)$cls['stat2_value'] ?>"></div>
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
          </div>
        </form>
      </div>
      <?php endforeach; ?>
    </section>

    <!-- ===================== ORANLAR ===================== -->
    <section class="section<?= $panelSection === 'oranlar-ayarlari' ? ' active' : '' ?>" id="oranlar-ayarlari">
      <div class="card" style="max-width:560px;">
        <div class="card-head"><h3>Sunucu Oranları (DNWeb.settings)</h3></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/oranlar')) ?>">
          <?= $csrf ?>
          <div class="form-row"><label>EXP</label><input type="number" name="exp" min="0" value="<?= (int)($siteRates['exp'] ?? 100) ?>"></div>
          <div class="form-row"><label>Drop</label><input type="number" name="drop" min="0" value="<?= (int)($siteRates['drop'] ?? 50) ?>"></div>
          <div class="form-row"><label>Yang</label><input type="number" name="yang" min="0" value="<?= (int)($siteRates['yang'] ?? 30) ?>"></div>
          <div class="form-row"><label>Metin yoğunluğu etiketi</label><input name="metin_label" value="<?= e((string)($siteRates['metin_label'] ?? 'Yüksek')) ?>"></div>
          <div class="form-row"><label>Metin bar %</label><input type="number" name="metin_pct" min="0" max="100" value="<?= (int)($siteRates['metin_pct'] ?? 85) ?>"></div>
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
      </div>
    </section>

    <!-- ===================== SIRADAKI BÖLÜM ===================== -->
    <section class="section<?= $panelSection === 'siradaki-bolum' ? ' active' : '' ?>" id="siradaki-bolum">
      <div class="card" style="max-width:560px;">
        <div class="card-head"><h3>Sıradaki Bölüm</h3></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/bolum')) ?>">
          <?= $csrf ?>
          <div class="form-row"><label>Ne oluyor?</label><input name="title" required value="<?= e((string)($siteChapter['title'] ?? '')) ?>"></div>
          <div class="form-row"><label>Tarih</label><input type="date" name="date" required value="<?= e((string)($siteChapter['date'] ?? '')) ?>"></div>
          <div class="form-row"><label>Saat</label><input type="time" name="time" required value="<?= e((string)($siteChapter['time'] ?? '20:00')) ?>"></div>
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
      </div>
    </section>

    <!-- ===================== GALERİ ===================== -->
    <section class="section<?= $panelSection === 'galeri-ayarlari' ? ' active' : '' ?>" id="galeri-ayarlari">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Galeri</h3></div>
          <table>
            <thead><tr><th>Önizleme</th><th>Başlık</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($siteGallery as $g): ?>
              <tr>
                <td><img src="<?= e((string)$g['image_path']) ?>" alt="" style="width:64px;height:40px;object-fit:cover;"></td>
                <td><?= e((string)$g['title']) ?></td>
                <td class="actions-cell">
                  <form method="post" action="<?= e(url('/admin/ayarlar/galeri/sil')) ?>" onsubmit="return confirm('Silinsin mi?');"><?= $csrf ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button></form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3>Görsel Yükle</h3></div>
          <form method="post" action="<?= e(url('/admin/ayarlar/galeri')) ?>" enctype="multipart/form-data">
            <?= $csrf ?>
            <div class="form-row"><label>Başlık</label><input name="title" placeholder="Yükseliş Vadisi"></div>
            <div class="form-row"><label>Dosya</label><input type="file" name="image" accept="image/*" required></div>
            <button type="submit" class="btn btn-primary btn-sm">Yükle</button>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== LOGO ===================== -->
    <section class="section<?= $panelSection === 'logo-ayarlari' ? ' active' : '' ?>" id="logo-ayarlari">
      <div class="card" style="max-width:720px;">
        <div class="card-head"><h3>Logo &amp; İkon</h3></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/logo')) ?>" enctype="multipart/form-data">
          <?= $csrf ?>
          <div class="grid grid-2" style="margin-bottom:18px;">
            <div>
              <div class="form-row"><label>Logo (anasayfa / geniş)</label>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;padding:12px;border:1px solid var(--line);background:var(--obsidian);">
                  <img src="<?= e($brandLogo) ?>" alt="Logo" style="max-height:56px;max-width:180px;object-fit:contain;">
                  <span style="font-size:.75rem;color:var(--ash);"><?= !empty($siteBrand['has_custom_logo']) ? 'Özel logo' : 'Varsayılan tema logosu' ?></span>
                </div>
                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,.svg">
                <?php if (!empty($siteBrand['has_custom_logo'])): ?>
                <label style="display:flex;align-items:center;gap:8px;margin-top:10px;text-transform:none;letter-spacing:0;cursor:pointer;font-size:.82rem;color:var(--ash);">
                  <input type="checkbox" name="remove_logo" value="1" style="width:auto;"> Özel logoyu kaldır (varsayılana dön)
                </label>
                <?php endif; ?>
              </div>
            </div>
            <div>
              <div class="form-row"><label>İkon / Favicon (paneller)</label>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;padding:12px;border:1px solid var(--line);background:var(--obsidian);">
                  <img src="<?= e($brandIcon) ?>" alt="İkon" style="height:48px;width:48px;object-fit:contain;">
                  <span style="font-size:.75rem;color:var(--ash);"><?= !empty($siteBrand['has_custom_icon']) ? 'Özel ikon' : 'Varsayılan tema ikonu' ?></span>
                </div>
                <input type="file" name="icon" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,image/x-icon,.ico,.svg">
                <?php if (!empty($siteBrand['has_custom_icon'])): ?>
                <label style="display:flex;align-items:center;gap:8px;margin-top:10px;text-transform:none;letter-spacing:0;cursor:pointer;font-size:.82rem;color:var(--ash);">
                  <input type="checkbox" name="remove_icon" value="1" style="width:auto;"> Özel ikonu kaldır (varsayılana dön)
                </label>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="card-head" style="margin-top:8px;"><h3 style="font-size:.95rem;">Boyutlar (px)</h3></div>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px;">
            <div class="form-row"><label>Ana sayfa logo yüksekliği</label><input type="number" name="home_size" min="16" max="160" value="<?= (int) ($siteBrand['home_size'] ?? 48) ?>"></div>
            <div class="form-row"><label>Kullanıcı paneli ikon</label><input type="number" name="user_size" min="16" max="120" value="<?= (int) ($siteBrand['user_size'] ?? 36) ?>"></div>
            <div class="form-row"><label>Admin paneli ikon</label><input type="number" name="admin_size" min="16" max="120" value="<?= (int) ($siteBrand['admin_size'] ?? 36) ?>"></div>
            <div class="form-row"><label>Nesne Market logo</label><input type="number" name="market_size" min="12" max="80" value="<?= (int) ($siteBrand['market_size'] ?? 22) ?>"></div>
            <div class="form-row"><label>Mail bildirimi logo (genişlik)</label><input type="number" name="mail_size" min="40" max="320" value="<?= (int) ($siteBrand['mail_size'] ?? 160) ?>"></div>
            <div class="form-row"><label>Şifre sıfırlama logo (yükseklik)</label><input type="number" name="reset_size" min="24" max="160" value="<?= (int) ($siteBrand['reset_size'] ?? 48) ?>"></div>
          </div>
          <p style="font-size:.72rem;color:var(--ash);margin:4px 0 0;">Mail: <code>{{logo}}</code> / <code>{{logo_width}}</code> (40–320 px). Şifre sıfırlama sayfası logo yüksekliği (24–160 px).</p>
          <?php
            $mailLogoPreview = \App\Services\MailService::logoUrl();
            $mailLogoW = max(40, min(320, (int) ($siteBrand['mail_size'] ?? 160)));
            $resetLogoPreview = (string) ($siteBrand['logo_url'] ?? $brandLogo);
            $resetLogoH = max(24, min(160, (int) ($siteBrand['reset_size'] ?? 48)));
          ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 0 4px;">
            <div style="display:flex;align-items:center;gap:14px;padding:12px;border:1px solid var(--line);background:var(--obsidian);">
              <img src="<?= e($mailLogoPreview) ?>" alt="Mail logo" width="<?= $mailLogoW ?>" style="max-width:<?= $mailLogoW ?>px;height:auto;display:block;object-fit:contain;">
              <span style="font-size:.75rem;color:var(--ash);">Mail · <?= $mailLogoW ?>px</span>
            </div>
            <div style="display:flex;align-items:center;gap:14px;padding:12px;border:1px solid var(--line);background:var(--obsidian);">
              <img src="<?= e($resetLogoPreview) ?>" alt="Şifre sıfırlama logo" style="max-height:<?= $resetLogoH ?>px;width:auto;display:block;object-fit:contain;">
              <span style="font-size:.75rem;color:var(--ash);">Şifre sıfırlama · <?= $resetLogoH ?>px</span>
            </div>
          </div>
          <div class="card-head" style="margin-top:18px;"><h3 style="font-size:.95rem;">Nesne Market logosu</h3></div>
          <div class="form-row" style="margin-bottom:12px;">
            <label>Özel market logosu (boşsa ana logo kullanılır)</label>
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;padding:12px;border:1px solid var(--line);background:var(--obsidian);">
              <img src="<?= e((string) ($siteBrand['market_logo_url'] ?? $brandLogo)) ?>" alt="Market Logo" style="max-height:<?= max(16, min(80, (int) ($siteBrand['market_size'] ?? 22))) ?>px;max-width:180px;object-fit:contain;">
              <span style="font-size:.75rem;color:var(--ash);"><?= !empty($siteBrand['has_custom_market_logo']) ? 'Özel market logosu' : 'Ana logo / varsayılan' ?></span>
            </div>
            <input type="file" name="market_logo" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,.svg">
            <?php if (!empty($siteBrand['has_custom_market_logo'])): ?>
            <label style="display:flex;align-items:center;gap:8px;margin-top:10px;text-transform:none;letter-spacing:0;cursor:pointer;font-size:.82rem;color:var(--ash);">
              <input type="checkbox" name="remove_market_logo" value="1" style="width:auto;"> Özel market logosunu kaldır
            </label>
            <?php endif; ?>
          </div>
          <p style="font-size:.75rem;color:var(--ash);margin:4px 0 16px;">Boş bırakılan yüklemeler mevcut dosyayı korur. Kaldır seçenekleri varsayılan tema görsellerine döner.</p>
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
      </div>
    </section>

    <!-- ===================== NESNE MARKET KATEGORİLER ===================== -->
    <section class="section<?= $panelSection === 'nesne-market-kategoriler' ? ' active' : '' ?>" id="nesne-market-kategoriler">
      <?php
        $marketCategories = isset($marketCategories) && is_array($marketCategories) ? $marketCategories : [];
      ?>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head">
            <h3>Kategoriler</h3>
            <span style="font-size:.8rem;color:var(--ash);"><?= count($marketCategories) ?> kayıt</span>
          </div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Aktif kategoriler Nesne Market sol menüsünde (Tümü’den sonra) sıralama ile görünür. İkon: Font Awesome (örn. <code>fa-solid fa-khanda</code>).</p>
          <table>
            <thead><tr><th>Sıra</th><th>İkon</th><th>Ad</th><th>Slug</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($marketCategories === []): ?>
                <tr><td colspan="6" style="color:var(--ash);">Kategori yok.</td></tr>
              <?php else: ?>
                <?php foreach ($marketCategories as $mc): ?>
                <tr>
                  <td><?= (int) $mc['sort_order'] ?></td>
                  <td><i class="<?= e((string) $mc['icon']) ?>" style="color:var(--gold-light);"></i></td>
                  <td><?= e((string) $mc['name']) ?></td>
                  <td><code><?= e((string) $mc['slug']) ?></code></td>
                  <td><?= !empty($mc['is_active']) ? '<span class="badge ok">Aktif</span>' : '<span class="badge ban">Pasif</span>' ?></td>
                  <td class="actions-cell">
                    <button type="button" title="Düzenle"
                      data-edit-market-cat
                      data-id="<?= (int) $mc['id'] ?>"
                      data-name="<?= e((string) $mc['name']) ?>"
                      data-slug="<?= e((string) $mc['slug']) ?>"
                      data-icon="<?= e((string) $mc['icon']) ?>"
                      data-sort="<?= (int) $mc['sort_order'] ?>"
                      data-active="<?= !empty($mc['is_active']) ? '1' : '0' ?>"
                    ><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/nesne-market/kategori/toggle')) ?>" style="display:inline;">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $mc['id'] ?>">
                      <input type="hidden" name="is_active" value="<?= !empty($mc['is_active']) ? '0' : '1' ?>">
                      <button type="submit" title="Aç/Kapat"><?= !empty($mc['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                    </form>
                    <form method="post" action="<?= e(url('/admin/nesne-market/kategori/sil')) ?>" style="display:inline;" onsubmit="return confirm('Kategori silinsin mi?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $mc['id'] ?>">
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
          <div class="card-head"><h3 id="marketCatFormTitle">Yeni Kategori</h3></div>
          <form method="post" action="<?= e(url('/admin/nesne-market/kategori/kaydet')) ?>" id="marketCatForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="marketCatId" value="">
            <div class="form-row"><label>Ad</label><input name="name" id="marketCatName" required maxlength="120" placeholder="Örn. Silah"></div>
            <div class="form-row"><label>Slug (boşsa addan üretilir)</label><input name="slug" id="marketCatSlug" maxlength="40" placeholder="silah"></div>
            <div class="form-row">
              <label>İkon (Font Awesome)</label>
              <div class="icon-pick-row">
                <span class="icon-pick-preview" id="marketCatIconPreview" title="Önizleme"><i class="fa-solid fa-box"></i></span>
                <input name="icon" id="marketCatIcon" required maxlength="80" value="fa-solid fa-box" placeholder="fa-solid fa-khanda" autocomplete="off">
              </div>
              <label class="icon-pick-toggle" style="margin-top:10px;">
                <input type="checkbox" id="marketCatIconPickToggle" style="width:auto;"> İkonları göster ve seç
              </label>
              <input type="search" id="marketCatIconSearch" class="icon-pick-search" placeholder="İkon ara (örn. khanda, horse)…" autocomplete="off">
              <div class="icon-pick-grid" id="marketCatIconGrid" aria-label="İkon seçici"></div>
            </div>
            <div class="grid grid-2">
              <div class="form-row"><label>Sıra</label><input type="number" name="sort_order" id="marketCatSort" value="0" min="0"></div>
              <div class="form-row"><label><input type="checkbox" name="is_active" id="marketCatActive" value="1" checked> Aktif</label></div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="marketCatReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== NESNE MARKET ÜRÜNLER ===================== -->
    <section class="section<?= $panelSection === 'nesne-market-urunler' ? ' active' : '' ?>" id="nesne-market-urunler">
      <?php
        $marketItems = isset($marketItems) && is_array($marketItems) ? $marketItems : [];
        $marketCategories = isset($marketCategories) && is_array($marketCategories) ? $marketCategories : [];
        $marketItemNextSort = isset($marketItemNextSort) ? (int) $marketItemNextSort : 1;
        if ($marketItemNextSort < 1) {
            $marketItemNextSort = 1;
        }
        $marketItemQ = isset($marketItemQ) ? (string) $marketItemQ : '';
        $marketItemCat = isset($marketItemCat) ? (int) $marketItemCat : 0;
      ?>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head">
            <h3>Ürünler</h3>
            <span style="font-size:.8rem;color:var(--ash);"><?= count($marketItems) ?> kayıt</span>
          </div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Aktif ürünler Nesne Market’te görünür. Görsel: m2icondb linki veya yükleme (<code>/uploads/market/</code>). İndirim aktifken fiyat yüzde olarak düşer.</p>
          <form class="filters" method="get" action="<?= e(url('/admin')) ?>" style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="section" value="nesne-market-urunler">
            <input name="market_item_q" value="<?= e($marketItemQ) ?>" placeholder="Ad veya item kodu ara…" style="flex:1;min-width:180px;">
            <select name="market_item_cat" style="min-width:140px;">
              <option value="0">Tüm kategoriler</option>
              <?php foreach ($marketCategories as $mc): ?>
                <option value="<?= (int) $mc['id'] ?>"<?= $marketItemCat === (int) $mc['id'] ? ' selected' : '' ?>><?= e((string) $mc['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
            <?php if ($marketItemQ !== '' || $marketItemCat > 0): ?>
              <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-urunler')) ?>">Temizle</a>
            <?php endif; ?>
          </form>
          <table>
            <thead><tr><th>Sıra</th><th></th><th>Kod</th><th>Ad</th><th>Kategori</th><th>Süre</th><th>Fiyat</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($marketItems === []): ?>
                <tr><td colspan="9" style="color:var(--ash);"><?= ($marketItemQ !== '' || $marketItemCat > 0) ? 'Filtreye uyan ürün yok.' : 'Henüz ürün yok. Sağdaki formdan ekleyebilirsin.' ?></td></tr>
              <?php else: ?>
                <?php foreach ($marketItems as $mi): ?>
                <tr>
                  <td><?= (int) $mi['sort_order'] ?></td>
                  <td>
                    <?php if ((string) ($mi['image_url'] ?? '') !== ''): ?>
                      <img src="<?= e((string) $mi['image_url']) ?>" alt="" referrerpolicy="no-referrer" style="width:28px;height:28px;object-fit:contain;image-rendering:pixelated;">
                    <?php else: ?>
                      <i class="fa-solid fa-box" style="color:var(--ash);"></i>
                    <?php endif; ?>
                  </td>
                  <td><code><?= e((string) ($mi['item_code'] ?? '')) ?></code></td>
                  <td><?= e((string) $mi['name']) ?></td>
                  <td><?= e((string) ($mi['category_name'] !== '' ? $mi['category_name'] : '—')) ?></td>
                  <td><?= ((string) ($mi['duration_type'] ?? '') === 'timed') ? 'Süreli' : 'Süresiz' ?></td>
                  <td>
                    <?= number_format((int) $mi['sale_price'], 0, ',', '.') ?>
                    <?php if (!empty($mi['old_price'])): ?>
                      <span style="text-decoration:line-through;color:var(--ash);font-size:.75rem;margin-left:4px;"><?= number_format((int) $mi['old_price'], 0, ',', '.') ?></span>
                      <span class="badge" style="margin-left:4px;">%<?= (int) $mi['discount_percent'] ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= !empty($mi['is_active']) ? '<span class="badge ok">Aktif</span>' : '<span class="badge ban">Pasif</span>' ?></td>
                  <td class="actions-cell">
                    <button type="button" title="Düzenle"
                      data-edit-market-item
                      data-id="<?= (int) $mi['id'] ?>"
                      data-code="<?= e((string) ($mi['item_code'] ?? '')) ?>"
                      data-name="<?= e((string) $mi['name']) ?>"
                      data-desc="<?= e((string) $mi['description']) ?>"
                      data-price="<?= (int) $mi['price'] ?>"
                      data-discount-active="<?= !empty($mi['discount_active']) ? '1' : '0' ?>"
                      data-discount-percent="<?= (int) $mi['discount_percent'] ?>"
                      data-image="<?= e((string) $mi['image_url']) ?>"
                      data-duration="<?= e((string) ($mi['duration_type'] ?? 'permanent')) ?>"
                      data-category="<?= (int) $mi['category_id'] ?>"
                      data-sort="<?= (int) $mi['sort_order'] ?>"
                      data-active="<?= !empty($mi['is_active']) ? '1' : '0' ?>"
                    ><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/nesne-market/urun/toggle')) ?>" style="display:inline;">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $mi['id'] ?>">
                      <input type="hidden" name="is_active" value="<?= !empty($mi['is_active']) ? '0' : '1' ?>">
                      <button type="submit" title="Aç/Kapat"><?= !empty($mi['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                    </form>
                    <form method="post" action="<?= e(url('/admin/nesne-market/urun/sil')) ?>" style="display:inline;" onsubmit="return confirm('Ürün silinsin mi?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $mi['id'] ?>">
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
          <div class="card-head"><h3 id="marketItemFormTitle">Yeni Ürün</h3></div>
          <form method="post" action="<?= e(url('/admin/nesne-market/urun/kaydet')) ?>" id="marketItemForm" enctype="multipart/form-data">
            <?= $csrf ?>
            <input type="hidden" name="id" id="marketItemId" value="">
            <div class="form-row"><label>Item kodu</label><input name="item_code" id="marketItemCode" required maxlength="32" placeholder="Örn. 10 veya 00010" autocomplete="off"></div>
            <div class="form-row"><label>Ürün adı</label><input name="name" id="marketItemName" required maxlength="160" placeholder="Örn. Kılıç+0"></div>
            <div class="form-row"><label>Açıklama</label><textarea name="description" id="marketItemDesc" rows="3" placeholder="Ürün açıklaması"></textarea></div>
            <div class="form-row">
              <label>Kategori</label>
              <select name="category_id" id="marketItemCategory" required>
                <option value="">Seçin…</option>
                <?php foreach ($marketCategories as $mc): ?>
                  <option value="<?= (int) $mc['id'] ?>"><?= e((string) $mc['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="grid grid-2">
              <div class="form-row"><label>Fiyat (Elmas)</label><input type="number" name="price" id="marketItemPrice" value="0" min="0" required></div>
              <div class="form-row"><label>Sıra</label><input type="number" name="sort_order" id="marketItemSort" value="<?= $marketItemNextSort ?>" min="0"></div>
            </div>
            <div class="form-row">
              <label>Süre</label>
              <select name="duration_type" id="marketItemDuration">
                <option value="permanent">Süresiz</option>
                <option value="timed">Süreli</option>
              </select>
            </div>
            <div class="form-row"><label><input type="checkbox" name="discount_active" id="marketItemDiscountActive" value="1"> İndirim aktif</label></div>
            <div class="form-row" id="marketItemDiscountRow" style="display:none;">
              <label>İndirim tutarı (%)</label>
              <input type="number" name="discount_percent" id="marketItemDiscountPercent" value="0" min="0" max="100">
            </div>
            <div class="form-row">
              <label>Item resmi (URL)</label>
              <input name="image_url" id="marketItemImageUrl" maxlength="500" placeholder="https://img.m2icondb.com/00010.png">
              <div id="marketItemImagePreview" style="margin-top:8px;min-height:32px;"></div>
            </div>
            <div class="form-row">
              <label>veya yükle (png/jpg/webp/gif, max 3MB)</label>
              <input type="file" name="image_file" id="marketItemImageFile" accept="image/png,image/jpeg,image/webp,image/gif">
            </div>
            <div class="form-row"><label><input type="checkbox" name="is_active" id="marketItemActive" value="1" checked> Aktif</label></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="marketItemReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== NESNE MARKET SATIŞ LOGLARI ===================== -->
    <section class="section<?= $panelSection === 'nesne-market-satis-loglari' ? ' active' : '' ?>" id="nesne-market-satis-loglari">
      <?php
        $marketSales = isset($marketSales) && is_array($marketSales) ? $marketSales : ['logs' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20, 'q' => ''];
        $saleLogs = is_array($marketSales['logs'] ?? null) ? $marketSales['logs'] : [];
        $saleQ = (string) ($marketSales['q'] ?? '');
        $salePage = (int) ($marketSales['page'] ?? 1);
        $salePages = (int) ($marketSales['pages'] ?? 1);
        $saleTotal = (int) ($marketSales['total'] ?? 0);
      ?>
      <div class="card">
        <div class="card-head">
          <h3>Satış Logları</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= $saleTotal ?> kayıt</span>
        </div>
        <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Satış ve kupon kullanımları. Kupon satırlarında tür <b>Kupon</b> yazar; fiyat = eklenen Elmas.</p>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>" style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <input type="hidden" name="section" value="nesne-market-satis-loglari">
          <input name="market_sale_q" value="<?= e($saleQ) ?>" placeholder="Hesap, item, KUPON veya tam kupon kodu…" style="flex:1;min-width:200px;">
          <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
          <?php if ($saleQ !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-satis-loglari')) ?>">Temizle</a>
          <?php endif; ?>
        </form>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Tür</th>
              <th>Tarih</th>
              <th>Hesap</th>
              <th>Ürün / Kupon</th>
              <th>Kod</th>
              <th>Fiyat / Elmas</th>
              <th>Cash önce</th>
              <th>Cash sonra</th>
              <th>Depo pos</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($saleLogs === []): ?>
              <tr><td colspan="11" style="color:var(--ash);">Henüz kayıt yok.</td></tr>
            <?php else: ?>
              <?php foreach ($saleLogs as $sl): ?>
              <?php $isCoupon = (($sl['entry_type'] ?? '') === 'coupon'); ?>
              <tr>
                <td><?= (int) $sl['id'] ?></td>
                <td><?php if ($isCoupon): ?><span class="badge active">Kupon</span><?php else: ?><span class="badge">Satış</span><?php endif; ?></td>
                <td style="white-space:nowrap;font-size:.78rem;"><?= e((string) $sl['created_at']) ?></td>
                <td>
                  <button type="button" class="linkish" data-detail="<?= (int) $sl['account_id'] ?>" style="background:none;border:none;color:var(--gold-light);padding:0;font:inherit;cursor:pointer;text-decoration:underline;"><?= e((string) $sl['account_login']) ?></button>
                  <span style="color:var(--ash);font-size:.72rem;">#<?= (int) $sl['account_id'] ?></span>
                </td>
                <td><?= e((string) $sl['item_name']) ?></td>
                <td><code><?= e((string) $sl['item_code']) ?></code></td>
                <td><?= $isCoupon ? '+' : '' ?><?= number_format((int) $sl['price'], 0, ',', '.') ?></td>
                <td><?= number_format((int) $sl['cash_before'], 0, ',', '.') ?></td>
                <td><?= number_format((int) $sl['cash_after'], 0, ',', '.') ?></td>
                <td><?= $isCoupon ? '—' : (int) $sl['safebox_pos'] ?></td>
                <td style="font-size:.75rem;"><?= e((string) $sl['ip']) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php if ($salePages > 1): ?>
          <div class="modal-pager" style="margin-top:14px;">
            <span>Sayfa <?= $salePage ?> / <?= $salePages ?></span>
            <div class="links">
              <?php if ($salePage > 1): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-satis-loglari&market_sale_q=' . rawurlencode($saleQ) . '&market_sale_page=' . ($salePage - 1))) ?>">Önceki</a>
              <?php endif; ?>
              <?php if ($salePage < $salePages): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-satis-loglari&market_sale_q=' . rawurlencode($saleQ) . '&market_sale_page=' . ($salePage + 1))) ?>">Sonraki</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== NESNE MARKET KUPONLAR ===================== -->
    <section class="section<?= $panelSection === 'nesne-market-kuponlar' ? ' active' : '' ?>" id="nesne-market-kuponlar">
      <?php
        $couponCats = isset($marketCouponCategories) && is_array($marketCouponCategories) ? $marketCouponCategories : [];
        $couponPack = isset($marketCoupons) && is_array($marketCoupons) ? $marketCoupons : ['coupons' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'q' => '', 'status' => '', 'category_id' => 0];
        $couponRows = is_array($couponPack['coupons'] ?? null) ? $couponPack['coupons'] : [];
        $genCodes = isset($couponGeneratedCodes) && is_array($couponGeneratedCodes) ? $couponGeneratedCodes : [];
      ?>
      <div class="grid grid-2" style="margin-bottom:18px;align-items:start;">
        <div class="card">
          <div class="card-head"><h3>Kupon kategorisi</h3></div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Count = aktif edilince hesaba eklenecek Elmas (`account.cash`).</p>
          <form method="post" action="<?= e(url('/admin/nesne-market/kupon-kategori/kaydet')) ?>">
            <?= $csrf ?>
            <input type="hidden" name="id" id="couponCatId" value="0">
            <div class="form-row"><label>Ad</label><input name="name" id="couponCatName" required maxlength="120" placeholder="örn. 100 Elmas"></div>
            <div class="form-row"><label>Count (Elmas)</label><input name="cash_amount" id="couponCatCash" type="number" min="1" max="100000000" required value="100"></div>
            <div class="form-row"><label>Sıra</label><input name="sort_order" id="couponCatSort" type="number" value="0"></div>
            <div class="form-row"><label><input type="checkbox" name="is_active" id="couponCatActive" value="1" checked> Aktif</label></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="couponCatReset">Temizle</button>
            </div>
          </form>
          <table style="margin-top:16px;">
            <thead><tr><th>Ad</th><th>Count</th><th>Kupon</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($couponCats === []): ?>
                <tr><td colspan="5" style="color:var(--ash);">Kategori yok.</td></tr>
              <?php else: foreach ($couponCats as $cc): ?>
                <tr>
                  <td><?= e((string) $cc['name']) ?></td>
                  <td><?= number_format((int) $cc['cash_amount'], 0, ',', '.') ?></td>
                  <td><?= (int) ($cc['unused_count'] ?? 0) ?> / <?= (int) ($cc['coupon_count'] ?? 0) ?></td>
                  <td><?= !empty($cc['is_active']) ? 'Aktif' : 'Pasif' ?></td>
                  <td style="white-space:nowrap;">
                    <button type="button" class="btn btn-ghost btn-sm coupon-cat-edit"
                      data-id="<?= (int) $cc['id'] ?>"
                      data-name="<?= e((string) $cc['name']) ?>"
                      data-cash="<?= (int) $cc['cash_amount'] ?>"
                      data-sort="<?= (int) $cc['sort_order'] ?>"
                      data-active="<?= !empty($cc['is_active']) ? '1' : '0' ?>">Düzenle</button>
                    <form method="post" action="<?= e(url('/admin/nesne-market/kupon-kategori/sil')) ?>" style="display:inline;" onsubmit="return confirm('Kategori silinsin mi?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $cc['id'] ?>">
                      <button type="submit" class="btn btn-ghost btn-sm">Sil</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-head"><h3>Kod oluştur</h3></div>
          <p style="font-size:.8rem;color:var(--ash);margin-bottom:12px;">Format: <code>XXX-YYY-ZZZ-DDD-FFF-RRR-AAA</code>. DB’de yalnızca SHA-256 hash saklanır.</p>
          <form method="post" action="<?= e(url('/admin/nesne-market/kupon/olustur')) ?>" onsubmit="return confirm('Seçilen adette kupon oluşturulsun mu?');">
            <?= $csrf ?>
            <div class="form-row">
              <label>Kategori</label>
              <select name="category_id" required>
                <option value="">Seç…</option>
                <?php foreach ($couponCats as $cc): if (empty($cc['is_active'])) continue; ?>
                  <option value="<?= (int) $cc['id'] ?>"><?= e((string) $cc['name']) ?> (+<?= number_format((int) $cc['cash_amount'], 0, ',', '.') ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row"><label>Kaç adet?</label><input name="quantity" type="number" min="1" max="500" required value="10"></div>
            <button type="submit" class="btn btn-jade btn-sm"><i class="fa-solid fa-plus"></i> Kupon oluştur</button>
          </form>
          <?php if ($genCodes !== []): ?>
            <div style="margin-top:16px;padding:12px;border:1px solid rgba(201,151,74,.25);border-radius:8px;background:rgba(0,0,0,.2);">
              <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;margin-bottom:8px;">
                <strong style="font-size:.85rem;">Yeni kodlar (bir kez gösterilir)</strong>
                <button type="button" class="btn btn-ghost btn-sm" id="copyCouponCodes">Kopyala</button>
              </div>
              <textarea id="couponCodesOut" readonly rows="<?= min(12, max(4, count($genCodes))) ?>" style="width:100%;font-family:ui-monospace,monospace;font-size:.78rem;"><?= e(implode("\n", $genCodes)) ?></textarea>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Kupon listesi</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= (int) ($couponPack['total'] ?? 0) ?> kayıt</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>" style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <input type="hidden" name="section" value="nesne-market-kuponlar">
          <input name="coupon_q" value="<?= e((string) ($couponPack['q'] ?? '')) ?>" placeholder="Tam kod, maske, hesap, id…" style="flex:1;min-width:160px;">
          <select name="coupon_status">
            <option value=""<?= ($couponPack['status'] ?? '') === '' ? ' selected' : '' ?>>Tümü</option>
            <option value="unused"<?= ($couponPack['status'] ?? '') === 'unused' ? ' selected' : '' ?>>Kullanılmadı</option>
            <option value="used"<?= ($couponPack['status'] ?? '') === 'used' ? ' selected' : '' ?>>Kullanıldı</option>
          </select>
          <select name="coupon_cat">
            <option value="0">Tüm kategoriler</option>
            <?php foreach ($couponCats as $cc): ?>
              <option value="<?= (int) $cc['id'] ?>"<?= (int) ($couponPack['category_id'] ?? 0) === (int) $cc['id'] ? ' selected' : '' ?>><?= e((string) $cc['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-ghost btn-sm">Filtrele</button>
        </form>

        <form method="post" action="<?= e(url('/admin/nesne-market/kupon/sil')) ?>" id="couponBulkForm" onsubmit="return confirm('Seçili kuponlar silinsin mi?');">
          <?= $csrf ?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;align-items:center;">
            <label style="font-size:.82rem;"><input type="checkbox" id="couponSelectAll"> Hepsini seç</label>
            <button type="submit" class="btn btn-ghost btn-sm">Seçilenleri sil</button>
          </div>
          <table>
            <thead>
              <tr>
                <th></th>
                <th>#</th>
                <th>Maske</th>
                <th>Kategori</th>
                <th>Elmas</th>
                <th>Durum</th>
                <th>Hesap</th>
                <th>Oluşturma</th>
                <th>Kullanım</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($couponRows === []): ?>
                <tr><td colspan="9" style="color:var(--ash);">Kupon yok.</td></tr>
              <?php else: foreach ($couponRows as $cr): ?>
                <tr>
                  <td><input type="checkbox" name="ids[]" value="<?= (int) $cr['id'] ?>" class="coupon-row-check"></td>
                  <td><?= (int) $cr['id'] ?></td>
                  <td><code><?= e((string) $cr['code_mask']) ?></code></td>
                  <td><?= e((string) $cr['category_name']) ?></td>
                  <td><?= number_format((int) $cr['cash_amount'], 0, ',', '.') ?></td>
                  <td><?= !empty($cr['is_used']) ? '<span class="badge banned">Kullanıldı</span>' : '<span class="badge active">Kullanılmadı</span>' ?></td>
                  <td>
                    <?php if (!empty($cr['is_used']) && (int) $cr['used_account_id'] > 0): ?>
                      <button type="button" class="linkish" data-detail="<?= (int) $cr['used_account_id'] ?>" style="background:none;border:none;color:var(--gold-light);padding:0;font:inherit;cursor:pointer;text-decoration:underline;"><?= e((string) $cr['used_account_login']) ?></button>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.78rem;white-space:nowrap;"><?= e((string) $cr['created_at']) ?></td>
                  <td style="font-size:.78rem;white-space:nowrap;"><?= e((string) ($cr['used_at'] ?: '—')) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </form>
        <?php
          $cPage = (int) ($couponPack['page'] ?? 1);
          $cPages = (int) ($couponPack['pages'] ?? 1);
          $cQ = rawurlencode((string) ($couponPack['q'] ?? ''));
          $cSt = rawurlencode((string) ($couponPack['status'] ?? ''));
          $cCat = (int) ($couponPack['category_id'] ?? 0);
        ?>
        <?php if ($cPages > 1): ?>
          <div class="modal-pager" style="margin-top:14px;">
            <span>Sayfa <?= $cPage ?> / <?= $cPages ?></span>
            <div class="links">
              <?php if ($cPage > 1): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-kuponlar&coupon_q=' . $cQ . '&coupon_status=' . $cSt . '&coupon_cat=' . $cCat . '&coupon_page=' . ($cPage - 1))) ?>">Önceki</a>
              <?php endif; ?>
              <?php if ($cPage < $cPages): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=nesne-market-kuponlar&coupon_q=' . $cQ . '&coupon_status=' . $cSt . '&coupon_cat=' . $cCat . '&coupon_page=' . ($cPage + 1))) ?>">Sonraki</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== WIKI YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'wiki-yonetim' ? ' active' : '' ?>" id="wiki-yonetim">
      <?php
        $wikiContent = isset($wikiContent) && is_array($wikiContent) ? $wikiContent : \App\Services\WikiService::content();
        $wHead = is_array($wikiContent['head'] ?? null) ? $wikiContent['head'] : [];
        $wIntro = is_array($wikiContent['intro'] ?? null) ? $wikiContent['intro'] : [];
        $wIntroCards = is_array($wIntro['cards'] ?? null) ? $wIntro['cards'] : [];
        $wClassSec = is_array($wikiContent['classes_section'] ?? null) ? $wikiContent['classes_section'] : [];
        $wClasses = is_array($wikiContent['classes'] ?? null) ? $wikiContent['classes'] : [];
        $wMapSec = is_array($wikiContent['maps_section'] ?? null) ? $wikiContent['maps_section'] : [];
        $wMaps = is_array($wikiContent['maps'] ?? null) ? $wikiContent['maps'] : [];
        $wMonSec = is_array($wikiContent['monsters_section'] ?? null) ? $wikiContent['monsters_section'] : [];
        $wMonsters = is_array($wikiContent['monsters'] ?? null) ? $wikiContent['monsters'] : [];
        $wMetSec = is_array($wikiContent['metins_section'] ?? null) ? $wikiContent['metins_section'] : [];
        $wMetins = is_array($wikiContent['metins'] ?? null) ? $wikiContent['metins'] : [];
        $wUpSec = is_array($wikiContent['upgrade_section'] ?? null) ? $wikiContent['upgrade_section'] : [];
        $wUpgrade = is_array($wikiContent['upgrade'] ?? null) ? $wikiContent['upgrade'] : [];
        $wClan = is_array($wikiContent['clan'] ?? null) ? $wikiContent['clan'] : [];
        $wFaqSec = is_array($wikiContent['faq_section'] ?? null) ? $wikiContent['faq_section'] : [];
        $wFaq = is_array($wikiContent['faq'] ?? null) ? $wikiContent['faq'] : [];
        $canWikiEdit = $can('wiki_manage') && !\App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null);
        $wikiIconSeq = 0;
        $wikiIconField = static function (string $name, string $value, bool $editable, string $label = 'İkon') use (&$wikiIconSeq): void {
            $wikiIconSeq++;
            $uid = 'wikiIcon' . $wikiIconSeq;
            $value = trim($value);
            if ($value === '') {
                $value = 'fa-solid fa-star';
            }
            ?>
            <div class="form-row wiki-icon-pick" data-wiki-icon-pick>
              <label><?= e($label) ?></label>
              <div class="icon-pick-row">
                <span class="icon-pick-preview" id="<?= e($uid) ?>Preview" title="Önizleme"><i class="<?= e($value) ?>"></i></span>
                <input
                  name="<?= e($name) ?>"
                  id="<?= e($uid) ?>Input"
                  class="wiki-icon-input"
                  value="<?= e($value) ?>"
                  maxlength="80"
                  placeholder="fa-solid fa-..."
                  autocomplete="off"
                  <?= $editable ? '' : ' readonly' ?>
                >
              </div>
              <?php if ($editable): ?>
                <label class="icon-pick-toggle" style="margin-top:10px;">
                  <input type="checkbox" class="wiki-icon-toggle" id="<?= e($uid) ?>Toggle" style="width:auto;"> İkonları göster ve seç
                </label>
                <input type="search" id="<?= e($uid) ?>Search" class="icon-pick-search wiki-icon-search" placeholder="İkon ara (örn. dragon, khanda)…" autocomplete="off">
                <div class="icon-pick-grid wiki-icon-grid" id="<?= e($uid) ?>Grid" aria-label="İkon seçici"></div>
              <?php endif; ?>
            </div>
            <?php
        };
      ?>
      <div class="card" style="max-width:1100px;">
        <div class="card-head">
          <h3>Wiki Yönetimi</h3>
          <a href="<?= e(url('/wiki')) ?>" target="_blank" style="font-size:.8rem;color:var(--gold-light);">Public sayfayı aç</a>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Public: <code>/wiki</code> · Yetki: <strong>Menü: Wiki Yönetimi</strong> + düzenleme için <strong>Wiki içeriği düzenleme</strong>.
          <?php if (!$canWikiEdit): ?>
            <br><span style="color:var(--blood-light);">Bu hesapta wiki kaydetme yetkisi yok (salt görüntüleme).</span>
          <?php endif; ?>
        </p>

        <form method="post" action="<?= e(url('/admin/wiki/kaydet')) ?>" id="wikiManageForm"<?= $canWikiEdit ? '' : ' onsubmit="return false;"' ?>>
          <?= $csrf ?>

          <h4 style="margin:8px 0 12px;color:var(--gold-light);font-size:.9rem;">Sayfa başlığı</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="head_eyebrow" value="<?= e((string) ($wHead['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Arama placeholder</label><input name="head_search" value="<?= e((string) ($wHead['search_placeholder'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Başlık</label><input name="head_title" value="<?= e((string) ($wHead['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          <div class="form-row"><label>Açıklama</label><textarea name="head_lead" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wHead['lead'] ?? '')) ?></textarea></div>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Giriş &amp; temel bilgiler</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="intro_eyebrow" value="<?= e((string) ($wIntro['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="intro_title" value="<?= e((string) ($wIntro['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Metin</label><textarea name="intro_text" rows="3"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wIntro['text'] ?? '')) ?></textarea></div>
          <div class="form-row"><label><input type="checkbox" name="intro_use_live_rates" value="1"<?= !empty($wIntro['use_live_rates']) ? ' checked' : '' ?><?= $canWikiEdit ? '' : ' disabled' ?>> İlk kartta canlı EXP/Drop/Yang oranlarını kullan</label></div>
          <?php foreach ($wIntroCards as $ci => $card): ?>
            <div style="margin-bottom:10px;padding:12px;border:1px solid var(--line);">
              <?php $wikiIconField('intro_card_icon[]', (string) ($card['icon'] ?? 'fa-solid fa-circle-info'), $canWikiEdit, 'Kart ikonu'); ?>
              <div class="grid grid-2">
                <div class="form-row"><label>Kart başlık</label><input name="intro_card_title[]" value="<?= e((string) ($card['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Kart metin</label><input name="intro_card_text[]" value="<?= e((string) ($card['text'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if ($canWikiEdit): ?>
            <div style="margin-bottom:10px;padding:12px;border:1px dashed var(--line);opacity:.9;">
              <?php $wikiIconField('intro_card_icon[]', 'fa-solid fa-plus', true, 'Yeni kart ikonu'); ?>
              <div class="grid grid-2">
                <div class="form-row"><label>Yeni kart başlık</label><input name="intro_card_title[]" placeholder="Boş bırakılırsa eklenmez"></div>
                <div class="form-row"><label>Yeni kart metin</label><input name="intro_card_text[]"></div>
              </div>
            </div>
          <?php endif; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Sınıflar</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="classes_eyebrow" value="<?= e((string) ($wClassSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="classes_title" value="<?= e((string) ($wClassSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Açıklama</label><textarea name="classes_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wClassSec['text'] ?? '')) ?></textarea></div>
          <?php foreach ($wClasses as $ci => $cl): $stats = is_array($cl['stats'] ?? null) ? $cl['stats'] : []; ?>
            <div style="margin-bottom:12px;padding:12px;border:1px solid var(--line);">
              <?php $wikiIconField('class_icon[' . (int) $ci . ']', (string) ($cl['icon'] ?? 'fa-solid fa-khanda'), $canWikiEdit, 'Sınıf ikonu'); ?>
              <div class="grid grid-2">
                <div class="form-row"><label>Ad</label><input name="class_name[<?= (int) $ci ?>]" value="<?= e((string) ($cl['name'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Alt</label><input name="class_sub[<?= (int) $ci ?>]" value="<?= e((string) ($cl['sub'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              </div>
              <div class="form-row"><label>Metin</label><input name="class_text[<?= (int) $ci ?>]" value="<?= e((string) ($cl['text'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="grid grid-3">
                <?php for ($s = 0; $s < 3; $s++): $st = $stats[$s] ?? ['label' => '', 'pct' => 0]; ?>
                  <div class="form-row">
                    <label>Stat <?= $s + 1 ?></label>
                    <input name="class_stat_label[<?= (int) $ci ?>][<?= $s ?>]" value="<?= e((string) ($st['label'] ?? '')) ?>" placeholder="Etiket" style="margin-bottom:4px;"<?= $canWikiEdit ? '' : ' readonly' ?>>
                    <input type="number" min="0" max="100" name="class_stat_pct[<?= (int) $ci ?>][<?= $s ?>]" value="<?= (int) ($st['pct'] ?? 0) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Haritalar</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="maps_eyebrow" value="<?= e((string) ($wMapSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="maps_title" value="<?= e((string) ($wMapSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Açıklama</label><textarea name="maps_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wMapSec['text'] ?? '')) ?></textarea></div>
          <?php foreach ($wMaps as $mi => $mp): ?>
            <div class="grid grid-2" style="margin-bottom:8px;padding:10px;border:1px solid var(--line);">
              <div class="form-row"><label>Tag</label><input name="map_tag[]" value="<?= e((string) ($mp['tag'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Tag sınıfı</label>
                <select name="map_tag_class[]"<?= $canWikiEdit ? '' : ' disabled' ?>>
                  <?php foreach (['pve' => 'PvE', 'pvp' => 'PvP', 'metin' => 'Metin'] as $tc => $tl): ?>
                    <option value="<?= e($tc) ?>"<?= (($mp['tag_class'] ?? '') === $tc) ? ' selected' : '' ?>><?= e($tl) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (!$canWikiEdit): ?><input type="hidden" name="map_tag_class[]" value="<?= e((string) ($mp['tag_class'] ?? 'pve')) ?>"><?php endif; ?>
              </div>
              <div class="form-row"><label>Ad</label><input name="map_title[]" value="<?= e((string) ($mp['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Seviye</label><input name="map_level[]" value="<?= e((string) ($mp['level'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row" style="grid-column:1/-1;"><label>Metin</label><input name="map_text[]" value="<?= e((string) ($mp['text'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            </div>
          <?php endforeach; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Canavarlar</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="monsters_eyebrow" value="<?= e((string) ($wMonSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="monsters_title" value="<?= e((string) ($wMonSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Açıklama</label><textarea name="monsters_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wMonSec['text'] ?? '')) ?></textarea></div>
          <?php foreach ($wMonsters as $mo): ?>
            <div style="margin-bottom:8px;padding:10px;border:1px solid var(--line);">
              <?php $wikiIconField('monster_icon[]', (string) ($mo['icon'] ?? 'fa-solid fa-paw'), $canWikiEdit, 'Canavar ikonu'); ?>
              <div class="grid grid-3">
                <div class="form-row"><label>Ad</label><input name="monster_name[]" value="<?= e((string) ($mo['name'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Rozet</label><input name="monster_badge[]" value="<?= e((string) ($mo['boss_badge'] ?? '')) ?>" placeholder="Boss / boş"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Seviye</label><input name="monster_level[]" value="<?= e((string) ($mo['level'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Harita</label><input name="monster_map[]" value="<?= e((string) ($mo['map'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
                <div class="form-row"><label>Can %</label><input type="number" min="0" max="100" name="monster_hp[]" value="<?= (int) ($mo['hp_pct'] ?? 50) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              </div>
              <div class="form-row"><label>Düşenler (virgülle)</label><input name="monster_drops[]" value="<?= e(implode(', ', is_array($mo['drops'] ?? null) ? $mo['drops'] : [])) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            </div>
          <?php endforeach; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Metin taşları</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="metins_eyebrow" value="<?= e((string) ($wMetSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="metins_title" value="<?= e((string) ($wMetSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Açıklama</label><textarea name="metins_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wMetSec['text'] ?? '')) ?></textarea></div>
          <?php foreach ($wMetins as $me): ?>
            <div class="grid grid-2" style="margin-bottom:8px;padding:10px;border:1px solid var(--line);">
              <div class="form-row"><label>Stil</label>
                <select name="metin_style[]"<?= $canWikiEdit ? '' : ' disabled' ?>>
                  <?php foreach (['red' => 'Kızıl', 'black' => 'Kara', 'gold' => 'Altın'] as $ms => $ml): ?>
                    <option value="<?= e($ms) ?>"<?= (($me['style'] ?? '') === $ms) ? ' selected' : '' ?>><?= e($ml) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (!$canWikiEdit): ?><input type="hidden" name="metin_style[]" value="<?= e((string) ($me['style'] ?? 'red')) ?>"><?php endif; ?>
              </div>
              <div class="form-row"><label>Glyph</label><input name="metin_glyph[]" value="<?= e((string) ($me['glyph'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Başlık</label><input name="metin_title[]" value="<?= e((string) ($me['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Metin</label><input name="metin_text[]" value="<?= e((string) ($me['text'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            </div>
          <?php endforeach; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Eşya yükseltme</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="upgrade_eyebrow" value="<?= e((string) ($wUpSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="upgrade_title" value="<?= e((string) ($wUpSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Açıklama</label><textarea name="upgrade_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wUpSec['text'] ?? '')) ?></textarea></div>
          <?php foreach ($wUpgrade as $up): ?>
            <div class="grid grid-2" style="margin-bottom:8px;padding:10px;border:1px solid var(--line);">
              <div class="form-row"><label>Seviye</label><input name="upgrade_level[]" value="<?= e((string) ($up['level'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Oran</label><input name="upgrade_rate[]" value="<?= e((string) ($up['rate'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Oran rengi</label>
                <select name="upgrade_rate_class[]"<?= $canWikiEdit ? '' : ' disabled' ?>>
                  <?php foreach (['rate-high' => 'Yüksek', 'rate-mid' => 'Orta', 'rate-low' => 'Düşük'] as $rc => $rl): ?>
                    <option value="<?= e($rc) ?>"<?= (($up['rate_class'] ?? '') === $rc) ? ' selected' : '' ?>><?= e($rl) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (!$canWikiEdit): ?><input type="hidden" name="upgrade_rate_class[]" value="<?= e((string) ($up['rate_class'] ?? 'rate-mid')) ?>"><?php endif; ?>
              </div>
              <div class="form-row"><label>Materyal</label><input name="upgrade_material[]" value="<?= e((string) ($up['material'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row" style="grid-column:1/-1;"><label>Kırılma riski</label><input name="upgrade_risk[]" value="<?= e((string) ($up['risk'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            </div>
          <?php endforeach; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">Lonca</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="clan_eyebrow" value="<?= e((string) ($wClan['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="clan_title" value="<?= e((string) ($wClan['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <div class="form-row"><label>Metin</label><textarea name="clan_text" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($wClan['text'] ?? '')) ?></textarea></div>
          <?php foreach ((is_array($wClan['stats'] ?? null) ? $wClan['stats'] : []) as $st): ?>
            <div class="grid grid-2" style="margin-bottom:6px;">
              <div class="form-row"><label>Değer</label><input name="clan_stat_value[]" value="<?= e((string) ($st['value'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Etiket</label><input name="clan_stat_label[]" value="<?= e((string) ($st['label'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            </div>
          <?php endforeach; ?>
          <?php foreach ((is_array($wClan['benefits'] ?? null) ? $wClan['benefits'] : []) as $ben): ?>
            <div class="form-row"><label>Avantaj</label><input name="clan_benefit[]" value="<?= e((string) $ben) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          <?php endforeach; ?>
          <?php if ($canWikiEdit): ?>
            <div class="form-row"><label>Yeni avantaj</label><input name="clan_benefit[]" placeholder="Boş bırakılırsa eklenmez"></div>
          <?php endif; ?>

          <h4 style="margin:22px 0 12px;color:var(--gold-light);font-size:.9rem;">SSS</h4>
          <div class="grid grid-2">
            <div class="form-row"><label>Eyebrow</label><input name="faq_eyebrow" value="<?= e((string) ($wFaqSec['eyebrow'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
            <div class="form-row"><label>Başlık</label><input name="faq_title" value="<?= e((string) ($wFaqSec['title'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
          </div>
          <?php foreach ($wFaq as $fi => $fq): ?>
            <div style="margin-bottom:10px;padding:10px;border:1px solid var(--line);">
              <div class="form-row"><label>Soru</label><input name="faq_q[]" value="<?= e((string) ($fq['q'] ?? '')) ?>"<?= $canWikiEdit ? '' : ' readonly' ?>></div>
              <div class="form-row"><label>Cevap</label><textarea name="faq_a[]" rows="2"<?= $canWikiEdit ? '' : ' readonly' ?>><?= e((string) ($fq['a'] ?? '')) ?></textarea></div>
            </div>
          <?php endforeach; ?>
          <?php if ($canWikiEdit): ?>
            <div style="margin-bottom:10px;padding:10px;border:1px dashed var(--line);">
              <div class="form-row"><label>Yeni soru</label><input name="faq_q[]" placeholder="Boş bırakılırsa eklenmez"></div>
              <div class="form-row"><label>Yeni cevap</label><textarea name="faq_a[]" rows="2"></textarea></div>
            </div>
          <?php endif; ?>

          <?php if ($canWikiEdit): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px;">
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Kaydet</button>
              <a class="btn btn-ghost btn-sm" href="<?= e(url('/wiki')) ?>" target="_blank">Önizle</a>
            </div>
          <?php endif; ?>
        </form>

        <?php if ($canWikiEdit): ?>
          <form method="post" action="<?= e(url('/admin/wiki/sifirla')) ?>" style="margin-top:14px;" onsubmit="return confirm('Wiki içeriği varsayılanlara döndürülsün mü? Mevcut kayıtlar silinir.');">
            <?= $csrf ?>
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--blood-light);"><i class="fa-solid fa-rotate-left"></i> Varsayılana sıfırla</button>
          </form>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===================== CAPTCHA ===================== -->
    <section class="section<?= $panelSection === 'captcha-ayarlari' ? ' active' : '' ?>" id="captcha-ayarlari">
      <?php
        $captchaConfig = isset($captchaConfig) && is_array($captchaConfig) ? $captchaConfig : \App\Services\CaptchaService::config();
        $capProvider = (string) ($captchaConfig['provider'] ?? 'google');
        $capEnabled = !empty($captchaConfig['enabled']);
      ?>
      <div class="card" style="max-width:720px;">
        <div class="card-head">
          <h3>Captcha / Robot Doğrulama</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= $capEnabled && !empty($captchaConfig['ready']) ? 'Aktif' : ($capEnabled ? 'Aktif (eksik key)' : 'Pasif') ?></span>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Aktifken giriş, kayıt ve parola sıfırlama formlarında zorunlu olur. Google reCAPTCHA v2 veya Cloudflare Turnstile seçin; script / doğrulama adresleri sabittir — yalnızca key’leri girin.
          <br><strong style="color:var(--gold-light);">Google:</strong> reCAPTCHA admin’de domain ekleyin
          (<code>127.0.0.1</code>, <code>localhost</code> veya canlı domain). v2 “I’m not a robot” Checkbox kullanın.
          <br><strong style="color:var(--gold-light);">Turnstile:</strong> Cloudflare widget Hostname’lerine aynı adresleri ekleyin. Aksi halde widget görünmez.
        </p>
        <form method="post" action="<?= e(url('/admin/ayarlar/captcha')) ?>" id="captchaSettingsForm">
          <?= $csrf ?>
          <div class="form-row">
            <label><input type="checkbox" name="enabled" value="1"<?= $capEnabled ? ' checked' : '' ?>> Captcha aktif</label>
          </div>
          <div class="form-row">
            <label>Sağlayıcı</label>
            <select name="provider" id="captchaProvider">
              <?php foreach (($captchaConfig['providers'] ?? []) as $pkey => $pinfo): ?>
                <option value="<?= e((string) $pkey) ?>"<?= $capProvider === $pkey ? ' selected' : '' ?>><?= e((string) ($pinfo['label'] ?? $pkey)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="grid grid-2">
            <div class="form-row"><label>Site Key</label><input name="site_key" value="<?= e((string) ($captchaConfig['site_key'] ?? '')) ?>" autocomplete="off" placeholder="Public site key"></div>
            <div class="form-row"><label>Secret Key</label><input name="secret_key" type="password" value="<?= e((string) ($captchaConfig['secret_key'] ?? '')) ?>" autocomplete="new-password" placeholder="Secret key"></div>
          </div>
          <div id="captchaProviderHint" style="font-size:.75rem;color:var(--ash);margin:0 0 14px;line-height:1.5;"></div>
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
      </div>
    </section>

    <!-- ===================== MAIL ===================== -->
    <section class="section<?= $panelSection === 'mail-ayarlari' ? ' active' : '' ?>" id="mail-ayarlari">
      <div class="mail-tabs" id="mailTabs">
        <button type="button" data-mail-tab="sunucu" class="<?= $mailTab === 'sunucu' ? 'active' : '' ?>">1. Sunucu</button>
        <button type="button" data-mail-tab="bildirimler" class="<?= $mailTab === 'bildirimler' ? 'active' : '' ?>">2. Bildirimler</button>
        <button type="button" data-mail-tab="test" class="<?= $mailTab === 'test' ? 'active' : '' ?>">3. Test</button>
        <button type="button" data-mail-tab="loglar" class="<?= $mailTab === 'loglar' ? 'active' : '' ?>">4. Gönderim</button>
      </div>

      <div class="mail-pane<?= $mailTab === 'sunucu' ? ' active' : '' ?>" data-mail-pane="sunucu">
        <div class="grid grid-2">
          <div class="card">
            <div class="card-head"><h3>Mail sunucusu ekle / düzenle</h3></div>
            <form method="post" action="<?= e(url('/admin/ayarlar/mail')) ?>" id="mailServerForm">
              <?= $csrf ?>
              <input type="hidden" name="mail_tab" value="sunucu">
              <input type="hidden" name="id" id="mailServerId" value="">
              <div class="form-row"><label>Ad</label><input name="name" id="mailServerName" required placeholder="Örn. Gmail hesap"></div>
              <div class="form-row"><label>Sağlayıcı</label>
                <select name="provider" id="mailProvider">
                  <?php foreach ($mailPresets as $pkey => $preset): ?>
                    <option value="<?= e((string) $pkey) ?>"><?= e((string) ($preset['label'] ?? $pkey)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div id="mailCustomFields" style="display:none;">
                <div class="form-row"><label>Host</label><input name="host" id="mailHost" placeholder="smtp.ornek.com"></div>
                <div class="grid grid-2">
                  <div class="form-row"><label>Port</label><input type="number" name="port" id="mailPort" value="587"></div>
                  <div class="form-row"><label>Şifreleme</label>
                    <select name="encryption" id="mailEnc">
                      <option value="tls">TLS</option>
                      <option value="ssl">SSL</option>
                      <option value="none">Yok</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-row"><label>Hesap (kullanıcı / e-posta)</label><input name="username" id="mailUser" required autocomplete="off"></div>
              <div class="form-row"><label>Parola</label><input type="password" name="password" id="mailPass" autocomplete="new-password" placeholder="Düzenlemede boş bırak = değişmez"></div>
              <div class="form-row"><label>Gönderen e-posta</label><input name="from_email" id="mailFrom" type="email" required></div>
              <div class="form-row"><label>Gönderen adı</label><input name="from_name" id="mailFromName" value="<?= e($appName) ?>"></div>
              <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:.82rem;color:var(--ash);cursor:pointer;">
                <input type="checkbox" name="activate" value="1" checked style="width:auto;"> Aktif sunucu yap
              </label>
              <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
                <button type="button" class="btn btn-ghost btn-sm" id="mailServerReset">Formu temizle</button>
                <button type="button" class="btn btn-jade btn-sm" id="mailQuickTest" title="Kayıtlı sunucu ile test maili gönder">
                  <i class="fa-solid fa-paper-plane"></i> Test gönder
                </button>
              </div>
              <p style="font-size:.75rem;color:var(--ash);margin-top:12px;">Parola veritabanında şifreli saklanır. Önce kaydedin, sonra Test ile doğrulayın.</p>
              <div id="mailProviderHint" style="display:none;font-size:.78rem;color:var(--gold-light);margin-top:10px;padding:12px;border:1px solid rgba(201,151,74,.25);background:rgba(201,151,74,.06);line-height:1.45;"></div>
            </form>
          </div>
          <div class="card">
            <div class="card-head"><h3>Kayıtlı sunucular</h3></div>
            <?php if ($mailServers === []): ?>
              <p style="color:var(--ash);font-size:.88rem;">Henüz sunucu yok.</p>
            <?php else: ?>
              <table>
                <thead><tr><th>Ad</th><th>Sağlayıcı</th><th>Hesap</th><th style="width:1%;">İşlem</th></tr></thead>
                <tbody>
                  <?php foreach ($mailServers as $ms): ?>
                  <?php
                    $msTestTo = trim((string) ($ms['from_email'] ?? ''));
                    if ($msTestTo === '' || !filter_var($msTestTo, FILTER_VALIDATE_EMAIL)) {
                        $msTestTo = trim((string) ($ms['username'] ?? ''));
                    }
                  ?>
                  <tr>
                    <td><?= e((string) $ms['name']) ?><?php if (!empty($ms['is_active'])): ?> <span class="badge ok">Aktif</span><?php endif; ?></td>
                    <td><?= e((string) $ms['provider']) ?></td>
                    <td style="font-size:.78rem;word-break:break-all;"><?= e((string) $ms['username']) ?></td>
                    <td class="actions-cell mail-server-actions">
                      <button type="button" title="Düzenle" data-edit-mail
                        data-id="<?= (int) $ms['id'] ?>"
                        data-name="<?= e((string) $ms['name']) ?>"
                        data-provider="<?= e((string) $ms['provider']) ?>"
                        data-host="<?= e((string) $ms['host']) ?>"
                        data-port="<?= (int) $ms['port'] ?>"
                        data-encryption="<?= e((string) $ms['encryption']) ?>"
                        data-username="<?= e((string) $ms['username']) ?>"
                        data-from="<?= e((string) $ms['from_email']) ?>"
                        data-from-name="<?= e((string) $ms['from_name']) ?>"><i class="fa-solid fa-pen"></i></button>
                      <button type="button" title="Test gönder" data-mail-test
                        data-server-id="<?= (int) $ms['id'] ?>"
                        data-server-name="<?= e((string) $ms['name']) ?>"
                        data-default-to="<?= e($msTestTo) ?>"><i class="fa-solid fa-paper-plane"></i></button>
                      <?php if (empty($ms['is_active'])): ?>
                      <form method="post" action="<?= e(url('/admin/ayarlar/mail/aktif')) ?>">
                        <?= $csrf ?><input type="hidden" name="mail_tab" value="sunucu"><input type="hidden" name="id" value="<?= (int) $ms['id'] ?>">
                        <button type="submit" title="Aktif yap"><i class="fa-solid fa-check"></i></button>
                      </form>
                      <?php endif; ?>
                      <form method="post" action="<?= e(url('/admin/ayarlar/mail/sil')) ?>" onsubmit="return confirm('Silinsin mi?');">
                        <?= $csrf ?><input type="hidden" name="mail_tab" value="sunucu"><input type="hidden" name="id" value="<?= (int) $ms['id'] ?>">
                        <button type="submit" class="danger" title="Sil"><i class="fa-solid fa-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="mail-pane<?= $mailTab === 'bildirimler' ? ' active' : '' ?>" data-mail-pane="bildirimler">
        <div class="card">
          <div class="card-head"><h3>E-posta bildirim şablonları</h3>
            <span style="font-size:.78rem;color:var(--ash);">Varsayılan: kapalı · Değişkenler: {{login}} {{email}} {{link}} {{reason}} {{code}} {{subject}} {{app}} {{logo}} {{logo_width}}</span>
          </div>
          <?php if ($mailTemplates === []): ?>
            <p style="color:var(--ash);">Şablon bulunamadı.</p>
          <?php else: ?>
            <?php foreach ($mailTemplates as $tpl): ?>
              <form method="post" action="<?= e(url('/admin/ayarlar/mail/sablon')) ?>" style="margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--line);">
                <?= $csrf ?>
                <input type="hidden" name="mail_tab" value="bildirimler">
                <input type="hidden" name="code" value="<?= e((string) $tpl['code']) ?>">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
                  <strong style="color:var(--gold-light);"><?= e((string) $tpl['name']) ?> <span style="color:var(--ash);font-weight:400;font-size:.78rem;">(<?= e((string) $tpl['code']) ?>)</span></strong>
                  <label style="display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--ash);cursor:pointer;">
                    <input type="checkbox" name="is_enabled" value="1" <?= !empty($tpl['is_enabled']) ? 'checked' : '' ?> style="width:auto;"> Aktif
                  </label>
                </div>
                <div class="form-row"><label>Konu</label><input name="subject" value="<?= e((string) $tpl['subject']) ?>" required></div>
                <div class="form-row"><label>HTML içerik</label>
                  <div class="mail-tpl-wrap" data-mail-tpl-wrap>
                    <div class="mail-tpl-toolbar" data-mail-toolbar role="toolbar" aria-label="Mail şablon araçları">
                      <button type="button" data-cmd="bold" title="Kalın"><b>B</b></button>
                      <button type="button" data-cmd="italic" title="İtalik"><i>I</i></button>
                      <button type="button" data-cmd="underline" title="Altı çizili"><u>U</u></button>
                      <span class="sep"></span>
                      <label class="mail-tool" title="Yazı rengi"><input type="color" data-fore-color value="#eccd8e" aria-label="Yazı rengi"></label>
                      <span class="sep"></span>
                      <button type="button" data-cmd="insertUnorderedList" title="Liste"><i class="fa-solid fa-list-ul"></i></button>
                      <button type="button" data-cmd="insertOrderedList" title="Numaralı"><i class="fa-solid fa-list-ol"></i></button>
                      <button type="button" data-cmd="createLink" title="Link"><i class="fa-solid fa-link"></i></button>
                      <button type="button" data-cmd="insertTable" title="Tablo"><i class="fa-solid fa-table"></i></button>
                      <span class="sep"></span>
                      <button type="button" data-cmd="formatBlock" data-value="h2" title="Başlık">H2</button>
                      <button type="button" data-cmd="formatBlock" data-value="p" title="Paragraf">P</button>
                      <button type="button" data-cmd="removeFormat" title="Biçimi temizle"><i class="fa-solid fa-eraser"></i></button>
                      <span class="sep"></span>
                      <select class="mail-var" data-insert-var title="Değişken ekle" aria-label="Değişken ekle">
                        <option value="">+ Değişken</option>
                        <option value="{{login}}">{{login}}</option>
                        <option value="{{email}}">{{email}}</option>
                        <option value="{{link}}">{{link}}</option>
                        <option value="{{logo}}">{{logo}}</option>
                        <option value="{{logo_width}}">{{logo_width}}</option>
                        <option value="{{reason}}">{{reason}}</option>
                        <option value="{{code}}">{{code}}</option>
                        <option value="{{subject}}">{{subject}}</option>
                        <option value="{{app}}">{{app}}</option>
                      </select>
                      <button type="button" data-cmd="toggleHtml" title="HTML kaynak kodu" data-html-toggle><i class="fa-solid fa-code"></i> HTML</button>
                    </div>
                    <?php
                      $tplBodyNorm = \App\Services\MailService::repairTemplateIfBroken(
                          (string) $tpl['code'],
                          (string) $tpl['body_html']
                      );
                    ?>
                    <div class="mail-tpl-editor" contenteditable="true" data-html-editor data-placeholder="Mail HTML içeriğini yaz veya HTML modunda yapıştır…"><?= $tplBodyNorm ?></div>
                    <textarea class="mail-tpl-source" data-html-source spellcheck="false" aria-label="HTML kaynak"><?= e($tplBodyNorm) ?></textarea>
                    <textarea name="body_html" hidden><?= e($tplBodyNorm) ?></textarea>
                  </div>
                  <p style="font-size:.72rem;color:var(--ash);margin-top:8px;">Tam HTML şablon için <b style="color:var(--gold-light);">HTML</b> moduna geçip yapıştır (görsel moda alma — bozulur). Açık arka planlı şablonlar Gmail/Outlook’ta daha güvenilir.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                  <button type="submit" class="btn btn-primary btn-sm">Şablonu kaydet</button>
                  <button type="button" class="btn btn-ghost btn-sm" data-mail-preview>Önizle</button>
                </div>
              </form>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="mail-pane<?= $mailTab === 'test' ? ' active' : '' ?>" data-mail-pane="test">
        <div class="card" style="max-width:480px;">
          <div class="card-head"><h3>Test maili gönder</h3></div>
          <form method="post" action="<?= e(url('/admin/ayarlar/mail/test')) ?>">
            <?= $csrf ?>
            <input type="hidden" name="mail_tab" value="test">
            <div class="form-row"><label>Alıcı e-posta</label><input type="email" name="to_email" required placeholder="ornek@mail.com"></div>
            <button type="submit" class="btn btn-primary btn-sm">Gönder</button>
          </form>
        </div>
      </div>

      <div class="mail-pane<?= $mailTab === 'loglar' ? ' active' : '' ?>" data-mail-pane="loglar">
        <div class="card">
          <div class="card-head" style="flex-wrap:wrap;gap:10px;">
            <h3>Son gönderimler</h3>
            <span style="font-size:.78rem;color:var(--ash);">Son 10 kayıt</span>
          </div>
          <form method="get" action="<?= e(url('/admin')) ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center;">
            <input type="hidden" name="section" value="mail-ayarlari">
            <input type="hidden" name="mail_tab" value="loglar">
            <input type="search" name="mail_q" value="<?= e($mailLogSearch) ?>" placeholder="Alıcı e-posta / login / konu…" style="flex:1;min-width:200px;max-width:360px;" autocomplete="off">
            <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
            <?php if ($mailLogSearch !== ''): ?>
              <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=mail-ayarlari&mail_tab=loglar')) ?>">Temizle</a>
            <?php endif; ?>
          </form>
          <?php if ($mailLogs === []): ?>
            <p style="color:var(--ash);"><?= $mailLogSearch !== '' ? 'Arama sonucu bulunamadı.' : 'Kayıt yok.' ?></p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Zaman</th>
                  <th>Şablon</th>
                  <th>Alıcı</th>
                  <th>Konu</th>
                  <th>Durum</th>
                  <th>İşlemler</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mailLogs as $ml): ?>
                <tr>
                  <td style="font-size:.75rem;"><?= e((string) ($ml['created_at'] ?? '')) ?></td>
                  <td><?= e((string) ($ml['template_code'] ?? '')) ?></td>
                  <td style="font-size:.78rem;"><?= e((string) ($ml['to_email'] ?? '')) ?><br><span style="color:var(--ash);"><?= e((string) ($ml['to_login'] ?? '')) ?></span></td>
                  <td><?= e((string) ($ml['subject'] ?? '')) ?></td>
                  <td>
                    <?php if (($ml['status'] ?? '') === 'ok'): ?>
                      <span class="badge ok">OK</span>
                    <?php else: ?>
                      <span class="badge ban" title="<?= e((string) ($ml['error'] ?? '')) ?>">Hata</span>
                    <?php endif; ?>
                  </td>
                  <td class="actions-cell">
                    <?php if (!empty($ml['has_body'])): ?>
                      <form method="post" action="<?= e(url('/admin/ayarlar/mail/tekrar')) ?>" style="display:inline;" onsubmit="return confirm('Bu mail aynı içerikle tekrar gönderilsin mi?');">
                        <?= $csrf ?>
                        <input type="hidden" name="mail_tab" value="loglar">
                        <input type="hidden" name="id" value="<?= (int) ($ml['id'] ?? 0) ?>">
                        <button type="submit" title="Tekrar gönder"><i class="fa-solid fa-paper-plane"></i></button>
                      </form>
                    <?php else: ?>
                      <button type="button" disabled title="Eski kayıt — içerik yok" style="opacity:.35;cursor:not-allowed;"><i class="fa-solid fa-paper-plane"></i></button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <section class="section<?= $panelSection === 'footer-ayarlari' ? ' active' : '' ?>" id="footer-ayarlari">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><h3>Footer Metinleri</h3><span style="font-size:.8rem;color:var(--ash);">v<?= e($appVersion) ?></span></div>
        <form method="post" action="<?= e(url('/admin/ayarlar/footer-meta')) ?>">
          <?= $csrf ?>
          <div class="form-row"><label>Copyright</label><input name="copyright" value="<?= e((string)($siteFooter['copyright'] ?? '')) ?>"></div>
          <div class="form-row"><label>Marka açıklaması</label><textarea name="brand_text" style="min-height:80px;"><?= e((string)($siteFooter['brand_text'] ?? '')) ?></textarea></div>
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
      </div>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Footer Linkleri</h3></div>
          <table>
            <thead><tr><th>Kolon</th><th>Etiket</th><th>URL</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($siteFooterLinks as $fl): ?>
              <tr>
                <td><?= e((string)$fl['column_key']) ?></td>
                <td><?= e((string)$fl['label']) ?></td>
                <td style="font-size:.75rem;word-break:break-all;"><?= e((string)$fl['url']) ?></td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-edit-footer-link
                    data-id="<?= (int)$fl['id'] ?>"
                    data-column="<?= e((string)$fl['column_key']) ?>"
                    data-label="<?= e((string)$fl['label']) ?>"
                    data-url="<?= e((string)$fl['url']) ?>"><i class="fa-solid fa-pen"></i></button>
                  <form method="post" action="<?= e(url('/admin/ayarlar/footer-link/sil')) ?>" style="display:inline;" onsubmit="return confirm('Silinsin mi?');"><?= $csrf ?><input type="hidden" name="id" value="<?= (int)$fl['id'] ?>"><button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button></form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?= e(url('/admin/ayarlar/footer-link')) ?>" id="footerLinkForm" style="margin-top:14px;">
            <?= $csrf ?>
            <input type="hidden" name="id" id="footerLinkId" value="">
            <div class="card-head" style="margin-bottom:12px;padding:0;"><h3 id="footerLinkFormTitle" style="font-size:.95rem;">Yeni Link</h3></div>
            <div class="form-row"><label>Kolon</label>
              <select name="column_key" id="footerLinkColumn">
                <option value="server">Sunucu</option>
                <option value="community">Topluluk</option>
              </select>
            </div>
            <div class="form-row"><label>Etiket</label><input name="label" id="footerLinkLabel" required></div>
            <div class="form-row"><label>URL</label><input name="url" id="footerLinkUrl" required></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="footerLinkReset">Temizle</button>
            </div>
          </form>
        </div>
        <div class="card">
          <div class="card-head"><h3>Sosyal Medya</h3></div>
          <table>
            <thead><tr><th>Ad</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($siteSocials as $soc): ?>
              <tr>
                <td><i class="<?= e((string)$soc['icon']) ?>"></i> <?= e((string)$soc['name']) ?><div style="font-size:.72rem;color:var(--ash);word-break:break-all;"><?= e((string)$soc['url']) ?></div></td>
                <td><?= !empty($soc['is_active']) ? 'Aktif' : 'Pasif' ?></td>
                <td class="actions-cell">
                  <button type="button" title="Düzenle"
                    data-edit-social
                    data-id="<?= (int)$soc['id'] ?>"
                    data-name="<?= e((string)$soc['name']) ?>"
                    data-icon="<?= e((string)$soc['icon']) ?>"
                    data-url="<?= e((string)$soc['url']) ?>"
                    data-active="<?= !empty($soc['is_active']) ? '1' : '0' ?>"><i class="fa-solid fa-pen"></i></button>
                  <form method="post" action="<?= e(url('/admin/ayarlar/sosyal')) ?>" style="display:inline;"><?= $csrf ?>
                    <input type="hidden" name="id" value="<?= (int)$soc['id'] ?>">
                    <input type="hidden" name="name" value="<?= e((string)$soc['name']) ?>">
                    <input type="hidden" name="icon" value="<?= e((string)$soc['icon']) ?>">
                    <input type="hidden" name="url" value="<?= e((string)$soc['url']) ?>">
                    <input type="hidden" name="is_active" value="<?= !empty($soc['is_active']) ? '0' : '1' ?>">
                    <button type="submit" title="Aktif/Pasif"><?= !empty($soc['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                  </form>
                  <form method="post" action="<?= e(url('/admin/ayarlar/sosyal/sil')) ?>" style="display:inline;" onsubmit="return confirm('Silinsin mi?');"><?= $csrf ?><input type="hidden" name="id" value="<?= (int)$soc['id'] ?>"><button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button></form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?= e(url('/admin/ayarlar/sosyal')) ?>" id="socialForm" style="margin-top:14px;">
            <?= $csrf ?>
            <input type="hidden" name="id" id="socialId" value="">
            <div class="card-head" style="margin-bottom:12px;padding:0;"><h3 id="socialFormTitle" style="font-size:.95rem;">Yeni Sosyal</h3></div>
            <div class="form-row"><label>Ad</label><input name="name" id="socialName" required placeholder="Discord"></div>
            <div class="form-row"><label>İkon (FA class)</label><input name="icon" id="socialIcon" value="fa-brands fa-discord"></div>
            <div class="form-row"><label>URL</label><input name="url" id="socialUrl" required placeholder="https://..."></div>
            <div class="form-row"><label>Durum</label>
              <select name="is_active" id="socialActive">
                <option value="1">Aktif</option>
                <option value="0">Pasif</option>
              </select>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="socialReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== DUYURULAR ===================== -->
    <section class="section<?= $panelSection === 'duyurular' ? ' active' : '' ?>" id="duyurular">
      <?php if (!$can('announcements') && !$can('menu_duyurular')): ?>
        <div class="card"><p style="color:var(--ash);">Duyuru yetkin yok.</p></div>
      <?php else: ?>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Yayınlanan Duyurular</h3></div>
          <?php if ($announcements === []): ?>
            <p style="color:var(--ash);font-size:.88rem;">Henüz duyuru yok.</p>
          <?php else: ?>
            <?php
              $annBodiesMap = [];
              foreach ($announcements as $ann) {
                  $annBodiesMap[(string) (int) $ann['id']] = (string) $ann['body'];
              }
            ?>
            <script>window.__annBodies = <?= json_encode($annBodiesMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
            <table>
              <thead><tr><th>ID</th><th>Başlık</th><th>Tür</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($announcements as $ann): ?>
                <tr>
                  <td><code>#<?= (int) $ann['id'] ?></code></td>
                  <td><?= e((string) $ann['title']) ?></td>
                  <td style="color:var(--ash);"><?= e((string) $ann['type_name']) ?></td>
                  <td><span class="badge <?= !empty($ann['is_active']) ? 'pending' : 'closed' ?>"><?= !empty($ann['is_active']) ? 'Aktif' : 'Pasif' ?></span></td>
                  <td style="font-size:.8rem;"><?= e((string) $ann['published_label']) ?></td>
                  <td class="actions-cell">
                    <?php if ($can('announcements')): ?>
                    <button type="button" title="Düzenle"
                      data-edit-ann
                      data-id="<?= (int) $ann['id'] ?>"
                      data-type="<?= (int) $ann['type_id'] ?>"
                      data-title="<?= e((string) $ann['title']) ?>"
                      data-active="<?= !empty($ann['is_active']) ? '1' : '0' ?>"
                    ><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/duyuru/toggle')) ?>" style="display:inline;">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $ann['id'] ?>">
                      <input type="hidden" name="is_active" value="<?= !empty($ann['is_active']) ? '0' : '1' ?>">
                      <button type="submit" title="Aktif/Pasif"><?= !empty($ann['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                    </form>
                    <form method="post" action="<?= e(url('/admin/duyuru/sil')) ?>" style="display:inline;" onsubmit="return confirm('Duyuru silinsin mi?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $ann['id'] ?>">
                      <button type="submit" class="danger" title="Sil"><i class="fa-solid fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="annFormTitle">Yeni Duyuru</h3></div>
          <?php if (!$can('announcements')): ?>
            <p style="color:var(--ash);font-size:.88rem;">Duyuru yazmak için “Duyuru işlemleri” yetkisi gerekir.</p>
          <?php else: ?>
          <form method="post" action="<?= e(url('/admin/duyuru/kaydet')) ?>" id="annForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="annId" value="">
            <input type="hidden" name="body" id="annBody" value="">
            <div class="form-row">
              <label>Duyuru türü</label>
              <select name="type_id" id="annType" required>
                <option value="">Seç...</option>
                <?php foreach ($announcementTypesActive as $t): ?>
                  <option value="<?= (int) $t['id'] ?>"><?= e((string) $t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row"><label>Başlık</label><input name="title" id="annTitle" required maxlength="200" placeholder="Duyuru başlığı"></div>
            <div class="form-row">
              <label>İçerik</label>
              <div id="annEditorWrap">
                <div class="ann-toolbar" id="annToolbar" role="toolbar" aria-label="Metin araçları">
                  <button type="button" data-cmd="bold" title="Kalın"><b>B</b></button>
                  <button type="button" data-cmd="italic" title="İtalik"><i>I</i></button>
                  <button type="button" data-cmd="underline" title="Altı çizili"><u>U</u></button>
                  <button type="button" data-cmd="strikeThrough" title="Üstü çizili"><s>S</s></button>
                  <span class="sep"></span>
                  <label class="ann-tool" title="Yazı rengi">
                    <input type="color" id="annForeColor" value="#eccd8e" aria-label="Yazı rengi">
                  </label>
                  <label class="ann-tool" title="Arka plan rengi">
                    <input type="color" id="annHiliteColor" value="#8f1c29" aria-label="Arka plan">
                  </label>
                  <span class="sep"></span>
                  <button type="button" data-cmd="justifyLeft" title="Sola"><i class="fa-solid fa-align-left"></i></button>
                  <button type="button" data-cmd="justifyCenter" title="Ortala"><i class="fa-solid fa-align-center"></i></button>
                  <button type="button" data-cmd="justifyRight" title="Sağa"><i class="fa-solid fa-align-right"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="insertUnorderedList" title="Madde listesi"><i class="fa-solid fa-list-ul"></i></button>
                  <button type="button" data-cmd="insertOrderedList" title="Numaralı liste"><i class="fa-solid fa-list-ol"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="createLink" title="Link"><i class="fa-solid fa-link"></i></button>
                  <button type="button" data-cmd="unlink" title="Linki kaldır"><i class="fa-solid fa-link-slash"></i></button>
                  <button type="button" data-cmd="insertTable" title="Tablo ekle"><i class="fa-solid fa-table"></i></button>
                  <span class="sep"></span>
                  <button type="button" data-cmd="formatBlock" data-value="h2" title="Başlık">H2</button>
                  <button type="button" data-cmd="formatBlock" data-value="p" title="Paragraf">P</button>
                  <button type="button" data-cmd="removeFormat" title="Biçimi temizle"><i class="fa-solid fa-eraser"></i></button>
                  <button type="button" data-cmd="toggleHtml" title="HTML kaynak" id="annToggleHtml"><i class="fa-solid fa-code"></i></button>
                </div>
                <div id="annEditor" contenteditable="true" data-placeholder="Duyuru içeriğini buraya yaz…"></div>
                <textarea id="annHtmlPanel" spellcheck="false" aria-label="HTML kaynak"></textarea>
              </div>
            </div>
            <div class="form-row">
              <label style="display:flex;align-items:center;gap:10px;text-transform:none;letter-spacing:0;cursor:pointer;">
                <input type="checkbox" name="is_active" id="annActive" value="1" checked style="width:auto;">
                Aktif yayınla
              </label>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="annReset">Temizle</button>
            </div>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <!-- ===================== DUYURU TÜRLERİ ===================== -->
    <section class="section<?= $panelSection === 'duyuru-turleri' ? ' active' : '' ?>" id="duyuru-turleri">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Duyuru Türleri</h3></div>
          <table>
            <thead><tr><th>ID</th><th>Ad</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($announcementTypes === []): ?>
                <tr><td colspan="4" style="color:var(--ash);">Tür yok.</td></tr>
              <?php else: ?>
                <?php foreach ($announcementTypes as $t): ?>
                <tr>
                  <td><code>#<?= (int) $t['id'] ?></code></td>
                  <td><?= e((string) $t['name']) ?></td>
                  <td><?= !empty($t['is_active']) ? 'Aktif' : 'Pasif' ?></td>
                  <td class="actions-cell">
                    <button type="button" title="Düzenle"
                      data-edit-anntype
                      data-id="<?= (int) $t['id'] ?>"
                      data-name="<?= e((string) $t['name']) ?>"
                    ><i class="fa-solid fa-pen"></i></button>
                    <form method="post" action="<?= e(url('/admin/duyuru-tur/toggle')) ?>" style="display:inline;">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                      <input type="hidden" name="is_active" value="<?= !empty($t['is_active']) ? '0' : '1' ?>">
                      <button type="submit"><?= !empty($t['is_active']) ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>' ?></button>
                    </form>
                    <form method="post" action="<?= e(url('/admin/duyuru-tur/sil')) ?>" style="display:inline;" onsubmit="return confirm('Tür silinsin / pasife alınsın mı?');">
                      <?= $csrf ?>
                      <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                      <button type="submit" class="danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-head"><h3 id="annTypeFormTitle">Yeni Tür</h3></div>
          <form method="post" action="<?= e(url('/admin/duyuru-tur/kaydet')) ?>" id="annTypeForm">
            <?= $csrf ?>
            <input type="hidden" name="id" id="annTypeId" value="">
            <div class="form-row"><label>Tür adı</label><input name="name" id="annTypeName" required maxlength="120" placeholder="Örn: Etkinlik Duyurusu"></div>
            <div style="display:flex;gap:10px;">
              <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
              <button type="button" class="btn btn-ghost btn-sm" id="annTypeReset">Temizle</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ===================== DESTEK TALEPLERİ ===================== -->
    <section class="section<?= $panelSection === 'destekler' ? ' active' : '' ?>" id="destekler">
      <?php if (!$can('tickets')): ?>
        <div class="card"><p style="color:var(--ash);">Destek talebi işlemleri için yetkin yok.</p></div>
      <?php else: ?>
      <div class="grid grid-2">
        <div class="card">
          <div class="card-head"><h3>Destek Talepleri</h3></div>
          <form class="filters" method="get" action="<?= e(url('/admin')) ?>" style="margin-bottom:14px;">
            <input type="hidden" name="section" value="destekler">
            <input name="ticket_q" value="<?= e($ticketSearch) ?>" placeholder="Ticket kodu veya hesap adı..." style="flex:1; min-width:200px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
            <?php if ($ticketSearch !== ''): ?>
              <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=destekler')) ?>">Temizle</a>
            <?php endif; ?>
          </form>
          <table>
            <thead><tr><th>Kod</th><th>Oyuncu</th><th>Konu</th><th>Kategori</th><th>Durum</th><th></th></tr></thead>
            <tbody>
              <?php if ($adminTickets === []): ?>
                <tr><td colspan="6" style="color:var(--ash);"><?= $ticketSearch !== '' ? 'Aramayla eşleşen ticket yok.' : 'Henüz ticket yok.' ?></td></tr>
              <?php else: ?>
                <?php foreach ($adminTickets as $t): ?>
                <tr>
                  <td><code><?= e((string) $t['public_code']) ?></code></td>
                  <td><?= e((string) $t['account_login']) ?></td>
                  <td><?= e((string) $t['subject']) ?></td>
                  <td style="color:var(--ash);"><?= e((string) $t['category_name']) ?></td>
                  <td><span class="badge <?= ($t['status_code'] ?? '') === 'closed' ? 'closed' : 'pending' ?>"><?= e((string) $t['status_label']) ?></span></td>
                  <td class="actions-cell">
                    <a href="<?= e(url('/admin?section=destekler&ticket=' . (int) $t['id'] . ($ticketSearch !== '' ? '&ticket_q=' . rawurlencode($ticketSearch) : ''))) ?>" title="Aç"><i class="fa-solid fa-eye"></i></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card">
          <?php if ($activeTicket === null): ?>
            <div class="card-head"><h3>Ticket Detayı</h3></div>
            <p style="color:var(--ash);font-size:.88rem;">Listeden bir ticket seç.</p>
          <?php else: ?>
            <div class="card-head">
              <h3><?= e((string) $activeTicket['public_code']) ?></h3>
              <span class="badge pending"><?= e((string) $activeTicket['status_label']) ?></span>
            </div>
            <p style="font-size:.9rem;margin-bottom:8px;"><b><?= e((string) $activeTicket['subject']) ?></b></p>
            <p style="font-size:.8rem;color:var(--ash);margin-bottom:14px;">
              Oyuncu: <?= e((string) $activeTicket['account_login']) ?> (#<?= (int) $activeTicket['account_id'] ?>)
              · <?= e((string) $activeTicket['category_name']) ?>
              <?php if ($can('player_detail')): ?>
                · <button type="button" class="linkish" data-detail="<?= (int) $activeTicket['account_id'] ?>" style="background:none;border:none;color:var(--gold-light);padding:0;font:inherit;cursor:pointer;text-decoration:underline;">Hesap detayı</button>
              <?php endif; ?>
            </p>
            <div style="max-height:280px;overflow:auto;margin-bottom:16px;border:1px solid var(--line);padding:12px;">
              <?php foreach (($activeTicket['messages'] ?? []) as $m): ?>
                <div style="margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--line);">
                  <div style="font-size:.75rem;color:var(--ash);margin-bottom:4px;">
                    <?= !empty($m['is_staff']) ? 'Yetkili' : 'Oyuncu' ?> · <?= e((string) $m['account_login']) ?> · <?= e((string) ($m['created_label'] ?? '')) ?>
                  </div>
                  <div style="font-size:.88rem;white-space:pre-wrap;"><?= e((string) $m['body']) ?></div>
                  <?php if (!empty($m['attachment'])): ?>
                    <a href="<?= e((string) $m['attachment']['path']) ?>" target="_blank" style="font-size:.78rem;color:var(--gold-light);">
                      <i class="fa-solid fa-paperclip"></i> <?= e((string) $m['attachment']['name']) ?>
                    </a>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if (($activeTicket['status_code'] ?? '') !== 'closed'): ?>
            <form method="post" action="<?= e(url('/admin/ticket/yanit')) ?>" enctype="multipart/form-data">
              <?= $csrf ?>
              <input type="hidden" name="ticket_id" value="<?= (int) $activeTicket['id'] ?>">
              <div class="form-row"><label>Yanıt</label><textarea name="body" required style="min-height:90px;"></textarea></div>
              <div class="form-row"><label>Dosya (opsiyonel)</label><input type="file" name="attachment"></div>
              <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary btn-sm">Yanıtla</button>
              </div>
            </form>
            <form method="post" action="<?= e(url('/admin/ticket/kapat')) ?>" style="margin-top:10px;" onsubmit="return confirm('Ticket kapatılsın mı?');">
              <?= $csrf ?>
              <input type="hidden" name="ticket_id" value="<?= (int) $activeTicket['id'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm">Kapat / Çözümle</button>
            </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <!-- ===================== SUNUCU YÖNETİMİ ===================== -->
    <section class="section<?= $panelSection === 'sunucu' ? ' active' : '' ?>" id="sunucu">
      <div class="card" style="text-align:center; padding:48px 24px;">
        <div style="font-size:2rem; color:var(--gold); margin-bottom:12px;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <h3 style="color:var(--gold-light); margin-bottom:10px;">Sunucu Yönetimi</h3>
        <p style="color:var(--ash); font-size:.92rem; line-height:1.6;">Bu bölüm yapım aşamasındadır.</p>
      </div>
    </section>

    <!-- ===================== YASAKLI KELİMELER ===================== -->
    <section class="section<?= $panelSection === 'yasakli-kelimeler' ? ' active' : '' ?>" id="yasakli-kelimeler">
      <div class="card" style="margin-bottom:16px;">
        <div class="card-head">
          <h3>Yasaklı Kelime Ekle</h3>
        </div>
        <p style="font-size:.82rem;color:var(--ash);margin-bottom:14px;line-height:1.55;">
          Kelimeler <code>player.banword</code> tablosuna yazılır; oyun sohbetinde sansürlenir. En fazla 24 bayt.
        </p>
        <form method="post" action="<?= e(url('/admin/banword/ekle')) ?>" class="filters" style="align-items:flex-end;">
          <?= $csrf ?>
          <div class="form-row" style="flex:1;min-width:200px;margin:0;">
            <label>Kelime</label>
            <input name="word" maxlength="24" required placeholder="örn. salak" autocomplete="off">
          </div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Ekle</button>
        </form>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Yasaklı Kelimeler</h3>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:.8rem; color:var(--ash);"><?= number_format($banwordTotal, 0, ',', '.') ?> kelime</span>
            <?php
              $bwRefreshQs = http_build_query(array_filter([
                  'section' => 'yasakli-kelimeler',
                  'banword_q' => $banwordQ !== '' ? $banwordQ : null,
                  'banword_per' => $banwordPerPage !== 20 ? $banwordPerPage : null,
                  'banword_page' => $banwordPage > 1 ? $banwordPage : null,
              ], static fn($v) => $v !== null && $v !== ''));
            ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?' . $bwRefreshQs)) ?>" title="Yenile"><i class="fa-solid fa-arrows-rotate"></i> Yenile</a>
          </div>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="yasakli-kelimeler">
          <input name="banword_q" value="<?= e($banwordQ) ?>" placeholder="Kelime ara..." style="flex:1; min-width:200px;">
          <select name="banword_per" title="Sayfa başına">
            <?php foreach ($banwordPerOptions as $opt): ?>
              <option value="<?= (int) $opt ?>"<?= $banwordPerPage === (int) $opt ? ' selected' : '' ?>><?= (int) $opt ?> / sayfa</option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>Kelime</th>
              <th style="width:100px;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($banwordRows === []): ?>
              <tr><td colspan="2" style="color:var(--ash);">Yasaklı kelime yok.</td></tr>
            <?php else: ?>
              <?php foreach ($banwordRows as $bw): ?>
              <tr>
                <td><code style="color:var(--gold-light);"><?= e((string) $bw['word']) ?></code></td>
                <td class="actions-cell">
                  <form method="post" action="<?= e(url('/admin/banword/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu kelime silinsin mi?');">
                    <?= $csrf ?>
                    <input type="hidden" name="word" value="<?= e((string) $bw['word']) ?>">
                    <button type="submit" title="Sil"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php
          $bmk = static function (int $p, ?int $per = null) use ($banwordQ, $banwordPerPage): string {
              $per = $per ?? $banwordPerPage;
              $qs = http_build_query(array_filter([
                  'section' => 'yasakli-kelimeler',
                  'banword_q' => $banwordQ !== '' ? $banwordQ : null,
                  'banword_per' => $per !== 20 ? $per : null,
                  'banword_page' => $p > 1 ? $p : null,
              ], static fn($v) => $v !== null && $v !== ''));
              return url('/admin' . ($qs !== '' ? '?' . $qs : ''));
          };
        ?>
        <div class="pager">
          <div>
            Sayfa <?= (int) $banwordPage ?> / <?= (int) $banwordPages ?>
            · <?= (int) $banwordPerPage ?> / sayfa
            · Toplam <?= number_format($banwordTotal, 0, ',', '.') ?>
          </div>
          <div class="links">
            <a class="<?= $banwordPage <= 1 ? 'disabled' : '' ?>" href="<?= e($bmk(max(1, $banwordPage - 1))) ?>">Önceki</a>
            <?php
              $bwStart = max(1, $banwordPage - 2);
              $bwEnd = min($banwordPages, $banwordPage + 2);
              for ($i = $bwStart; $i <= $bwEnd; $i++):
            ?>
              <?php if ($i === $banwordPage): ?>
                <span class="cur"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= e($bmk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="<?= $banwordPage >= $banwordPages ? 'disabled' : '' ?>" href="<?= e($bmk(min($banwordPages, $banwordPage + 1))) ?>">Sonraki</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== LOGLAR ===================== -->
    <section class="section<?= $panelSection === 'loglar' ? ' active' : '' ?>" id="loglar">
      <div class="mail-tabs" id="logTabs">
        <a class="btn btn-ghost btn-sm<?= $logTab === 'yonetici' ? ' active' : '' ?>" href="<?= e(url('/admin?section=loglar&log_tab=yonetici')) ?>" style="<?= $logTab === 'yonetici' ? 'color:var(--gold-light);border-color:rgba(201,151,74,.45);background:rgba(201,151,74,.08);' : '' ?>">1. Yönetici Logları</a>
        <a class="btn btn-ghost btn-sm<?= $logTab === 'oyun' ? ' active' : '' ?>" href="<?= e(url('/admin?section=loglar&log_tab=oyun' . (($gameLogs['table'] ?? '') !== '' ? '&game_log=' . rawurlencode((string) $gameLogs['table']) : ''))) ?>" style="<?= $logTab === 'oyun' ? 'color:var(--gold-light);border-color:rgba(201,151,74,.45);background:rgba(201,151,74,.08);' : '' ?>">2. Oyun Logları</a>
      </div>

      <?php if ($logTab !== 'oyun'): ?>
      <div class="card">
        <div class="card-head">
          <h3>Yönetici Logları</h3>
          <span style="font-size:.8rem;color:var(--ash);"><?= number_format($adminLogTotal, 0, ',', '.') ?> kayıt · 10 / sayfa</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="loglar">
          <input type="hidden" name="log_tab" value="yonetici">
          <input name="log_q" value="<?= e($adminLogFilter) ?>" placeholder="Hesap adı veya ID (yetkili / hedef)..." style="flex:1; min-width:200px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Filtrele</button>
          <?php if ($adminLogFilter !== ''): ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin?section=loglar&log_tab=yonetici')) ?>">Temizle</a>
          <?php endif; ?>
        </form>
        <table>
          <thead><tr><th>Zaman</th><th>Yetkili</th><th>İşlem</th><th>Hedef</th><th>Detay</th></tr></thead>
          <tbody>
            <?php if ($adminLogRows === []): ?>
              <tr><td colspan="5" style="color:var(--ash);"><?= $adminLogFilter !== '' ? 'Filtreyle eşleşen kayıt yok.' : 'Henüz yönetici işlemi yok.' ?></td></tr>
            <?php else: ?>
              <?php foreach ($adminLogRows as $log): ?>
              <tr>
                <td style="white-space:nowrap;font-size:.8rem;"><?= e((string) $log['created_label']) ?></td>
                <td>
                  <?= e((string) $log['actor_login']) ?>
                  <?php if (!empty($log['actor_account_id'])): ?>
                    <div style="font-size:.7rem;color:var(--ash);">#<?= (int) $log['actor_account_id'] ?></div>
                  <?php endif; ?>
                </td>
                <td><?= e((string) $log['action']) ?></td>
                <td>
                  <?php
                    $tLogin = trim((string) ($log['target_login'] ?? ''));
                    $tId = (int) ($log['target_account_id'] ?? 0);
                  ?>
                  <?php if ($tLogin !== '' || $tId > 0): ?>
                    <?php if ($tLogin !== ''): ?>
                      <?= e($tLogin) ?>
                    <?php elseif ($tId > 0): ?>
                      <span style="color:var(--ash);">Hesap #<?= $tId ?></span>
                    <?php endif; ?>
                    <?php if ($tId > 0 && $tLogin !== ''): ?>
                      <div style="font-size:.7rem;color:var(--ash);">#<?= $tId ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="color:var(--ash);">—</span>
                  <?php endif; ?>
                </td>
                <td style="color:var(--ash);font-size:.82rem;max-width:280px;"><?= e((string) ($log['detail'] !== '' ? $log['detail'] : '—')) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <?php if ($adminLogPages > 1): ?>
          <?php
            $logMk = static function (int $p) use ($adminLogFilter): string {
                return url('/admin?' . http_build_query(array_filter([
                    'section' => 'loglar',
                    'log_tab' => 'yonetici',
                    'log_q' => $adminLogFilter !== '' ? $adminLogFilter : null,
                    'log_page' => $p > 1 ? $p : null,
                ])));
            };
          ?>
          <div class="pager" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:16px;">
            <a class="btn btn-ghost btn-sm<?= $adminLogPage <= 1 ? ' disabled' : '' ?>" href="<?= $adminLogPage <= 1 ? '#' : e($logMk($adminLogPage - 1)) ?>"<?= $adminLogPage <= 1 ? ' onclick="return false;" style="opacity:.4;pointer-events:none;"' : '' ?>>Önceki</a>
            <?php
              $from = max(1, $adminLogPage - 2);
              $to = min($adminLogPages, $adminLogPage + 2);
              for ($i = $from; $i <= $to; $i++):
            ?>
              <?php if ($i === $adminLogPage): ?>
                <span class="btn btn-ghost btn-sm" style="border-color:var(--gold);"><?= $i ?></span>
              <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= e($logMk($i)) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <a class="btn btn-ghost btn-sm" href="<?= $adminLogPage >= $adminLogPages ? '#' : e($logMk($adminLogPage + 1)) ?>"<?= $adminLogPage >= $adminLogPages ? ' onclick="return false;" style="opacity:.4;pointer-events:none;"' : '' ?>>Sonraki</a>
          </div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div class="card">
        <div class="card-head">
          <h3>Oyun Logları</h3>
          <span style="font-size:.8rem;color:var(--ash);">Son 10 kayıt · <code>log</code> DB</span>
        </div>
        <form class="filters" method="get" action="<?= e(url('/admin')) ?>">
          <input type="hidden" name="section" value="loglar">
          <input type="hidden" name="log_tab" value="oyun">
          <div class="form-row" style="margin:0;min-width:240px;flex:1;">
            <label>Log tablosu</label>
            <select name="game_log" onchange="this.form.submit()">
              <?php if ($gameLogTables === []): ?>
                <option value="">Tablo yok</option>
              <?php else: ?>
                <?php foreach ($gameLogTables as $gt): ?>
                  <option value="<?= e((string) $gt['key']) ?>"<?= ((string) ($gameLogs['table'] ?? '') === (string) $gt['key']) ? ' selected' : '' ?>><?= e((string) $gt['label']) ?> (<?= e((string) $gt['key']) ?>)</option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Göster</button>
        </form>
        <?php if (!empty($gameLogs['error'])): ?>
          <p style="color:var(--blood-light);font-size:.85rem;"><?= e((string) $gameLogs['error']) ?></p>
        <?php elseif ($gameLogColumns === []): ?>
          <p style="color:var(--ash);">Gösterilecek log seçin.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <?php foreach ($gameLogColumns as $col): ?>
                    <th><?= e((string) $col) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php if ($gameLogRows === []): ?>
                  <tr><td colspan="<?= max(1, count($gameLogColumns)) ?>" style="color:var(--ash);">Kayıt yok.</td></tr>
                <?php else: ?>
                  <?php foreach ($gameLogRows as $grow): ?>
                  <tr>
                    <?php foreach ($gameLogColumns as $col): ?>
                      <td style="font-size:.78rem;white-space:nowrap;max-width:220px;overflow:hidden;text-overflow:ellipsis;"><?= e((string) ($grow[$col] ?? '')) ?></td>
                    <?php endforeach; ?>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </section>

  </main>
</div>

<!-- ============ MAIL TEST MODAL ============ -->
<div class="modal-overlay" id="mailTestModal">
  <div class="modal">
    <h3><i class="fa-solid fa-paper-plane"></i> Test maili gönder</h3>
    <p id="mailTestModalHint">Test mailinin gideceği adresi girin. Tamam deyince gönderim başlar.</p>
    <form method="post" action="<?= e(url('/admin/ayarlar/mail/test')) ?>" id="mailTestForm">
      <?= $csrf ?>
      <input type="hidden" name="mail_tab" value="sunucu">
      <input type="hidden" name="server_id" id="mailTestServerId" value="">
      <div class="form-row">
        <label for="mailTestToEmail">Alıcı e-posta</label>
        <input type="email" name="to_email" id="mailTestToEmail" required placeholder="ornek@mail.com" autocomplete="email">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="mailTestCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm" id="mailTestConfirm">Tamam</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ DUYURU MODAL ============ -->
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

<!-- ============ BAN MODAL ============ -->
<div class="modal-overlay" id="banModal">
  <div class="modal">
    <h3><i class="fa-solid fa-gavel"></i> Oyuncuyu Banla</h3>
    <p><b id="banTarget">—</b> hesabını banlamak üzeresin. Oyuna giriş engellenir; panele girebilir.</p>
    <form method="post" action="<?= e(url('/admin/oyuncu/ban')) ?>" id="banForm">
      <?= $csrf ?>
      <input type="hidden" name="account_id" id="banAccountId" value="">
      <input type="hidden" name="account_login" id="banAccountLogin" value="">
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

<!-- ============ YETKİ ATA MODAL ============ -->
<div class="modal-overlay" id="permModal">
  <div class="modal">
    <h3><i class="fa-solid fa-shield-halved"></i> Yetki Grubu Ata</h3>
    <p><b id="permTarget">—</b> hesabına bir veya daha fazla yetki grubu seç. WebPerm 1 gruplarının bayrakları birleşir. Süper Admin tek başına atanır.</p>
    <form method="post" action="<?= e(url('/admin/yetki/ata')) ?>" id="permForm">
      <?= $csrf ?>
      <input type="hidden" name="account_id" id="permAccountId" value="">
      <div class="form-row">
        <label>Yetki grupları</label>
        <div id="permGroupList" style="display:flex;flex-direction:column;gap:8px;max-height:280px;overflow:auto;padding:10px;border:1px solid var(--line);background:var(--obsidian);">
          <?php
            $actorIsSuper = ((int) ($authUser['permission'] ?? 0) === 2);
            foreach ($permissionGroups as $g):
              $gWeb = (int) $g['web_permission'];
              if ($gWeb === 2 && !$actorIsSuper) {
                  continue;
              }
          ?>
            <label class="perm-group-opt" style="display:flex;align-items:flex-start;gap:10px;font-size:.84rem;color:var(--parchment);cursor:pointer;">
              <input
                type="checkbox"
                name="group_ids[]"
                class="perm-group-cb"
                value="<?= (int) $g['id'] ?>"
                data-web="<?= $gWeb ?>"
                style="width:auto;margin-top:3px;"
              >
              <span>
                <strong><?= e((string) $g['name']) ?></strong>
                <span style="color:var(--ash);font-size:.75rem;"> · #<?= (int) $g['id'] ?> · web=<?= $gWeb ?><?= $gWeb === 2 ? ' (tek rol)' : ($gWeb === 0 ? ' (oyuncu)' : '') ?></span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
        <p style="margin:8px 0 0;font-size:.75rem;color:var(--ash);line-height:1.45;">Örn. bir grup yalnızca Oyuncu Yönetimi, diğeri Destek Talepleri açıyorsa ikisini de seç — menü/bayraklar birleşir.</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="permCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Ata</button>
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
      <input type="hidden" name="account_login" id="unbanAccountLogin" value="">
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

<!-- ============ SIRALAMA DETAY ============ -->
<div class="modal-overlay" id="rankDetailModal">
  <div class="modal">
    <h3><i class="fa-solid fa-ranking-star"></i> <span id="rankDetailTitle">Karakter</span></h3>
    <div id="rankDetailBody" class="detail-meta" style="color:var(--ash);font-size:.88rem;"></div>
    <div class="modal-actions" style="margin-top:18px;">
      <button type="button" class="btn btn-ghost btn-sm" id="rankDetailClose">Kapat</button>
    </div>
  </div>
</div>

<!-- ============ LONCA DETAY ============ -->
<div class="modal-overlay" id="guildDetailModal">
  <div class="modal modal-lg">
    <h3><i class="fa-solid fa-shield"></i> <span id="guildDetailTitle">Lonca Detayı</span></h3>
    <div id="guildDetailBody" style="color:var(--ash); font-size:.88rem;">Yükleniyor…</div>
    <div class="modal-actions" style="margin-top:18px;">
      <button type="button" class="btn btn-ghost btn-sm" id="guildDetailClose">Kapat</button>
    </div>
  </div>
</div>

<!-- ============ LONCA ADI ============ -->
<div class="modal-overlay" id="guildRenameModal">
  <div class="modal">
    <h3><i class="fa-solid fa-pen"></i> Lonca Adı Değiştir</h3>
    <form method="post" action="<?= e(url('/admin/lonca/ad')) ?>">
      <?= $csrf ?>
      <input type="hidden" name="guild_id" id="guildRenameId" value="">
      <p style="font-size:.82rem;color:var(--ash);margin-bottom:12px;">Lonca: <strong id="guildRenameLabel" style="color:var(--gold-light);"></strong></p>
      <div class="form-row"><label>Yeni lonca adı</label><input name="name" id="guildRenameName" maxlength="12" required></div>
      <p style="font-size:.75rem;color:var(--ash);margin:-6px 0 12px;">En fazla 12 karakter. Aynı isimde başka lonca olamaz.</p>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="guildRenameCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ BİNEK ADI ============ -->
<div class="modal-overlay" id="horseRenameModal">
  <div class="modal">
    <h3><i class="fa-solid fa-horse"></i> At Adını Değiştir</h3>
    <form method="post" action="<?= e(url('/admin/binek/ad')) ?>">
      <?= $csrf ?>
      <input type="hidden" name="player_id" id="horseRenameId" value="">
      <p style="font-size:.82rem;color:var(--ash);margin-bottom:12px;">
        Karakter: <strong id="horseRenameChar" style="color:var(--gold-light);"></strong>
        · Mevcut: <strong id="horseRenameLabel" style="color:var(--gold-light);"></strong>
      </p>
      <div class="form-row"><label>Yeni at adı</label><input name="name" id="horseRenameName" maxlength="24" required></div>
      <p style="font-size:.75rem;color:var(--ash);margin:-6px 0 12px;">En fazla 24 karakter.</p>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="horseRenameCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ GM DÜZENLE ============ -->
<div class="modal-overlay" id="gmEditModal">
  <div class="modal">
    <h3><i class="fa-solid fa-user-shield"></i> GM Düzenle</h3>
    <form method="post" action="<?= e(url('/admin/gm/guncelle')) ?>">
      <?= $csrf ?>
      <input type="hidden" name="id" id="gmEditId" value="">
      <div class="form-row"><label>Hesap (mAccount)</label><input name="account" id="gmEditAccount" maxlength="32" required></div>
      <div class="form-row"><label>Karakter (mName)</label><input name="name" id="gmEditName" maxlength="32" required></div>
      <div class="form-row">
        <label>Yetki (mAuthority)</label>
        <select name="authority" id="gmEditAuthority" required>
          <?php foreach ($gmAuthorities as $akey => $alabel): ?>
            <option value="<?= e((string) $akey) ?>"><?= e((string) $alabel) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row"><label>Contact IP</label><input name="contact_ip" id="gmEditContact" maxlength="16" placeholder="boş bırakılabilir"></div>
      <div class="form-row"><label>Server IP</label><input name="server_ip" id="gmEditServer" maxlength="16" value="ALL"></div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="gmEditCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ LONCA USTASI ============ -->
<div class="modal-overlay" id="guildMasterModal">
  <div class="modal">
    <h3><i class="fa-solid fa-crown"></i> Lonca Ustası Değiştir</h3>
    <form method="post" action="<?= e(url('/admin/lonca/usta')) ?>">
      <?= $csrf ?>
      <input type="hidden" name="guild_id" id="guildMasterId" value="">
      <p style="font-size:.82rem;color:var(--ash);margin-bottom:12px;">Lonca: <strong id="guildMasterLabel" style="color:var(--gold-light);"></strong></p>
      <div class="form-row">
        <label>Yeni usta (üye listesinden)</label>
        <select name="master_pid" id="guildMasterSelect" required>
          <option value="">Yükleniyor…</option>
        </select>
      </div>
      <p style="font-size:.75rem;color:var(--ash);margin:-6px 0 12px;">Yeni usta bu loncanın üyesi olmalı ve başka bir loncanın ustası olmamalıdır.</p>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="guildMasterCancel">Vazgeç</button>
        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ BİLDİRİM DETAY ============ -->
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

<!-- ============ MAIL ŞABLON ÖNİZLEME ============ -->
<div class="modal-overlay" id="mailPreviewModal">
  <div class="modal modal-lg">
    <h3><i class="fa-solid fa-eye"></i> Şablon önizleme</h3>
    <div class="mail-preview-meta" id="mailPreviewMeta">—</div>
    <iframe class="mail-preview-frame" id="mailPreviewFrame" title="Mail önizleme"></iframe>
    <p style="font-size:.75rem;color:var(--ash);margin:10px 0 0;">Örnek değişkenlerle render edilir (kayıt edilmez).</p>
    <div class="modal-actions" style="margin-top:18px;">
      <button type="button" class="btn btn-ghost btn-sm" id="mailPreviewClose">Kapat</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const navItems = document.querySelectorAll('.nav-item[data-target]');
  const sections = document.querySelectorAll('.section');
  const initialSection = <?= json_encode($panelSection, JSON_UNESCAPED_UNICODE) ?>;
  const playerJsonUrl = <?= json_encode(url('/admin/oyuncu/json'), JSON_UNESCAPED_UNICODE) ?>;
  const playerSearchUrl = <?= json_encode(url('/admin/oyuncu/ara'), JSON_UNESCAPED_UNICODE) ?>;
  const guildJsonUrl = <?= json_encode(url('/admin/lonca/json'), JSON_UNESCAPED_UNICODE) ?>;
  const canPlayerDetail = <?= !empty($permFlags['player_detail']) ? 'true' : 'false' ?>;
  const oyuncularUrl = <?= json_encode(url('/admin?section=oyuncular'), JSON_UNESCAPED_UNICODE) ?>;
  const csrfToken = <?= json_encode(\App\Core\Security::csrfToken(), JSON_UNESCAPED_UNICODE) ?>;
  const emailChangeUrl = <?= json_encode(url('/admin/oyuncu/email'), JSON_UNESCAPED_UNICODE) ?>;
  const resetLinkUrl = <?= json_encode(url('/admin/oyuncu/sifre-link'), JSON_UNESCAPED_UNICODE) ?>;
  const setPasswordUrl = <?= json_encode(url('/admin/oyuncu/sifre'), JSON_UNESCAPED_UNICODE) ?>;
  const setSecurityCodeUrl = <?= json_encode(url('/admin/oyuncu/guvenlik-kodu'), JSON_UNESCAPED_UNICODE) ?>;
  const setSafeboxPasswordUrl = <?= json_encode(url('/admin/oyuncu/depo'), JSON_UNESCAPED_UNICODE) ?>;
  const disable2faUrl = <?= json_encode(url('/admin/oyuncu/2fa-kapat'), JSON_UNESCAPED_UNICODE) ?>;
  const notifListUrl = <?= json_encode(url('/bildirimler/json'), JSON_UNESCAPED_UNICODE) ?>;
  const notifReadUrl = <?= json_encode(url('/bildirimler/okundu'), JSON_UNESCAPED_UNICODE) ?>;
  const isSuperAdmin = <?= $authPermission === 2 ? 'true' : 'false' ?>;
  const isReadOnlyAdmin = <?= \App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null) ? 'true' : 'false' ?>;
  const canResetSecurityCode = <?= !empty($permFlags['reset_security_code']) && !\App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null) ? 'true' : 'false' ?>;
  const canResetSafebox = <?= !empty($permFlags['reset_safebox_password']) && !\App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null) ? 'true' : 'false' ?>;
  const canDisable2fa = <?= !empty($permFlags['disable_2fa']) && !\App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null) ? 'true' : 'false' ?>;
  const canMailOps = <?= !empty($permFlags['player_detail']) && !\App\Services\PermissionService::isReadOnly(is_array($authUser ?? null) ? $authUser : null) ? 'true' : 'false' ?>;
  const adminIndexUrl = <?= json_encode(url('/admin'), JSON_UNESCAPED_UNICODE) ?>;
  const mailPresetsJs = <?= json_encode($mailPresets, JSON_UNESCAPED_UNICODE) ?>;
  const annModalMap = <?= json_encode(array_reduce($overviewAnnouncements, static function (array $map, array $ann): array {
      $map[(string) (int) $ann['id']] = [
          'id' => (int) $ann['id'],
          'title' => (string) $ann['title'],
          'type_name' => (string) ($ann['type_name'] ?: 'Duyuru'),
          'published_label' => (string) $ann['published_label'],
          'author_login' => (string) ($ann['author_login'] ?? ''),
          'body' => \App\Services\AnnouncementService::sanitizeHtml((string) $ann['body']),
      ];
      return $map;
  }, []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

  /** DB'den taze veri için sekme değişiminde tam sayfa yükle */
  function navigateSection(target, extraParams) {
    if (!target) return;
    const params = new URLSearchParams();
    params.set('section', target);
    if (extraParams && typeof extraParams === 'object') {
      Object.keys(extraParams).forEach((k) => {
        if (extraParams[k] != null && extraParams[k] !== '') params.set(k, String(extraParams[k]));
      });
    }
    window.location.assign(adminIndexUrl + '?' + params.toString());
  }

  function showSection(target) {
    if (!target) return;
    document.querySelectorAll('.nav-item').forEach(n => {
      if (n.dataset.target) n.classList.toggle('active', n.dataset.target === target);
    });
    sections.forEach(s => s.classList.toggle('active', s.id === target));
    document.getElementById('sidebar')?.classList.remove('open');
  }

  if (initialSection) showSection(initialSection);

  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      const target = item.dataset.target;
      if (!target) return;
      e.preventDefault();
      // Her menü tıklamasında sunucudan yeniden çek (SPA stale data engeli)
      if (target === initialSection) {
        window.location.reload();
        return;
      }
      navigateSection(target);
    });
  });

  document.querySelectorAll('[data-jump-section]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      navigateSection(el.dataset.jumpSection || el.dataset.target);
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

  // Duyuru editörü — yerel araç çubuğu (CDN yok)
  const annEditor = document.getElementById('annEditor');
  const annHtmlPanel = document.getElementById('annHtmlPanel');
  let annHtmlMode = false;

  function syncHtmlFromEditor() {
    if (annHtmlPanel && annEditor) annHtmlPanel.value = annEditor.innerHTML;
  }
  function syncEditorFromHtml() {
    if (annHtmlPanel && annEditor) annEditor.innerHTML = annHtmlPanel.value;
  }
  function getAnnHtml() {
    if (annHtmlMode && annHtmlPanel) return annHtmlPanel.value || '';
    return annEditor ? (annEditor.innerHTML || '') : '';
  }
  function setAnnHtml(html) {
    const safe = html || '';
    if (annEditor) annEditor.innerHTML = safe;
    if (annHtmlPanel) annHtmlPanel.value = safe;
    if (annHtmlMode) {
      annHtmlMode = false;
      annEditor?.classList.remove('html-mode');
      annHtmlPanel?.classList.remove('open');
    }
  }
  function annExec(cmd, value) {
    if (!annEditor) return;
    if (annHtmlMode) return;
    annEditor.focus();
    if (cmd === 'createLink') {
      const url = window.prompt('Link URL', 'https://');
      if (!url) return;
      document.execCommand('createLink', false, url);
      return;
    }
    if (cmd === 'insertTable') {
      const rows = Math.min(10, Math.max(1, parseInt(window.prompt('Satır sayısı', '3') || '3', 10) || 3));
      const cols = Math.min(10, Math.max(1, parseInt(window.prompt('Sütun sayısı', '3') || '3', 10) || 3));
      let html = '<table><tbody>';
      for (let r = 0; r < rows; r++) {
        html += '<tr>';
        for (let c = 0; c < cols; c++) html += '<td>&nbsp;</td>';
        html += '</tr>';
      }
      html += '</tbody></table><p><br></p>';
      document.execCommand('insertHTML', false, html);
      return;
    }
    if (cmd === 'formatBlock') {
      document.execCommand('formatBlock', false, value || 'p');
      return;
    }
    if (cmd === 'toggleHtml') {
      if (!annHtmlMode) {
        syncHtmlFromEditor();
        annHtmlMode = true;
        annEditor.classList.add('html-mode');
        annHtmlPanel?.classList.add('open');
        annHtmlPanel?.focus();
      } else {
        syncEditorFromHtml();
        annHtmlMode = false;
        annEditor.classList.remove('html-mode');
        annHtmlPanel?.classList.remove('open');
        annEditor.focus();
      }
      return;
    }
    document.execCommand(cmd, false, value || null);
  }
  document.getElementById('annToolbar')?.addEventListener('mousedown', (e) => {
    // Odak kaybını önle
    if (e.target.closest('button,[data-cmd],.ann-tool')) e.preventDefault();
  });
  document.getElementById('annToolbar')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-cmd]');
    if (!btn) return;
    e.preventDefault();
    annExec(btn.dataset.cmd, btn.dataset.value);
  });
  document.getElementById('annForeColor')?.addEventListener('input', (e) => {
    annExec('foreColor', e.target.value);
  });
  document.getElementById('annHiliteColor')?.addEventListener('input', (e) => {
    // Chrome: hiliteColor; Firefox: backColor
    if (!document.execCommand('hiliteColor', false, e.target.value)) {
      document.execCommand('backColor', false, e.target.value);
    }
  });
  function resetAnnForm() {
    const title = document.getElementById('annFormTitle');
    if (title) title.textContent = 'Yeni Duyuru';
    const id = document.getElementById('annId');
    if (id) id.value = '';
    const t = document.getElementById('annTitle');
    if (t) t.value = '';
    const type = document.getElementById('annType');
    if (type) type.value = '';
    const active = document.getElementById('annActive');
    if (active) active.checked = true;
    setAnnHtml('');
    const body = document.getElementById('annBody');
    if (body) body.value = '';
  }
  document.getElementById('annForm')?.addEventListener('submit', (e) => {
    if (annHtmlMode) syncEditorFromHtml();
    const body = document.getElementById('annBody');
    const html = getAnnHtml().trim();
    if (body) body.value = html;
    if (!html || html === '<br>' || html === '<div><br></div>') {
      e.preventDefault();
      alert('İçerik zorunlu.');
    }
  });
  document.getElementById('annReset')?.addEventListener('click', resetAnnForm);
  document.querySelectorAll('[data-edit-ann]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('annFormTitle').textContent = 'Duyuru Düzenle';
      document.getElementById('annId').value = btn.dataset.id || '';
      document.getElementById('annTitle').value = btn.dataset.title || '';
      document.getElementById('annType').value = btn.dataset.type || '';
      document.getElementById('annActive').checked = btn.dataset.active === '1';
      showSection('duyurular');
      const bodies = window.__annBodies || {};
      setTimeout(() => setAnnHtml(bodies[btn.dataset.id] || ''), 60);
    });
  });
  document.querySelectorAll('[data-edit-anntype]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('annTypeFormTitle').textContent = 'Tür Düzenle';
      document.getElementById('annTypeId').value = btn.dataset.id || '';
      document.getElementById('annTypeName').value = btn.dataset.name || '';
      showSection('duyuru-turleri');
    });
  });
  document.getElementById('annTypeReset')?.addEventListener('click', () => {
    document.getElementById('annTypeFormTitle').textContent = 'Yeni Tür';
    document.getElementById('annTypeId').value = '';
    document.getElementById('annTypeName').value = '';
  });

  document.querySelectorAll('[data-edit-market-cat]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('marketCatFormTitle').textContent = 'Kategori Düzenle';
      document.getElementById('marketCatId').value = btn.dataset.id || '';
      document.getElementById('marketCatName').value = btn.dataset.name || '';
      document.getElementById('marketCatSlug').value = btn.dataset.slug || '';
      document.getElementById('marketCatIcon').value = btn.dataset.icon || 'fa-solid fa-box';
      document.getElementById('marketCatSort').value = btn.dataset.sort || '0';
      document.getElementById('marketCatActive').checked = btn.dataset.active === '1';
      if (typeof window.syncMarketCatIconPicker === 'function') window.syncMarketCatIconPicker();
      showSection('nesne-market-kategoriler');
    });
  });
  document.getElementById('marketCatReset')?.addEventListener('click', () => {
    document.getElementById('marketCatFormTitle').textContent = 'Yeni Kategori';
    document.getElementById('marketCatId').value = '';
    document.getElementById('marketCatName').value = '';
    document.getElementById('marketCatSlug').value = '';
    document.getElementById('marketCatIcon').value = 'fa-solid fa-box';
    document.getElementById('marketCatSort').value = '0';
    document.getElementById('marketCatActive').checked = true;
    if (typeof window.syncMarketCatIconPicker === 'function') window.syncMarketCatIconPicker();
  });

  (function marketItemAdmin() {
    const nextSort = <?= (int) ($marketItemNextSort ?? 1) ?>;
    const discActive = document.getElementById('marketItemDiscountActive');
    const discRow = document.getElementById('marketItemDiscountRow');
    const imageUrl = document.getElementById('marketItemImageUrl');
    const imagePrev = document.getElementById('marketItemImagePreview');

    function syncDiscountRow() {
      if (!discRow || !discActive) return;
      discRow.style.display = discActive.checked ? '' : 'none';
    }
    function syncImagePreview() {
      if (!imagePrev) return;
      const u = (imageUrl?.value || '').trim();
      if (!u) {
        imagePrev.innerHTML = '';
        return;
      }
      imagePrev.innerHTML = '<img src="' + u.replace(/"/g, '&quot;') + '" alt="" referrerpolicy="no-referrer" style="width:48px;height:48px;object-fit:contain;image-rendering:pixelated;border:1px solid rgba(255,255,255,.08);padding:4px;">';
    }
    discActive?.addEventListener('change', syncDiscountRow);
    imageUrl?.addEventListener('input', syncImagePreview);
    syncDiscountRow();
    syncImagePreview();

    document.querySelectorAll('[data-edit-market-item]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('marketItemFormTitle').textContent = 'Ürün Düzenle';
        document.getElementById('marketItemId').value = btn.dataset.id || '';
        document.getElementById('marketItemCode').value = btn.dataset.code || '';
        document.getElementById('marketItemName').value = btn.dataset.name || '';
        document.getElementById('marketItemDesc').value = btn.dataset.desc || '';
        document.getElementById('marketItemPrice').value = btn.dataset.price || '0';
        document.getElementById('marketItemDiscountActive').checked = btn.dataset.discountActive === '1';
        document.getElementById('marketItemDiscountPercent').value = btn.dataset.discountPercent || '0';
        document.getElementById('marketItemImageUrl').value = btn.dataset.image || '';
        document.getElementById('marketItemDuration').value = btn.dataset.duration || 'permanent';
        document.getElementById('marketItemCategory').value = btn.dataset.category || '';
        document.getElementById('marketItemSort').value = btn.dataset.sort || '0';
        document.getElementById('marketItemActive').checked = btn.dataset.active === '1';
        const file = document.getElementById('marketItemImageFile');
        if (file) file.value = '';
        syncDiscountRow();
        syncImagePreview();
        showSection('nesne-market-urunler');
      });
    });
    document.getElementById('marketItemReset')?.addEventListener('click', () => {
      document.getElementById('marketItemFormTitle').textContent = 'Yeni Ürün';
      document.getElementById('marketItemId').value = '';
      document.getElementById('marketItemCode').value = '';
      document.getElementById('marketItemName').value = '';
      document.getElementById('marketItemDesc').value = '';
      document.getElementById('marketItemPrice').value = '0';
      document.getElementById('marketItemDiscountActive').checked = false;
      document.getElementById('marketItemDiscountPercent').value = '0';
      document.getElementById('marketItemImageUrl').value = '';
      document.getElementById('marketItemDuration').value = 'permanent';
      document.getElementById('marketItemCategory').value = '';
      document.getElementById('marketItemSort').value = String(nextSort);
      document.getElementById('marketItemActive').checked = true;
      const file = document.getElementById('marketItemImageFile');
      if (file) file.value = '';
      syncDiscountRow();
      syncImagePreview();
    });
  })();

  (function marketCouponsUi() {
    document.querySelectorAll('.coupon-cat-edit').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.getElementById('couponCatId').value = btn.dataset.id || '0';
        document.getElementById('couponCatName').value = btn.dataset.name || '';
        document.getElementById('couponCatCash').value = btn.dataset.cash || '100';
        document.getElementById('couponCatSort').value = btn.dataset.sort || '0';
        document.getElementById('couponCatActive').checked = btn.dataset.active === '1';
        showSection('nesne-market-kuponlar');
      });
    });
    document.getElementById('couponCatReset')?.addEventListener('click', () => {
      document.getElementById('couponCatId').value = '0';
      document.getElementById('couponCatName').value = '';
      document.getElementById('couponCatCash').value = '100';
      document.getElementById('couponCatSort').value = '0';
      document.getElementById('couponCatActive').checked = true;
    });
    const selectAll = document.getElementById('couponSelectAll');
    selectAll?.addEventListener('change', () => {
      document.querySelectorAll('.coupon-row-check').forEach((cb) => {
        cb.checked = !!selectAll.checked;
      });
    });
    document.getElementById('copyCouponCodes')?.addEventListener('click', () => {
      const ta = document.getElementById('couponCodesOut');
      if (!ta) return;
      ta.select();
      navigator.clipboard?.writeText(ta.value).catch(() => {});
    });
  })();

  (function faIconPickers() {
    const icons = [
      'fa-solid fa-box', 'fa-solid fa-box-open', 'fa-solid fa-gift', 'fa-solid fa-cubes',
      'fa-solid fa-khanda', 'fa-solid fa-gun', 'fa-solid fa-hand-fist', 'fa-solid fa-shield',
      'fa-solid fa-shield-halved', 'fa-solid fa-shirt', 'fa-solid fa-hat-wizard', 'fa-solid fa-crown',
      'fa-solid fa-helmet-un', 'fa-solid fa-gem', 'fa-solid fa-ring', 'fa-solid fa-wand-sparkles',
      'fa-solid fa-flask', 'fa-solid fa-heart', 'fa-solid fa-heart-pulse', 'fa-solid fa-pills',
      'fa-solid fa-horse', 'fa-solid fa-paw', 'fa-solid fa-dragon', 'fa-solid fa-dove',
      'fa-solid fa-spider', 'fa-solid fa-bug', 'fa-solid fa-worm', 'fa-solid fa-otter',
      'fa-solid fa-star', 'fa-solid fa-bolt', 'fa-solid fa-fire', 'fa-solid fa-snowflake',
      'fa-solid fa-leaf', 'fa-solid fa-moon', 'fa-solid fa-sun', 'fa-solid fa-cloud',
      'fa-solid fa-wind', 'fa-solid fa-water', 'fa-solid fa-earth-asia', 'fa-solid fa-mountain-sun',
      'fa-solid fa-key', 'fa-solid fa-lock', 'fa-solid fa-coins', 'fa-solid fa-sack-dollar',
      'fa-solid fa-scroll', 'fa-solid fa-book', 'fa-solid fa-book-open', 'fa-solid fa-map',
      'fa-solid fa-map-location-dot', 'fa-solid fa-compass', 'fa-solid fa-location-dot',
      'fa-solid fa-ticket', 'fa-solid fa-tags', 'fa-solid fa-basket-shopping', 'fa-solid fa-cart-shopping',
      'fa-solid fa-user', 'fa-solid fa-users', 'fa-solid fa-skull', 'fa-solid fa-ghost',
      'fa-solid fa-mask', 'fa-solid fa-eye', 'fa-solid fa-hand', 'fa-solid fa-fingerprint',
      'fa-solid fa-hammer', 'fa-solid fa-wrench', 'fa-solid fa-gear', 'fa-solid fa-toolbox',
      'fa-solid fa-fish', 'fa-solid fa-tree', 'fa-solid fa-mountain', 'fa-solid fa-campground',
      'fa-solid fa-chess-knight', 'fa-solid fa-chess-rook', 'fa-solid fa-trophy', 'fa-solid fa-medal',
      'fa-solid fa-gauge-high', 'fa-solid fa-clock', 'fa-solid fa-hourglass-half', 'fa-solid fa-calendar',
      'fa-solid fa-circle-info', 'fa-solid fa-circle-question', 'fa-solid fa-circle-check', 'fa-solid fa-plus',
      'fa-solid fa-flag', 'fa-solid fa-crosshairs', 'fa-solid fa-bomb', 'fa-solid fa-explosion',
    ];

    const bindPicker = (input, preview, toggle, grid, search, fallback) => {
      if (!input || !preview) return;
      const fb = fallback || 'fa-solid fa-star';

      const setPreview = (cls) => {
        const safe = (cls || '').trim() || fb;
        let i = preview.querySelector('i');
        if (!i) {
          preview.innerHTML = '';
          i = document.createElement('i');
          preview.appendChild(i);
        }
        i.className = safe;
      };

      const highlight = () => {
        if (!grid) return;
        const cur = (input.value || '').trim();
        grid.querySelectorAll('button').forEach((b) => {
          b.classList.toggle('active', b.dataset.icon === cur);
        });
      };

      const render = (filter) => {
        if (!grid) return;
        const q = String(filter || '').trim().toLowerCase();
        grid.innerHTML = '';
        icons.filter((cls) => !q || cls.toLowerCase().includes(q)).forEach((cls) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.dataset.icon = cls;
          btn.title = cls;
          btn.innerHTML = '<i class="' + cls + '"></i>';
          btn.addEventListener('click', () => {
            if (input.readOnly || input.disabled) return;
            input.value = cls;
            setPreview(cls);
            highlight();
            input.dispatchEvent(new Event('input', { bubbles: true }));
          });
          grid.appendChild(btn);
        });
        highlight();
      };

      toggle?.addEventListener('change', () => {
        const on = !!toggle.checked;
        grid?.classList.toggle('open', on);
        search?.classList.toggle('open', on);
        if (on) render(search?.value || '');
      });
      search?.addEventListener('input', () => render(search.value));
      input.addEventListener('input', () => {
        setPreview((input.value || '').trim() || fb);
        highlight();
      });
      setPreview(input.value);
      if (grid) render('');
      return { setPreview, highlight, render };
    };

    // Market kategori
    (function marketCatIconPicker() {
      const input = document.getElementById('marketCatIcon');
      const preview = document.getElementById('marketCatIconPreview');
      const toggle = document.getElementById('marketCatIconPickToggle');
      const grid = document.getElementById('marketCatIconGrid');
      const search = document.getElementById('marketCatIconSearch');
      const api = bindPicker(input, preview, toggle, grid, search, 'fa-solid fa-box');
      window.syncMarketCatIconPicker = () => {
        if (!api || !input) return;
        api.setPreview((input.value || '').trim() || 'fa-solid fa-box');
        api.highlight();
      };
    })();

    // Wiki Yönetimi — tüm ikon alanları
    document.querySelectorAll('[data-wiki-icon-pick]').forEach((wrap) => {
      const input = wrap.querySelector('.wiki-icon-input');
      const preview = wrap.querySelector('.icon-pick-preview');
      const toggle = wrap.querySelector('.wiki-icon-toggle');
      const grid = wrap.querySelector('.wiki-icon-grid');
      const search = wrap.querySelector('.wiki-icon-search');
      bindPicker(input, preview, toggle, grid, search, 'fa-solid fa-star');
    });
  })();

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
      const banLogin = document.getElementById('banAccountLogin');
      if (banLogin) banLogin.value = btn.dataset.banLogin || '';
      document.getElementById('banPenaltyId').value = '';
      document.getElementById('banEvidence').value = '';
      banModal.classList.add('open');
    });
  });
  document.getElementById('banCancel').addEventListener('click', () => banModal.classList.remove('open'));
  banModal.addEventListener('click', (e) => { if (e.target === banModal) banModal.classList.remove('open'); });

  // Yetki ata modal (çoklu grup)
  const permModal = document.getElementById('permModal');
  const permTarget = document.getElementById('permTarget');
  const permAccountId = document.getElementById('permAccountId');
  const permForm = document.getElementById('permForm');
  const permCbs = () => [...document.querySelectorAll('.perm-group-cb')];

  const syncPermExclusive = (changed) => {
    const web = parseInt(changed?.dataset.web || '0', 10);
    if (!changed?.checked) return;
    if (web === 2 || web === 0) {
      permCbs().forEach((cb) => {
        if (cb !== changed) cb.checked = false;
      });
      return;
    }
    // WebPerm 1 seçildiğinde Super / Default User kapansın
    permCbs().forEach((cb) => {
      const w = parseInt(cb.dataset.web || '0', 10);
      if (w === 2 || w === 0) cb.checked = false;
    });
  };

  permCbs().forEach((cb) => {
    cb.addEventListener('change', () => syncPermExclusive(cb));
  });

  document.querySelectorAll('[data-perm-id]').forEach(btn => {
    btn.addEventListener('click', () => {
      permTarget.textContent = btn.dataset.permLogin || '—';
      permAccountId.value = btn.dataset.permId || '';
      let selected = [];
      try {
        selected = JSON.parse(btn.dataset.permGroups || '[]');
      } catch (e) {
        selected = [];
      }
      if (!Array.isArray(selected) || selected.length === 0) {
        const single = parseInt(btn.dataset.permGroup || '0', 10);
        if (single > 0) selected = [single];
      }
      const set = new Set(selected.map((n) => parseInt(n, 10)).filter((n) => n > 0));
      permCbs().forEach((cb) => {
        cb.checked = set.has(parseInt(cb.value, 10));
      });
      const checked = permCbs().find((cb) => cb.checked);
      if (checked) syncPermExclusive(checked);
      permModal.classList.add('open');
    });
  });
  document.getElementById('permCancel')?.addEventListener('click', () => permModal?.classList.remove('open'));
  permModal?.addEventListener('click', (e) => { if (e.target === permModal) permModal.classList.remove('open'); });
  permForm?.addEventListener('submit', (e) => {
    if (!permCbs().some((cb) => cb.checked)) {
      e.preventDefault();
      alert('En az bir yetki grubu seçin.');
    }
  });

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
      const unbanLogin = document.getElementById('unbanAccountLogin');
      if (unbanLogin) unbanLogin.value = btn.dataset.unbanLogin || '';
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
        const sec = res.data.security || {};
        const totpOn = !!(sec.totp_enabled && sec.totp_confirmed);
        const totpPending = !totpOn && !!sec.totp_secret_set;
        const totpLabel = totpOn ? 'Aktif' : (totpPending ? 'Kurulumda' : 'Kapalı');
        detailTitle.textContent = a.login || 'Oyuncu';
        let html = '<div class="detail-meta">';
        html += '<div class="row"><span class="k">E-posta</span><span class="v">' + esc(a.email || '—') + '</span></div>';
        html += '<div class="row"><span class="k">IP</span><span class="v">' + esc(a.ip || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Kayıt</span><span class="v">' + esc(a.create_label || '—') + '</span></div>';
        html += '<div class="row"><span class="k">Durum</span><span class="v"><span class="badge ' + esc(a.status_badge || '') + '">' + esc(a.status_label || '—') + '</span></span></div>';
        html += '<div class="row"><span class="k">2FA</span><span class="v">' + esc(totpLabel) + '</span></div>';
        html += '<div class="row"><span class="k">Elmas</span><span class="v">' + Number(a.cash || 0).toLocaleString('tr-TR') + '</span></div>';
        html += '<div class="row"><span class="k">Kurallar</span><span class="v">' + esc(a.rules_accepted_label || 'Hayır') + '</span></div>';
        html += '<div class="row"><span class="k">Gizlilik</span><span class="v">' + esc(a.privacy_accepted_label || 'Hayır') + '</span></div>';
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
          html += '<table><thead><tr><th>Ad</th><th>Sınıf</th><th>Sv.</th><th>Eş</th></tr></thead><tbody>';
          chars.forEach(ch => {
            const spouse = ch.spouse_name || (ch.spouse && ch.spouse.name) || (ch.married ? '—' : 'Bekar');
            html += '<tr><td>' + esc(ch.name) + '</td><td>' + esc(ch.job_label) + '</td><td>' + esc(ch.level) + '</td><td>' + esc(spouse) + '</td></tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';

        const empireLogs = res.data.empire_changes || [];
        html += '<div class="detail-block"><h4>Bayrak Değişimi</h4>';
        if (!empireLogs.length) {
          html += '<div>Bayrak değişim kaydı yok.</div>';
        } else {
          html += '<table><thead><tr><th>Son değişim</th><th>Değişim sayısı</th><th>Güncel bayrak</th></tr></thead><tbody>';
          empireLogs.forEach(er => {
            html += '<tr><td>' + esc(er.time_label || '—') + '</td><td>' + esc(String(er.change_count ?? 0)) + '</td><td>' + esc(er.empire_label || '—') + '</td></tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';

        html += '<div class="detail-block"><h4>Panel Hesap Kayıtları</h4>';
        if (!logs.length) html += '<div>Henüz kayıt yok.</div>';
        else {
          html += '<table><thead><tr><th>Zaman</th><th>İşlem</th><th>Detay</th><th>Yetkili</th></tr></thead><tbody>';
          logs.slice(0, 5).forEach(log => {
            html += '<tr><td>' + esc(log.created_label) + '</td><td>' + esc(log.action_label) + '</td>';
            let det = log.detail || '—';
            if (log.evidence) det += (det !== '—' ? ' · ' : '') + 'Kanıt: ' + log.evidence;
            html += '<td style="color:var(--ash);">' + esc(det) + '</td>';
            html += '<td>' + esc(log.actor_login || '—') + '</td></tr>';
          });
          html += '</tbody></table>';
        }
        html += '</div>';

        const targetWebPerm = Number(a.web_permission || 0);
        const targetIsSuper = targetWebPerm >= 2;
        const blockedOnTarget = !isSuperAdmin && targetIsSuper;
        const canShowOpsSection = a.id && (canMailOps || isSuperAdmin || canResetSecurityCode || canResetSafebox || canDisable2fa || isReadOnlyAdmin || blockedOnTarget);
        if (canShowOpsSection) {
          html += '<div class="detail-ops"><h4>İşlemler</h4>';

          if (isReadOnlyAdmin) {
            html += '<div class="ops-block ops-block-denied"><div class="ops-title">Not Perm</div>';
            html += '<p style="margin:0;font-size:.85rem;color:var(--ash);line-height:1.55;">Ready Only: Bu hesapta işlem yapılamaz. Yalnızca görüntüleme yetkin var.</p></div>';
          } else if (blockedOnTarget) {
            html += '<div class="ops-block ops-block-denied"><div class="ops-title">Not Perm</div>';
            html += '<p style="margin:0;font-size:.85rem;color:var(--ash);line-height:1.55;">İşlem yapılamaz. WebPermission 2 (Süper Admin) hesaplar üzerinde WebPermission 1 ile işlem yapılamaz.</p></div>';
          } else {
          if (canMailOps) {
            html += '<div class="ops-block"><div class="ops-title">E-posta</div>';
            html += '<form method="post" action="' + esc(emailChangeUrl) + '">';
            html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
            html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
            html += '<div class="form-row"><label>E-posta değiştir</label><input name="email" type="email" maxlength="64" required value="' + esc(a.email || '') + '"></div>';
            html += '<button type="submit" class="btn btn-primary btn-sm">E-postayı kaydet</button></form></div>';

            html += '<div class="ops-block"><div class="ops-title">Şifre sıfırlama linki</div>';
            html += '<form method="post" action="' + esc(resetLinkUrl) + '" onsubmit="return confirm(\'Sıfırlama bağlantısı e-postaya gönderilsin mi?\');">';
            html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
            html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
            html += '<button type="submit" class="btn btn-ghost btn-sm">Şifre sıfırlama linki gönder</button></form></div>';
          }

          if (isSuperAdmin) {
            html += '<div class="ops-block"><div class="ops-title">Hesap şifresi (süper admin)</div>';
            html += '<form method="post" action="' + esc(setPasswordUrl) + '">';
            html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
            html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
            html += '<div class="ops-inline">';
            html += '<div class="form-row"><label>Yeni şifre</label><input name="password" type="password" maxlength="16" minlength="4" required></div>';
            html += '<button type="submit" class="btn btn-jade btn-sm">Şifreyi sıfırla</button>';
            html += '</div></form></div>';
          }

          if (canResetSecurityCode) {
            html += '<div class="ops-block"><div class="ops-title">Güvenlik kodu</div>';
            html += '<form method="post" action="' + esc(setSecurityCodeUrl) + '" onsubmit="return confirm(\'Güvenlik kodu sıfırlansın mı?\');">';
            html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
            html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
            html += '<div class="ops-inline">';
            html += '<div class="form-row"><label>Yeni güvenlik kodu (1–6 hane)</label><input name="securitycode" type="text" inputmode="numeric" pattern="\\d{1,6}" maxlength="6" required placeholder="örn. 123456"></div>';
            html += '<button type="submit" class="btn btn-ghost btn-sm">Güvenlik kodunu sıfırla</button>';
            html += '</div></form></div>';
          }

          if (canResetSafebox) {
            html += '<div class="ops-block"><div class="ops-title">Depo şifresi</div>';
            html += '<form method="post" action="' + esc(setSafeboxPasswordUrl) + '" onsubmit="return confirm(\'Oyun depo şifresi sıfırlansın mı?\');">';
            html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
            html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
            html += '<div class="ops-inline">';
            html += '<div class="form-row"><label>Yeni depo şifresi (1–6 hane)</label><input name="safebox_password" type="text" inputmode="numeric" pattern="\\d{1,6}" maxlength="6" required placeholder="örn. 123456"></div>';
            html += '<button type="submit" class="btn btn-ghost btn-sm">Depo şifresini sıfırla</button>';
            html += '</div></form></div>';
          }

          if (canDisable2fa) {
            html += '<div class="ops-block"><div class="ops-title">2FA</div>';
            html += '<p style="margin:0 0 12px;font-size:.85rem;color:var(--ash);line-height:1.55;">Durum: <strong style="color:var(--ink);">' + esc(totpLabel) + '</strong>. Kapatınca TOTP anahtarı silinir; oyuncu yeniden kurmalı.</p>';
            if (totpOn || totpPending) {
              html += '<form method="post" action="' + esc(disable2faUrl) + '" onsubmit="return confirm(\'Bu hesabın 2FA doğrulaması kapatılsın mı?\');">';
              html += '<input type="hidden" name="csrf_token" value="' + esc(csrfToken) + '">';
              html += '<input type="hidden" name="account_id" value="' + esc(String(a.id)) + '">';
              html += '<button type="submit" class="btn btn-ghost btn-sm">2FA kapat</button></form>';
            } else {
              html += '<p style="margin:0;font-size:.85rem;color:var(--ash);">Kapatılacak 2FA yok.</p>';
            }
            html += '</div>';
          }
          }

          html += '</div>';
        }

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

  const rankDetailModal = document.getElementById('rankDetailModal');
  const rankDetailBody = document.getElementById('rankDetailBody');
  const rankDetailTitle = document.getElementById('rankDetailTitle');
  document.querySelectorAll('[data-rank-detail]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!rankDetailModal || !rankDetailBody) return;
      rankDetailTitle.textContent = btn.dataset.rankName || 'Karakter';
      let html = '';
      html += '<div class="row"><span class="k">Karakter</span><span class="v">' + esc(btn.dataset.rankName || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Job</span><span class="v">' + esc(btn.dataset.rankJob || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Level</span><span class="v">' + esc(btn.dataset.rankLevel || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Stamina</span><span class="v">' + esc(btn.dataset.rankStamina || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Lonca</span><span class="v">' + esc(btn.dataset.rankGuild || '—') + '</span></div>';
      html += '<div class="row"><span class="k">Bayrak</span><span class="v">' + esc(btn.dataset.rankEmpire || '—') + '</span></div>';
      rankDetailBody.innerHTML = html;
      rankDetailModal.classList.add('open');
    });
  });
  document.getElementById('rankDetailClose')?.addEventListener('click', () => rankDetailModal?.classList.remove('open'));
  rankDetailModal?.addEventListener('click', (e) => { if (e.target === rankDetailModal) rankDetailModal.classList.remove('open'); });

  // Lonca detay / ad / usta
  const guildDetailModal = document.getElementById('guildDetailModal');
  const guildDetailBody = document.getElementById('guildDetailBody');
  const guildDetailTitle = document.getElementById('guildDetailTitle');
  const guildRenameModal = document.getElementById('guildRenameModal');
  const guildMasterModal = document.getElementById('guildMasterModal');
  const guildMasterSelect = document.getElementById('guildMasterSelect');

  function openGuildDetail(id) {
    if (!guildDetailModal || !guildDetailBody) return;
    guildDetailTitle.textContent = 'Lonca Detayı';
    guildDetailBody.innerHTML = 'Yükleniyor…';
    guildDetailModal.classList.add('open');
    fetch(guildJsonUrl + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        if (!res.ok || !res.data) {
          guildDetailBody.innerHTML = '<div style="color:var(--blood-light);">' + esc(res.error || 'Yüklenemedi') + '</div>';
          return;
        }
        const g = res.data.guild || {};
        const members = res.data.members || [];
        const comments = res.data.comments || [];
        const grades = res.data.grades || [];
        guildDetailTitle.textContent = g.name || 'Lonca';

        let commentsHtml = '';
        if (!comments.length) {
          commentsHtml = '<div style="color:var(--ash);">Yorum / duyuru kaydı yok.</div>';
        } else {
          comments.forEach(c => {
            commentsHtml += '<div class="guild-comment">';
            commentsHtml += '<div class="meta"><span><strong style="color:var(--gold-light);">' + esc(c.name || '—') + '</strong>';
            if (c.notice) commentsHtml += ' <span class="badge-notice">Duyuru</span>';
            commentsHtml += '</span><span>' + esc(c.time_label || '—') + '</span></div>';
            commentsHtml += '<div class="body">' + esc(c.content || '') + '</div>';
            commentsHtml += '</div>';
          });
        }

        let generalHtml = '<div class="detail-meta">';
        generalHtml += '<div class="row"><span class="k">ID</span><span class="v">#' + esc(String(g.id || '')) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Usta</span><span class="v">' + esc(g.master_name || '—') + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Usta Rütbe</span><span class="v">' + esc(g.master_grade_label || '—') + (g.master_grade ? ' (#' + esc(String(g.master_grade)) + ')' : '') + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Lonca Seviye</span><span class="v">' + esc(String(g.level || 0)) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">EXP</span><span class="v">' + Number(g.exp || 0).toLocaleString('tr-TR') + '</span></div>';
        generalHtml += '<div class="row"><span class="k">SP / Skill Puan</span><span class="v">' + esc(String(g.sp || 0)) + ' / ' + esc(String(g.skill_point || 0)) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Ladder</span><span class="v">' + Number(g.ladder_point || 0).toLocaleString('tr-TR') + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Savaş sayısı</span><span class="v">' + esc(String(g.wars != null ? g.wars : ((g.win||0)+(g.draw||0)+(g.loss||0)))) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Galibiyet</span><span class="v">' + esc(String(g.win || 0)) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Beraberlik</span><span class="v">' + esc(String(g.draw || 0)) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Mağlubiyet</span><span class="v">' + esc(String(g.loss || 0)) + '</span></div>';
        generalHtml += '<div class="row"><span class="k">G / B / M</span><span class="v">' + esc(g.record_label || '—') + '</span></div>';
        if (g.win_rate != null) {
          generalHtml += '<div class="row"><span class="k">Galibiyet %</span><span class="v">' + esc(String(g.win_rate)) + '%</span></div>';
        }
        generalHtml += '<div class="row"><span class="k">Yang</span><span class="v">' + Number(g.gold || 0).toLocaleString('tr-TR') + '</span></div>';
        generalHtml += '<div class="row"><span class="k">Üye</span><span class="v">' + esc(String(g.member_count || members.length || 0)) + '</span></div>';
        generalHtml += '</div>';

        const warStats = res.data.war_stats || {};
        const recentWars = res.data.recent_wars || [];
        let warsHtml = '<div class="detail-meta" style="margin-bottom:14px;">';
        warsHtml += '<div class="row"><span class="k">Toplam savaş</span><span class="v">' + esc(String(warStats.wars || g.wars || 0)) + '</span></div>';
        warsHtml += '<div class="row"><span class="k">Galibiyet</span><span class="v" style="color:var(--gold-light);">' + esc(String(warStats.win != null ? warStats.win : (g.win || 0))) + '</span></div>';
        warsHtml += '<div class="row"><span class="k">Beraberlik</span><span class="v">' + esc(String(warStats.draw != null ? warStats.draw : (g.draw || 0))) + '</span></div>';
        warsHtml += '<div class="row"><span class="k">Mağlubiyet</span><span class="v">' + esc(String(warStats.loss != null ? warStats.loss : (g.loss || 0))) + '</span></div>';
        warsHtml += '<div class="row"><span class="k">Galibiyet oranı</span><span class="v">' + esc(String(warStats.win_rate != null ? warStats.win_rate : (g.win_rate || 0))) + '%</span></div>';
        warsHtml += '</div>';
        if (!recentWars.length) {
          warsHtml += '<div style="color:var(--ash);">Bu loncaya ait savaş kaydı yok.</div>';
        } else {
          warsHtml += '<table><thead><tr><th>Tarih</th><th>Rakip</th><th>Tür</th><th>Skor</th><th>Ganimet</th><th>Sonuç</th></tr></thead><tbody>';
          recentWars.forEach(w => {
            const gid = Number(g.id || 0);
            const opponent = Number(w.from_id) === gid ? (w.to_name || '—') : (w.from_name || '—');
            warsHtml += '<tr>';
            warsHtml += '<td style="white-space:nowrap;font-size:.8rem;">' + esc(w.reserved_label || '—') + '</td>';
            warsHtml += '<td>' + esc(opponent) + '</td>';
            warsHtml += '<td>' + esc(w.war_type_label || '—') + '</td>';
            warsHtml += '<td style="color:var(--gold-light);">' + esc(w.score_label || '—') + '</td>';
            warsHtml += '<td>' + esc(w.warprice_label || '—') + '</td>';
            warsHtml += '<td>' + esc(w.status_label || '—') + '</td>';
            warsHtml += '</tr>';
          });
          warsHtml += '</tbody></table>';
        }

        let gradesHtml = '';
        if (!grades.length) {
          gradesHtml = '<div style="color:var(--ash);">Rütbe tanımı yok.</div>';
        } else {
          gradesHtml = '<table><thead><tr><th>Grade</th><th>Ad</th><th>Yetkiler</th></tr></thead><tbody>';
          grades.forEach(gr => {
            const auth = (gr.auth_list && gr.auth_list.length) ? gr.auth_list.join(', ') : (gr.auth || '—');
            gradesHtml += '<tr><td>' + esc(String(gr.grade || 0)) + '</td><td>' + esc(gr.name || gr.grade_label || '—') + '</td><td style="color:var(--ash);font-size:.8rem;">' + esc(auth) + '</td></tr>';
          });
          gradesHtml += '</tbody></table>';
        }

        let membersHtml = '';
        if (!members.length) {
          membersHtml = '<div style="color:var(--ash);">Bu loncada üye kaydı yok.</div>';
        } else {
          membersHtml = '<table><thead><tr><th>Karakter adı</th><th>Job</th><th>Seviye</th></tr></thead><tbody>';
          members.forEach(m => {
            membersHtml += '<tr>';
            membersHtml += '<td>' + esc(m.character_name || '—') + (m.is_master ? ' <span style="color:var(--gold-light);">★ Usta</span>' : '') + '</td>';
            membersHtml += '<td>' + esc(m.job_label || '—') + '</td>';
            membersHtml += '<td>' + esc(String(m.level || 0)) + '</td>';
            membersHtml += '</tr>';
          });
          membersHtml += '</tbody></table>';
        }

        guildDetailBody.innerHTML =
          '<div class="guild-tabs" id="guildDetailTabs">' +
            '<button type="button" data-guild-tab="comments">1. Yorumlar</button>' +
            '<button type="button" class="active" data-guild-tab="members">2. Üyeler (' + members.length + ')</button>' +
            '<button type="button" data-guild-tab="general">3. Genel</button>' +
            '<button type="button" data-guild-tab="wars">4. Savaşlar</button>' +
            '<button type="button" data-guild-tab="grades">5. Rütbeler</button>' +
          '</div>' +
          '<div class="guild-pane" data-guild-pane="comments">' + commentsHtml + '</div>' +
          '<div class="guild-pane active" data-guild-pane="members">' + membersHtml + '</div>' +
          '<div class="guild-pane" data-guild-pane="general">' + generalHtml + '</div>' +
          '<div class="guild-pane" data-guild-pane="wars">' + warsHtml + '</div>' +
          '<div class="guild-pane" data-guild-pane="grades">' + gradesHtml + '</div>';

        guildDetailBody.querySelectorAll('[data-guild-tab]').forEach(btn => {
          btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-guild-tab');
            guildDetailBody.querySelectorAll('[data-guild-tab]').forEach(b => b.classList.toggle('active', b === btn));
            guildDetailBody.querySelectorAll('[data-guild-pane]').forEach(p => {
              p.classList.toggle('active', p.getAttribute('data-guild-pane') === tab);
            });
          });
        });
      })
      .catch(() => {
        guildDetailBody.innerHTML = '<div style="color:var(--blood-light);">Detay yüklenemedi.</div>';
      });
  }

  document.querySelectorAll('[data-guild-detail]').forEach(btn => {
    btn.addEventListener('click', () => openGuildDetail(btn.dataset.guildDetail));
  });
  document.getElementById('guildDetailClose')?.addEventListener('click', () => guildDetailModal?.classList.remove('open'));
  guildDetailModal?.addEventListener('click', (e) => { if (e.target === guildDetailModal) guildDetailModal.classList.remove('open'); });

  document.getElementById('adminWarTabs')?.querySelectorAll('[data-war-tab]').forEach(btn => {
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

  document.querySelectorAll('[data-guild-rename]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('guildRenameId').value = btn.dataset.guildRename || '';
      document.getElementById('guildRenameLabel').textContent = btn.dataset.guildName || '—';
      document.getElementById('guildRenameName').value = btn.dataset.guildName || '';
      guildRenameModal?.classList.add('open');
      document.getElementById('guildRenameName')?.focus();
    });
  });
  document.getElementById('guildRenameCancel')?.addEventListener('click', () => guildRenameModal?.classList.remove('open'));
  guildRenameModal?.addEventListener('click', (e) => { if (e.target === guildRenameModal) guildRenameModal.classList.remove('open'); });

  const horseRenameModal = document.getElementById('horseRenameModal');
  document.querySelectorAll('[data-horse-rename]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('horseRenameId').value = btn.dataset.horseRename || '';
      document.getElementById('horseRenameLabel').textContent = btn.dataset.horseName || '—';
      document.getElementById('horseRenameChar').textContent = btn.dataset.horseChar || '—';
      document.getElementById('horseRenameName').value = btn.dataset.horseName || '';
      horseRenameModal?.classList.add('open');
      document.getElementById('horseRenameName')?.focus();
    });
  });
  document.getElementById('horseRenameCancel')?.addEventListener('click', () => horseRenameModal?.classList.remove('open'));
  horseRenameModal?.addEventListener('click', (e) => { if (e.target === horseRenameModal) horseRenameModal.classList.remove('open'); });

  const gmEditModal = document.getElementById('gmEditModal');
  document.querySelectorAll('[data-gm-edit]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('gmEditId').value = btn.dataset.gmEdit || '';
      document.getElementById('gmEditAccount').value = btn.dataset.gmAccount || '';
      document.getElementById('gmEditName').value = btn.dataset.gmName || '';
      document.getElementById('gmEditContact').value = btn.dataset.gmContact || '';
      document.getElementById('gmEditServer').value = btn.dataset.gmServer || 'ALL';
      const authSel = document.getElementById('gmEditAuthority');
      if (authSel) authSel.value = btn.dataset.gmAuthority || 'PLAYER';
      gmEditModal?.classList.add('open');
      document.getElementById('gmEditAccount')?.focus();
    });
  });
  document.getElementById('gmEditCancel')?.addEventListener('click', () => gmEditModal?.classList.remove('open'));
  gmEditModal?.addEventListener('click', (e) => { if (e.target === gmEditModal) gmEditModal.classList.remove('open'); });

  document.querySelectorAll('[data-guild-master]').forEach(btn => {
    btn.addEventListener('click', () => {
      const gid = btn.dataset.guildMaster || '';
      const currentPid = String(btn.dataset.guildMasterPid || '');
      document.getElementById('guildMasterId').value = gid;
      document.getElementById('guildMasterLabel').textContent = btn.dataset.guildName || '—';
      if (guildMasterSelect) {
        guildMasterSelect.innerHTML = '<option value="">Yükleniyor…</option>';
      }
      guildMasterModal?.classList.add('open');
      fetch(guildJsonUrl + '?id=' + encodeURIComponent(gid), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
          if (!guildMasterSelect) return;
          const members = (res.data && res.data.members) ? res.data.members : [];
          if (!members.length) {
            guildMasterSelect.innerHTML = '<option value="">Üye bulunamadı</option>';
            return;
          }
          guildMasterSelect.innerHTML = members.map(m => {
            const sel = String(m.pid) === currentPid ? ' selected' : '';
            const mark = m.is_master ? ' (mevcut usta)' : '';
            return '<option value="' + esc(String(m.pid)) + '"' + sel + '>' + esc(m.character_name) + ' · Sv.' + esc(String(m.level || 0)) + mark + '</option>';
          }).join('');
        })
        .catch(() => {
          if (guildMasterSelect) guildMasterSelect.innerHTML = '<option value="">Yüklenemedi</option>';
        });
    });
  });
  document.getElementById('guildMasterCancel')?.addEventListener('click', () => guildMasterModal?.classList.remove('open'));
  guildMasterModal?.addEventListener('click', (e) => { if (e.target === guildMasterModal) guildMasterModal.classList.remove('open'); });

  // Üst arama (hesap / e-posta / karakter)
  (function topPlayerSearch() {
    const input = document.getElementById('topSearchInput');
    const drop = document.getElementById('topSearchDrop');
    const wrap = document.getElementById('topSearchWrap');
    if (!input || !drop || !wrap) return;
    let timer = null;
    let lastQ = '';

    const render = (rows, q) => {
      if (!rows.length) {
        drop.innerHTML = '<div class="empty">“' + esc(q) + '” için sonuç yok.</div>';
        drop.classList.add('open');
        return;
      }
      drop.innerHTML = rows.map(r => {
        const meta = [];
        if (r.email && r.email !== '—') meta.push(esc(r.email));
        if (r.character_name && r.character_name !== '—') meta.push('Karakter: ' + esc(r.character_name));
        meta.push(esc(r.role_label || 'Oyuncu'));
        meta.push(esc(r.status_label || ''));
        return '<button type="button" data-acc-id="' + esc(String(r.id)) + '" data-acc-login="' + esc(r.login || '') + '">'
          + '<span class="s-login">' + esc(r.login || '') + ' <span style="color:var(--ash);font-weight:500;">#' + esc(String(r.id)) + '</span></span>'
          + '<span class="s-meta">' + meta.join(' · ') + '</span>'
          + '</button>';
      }).join('');
      drop.classList.add('open');
      drop.querySelectorAll('[data-acc-id]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.accId;
          drop.classList.remove('open');
          input.blur();
          if (canPlayerDetail) {
            openDetail(id);
          } else {
            window.location.href = oyuncularUrl + '&q=' + encodeURIComponent(btn.dataset.accLogin || '');
          }
        });
      });
    };

    const run = () => {
      const q = input.value.trim();
      if (q.length < 2) {
        drop.classList.remove('open');
        drop.innerHTML = '';
        return;
      }
      if (q === lastQ) return;
      lastQ = q;
      drop.innerHTML = '<div class="empty">Aranıyor…</div>';
      drop.classList.add('open');
      fetch(playerSearchUrl + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
          if (input.value.trim() !== q) return;
          if (!res.ok) {
            drop.innerHTML = '<div class="empty">' + esc(res.error || 'Arama başarısız') + '</div>';
            return;
          }
          render(res.results || [], q);
        })
        .catch(() => {
          drop.innerHTML = '<div class="empty">Arama başarısız.</div>';
        });
    };

    input.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(run, 220);
    });
    input.addEventListener('focus', () => {
      if (drop.innerHTML) drop.classList.add('open');
      else if (input.value.trim().length >= 2) run();
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        drop.classList.remove('open');
        input.blur();
      }
      if (e.key === 'Enter') {
        e.preventDefault();
        const first = drop.querySelector('[data-acc-id]');
        if (first) first.click();
      }
    });
    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) drop.classList.remove('open');
    });
  })();

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

  // Topluluk kuralları
  const ruleFormTitle = document.getElementById('ruleFormTitle');
  const ruleId = document.getElementById('ruleId');
  const ruleTitle = document.getElementById('ruleTitle');
  const ruleDetail = document.getElementById('ruleDetail');
  const ruleP1 = document.getElementById('ruleP1');
  const ruleP2 = document.getElementById('ruleP2');
  const ruleP3 = document.getElementById('ruleP3');
  const ruleSort = document.getElementById('ruleSort');
  const ruleActive = document.getElementById('ruleActive');
  let communityRulesMap = {};
  try {
    const raw = document.getElementById('communityRulesJson')?.textContent || '[]';
    JSON.parse(raw).forEach(r => { communityRulesMap[String(r.id)] = r; });
  } catch (_) { communityRulesMap = {}; }
  function resetRuleForm() {
    if (ruleFormTitle) ruleFormTitle.textContent = 'Yeni Kural';
    if (ruleId) ruleId.value = '';
    if (ruleTitle) ruleTitle.value = '';
    if (ruleDetail) ruleDetail.value = '';
    if (ruleP1) ruleP1.value = '';
    if (ruleP2) ruleP2.value = '';
    if (ruleP3) ruleP3.value = '';
    if (ruleSort) ruleSort.value = '0';
    if (ruleActive) ruleActive.checked = true;
  }
  document.querySelectorAll('[data-edit-rule]').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = communityRulesMap[String(btn.dataset.id || '')];
      if (!row) return;
      if (ruleFormTitle) ruleFormTitle.textContent = 'Kural Düzenle #' + (row.id || '');
      if (ruleId) ruleId.value = String(row.id || '');
      if (ruleTitle) ruleTitle.value = row.title || '';
      if (ruleDetail) ruleDetail.value = row.detail || '';
      if (ruleP1) ruleP1.value = row.penalty_1 || '';
      if (ruleP2) ruleP2.value = row.penalty_2 || '';
      if (ruleP3) ruleP3.value = row.penalty_3 || '';
      if (ruleSort) ruleSort.value = String(row.sort_order ?? 0);
      if (ruleActive) ruleActive.checked = !!row.is_active;
      showSection('kurallar-ayarlari');
      ruleTitle?.focus();
    });
  });
  document.getElementById('ruleReset')?.addEventListener('click', resetRuleForm);

  // Yetki grupları
  const groupFormTitle = document.getElementById('groupFormTitle');
  const groupId = document.getElementById('groupId');
  const groupName = document.getElementById('groupName');
  const groupWeb = document.getElementById('groupWeb');
  function resetGroupForm() {
    if (groupFormTitle) groupFormTitle.textContent = 'Yeni Yetki Grubu';
    if (groupId) groupId.value = '';
    if (groupName) groupName.value = '';
    if (groupWeb) groupWeb.value = '1';
    document.querySelectorAll('#groupFlagsWrap input[data-flag]').forEach(cb => { cb.checked = false; cb.disabled = false; });
    const all = document.getElementById('flagsSelectAll');
    if (all) { all.checked = false; all.disabled = false; }
  }
  function syncFlagsSelectAll() {
    const all = document.getElementById('flagsSelectAll');
    const boxes = [...document.querySelectorAll('#groupFlagsWrap input[data-flag]')].filter(cb => !cb.disabled);
    if (!all || boxes.length === 0) return;
    all.checked = boxes.every(cb => cb.checked);
    all.indeterminate = !all.checked && boxes.some(cb => cb.checked);
  }
  document.getElementById('flagsSelectAll')?.addEventListener('change', (e) => {
    const on = !!e.target.checked;
    document.querySelectorAll('#groupFlagsWrap input[data-flag]').forEach(cb => {
      if (!cb.disabled) cb.checked = on;
    });
  });
  document.querySelectorAll('#groupFlagsWrap input[data-flag]').forEach(cb => {
    cb.addEventListener('change', syncFlagsSelectAll);
  });
  document.querySelectorAll('[data-edit-group]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (groupFormTitle) groupFormTitle.textContent = 'Yetki Grubu Düzenle';
      if (groupId) groupId.value = btn.dataset.id || '';
      if (groupName) groupName.value = btn.dataset.name || '';
      if (groupWeb) groupWeb.value = btn.dataset.web || '1';
      let flags = {};
      try { flags = JSON.parse(btn.dataset.flags || '{}'); } catch (e) { flags = {}; }
      const web = parseInt(btn.dataset.web || '1', 10);
      document.querySelectorAll('#groupFlagsWrap input[data-flag]').forEach(cb => {
        const k = cb.dataset.flag;
        cb.checked = !!flags[k];
        cb.disabled = web === 2;
      });
      const all = document.getElementById('flagsSelectAll');
      if (all) all.disabled = web === 2;
      syncFlagsSelectAll();
      showSection('yetki-gruplari');
      groupName?.focus();
    });
  });
  document.getElementById('groupReset')?.addEventListener('click', resetGroupForm);

  document.querySelectorAll('[data-edit-tcat]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('tcatId').value = btn.dataset.id || '';
      document.getElementById('tcatName').value = btn.dataset.name || '';
      document.getElementById('tcatDesc').value = btn.dataset.description || '';
      showSection('ticket-ayarlari');
    });
  });
  document.querySelectorAll('[data-edit-tstat]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('tstatId').value = btn.dataset.id || '';
      const code = document.getElementById('tstatCode');
      code.value = btn.dataset.code || '';
      code.readOnly = btn.dataset.system === '1';
      document.getElementById('tstatLabel').value = btn.dataset.label || '';
      showSection('ticket-ayarlari');
    });
  });

  document.querySelectorAll('[data-edit-feature]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('featureFormTitle').textContent = 'Özellik Düzenle';
      document.getElementById('featureId').value = btn.dataset.id || '';
      document.getElementById('featureIcon').value = btn.dataset.icon || '';
      document.getElementById('featureTitle').value = btn.dataset.title || '';
      document.getElementById('featureBody').value = btn.dataset.body || '';
      showSection('ozellikler-ayarlari');
    });
  });

  function resetDownloadForm() {
    const t = document.getElementById('downloadFormTitle');
    if (t) t.textContent = 'Yeni Link';
    const id = document.getElementById('downloadId');
    if (id) id.value = '';
    document.getElementById('downloadTitle').value = '';
    document.getElementById('downloadUrl').value = '';
    document.getElementById('downloadDescription').value = '';
    document.getElementById('downloadPack').value = 'normal';
  }
  document.querySelectorAll('[data-edit-download]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('downloadFormTitle').textContent = 'Linki Düzenle';
      document.getElementById('downloadId').value = btn.dataset.id || '';
      document.getElementById('downloadTitle').value = btn.dataset.title || '';
      document.getElementById('downloadUrl').value = btn.dataset.url || '';
      document.getElementById('downloadDescription').value = btn.dataset.description || '';
      document.getElementById('downloadPack').value = btn.dataset.pack || 'normal';
      showSection('patch-linkleri');
      document.getElementById('downloadTitle').focus();
    });
  });
  document.getElementById('downloadReset')?.addEventListener('click', resetDownloadForm);

  function resetFooterLinkForm() {
    const t = document.getElementById('footerLinkFormTitle');
    if (t) t.textContent = 'Yeni Link';
    document.getElementById('footerLinkId').value = '';
    document.getElementById('footerLinkColumn').value = 'server';
    document.getElementById('footerLinkLabel').value = '';
    document.getElementById('footerLinkUrl').value = '';
  }
  document.querySelectorAll('[data-edit-footer-link]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('footerLinkFormTitle').textContent = 'Linki Düzenle';
      document.getElementById('footerLinkId').value = btn.dataset.id || '';
      document.getElementById('footerLinkColumn').value = btn.dataset.column || 'server';
      document.getElementById('footerLinkLabel').value = btn.dataset.label || '';
      document.getElementById('footerLinkUrl').value = btn.dataset.url || '';
      showSection('footer-ayarlari');
      document.getElementById('footerLinkLabel').focus();
    });
  });
  document.getElementById('footerLinkReset')?.addEventListener('click', resetFooterLinkForm);

  function resetSocialForm() {
    const t = document.getElementById('socialFormTitle');
    if (t) t.textContent = 'Yeni Sosyal';
    document.getElementById('socialId').value = '';
    document.getElementById('socialName').value = '';
    document.getElementById('socialIcon').value = 'fa-brands fa-discord';
    document.getElementById('socialUrl').value = '';
    document.getElementById('socialActive').value = '1';
  }
  document.querySelectorAll('[data-edit-social]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('socialFormTitle').textContent = 'Sosyal Düzenle';
      document.getElementById('socialId').value = btn.dataset.id || '';
      document.getElementById('socialName').value = btn.dataset.name || '';
      document.getElementById('socialIcon').value = btn.dataset.icon || '';
      document.getElementById('socialUrl').value = btn.dataset.url || '';
      document.getElementById('socialActive').value = btn.dataset.active || '1';
      showSection('footer-ayarlari');
      document.getElementById('socialName').focus();
    });
  });
  document.getElementById('socialReset')?.addEventListener('click', resetSocialForm);

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
<script>
  // Mail ayarları sekmeleri + HTML editör
  (function mailSettings() {
    const tabs = document.querySelectorAll('#mailTabs [data-mail-tab]');
    const panes = document.querySelectorAll('[data-mail-pane]');
    tabs.forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.dataset.mailTab;
        tabs.forEach(b => b.classList.toggle('active', b === btn));
        panes.forEach(p => p.classList.toggle('active', p.dataset.mailPane === key));
      });
    });
    const provider = document.getElementById('mailProvider');
    const custom = document.getElementById('mailCustomFields');
    const syncProvider = () => {
      if (!provider || !custom) return;
      custom.style.display = provider.value === 'custom' ? 'block' : 'none';
      const preset = mailPresetsJs[provider.value];
      if (preset && provider.value !== 'custom') {
        const host = document.getElementById('mailHost');
        const port = document.getElementById('mailPort');
        const enc = document.getElementById('mailEnc');
        if (host) host.value = preset.host || '';
        if (port) port.value = preset.port || 587;
        if (enc) enc.value = preset.encryption || 'tls';
      }
      const hint = document.getElementById('mailProviderHint');
      if (hint) {
        const hints = {
          yandex: 'Yandex / Yandex 360: Host otomatik smtp.yandex.com:465 SSL. smtp.yandex.com.tr kullanma. Hesap=gönderen tam adres (ör. m2dn@trueddn.com.tr). Parola=uygulama şifresi. mail.yandex.com → Posta istemcileri açık olmalı. Kurumsal kutuda Yandex 360 admin izni gerekir.',
          gmail: 'Gmail: Google hesabında 2FA açıkken Uygulama şifresi oluşturun. SMTP: smtp.gmail.com:587 TLS.',
          microsoft: 'Microsoft: SMTP AUTH açık kutu + uygulama şifresi gerekebilir. SMTP: smtp.office365.com:587 TLS.',
          custom: ''
        };
        const text = hints[provider.value] || '';
        hint.style.display = text ? 'block' : 'none';
        hint.textContent = text;
      }
    };
    provider?.addEventListener('change', syncProvider);
    syncProvider();
    document.querySelectorAll('[data-edit-mail]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('mailServerId').value = btn.dataset.id || '';
        document.getElementById('mailServerName').value = btn.dataset.name || '';
        document.getElementById('mailProvider').value = btn.dataset.provider || 'custom';
        document.getElementById('mailHost').value = btn.dataset.host || '';
        document.getElementById('mailPort').value = btn.dataset.port || '587';
        document.getElementById('mailEnc').value = btn.dataset.encryption || 'tls';
        document.getElementById('mailUser').value = btn.dataset.username || '';
        document.getElementById('mailPass').value = '';
        document.getElementById('mailFrom').value = btn.dataset.from || '';
        document.getElementById('mailFromName').value = btn.dataset.fromName || '';
        syncProvider();
        showSection('mail-ayarlari');
        document.querySelector('#mailTabs [data-mail-tab="sunucu"]')?.click();
      });
    });
    document.getElementById('mailServerReset')?.addEventListener('click', () => {
      document.getElementById('mailServerForm')?.reset();
      document.getElementById('mailServerId').value = '';
      syncProvider();
    });
    const mailTestModal = document.getElementById('mailTestModal');
    const openMailTestModal = (serverId, defaultTo, serverName) => {
      if (!mailTestModal) return;
      const sid = String(serverId || '').trim();
      if (!sid) {
        alert('Önce sunucuyu kaydedin, sonra listeden veya düzenleme modunda Test gönderin.');
        return;
      }
      const sidEl = document.getElementById('mailTestServerId');
      const toEl = document.getElementById('mailTestToEmail');
      const hint = document.getElementById('mailTestModalHint');
      if (sidEl) sidEl.value = sid;
      if (toEl) {
        toEl.value = String(defaultTo || '').trim();
      }
      if (hint) {
        const name = String(serverName || '').trim();
        hint.textContent = name
          ? ('«' + name + '» sunucusu ile test maili gönderilecek. Alıcı adresini girip Tamam’a basın.')
          : 'Test mailinin gideceği adresi girin. Tamam deyince gönderim başlar.';
      }
      mailTestModal.classList.add('open');
      setTimeout(() => toEl?.focus(), 50);
    };
    document.getElementById('mailTestCancel')?.addEventListener('click', () => {
      mailTestModal?.classList.remove('open');
    });
    mailTestModal?.addEventListener('click', (e) => {
      if (e.target === mailTestModal) mailTestModal.classList.remove('open');
    });
    document.getElementById('mailTestForm')?.addEventListener('submit', (e) => {
      const to = (document.getElementById('mailTestToEmail')?.value || '').trim();
      if (!to) {
        e.preventDefault();
        alert('Alıcı e-posta gerekli.');
        return;
      }
      const btn = document.getElementById('mailTestConfirm');
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Gönderiliyor…';
      }
    });
    document.getElementById('mailQuickTest')?.addEventListener('click', () => {
      const sid = (document.getElementById('mailServerId')?.value || '').trim();
      const to = (document.getElementById('mailFrom')?.value || document.getElementById('mailUser')?.value || '').trim();
      const name = (document.getElementById('mailServerName')?.value || '').trim();
      openMailTestModal(sid, to, name);
    });
    document.querySelectorAll('[data-mail-test]').forEach(btn => {
      btn.addEventListener('click', () => {
        openMailTestModal(btn.dataset.serverId, btn.dataset.defaultTo, btn.dataset.serverName);
      });
    });
    document.querySelectorAll('form[action*="mail/sablon"]').forEach(form => {
      const wrap = form.querySelector('[data-mail-tpl-wrap]');
      const editor = form.querySelector('[data-html-editor]');
      const source = form.querySelector('[data-html-source]');
      const hidden = form.querySelector('textarea[name="body_html"]');
      const toggleBtn = form.querySelector('[data-html-toggle]');
      let htmlMode = false;

      const decodeEntities = (str) => {
        const ta = document.createElement('textarea');
        ta.innerHTML = String(str || '');
        return ta.value;
      };
      const normalizeBodyHtml = (raw) => {
        let html = String(raw || '');
        const asTextMatch = () => {
          // Görsel editör: HTML kodu metin olarak yapıştırıldıysa
          if (!editor) return html;
          const asText = (editor.textContent || '').trim();
          const asHtml = editor.innerHTML || '';
          if (/<\/?[a-z][\s\S]*>/i.test(asText) && /&lt;\/?[a-z!]/i.test(asHtml)) {
            return asText;
          }
          return asHtml;
        };
        if (htmlMode && source) {
          html = source.value || '';
        } else {
          html = asTextMatch();
        }
        let guard = 0;
        while (guard < 3 && /&lt;\/?[a-z!]/i.test(html)) {
          const next = decodeEntities(html);
          if (next === html) break;
          html = next;
          guard += 1;
        }
        return html;
      };
      const syncHidden = () => {
        if (!hidden) return;
        hidden.value = normalizeBodyHtml();
      };
      const getBodyHtml = () => normalizeBodyHtml();

      form.addEventListener('submit', () => { syncHidden(); });

      form.querySelectorAll('[data-mail-toolbar] button[data-cmd]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const cmd = btn.dataset.cmd || '';
          if (cmd === 'toggleHtml') {
            htmlMode = !htmlMode;
            if (htmlMode) {
              if (source && editor) {
                const asText = (editor.textContent || '').trim();
                const asHtml = editor.innerHTML || '';
                // Görsel alana HTML kodu metin olarak yapıştırıldıysa kaynakta gerçek HTML göster
                if (/<\/?[a-z][\s\S]*>/i.test(asText) && /&lt;\/?[a-z]/i.test(asHtml)) {
                  source.value = asText;
                } else {
                  source.value = asHtml;
                }
              }
              editor?.classList.add('html-mode');
              source?.classList.add('open');
              toggleBtn?.classList.add('active-mode');
              source?.focus();
            } else {
              if (source && editor) {
                const raw = source.value || '';
                // Tam HTML / tablo şablonları görsel moda geçince <p> ile parçalanır
                if (/<!DOCTYPE|<\s*html[\s>]/i.test(raw) || (/<\s*table[\s>]/i.test(raw) && /<\s*tr[\s>]/i.test(raw) && raw.length > 600)) {
                  alert('Tam HTML şablonlar görsel modda bozulur. HTML modunda düzenleyip kaydedin.');
                  return;
                }
                editor.innerHTML = raw;
              }
              editor?.classList.remove('html-mode');
              source?.classList.remove('open');
              toggleBtn?.classList.remove('active-mode');
              editor?.focus();
            }
            syncHidden();
            return;
          }
          if (htmlMode) return;
          if (!editor) return;
          editor.focus();
          if (cmd === 'createLink') {
            const url = prompt('Link URL', 'https://');
            if (url) document.execCommand('createLink', false, url);
            syncHidden();
            return;
          }
          if (cmd === 'insertTable') {
            document.execCommand('insertHTML', false, '<table><tr><th>Başlık</th><th>Başlık</th></tr><tr><td>Hücre</td><td>Hücre</td></tr></table><p></p>');
            syncHidden();
            return;
          }
          if (cmd === 'formatBlock') {
            document.execCommand('formatBlock', false, btn.dataset.value || 'p');
            syncHidden();
            return;
          }
          document.execCommand(cmd, false, null);
          syncHidden();
        });
      });

      form.querySelector('[data-fore-color]')?.addEventListener('input', (e) => {
        if (htmlMode || !editor) return;
        editor.focus();
        document.execCommand('foreColor', false, e.target.value);
        syncHidden();
      });

      form.querySelector('[data-insert-var]')?.addEventListener('change', (e) => {
        const val = e.target.value;
        e.target.value = '';
        if (!val) return;
        if (htmlMode && source) {
          const start = source.selectionStart || 0;
          const end = source.selectionEnd || 0;
          const text = source.value || '';
          source.value = text.slice(0, start) + val + text.slice(end);
          source.focus();
          source.selectionStart = source.selectionEnd = start + val.length;
        } else if (editor) {
          editor.focus();
          document.execCommand('insertText', false, val);
        }
        syncHidden();
      });

      editor?.addEventListener('input', syncHidden);
      source?.addEventListener('input', syncHidden);
      form._mailGetBodyHtml = getBodyHtml;
    });

    const previewModal = document.getElementById('mailPreviewModal');
    const previewMeta = document.getElementById('mailPreviewMeta');
    const previewFrame = document.getElementById('mailPreviewFrame');
    const previewClose = document.getElementById('mailPreviewClose');
    const sampleVars = {
      app: <?= json_encode($appName, JSON_UNESCAPED_UNICODE) ?>,
      login: 'ornek_oyuncu',
      email: 'ornek@mail.com',
      link: <?= json_encode(rtrim((string) (\App\Core\Config::get('app.url', 'http://127.0.0.1:8080')), '/'), JSON_UNESCAPED_UNICODE) ?>,
      logo: <?= json_encode(\App\Services\MailService::logoUrl(), JSON_UNESCAPED_UNICODE) ?>,
      logo_width: <?= json_encode((string) \App\Services\MailService::logoWidth(), JSON_UNESCAPED_UNICODE) ?>,
      reason: 'Kural ihlali örneği',
      code: 'M2DN-4821',
      subject: 'Destek konusu örneği',
    };
    const renderMailTpl = (text, vars) => String(text || '').replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (_, k) => (vars[k] != null ? String(vars[k]) : ''));
    const decodeHtmlEntities = (str) => {
      const ta = document.createElement('textarea');
      ta.innerHTML = String(str || '');
      return ta.value;
    };
    const resolvePreviewHtml = (raw, editorEl, sourceEl, htmlMode) => {
      let html = '';
      if (htmlMode && sourceEl) {
        html = sourceEl.value || '';
      } else if (editorEl) {
        // Görsel editörde HTML kodu düz metin olarak yapıştırıldıysa textContent kullan
        const asText = (editorEl.textContent || '').trim();
        const asHtml = editorEl.innerHTML || '';
        if (/<\/?[a-z][\s\S]*>/i.test(asText) && /&lt;\/?[a-z]/i.test(asHtml)) {
          html = asText;
        } else {
          html = asHtml;
        }
      } else {
        html = String(raw || '');
      }
      // Entity kaçışlı HTML'i çöz (&lt;p&gt; → <p>)
      let guard = 0;
      while (guard < 3 && /&lt;\/?[a-z!]/i.test(html)) {
        const next = decodeHtmlEntities(html);
        if (next === html) break;
        html = next;
        guard += 1;
      }
      return html;
    };
    const writePreviewDoc = (frame, bodyHtml) => {
      const full = '<!DOCTYPE html><html><head><meta charset="utf-8">'
        + '<style>body{font-family:Segoe UI,Arial,sans-serif;font-size:14px;line-height:1.55;color:#222;margin:16px;background:#fff;}'
        + 'a{color:#0b57d0;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ccc;padding:6px 8px;}'
        + 'img{max-width:100%;height:auto;}</style></head><body>' + bodyHtml + '</body></html>';
      try {
        const doc = frame.contentDocument || frame.contentWindow?.document;
        if (doc) {
          doc.open();
          doc.write(full);
          doc.close();
          return;
        }
      } catch (err) {}
      frame.srcdoc = full;
    };
    document.querySelectorAll('[data-mail-preview]').forEach(btn => {
      btn.addEventListener('click', () => {
        const form = btn.closest('form');
        if (!form || !previewModal || !previewFrame) return;
        const subjectInput = form.querySelector('input[name="subject"]');
        const nameEl = form.querySelector('strong');
        const editorEl = form.querySelector('[data-html-editor]');
        const sourceEl = form.querySelector('[data-html-source]');
        const htmlMode = !!(sourceEl && sourceEl.classList.contains('open'));
        const subjectRaw = subjectInput ? subjectInput.value : '';
        const bodyRaw = resolvePreviewHtml('', editorEl, sourceEl, htmlMode);
        const subject = renderMailTpl(subjectRaw, sampleVars);
        const body = renderMailTpl(bodyRaw, sampleVars);
        if (previewMeta) {
          previewMeta.innerHTML = '<div><b>Şablon:</b> ' + (nameEl ? nameEl.textContent.trim() : '—') + '</div>'
            + '<div style="margin-top:6px;"><b>Konu:</b> ' + String(subject).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])) + '</div>';
        }
        writePreviewDoc(previewFrame, body);
        previewModal.classList.add('open');
      });
    });
    previewClose?.addEventListener('click', () => previewModal?.classList.remove('open'));
    previewModal?.addEventListener('click', (e) => { if (e.target === previewModal) previewModal.classList.remove('open'); });
  })();

  // Bildirim zili
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
      if (icon) {
        icon.className = hasAny ? 'fa-solid fa-bell' : 'fa-solid fa-bell-slash';
      }
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

    markAll?.addEventListener('click', () => {
      markRead(null).then(() => load());
    });

    list.addEventListener('click', (e) => {
      const item = e.target.closest('.notif-item');
      if (!item) return;
      const id = item.dataset.nid;
      const title = item.dataset.title || 'Bildirim';
      const body = item.dataset.body || '';
      const link = item.dataset.link || '';
      markRead(id).then(() => load());
      drop.classList.remove('open');
      if (detailTitle) detailTitle.textContent = title;
      if (detailBody) detailBody.textContent = body || 'Detay yok.';
      if (detailGo) {
        if (link) {
          detailGo.href = link;
          detailGo.style.display = '';
        } else {
          detailGo.style.display = 'none';
        }
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
<script>
  (function captchaSettingsHint() {
    const sel = document.getElementById('captchaProvider');
    const hint = document.getElementById('captchaProviderHint');
    if (!sel || !hint) return;
    const meta = <?= json_encode(array_map(static function (array $p): array {
        return ['label' => (string) ($p['label'] ?? ''), 'script' => (string) ($p['script'] ?? ''), 'verify' => (string) ($p['verify'] ?? '')];
    }, \App\Services\CaptchaService::config()['providers']), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
    function sync() {
      const p = meta[sel.value] || {};
      hint.innerHTML = '<strong>' + (p.label || sel.value) + '</strong><br>Script: <code>' + (p.script || '—') + '</code><br>Verify: <code>' + (p.verify || '—') + '</code>';
    }
    sel.addEventListener('change', sync);
    sync();
  })();
</script>
<script>
  (function privacyEditor() {
    const editor = document.getElementById('privacyEditor');
    const htmlPanel = document.getElementById('privacyHtmlPanel');
    const bodyField = document.getElementById('privacyBody');
    const form = document.getElementById('privacyForm');
    const toolbar = document.getElementById('privacyToolbar');
    if (!editor || !bodyField || !form) return;
    let htmlMode = false;
    const initial = bodyField.value || '';
    editor.innerHTML = initial;
    if (htmlPanel) htmlPanel.value = initial;

    function syncHidden() {
      bodyField.value = htmlMode && htmlPanel ? htmlPanel.value : editor.innerHTML;
    }
    function exec(cmd, value) {
      if (htmlMode && cmd !== 'toggleHtml') return;
      editor.focus();
      if (cmd === 'createLink') {
        const url = window.prompt('Link URL', 'https://');
        if (!url) return;
        document.execCommand('createLink', false, url);
        return;
      }
      if (cmd === 'formatBlock') {
        document.execCommand('formatBlock', false, value || 'p');
        return;
      }
      if (cmd === 'toggleHtml') {
        if (!htmlMode) {
          if (htmlPanel) htmlPanel.value = editor.innerHTML;
          htmlMode = true;
          editor.classList.add('html-mode');
          htmlPanel?.classList.add('open');
          htmlPanel?.focus();
        } else {
          if (htmlPanel) editor.innerHTML = htmlPanel.value;
          htmlMode = false;
          editor.classList.remove('html-mode');
          htmlPanel?.classList.remove('open');
          editor.focus();
        }
        return;
      }
      document.execCommand(cmd, false, value || null);
    }
    toolbar?.addEventListener('mousedown', (e) => {
      if (e.target.closest('button,[data-pcmd]')) e.preventDefault();
    });
    toolbar?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-pcmd]');
      if (!btn) return;
      exec(btn.getAttribute('data-pcmd'), btn.getAttribute('data-value'));
    });
    editor.addEventListener('input', syncHidden);
    htmlPanel?.addEventListener('input', syncHidden);
    form.addEventListener('submit', () => { syncHidden(); });
  })();
</script>
</body>
</html>
