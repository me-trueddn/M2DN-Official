# M2DN — Metin2 Web Paneli

M2DN, Metin2 özel sunucuları için PHP tabanlı web sitesi ve yönetim panelidir. Oyuncular kayıt / giriş yapar, karakterlerini ve hesabını yönetir; yetkililer ise admin panelinden sunucu, oyuncu, lonca, içerik ve site ayarlarını kontrol eder.

- **Tema:** EasternV1 (çoklu tema altyapısı hazır)
- **Sürüm:** `config/version.json` → `version`
- **Dil:** Türkçe arayüz

---

## Güncelleme notları

Sürüm geçmişi ve değişiklikler: [updates.md](updates.md).

---

## Bu site nedir?

| Katman | Açıklama |
|--------|----------|
| **Anasayfa** | Tanıtım, oranlar, sınıflar, patch indirme, galeri, **Wiki**, kayıt / giriş |
| **Oyuncu paneli** (`/panel`) | Karakter, güvenlik, destek, sıralama, lonca savaşları, **Evlilikler**, **Nesne Market** |
| **Yönetim paneli** (`/admin`) | Oyuncu / lonca / GM / ban / içerik / log / site ayarları / **Nesne Market** |
| **Nesne Market** (`/nesne-market`) | Elmas ile ürün satın alma (panel + oyun içi CEF) |
| **DNWeb** | Site CMS (duyuru, ticket, ayarlar, yetkiler, **market kataloğu / satış logları**) |
| **Oyun DB** | `account` / `common` / `player` / `log` — klasik Metin2 şeması |

Oyun hesap şifreleri **MD5** (`account.password`). Panel oturumu ayrı token ile `DNWeb.web_sessions` üzerinde tutulur.

**Yetki seviyeleri (`account.WebPermission`):**

| Değer | Rol |
|------:|-----|
| `0` | Oyuncu (varsayılan) — tek rol |
| `1` | Admin — birden fazla yetki grubu atanabilir; bayraklar birleşir |
| `2` | Süper admin — yalnızca Süper Admin tek rol |

---

## Neler yapılır? — Menüler

### Anasayfa (herkese açık)

- Sunucu tanıtımı, EXP / DROP / YANG oranları, sınıf kartları
- Patch / istemci indirme linkleri, galeri
- **Wiki** (`/wiki`) — herkese açık bilgi bankası
- **Kayıt** ve **Giriş** (opsiyonel Captcha: Google reCAPTCHA v2 veya Cloudflare Turnstile); kayıtta şifre tekrarı ve gizlilik onayı zorunlu
- Topluluk kuralları (`/kurallar`) ve gizlilik (`/gizlilik`)
- Kurallar veya gizlilik içeriği güncellenince giriş yapan oyuncudan yeniden onay istenir; reddederse oturum kapanır

### Oyuncu paneli — `/panel`

Giriş yapan tüm web grupları (`0` / `1` / `2`) görür.

#### Genel

| Menü | Ne işe yarar? |
|------|----------------|
| **Genel Bakış** | Hesap özeti, karakter özeti, hızlı erişim |
| **Oyuncu Sıralaması** | Level sıralaması: karakter, job, level, stamina, lonca, bayrak; detay modal |
| **Nesne Market** | **Yayında** — Elmas ile alışveriş (en az bir karakter gerekir); ürün depoya düşer (`/nesne-market`) |
| **Duyurular** | Yayınlanmış sunucu duyuruları |
| **Karakterlerim** | Hesaba ait karakter listesi ve detay (evliyse eş adı) |
| **Evlilikler** | Sunucudaki evlilikler (salt okunur) |
| **Hesap Kayıtları** | Panel işlem logları (giriş, şifre, 2FA vb.) |
| **Lonca Savaşları** | Aktif savaşlar, geçmiş, lonca ladder (salt okunur) |
| **Market Kupon** | Genel Bakış → **Market Kupon Aktif Et** — kupon kodu ile Elmas yükleme |

#### Hesap

| Menü | Ne işe yarar? |
|------|----------------|
| **Destek Talepleri** | Ticket açma / yanıtlama |
| **Hesap Güvenliği** | Şifre, depo şifresi, 2FA, IP kilidi, giriş bildirimi |

