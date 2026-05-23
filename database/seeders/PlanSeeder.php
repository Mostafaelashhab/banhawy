<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'          => 'starter',
                'name'          => 'Starter',
                'tagline_ar'    => 'للبدايات',
                'price_monthly' => 199,
                'is_featured'   => false,
                'features'      => [
                    'صفحة نشاط أساسية',
                    'منيو/منتجات حتى 20 صنف',
                    'طلبات واتساب',
                    'QR Code للمتجر',
                ],
                'sort' => 1,
            ],
            [
                'slug'          => 'pro',
                'name'          => 'Pro',
                'tagline_ar'    => 'للأنشطة النامية',
                'price_monthly' => 399,
                'is_featured'   => true,
                'features'      => [
                    'كل مزايا Starter',
                    'منتجات غير محدودة + كوبونات',
                    'ظهور مميز على الخريطة',
                    'تحليلات متقدمة',
                    'نظام الحجوزات',
                ],
                'sort' => 2,
            ],
            [
                'slug'          => 'business',
                'name'          => 'Business',
                'tagline_ar'    => 'لسلاسل المحلات',
                'price_monthly' => 799,
                'is_featured'   => false,
                'features'      => [
                    'كل مزايا Pro',
                    'عدة فروع + مدراء',
                    'دعم فني خاص',
                    'API للتكاملات',
                ],
                'sort' => 3,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
