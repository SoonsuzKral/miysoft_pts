<?php

namespace Database\Seeders;

use App\Modules\CMS\Models\Content;
use App\Models\User;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $updatedBy = User::where('email', 'admin@miysoft.com.tr')->value('id');

        $contents = [
            // ─── Hero ──────────────────────────────────────────
            ['hero.title',       'hero',    'Ana Başlık',    'Personel Yönetimini', 'text'],
            ['hero.subtitle',    'hero',    'Alt Başlık',    'İzin, puantaj, envanter, masraf ve 20+ modül ile şirketinizin tüm İK süreçlerini tek platformdan yönetin. Kurumsal güç, kolay kullanım.', 'textarea'],
            ['hero.cta_primary',   'hero',    'Birincil Buton', '14 Gün Ücretsiz Deneyin', 'text'],
            ['hero.cta_secondary', 'hero',    'İkincil Buton',  'Demo İzle', 'text'],

            // ─── Features ──────────────────────────────────────
            ['features.title',    'features', 'Bölüm Başlığı', 'Her Şey Tek Platformda', 'text'],
            ['features.subtitle', 'features', 'Alt Başlık',    'İnsan kaynaklarının tüm süreçlerini dijitalleştirin.', 'textarea'],

            // ─── Contact ───────────────────────────────────────
            ['contact.address', 'contact', 'Adres',   "MİYSOFT Teknoloji A.Ş.\nGülbahar Mah. Üçgen Sk. No:12\nŞişli / İstanbul", 'textarea'],
            ['contact.phone',   'contact', 'Telefon', '+90 212 000 00 00', 'text'],
            ['contact.email',   'contact', 'E-posta', 'info@miysoft.com.tr', 'text'],
            ['contact.map_url', 'contact', 'Harita URL', 'https://maps.google.com/?q=İstanbul', 'url'],

            // ─── About ─────────────────────────────────────────
            ['about.title',   'about', 'Başlık', 'MİYSOFT PTS', 'text'],
            ['about.content', 'about', 'İçerik', '<p>MİYSOFT PTS, Türkiye\'nin en kapsamlı bulut tabanlı personel takip sistemidir. 2023 yılında kurulan şirketimiz, insan kaynakları süreçlerini dijitalleştirerek şirketlerin verimliliğini artırmayı hedeflemektedir.</p><p>Modern teknolojilerle geliştirilen platformumuz, personel yönetiminden izin takibine, puantajdan envanter yönetimine kadar tüm süreçleri tek bir çatı altında toplar.</p>', 'html'],
            ['about.vision',  'about', 'Vizyon', 'Türkiye ve bölgede İK ve personel yönetiminde referans olan, en yenilikçi ve güvenilir SaaS platformu olmak.', 'textarea'],
            ['about.mission', 'about', 'Misyon', 'Şirketlerin insan kaynakları süreçlerini dijitalleştirmek, personel yönetimini kolaylaştırmak ve verimliliği artırmak için güçlü, kullanıcı dostu bir platform sunmak.', 'textarea'],

            // ─── Footer ────────────────────────────────────────
            ['footer.description',      'footer', 'Açıklama Metni',       "Türkiye'nin en kapsamlı bulut tabanlı İK ve personel yönetim platformu.", 'textarea'],
            ['footer.copyright',        'footer', 'Telif Hakkı Metni',    'MİYSOFT Teknoloji A.Ş. Tüm hakları saklıdır.', 'text'],
            ['footer.email',            'footer', 'İletişim E-posta',     'info@miysoft.com.tr', 'text'],
            ['footer.phone',            'footer', 'Telefon',              '+90 212 000 00 00', 'text'],
            ['footer.address',          'footer', 'Adres',                'Şişli / İstanbul', 'textarea'],
            ['footer.social.facebook',  'footer', 'Facebook URL',         'https://facebook.com/miysoft', 'url'],
            ['footer.social.twitter',   'footer', 'Twitter/X URL',        'https://x.com/miysoft', 'url'],
            ['footer.social.linkedin',  'footer', 'LinkedIn URL',         'https://linkedin.com/company/miysoft', 'url'],
            ['footer.social.instagram', 'footer', 'Instagram URL',        'https://instagram.com/miysoft', 'url'],

            // ─── Legal ─────────────────────────────────────────
            ['legal.kvkk',    'legal', 'KVKK Aydınlatma Metni', '<h2>KVKK Aydınlatma Metni</h2><p>MİYSOFT Teknoloji A.Ş. olarak, kişisel verilerinizin güvenliğine önem vermekteyiz. Bu aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında sizleri bilgilendirmek amacıyla hazırlanmıştır.</p><p>Kişisel verileriniz, sunduğumuz hizmetlerin gereği olarak, hizmet sözleşmesinin ifası ve yasal yükümlülüklerimizin yerine getirilmesi amacıyla işlenmektedir.</p>', 'html'],
            ['legal.privacy', 'legal', 'Gizlilik Politikası',  '<h2>Gizlilik Politikası</h2><p>MİYSOFT PTS olarak, kullanıcılarımızın gizliliğine saygı duyuyoruz. Bu politika, platformumuzu kullanırken hangi bilgilerin toplandığını, nasıl kullanıldığını ve korunduğunu açıklamaktadır.</p>', 'html'],
            ['legal.terms',   'legal', 'Kullanım Şartları',    '<h2>Kullanım Şartları</h2><p>MİYSOFT PTS platformunu kullanarak aşağıdaki kullanım şartlarını kabul etmiş sayılırsınız. Platform, bir SaaS (Hizmet Olarak Yazılım) modeliyle sunulmakta olup, abonelik bazlıdır.</p>', 'html'],
        ];

        foreach ($contents as [$key, $section, $label, $value, $type]) {
            Content::updateOrCreate(
                ['key' => $key],
                compact('key', 'section', 'label', 'value', 'type') + [
                    'is_active'  => true,
                    'updated_by' => $updatedBy,
                ]
            );
        }

        $this->command->info('CMS içerikleri başarıyla eklendi: ' . count($contents) . ' kayıt.');
    }
}