Admin yetkisi olanlar menüde **Admin Panel** linkini de görür. **Ready Only** grubu menüleri görür; yazma işlemleri kapalıdır.

### Yönetim paneli — `/admin`

Menü görünürlüğü **Yetki Grupları** bayrakları ile kontrol edilir (`WebPermission ≥ 1` gerekir).

#### Genel

| Menü | Ne işe yarar? |
|------|----------------|
| **Genel Bakış** | Online / hesap / ticket özeti, kısa duyurular |

#### Oyuncular

| Menü | Ne işe yarar? |
|------|----------------|
| **Oyuncu Yönetimi** | Hesap arama; durum filtresi; **Yöneticileri göster** (`WebPermission ≥ 1`); ban / unban; detay; e-posta / şifre / depo; **2FA kapat** |
| **Evlilikler** | `player.marriage` listesi; evliliği bitirme |
| **Oyuncu Sıralaması** | Level sıralaması (admin görünümü) |
| **Binek Yönetimi** | `player.horse_name` at adı düzenleme |
| **GM Yönetimi** | `common.gmlist` ekle / düzenle / sil; `mAuthority` combobox |
| **IP Ban** | `player.pcbang_ip` + sebep kaydı `DNWeb.ip_bans` |
| **Loncalar** | Lonca listesi, ad / usta değiştirme, detay |
| **Lonca Savaşı** | Aktif / geçmiş savaşlar, ladder, lonca kartı |
| **Ban / Mute** | Aktif cezalar ve ceza şablonları |

#### İçerik

| Menü | Ne işe yarar? |
|------|----------------|
| **Duyurular** | Duyuru oluşturma / yayınlama |
| **Destek Talepleri** | Oyuncu ticket’larını yönetme |

#### Sunucu işlemleri

| Menü | Ne işe yarar? |
|------|----------------|
| **Sunucu Yönetimi** | Sunucu ile ilgili panel işlemleri |
| **Yasaklı Kelimeler** | `player.banword` ekle / sil |
| **Loglar** | 1) Yönetici logları (`DNWeb`) · 2) Oyun `log` DB tabloları (seçilebilir, son 10) |

#### Nesne Market

| Menü | Ne işe yarar? |
|------|----------------|
| **Kategoriler** | Market kategori CRUD (slug, ikon, sıra, aktif) |
| **Ürünler** | Ürün CRUD: ad, açıklama, Elmas fiyatı, indirim %, görsel URL/yükleme, `item_code`, süre, kategori |
| **Satış Logları** | Satın alma / kupon; Elmas önce/sonra; depo slot; IP; tam kupon kodu ile arama |
| **Market Kuponları** | Kategori + kupon üretimi (hash), kullanılmış/kullanılmamış, çoklu sil, hesap detay |

#### Ayarlar

| Menü | Ne işe yarar? |
|------|----------------|
| **Patch Linkleri** | İndirme / patch URL’leri |
| **Özellikler** | Anasayfa özellik kartları |
| **Sınıflar** | Sınıf tanıtım içerikleri |
| **Sunucu Oranları** | EXP / DROP / YANG gösterimi |
| **Sıradaki Bölüm** | Geri sayım / bölüm başlığı |
| **Galeri** | Anasayfa galeri görselleri |
| **Logo** | Site / market logo / ikon |
| **Captcha** | Google / Turnstile aç-kapa ve key’ler |
| **Mail** | SMTP, bildirim şablonları, test, gönderim logu |
| **Footer / Border** | Alt menü, sosyal linkler, metinler |
| **Ceza Ayarları** | Ban şablonları (gün / sebep) |
| **Topluluk Kuralları** | Kural maddeleri; kayıt güncellenince oyuncudan yeniden onay istenir |
| **Gizlilik / KVKK** | Gizlilik sayfası; içerik kaydı revizyon artırır → girişte yeniden onay |
| **Yetki Grupları** | Menü ve işlem bayrakları; hesaba çoklu grup atama (OR birleşim; Süper Admin tek rol) |
| **Ticket Ayarları** | Kategori, durum, dosya tipi |
| **Duyuru Türleri** | Duyuru kategorileri |

Oyuncu detayında **bayrak değişimi** (`player.change_empire`) ve **eş** bilgisi görüntülenir.

