# Güncelleme notları

Sürüm geçmişi. Yeni notlar en üste eklenir.

Sürüm dosyası: `config/version.json`

### 2026-08-08 — Sürüm 3.6.17 · Wiki çoklu alt kategori

| Konu | Açıklama |
|------|----------|
| **Ağaç** | Admin listesi ana → alt sırası; bir ana altında sınırsız alt |
| **Slug** | Ana kategoriler `main-{id}` (URL çakışması yok) |
| **İçerik** | Alt kategori seçimi ana gruba göre `optgroup` |
| **SQL** | `07_wiki_main_slug_normalize.sql` |

### 2026-08-08 — Sürüm 3.6.16 · Wiki sınıf kartı

| Konu | Açıklama |
|------|----------|
| **Editör** | **Kart ×2** — yan yana 2 sınıf kartı (resim + başlık + açıklama) |
| **Çerçeve** | Resim alanında altın temalı çerçeve |
| **Sanitize** | Wiki HTML’de `class` / kart yapısı korunur |

### 2026-08-08 — Sürüm 3.6.15 · Wiki başlangıç + Intelephense

| Konu | Açıklama |
|------|----------|
| **Başlangıç** | Alt kategoride radio / form ile `/wiki` ana sayfa seçimi |
| **Redirect** | Seçiliyse `/wiki` → `/wiki/{slug}.html` |
| **Fix** | `wiki.php` — `$wikiCategory` / `$wikiPage` `@var` (Intelephense P1008) |

### 2026-08-08 — Sürüm 3.6.14 · Wiki sayfa URL’leri

| Konu | Açıklama |
|------|----------|
| **URL** | Alt kategori sayfaları: `/wiki/{slug}.html` (yalnızca o içerik) |
| **Index** | `/wiki` — kategori kartları + TOC; tıklanınca ayrı sayfa |
| **Slug** | `wiki_categories.slug` + admin form alanı |

### 2026-08-08 — Sürüm 3.6.13 · Wiki içerik tipleri + Basit metin

| Konu | Açıklama |
|------|----------|
| **İçerik tipleri** | Admin Wiki → İçerik Tipleri (seed: Basit metin) |
| **İçerikler** | Alt kategoriye bağlı sayfa; zengin HTML editör + resim yükleme (`/uploads/wiki/`) |
| **Public `/wiki`** | Ana kategoriler her zaman görünür; alt sayfa içeriği render |
| **SQL** | `04_wiki_content.sql` |

### 2026-08-08 — Sürüm 3.6.12 · Wiki kategoriler + panel cursor

| Konu | Açıklama |
|------|----------|
| **Wiki** | Admin **Wiki → Kategoriler** (ana / alt); eski içerik editörü kaldırıldı |
| **Public `/wiki`** | TOC ve içerik `wiki_categories` tablosundan; ana kategori TOC’ta tekrarlanmaz |
| **SQL** | `02_drop_wiki_content.sql`, `03_wiki_categories.sql` |
| **Panel** | Admin / kullanıcı menü `.nav-item` üzerinde `cursor:pointer` (yazı seçme imleci düzeltildi) |

### 2026-08-07 — Sürüm 3.6.11 · Ban / ticket mail düzeltmesi

| Konu | Açıklama |
|------|----------|
| **Ban / unban** | Mail hatası ban işlemini etkilemez; başarısızlık Gönderim loguna yazılır + admin uyarısı |
| **Neden görünür** | Şablon kapalı / e-posta yok / SMTP hatası `mail_logs`’a düşer |
| **Ticket** | Oyuncu yanıtında yetkililere de `ticket_replied` maili |

### 2026-08-07 — Sürüm 3.6.10 · Şifre sıfırlama logo boyutu

| Konu | Açıklama |
|------|----------|
| **Logo menü** | **Şifre sıfırlama logo (yükseklik)** (24–160 px, varsayılan 48) |
| **Sayfa** | `/sifre-sifirla` logosu bu boyuta göre render edilir |

### 2026-08-07 — Sürüm 3.6.9 · Mail logo boyutu

| Konu | Açıklama |
|------|----------|
| **Logo menü** | **Mail bildirimi logo (genişlik)** alanı (40–320 px, varsayılan 160) |
| **Şablon** | `{{logo_width}}` — bildirim kartında logo genişliği |

### 2026-08-07 — Sürüm 3.6.8 · Mail logo PNG

