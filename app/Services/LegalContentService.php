<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Gizlilik Sözleşmesi / KVKK sayfa içeriği (DNWeb settings).
 */
final class LegalContentService
{
    public static function privacyTitle(): string
    {
        $t = trim((string) (SiteContentService::get('legal', 'privacy_title', 'Gizlilik Sözleşmesi ve KVKK') ?? ''));
        return $t !== '' ? $t : 'Gizlilik Sözleşmesi ve KVKK';
    }

    public static function privacyHtml(): string
    {
        $html = (string) (SiteContentService::get('legal', 'privacy_html', '') ?? '');
        if (trim($html) === '') {
            $html = self::defaultPrivacyHtml();
        }
        return AnnouncementService::sanitizeHtml($html);
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function savePrivacy(string $title, string $html): array
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 200) {
            return ['ok' => false, 'errors' => ['Başlık zorunlu (max 200).']];
        }
        $html = AnnouncementService::sanitizeHtml($html);
        if (trim(strip_tags($html)) === '') {
            return ['ok' => false, 'errors' => ['İçerik boş olamaz.']];
        }
        SiteContentService::set('legal', 'privacy_title', $title);
        SiteContentService::set('legal', 'privacy_html', $html);
        self::bumpPrivacyRevision();
        return ['ok' => true, 'errors' => []];
    }

    public static function privacyRevision(): int
    {
        $raw = SiteContentService::get('legal', 'privacy_revision', '1');
        $n = (int) $raw;
        return $n > 0 ? $n : 1;
    }

    public static function bumpPrivacyRevision(): int
    {
        $next = self::privacyRevision() + 1;
        SiteContentService::set('legal', 'privacy_revision', (string) $next);
        return $next;
    }

    public static function defaultPrivacyHtml(): string
    {
        return <<<'HTML'
<h2>Genel Kullanım</h2>
<p>m2dn.com.tr internet sitesi ve M2DN oyunu üzerinde yer alan kullanıcı üyelikleri ve bu üyelikleri kullanan kişilere ait hangi bilgilerin depolandığı ve bu bilgilerin nasıl kullanıldığına dair detaylar yer almaktadır. Siteyi ziyaret ederek veya Oyunu kullanarak, sizinle ilgili bilgileri bu Politikada belirtilen şekilde toplamamıza, kullanmamıza, saklamamıza, silmemize ve ifşa etmemize izin vermiş oluyorsunuz. İşbu Politika, yalnızca Site ve oyun için geçerlidir ve Siteden erişim sağlayabileceğiniz ve her biri işbu Politikadan önemli ölçüde farklılık gösteren kendilerine ait veri toplama ve kullanma uygulamalarına ve politikalarına sahip olabilecek olan diğer üçüncü taraf web siteleri için geçerli değildir.</p>

<h2>Hangi Bilgileri Kaydediyoruz?</h2>
<p>m2dn.com.tr sitesini ziyaret etmek için adınız, soyadınız veya başka bir kişisel bilginizi bize sunmanıza gerek yoktur. Fakat sitedeki belli alanları kullanabilmek ve oyunumuzu oynayabilmek için kayıt olmanız gerekmektedir. Kayıt olurken ve kayıt olduktan sonraki aşamalarda kullanıcı adı, şifre, ad soyad, doğum tarihi ve benzeri bilgiler sizden istenebilir. M2DN internet sitesi ve M2DN oyunu üzerinden paylaşmış olduğunuz bilgiler çeşitli promosyon hizmetleri için kullanılabilir. M2DN için kullanılan ödeme yöntemlerine bağlı olarak sizden istenen bilgiler üçüncü bir ödeme sağlayıcı ile paylaşılabilir. M2DN yönetimi, oyunu oynamak için kullanmış olduğunuz bilgisayarınıza ait bazı bilgileri (bilgisayar adı, IP adresi vb. bilgiler) hile kullanımını engellemek ve hesabınızın güvenliği için kayıt etmektedir. Oyun içi sohbet kayıtları, giriş çıkış kayıtları ve her türlü ziyaretçi bilgisinin kayıtları M2DN yönetimi tarafından tutulmaktadır.</p>

<h2>Kaydedilen Bilgileri Ne Yapıyoruz?</h2>

<h3>Kişisel Bilgiler</h3>
<p>M2DN tarafından toplanan kişisel bilgiler yasalara uygun olmayan durumlar, gizlilik politikasında açıklanan diğer şartlar dışında sizin izniniz olmadan 3. taraflarla paylaşılmamaktadır. Oyun içerisindeki değişiklikler, sitede yapılacak güncellemeler ve güvenlik amaçlı hesabınızı doğrulamak, incelemek veya sizinle iletişime geçmemizi gerektiren durumlarda kişisel bilgilerinizi kullanabiliriz.</p>

<h3>Çerezlerin Kullanımı</h3>
<p>İlgi alanlarınıza yönelik içerik sunmak, Siteyi her ziyaretinizde parolanızı tekrar girmek zorunda kalmamanız için parolanızı kaydetmek veya diğer amaçlar için çerezler kullanabiliyoruz. Sitede yayınlanan promosyonlar veya reklamlar çerezler içerebilir. Sitedeki dış reklamcılar tarafından toplanan bilgiler üzerinde erişim veya kontrol hakkımız bulunmamaktadır.</p>

<h3>Anonim Bilgiler</h3>
<p>Anonim bilgileri Site trafiğini analiz etmek için kullanıyoruz; ancak bu bilgileri bireysel olarak tanımlayıcı bilgiler açısından incelemiyoruz. Ayrıca anonim IP adreslerini sunucularımızla ilgili sorunları tespit etmek, Siteyi yönetmek veya tercihlerinize göre içeriği görüntülemek için kullanabiliyoruz. Trafik ve işlem bilgileri de toplu ve anonim olarak iş ortaklarıyla ve reklamcılarla paylaşılabilmektedir.</p>

<h3>Kişisel Bilgilerin İfşası</h3>
<p>Yasal talep gelmesi durumunda, M2DN oyunu, sitesi, bağlı bulunduğu şirket veya bağlı ortaklarına karşı hukuki uygunluk durumlarında, oyunu ve firmanın haklarını korumak ve müdafaa gerektiren durumlarda, olağandışı veya acil durumlarda, firmanın, oyunun ve halkın güvenliğini korumaya yönelik işlemlerde kişisel bilgiler ifşa edilebilir.</p>

<h3>Bilgilerin Satışı</h3>
<p>Toplanan kişisel bilgiler M2DN veya bağlı bulunduğu şirketin satılması, devredilmesi vb. gibi durumlarda daha önce toplanan kişisel bilgileriniz de devredilmiş olacaktır. Bu tarz bir durum oluşması durumunda gerekli duyurular internet sitemiz üzerinden yapılacaktır.</p>

<h2>Güvenlik</h2>
<p>M2DN internet sitesi ve M2DN oyununda kullanıcılardan topladığımız bilgilerin kaybolmaması, suiistimal edilmemesi ve değiştirilmemesine yönelik almış olduğumuz pek çok güvenlik önlemi bulunmaktadır. Kullanıcılara ait bilgilere erişim hakkı sadece belirli M2DN çalışanlarına verilmiştir. M2DN sunucuları güvenli bir veri merkezinde yer almaktadır. Fakat hiçbir sunucu, bilgisayar, sistem veya internet üzerinden gerçekleştirilen bir işlem %100 güvenli değildir. Bu yüzden kullanıcılara ait toplanan bilgiler ve verilerin kaybı, suiistimal edilmesi vb. durumlar için herhangi bir teminat veremiyoruz. Bu bilgilerin tamamının riski sizlere aittir.</p>

<h2>Hizmet Sağlayıcılar</h2>
<p>M2DN yönetimi ürün siparişleri, faturalandırma ve ödeme işlemleri için görevlendirdiği firmalarla size ait bilgileri paylaşabilir. Bu firmalar sizlere hizmet sunarken kişisel bilgilerinize erişim sağlayabilir.</p>

<h2>Güncellemeler ve Değişiklikler</h2>
<p>Gizlilik politikasını dönem dönem güncelleyebiliriz. M2DN yönetimi değişiklikleri size e-posta veya sitede/oyunda yer alan bir uyarıyla bildirebilir. Gizlilik politikamıza yönelik en son bilgileri öğrenebilmek adına bu sayfayı periyodik olarak ziyaret etmenizi öneriyoruz.</p>

<p class="closing">Siteyi ve/veya oyunu kullanarak, işbu Gizlilik Politikasının şartlarını okuyup anlamış ve kabul etmiş olduğunuzu belirtmiş oluyorsunuz. Bu Gizlilik Politikasını kabul etmiyorsanız, lütfen Siteyi veya Oyunu kullanmayınız.</p>

<h2>Ek Politikalar</h2>
<p>Site ve oyunun kullanımıyla ilgili ek politikalar bulunmaktadır. Oyun kuralları, forum kuralları, hizmet sözleşmesi ve kullanım şartları gibi sayfaları da inceleyip bilgi edinmenizi öneririz.</p>
HTML;
    }

    public static function ensureSeeded(): void
    {
        try {
            $existing = SiteContentService::get('legal', 'privacy_html', null);
            if ($existing === null || trim((string) $existing) === '') {
                SiteContentService::set('legal', 'privacy_title', 'Gizlilik Sözleşmesi ve KVKK');
                SiteContentService::set('legal', 'privacy_html', self::defaultPrivacyHtml());
            }
        } catch (\Throwable) {
            // ignore
        }
    }
}