---

## Kurulum

### Gereksinimler

- PHP **8.1+** (PDO MySQL, mbstring, openssl, curl önerilir)
- MySQL / MariaDB
- Oyun sunucusunun klasik Metin2 DB şeması (`account` / `common` / `player` / `log` tabloları dolu veya en azından temel tablolar mevcut)

### 1) Dosyalar

Projeyi sunucuya kopyalayın. Document root **`public/`** olmalıdır.

Yerel test:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

### 2) Veritabanı — oluşturmanız gerekenler

Önerilen sıra: oyun dump’ı → DNWeb tam şema → `WebPermission` → config.

#### A) Oyun veritabanları (zorunlu)

Metin2 sunucunuzdan gelen (veya sıfırdan kurduğunuz) dört veritabanı:

| DB | Rol |
|----|-----|
| `account` | Hesaplar (`account` tablosu + `WebPermission` + `cash`) |
| `common` | GM listesi (`gmlist`) vb. |
| `player` | Karakter, lonca, banword, pcbang_ip, change_empire, **marriage**, **item** (SAFEBOX)… |
| `log` | loginlog, command_log, levellog… |

İsimler `config.php` → `servers.*.databases` ile değiştirilebilir; varsayılanlar yukarıdakilerdir.

1. İsterseniz boş DB iskeleti oluşturun (oyun tabloları **yok**):

```bash
mysql -u root -p < database/setup_databases.sql
```

2. Oyun tablolarını kendi Metin2 SQL dump’ınızla yükleyin (`account`, `common`, `player`, `log`).

`setup_databases.sql` yalnızca boş `account` / `common` / `player` / `log` / `DNWeb` veritabanlarını (ve eski bir DNWeb iskeletini) oluşturur. **Oyun tablolarını oluşturmaz.**

#### B) DNWeb (zorunlu — site CMS)

`DNWeb` site ayarları, duyuru, ticket, oturum, yetki, captcha, mail, IP ban sebebi ve **Nesne Market** içindir.

Tam şemayı (tüm tablolar + kolonlar + market) SQL ile kurun:

```bash
mysql -u root -p < database/dnweb_full_schema.sql
```

Bu dosya `Schema::ensure()` ile aynı nihai DNWeb yapısını oluşturur. Varsayılan içerik (özellik kartları, mail şablonları, ceza şablonları, market kategorileri vb.) site ilk açıldığında / SQL seed ile eklenir.

> Alternatif: Sadece boş `DNWeb` bırakıp siteyi açmak da yeterlidir — `Schema::ensure()` eksik tabloları/kolonları otomatik tamamlar. Elle kurulum için `dnweb_full_schema.sql` tercih edilir.

#### C) Hesap yetkisi kolonu (`WebPermission`)

Panel yetkisi için `account.account.WebPermission` gerekir (`0` oyuncu, `1` admin, `2` süper admin). Oyun dump’ı yüklendikten sonra:

```bash
mysql -u root -p < database/account_web_permission.sql
```

Schema da ilk istekte kolonu eklemeye çalışır; SQL ile kurmak daha nettir.

Örnek süper admin:

```sql
UPDATE account.account SET WebPermission = 2 WHERE login = 'SENIN_LOGIN';
```

#### D) Mevcut kurulum — sadece Nesne Market tabloları

DNWeb zaten kuruluysa, yalnızca market tablolarını eklemek için:

```bash
mysql -u root -p < database/migrate_nesne_market.sql
```

Kupon tabloları için ayrıca:

```bash
mysql -u root -p DNWeb < database/2026-08-07-market-kuponlar/01_market_coupons.sql
mysql -u root -p DNWeb < database/2026-08-07-market-kuponlar/02_sales_log_coupon_hash.sql
```

#### E) 2026-08-07 canlı migrate klasörleri

Mevcut DNWeb kurulumunda bugünkü özellikler için (sıra önerisi):

