<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Banhawy motivational nudges ───────────────────────────────────────────
// Merchants get a daily nudge tailored to their state (missing photos, pending
// orders, low setup progress, etc.). Customers get a sparse twice-weekly nudge
// pointing them at featured businesses.
Schedule::command('banhawy:nudge --audience=merchants')
    ->dailyAt('11:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('banhawy:nudge --audience=customers')
    ->days([0, 4])                 // Sunday + Thursday
    ->at('18:00')
    ->withoutOverlapping()
    ->onOneServer();

// ── Road alerts maintenance ──────────────────────────────────────────────
// Mark past-TTL alerts as expired so they drop out of hot-path queries fast.
// Every 5 minutes is a good balance between freshness and load.
Schedule::command('banhawy:expire-alerts')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Hard-delete inactive alerts older than 30 days — daily at 3 AM (low traffic).
Schedule::command('banhawy:purge-alerts --days=30')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();
