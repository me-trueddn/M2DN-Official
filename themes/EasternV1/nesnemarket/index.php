<?php
/** @var string $appName */
/** @var string $themeUrl */
/** @var string $marketAssetUrl */
/** @var string $marketMode embed|ingame|web */
/** @var array $authUser */
/** @var array $account */
/** @var list<array> $marketItems */
/** @var list<array> $marketCategories */
/** @var array $siteBrand */
/** @var string $appVersion */

$marketMode = in_array(($marketMode ?? 'web'), ['embed', 'ingame', 'web'], true)
    ? $marketMode
    : 'web';
$logo = (string) (($siteBrand['market_logo_url'] ?? '') ?: (($siteBrand['logo_url'] ?? '') ?: ($themeUrl . '/img/logo-nav.svg')));
$logoSize = (int) ($siteBrand['market_size'] ?? 22);
if ($logoSize < 12) {
    $logoSize = 12;
}
if ($logoSize > 80) {
    $logoSize = 80;
}
$cash = (int) ($account['cash'] ?? 0);
$cashLabel = number_format($cash, 0, ',', '.');
$accountLogin = (string) ($account['login'] ?? $authUser['login'] ?? 'Hesap');
$bodyClass = $marketMode === 'web' ? '' : 'mode-' . $marketMode;
$categories = is_array($marketCategories ?? null) ? $marketCategories : [];
$assetVer = rawurlencode((string) ($appVersion ?? '1'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nesne Market — <?= e($appName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;900&family=Ma+Shan+Zheng&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e($marketAssetUrl) ?>/market.css?v=<?= e($assetVer) ?>">
</head>
<body class="<?= e($bodyClass) ?>">

<div class="market-window">
  <div class="corner tl"></div><div class="corner tr"></div><div class="corner bl"></div><div class="corner br"></div>

  <div class="title-bar">
    <div class="title-left">
      <img src="<?= e($logo) ?>" alt="<?= e($appName) ?>" style="height:<?= $logoSize ?>px;width:auto;">
      <span>Nesne Market</span>
      <span class="brush">寶物</span>
    </div>
    <div class="win-controls">
      <button type="button" title="Kapat" class="close" data-market-close><i class="fa-solid fa-xmark"></i></button>
    </div>
  </div>

  <div class="sub-bar">
    <div class="char-tag" title="Hesap"><i class="fa-solid fa-user"></i> <b><?= e($accountLogin) ?></b></div>
    <div class="search-mini"><i class="fa-solid fa-magnifying-glass"></i><input placeholder="Eşya veya kod ara..." autocomplete="off"></div>
    <div class="balance-pill" title="account.cash"><i class="fa-solid fa-gem"></i> <span id="cashPill"><?= e($cashLabel) ?></span> Elmas</div>
  </div>

  <div class="body-layout">
    <div class="cat-rail" id="catRail">
      <a href="#" class="active" data-cat="all"><i class="fa-solid fa-border-all"></i><span>Tümü</span></a>
      <?php foreach ($categories as $cat): ?>
      <a href="#" data-cat="<?= e((string) $cat['slug']) ?>"><i class="<?= e((string) $cat['icon']) ?>"></i><span><?= e((string) $cat['name']) ?></span></a>
      <?php endforeach; ?>
    </div>

    <aside class="preview-panel" id="previewPanel" aria-label="Ürün detayı">
      <div class="preview-empty" id="previewEmpty">
        <i class="fa-solid fa-hand-pointer"></i>
        Detayları görmek için<br>bir eşyaya tıkla.
      </div>
      <div class="preview-content" id="previewContent">
        <div class="preview-thumb" id="pThumbWrap"><i class="fa-solid fa-box" id="pThumb"></i></div>
        <h3 id="pName">—</h3>
        <div class="preview-code" id="pCodeWrap">Kod: <b id="pCode">—</b></div>
        <div class="preview-cat" id="pCat">—</div>
        <p class="preview-desc" id="pDesc">—</p>
        <div class="preview-stats">
          <div class="stat"><span>Süre</span><b id="pDuration">—</b></div>
        </div>
        <div class="preview-price" id="pPrice">—</div>
        <button type="button" class="btn btn-primary" id="pBuyBtn"><i class="fa-solid fa-gem"></i> Satın Al</button>
        <button type="button" class="btn btn-ghost">Hediye Et</button>
      </div>
    </aside>

    <div class="item-area">
      <div class="item-grid" id="itemGrid"></div>
    </div>
  </div>

  <div class="bottom-bar">
    <div class="bottom-left">Bakiyen: <b id="cashLabel"><?= e($cashLabel) ?> Elmas</b> · Seçili: <b id="selName">Eşya seçilmedi</b></div>
    <div class="bottom-actions">
      <button type="button" class="btn btn-close-window" data-market-close>Kapat</button>
      <button type="button" class="btn btn-primary" id="bottomBuyBtn" disabled><i class="fa-solid fa-gem"></i> Satın Al</button>
    </div>
  </div>
</div>

<div class="buy-overlay" id="buyOverlay" aria-hidden="true">
  <div class="buy-dialog" role="dialog" aria-labelledby="buyDialogTitle">
    <h3 id="buyDialogTitle">Satın almayı onayla</h3>
    <p class="buy-warn"><i class="fa-solid fa-triangle-exclamation"></i> Satın almadan önce <b>deponun (safebox) kapalı</b> olduğundan emin olun. Açıkken alınan eşya görünmeyebilir veya çakışabilir.</p>
    <div class="buy-meta" id="buyMeta"></div>
    <div class="buy-actions">
      <button type="button" class="btn btn-ghost" id="buyCancelBtn">Vazgeç</button>
      <button type="button" class="btn btn-primary" id="buyConfirmBtn"><i class="fa-solid fa-gem"></i> Onayla</button>
    </div>
    <div class="buy-error" id="buyError" hidden></div>
  </div>
</div>

<script>
  window.M2DN_MARKET_ITEMS = <?= json_encode($marketItems ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
  window.M2DN_MARKET_CATEGORIES = <?= json_encode(array_values(array_map(static function (array $c): array {
      return [
          'slug' => (string) $c['slug'],
          'name' => (string) $c['name'],
          'icon' => (string) $c['icon'],
      ];
  }, $categories)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
  window.M2DN_MARKET_BUY = <?= json_encode([
      'url' => (string) ($marketBuyUrl ?? url('/nesne-market/satin-al')),
      'csrfName' => (string) ($csrfTokenName ?? 'csrf_token'),
      'csrf' => (string) ($csrfToken ?? ''),
      'cash' => (int) ($account['cash'] ?? 0),
  ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
</script>
<script src="<?= e($marketAssetUrl) ?>/market.js?v=<?= e($assetVer) ?>"></script>
</body>
</html>
