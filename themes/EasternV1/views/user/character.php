<?php
/** @var string $appName */
/** @var string $csrf */
/** @var array|null $authUser */
/** @var array $character */
/** @var int $maxLevel */
/** @var array $currentServer */
/** @var array $siteBrand */

$appName = $appName ?? 'M2DN';
$csrf = $csrf ?? '';
$authUser = is_array($authUser ?? null) ? $authUser : null;
$character = is_array($character ?? null) ? $character : [];
$maxLevel = max(1, (int) ($maxLevel ?? 99));
$currentServer = is_array($currentServer ?? null) ? $currentServer : [];
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandUserSize = (int) ($siteBrand['user_size'] ?? 36);

$levelPct = \App\Services\PlayerService::levelProgressPercent(
    (int) ($character['level'] ?? 0),
    $maxLevel,
    (int) ($character['level_step'] ?? 0)
);

$lastPlay = '—';
if (!empty($character['last_play']) && $character['last_play'] !== '0000-00-00 00:00:00') {
    $ts = strtotime((string) $character['last_play']);
    if ($ts) {
        $lastPlay = date('d.m.Y H:i', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e((string) ($character['name'] ?? 'Karakter')) ?> | <?= e($appName) ?></title>
<link rel="icon" href="<?= e($brandIcon) ?>">
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
  a{color:inherit; text-decoration:none;}
  h1,h2,h3{font-family:var(--font-display);}
  .wrap{max-width:960px; margin:0 auto; padding:36px 22px 80px;}
  .top{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:28px;}
  .brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; color:var(--gold-light); letter-spacing:.06em;}
  .brand img{width:<?= $brandUserSize ?>px; height:<?= $brandUserSize ?>px; object-fit:contain;}
  .brand span{color:var(--blood-light);}
  .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 16px; font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700;}
  .btn-ghost{border:1px solid var(--line); color:var(--gold-light);}
  .card{background:var(--obsidian-2); border:1px solid var(--line); padding:28px;}
  .char-head{display:flex; gap:22px; align-items:center; margin-bottom:28px;}
  .portrait{width:120px; height:120px; border-radius:50%; background:conic-gradient(var(--gold), var(--blood-light), #33594a, var(--gold)); padding:3px; flex-shrink:0;}
  .portrait-inner{width:100%; height:100%; border-radius:50%; background:#0b0906; overflow:hidden; display:flex; align-items:center; justify-content:center;}
  .portrait-inner img{width:100%; height:100%; object-fit:cover; object-position:center top; display:block;}
  .portrait-inner i{font-size:2rem; color:var(--gold-light);}
  .meta{display:flex; flex-wrap:wrap; gap:14px 22px; margin-top:10px; font-size:.82rem; color:var(--ash);}
  .meta b{color:var(--parchment); font-weight:600;}
  .exp-bar{margin-top:18px;}
  .track{height:8px; background:rgba(233,223,198,.08); overflow:hidden;}
  .fill{height:100%; background:linear-gradient(90deg, var(--blood), var(--gold));}
  .lbl{display:flex; justify-content:space-between; margin-top:8px; font-size:.72rem; color:var(--ash);}
  .grid{display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:22px;}
  .stat{background:var(--obsidian); border:1px solid var(--line); padding:16px;}
  .stat .k{font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; color:var(--ash); margin-bottom:6px;}
  .stat .v{font-size:1.05rem; color:var(--gold-light); font-weight:700;}
  @media (max-width:700px){.grid{grid-template-columns:1fr;}.char-head{flex-direction:column;align-items:flex-start;}}
</style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <a class="brand" href="<?= e(url('/panel')) ?>">
        <img src="<?= e($brandIcon) ?>" alt="<?= e($appName) ?>">
        M2<span>DN</span>
      </a>
      <a class="btn btn-ghost" href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-arrow-left"></i> Panele Dön</a>
    </div>

    <div class="card">
      <div class="char-head">
        <div class="portrait">
          <div class="portrait-inner">
            <?php if (!empty($character['job_gif'])): ?>
              <img src="<?= e((string) $character['job_gif']) ?>" alt="<?= e((string) ($character['job_label'] ?? '')) ?>" loading="lazy">
            <?php else: ?>
              <i class="fa-solid <?= e((string) ($character['job_icon'] ?? 'fa-user')) ?>"></i>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <h1><?= e((string) ($character['name'] ?? '')) ?></h1>
          <div class="meta">
            <span>Sınıf <b><?= e((string) ($character['job_label'] ?? '—')) ?></b></span>
            <span>Seviye <b><?= (int) ($character['level'] ?? 0) ?> / <?= (int) $maxLevel ?></b></span>
            <span>Krallık <b><?= e((string) ($character['empire_label'] ?? '—')) ?></b></span>
            <span>Klan <b><?= e((string) ($character['guild'] ?? '—')) ?></b></span>
          </div>
          <div class="exp-bar">
            <div class="track"><div class="fill" style="width:<?= (int) $levelPct ?>%"></div></div>
            <div class="lbl">
              <span>Seviye ilerlemesi</span>
              <span>%<?= (int) $levelPct ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid">
        <div class="stat"><div class="k">Yang</div><div class="v"><?= number_format((int) ($character['gold'] ?? 0), 0, ',', '.') ?></div></div>
        <div class="stat"><div class="k">Oyun Süresi</div><div class="v"><?= e((string) ($character['playtime_label'] ?? '—')) ?></div></div>
        <div class="stat"><div class="k">Son Oyun</div><div class="v"><?= e($lastPlay) ?></div></div>
      </div>
    </div>
  </div>
</body>
</html>
