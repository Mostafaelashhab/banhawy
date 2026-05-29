<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add composite indexes that match our hot query paths:
 *  - bounding-box queries:  WHERE status='active' AND expires_at > NOW() AND lat BETWEEN ? AND ? AND lng BETWEEN ? AND ?
 *  - delta polling:         WHERE status='active' AND updated_at > ?
 *  - dedup check:           WHERE type=? AND status='active' AND created_at > ?
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('road_alerts', function (Blueprint $table) {
            // Bounding-box scans: filter by status first (highly selective),
            // then range-scan on lat, then on lng.
            $table->index(['status', 'lat', 'lng'], 'idx_alerts_status_lat_lng');

            // Delta polling: cheap "what changed since X" queries
            $table->index(['status', 'updated_at'], 'idx_alerts_status_updated');

            // Dedup queries: same type recently
            $table->index(['type', 'status', 'created_at'], 'idx_alerts_type_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('road_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_alerts_status_lat_lng');
            $table->dropIndex('idx_alerts_status_updated');
            $table->dropIndex('idx_alerts_type_status_created');
        });
    }
};
