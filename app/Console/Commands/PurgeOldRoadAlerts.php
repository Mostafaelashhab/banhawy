<?php

namespace App\Console\Commands;

use App\Models\RoadAlert;
use App\Models\RoadAlertVote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hard-delete alerts that have been expired/rejected for > N days.
 *
 * Keeps the table small (queries fast). We don't need 6-month-old "زحمة"
 * reports — they have zero analytical value beyond a week or two.
 *
 * Default cutoff: 30 days. Configure via --days=N.
 */
class PurgeOldRoadAlerts extends Command
{
    protected $signature = 'banhawy:purge-alerts
                              {--days=30 : Delete inactive alerts older than N days}
                              {--dry : Show what would be deleted without writing}';

    protected $description = 'Delete inactive road alerts older than the cutoff.';

    public function handle(): int
    {
        $days   = max(7, (int) $this->option('days')); // refuse to nuke recent data
        $cutoff = now()->subDays($days);

        $query = RoadAlert::whereIn('status', ['expired', 'rejected'])
            ->where('updated_at', '<', $cutoff);

        if ($this->option('dry')) {
            $this->info("DRY-RUN: would delete " . $query->count() . " alert(s) older than $days days.");
            return self::SUCCESS;
        }

        $deleted = 0;
        DB::transaction(function () use ($query, &$deleted) {
            $query->chunkById(500, function ($chunk) use (&$deleted) {
                $ids = $chunk->pluck('id')->all();
                // Votes are FK cascade-on-delete but we delete explicitly to be loud
                RoadAlertVote::whereIn('road_alert_id', $ids)->delete();
                RoadAlert::whereIn('id', $ids)->delete();
                $deleted += count($ids);
            });
        });

        if ($deleted > 0) {
            $this->info("Purged $deleted alert(s) older than $days days ✓");
        }
        return self::SUCCESS;
    }
}
