<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['business_id', 'viewed_at']);
        });

        Schema::create('whatsapp_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('source', 32)->nullable(); // 'profile', 'order', 'menu'
            $table->timestamp('clicked_at')->useCurrent();

            $table->index(['business_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_clicks');
        Schema::dropIfExists('business_views');
    }
};