```bash
mysql -u root -p DNWeb < database/2026-08-07-ready-only-yetki/01_ready_only_group.sql
mysql -u root -p DNWeb < database/2026-08-07-disable-2fa/01_disable_2fa_flag.sql
mysql -u root -p DNWeb < database/2026-08-07-market-kuponlar/01_market_coupons.sql
mysql -u root -p DNWeb < database/2026-08-07-market-kuponlar/02_sales_log_coupon_hash.sql
mysql -u root -p DNWeb < database/2026-08-07-gizlilik-onay/01_privacy_consent.sql
mysql -u root -p DNWeb < database/2026-08-07-wiki-yonetim/01_wiki_flags.sql
mysql -u root -p DNWeb < database/2026-08-07-coklu-yetki-grubu/01_multi_staff_groups.sql
```

Her klasörde kısa `README.md` vardır. Alternatif: siteyi bir kez açmak (`Schema::ensure`) birçok eksik tablo/kolonu otomatik tamamlar; elle SQL tercih edilir.

#### F) `database/` SQL dosyaları

| Dosya / klasör | Ne işe yarar? |
|----------------|---------------|
| `setup_databases.sql` | Boş 5 DB (+ eski DNWeb iskeleti) |
| `dnweb_full_schema.sql` | **DNWeb tam CREATE** (market + kupon + consent dahil — önerilen) |
| `migrate_nesne_market.sql` | **Nesne Market** tabloları + varsayılan kategoriler (mevcut DNWeb’e) |
| `2026-08-07-ready-only-yetki/` | Ready Only yetki grubu |
| `2026-08-07-disable-2fa/` | `disable_2fa` bayrağı |
| `2026-08-07-market-kuponlar/` | Market kupon tabloları + satış log `coupon_hash` |
| `2026-08-07-gizlilik-onay/` | Gizlilik onayı kolonları (`account_consents`) |
| `2026-08-07-wiki-yonetim/` | Wiki menü / yönetim bayrakları |
| `2026-08-07-coklu-yetki-grubu/` | Çoklu yetki grubu (`account_staff_groups` composite PK) |
| `player_marriage_reference.sql` | `player.marriage` referans CREATE (oyun dump’ında yoksa) |
| `account_web_permission.sql` | `account.WebPermission` kolonu |
| `migrate_auth.sql` | Eski migrate: WebPermission + `web_sessions` (yeni kurulumda gerekmez) |
| `migrate_account_security.sql` | Eski migrate: `account_security` (yeni kurulumda gerekmez) |
| `convert_charset.sql` | Charset dönüşümü |
| `seed_*.php` / `seed_*.sql` | Test verisi |

### 3) Yapılandırma

`config/config.example.php` dosyasını `config/config.php` olarak kopyalayıp doldurun (`config.php` gitignore’dadır):

1. **`app.url`** — site adresi (ör. `http://127.0.0.1:8080`)
2. **`web_database`** — DNWeb bağlantısı (host, user, password, `DNWeb`)
3. **`servers.main`** — oyun DB host / user / password ve `databases` alias’ları
4. **`security.app_key`** — canlıda uzun rastgele anahtar
5. **`nesne_market.ingame_secret`** — canlıda değiştirin
6. Canlıda: `app.debug = false`, HTTPS / cookie secure ayarları

Uygulama sürümü `config/version.json` içinde tutulur (footer / panelde gösterilir).

### 4) İzinler / klasörler

`storage/` yazılabilir olmalı (session, log, cache, upload — market görselleri `storage` / `public/uploads/market`).

### 5) İlk giriş

1. Oyunda veya seed script ile bir hesap oluşturun  
2. `WebPermission = 1` veya `2` verin  
3. `/` üzerinden giriş → `/panel` veya `/admin`

Opsiyonel test hesapları: `database/seed_test_accounts.php`, `database/seed_guilds.php`

### 6) Nesne Market (yayında)

- Panel: **Nesne Market** → `/nesne-market` (hesapta en az bir karakter gerekir)
- Admin: kategoriler / ürünler / satış logları / **market kuponları**
- Oyuncu: Genel Bakış → **Market Kupon Aktif Et**
- Oyun içi CEF: imzalı URL ile o anki hesap oturumu açılır

```
/nesne-market?mode=ingame&login=LOGIN&aid=ID&pid=PID&char=NAME&ts=UNIX&sig=HMAC
```

`sig = hash_hmac('sha256', "{login}|{aid}|{pid}|{ts}", config nesne_market.ingame_secret)`  
`config/config.php` → `nesne_market.ingame_secret` değerini canlıda değiştirin.

