<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'restaurant', 'name_ar' => 'مطعم / كافيه',   'icon' => 'utensils',   'description_ar' => 'طلبات وحجوزات طاولات', 'sort' => 1],
            ['slug' => 'shop',       'name_ar' => 'محل / متجر',     'icon' => 'shop',       'description_ar' => 'ملابس · مستلزمات',     'sort' => 2],
            ['slug' => 'clinic',     'name_ar' => 'عيادة / دكتور',  'icon' => 'steth',      'description_ar' => 'حجز كشف وعيادات',      'sort' => 3],
            ['slug' => 'salon',      'name_ar' => 'صالون / تجميل',  'icon' => 'scissors',   'description_ar' => 'حجز مواعيد',           'sort' => 4],
            ['slug' => 'education',  'name_ar' => 'مركز تعليمي',    'icon' => 'graduation', 'description_ar' => 'كورسات · دروس',        'sort' => 5],
            ['slug' => 'service',    'name_ar' => 'خدمة / شركة',    'icon' => 'briefcase',  'description_ar' => 'صنايعية · حلول',       'sort' => 6],
        ];

        foreach ($types as $t) {
            BusinessType::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
