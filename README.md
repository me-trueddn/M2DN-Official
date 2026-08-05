# M2DN — PHP Metin2 Web Altyapısı

Güvenlik odaklı, çoklu sunucu ve çoklu tema destekli panel iskeleti.

## Kurulum

1. Web sunucu document root'unu `public/` klasörüne yönlendirin  
   veya yerel test için:

```bash
php -S localhost:8080 -t public public/router.php
```

2. `config/config.php` dosyasında DB bilgilerini doldurun.
3. Varsayılan tema: **EasternV1** (`config.php` → `theme.active`).

## Yapı

```
config/config.php          → Uygulama, tema, güvenlik, sunucu/DB ayarları
app/Core/                  → Config, Database, Security, Session, Theme, Router
app/Controllers/           → Sayfa controller'ları
themes/EasternV1/          → Aktif tema (anasayfa, oyuncu paneli, admin)
public/                    → Giriş noktası (document root)
routes/web.php             → Rotalar
storage/                   → Session / log / cache
```

## Rotalar

| URL | Açıklama |
|-----|----------|
| `/` | Anasayfa |
| `/panel` | Oyuncu paneli |
| `/admin` | Yönetim paneli |
| `POST /server/select` | Aktif oyun sunucusu seçimi |

## Çoklu sunucu

`config.php` içindeki `servers` dizisine yeni sunucu ekleyin. Her sunucuda aynı DB alias'ları kullanılır:

- `account`
- `common`
- `player`
- `log`

Kodda kullanım:

```php
use App\Core\Database;
use App\Core\ServerManager;

$pdo = Database::account();              // varsayılan sunucu
$pdo = Database::player('main');         // belirli sunucu
$pdo = Database::web();                  // panel CMS DB
$server = ServerManager::current();
```

## Yeni tema ekleme

1. `themes/YeniTema/` klasörü oluşturun (`theme.json` + `views/`).
2. `config.php` → `theme.active` değerini `YeniTema` yapın.

## Güvenlik (hazır)

- CSRF token
- Güvenli session cookie ayarları
- XSS escape (`e()`)
- Güvenlik HTTP header'ları
- Prepared statements (PDO)
- Tema asset path traversal koruması