Satın alma doğrulama (örnek):

```sql
SELECT * FROM player.item WHERE owner_id = ? AND window = 'SAFEBOX' ORDER BY id DESC LIMIT 5;
SELECT * FROM DNWeb.market_sales_logs ORDER BY id DESC LIMIT 5;
```

### 7) Captcha (opsiyonel)

Admin → **Captcha**: Google reCAPTCHA v2 veya Cloudflare Turnstile key’lerini girin.  
Domain / hostname listesine `127.0.0.1`, `localhost` ve canlı domain’i ekleyin.

---

## Veritabanı özeti

```
account   → hesap, WebPermission, cash (Elmas)…
common    → gmlist…
player    → player, guild, banword, horse_name, pcbang_ip, change_empire,
            marriage, item (SAFEBOX), safebox…
log       → loginlog, command_log, levellog, hack_log…
DNWeb     → settings, duyuru, ticket, web_sessions, yetkiler, admin log,
            ip_bans, community_rules, account_consents (kurallar + gizlilik),
            mail, captcha,
            market_categories, market_items, market_sales_logs,
            market_coupon_categories, market_coupons…
```

Detaylı tablo referansı: `docs/database-reference.md`

Panel, oyun tablolarını **okur / gerektiğinde yazar**; şema dump’ınız eksikse ilgili menü boş veya hata verir (ör. `gmlist` yoksa GM menüsü çalışmaz).

---

## Proje yapısı

```
config/config.php       → Uygulama, tema, güvenlik, sunucu / DB, nesne_market (gitignore)
config/config.example.php → Örnek config
config/version.json     → Uygulama sürümü (ör. 3.5.9)
app/Core/               → Config, Database, Security, Session, Theme, Router, Schema
app/Controllers/        → HTTP controller’lar (NesneMarket, AdminMarket…)
app/Services/           → İş mantığı (MarketPurchase, Marriage, Coupon…)
themes/EasternV1/       → Anasayfa, oyuncu paneli, admin, nesnemarket/
public/                 → Document root (index.php, router.php)
routes/web.php          → Rotalar
database/               → Kurulum / migrate / seed (+ tarihli canlı migrate klasörleri)
storage/                → Session, log, cache, upload
docs/                   → DB referansı
```

## Çoklu sunucu

`config.php` → `servers` dizisine yeni sunucu ekleyin. Her sunucuda aynı alias isimleri kullanılır (`account`, `common`, `player`, `log`); host bilgisi sunucuya göre farklı olabilir. Panelden sunucu seçici ile geçiş yapılır.

## Yeni tema

1. `themes/YeniTema/` (`theme.json` + `views/`)
2. `config.php` → `theme.active = 'YeniTema'`

## Güvenlik

- CSRF token  
- Session cookie / TTL  
- XSS escape (`e()`)  
- HTTP güvenlik header’ları (CSP; captcha / market görsel domain’leri dahil)  
- PDO prepared statements  
- Tema asset path koruması  
- Market satın almada cash `FOR UPDATE` / optimistic kontrol  

---

## Hızlı kontrol listesi

- [ ] `account` / `common` / `player` / `log` dump yüklü  
- [ ] `database/dnweb_full_schema.sql` çalıştırıldı (veya Schema otomatik tamamladı)  
- [ ] Mevcut kurulumda market yoksa: `database/migrate_nesne_market.sql`  
- [ ] Mevcut kurulumda 2026-08-07 özellikler için: `database/2026-08-07-*` migrate’leri  
- [ ] `database/account_web_permission.sql` çalıştırıldı  
- [ ] `config/config.php` (örnekten kopya) DB, `app.url`, `nesne_market.ingame_secret` dolu  
- [ ] `config/version.json` mevcut  
- [ ] En az bir hesapta `WebPermission = 2`  
- [ ] `php -S … -t public` veya vhost → `public/`  
- [ ] `/` açılıyor, kayıt / giriş / gizlilik onayı çalışıyor  
- [ ] `/admin` menüleri yetki grubunda görünüyor  
- [ ] `/nesne-market` açılıyor; admin ürün / kupon ekleyip satış testi yapıldı  
