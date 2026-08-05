<?php
/** @var string $csrf */
/** @var string $token */
/** @var list<string> $resetErrors */
/** @var string|null $resetSuccess */
/** @var string $appName */
/** @var array $siteBrand */

$appName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'M2DN';
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandLogo = (string) ($siteBrand['logo_url'] ?? '');
$token = isset($token) && is_string($token) ? $token : '';
$resetErrors = isset($resetErrors) && is_array($resetErrors) ? $resetErrors : [];
$csrf = isset($csrf) && is_string($csrf) ? $csrf : \App\Core\Security::csrfField();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Şifre Sıfırla · <?= e($appName) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root{--obsidian:#0b0906;--obsidian-2:#14110c;--line:rgba(201,151,74,.22);--gold:#c9974a;--gold-light:#e8c078;--ash:#9a8f7e;--blood-light:#e07070;--paper:#f3ebe0;}
    *{box-sizing:border-box} body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:radial-gradient(ellipse at 30% 20%,#1a140c,#0b0906 60%);font-family:Georgia,"Times New Roman",serif;color:var(--paper);padding:24px;}
    .card{width:100%;max-width:420px;background:var(--obsidian-2);border:1px solid var(--line);padding:28px 26px;}
    .logo{display:block;max-height:48px;margin:0 auto 18px;}
    h1{font-size:1.35rem;margin:0 0 8px;text-align:center;font-weight:600;}
    .sub{text-align:center;color:var(--ash);font-size:.9rem;margin:0 0 20px;}
    label{display:block;font-size:.78rem;letter-spacing:.04em;text-transform:uppercase;color:var(--ash);margin-bottom:6px;}
    input{width:100%;padding:11px 12px;background:var(--obsidian);border:1px solid var(--line);color:var(--paper);font:inherit;margin-bottom:14px;}
    .btn{width:100%;padding:12px;border:0;background:linear-gradient(135deg,var(--gold),#a67a35);color:#1a1208;font-weight:700;cursor:pointer;font:inherit;}
    .err{background:rgba(224,112,112,.12);border:1px solid rgba(224,112,112,.35);color:var(--blood-light);padding:10px 12px;margin-bottom:14px;font-size:.88rem;}
    .err ul{margin:0;padding-left:18px;}
    a{color:var(--gold-light);text-decoration:none;}
    .foot{text-align:center;margin-top:16px;font-size:.85rem;color:var(--ash);}
  </style>
</head>
<body>
  <div class="card">
    <?php if ($brandLogo !== ''): ?>
      <img class="logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
    <?php endif; ?>
    <h1>Yeni Şifre Belirle</h1>
    <p class="sub">Bağlantı tek kullanımlıktır ve 20 dakika geçerlidir.</p>

    <?php if ($resetErrors !== []): ?>
      <div class="err"><ul><?php foreach ($resetErrors as $err): ?><li><?= e((string) $err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($token === ''): ?>
      <div class="err">Geçersiz veya eksik sıfırlama bağlantısı.</div>
      <p class="foot"><a href="<?= e(url('/')) ?>">Ana sayfaya dön</a></p>
    <?php else: ?>
      <form method="post" action="<?= e(url('/sifre-sifirla')) ?>" autocomplete="off">
        <?= $csrf ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label for="np">Yeni parola</label>
        <input id="np" type="password" name="password" maxlength="16" required minlength="4">
        <label for="npc">Parola tekrar</label>
        <input id="npc" type="password" name="password_confirm" maxlength="16" required minlength="4">
        <button type="submit" class="btn">Şifreyi Güncelle</button>
      </form>
      <p class="foot"><a href="<?= e(url('/giris')) ?>">Giriş sayfasına dön</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
