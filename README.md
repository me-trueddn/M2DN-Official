# M2DN — Metin2 Web Paneli

M2DN, Metin2 özel sunucuları için PHP tabanlı web sitesi ve yönetim panelidir. Oyuncular kayıt / giriş yapar, karakterlerini ve hesabını yönetir; yetkililer ise admin panelinden sunucu, oyuncu, lonca, içerik ve site ayarlarını kontrol eder.

- **Tema:** EasternV1 (çoklu tema altyapısı hazır)
- **Sürüm:** `config/config.php` → `app.version`
- **Dil:** Türkçe arayüz

---

## Bu site nedir?

| Katman | Açıklama |
|--------|----------|
| **Anasayfa** | Tanıtım, oranlar, sınıflar, patch indirme, galeri, kayıt / giriş |
| **Oyuncu paneli** (`/panel`) | Karakter, güvenlik, destek, sıralama, lonca savaşları |
| **Yönetim paneli** (`/admin`) | Oyuncu / lonca / GM / ban / içerik / log / site ayarları |
| **DNWeb** | Site CMS verisi (duyuru, ticket, ayarlar, yetkiler…) |
| **Oyun DB** | `account`, `common`, `player`, `log` — klasik Metin2 şeması |

Oyun hesap şifreleri **MD5** (`account.password`). Panel oturumu ayrı token ile `DNWeb.web_sessions` üzerinde tutulur.

**Yetki seviyeleri (`account.WebPermission`):**

| Değer | Rol |
|------:|-----|
| `0` | Oyuncu (varsayılan) |
| `1` | Admin |
| `2` | Süper admin |

---

## Neler yapılır? — Menüler

### Anasayfa (herkese açık)

- Sunucu tanıtımı, EXP / DROP / YANG oranları, sınıf kartları
- Patch / istemci indirme linkleri, galeri
- **Kayıt** ve **Giriş** (opsiyonel Captcha: Google reCAPTCHA v2 veya Cloudflare Turnstile)
- Topluluk kuralları (`/kurallar`) ve gizlilik (`/gizlilik`)
- Kurallar güncellenince giriş yapan oyuncudan yeniden onay istenir; reddederse oturum kapanır

### Oyuncu paneli — `/panel`

Giriş yapan tüm web grupları (`0` / `1` / `2`) görür.

#### Genel

| Menü | Ne işe yarar? |
|------|----------------|
| **Genel Bakış** | Hesap özeti, karakter özeti, hızlı erişim |
| **Oyuncu Sıralaması** | Level sıralaması: karakter, job, level, stamina, lonca, bayrak; detay modal |
| **Duyurular** | Yayınlanmış sunucu duyuruları |
| **Karakterlerim** | Hesaba ait karakter listesi ve detay |
| **Hesap Kayıtları** | Panel işlem logları (giriş, şifre, 2FA vb.) |
| **Lonca Savaşları** | Aktif savaşlar, geçmiş, lonca ladder (salt okunur) |

#### Hesap

| Menü | Ne işe yarar? |
|------|----------------|
| **Destek Talepleri** | Ticket açma / yanıtlama |
| **Hesap Güvenliği** | Şifre, depo şifresi, 2FA, IP kilidi, giriş bildirimi |

Admin yetkisi olanlar menüde **Admin Panel** linkini de görür.

### Yönetim paneli — `/admin`

Menü görünürlüğü **Yetki Grupları** bayrakları ile kontrol edilir (`WebPermission ≥ 1` gerekir).

#### Genel

| Menü | Ne işe yarar? |
|------|----------------|
| **Genel Bakış** | Online / hesap / ticket özeti, kısa duyurular |

#### Oyuncular

| Menü | Ne işe yarar? |
|------|----------------|
| **Oyuncu Yönetimi** | Hesap arama, ban / unban, detay, e-posta / şifre / depo işlemleri |
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

#### Ayarlar

| Menü | Ne işe yarar? |
|------|----------------|
| **Patch Linkleri** | İndirme / patch URL’leri |
| **Özellikler** | Anasayfa özellik kartları |
| **Sınıflar** | Sınıf tanıtım içerikleri |
| **Sunucu Oranları** | EXP / DROP / YANG gösterimi |
| **Sıradaki Bölüm** | Geri sayım / bölüm başlığı |
| **Galeri** | Anasayfa galeri görselleri |
| **Logo** | Site logo / ikon |
| **Captcha** | Google / Turnstile aç-kapa ve key’ler |
| **Mail** | SMTP, bildirim şablonları, test, gönderim logu |
| **Footer / Border** | Alt menü, sosyal linkler, metinler |
| **Ceza Ayarları** | Ban şablonları (gün / sebep) |
| **Topluluk Kuralları** | Kural maddeleri; kayıt güncellenince oyuncudan yeniden onay istenir |
| **Gizlilik / KVKK** | Gizlilik sayfası içeriği |
| **Yetki Grupları** | Menü ve işlem bayrakları |
| **Ticket Ayarları** | Kategori, durum, dosya tipi |
| **Duyuru Türleri** | Duyuru kategorileri |

