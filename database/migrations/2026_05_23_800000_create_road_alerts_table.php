<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // radar | traffic | accident | pothole | blocked | caution | signal | safety
            $table->string('type', 20)->index();
            $table->string('title', 160)->nullable();
            $table->text('description')->nullable();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // active | expired | rejected
            $table->enum('status', ['active', 'expired', 'rejected'])->default('active')->index();

            $table->unsignedSmallInteger('confirmations_count')->default(0);
            $table->unsignedSmallInteger('rejections_count')->default(0);

            $table->string('ip_hash', 64)->nullable();   // light de-dup on report

            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['type', 'status']);
        });

        // Track each user vote to prevent ballot stuffing
        Schema::create('road_alert_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_alert_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 64);
            $table->enum('kind', ['confirm', 'reject']);
            $table->timestamps();
            $table->unique(['road_alert_id', 'ip_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_alert_votes');
        Schema::dropIfExists('road_alerts');
    }
};
