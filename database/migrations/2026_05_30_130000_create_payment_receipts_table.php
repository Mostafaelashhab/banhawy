<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['instapay', 'vodafone_cash']);
            $table->string('receipt_path');
            $table->string('reference_number', 120)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
