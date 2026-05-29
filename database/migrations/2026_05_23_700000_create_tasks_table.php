<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('category', 40);   // cleaning | delivery | repair | tutoring | moving | shopping | other
            $table->text('description');
            $table->string('location')->nullable();      // text location (e.g. "حدائق بنها")
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->unsignedInteger('budget')->nullable();    // EGP, optional
            $table->enum('urgency', ['low', 'normal', 'urgent'])->default('normal');

            $table->string('contact_name', 120);
            $table->string('contact_phone', 30);
            $table->string('contact_whatsapp', 30)->nullable();

            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->timestamp('closed_at')->nullable();

            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('responses_count')->default(0);

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
