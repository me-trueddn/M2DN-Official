# 2026-08-07 — Ready Only yetki

Canlı site migrate klasörü.

| Dosya | Açıklama |
|-------|----------|
| `01_ready_only_group.sql` | `Ready Only` grubu (WebPerm 1) + salt görüntüleme bayrakları |

```bash
mysql -u USER -p DNWeb < database/2026-08-07-ready-only-yetki/01_ready_only_group.sql
```

Alternatif: siteyi bir kez açmak (`Schema::ensure`) aynı grubu otomatik ekler.
