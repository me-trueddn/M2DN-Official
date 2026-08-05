<?php
/** @var string $appName */
/** @var string $csrf */
/** @var array|null $authUser */
/** @var array $account */
/** @var list<array> $characters */
/** @var list<array> $activity */
/** @var list<array> $gameLogins */
/** @var array $security */
/** @var array $currentServer */
/** @var array $siteBrand */

$appName = $appName ?? 'M2DN';
$csrf = $csrf ?? '';
$authUser = is_array($authUser ?? null) ? $authUser : null;
$account = is_array($account ?? null) ? $account : [];
$characters = is_array($characters ?? null) ? $characters : [];
$activity = is_array($activity ?? null) ? $activity : [];
$gameLogins = is_array($gameLogins ?? null) ? $gameLogins : [];
$security = is_array($security ?? null) ? $security : [];
$currentServer = is_array($currentServer ?? null) ? $currentServer : [];
$totpOn = !empty($security['totp_enabled']);
$ipLockOn = !empty($security['ip_lock_enabled']);
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandAdminSize = (int) ($siteBrand['admin_size'] ?? 36);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e((string) ($account['login'] ?? 'Oyuncu')) ?> · Yönetim | <?= e($appName) ?></title>
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
    --font-display:'Cinzel', serif; --font-body:'Inter', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--obsidian); color:var(--parchment); font-family:var(--font-body); min-height:100vh;}
  a{color:inherit; text-decoration:none;}
  h1,h2,h3{font-family:var(--font-display);}
  .wrap{max-width:1100px; margin:0 auto; padding:28px 22px 80px;}
  .top{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap;}
  .brand{display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-weight:800; color:var(--gold-light); letter-spacing:.06em;}
  .brand img{width:<?= $brandAdminSize ?>px; height:<?= $brandAdminSize ?>px; object-fit:contain;}
  .brand span{color:var(--blood-light);}
  .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 16px; font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; border:1px solid var(--line); color:var(--gold-light);}
  .btn:hover{background:rgba(201,151,74,.08);}
  .grid{display:grid; gap:18px; grid-template-columns:1.2fr 1fr;}
  .card{background:var(--obsidian-2); border:1px solid var(--line); padding:22px;}
  .card h3{font-size:1rem; margin-bottom:14px; color:var(--parchment);}
  .meta{display:grid; gap:10px;}
  .meta .row{display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid rgba(201,151,74,.08); font-size:.88rem;}
  .meta .k{color:var(--ash);}
  .meta .v{color:var(--gold-light); font-weight:600; text-align:right; word-break:break-all;}
  .badge{display:inline-flex; padding:4px 10px; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600;}
  .badge.active{background:rgba(51,89,74,.2); color:var(--jade-light);}
  .badge.banned{background:rgba(143,28,41,.2); color:var(--blood-light);}
  table{width:100%; border-collapse:collapse; font-size:.84rem;}
  th{text-align:left; padding:8px 10px; color:var(--ash); font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--line);}
  td{padding:11px 10px; border-bottom:1px solid rgba(201,151,74,.08);}
  .empty{color:var(--ash); font-size:.85rem; padding:8px 0;}
  .stack{display:grid; gap:18px;}
  @media (max-width:900px){.grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <a class="brand" href="<?= e(url('/admin?section=oyuncular')) ?>">
        <img src="<?= e($brandIcon) ?>" alt="<?= e($appName) ?>">
        M2<span>DN</span>
      </a>
      <a class="btn" href="<?= e(url('/admin?section=oyuncular')) ?>"><i class="fa-solid fa-arrow-left"></i> Oyuncu Listesi</a>
    </div>

    <div style="margin-bottom:20px;">
      <h1 style="font-size:1.55rem;"><?= e((string) ($account['login'] ?? '')) ?></h1>
      <div style="color:var(--ash); font-size:.85rem; margin-top:4px;">
        <?= e($currentServer['name'] ?? 'M2DN') ?> · Hesap #<?= (int) ($account['id'] ?? 0) ?>
      </div>
    </div>

    <div class="grid" style="margin-bottom:18px;">
      <div class="card">
        <h3>Hesap Bilgileri</h3>
        <div class="meta">
          <div class="row"><span class="k">E-posta</span><span class="v"><?= e((string) ($account['email'] ?? '—')) ?></span></div>
          <div class="row"><span class="k">IP</span><span class="v"><?= e((string) ($account['ip'] ?? '—')) ?></span></div>
          <div class="row"><span class="k">Kayıt</span><span class="v"><?= e((string) ($account['create_label'] ?? '—')) ?></span></div>
          <div class="row"><span class="k">Durum</span><span class="v"><span class="badge <?= e((string) ($account['status_badge'] ?? 'active')) ?>"><?= e((string) ($account['status_label'] ?? '—')) ?></span></span></div>
          <div class="row"><span class="k">Cash</span><span class="v"><?= number_format((int) ($account['cash'] ?? 0), 0, ',', '.') ?></span></div>
          <div class="row"><span class="k">2FA</span><span class="v"><?= $totpOn ? 'Aktif' : 'Kapalı' ?></span></div>
          <div class="row"><span class="k">IP Kilidi</span><span class="v"><?= $ipLockOn ? e((string) ($security['locked_ip'] ?? 'Açık')) : 'Kapalı' ?></span></div>
        </div>
      </div>

      <div class="card">
        <h3>Karakterler (<?= count($characters) ?>)</h3>
        <?php if ($characters === []): ?>
          <div class="empty">Karakter yok.</div>
        <?php else: ?>
          <table>
            <thead><tr><th>Ad</th><th>Sınıf</th><th>Sv.</th><th>Yang</th></tr></thead>
            <tbody>
              <?php foreach ($characters as $ch): ?>
              <tr>
                <td><?= e((string) $ch['name']) ?></td>
                <td><?= e((string) $ch['job_label']) ?></td>
                <td><?= (int) $ch['level'] ?></td>
                <td><?= number_format((int) $ch['gold'], 0, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="stack">
      <div class="card">
        <h3>Panel Hesap Kayıtları</h3>
        <?php if ($activity === []): ?>
          <div class="empty">Henüz panel işlemi yok.</div>
        <?php else: ?>
          <table>
            <thead><tr><th>Zaman</th><th>İşlem</th><th>Detay / Kanıt</th><th>Yetkili</th><th>IP</th></tr></thead>
            <tbody>
              <?php foreach ($activity as $log): ?>
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
                            echo ($det !== '' ? '<br>' : '') . 'Kanıt: ' . e($ev);
                        }
                    }
                  ?>
                </td>
                <td><?= e((string) (($log['actor_login'] ?? '') !== '' ? $log['actor_login'] : '—')) ?></td>
                <td><?= e((string) ($log['ip'] ?: '—')) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>Oyun Giriş Logları <span style="font-weight:400;color:var(--ash);font-size:.75rem;">(log.loginlog)</span></h3>
        <?php if ($gameLogins === []): ?>
          <div class="empty">Oyun giriş kaydı bulunamadı.</div>
        <?php else: ?>
          <table>
            <thead><tr><th>Zaman</th><th>Tür</th><th>Kanal</th><th>PID</th><th>Sv.</th><th>Süre</th></tr></thead>
            <tbody>
              <?php foreach ($gameLogins as $gl): ?>
              <tr>
                <td><?= e((string) $gl['time_label']) ?></td>
                <td><?= e((string) $gl['type_label']) ?></td>
                <td><?= (int) $gl['channel'] ?></td>
                <td><?= (int) $gl['pid'] ?></td>
                <td><?= (int) $gl['level'] ?></td>
                <td><?= (int) $gl['playtime'] ?> dk</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
