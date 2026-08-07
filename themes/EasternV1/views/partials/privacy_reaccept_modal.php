<?php
/**
 * Zorunlu gizlilik / KVKK yeniden kabul modalı.
 * @var string $csrf
 * @var string $appName
 * @var string $forcePrivacyTitle
 * @var string $forcePrivacyExcerpt
 */
$csrf = $csrf ?? '';
$appName = $appName ?? 'M2DN';
$forcePrivacyTitle = (string) ($forcePrivacyTitle ?? 'Gizlilik Sözleşmesi ve KVKK');
$forcePrivacyExcerpt = (string) ($forcePrivacyExcerpt ?? '');
?>
<style>
  #privacyReacceptOverlay{
    position:fixed; inset:0; z-index:12000;
    display:flex; align-items:center; justify-content:center;
    padding:20px;
    background:rgba(8,6,4,.82);
    backdrop-filter:blur(4px);
  }
  #privacyReacceptCard{
    width:min(560px,100%);
    max-height:min(88vh,720px);
    overflow:auto;
    background:#161009;
    border:1px solid rgba(201,151,74,.28);
    padding:28px 26px 24px;
    color:#e9dfc6;
    box-shadow:0 24px 60px rgba(0,0,0,.55);
    font-family:'Inter',system-ui,sans-serif;
  }
  #privacyReacceptCard .eyebrow{
    font-size:.68rem; letter-spacing:.14em; text-transform:uppercase;
    color:#c9974a; margin-bottom:8px;
  }
  #privacyReacceptCard h2{
    font-family:'Cinzel',serif; font-size:1.35rem; color:#eccd8e;
    margin:0 0 10px; font-weight:700;
  }
  #privacyReacceptCard .sub{
    color:#9a8f80; font-size:.88rem; line-height:1.55; margin:0 0 16px;
  }
  #privacyReacceptCard .privacy-scroll{
    max-height:280px; overflow:auto;
    border:1px solid rgba(201,151,74,.15);
    background:#0b0906;
    padding:12px 14px;
    margin-bottom:16px;
    font-size:.82rem; line-height:1.55; color:#e9dfc6; white-space:pre-wrap;
  }
  #privacyReacceptCard .privacy-scroll a{color:#eccd8e;}
  #privacyReacceptCard .actions{
    display:flex; gap:10px; flex-wrap:wrap; margin-top:4px;
  }
  #privacyReacceptCard .btn-pr{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 18px; font-size:.78rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.06em; border:1px solid rgba(201,151,74,.25);
    cursor:pointer; font-family:inherit; text-decoration:none; color:inherit;
  }
  #privacyReacceptCard .btn-pr-accept{
    background:rgba(201,151,74,.16); color:#eccd8e; border-color:rgba(201,151,74,.45); flex:1;
  }
  #privacyReacceptCard .btn-pr-accept:hover{background:rgba(201,151,74,.28);}
  #privacyReacceptCard .btn-pr-decline{
    background:transparent; color:#c53347; border-color:rgba(143,28,41,.45);
  }
  #privacyReacceptCard .btn-pr-decline:hover{background:rgba(143,28,41,.15);}
  body.privacy-reaccept-open{overflow:hidden !important;}
</style>
<div id="privacyReacceptOverlay" role="dialog" aria-modal="true" aria-labelledby="privacyReacceptTitle">
  <div id="privacyReacceptCard">
    <div class="eyebrow">Gizlilik / KVKK</div>
    <h2 id="privacyReacceptTitle">Gizlilik sözleşmesi güncellendi</h2>
    <p class="sub">
      <?= e($appName) ?> gizlilik sözleşmesi değişti. Siteyi kullanmaya devam etmek için güncel metni okuyup kabul etmelisin.
      Kabul etmezsen oturumun kapatılır.
    </p>
    <div class="privacy-scroll">
      <strong style="display:block;margin-bottom:8px;color:#eccd8e;"><?= e($forcePrivacyTitle) ?></strong>
      <?php if ($forcePrivacyExcerpt === ''): ?>
        <p style="margin:0;color:#9a8f80;">Özet bulunamadı. Tam metin için gizlilik sayfasını aç.</p>
      <?php else: ?>
        <?= e($forcePrivacyExcerpt) ?>
      <?php endif; ?>
      <p style="margin:10px 0 0;font-size:.8rem;">
        <a href="<?= e(url('/gizlilik')) ?>" target="_blank" rel="noopener">Tam gizlilik sayfasını yeni sekmede aç</a>
      </p>
    </div>
    <div class="actions">
      <form method="post" action="<?= e(url('/gizlilik/kabul')) ?>" style="flex:1;display:flex;">
        <?= $csrf ?>
        <button type="submit" class="btn-pr btn-pr-accept" style="width:100%;"><i class="fa-solid fa-check"></i> Kabul ediyorum</button>
      </form>
      <form method="post" action="<?= e(url('/gizlilik/reddet')) ?>">
        <?= $csrf ?>
        <button type="submit" class="btn-pr btn-pr-decline"><i class="fa-solid fa-right-from-bracket"></i> Reddet / Çıkış</button>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  document.body.classList.add('privacy-reaccept-open');
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);
})();
</script>
