<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadAlert extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'description',
        'lat', 'lng', 'status',
        'confirmations_count', 'rejections_count',
        'ip_hash', 'expires_at',
    ];

    protected $casts = [
        'lat'        => 'decimal:7',
        'lng'        => 'decimal:7',
        'expires_at' => 'datetime',
    ];

    /**
     * Each type has:
     *  - label_ar    (Arabic display name)
     *  - icon        (emoji-ish glyph for the marker)
     *  - color       (hex for the marker pin)
     *  - ttl_hours   (auto-expire after this many hours)
     *  - chip_label  (short chip text in the filter bar)
     *  - dedup_radius_m  (anti-spam: same type within this radius is treated as a duplicate)
     */
    public const TYPES = [
        'radar'    => ['label_ar' => 'رادار',                  'chip_label' => 'رادارات', 'icon' => '📷', 'color' => '#DC2626', 'ttl_hours' => 3,  'dedup_radius_m' => 80],
        'traffic'  => ['label_ar' => 'زحمة',                   'chip_label' => 'زحمة',    'icon' => '🚗', 'color' => '#F97316', 'ttl_hours' => 3,  'dedup_radius_m' => 200],
        'accident' => ['label_ar' => 'حادثة',                  'chip_label' => 'حوادث',   'icon' => '⚠',  'color' => '#DC2626', 'ttl_hours' => 3,  'dedup_radius_m' => 100],
        'pothole'  => ['label_ar' => 'حفرة / مطب خطر',         'chip_label' => 'حفر',     'icon' => '🕳', 'color' => '#EAB308', 'ttl_hours' => 24, 'dedup_radius_m' => 50],
        'blocked'  => ['label_ar' => 'طريق مقفول / تحويلة',    'chip_label' => 'طرق',     'icon' => '🚧', 'color' => '#3B82F6', 'ttl_hours' => 24, 'dedup_radius_m' => 150],
        'caution'  => ['label_ar' => 'منطقة تحتاج انتباه',     'chip_label' => 'انتباه',  'icon' => '❗', 'color' => '#F59E0B', 'ttl_hours' => 6,  'dedup_radius_m' => 100],
        'signal'   => ['label_ar' => 'عطل إشارة',              'chip_label' => 'إشارات',  'icon' => '🚦', 'color' => '#94A1AE', 'ttl_hours' => 6,  'dedup_radius_m' => 80],
        'safety'   => ['label_ar' => 'تنبيه أمان',             'chip_label' => 'أمان',    'icon' => '🛡', 'color' => '#10B981', 'ttl_hours' => 6,  'dedup_radius_m' => 150],
    ];

    public const CONFIRM_THRESHOLD = 3;
    public const REJECT_THRESHOLD  = 5;

    /* ── Relations ──────────────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(RoadAlertVote::class);
    }

    /* ── Helpers ────────────────────────────────────────────────── */

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCommunityConfirmed(): bool
    {
        return $this->confirmations_count >= self::CONFIRM_THRESHOLD;
    }

    public function ttlHoursForType(): int
    {
        return self::TYPES[$this->type]['ttl_hours'] ?? 6;
    }

    public function dedupRadiusForType(): int
    {
        return self::TYPES[$this->type]['dedup_radius_m'] ?? 100;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label_ar'] ?? $this->type;
    }

    public function typeColor(): string
    {
        return self::TYPES[$this->type]['color'] ?? '#5E6A77';
    }

    public function typeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? '📍';
    }

    /* ── Scopes ─────────────────────────────────────────────────── */

    /** Active = not expired AND status active. Uses the (status, expires_at) index. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active')
                 ->where('expires_at', '>', now());
    }

    /** Restrict to a lat/lng bounding box (cheap range scan via spatial index). */
    public function scopeWithinBounds(Builder $q, float $south, float $west, float $north, float $east): Builder
    {
        return $q->whereBetween('lat', [$south, $north])
                 ->whereBetween('lng', [$west, $east]);
    }

    /** Delta polling: rows that changed (created/updated) since the given timestamp. */
    public function scopeChangedSince(Builder $q, \DateTimeInterface $since): Builder
    {
        return $q->where('updated_at', '>', $since);
    }

    /* ── Anti-duplicate (server-side) ───────────────────────────── */

    /**
     * Has anyone reported the same alert type at roughly this spot in the recent past?
     * Uses bounding-box prefilter (fast) then exact distance on the small candidate set.
     */
    public static function findNearbyDuplicate(string $type, float $lat, float $lng, int $minutes = 10): ?self
    {
        $cfg    = self::TYPES[$type] ?? null;
        if (! $cfg) return null;
        $radius = $cfg['dedup_radius_m'];

        // Rough degree → metres conversion (works fine at Banha latitude ~30°)
        $delta = ($radius / 111000) * 1.3;

        $candidates = self::active()
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->withinBounds($lat - $delta, $lng - $delta, $lat + $delta, $lng + $delta)
            ->get();

        foreach ($candidates as $c) {
            $d = self::distanceMeters($lat, $lng, (float) $c->lat, (float) $c->lng);
            if ($d <= $radius) return $c;
        }
        return null;
    }

    /** Great-circle distance in metres (haversine). */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
