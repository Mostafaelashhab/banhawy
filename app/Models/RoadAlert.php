<?php

namespace App\Models;

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
     */
    public const TYPES = [
        'radar' => [
            'label_ar'   => 'رادار',
            'chip_label' => 'رادارات',
            'icon'       => '📷',
            'color'      => '#DC2626',
            'ttl_hours'  => 3,
        ],
        'traffic' => [
            'label_ar'   => 'زحمة',
            'chip_label' => 'زحمة',
            'icon'       => '🚗',
            'color'      => '#F97316',
            'ttl_hours'  => 3,
        ],
        'accident' => [
            'label_ar'   => 'حادثة',
            'chip_label' => 'حوادث',
            'icon'       => '⚠',
            'color'      => '#DC2626',
            'ttl_hours'  => 3,
        ],
        'pothole' => [
            'label_ar'   => 'حفرة / مطب خطر',
            'chip_label' => 'حفر',
            'icon'       => '🕳',
            'color'      => '#EAB308',
            'ttl_hours'  => 24,
        ],
        'blocked' => [
            'label_ar'   => 'طريق مقفول / تحويلة',
            'chip_label' => 'طرق',
            'icon'       => '🚧',
            'color'      => '#3B82F6',
            'ttl_hours'  => 24,
        ],
        'caution' => [
            'label_ar'   => 'منطقة تحتاج انتباه',
            'chip_label' => 'انتباه',
            'icon'       => '❗',
            'color'      => '#F59E0B',
            'ttl_hours'  => 6,
        ],
        'signal' => [
            'label_ar'   => 'عطل إشارة',
            'chip_label' => 'إشارات',
            'icon'       => '🚦',
            'color'      => '#94A1AE',
            'ttl_hours'  => 6,
        ],
        'safety' => [
            'label_ar'   => 'تنبيه أمان',
            'chip_label' => 'أمان',
            'icon'       => '🛡',
            'color'      => '#10B981',
            'ttl_hours'  => 6,
        ],
    ];

    public const CONFIRM_THRESHOLD = 3;   // votes needed to earn "مؤكد من المجتمع"
    public const REJECT_THRESHOLD  = 5;   // votes that auto-hide an alert

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(RoadAlertVote::class);
    }

    /* ── Helpers ─────────────────────────────────────────────────── */

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

    /**
     * Lifecycle scopes
     */
    public function scopeActive($q)
    {
        return $q->where('status', 'active')
                 ->where('expires_at', '>', now());
    }
}
