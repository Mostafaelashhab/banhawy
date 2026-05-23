<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Set when an authenticated user places the order — used to gate
            // /track so a logged-in user only sees orders linked to them.
            $table->foreignId('user_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->index(['user_id', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'placed_at']);
            $table->dropColumn('user_id');
        });
    }
};
