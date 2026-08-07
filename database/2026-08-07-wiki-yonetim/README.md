# 2026-08-07 — Wiki Yönetimi yetkileri

Admin ana menü **Wiki → Wiki Yönetimi**. İçerik `DNWeb.settings` (`wiki` / `content_json`).

| Bayrak | Açıklama |
|--------|----------|
| `menu_wiki` | Menü görünür |
| `wiki_manage` | İçerik kaydet / varsayılana sıfırla |

```bash
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/01_wiki_flags.sql
```

Alternatif: siteyi bir kez açmak (`Schema::ensure`) bayrakları Admin/Super gruplara ekler.
