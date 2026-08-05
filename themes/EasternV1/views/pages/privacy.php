<?php
/** @var string $pageTitle */
/** @var string $pageHtml */
/** @var string $appName */
/** @var string $appVersion */
/** @var array $siteBrand */
/** @var array $siteFooter */
/** @var array $siteFooterLinks */
/** @var list<array> $siteSocials */

$appName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'M2DN';
$appVersion = (string) ($appVersion ?? '');
$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Gizlilik Sözleşmesi ve KVKK';
$pageHtml = isset($pageHtml) && is_string($pageHtml) ? $pageHtml : '';
if (!isset($siteBrand) || !is_array($siteBrand)) {
    $siteBrand = \App\Services\SiteContentService::brandingDefaults();
}
$brandLogo = (string) ($siteBrand['logo_url'] ?? asset('img/logo-nav.svg'));
$brandIcon = (string) ($siteBrand['icon_url'] ?? asset('img/logo-mark.svg'));
$brandHomeSize = (int) ($siteBrand['home_size'] ?? 48);
$siteFooter = isset($siteFooter) && is_array($siteFooter) ? $siteFooter : [];
$siteFooterLinks = isset($siteFooterLinks) && is_array($siteFooterLinks) ? $siteFooterLinks : [];

$footerHref = static function (string $url): string {
    $url = trim($url);
    if ($url === '' || $url === '#') {
        return '#';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:')) {
        return $url;
    }
    if (str_starts_with($url, '#')) {
        return url('/') . $url;
    }
    return url($url);
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e($appName) ?></title>
<link rel="icon" href="<?= e($brandIcon) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;900&family=Ma+Shan+Zheng&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php require __DIR__ . '/../partials/public_page_styles.php'; ?>
</head>
<body>
  <header class="site-top">
    <div class="inner">
      <a href="<?= e(url('/')) ?>" aria-label="<?= e($appName) ?>">
        <img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>">
      </a>
      <a class="back" href="<?= e(url('/')) ?>"><i class="fa-solid fa-arrow-left"></i> Anasayfa</a>
    </div>
  </header>

  <div class="page-shell">
    <main class="wrap">
      <div class="hero">
        <div class="eyebrow">Yasal</div>
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($appName) ?> sitesi ve oyunu kapsamında kişisel verilerin işlenmesi, saklanması ve korunmasına ilişkin bilgilendirme metnidir.</p>
      </div>

      <div class="panel">
        <?php if (trim(strip_tags($pageHtml)) === ''): ?>
          <div class="empty">İçerik henüz yayınlanmamış.</div>
        <?php else: ?>
          <div class="legal-body"><?= $pageHtml ?></div>
        <?php endif; ?>
      </div>

      <div class="page-foot">
        <a href="<?= e(url('/kurallar')) ?>">← Topluluk Kuralları</a>
        <a href="<?= e(url('/')) ?>">Ana sayfa</a>
      </div>
    </main>
  </div>

  <footer class="site-foot">
    <div class="foot-inner">
      <div class="foot-col foot-brand">
        <a href="<?= e(url('/')) ?>"><img class="brand-logo" src="<?= e($brandLogo) ?>" alt="<?= e($appName) ?>" style="height:<?= max(24, (int) round($brandHomeSize * 0.7)) ?>px;margin-bottom:12px;"></a>
        <p><?= e((string) ($siteFooter['brand_text'] ?? '')) ?></p>
        <p class="copy"><?= e((string) ($siteFooter['copyright'] ?? '')) ?><?= $appVersion !== '' ? ' · v' . e($appVersion) : '' ?></p>
      </div>
      <div class="foot-col">
        <h4>Sunucu</h4>
        <?php foreach (($siteFooterLinks['server'] ?? []) as $fl): ?>
          <a href="<?= e($footerHref((string) ($fl['url'] ?? '#'))) ?>"><?= e((string) ($fl['label'] ?? '')) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="foot-col">
        <h4>Topluluk</h4>
        <?php foreach (($siteFooterLinks['community'] ?? []) as $fl): ?>
          <a href="<?= e($footerHref((string) ($fl['url'] ?? '#'))) ?>"><?= e((string) ($fl['label'] ?? '')) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </footer>
</body>
</html>
