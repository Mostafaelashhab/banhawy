<?php

namespace App\Console\Commands;

use App\Models\RoadAlert;
use Illuminate\Console\Command;

/**
 * Move past-TTL alerts from `active` to `expired` so they stop appearing in
 * polling queries even if `expires_at > NOW()` indexes aren't perfectly tight.
 *
 * Without this, every `WHERE status='active' AND expires_at > NOW()` query
 * still does a range scan against potentially millions of stale rows.
 * Flipping the status column lets the index (`status`, …) prune them upfront.
 *
 * Idempotent — safe to run as often as you like. Default schedule: every 5 minutes.
 */
class ExpireRoadAlerts extends Command
{
    protected $signature = 'banhawy:expire-alerts {--dry : Show what would expire without writing}';
    protected $description = 'Flip past-TTL road alerts from active to expired.';

    public function handle(): int
    {
        $cutoff = now();

        $query = RoadAlert::where('status', 'active')
            ->where('expires_at', '<=', $cutoff);

        if ($this->option('dry')) {
            $n = $query->count();
            $this->info("DRY-RUN: would expire $n alerts.");
            return self::SUCCESS;
        }

        // Update in batches to avoid long-running transactions on a hot table
        $updated = 0;
        $query->chunkById(500, function ($chunk) use (&$updated) {
            $ids = $chunk->pluck('id')->all();
            RoadAlert::whereIn('id', $ids)->update(['status' => 'expired']);
            $updated += count($ids);
        });

        if ($updated > 0) {
            $this->info("Expired $updated alert(s) ✓");
        }
        return self::SUCCESS;
    }
}
