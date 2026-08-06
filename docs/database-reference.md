# M2DN — Oyun Veritabanı Referansı

Kaynak: Metin2 klasik şema (account / common / log / player).
Site ayarları `DNWeb` içinde tutulur; oyun verisi bu 4 DB'dedir.

---

## account

| Tablo | İşlev |
|-------|--------|
| `account` | Kayıtlı hesaplar (login, şifre, e-posta, cash, mileage, status, **WebPermission**) |
| `WebPermission` | `0` user · `1` admin · `2` süper admin · `NULL` → 0 sayılır |
| `block_exception` | *(net değil — genelde IP/hesap engel istisnaları)* |
| `GameTime` | *(net değil — oyun süresi / PCBang tarzı süre)* |
| `GameTimeIP` | *(net değil — süre sistemi IP bağları)* |
| `GameTimeLog` | *(net değil — süre kullanım logları)* |
| `iptocountry` | IP aralığı → ülke eşlemesi |
| `string` | *(net değil)* |

**Panel kullanımı:** kayıt/giriş, cash yükleme, hesap ban/status, e-posta, **Nesne Market Elmas (`cash`) düşümü**.

---

## common

| Tablo | İşlev |
|-------|--------|
| `gmhost` | *(net değil — genelde GM yetkisinin geçerli olduğu host/IP)* |
| `gmlist` | GM hesapları ve yetki seviyeleri |
| `locale` | Dil / locale anahtar-değer ayarları |
| `spam_db` | Spam kabul edilen kelimeler |

**Panel kullanımı:** GM yönetimi, spam kelime listesi.

---

## log

| Tablo | İşlev |
|-------|--------|
| `bootlog` | Kanalların açılış zamanları |
| `change_empire` | Krallığın İzi ile imparatorluk değiştirenler |
| `change_name` | Karakter adı değişimleri (eski isim) |
| `chat_log` | Global kanal sohbet kayıtları |
| `command_log` | GM komut kayıtları |
| `cube` | Craft (cube) sistemi logları |
| `dragon_slay_log` | *(net değil — ejderha/boss öldürme logu olabilir)* |
| `fish_log` | Balıkçılık ödül logları |
| `GameTimeLog` | *(bilinmiyor)* |
| `goldlog` | Yang kazanım logları |
| `hack_crc_log` | Hile tespiti (CRC) |
| `hack_log` | Hile tespiti |
| `hackshield_log` | HackShield koruma kayıtları |
| `invalid_server_log` | *(bilinmiyor)* |
| `levellog` | Level atlama kayıtları |
| `log` | Genel oyun logu (giriş, trade, pazar, yere atma/alma vb.) |
| `loginlog` | Oyuna giriş kayıtları |
| `loginlog2` | Giriş + istemci versiyonu |
| `money_log` | Yang logları |
| `pcbang_loginlog` | *(bilinmiyor — PCBang giriş)* |
| `quest_reward_log` | Görev ödül kayıtları |
| `refinelog` | + basma (refine) kayıtları |
| `shout_log` | Bağırma kanalı mesajları |
| `speed_hack` | Hız hilesi tespit verileri |
| `vcard_log` | *(muhtemelen ödeme / vcard)* |

**Panel kullanımı:** admin audit, hile inceleme, ekonomi/trade denetimi, GM komut izleme.

---

## player

