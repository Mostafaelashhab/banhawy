<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('kind', ['lost', 'found']);     // lost = "ضايع مني" | found = "لقيته"
            $table->string('title');
            $table->string('category', 40);              // documents | electronics | jewelry | keys | bag | pet | other
            $table->text('description');

            $table->string('location')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->date('happened_on')->nullable();     // when lost/found

            $table->string('image')->nullable();         // single photo path/url
            $table->unsignedInteger('reward')->nullable(); // EGP, optional

            $table->string('contact_name', 120);
            $table->string('contact_phone', 30);

            $table->enum('status', ['open', 'resolved', 'expired'])->default('open');
            $table->timestamp('resolved_at')->nullable();

            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();

            $table->index(['kind', 'status', 'created_at']);
            $table->index(['category', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_items');
    }
};
