<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('slug');
            $table->json('images')->nullable()->after('cover');
        });

        // Allow imported/unclaimed businesses to have no owner
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->unsignedBigInteger('owner_id')->nullable()->change();
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->string('reviewer_phone', 30)->nullable()->after('reviewer_name');
            $table->json('replies')->nullable()->after('body');
        });

        // Ownership-claim requests submitted by users
        Schema::create('business_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claimant_name');
            $table->string('claimant_phone', 30);
            $table->string('claimant_email')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_claims');

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();

            $table->dropUnique(['firebase_id']);
            $table->dropColumn(['firebase_id', 'images']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['firebase_id']);
            $table->dropColumn(['firebase_id', 'reviewer_phone', 'replies']);
        });
    }
};