| Tablo | İşlev |
|-------|--------|
| `affect` | Karakter buff/efektleri (iksir, biyolog vb.) |
| `banword` | Yasaklı kelimeler (oyunda **** sansür) |
| `guild` | Loncalar |
| `guild_comment` | Lonca yorumları |
| `guild_grade` | Lonca rütbe/yetki tanımları |
| `guild_member` | Lonca üyeleri |
| `guild_war` | Lonca savaşları |
| `guild_war_bet` | *(emin değil — savaş bahisleri olabilir)* |
| `guild_war_reservation` | Lonca savaşı rezervasyon/detayları |
| `horse_name` | At isimleri |
| `item` | Envanter + depo eşyaları (`window='SAFEBOX'` = depo; Nesne Market teslimatı) |
| `item_attr` | Eşya efsun (attr) tanımları |
| `item_attr_rare` | 6. ve 7. efsun tanımları |
| `item_award` | Nesne marketten alınan / ödül bekleyen itemler *(eski akış; canlı market `player.item` SAFEBOX kullanır)* |
| `item_proto` | Tüm eşya prototipleri |
| `land` | Lonca arazileri |
| `lotto_list` | *(bilinmiyor)* |
| `marriage` | Evlilik (`pid1`, `pid2`, `love_point`, `time`, `is_married`) — panel Evlilikler |
| `messenger_list` | Arkadaş listesi |
| `mob_proto` | Mob / NPC prototipleri |
| `monarch` | Monarşi sistemi |
| `monarch_candidacy` | *(bilinmiyor — adaylık)* |
| `monarch_election` | Monarşi oyları |
| `myshop_pricelist` | İpek Bohça pazar fiyat hafızası |
| `object` | Lonca arazisi binaları |
| `object_proto` | Arazi bina prototipleri |
| `pcbang_ip` | IP ban listesi (panel IP Ban) |
| `player` | Karakterler |
| `player_deleted` | Silinen karakterler |
| `player_index` | Hesap ↔ karakter / bayrak (empire) indeksi |
| `quest` | Görev ilerleme / event durumları |
| `refine_proto` | + basma malzeme, yang, oran tanımları |
| `safebox` | Depo şifresi ve boyut (sayfa sayısı) |
| `shop` | Eşya satan NPC tanımları |
| `shop_item` | NPC'nin sattığı item + adet (`item_proto.gold` = fiyat) |
| `skill_proto` | Yetenek prototipleri |
| `sms_pool` | SMS gönderim kayıtları (genelde kapalı) |
| `string` | *(bilinmiyor)* |
| `change_empire` | Hesap bazlı bayrak değişim sayacı |

**Panel kullanımı:** karakter listesi, lonca, banword, binek, IP ban, evlilik, Nesne Market depo teslimatı (`item` + `safebox`), bayrak değişimi.

Referans SQL: `database/player_marriage_reference.sql`

---

## DNWeb (site)

| Tablo | İşlev |
|-------|--------|
| `settings` | Site config (tema, oranlar, captcha, logo…) |
| `web_sessions` | Panel oturumları |
| `account_security` | 2FA, IP kilidi, depo şifresi metası |
| `account_consents` | Topluluk kuralları onayı |
| `account_activity_log` | Oyuncu panel işlem logları |
| `account_bans` / `penalty_templates` | Ban kayıtları / şablonlar |
| `ip_bans` | IP ban sebep metası (`player.pcbang_ip` ile) |
| `admin_action_logs` | Yönetici işlem logları |
| `announcements` / `announcement_types` | Duyurular |
| `tickets` / `ticket_*` | Destek sistemi |
| `mail_*` / `password_resets` / `notifications` | Mail ve bildirim |
| `site_*` / `community_rules` | Anasayfa içerik, kurallar |
| `permission_*` / `account_staff_groups` | Yetki grupları |
| **`market_categories`** | Nesne Market kategorileri |
| **`market_items`** | Nesne Market ürünleri (kod, fiyat, indirim, görsel, süre) |
| **`market_sales_logs`** | Satış logları (hesap, elmas önce/sonra, depo slot) |

SQL: `database/dnweb_full_schema.sql` · migrate: `database/migrate_nesne_market.sql`

---

## Panel geliştirme eşlemesi (özet)

| Özellik | Kaynak |
|---------|--------|
| Giriş / kayıt | `account.account` |
| Karakterlerim | `player.player` + `player.player_index` |
| Cash / Elmas | `account.account.cash` |
| Nesne Market katalog | `DNWeb.market_*` |
| Nesne Market satın alma | `account.cash` − · `player.item` SAFEBOX + · `market_sales_logs` |
| Evlilikler | `player.marriage` |
| Lonca | `player.guild*` |
| Ban / sansür | `player.banword`, `account.account.status` |
| GM listesi | `common.gmlist` |
| Admin loglar | `DNWeb.admin_action_logs` + `log.*` |
| Site ayarları | `DNWeb.settings` |