Oyuncu detayında ayrıca **bayrak değişimi** (`player.change_empire`) görüntülenir.

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

#### A) Oyun veritabanları (zorunlu)

Metin2 sunucunuzdan gelen (veya sıfırdan kurduğunuz) dört veritabanı:

| DB | Rol |
|----|-----|
| `account` | Hesaplar (`account` tablosu + `WebPermission`) |
| `common` | GM listesi (`gmlist`) vb. |
| `player` | Karakter, lonca, banword, pcbang_ip, change_empire… |
| `log` | loginlog, command_log, levellog… |

İsimler `config.php` → `servers.*.databases` ile değiştirilebilir; varsayılanlar yukarıdakilerdir.

Sadece boş DB iskeleti için (tablo şeması oyun dump’ınızdan gelmelidir):

```bash
mysql -u root -p < database/setup_databases.sql
```

Bu script `account`, `common`, `player`, `log` ve `DNWeb` veritabanlarını oluşturur. **Oyun tablolarını oluşturmaz** — onları kendi Metin2 SQL dump’ınızla yükleyin.

#### B) DNWeb (zorunlu — site CMS)

`DNWeb` veritabanı site ayarları, duyuru, ticket, oturum, yetki, captcha, mail log vb. içindir.

- `setup_databases.sql` ile boş `DNWeb` + temel `settings` oluşur.
- İlk sayfa isteğinde **`Schema::ensure()`** otomatik olarak eksik DNWeb tablolarını ve kolonları oluşturur / günceller (manuel migration gerekmez).

#### C) Hesap yetkisi kolonu

`account.account.WebPermission` paneli açmak için gerekir. Schema bunu da otomatik eklemeye çalışır; elle:

```bash
mysql -u root -p < database/migrate_auth.sql
```

Örnek süper admin:

```sql
UPDATE account.account SET WebPermission = 2 WHERE login = 'SENIN_LOGIN';
```

### 3) Yapılandırma

`config/config.php` içinde doldurun:

1. **`app.url`** — site adresi (ör. `http://127.0.0.1:8080`)
2. **`web_database`** — DNWeb bağlantısı (host, user, password, `DNWeb`)
3. **`servers.main`** — oyun DB host / user / password ve `databases` alias’ları
4. **`security.app_key`** — canlıda uzun rastgele anahtar
5. Canlıda: `app.debug = false`, HTTPS / cookie secure ayarları

### 4) İzinler / klasörler

`storage/` yazılabilir olmalı (session, log, cache, upload).

### 5) İlk giriş

1. Oyunda veya seed script ile bir hesap oluşturun  
2. `WebPermission = 1` veya `2` verin  
3. `/` üzerinden giriş → `/panel` veya `/admin`

Opsiyonel test hesapları: `database/seed_test_accounts.php`, `database/seed_guilds.php`

### 6) Captcha (opsiyonel)

Admin → **Captcha**: Google reCAPTCHA v2 veya Cloudflare Turnstile key’lerini girin.  
Domain / hostname listesine `127.0.0.1`, `localhost` ve canlı domain’i ekleyin.

---

## Veritabanı özeti

```
account   → hesap, WebPermission, cash…
common    → gmlist…
player    → player, guild, banword, horse_name, pcbang_ip, change_empire…
log       → loginlog, command_log, levellog, hack_log…
DNWeb     → settings, duyuru, ticket, web_sessions, yetkiler, admin log,
            ip_bans (sebep), community_rules, mail, captcha ayarları…
```

Detaylı tablo referansı: `docs/database-reference.md`

Panel, oyun tablolarını **okur / gerektiğinde yazar**; şema dump’ınız eksikse ilgili menü boş veya hata verir (ör. `gmlist` yoksa GM menüsü çalışmaz).

---

## Proje yapısı

```
config/config.php       → Uygulama, tema, güvenlik, sunucu / DB
app/Core/               → Config, Database, Security, Session, Theme, Router, Schema
app/Controllers/        → HTTP controller’lar
app/Services/           → İş mantığı
themes/EasternV1/       → Anasayfa, oyuncu paneli, admin görünümleri
public/                 → Document root (index.php, router.php)
routes/web.php          → Rotalar
database/               → Kurulum / migrate / seed scriptleri
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
- HTTP güvenlik header’ları (CSP; captcha domain’leri dahil)  
- PDO prepared statements  
- Tema asset path koruması  

---

## Hızlı kontrol listesi

- [ ] `account` / `common` / `player` / `log` dump yüklü  
- [ ] `DNWeb` oluşturuldu  
- [ ] `config/config.php` DB ve `app.url` dolu  
- [ ] En az bir hesapta `WebPermission = 2`  
- [ ] `php -S … -t public` veya vhost → `public/`  
- [ ] `/` açılıyor, kayıt / giriş çalışıyor  
- [ ] `/admin` menüleri yetki grubunda görünüyor  
