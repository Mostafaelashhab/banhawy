<?php

namespace App\Support;

use Carbon\Carbon;

class Time
{
    /**
     * Format an "H:i" string (e.g. "23:59") as 12-hour Arabic
     * with ص (morning) or م (evening) suffix.
     *
     * Examples:
     *   "08:00" → "8:00 ص"
     *   "12:00" → "12:00 م"
     *   "23:59" → "11:59 م"
     *   "00:30" → "12:30 ص"
     */
    public static function format12(?string $time): string
    {
        if (! $time) return '';
        try {
            $c = Carbon::createFromFormat('H:i', $time);
            return $c->format('g:i') . ' ' . ($c->hour < 12 ? 'ص' : 'م');
        } catch (\Throwable $e) {
            return $time; // fall back to raw value rather than crash
        }
    }
}
