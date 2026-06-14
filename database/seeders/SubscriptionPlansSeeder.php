<?php

namespace Database\Seeders;

use App\Modules\Abonelik\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Ücretsiz Deneme',
                'slug'          => 'trial',
                'description'   => '15 günlük ücretsiz deneme. Tüm özellikleri keşfedin.',
                'price_monthly' => 0,
                'price_yearly'  => 0,
                'max_personel'  => 10,
                'features'      => [
                    ['label' => 'Personel Yönetimi (10 kişiye kadar)', 'included' => true],
                    ['label' => 'Temel Puantaj', 'included' => true],
                    ['label' => 'İzin Yönetimi', 'included' => true],
                    ['label' => 'E-posta Desteği', 'included' => true],
                ],
                'is_popular' => false,
                'is_active'  => true,
                'sort_order' => 0,
            ],
            [
                'name'          => 'Temel',
                'slug'          => 'basic',
                'description'   => 'Küçük işletmeler için ideal çözüm.',
                'price_monthly' => 299,
                'price_yearly'  => 2990,
                'max_personel'  => 30,
                'features'      => [
                    ['label' => 'Personel Yönetimi (30 kişiye kadar)', 'included' => true],
                    ['label' => 'Puantaj & Mesai Takibi', 'included' => true],
                    ['label' => 'İzin & Avans Yönetimi', 'included' => true],
                    ['label' => 'Vardiya Planlama', 'included' => true],
                    ['label' => 'Temel Raporlar', 'included' => true],
                    ['label' => 'E-posta Desteği', 'included' => true],
                ],
                'is_popular' => false,
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'name'          => 'Profesyonel',
                'slug'          => 'professional',
                'description'   => 'Büyüyen şirketler için tüm özellikler.',
                'price_monthly' => 799,
                'price_yearly'  => 7990,
                'max_personel'  => 100,
                'features'      => [
                    ['label' => 'Personel Yönetimi (100 kişiye kadar)', 'included' => true],
                    ['label' => 'Puantaj & Mesai Takibi', 'included' => true],
                    ['label' => 'İzin & Avans & Masraf Yönetimi', 'included' => true],
                    ['label' => 'Vardiya Planlama & Çakışma Tespiti', 'included' => true],
                    ['label' => 'Envanter Takibi', 'included' => true],
                    ['label' => 'Seyahat & Araç Yönetimi', 'included' => true],
                    ['label' => 'Gelişmiş Raporlar (PDF/Excel/CSV)', 'included' => true],
                    ['label' => 'CMS & Duyuru & Anket', 'included' => true],
                    ['label' => 'Öncelikli E-posta Desteği', 'included' => true],
                ],
                'is_popular' => true,
                'is_active'  => true,
                'sort_order' => 2,
            ],
            [
                'name'          => 'Kurumsal',
                'slug'          => 'enterprise',
                'description'   => 'Sınırsız personel ve özel çözümler.',
                'price_monthly' => 1999,
                'price_yearly'  => 19990,
                'max_personel'  => null,
                'features'      => [
                    ['label' => 'Sınırsız Personel', 'included' => true],
                    ['label' => 'Tüm Modüller Tam Erişim', 'included' => true],
                    ['label' => 'Özel Rapor & Analiz', 'included' => true],
                    ['label' => 'API Entegrasyonu', 'included' => true],
                    ['label' => 'Çoklu Şirket Desteği', 'included' => true],
                    ['label' => 'Özel Alan Adı', 'included' => true],
                    ['label' => '7/24 Telefon & E-posta Desteği', 'included' => true],
                    ['label' => 'Özel Eğitim & Danışmanlık', 'included' => true],
                ],
                'is_popular' => false,
                'is_active'  => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Abonelik planları oluşturuldu: ' . count($plans) . ' plan.');
    }
}
