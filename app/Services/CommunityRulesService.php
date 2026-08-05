<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class CommunityRulesService
{
    /**
     * @return list<array{
     *   id:int, rule_no:int, title:string, detail:string,
     *   penalty_1:string, penalty_2:string, penalty_3:string,
     *   sort_order:int, is_active:bool
     * }>
     */
    public static function list(bool $onlyActive = true): array
    {
        try {
            $sql = 'SELECT id, rule_no, title, detail, penalty_1, penalty_2, penalty_3, sort_order, is_active
                    FROM community_rules';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, rule_no ASC, id ASC';
            $rows = Database::web()->query($sql)->fetchAll() ?: [];
            return array_map([self::class, 'map'], $rows);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function get(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT id, rule_no, title, detail, penalty_1, penalty_2, penalty_3, sort_order, is_active
                 FROM community_rules WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? self::map($row) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>, id?:int}
     */
    public static function save(
        ?int $id,
        string $title,
        string $detail,
        string $penalty1,
        string $penalty2,
        string $penalty3,
        int $sortOrder = 0,
        bool $active = true
    ): array {
        $title = trim($title);
        $detail = trim($detail);
        $penalty1 = trim($penalty1);
        $penalty2 = trim($penalty2);
        $penalty3 = trim($penalty3);
        $errors = [];
        if ($title === '' || mb_strlen($title) > 200) {
            $errors[] = 'Kural başlığı zorunlu (max 200).';
        }
        if ($detail === '') {
            $errors[] = 'Detay zorunlu.';
        }
        if ($penalty1 === '' || $penalty2 === '' || $penalty3 === '') {
            $errors[] = '1 / 2 / 3. ihlal cezaları zorunlu.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $web = Database::web();
            if ($id !== null && $id > 0) {
                $web->prepare(
                    'UPDATE community_rules
                     SET title=?, detail=?, penalty_1=?, penalty_2=?, penalty_3=?,
                         sort_order=?, is_active=?, updated_at=NOW()
                     WHERE id=?'
                )->execute([
                    $title, $detail, $penalty1, $penalty2, $penalty3,
                    $sortOrder, $active ? 1 : 0, $id,
                ]);
                return ['ok' => true, 'errors' => [], 'id' => $id];
            }
            $nextNo = (int) $web->query('SELECT COALESCE(MAX(rule_no), 0) + 1 FROM community_rules')->fetchColumn();
            if ($sortOrder <= 0) {
                $sortOrder = $nextNo;
            }
            $web->prepare(
                'INSERT INTO community_rules
                  (rule_no, title, detail, penalty_1, penalty_2, penalty_3, sort_order, is_active, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([
                $nextNo, $title, $detail, $penalty1, $penalty2, $penalty3,
                $sortOrder, $active ? 1 : 0,
            ]);
            return ['ok' => true, 'errors' => [], 'id' => (int) $web->lastInsertId()];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kural kaydedilemedi.']];
        }
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM community_rules WHERE id=?')->execute([$id]);
            self::renumber();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** Madde numaralarını 1..N sırala. */
    public static function renumber(): void
    {
        try {
            $web = Database::web();
            $rows = $web->query(
                'SELECT id FROM community_rules ORDER BY sort_order ASC, rule_no ASC, id ASC'
            )->fetchAll() ?: [];
            $upd = $web->prepare('UPDATE community_rules SET rule_no=?, sort_order=? WHERE id=?');
            $n = 1;
            foreach ($rows as $r) {
                $upd->execute([$n, $n, (int) $r['id']]);
                $n++;
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /** @return list<array{title:string,detail:string,penalty_1:string,penalty_2:string,penalty_3:string}> */
    public static function defaults(): array
    {
        return [
            [
                'title' => 'Sohbet Ekranı ve Bağırmak Kullanılarak Spam/Flood Yapmak',
                'detail' => "\"Sohbet Ekranı ve Bağırmak Kullanarak Spam/Flood Yapmak\" herhangi birinin yapılmasıdır:\n\n• Tekrarlı şekilde anlamsız cümle veya rastgele yazıların sohbet ekranı üzerinden geçilmesi.\n• Sohbet ekranında ölçüsüz gönderiler veya diğer üyelerin sohbet ekran kullanımını engelleyecek şekilde aynı içerikli gönderilerin tekrarlı bir şekilde devam etmesi.",
                'penalty_1' => '1 Gün hesap kapatılması',
                'penalty_2' => '7 Gün hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Küfür, Hakaret ve Uygunsuz Üslup Kullanımı',
                'detail' => "\"Küfür, Hakaret ve Uygunsuz Üslup Kullanımı\" herhangi birinin yapılmasıdır:\n\n• Küfür, hakaret ya da sözlü taciz içeren davranışlarda bulunmak.\n• Açık saçık sözler, cinsel ifadeler veya davranışlar ile cinsel taciz yapılması.\n• Cinsel imalarda bulunarak oyuncuların taciz edilmesi.\n• Ülke, topluluk, bölge, din, ırk ya da engel ile alay edilmesi.\n• Gerçek hayat ile diğer oyuncuları korkutmaya çalışmak ya da gerçek hayat ile ilgili taleplerde bulunmak.\n• Kanunlar tarafından yasaklanmış herhangi bir kelimeyi kullanmak.\n• Oyun yöneticileri ile iletişimde uygunsuz dil kullanmak.\n• Resmi konular/postlar/Discord ve destek bölümü biletleri de dahil olmak üzere.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Uygunsuz / Yasaklı Karakter İsmi Kullanımı',
                'detail' => "\"Uygunsuz / Yasaklı Karakter İsmi Kullanımı\" herhangi birinin yapılmasıdır:\n\n• GM'yi taklit eden bir isim kullanmak.\n• Küfür, argo ya da uygunsuz içeriğe sahip karakter oluşturmak.\n• Diğer oyuncular/çalışanlar için hakaret veya cinsel taciz içeren bir isim kullanmak.\n• Ülke, topluluk, bölge, din, ırk, cinsiyet veya engeller ile alay veya bunları ayıplayan isimler kullanmak.\n• Diğer oyuncuları aldatmak veya taklit ederek dolandırmak için benzer karakter isimleri almak.\n• Üçüncü kişilerin haklarını (şeref, telif hakları ve kişisel bilgileri dahil) çiğneyen veya zarar veren bir isim kullanmak.\n• Kanunlarca yasaklanmış herhangi bir ismin kullanılması.",
                'penalty_1' => 'İsmi değiştirme zorunluluğu',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Uygunsuz Lonca İsmi / Sembolü Kullanımı',
                'detail' => "\"Uygunsuz Lonca İsmi / Sembolü Kullanımı\" herhangi birinin yapılmasıdır:\n\n• Şirket ile bağlantılı ya da çalışanlar hakkında kötü içeriğe sahip lonca ismi ya da sembolü kullanımı.\n• Küfür, argo ya da uygunsuz içeriğe sahip lonca ismi ya da sembolü kullanımı.\n• Ülke, topluluk, bölge, din, ırk, cinsiyet veya engeller ile alay veya bunları ayıplayan isimler/semboller kullanmak.\n• Üçüncü kişilerin haklarını (şeref, telif hakları ve kişisel bilgileri dahil) çiğneyen veya zarar veren bir isim/sembol kullanmak.\n• Kanunlarca yasaklanmış olan isim ya da sembollerin kullanılması.",
                'penalty_1' => 'İsim/Sembol değiştirme zorunluluğu',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Oyuncuları Dolandırmaya Çalışmak',
                'detail' => "\"Oyuncuları Dolandırmaya Çalışmak\" herhangi birinin yapılmasıdır:\n\n• Başka bir oyuncunun karakter adına benzer isim ile oyuncuları dolandırmaya çalışmak.\n• Oyuncuları gerçek dışı bilgiler ile herhangi bir web sitesine yönlendirmeye ve dolandırmaya çalışmak.\n• Takas işlemlerinde kandırmaya yönelik benzer görünümlü farklı bir eşya kullanmaya çalışmak.\n• Sunucudaki eşyaların Yang (oyun içi para birimi) fiyatlarının bireysel veya organize olarak manipüle edilmesi.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Oyun Açığı (Bug) Kullanımı',
                'detail' => "\"Oyun Açığı (Bug) Kullanımı\" herhangi birinin yapılmasıdır:\n\n• Oyun veya hizmet hatalarını kullanarak diğer üyelere zarar vermek veya bir kazanç sağlamak.\n• Oyun veya sistem hatalarını kullanarak oyun dengesini veya sistemini etkileyen davranışlar.\n• Normal şartlarda giriş yapmanızın imkansız olduğu alanlara giriş yaparak rakiplerinizi öldürmek vb. eylemler.\n• Hata bulunan görevlerden faydalanmak vb. her türlü eylem.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Nesne Market Açığı Kullanımı',
                'detail' => "\"Nesne Market Açığı Kullanımı\" herhangi birinin yapılmasıdır:\n\n• Oyun mağazasına herhangi bir şekilde müdahale ederek ya da açık kullanarak elde edilemeyen eşyaların alınması.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Yaratık Avı Suistimali',
                'detail' => "\"Yaratık Avı Suistimali\" herhangi birinin yapılmasıdır:\n\n• Yaratıkları oyunculara atak yapamadığı noktalarda durarak öldürmeniz.\n• Eylemi gerçekleştiren kişilerle aynı grup içerisinde olmak.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Rahatsız Edici Oynanış',
                'detail' => "\"Rahatsız Edici Oynanış\" herhangi birinin yapılmasıdır:\n\n• Bireysel/Lonca olarak yer alınan bir etkinlikte başka bir oyuncuyla/loncayla anlaşarak fayda sağlamak.\n  Örn: OX etkinliği, VS/Lonca Turnuvası\n• Oyun görevlileri tarafından organize edilen etkinliklerde diğer oyuncuların etkinlikten yararlanmasını engellemek.",
                'penalty_1' => '7 Gün hesap kapatılması',
                'penalty_2' => '15 Gün hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => '3. Parti İllegal Program, Reklam İçerikli Yazılar',
                'detail' => "\"3. Parti İllegal Program, Reklam İçerikli Yazılar\" aşağıdakilerden birinin yapılmasıdır:\n\n• Amaç ne olursa olsun kurallarda yasaklanmış olan durumların/uygulamaların reklamını yapmanız.\n• Reklam amacı olmasa dahi, sürekli olarak bu tarz durumları/uygulamaları övecek şekilde konuşmanız ya da diğer oyuncuları çekmek için yazılar yazmanız.\n• Diğer sunucuların veya illegal yazılımların yer aldığı, reklamının yapıldığı web sitelerinin paylaşılması.\n• Resmi konular/postlar/Discord ve destek merkezi biletleri de dahil olmak üzere.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Oyun Dosyalarını Değiştirmek, 3. Parti İllegal Program Kullanımı',
                'detail' => "\"Oyun Dosyalarını Değiştirmek, 3. Parti İllegal Program Kullanımı\" herhangi birinin yapılmasıdır:\n\n• Şirket tarafından sağlanmakta olan dosyaların izinsiz olarak değiştirilmesi/Manipüle edilmesi.\n• Bilgisayar programları kullanarak, hizmetin teknik korumasının normal operasyonunu devre dışı bırakmak.\n• 3. parti uygulamalar kullanarak kendinize avantaj sağlamak ya da afk olarak karakterinizi geliştirmeniz.\n• Üyelerin birden fazla basit adımda yapabileceği operasyonları tek bir adımda yapabilmelerine olanak sağlayan yasak programların kullanılması.\n• Şirket tarafından onaylanmamış başka bir üreticinin programlarının kullanılması.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Çalıntı ya da İllegal Eşyaların Bulundurulması',
                'detail' => "\"Çalıntı ya da İllegal Eşyaların Bulundurulması\" aşağıdakilerden birinin yapılmasıdır:\n\n• Çalıntı ya da illegal eşyaların dolayı ya da direkt olarak bir hesaba aktarılması.\n• Çalıntı ya da illegal olarak kazanılmış olan eşyaların aktarım için dahi olsa karakterinize geçmesi.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Ödeme Sahtekarlığı',
                'detail' => "\"Ödeme Sahtekarlığı\" şu durumları ifade eder:\n\n• Hizmet kullanımı vs. için izin alınmaksızın, başkasına ait bir ödeme yöntemi (Kredi kartı, E-Pin v.b) ile Kristal yüklenmesi.\n• Yapılan ödeme için ücret iadesinin banka veya aracı kurumdan talep edilmesi veya iadenin alınması.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Özel Hak İhlali',
                'detail' => "\"Özel Hak İhlali\" aşağıdakilerden birinin yapılmasıdır:\n\n• Oyunculara, ya da oyun yöneticilerine ait her türlü kişisel bilgilerin paylaşılması.\n• Gerçek hayata dair bilgilerin kullanılması.\n• Telefon numarasıyla lonca açılması. Size ait olsa dahi bu gibi kullanımlar ağır cezalar almanıza sebebiyet verir.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Uygun Olmayan Gönderiler ve Platform Yayınları',
                'detail' => "\"Uygun Olmayan Gönderiler ve Platform Yayınları\" aşağıdakilerden birinin yapılmasıdır:\n\n• Şirketin haklarının çiğnenmesi (telif hakları, patent vs.).\n• Şirketin veya şirket çalışanlarının itibarının zedelenmesi.\n• Şirketin ve şirket çalışanlarınına ait her türlü kişisel bilgiyi içeren gönderiler (kişisel bilgi, konum, bağlantı, eposta vs.).\n\nNot: Uygulanacak yaptırım direkt olarak eylemin yer aldığı platform kanalının sahibinin hesabına uygulanır.",
                'penalty_1' => '7 Gün hesap kapatılması',
                'penalty_2' => '15 Gün hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Yanlış Bilgi Yayılması',
                'detail' => "\"Yanlış Bilgi Yayılması\" aşağıdakilerden birinin yapılmasıdır:\n\n• GM ekibi tarafından doğrulanmamış bilgilerin, karmaşa yaratmak için veya doğrudan/dolaylı olarak diğer üyelere zarar vermek için kullanılması, yayılması ve kazanç elde etmek.\n• Bu gibi bilgilerin yayın/video platformları, discord ya da forum üzerinden paylaşılması.",
                'penalty_1' => '7 Gün hesap kapatılması',
                'penalty_2' => '15 Gün hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Gerçek Para ile Alışveriş',
                'detail' => "\"Gerçek Para ile Alışveriş\" aşağıdakilerden birinin yapılmasıdır:\n\n• Oyuna ait Eşya veya Hesapların Türk Lirası vb para birimi kullanılarak başka bir oyuncuya satılması.",
                'penalty_1' => 'Süresiz hesap kapatılması',
                'penalty_2' => 'Süresiz hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
            [
                'title' => 'Lonca Puanı Transferi',
                'detail' => "\"Lonca Puanı Transferi\" aşağıdakilerden birinin yapılmasıdır:\n\n• Anlaşmalı olarak yapılan ve amacının skor veya puan transfer olduğu lonca savaşları.",
                'penalty_1' => '1 Gün hesap kapatılması',
                'penalty_2' => '30 Gün hesap kapatılması',
                'penalty_3' => 'Süresiz hesap kapatılması',
            ],
        ];
    }

    /** @param array<string, mixed> $row */
    private static function map(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'rule_no' => (int) ($row['rule_no'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'detail' => (string) ($row['detail'] ?? ''),
            'penalty_1' => (string) ($row['penalty_1'] ?? ''),
            'penalty_2' => (string) ($row['penalty_2'] ?? ''),
            'penalty_3' => (string) ($row['penalty_3'] ?? ''),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
        ];
    }
}
