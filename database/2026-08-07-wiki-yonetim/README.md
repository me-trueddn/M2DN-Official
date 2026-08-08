# 2026-08-07 — Wiki Yönetimi (canlı migrate)

Admin menü **Wiki → Kategoriler / İçerik Tipleri / İçerikler**.

## Sıra (hepsini bu sırayla çalıştırın)

| # | Dosya | Ne yapar |
|---|--------|----------|
| **1** | `01_wiki_flags.sql` | `menu_wiki` + `wiki_manage` bayrakları |
| **2** | `02_drop_wiki_content.sql` | Eski `settings` wiki içeriğini siler (`group_key='wiki'`) |
| **3** | `03_wiki_categories.sql` | `wiki_categories` (slug + `is_wiki_home` dahil) |
| **4** | `04_wiki_content.sql` | `wiki_content_types` + `wiki_pages` (seed: Basit metin) |
| **5** | `05_wiki_category_slug.sql` | Eski tabloda `slug` yoksa ekler (yoksa no-op) |
| **6** | `06_wiki_home.sql` | Eski tabloda `is_wiki_home` yoksa ekler (yoksa no-op) |

```bash
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/01_wiki_flags.sql
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/02_drop_wiki_content.sql
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/03_wiki_categories.sql
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/04_wiki_content.sql
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/05_wiki_category_slug.sql
mysql -u USER -p DNWeb < database/2026-08-07-wiki-yonetim/06_wiki_home.sql
```

Public URL: `/wiki/{slug}.html` (yalnızca alt kategoriler). `/wiki` başlangıç sayfası admin’de **Başlangıç** radyosu ile seçilir.

Alternatif: siteyi bir kez açmak (`Schema::ensure`) tabloları / kolonları / Basit metin seed’ini oluşturur; eski settings içeriği için yine **02** çalıştırın.

| Bayrak | Açıklama |
|--------|----------|
| `menu_wiki` | Menü görünür |
| `wiki_manage` | Kategori / tip / içerik düzenleme |
