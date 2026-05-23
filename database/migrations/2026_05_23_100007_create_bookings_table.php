<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('service')->nullable();
            $table->dateTime('booked_at');
            $table->unsignedTinyInteger('party_size')->default(1);
            $table->enum('status', ['new', 'confirmed', 'completed', 'cancelled'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'booked_at']);
            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
