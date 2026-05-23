<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_type_id')->constrained();
            $table->foreignId('plan_id')->nullable()->constrained();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();

            $table->string('whatsapp', 20);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->string('address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            $table->string('logo')->nullable();
            $table->string('cover')->nullable();

            $table->enum('price_range', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('delivery')->default(false);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);

            // Hours stored as JSON keyed by day index (0=Sunday) → ['open' => 'HH:MM', 'close' => 'HH:MM', 'closed' => bool]
            $table->json('hours')->nullable();

            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('whatsapp_clicks')->default(0);

            $table->unsignedTinyInteger('setup_progress')->default(0);

            $table->timestamps();

            $table->index(['business_type_id', 'is_active']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
