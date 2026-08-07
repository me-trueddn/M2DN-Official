<?php
/** @var string $appName */
/** @var string $appTagline */
/** @var string $csrf */
/** @var list<string>|mixed $errors */
/** @var array|mixed $old */
/** @var mixed $success */
/** @var array $siteBrand */

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$success = is_string($success ?? null) ? $success : null;
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandLogo = (string) ($siteBrand['logo_url'] ?? asset('img/logo-nav.svg'));
$brandHomeSize = (int) ($siteBrand['home_size'] ?? 48);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kayıt Ol | <?= e($appName) ?></title>
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
    --font-display:'Cinzel', serif; --font-brush:'Ma Shan Zheng', cursive; --font-body:'Inter', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
    min-height:100vh;
    background:
      radial-gradient(ellipse 700px 420px at 80% 10%, rgba(143,28,41,.2), transparent 60%),
      radial-gradient(ellipse 600px 400px at 10% 90%, rgba(51,89,74,.14), transparent 60%),
      var(--obsidian);
    color:var(--parchment);
    font-family:var(--font-body);
    display:flex; align-items:center; justify-content:center;
    padding:32px 18px;
  }
  a{color:inherit; text-decoration:none;}
  h1{font-family:var(--font-display); letter-spacing:.02em;}
  ::selection{background:var(--blood); color:var(--gold-light);}

  .wrap{width:100%; max-width:440px;}
  .brand{
    display:flex; align-items:center; justify-content:center; gap:0;
    margin-bottom:22px; line-height:0;
  }
  .brand:hover{opacity:.92;}
  .brand-logo{height:<?= max(32, $brandHomeSize) ?>px; width:auto; display:block; object-fit:contain;}

  .card{
    background:var(--obsidian-2);
    border:1px solid var(--line);
    padding:34px 30px 28px;
    clip-path:polygon(14px 0,100% 0,100% calc(100% - 14px),calc(100% - 14px) 100%,0 100%,0 14px);
  }
  .eyebrow{
    font-family:var(--font-brush); font-size:1.25rem; color:var(--blood-light);
    display:inline-flex; align-items:center; gap:.5rem; margin-bottom:8px;
  }
  .eyebrow::before{content:""; width:22px; height:1px; background:var(--gold);}
  .card h1{font-size:1.55rem; margin-bottom:8px;}
  .card .sub{color:var(--ash); font-size:.9rem; line-height:1.6; margin-bottom:24px;}

  .form-row{margin-bottom:16px;}
  .form-row label{
    display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em;
    color:var(--ash); margin-bottom:8px;
  }
  .form-row input{
    width:100%; background:var(--obsidian); border:1px solid var(--line);
    padding:12px 14px; color:var(--parchment); font-size:.92rem; outline:none;
    font-family:inherit; transition:border-color .2s;
    border-radius:0; -webkit-appearance:none; appearance:none;
  }
  .form-row input:focus{border-color:var(--gold);}
  .form-row input:-webkit-autofill,
  .form-row input:-webkit-autofill:hover,
  .form-row input:-webkit-autofill:focus{
    -webkit-text-fill-color:var(--parchment) !important;
    caret-color:var(--parchment);
    box-shadow:0 0 0 1000px var(--obsidian) inset !important;
    transition:background-color 99999s ease-out 0s;
    border:1px solid var(--line);
  }
  .hint{font-size:.72rem; color:var(--ash); margin-top:6px;}

  .rules-accept{display:flex;align-items:flex-start;gap:10px;margin:4px 0 16px;font-size:.85rem;color:var(--ash);line-height:1.45;}
  .rules-accept input{width:auto;margin-top:3px;flex-shrink:0;}
  .rules-accept a{color:var(--gold-light);text-decoration:underline;}

  .btn{
    width:100%; justify-content:center; margin-top:8px;
    padding:14px 22px; font-size:.85rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; display:inline-flex; align-items:center; gap:10px; border:none;
    cursor:pointer; font-family:inherit;
    clip-path:polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px);
    background:linear-gradient(135deg, var(--blood-light), var(--blood)); color:var(--parchment);
    transition:transform .2s, box-shadow .2s;
  }
  .btn:hover{transform:translateY(-2px); box-shadow:0 12px 28px rgba(143,28,41,.35);}

  .alerts{margin-bottom:18px;}
  .alert{
    padding:12px 14px; font-size:.85rem; line-height:1.5; margin-bottom:10px;
    border:1px solid var(--line);
  }
  .alert.error{background:rgba(143,28,41,.18); border-color:rgba(197,51,71,.35); color:var(--parchment);}
  .alert.success{background:rgba(51,89,74,.2); border-color:rgba(79,138,113,.35); color:var(--jade-light);}
  .alert ul{margin:6px 0 0 18px;}

  .foot{
    margin-top:18px; text-align:center; font-size:.82rem; color:var(--ash);
    display:flex; justify-content:center; gap:18px; flex-wrap:wrap;
  }
  .foot a{color:var(--gold-light);}
  .foot a:hover{color:var(--parchment);}
</style>
</head>
<body>
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e($appName) ?> Anasayfa">
      <img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
    </a>

    <div class="card">
      <div class="eyebrow">登錄 · Yeni Hesap</div>
      <h1>Kayıt Ol</h1>
      <p class="sub">Kullanıcı adı, parola ve e-posta ile hesabını oluştur.</p>

      <?php if ($errors !== [] || $success): ?>
      <div class="alerts">
        <?php if ($success): ?>
          <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
          <div class="alert error">
            Kayıt tamamlanamadı:
            <ul>
              <?php foreach ($errors as $err): ?>
                <li><?= e((string) $err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('/kayit')) ?>" autocomplete="off">
        <?= $csrf ?>

        <div class="form-row">
          <label for="login">Kullanıcı Adı</label>
          <input id="login" name="login" type="text" maxlength="16" required
                 autocomplete="username"
                 value="<?= e((string) ($old['login'] ?? '')) ?>">
          <div class="hint">4–16 karakter (harf, rakam, _)</div>
        </div>

        <div class="form-row">
          <label for="password">Parola</label>
          <input id="password" name="password" type="password" maxlength="16" required
                 autocomplete="new-password">
          <div class="hint">4–16 karakter</div>
        </div>

        <div class="form-row">
          <label for="password_confirm">Parola Tekrar</label>
          <input id="password_confirm" name="password_confirm" type="password" maxlength="16" required
                 autocomplete="new-password">
          <div class="hint">Parolanı doğrula</div>
        </div>

        <div class="form-row">
          <label for="email">E-posta</label>
          <input id="email" name="email" type="email" maxlength="64" required
                 autocomplete="email"
                 value="<?= e((string) ($old['email'] ?? '')) ?>">
        </div>

        <div class="form-row">
          <label for="securitycode">Güvenlik Kodu</label>
          <input id="securitycode" name="securitycode" type="text" inputmode="numeric"
                 pattern="\d{1,6}" maxlength="6" required
                 autocomplete="off" placeholder="örn. 123456">
          <div class="hint">1–6 haneli sayı (oyun / panel güvenliği)</div>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-user-plus"></i> Hesap Oluştur</button>
      </form>

      <div class="foot">
        <a href="<?= e(url('/')) ?>"><i class="fa-solid fa-arrow-left"></i> Anasayfa</a>
        <a href="<?= e(url('/panel')) ?>"><i class="fa-solid fa-user"></i> Panel</a>
      </div>
    </div>
  </div>
</body>
</html>
