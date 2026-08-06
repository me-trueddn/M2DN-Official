(function () {
  'use strict';

  const items = Array.isArray(window.M2DN_MARKET_ITEMS) ? window.M2DN_MARKET_ITEMS : [];
  const buyCfg = window.M2DN_MARKET_BUY && typeof window.M2DN_MARKET_BUY === 'object' ? window.M2DN_MARKET_BUY : {};
  const grid = document.getElementById('itemGrid');
  const searchInput = document.querySelector('.search-mini input');
  let selectedId = null;
  let activeCat = 'all';
  let searchQ = '';
  let buying = false;
  let cashBalance = Number(buyCfg.cash || 0);

  function closeMarket() {
    if (window.parent && window.parent !== window) {
      try {
        window.parent.postMessage({ type: 'm2dn-market-close' }, '*');
      } catch (e) {}
      return;
    }
    try {
      window.close();
    } catch (e) {}
  }

  document.querySelectorAll('[data-market-close]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      closeMarket();
    });
  });

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );
  }

  function formatCash(n) {
    return Number(n || 0).toLocaleString('tr-TR');
  }

  function setCashDisplay(n) {
    cashBalance = Number(n || 0);
    const pill = document.getElementById('cashPill');
    if (pill) pill.textContent = formatCash(cashBalance);
    const label = document.getElementById('cashLabel');
    if (label) label.textContent = formatCash(cashBalance) + ' Elmas';
  }

  function thumbHtml(item) {
    const img = String(item.image || '').trim();
    if (img) {
      return (
        '<img src="' +
        escapeHtml(img) +
        '" alt="' +
        escapeHtml(item.name || '') +
        '" loading="lazy" decoding="async" referrerpolicy="no-referrer">'
      );
    }
    const icon = String(item.icon || 'fa-box').replace(/^fa-solid\s+/, '');
    return '<i class="fa-solid ' + escapeHtml(icon) + '"></i>';
  }

  function setPreviewThumb(item) {
    const wrap = document.getElementById('pThumbWrap') || document.querySelector('.preview-thumb');
    if (!wrap) return;
    wrap.innerHTML = thumbHtml(item);
  }

  function renderGrid() {
    if (!grid) return;
    grid.innerHTML = '';
    const filtered = items.filter((i) => {
      if (activeCat !== 'all' && i.cat !== activeCat) return false;
      if (searchQ === '') return true;
      const name = String(i.name || '').toLowerCase();
      const code = String(i.code || '').toLowerCase();
      return name.includes(searchQ) || code.includes(searchQ);
    });
    if (filtered.length === 0) {
      grid.innerHTML =
        '<div class="item-empty" style="grid-column:1/-1;padding:28px;text-align:center;color:var(--ash);font-size:.9rem;">Bu kategoride ürün yok.</div>';
      return;
    }
    filtered.forEach((item) => {
      const card = document.createElement('div');
      card.className = 'item-card' + (item.id === selectedId ? ' selected' : '');
      card.dataset.id = String(item.id);
      card.innerHTML =
        (item.ribbon
          ? '<div class="ribbon ' + item.ribbon + '">' + (item.ribbon === 'sale' ? 'İndirim' : 'Yeni') + '</div>'
          : '') +
        '<div class="thumb">' +
        thumbHtml(item) +
        '</div>' +
        '<h4>' +
        escapeHtml(item.name) +
        '</h4>' +
        '<div class="price">' +
        formatCash(item.price) +
        ' Elmas</div>';
      card.addEventListener('click', () => selectItem(item.id));
      grid.appendChild(card);
    });
  }

  function selectItem(id) {
    selectedId = id;
    const item = items.find((i) => i.id === id);
    if (!item) return;

    const empty = document.getElementById('previewEmpty');
    const pc = document.getElementById('previewContent');
    if (empty) empty.style.display = 'none';
    if (pc) pc.classList.add('show');

    setPreviewThumb(item);

    const nameEl = document.getElementById('pName');
    if (nameEl) nameEl.textContent = item.name || '—';

    const codeEl = document.getElementById('pCode');
    if (codeEl) codeEl.textContent = item.code ? String(item.code) : '—';

    const catEl = document.getElementById('pCat');
    if (catEl) catEl.textContent = labelForCat(item.cat);

    const descEl = document.getElementById('pDesc');
    if (descEl) descEl.textContent = item.desc || '';

    const durEl = document.getElementById('pDuration');
    if (durEl) {
      durEl.textContent = item.duration === 'timed' ? 'Süreli' : 'Süresiz';
    }

    const priceEl = document.getElementById('pPrice');
    if (priceEl) {
      priceEl.innerHTML =
        formatCash(item.price) +
        ' Elmas' +
        (item.old ? '<span class="old">' + formatCash(item.old) + ' Elmas</span>' : '');
    }

    const sel = document.getElementById('selName');
    if (sel) sel.textContent = item.name;

    const bottomBuy = document.getElementById('bottomBuyBtn');
    if (bottomBuy) bottomBuy.disabled = false;

    const panel = document.getElementById('previewPanel');
    if (panel && typeof panel.scrollIntoView === 'function') {
      try {
        panel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      } catch (e) {}
    }

    renderGrid();
  }

  function labelForCat(c) {
    const cats = Array.isArray(window.M2DN_MARKET_CATEGORIES) ? window.M2DN_MARKET_CATEGORIES : [];
    const found = cats.find((x) => x.slug === c);
    if (found && found.name) return found.name;
    return (
      {
        silah: 'Silah',
        zirh: 'Zırh & Kostüm',
        binek: 'Binek',
        sarf: 'Sarf Malzemesi',
        paket: 'Özel Paket',
      }[c] || c
    );
  }

  document.querySelectorAll('#catRail a').forEach((a) => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('#catRail a').forEach((x) => x.classList.remove('active'));
      a.classList.add('active');
      activeCat = a.dataset.cat || 'all';
      renderGrid();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      searchQ = String(searchInput.value || '').trim().toLowerCase();
      renderGrid();
    });
  }

  const buyOverlay = document.getElementById('buyOverlay');
  const buyMeta = document.getElementById('buyMeta');
  const buyError = document.getElementById('buyError');
  const buyConfirmBtn = document.getElementById('buyConfirmBtn');

  function openBuyDialog() {
    if (!selectedId || buying) return;
    const item = items.find((i) => i.id === selectedId);
    if (!item) return;
    if (buyError) {
      buyError.hidden = true;
      buyError.textContent = '';
    }
    if (buyMeta) {
      buyMeta.innerHTML =
        '<div class="row"><span>Ürün</span><b>' +
        escapeHtml(item.name) +
        '</b></div>' +
        '<div class="row"><span>Item kodu</span><b>' +
        escapeHtml(item.code || '—') +
        '</b></div>' +
        '<div class="row"><span>Fiyat</span><b>' +
        formatCash(item.price) +
        ' Elmas</b></div>' +
        '<div class="row"><span>Mevcut bakiye</span><b>' +
        formatCash(cashBalance) +
        ' Elmas</b></div>';
    }
    buyOverlay?.classList.add('open');
    buyOverlay?.setAttribute('aria-hidden', 'false');
  }

  function closeBuyDialog() {
    if (buying) return;
    buyOverlay?.classList.remove('open');
    buyOverlay?.setAttribute('aria-hidden', 'true');
  }

  async function confirmBuy() {
    if (!selectedId || buying) return;
    const item = items.find((i) => i.id === selectedId);
    if (!item) return;
    const url = String(buyCfg.url || '');
    const csrfName = String(buyCfg.csrfName || 'csrf_token');
    const csrf = String(buyCfg.csrf || '');
    if (!url || !csrf) {
      if (buyError) {
        buyError.hidden = false;
        buyError.textContent = 'Satın alma yapılandırması eksik. Sayfayı yenile.';
      }
      return;
    }

    buying = true;
    if (buyConfirmBtn) {
      buyConfirmBtn.disabled = true;
      buyConfirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> İşleniyor…';
    }
    if (buyError) {
      buyError.hidden = true;
      buyError.textContent = '';
    }

    try {
      const body = new URLSearchParams();
      body.set(csrfName, csrf);
      body.set('item_id', String(item.id));
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-TOKEN': csrf,
        },
        body: body.toString(),
      });
      const data = await res.json().catch(() => null);
      if (!data || !data.ok) {
        const err =
          data && Array.isArray(data.errors) && data.errors.length
            ? data.errors.join(' ')
            : 'Satın alma başarısız.';
        if (buyError) {
          buyError.hidden = false;
          buyError.textContent = err;
        }
        return;
      }
      if (typeof data.cash === 'number') {
        setCashDisplay(data.cash);
      }
      buying = false;
      closeBuyDialog();
      const bottomBuy = document.getElementById('bottomBuyBtn');
      if (bottomBuy) {
        const original = bottomBuy.innerHTML;
        bottomBuy.innerHTML = '<i class="fa-solid fa-check"></i> Depoya eklendi';
        setTimeout(() => {
          bottomBuy.innerHTML = original;
        }, 1600);
      }
    } catch (e) {
      if (buyError) {
        buyError.hidden = false;
        buyError.textContent = 'Bağlantı hatası. Tekrar dene.';
      }
    } finally {
      buying = false;
      if (buyConfirmBtn) {
        buyConfirmBtn.disabled = false;
        buyConfirmBtn.innerHTML = '<i class="fa-solid fa-gem"></i> Onayla';
      }
    }
  }

  document.getElementById('pBuyBtn')?.addEventListener('click', openBuyDialog);
  document.getElementById('bottomBuyBtn')?.addEventListener('click', openBuyDialog);
  document.getElementById('buyCancelBtn')?.addEventListener('click', closeBuyDialog);
  buyConfirmBtn?.addEventListener('click', confirmBuy);
  buyOverlay?.addEventListener('click', (e) => {
    if (e.target === buyOverlay) closeBuyDialog();
  });

  setCashDisplay(cashBalance);
  renderGrid();
})();
