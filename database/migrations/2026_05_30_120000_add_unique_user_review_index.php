<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DELETE r1 FROM reviews r1
            INNER JOIN reviews r2
              ON r1.business_id = r2.business_id
             AND r1.user_id     = r2.user_id
             AND r1.user_id IS NOT NULL
             AND r1.id          < r2.id
        ");

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['business_id', 'user_id'], 'reviews_business_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_business_user_unique');
        });
    }
};
