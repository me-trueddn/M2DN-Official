<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Public /wiki içeriği — DNWeb settings (wiki.content_json).
 */
final class WikiService
{
    public const SETTINGS_GROUP = 'wiki';
    public const SETTINGS_KEY = 'content_json';

    /** @return array<string, mixed> */
    public static function content(): array
    {
        $raw = SiteContentService::get(self::SETTINGS_GROUP, self::SETTINGS_KEY, null);
        if (!is_string($raw) || trim($raw) === '') {
            return self::defaults();
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return self::defaults();
        }
        return self::mergeDefaults($decoded);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok:bool, errors:list<string>}
     */
    public static function save(array $data): array
    {
        $normalized = self::normalize($data);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return ['ok' => false, 'errors' => ['Wiki içeriği kaydedilemedi (JSON).']];
        }
        if (!SiteContentService::set(self::SETTINGS_GROUP, self::SETTINGS_KEY, $json)) {
            return ['ok' => false, 'errors' => ['Wiki içeriği veritabanına yazılamadı.']];
        }
        return ['ok' => true, 'errors' => []];
    }

    public static function resetToDefaults(): array
    {
        return self::save(self::defaults());
    }

    /**
     * Admin form POST → yapı.
     *
     * @return array<string, mixed>
     */
    public static function fromPost(array $post): array
    {
        $cards = [];
        $cTitles = $post['intro_card_title'] ?? [];
        $cTexts = $post['intro_card_text'] ?? [];
        $cIcons = $post['intro_card_icon'] ?? [];
        if (is_array($cTitles)) {
            foreach ($cTitles as $i => $title) {
                $title = trim((string) $title);
                if ($title === '') {
                    continue;
                }
                $cards[] = [
                    'icon' => trim((string) ($cIcons[$i] ?? 'fa-solid fa-circle-info')),
                    'title' => $title,
                    'text' => trim((string) ($cTexts[$i] ?? '')),
                ];
            }
        }

        $classes = [];
        $clNames = $post['class_name'] ?? [];
        if (is_array($clNames)) {
            foreach ($clNames as $i => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }
                $stats = [];
                for ($s = 0; $s < 3; $s++) {
                    $lab = trim((string) ($post['class_stat_label'][$i][$s] ?? ''));
                    if ($lab === '') {
                        continue;
                    }
                    $stats[] = [
                        'label' => $lab,
                        'pct' => max(0, min(100, (int) ($post['class_stat_pct'][$i][$s] ?? 0))),
                    ];
                }
                $classes[] = [
                    'icon' => trim((string) ($post['class_icon'][$i] ?? 'fa-solid fa-khanda')),
                    'name' => $name,
                    'sub' => trim((string) ($post['class_sub'][$i] ?? '')),
                    'text' => trim((string) ($post['class_text'][$i] ?? '')),
                    'stats' => $stats,
                ];
            }
        }

        $maps = [];
        $mTitles = $post['map_title'] ?? [];
        if (is_array($mTitles)) {
            foreach ($mTitles as $i => $title) {
                $title = trim((string) $title);
                if ($title === '') {
                    continue;
                }
                $tagClass = trim((string) ($post['map_tag_class'][$i] ?? 'pve'));
                if (!in_array($tagClass, ['pve', 'pvp', 'metin'], true)) {
                    $tagClass = 'pve';
                }
                $maps[] = [
                    'tag' => trim((string) ($post['map_tag'][$i] ?? 'PvE')),
                    'tag_class' => $tagClass,
                    'title' => $title,
                    'level' => trim((string) ($post['map_level'][$i] ?? '')),
                    'text' => trim((string) ($post['map_text'][$i] ?? '')),
                ];
            }
        }

        $monsters = [];
        $moNames = $post['monster_name'] ?? [];
        if (is_array($moNames)) {
            foreach ($moNames as $i => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }
                $dropsRaw = trim((string) ($post['monster_drops'][$i] ?? ''));
                $drops = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $dropsRaw) ?: [])));
                $monsters[] = [
                    'icon' => trim((string) ($post['monster_icon'][$i] ?? 'fa-solid fa-paw')),
                    'name' => $name,
                    'boss_badge' => trim((string) ($post['monster_badge'][$i] ?? '')),
                    'level' => trim((string) ($post['monster_level'][$i] ?? '')),
                    'map' => trim((string) ($post['monster_map'][$i] ?? '')),
                    'hp_pct' => max(0, min(100, (int) ($post['monster_hp'][$i] ?? 50))),
                    'drops' => $drops,
                ];
            }
        }

        $metins = [];
        $meTitles = $post['metin_title'] ?? [];
        if (is_array($meTitles)) {
            foreach ($meTitles as $i => $title) {
                $title = trim((string) $title);
                if ($title === '') {
                    continue;
                }
                $style = trim((string) ($post['metin_style'][$i] ?? 'red'));
                if (!in_array($style, ['red', 'black', 'gold'], true)) {
                    $style = 'red';
                }
                $metins[] = [
                    'style' => $style,
                    'glyph' => trim((string) ($post['metin_glyph'][$i] ?? '石')),
                    'title' => $title,
                    'text' => trim((string) ($post['metin_text'][$i] ?? '')),
                ];
            }
        }

        $upgrade = [];
        $upLevels = $post['upgrade_level'] ?? [];
        if (is_array($upLevels)) {
            foreach ($upLevels as $i => $level) {
                $level = trim((string) $level);
                if ($level === '') {
                    continue;
                }
                $rateClass = trim((string) ($post['upgrade_rate_class'][$i] ?? 'rate-mid'));
                if (!in_array($rateClass, ['rate-high', 'rate-mid', 'rate-low'], true)) {
                    $rateClass = 'rate-mid';
                }
                $upgrade[] = [
                    'level' => $level,
                    'rate' => trim((string) ($post['upgrade_rate'][$i] ?? '')),
                    'rate_class' => $rateClass,
                    'material' => trim((string) ($post['upgrade_material'][$i] ?? '')),
                    'risk' => trim((string) ($post['upgrade_risk'][$i] ?? '')),
                ];
            }
        }

        $clanStats = [];
        $csVals = $post['clan_stat_value'] ?? [];
        if (is_array($csVals)) {
            foreach ($csVals as $i => $val) {
                $val = trim((string) $val);
                $lab = trim((string) ($post['clan_stat_label'][$i] ?? ''));
                if ($val === '' && $lab === '') {
                    continue;
                }
                $clanStats[] = ['value' => $val, 'label' => $lab];
            }
        }

        $benefits = [];
        $bens = $post['clan_benefit'] ?? [];
        if (is_array($bens)) {
            foreach ($bens as $b) {
                $b = trim((string) $b);
                if ($b !== '') {
                    $benefits[] = $b;
                }
            }
        }

        $faq = [];
        $qs = $post['faq_q'] ?? [];
        $as = $post['faq_a'] ?? [];
        if (is_array($qs)) {
            foreach ($qs as $i => $q) {
                $q = trim((string) $q);
                if ($q === '') {
                    continue;
                }
                $faq[] = [
                    'q' => $q,
                    'a' => trim((string) ($as[$i] ?? '')),
                ];
            }
        }

        return [
            'head' => [
                'eyebrow' => trim((string) ($post['head_eyebrow'] ?? '')),
                'title' => trim((string) ($post['head_title'] ?? '')),
                'lead' => trim((string) ($post['head_lead'] ?? '')),
                'search_placeholder' => trim((string) ($post['head_search'] ?? '')),
            ],
            'intro' => [
                'eyebrow' => trim((string) ($post['intro_eyebrow'] ?? '')),
                'title' => trim((string) ($post['intro_title'] ?? '')),
                'text' => trim((string) ($post['intro_text'] ?? '')),
                'cards' => $cards,
                'use_live_rates' => !empty($post['intro_use_live_rates']),
            ],
            'classes_section' => [
                'eyebrow' => trim((string) ($post['classes_eyebrow'] ?? '')),
                'title' => trim((string) ($post['classes_title'] ?? '')),
                'text' => trim((string) ($post['classes_text'] ?? '')),
            ],
            'classes' => $classes,
            'maps_section' => [
                'eyebrow' => trim((string) ($post['maps_eyebrow'] ?? '')),
                'title' => trim((string) ($post['maps_title'] ?? '')),
                'text' => trim((string) ($post['maps_text'] ?? '')),
            ],
            'maps' => $maps,
            'monsters_section' => [
                'eyebrow' => trim((string) ($post['monsters_eyebrow'] ?? '')),
                'title' => trim((string) ($post['monsters_title'] ?? '')),
                'text' => trim((string) ($post['monsters_text'] ?? '')),
            ],
            'monsters' => $monsters,
            'metins_section' => [
                'eyebrow' => trim((string) ($post['metins_eyebrow'] ?? '')),
                'title' => trim((string) ($post['metins_title'] ?? '')),
                'text' => trim((string) ($post['metins_text'] ?? '')),
            ],
            'metins' => $metins,
            'upgrade_section' => [
                'eyebrow' => trim((string) ($post['upgrade_eyebrow'] ?? '')),
                'title' => trim((string) ($post['upgrade_title'] ?? '')),
                'text' => trim((string) ($post['upgrade_text'] ?? '')),
            ],
            'upgrade' => $upgrade,
            'clan' => [
                'eyebrow' => trim((string) ($post['clan_eyebrow'] ?? '')),
                'title' => trim((string) ($post['clan_title'] ?? '')),
                'text' => trim((string) ($post['clan_text'] ?? '')),
                'stats' => $clanStats,
                'benefits' => $benefits,
            ],
            'faq_section' => [
                'eyebrow' => trim((string) ($post['faq_eyebrow'] ?? '')),
                'title' => trim((string) ($post['faq_title'] ?? '')),
            ],
            'faq' => $faq,
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'head' => [
                'eyebrow' => '典籍 · Bilgi Bankası',
                'title' => 'Efsanenin tüm sırları burada.',
                'lead' => 'Sınıflardan haritalara, canavarlardan yükseltme oranlarına kadar M2DN dünyasına dair bilmen gereken her şey.',
                'search_placeholder' => 'Sınıf, harita, eşya veya canavar ara...',
            ],
            'intro' => [
                'eyebrow' => '序章 · Başlangıç',
                'title' => 'Giriş & Temel Bilgiler',
                'text' => 'M2DN, klasik Metin2 oynanışını koruyan, dengeli oranlarla kurulmuş bağımsız bir sunucudur. Karakterini oluşturduktan sonra Sohan Köyü\'nde başlar, seviye 10\'dan itibaren sınıfının gerçek yeteneklerini kullanmaya başlarsın.',
                'use_live_rates' => true,
                'cards' => [
                    [
                        'icon' => 'fa-solid fa-gauge-high',
                        'title' => 'Sunucu Oranları',
                        'text' => 'EXP, Drop ve Yang oranları site ayarlarından alınır. Oranlar oyunun ilerleyen bölümlerinde (60+ seviye) otomatik olarak kademeli artar.',
                    ],
                    [
                        'icon' => 'fa-solid fa-users',
                        'title' => 'Kanal Yapısı',
                        'text' => '4 kanal aktif olarak çalışır: Kanal 1–3 PvE ağırlıklı, Kanal 4 test/etkinlik amaçlıdır.',
                    ],
                    [
                        'icon' => 'fa-solid fa-shield-halved',
                        'title' => 'Fair Play',
                        'text' => 'Nesne Market\'te Elmas ile satın alınan ürünler dengeyi bozmayacak şekilde yönetilir. Asıl güç kazanımı oyun içi emekle sağlanır.',
                    ],
                    [
                        'icon' => 'fa-solid fa-clock',
                        'title' => 'Bakım Takvimi',
                        'text' => 'Planlı bakımlar her çarşamba 04:00–06:00 arasında yapılır. Acil bakımlar Discord ve site duyurularından bildirilir.',
                    ],
                ],
            ],
            'classes_section' => [
                'eyebrow' => '職業 · Karakter',
                'title' => 'Sınıflar',
                'text' => 'Her sınıfın kendine has bir savaş tarzı ve zorluk seviyesi vardır. Aşağıdaki temel istatistikler karşılaştırma amaçlıdır; gerçek performans ekipmana göre değişir.',
            ],
            'classes' => [
                [
                    'icon' => 'fa-solid fa-khanda',
                    'name' => 'Savaşçı',
                    'sub' => 'Yakın Dövüş',
                    'text' => 'Yüksek can ve fiziksel güce sahip, ön saf tankı. Yeni başlayanlar için en kolay sınıf.',
                    'stats' => [
                        ['label' => 'Güç', 'pct' => 92],
                        ['label' => 'Savunma', 'pct' => 80],
                        ['label' => 'Zorluk', 'pct' => 30],
                    ],
                ],
                [
                    'icon' => 'fa-solid fa-wind',
                    'name' => 'Ninja',
                    'sub' => 'Çeviklik / Menzilli',
                    'text' => 'Yüksek kritik vuruş ve çeviklikle öne çıkar. Solo avlanmada ve PvP\'de güçlüdür, oynaması zordur.',
                    'stats' => [
                        ['label' => 'Çeviklik', 'pct' => 95],
                        ['label' => 'Kritik', 'pct' => 88],
                        ['label' => 'Zorluk', 'pct' => 75],
                    ],
                ],
                [
                    'icon' => 'fa-solid fa-skull',
                    'name' => 'Sura',
                    'sub' => 'Karanlık Büyü',
                    'text' => 'Can emme ve alan hasarı büyüleriyle savaşır. Grup avlarında ve lonca savaşlarında etkilidir.',
                    'stats' => [
                        ['label' => 'Büyü', 'pct' => 90],
                        ['label' => 'Can Emme', 'pct' => 84],
                        ['label' => 'Zorluk', 'pct' => 60],
                    ],
                ],
                [
                    'icon' => 'fa-solid fa-leaf',
                    'name' => 'Şaman',
                    'sub' => 'Destek / İyileştirme',
                    'text' => 'Grubu iyileştirir ve güçlendirir. Lonca savaşlarında ve düzenli parti avlarında vazgeçilmezdir.',
                    'stats' => [
                        ['label' => 'İyileş.', 'pct' => 93],
                        ['label' => 'Destek', 'pct' => 87],
                        ['label' => 'Zorluk', 'pct' => 45],
                    ],
                ],
            ],
            'maps_section' => [
                'eyebrow' => '地図 · Dünya',
                'title' => 'Haritalar',
                'text' => 'Sohan Köyü\'nden Kızıl Tapınak\'a, seviyene uygun bölgeyi seç ve avına başla.',
            ],
            'maps' => [
                ['tag' => 'PvE', 'tag_class' => 'pve', 'title' => 'Yükseliş Vadisi', 'level' => 'Seviye 1–25', 'text' => 'Yeni başlayanlar için tasarlanmış, düşük seviyeli canavarların bulunduğu açık alan.'],
                ['tag' => 'PvE', 'tag_class' => 'pve', 'title' => 'Karanlık Orman', 'level' => 'Seviye 25–45', 'text' => 'Yoğun ağaç dokusu ve gölge canavarlarıyla dolu, orta seviye avlanma bölgesi.'],
                ['tag' => 'Metin', 'tag_class' => 'metin', 'title' => 'Metin Meydanı', 'level' => 'Seviye 30–60', 'text' => 'Yüksek metin taşı yoğunluğuna sahip, grup avları için ideal geniş meydan.'],
                ['tag' => 'PvE', 'tag_class' => 'pve', 'title' => 'Sis Gölü', 'level' => 'Seviye 45–70', 'text' => 'Su canavarları ve nadir balıkçılık noktalarıyla bilinen atmosferik bölge.'],
                ['tag' => 'PvP', 'tag_class' => 'pvp', 'title' => 'Kızıl Tapınak', 'level' => 'Seviye 65–90', 'text' => 'Sunucunun amiral gemisi haritası; hem PvE boss\'lar hem serbest PvP alanları içerir.'],
                ['tag' => 'PvP', 'tag_class' => 'pvp', 'title' => 'Ejderha İni', 'level' => 'Seviye 80+', 'text' => 'Son seviye oyuncular için tasarlanmış, haftalık boss rush etkinliğinin geçtiği alan.'],
            ],
            'monsters_section' => [
                'eyebrow' => '魔物 · Dünya',
                'title' => 'Canavarlar & Boss\'lar',
                'text' => 'Öne çıkan bazı canavar ve boss\'lar, seviyeleri ve bilinen düşen eşyaları.',
            ],
            'monsters' => [
                ['icon' => 'fa-solid fa-paw', 'name' => 'Gölge Kurdu', 'boss_badge' => '', 'level' => '22', 'map' => 'Karanlık Orman', 'hp_pct' => 30, 'drops' => ['Deri', 'Küçük Yang']],
                ['icon' => 'fa-solid fa-spider', 'name' => 'Kara Örümcek', 'boss_badge' => '', 'level' => '38', 'map' => 'Sis Gölü', 'hp_pct' => 45, 'drops' => ['Zehir Bezi', 'Şans Tılsımı']],
                ['icon' => 'fa-solid fa-dragon', 'name' => 'Kızıl Muhafız', 'boss_badge' => 'Boss', 'level' => '68', 'map' => 'Kızıl Tapınak', 'hp_pct' => 78, 'drops' => ['Efsane Silah Parçası', 'Yükseltme Taşı']],
                ['icon' => 'fa-solid fa-fire', 'name' => 'Alev Ejderhası', 'boss_badge' => 'Sunucu Boss\'u', 'level' => '90', 'map' => 'Ejderha İni', 'hp_pct' => 100, 'drops' => ['Ejderha Kanadı Kostümü', 'Kızıl Binek', 'Efsane Sandık']],
            ],
            'metins_section' => [
                'eyebrow' => '石 · Dünya',
                'title' => 'Metin Taşları',
                'text' => 'Haritalarda dağınık halde bulunan metin taşları, kırıldıklarında farklı ödül havuzlarına sahiptir.',
            ],
            'metins' => [
                ['style' => 'red', 'glyph' => '紅', 'title' => 'Kızıl Metin', 'text' => 'En yaygın taş. Kırıldığında canavar dalgası çıkarır, orta seviye eşya ve yang düşürür.'],
                ['style' => 'black', 'glyph' => '黑', 'title' => 'Kara Metin', 'text' => 'Daha nadir, kırılması zordur. Yükseltme taşı ve nadir sarf malzemesi düşürme ihtimali yüksektir.'],
                ['style' => 'gold', 'glyph' => '金', 'title' => 'Altın Metin', 'text' => 'Sunucuda haftada birkaç kez beliren özel taş; kırıldığında efsanevi eşya düşürme şansı sunar.'],
            ],
            'upgrade_section' => [
                'eyebrow' => '強化 · Sistemler',
                'title' => 'Eşya Yükseltme',
                'text' => 'Silah ve zırhlarını yükseltme taşı kullanarak güçlendirebilirsin. Başarı oranı seviye arttıkça düşer; +6 üzeri yükseltmelerde kırılma riski oluşur.',
            ],
            'upgrade' => [
                ['level' => '+0 → +3', 'rate' => '%95', 'rate_class' => 'rate-high', 'material' => 'Temel Yükseltme Taşı', 'risk' => 'Yok'],
                ['level' => '+3 → +6', 'rate' => '%70', 'rate_class' => 'rate-mid', 'material' => 'Orta Yükseltme Taşı', 'risk' => 'Yok'],
                ['level' => '+6 → +8', 'rate' => '%45', 'rate_class' => 'rate-mid', 'material' => 'Gelişmiş Yükseltme Taşı', 'risk' => 'Düşük'],
                ['level' => '+8 → +9', 'rate' => '%20', 'rate_class' => 'rate-low', 'material' => 'Efsane Yükseltme Taşı', 'risk' => 'Orta'],
            ],
            'clan' => [
                'eyebrow' => '結社 · Sistemler',
                'title' => 'Lonca Sistemi',
                'text' => 'Loncalar sunucunun sosyal ve rekabetçi omurgasıdır. Her cumartesi düzenlenen Lonca Savaşı\'nda en iyi loncalar sıralamada yükselir. Panelde lonca savaşları ve ladder görüntülenebilir.',
                'stats' => [
                    ['value' => '80', 'label' => 'Maks. Üye'],
                    ['value' => 'Sk. 20:00', 'label' => 'Lonca Savaşı'],
                    ['value' => '15', 'label' => 'Aktif Lonca'],
                    ['value' => 'Lv. 5', 'label' => 'Maks. Lonca Seviyesi'],
                ],
                'benefits' => [
                    'Lonca seviyesi arttıkça üyelere ek EXP bonusu.',
                    'Lonca Savaşı galibi haftalık özel eşya ödülü kazanır.',
                    'Lonca deposu ile üyeler arası eşya paylaşımı.',
                    'Lonca savaşları ve ladder oyuncu panelinde görüntülenir.',
                ],
            ],
            'faq_section' => [
                'eyebrow' => '問答 · Yardım',
                'title' => 'Sıkça Sorulan Sorular',
            ],
            'faq' => [
                [
                    'q' => 'Karakterim silinirse geri alabilir miyim?',
                    'a' => 'Evet, yanlışlıkla silinen karakterler için panelden destek talebi açman yeterli. GM ekibi hesap doğrulaması sonrası karakterini geri yükler.',
                ],
                [
                    'q' => 'Elmas\'ı gerçek paraya çevirebilir miyim?',
                    'a' => 'Hayır. Elmas yalnızca Nesne Market alışverişi ve market kuponları için kullanılır; nakde çevrilemez veya oyuncular arası transfer edilemez.',
                ],
                [
                    'q' => 'Bot kullanımı fark edilirse ne olur?',
                    'a' => 'İlk tespitte hesap 7 gün banlanır, tekrarında kalıcı ban uygulanır. Anti-cheat sistemi 7/24 aktif olarak çalışır. Detaylar için topluluk kurallarına bak.',
                ],
                [
                    'q' => 'Şifremi unuttum, ne yapmalıyım?',
                    'a' => 'Anasayfadaki Giriş penceresinden «Şifremi Unuttum» ile kayıtlı e-posta adresine sıfırlama bağlantısı alabilirsin.',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function mergeDefaults(array $data): array
    {
        $defaults = self::defaults();
        foreach ($defaults as $key => $defaultVal) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $defaultVal;
                continue;
            }
            if (is_array($defaultVal) && self::isAssoc($defaultVal) && is_array($data[$key])) {
                $data[$key] = array_merge($defaultVal, $data[$key]);
            }
        }
        return self::normalize($data);
    }

    /** @param array<string, mixed> $data */
    private static function isAssoc(array $data): bool
    {
        if ($data === []) {
            return false;
        }
        return array_keys($data) !== range(0, count($data) - 1);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function normalize(array $data): array
    {
        $d = self::defaults();
        $out = $d;

        foreach (['head', 'intro', 'classes_section', 'maps_section', 'monsters_section', 'metins_section', 'upgrade_section', 'faq_section', 'clan'] as $section) {
            if (isset($data[$section]) && is_array($data[$section])) {
                $out[$section] = array_merge($d[$section], $data[$section]);
            }
        }
        if (isset($data['intro']['use_live_rates'])) {
            $out['intro']['use_live_rates'] = !empty($data['intro']['use_live_rates']);
        }
        foreach (['classes', 'maps', 'monsters', 'metins', 'upgrade', 'faq'] as $listKey) {
            if (isset($data[$listKey]) && is_array($data[$listKey])) {
                $out[$listKey] = array_values($data[$listKey]);
            }
        }
        if (isset($data['clan']['stats']) && is_array($data['clan']['stats'])) {
            $out['clan']['stats'] = array_values($data['clan']['stats']);
        }
        if (isset($data['clan']['benefits']) && is_array($data['clan']['benefits'])) {
            $out['clan']['benefits'] = array_values($data['clan']['benefits']);
        }
        if (isset($data['intro']['cards']) && is_array($data['intro']['cards'])) {
            $out['intro']['cards'] = array_values($data['intro']['cards']);
        }

        return $out;
    }
}