| Konu | Açıklama |
|------|----------|
| **Logo** | `themes/EasternV1/assets/img/logo-mail.png` — SVG’den üretildi; `{{logo}}` artık PNG |
| **Bildirimler** | Şablonlardaki `.svg` logo `src` → `{{logo}}` (PNG); SVG’ye düşülmez |

### 2026-08-07 — Sürüm 3.6.7 · Mail test modal

| Konu | Açıklama |
|------|----------|
| **Test** | Test gönder → modal; alıcı e-posta girilir, Tamam ile gönderim başlar |

### 2026-08-07 — Sürüm 3.6.6 · Mail sunucu test + UI

| Konu | Açıklama |
|------|----------|
| **Test** | Kayıtlı sunucu satırında ve formda **Test gönder**; seçilen sunucu ile deneme maili |
| **UI** | Kayıtlı sunucularda taşan Düzenle/Aktif/Sil → ikon butonlar |

### 2026-08-07 — Sürüm 3.6.5 · Yönetici log hedef düzeltmesi

| Konu | Açıklama |
|------|----------|
| **Hedef** | Yetki grubu atamada Hedef sütunu grup adı (`Game` vb.) değil, işlem yapılan hesap login’i |
| **Detay** | Atanan grup adları ayrı alanda (`Atanan gruplar: …`) |

### 2026-08-07 — Sürüm 3.6.4 · Yönetici log hedef hesabı

| Konu | Açıklama |
|------|----------|
| **Hedef** | Yönetici Logları’nda hedef hesap adı boşsa `account_id` ile login çözülür (yeni kayıt + liste) |
| **Yetki atama** | Yetki grubu atama loguna hedef login yazılır |

### 2026-08-07 — Sürüm 3.6.3 · Çoklu yetki grubu

Bir hesaba birden fazla yetki grubu atanabilir; menü / bayrak erişimi grupların **birleşimi (OR)** ile hesaplanır.

| Konu | Açıklama |
|------|----------|
| **Çoklu atama** | Admin → Oyuncular → Yetki Ata: checkbox ile birden fazla `WebPermission = 1` grubu |
| **Birleşim** | Örn. bir grup yalnızca oyuncular menüsü, diğeri ticket → hesap ikisine de erişir |
| **Süper Admin** | `WebPermission = 2` → yalnızca **Süper Admin** tek rol; başka grupla karışmaz |
| **Default User** | `WebPermission = 0` → yalnızca kullanıcı rolü; admin gruplarıyla karışmaz |
| **Ready Only** | Ready Only + yazma bayraklı grup birlikteyse hesap salt okunur sayılmaz |
| **DB** | `account_staff_groups` PK: `(account_id, group_id)` |
| **SQL** | `database/2026-08-07-coklu-yetki-grubu/` (`01_multi_staff_groups.sql`) |

### 2026-08-07 — Sürüm 3.6.1 · Wiki Yönetimi + oturum düzeltmesi

| Konu | Açıklama |
|------|----------|
| **Admin** | Ana menü **Wiki → Wiki Yönetimi** — public `/wiki` içeriğini düzenleme |
| **Yetki** | `menu_wiki` (menü) · `wiki_manage` (düzenleme); Ready Only yalnızca menü |
| **SQL** | `database/2026-08-07-wiki-yonetim/` |
| **Oturum** | Çoklu sekmede login kaybı: regenerate grace + no-store cache |

### 2026-08-07 — Sürüm 3.6.0 · Wiki

Herkese açık **Wiki / Bilgi Bankası** sayfası eklendi (`/wiki`).

| Alan | Açıklama |
|------|----------|
| **Menü** | Anasayfa header → Galeri öncesi **Wiki** |
| **Erişim** | Login zorunlu değil; misafir de açabilir |
| **Oturum** | Giriş yapılmışsa oturum geri sayımı, kullanıcı menüsü, kurallar/gizlilik yeniden onay modalları geçerli |
| **İçerik** | Giriş, sınıflar, haritalar, canavarlar, metinler, yükseltme, lonca, SSS; oranlar site ayarlarından |
| **Giriş/Kayıt** | Misafirde `/giris` · `/kayit` (anasayfa modalına yönlendirir) |

### 2026-08-07 — Sürüm 3.5.9

Bugün eklenen / güncellenen özellikler (canlı migrate klasörleri `database/2026-08-07-*`):

#### Yetki & güvenlik

| Konu | Açıklama |
|------|----------|
| **Ready Only** | `WebPermission = 1` için salt görüntüleme yetki grubu; yazma işlemleri kapalı |
| **Admin 2FA kapatma** | Oyuncu detayı → İşlemler → **2FA**; `disable_2fa` bayrağı (Ready Only hariç) |
| **Oyuncu 2FA** | Panel güvenlik akışı iyileştirildi |
| **SQL** | `database/2026-08-07-ready-only-yetki/` · `database/2026-08-07-disable-2fa/` |

