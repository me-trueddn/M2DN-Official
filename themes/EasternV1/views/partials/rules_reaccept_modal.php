<?php
/**
 * Zorunlu topluluk kuralları yeniden kabul modalı.
 * @var string $csrf
 * @var string $appName
 * @var list<array> $forceRulesList
 */
$csrf = $csrf ?? '';
$appName = $appName ?? 'M2DN';
$forceRulesList = is_array($forceRulesList ?? null) ? $forceRulesList : [];
?>
<style>
  #rulesReacceptOverlay{
    position:fixed; inset:0; z-index:12000;
    display:flex; align-items:center; justify-content:center;
    padding:20px;
    background:rgba(8,6,4,.82);
    backdrop-filter:blur(4px);
  }
  #rulesReacceptCard{
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
  #rulesReacceptCard .eyebrow{
    font-size:.68rem; letter-spacing:.14em; text-transform:uppercase;
    color:#c9974a; margin-bottom:8px;
  }
  #rulesReacceptCard h2{
    font-family:'Cinzel',serif; font-size:1.35rem; color:#eccd8e;
    margin:0 0 10px; font-weight:700;
  }
  #rulesReacceptCard .sub{
    color:#9a8f80; font-size:.88rem; line-height:1.55; margin:0 0 16px;
  }
  #rulesReacceptCard .rules-scroll{
    max-height:280px; overflow:auto;
    border:1px solid rgba(201,151,74,.15);
    background:#0b0906;
    padding:12px 14px;
    margin-bottom:16px;
  }
  #rulesReacceptCard .rules-scroll ol{
    margin:0; padding-left:1.2rem; font-size:.82rem; line-height:1.5; color:#e9dfc6;
  }
  #rulesReacceptCard .rules-scroll li{margin:0 0 8px;}
  #rulesReacceptCard .rules-scroll a{color:#eccd8e;}
  #rulesReacceptCard .actions{
    display:flex; gap:10px; flex-wrap:wrap; margin-top:4px;
  }
  #rulesReacceptCard .btn-rr{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 18px; font-size:.78rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.06em; border:1px solid rgba(201,151,74,.25);
    cursor:pointer; font-family:inherit; text-decoration:none; color:inherit;
  }
  #rulesReacceptCard .btn-rr-accept{
    background:rgba(201,151,74,.16); color:#eccd8e; border-color:rgba(201,151,74,.45); flex:1;
  }
  #rulesReacceptCard .btn-rr-accept:hover{background:rgba(201,151,74,.28);}
  #rulesReacceptCard .btn-rr-decline{
    background:transparent; color:#c53347; border-color:rgba(143,28,41,.45);
  }
  #rulesReacceptCard .btn-rr-decline:hover{background:rgba(143,28,41,.15);}
  body.rules-reaccept-open{overflow:hidden !important;}
</style>
<div id="rulesReacceptOverlay" role="dialog" aria-modal="true" aria-labelledby="rulesReacceptTitle">
  <div id="rulesReacceptCard">
    <div class="eyebrow">Topluluk Kuralları</div>
    <h2 id="rulesReacceptTitle">Kurallar güncellendi</h2>
    <p class="sub">
      <?= e($appName) ?> topluluk kuralları değişti. Siteyi kullanmaya devam etmek için güncel kuralları okuyup kabul etmelisin.
      Kabul etmezsen oturumun kapatılır.
    </p>
    <div class="rules-scroll">
      <?php if ($forceRulesList === []): ?>
        <p style="margin:0;color:#9a8f80;font-size:.85rem;">Aktif kural bulunamadı. Tam metin için kurallar sayfasını aç.</p>
      <?php else: ?>
        <ol>
          <?php foreach ($forceRulesList as $rule): ?>
            <li>
              <strong><?= e((string) ($rule['title'] ?? '')) ?></strong>
              <?php if (!empty($rule['detail'])): ?>
                <div style="color:#9a8f80;margin-top:4px;white-space:pre-wrap;"><?= e(mb_strlen((string) $rule['detail']) > 180 ? mb_substr((string) $rule['detail'], 0, 180) . '…' : (string) $rule['detail']) ?></div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
      <p style="margin:10px 0 0;font-size:.8rem;">
        <a href="<?= e(url('/kurallar')) ?>" target="_blank" rel="noopener">Tam kurallar sayfasını yeni sekmede aç</a>
      </p>
    </div>
    <div class="actions">
      <form method="post" action="<?= e(url('/kurallar/kabul')) ?>" style="flex:1;display:flex;">
        <?= $csrf ?>
        <button type="submit" class="btn-rr btn-rr-accept" style="width:100%;"><i class="fa-solid fa-check"></i> Kabul ediyorum</button>
      </form>
      <form method="post" action="<?= e(url('/kurallar/reddet')) ?>">
        <?= $csrf ?>
        <button type="submit" class="btn-rr btn-rr-decline"><i class="fa-solid fa-right-from-bracket"></i> Reddet / Çıkış</button>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  document.body.classList.add('rules-reaccept-open');
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);
})();
</script>
