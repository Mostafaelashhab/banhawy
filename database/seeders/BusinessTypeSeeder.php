<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // ── Services (priority) ────────────────────────────────────
            ['slug' => 'shipping',   'name_ar' => 'شركات شحن',      'icon' => 'truck',      'description_ar' => 'شحن داخلي وخارجي',     'sort' => 1],
            ['slug' => 'service',    'name_ar' => 'خدمة / شركة',    'icon' => 'briefcase',  'description_ar' => 'صنايعية · حلول',       'sort' => 2],
            // ── Commerce (secondary) ───────────────────────────────────
        ];

        foreach ($types as $t) {
            BusinessType::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
