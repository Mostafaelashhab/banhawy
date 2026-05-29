<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Service-focused pricing — monthly subscription model for service providers
        // (shipping companies & service contractors). Numbers in EGP/month.
        $plans = [
            [
                'slug'          => 'free',
                'name'          => 'مجاني',
                'tagline_ar'    => 'ابدأ بالظهور بدون تكلفة',
                'price_monthly' => 0,
                'is_featured'   => false,
                'features'      => [
                    'صفحة خدمة أساسية',
                    'تليفون + واتساب',
                    'الظهور في نتائج البحث',
                    'حتى 3 صور للخدمة',
                    'تقييمات العملاء',
                ],
                'sort' => 1,
            ],
            [
                'slug'          => 'pro',
                'name'          => 'Pro',
                'tagline_ar'    => 'الأنسب لمعظم مقدمي الخدمات',
                'price_monthly' => 99,
                'is_featured'   => true,
                'features'      => [
                    'كل مزايا المجاني',
                    'علامة موثّق ✓',
                    'ظهور أعلى في النتائج',
                    'صور غير محدودة',
                    'لينك واتساب مباشر',
                    'تنبيهات فورية للطلبات',
                    'استرداد البيانات من البلاغات',
                ],
                'sort' => 2,
            ],
            [
                'slug'          => 'business',
                'name'          => 'Business',
                'tagline_ar'    => 'لشركات الشحن وفرق العمل',
                'price_monthly' => 299,
                'is_featured'   => false,
                'features'      => [
                    'كل مزايا Pro',
                    'تثبيت في أعلى التصنيف',
                    'باج "مميّز" حول البطاقة',
                    'تحليلات تفصيلية للزوار',
                    'دعم فني خاص على واتساب',
                    'حساب لفريق العمل',
                    'صفحة هبوط مخصصة',
                ],
                'sort' => 3,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Clean up legacy slugs that aren't part of the new services-focused pricing
        $allowed = collect($plans)->pluck('slug')->all();
        Plan::whereNotIn('slug', $allowed)->delete();
    }
}