#### Oyuncu yönetimi

| Konu | Açıklama |
|------|----------|
| **Yöneticileri göster** | Oyuncu Yönetimi filtresi: checkbox → yalnızca `WebPermission ≥ 1`; sıralama yetkiye göre |
| **Hesap kayıtları** | Oyuncu detay modalında en fazla **5** panel aktivite satırı |

#### Nesne Market — kuponlar

| Alan | Açıklama |
|------|----------|
| **Admin** | Nesne Market → **Market Kuponları** — kategori, toplu üret, çoklu sil, kullanılmış/kullanılmamış, hesap detay linki |
| **Oyuncu** | Panel → **Market Kupon Aktif Et** — kategori `cash_amount` kadar Elmas ekler |
| **Güvenlik** | Kod düz metin saklanmaz; `code_hash` (SHA-256) + `code_mask` |
| **Satış logları** | `entry_type` (`purchase` / `coupon`); tam kod ile arama (`coupon_hash`) |
| **Karakter şartı** | Hesapta oyun karakteri yoksa market açılmaz: *Oyun içi karakteriniz bulunmadığından Nesne Market açılamadı.* |
| **SQL** | `database/2026-08-07-market-kuponlar/` (`01_market_coupons.sql` + `02_sales_log_coupon_hash.sql`) |

#### Kayıt, gizlilik & UI

| Konu | Açıklama |
|------|----------|
| **Kayıt** | Şifre tekrarı zorunlu; modal dikey alan düzeni |
| **Gizlilik onayı** | Kayıtta zorunlu kabul; admin gizlilik içeriği değişince girişte yeniden onay (kurallar gibi) |
| **Reddetme** | Gizlilik reddedilirse oturum kapanır (`/gizlilik/kabul`, `/gizlilik/reddet`) |
| **Captcha / checkbox** | `appearance:none` yalnızca metin alanlarında; checkbox görünürlüğü düzeltildi |
| **Modal / sidebar** | Kayıt modalı temalı scrollbar; kurallar/gizlilik metni küçültüldü; admin sidebar scrollbar |
| **SQL** | `database/2026-08-07-gizlilik-onay/` |

#### Altyapı

| Konu | Açıklama |
|------|----------|
| **Sürüm dosyası** | Uygulama sürümü artık `config/version.json` (`Config::version()`). `config.php` repoda tutulmaz (gitignore); örnek: `config.example.php` |

### Nesne Market — yayında

Nesne Market **canlı kullanıma hazır**. Oyuncular Elmas (`account.cash`) ile ürün satın alır; item **depo (SAFEBOX)**’a düşer. Kupon ile Elmas yükleme yukarıdaki **Market Kuponları** bölümündedir.

| Alan | Açıklama |
|------|----------|
| **Oyuncu** | Panel → Nesne Market (`/nesne-market`) — en az bir karakter gerekir; onay diyaloğu |
| **Admin** | Kategoriler · Ürünler · Satış Logları · **Market Kuponları** |
| **Teslimat** | `player.item` · `window='SAFEBOX'` · `owner_id = account.id` |
| **Para birimi** | Arayüzde **Elmas** (`account.cash`) |
| **DB** | `market_categories` · `market_items` · `market_sales_logs` · `market_coupon_categories` · `market_coupons` |
| **SQL** | `migrate_nesne_market.sql` · `2026-08-07-market-kuponlar/` · `dnweb_full_schema.sql` |
| **Config** | `nesne_market.enabled`, `ingame_secret`, `safebox_page_size`, `safebox_default_pages` |
| **Oyun içi** | CEF imzalı URL (`mode=ingame`) ile aynı market |

Satın alma öncesi deponun kapalı olması önerilir (uyarı gösterilir; MySQL’de açık/kapalı durumu tutulmaz).

### Evlilikler

| Alan | Açıklama |
|------|----------|
| **Admin** | Oyuncular → **Evlilikler** — `player.marriage` listesi; **Evliliği bitir** (`WebPermission ≥ 1`) |
| **Oyuncu** | Panel → **Evlilikler** — salt görüntüleme |
| **Detay** | Karakter / hesap detayında evliyse eş adı |
| **SQL referans** | `database/player_marriage_reference.sql` (oyun dump’ında genelde zaten vardır) |

---
