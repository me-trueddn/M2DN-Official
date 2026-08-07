# 2026-08-07 — Admin 2FA kapatma

`disable_2fa` yetki bayrağı: WebPermission ≥ 1 (Ready Only hariç) gruplara eklenir.

```bash
mysql -u USER -p DNWeb < database/2026-08-07-disable-2fa/01_disable_2fa_flag.sql
```

Uygulama tarafı: Oyuncu detayı → İşlemler → **2FA**.
