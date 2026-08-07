# 2026-08-07 — Market Kuponları

DNWeb: `market_coupon_categories`, `market_coupons` + `market_sales_logs.entry_type`.

```bash
mysql -u USER -p DNWeb < database/2026-08-07-market-kuponlar/01_market_coupons.sql
```

Admin: Nesne Market → **Market Kuponları**  
Oyuncu: Panel Genel Bakış → **Market Kupon Aktif Et**

Ek migrate (satış logunda tam kod araması):

```bash
mysql -u USER -p DNWeb < database/2026-08-07-market-kuponlar/02_sales_log_coupon_hash.sql
```
