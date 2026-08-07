# 2026-08-07 — Çoklu yetki grubu

Bir hesaba birden fazla `permission_groups` atanabilir; bayraklar **OR** birleşir.

| Kural | Açıklama |
|-------|----------|
| WebPerm 1 grupları | Birden fazla seçilebilir (ör. Oyuncular + Ticket) |
| Süper Admin (web=2) | Tek rol; başka grupla birlikte olamaz |
| Default User (web=0) | Tek başına; admin gruplarıyla karışmaz |

```bash
mysql -u USER -p DNWeb < database/2026-08-07-coklu-yetki-grubu/01_multi_staff_groups.sql
```

Alternatif: siteyi bir kez açmak (`Schema::ensure`) PK migrate eder.
