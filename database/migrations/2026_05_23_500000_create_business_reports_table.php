<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 40);       // wrong_info | closed | inappropriate | offensive | duplicate | other
            $table->text('details')->nullable();
            $table->string('reporter_phone', 30)->nullable();
            $table->string('reporter_email', 120)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->enum('status', ['pending', 'reviewed', 'actioned', 'dismissed'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('ip_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_reports');
    }
};
