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
