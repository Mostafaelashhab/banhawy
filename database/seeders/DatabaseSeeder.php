<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BusinessTypeSeeder::class,
            PlanSeeder::class,
            BusinessSeeder::class,
            ServicesDemoSeeder::class,  
        ]);
    }
}
