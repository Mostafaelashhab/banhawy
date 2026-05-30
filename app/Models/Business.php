<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    protected $fillable = [
        'owner_id', 'business_type_id', 'plan_id',
        'name', 'slug', 'firebase_id', 'category', 'description',
        'whatsapp', 'phone', 'email',
        'address', 'lat', 'lng',
        'logo', 'cover', 'images',
        'price_range', 'delivery',
        'orders_via', 'bookings_via',
        'is_active', 'is_verified', 'is_featured',
        'hours', 'rating', 'reviews_count',
        'views_count', 'whatsapp_clicks', 'setup_progress',
    ];

    public function acceptsWebOrders(): bool { return in_array($this->orders_via, ['web', 'both']); }
    public function acceptsWhatsappOrders(): bool { return in_array($this->orders_via, ['whatsapp', 'both']); }
    public function acceptsWebBookings(): bool { return in_array($this->bookings_via, ['web', 'both']); }
    public function acceptsWhatsappBookings(): bool { return in_array($this->bookings_via, ['whatsapp', 'both']); }
    public function isWalkinOnly(): bool { return $this->bookings_via === 'walkin'; }

    /* ── Plan tier helpers ─────────────────────────────────────────
     * These power the actual feature gating: photo caps, verified
     * badge, top-of-list sorting, push notifications, etc.
     */
    public function planSlug(): string
    {
        return $this->plan?->slug ?? 'free';
    }

    public function isOnFreePlan(): bool     { return $this->planSlug() === 'free'; }
    public function isOnProPlan(): bool      { return $this->planSlug() === 'pro'; }
    public function isOnBusinessPlan(): bool { return $this->planSlug() === 'business'; }
    public function isOnPaidPlan(): bool     { return in_array($this->planSlug(), ['pro', 'business'], true); }

    /** Numeric tier — useful for ORDER BY. higher = better placement */
    public function planTier(): int
    {
        return match ($this->planSlug()) {
            'business' => 3,
            'pro'      => 2,
            default    => 1,
        };
    }

    /** How many photos this business can upload based on plan */
    public function photosLimit(): int
    {
        return $this->isOnFreePlan() ? 3 : 30;
    }

    /** Is the verified ✓ badge earned by plan tier? */
    public function isPlanVerified(): bool
    {
        return $this->isOnPaidPlan() || $this->is_verified;
    }

    /** Should the business get the "featured" promoted placement? */
    public function isPlanFeatured(): bool
    {
        return $this->isOnBusinessPlan() || $this->is_featured;
    }

    /** Whether merchant gets push notifications on new orders/claims */
    public function canReceivePushNotifications(): bool
    {
        return $this->isOnPaidPlan();
    }

    protected $casts = [
        'hours'        => 'array',
        'images'       => 'array',
        'lat'          => 'decimal:7',
        'lng'          => 'decimal:7',
        'rating'       => 'decimal:2',
        'delivery'     => 'bool',
        'is_active'    => 'bool',
        'is_verified'  => 'bool',
        'is_featured'  => 'bool',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class)->orderBy('sort');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isOpenNow(): bool
    {
        $now = now()->setTimezone(config('app.timezone'));
        $day = (int) $now->format('w'); // 0=Sunday
        $h   = $this->hours[$day] ?? null;
        if (! $h || ! empty($h['closed'])) {
            return false;
        }
        $time = $now->format('H:i');
        return $time >= $h['open'] && $time <= $h['close'];
    }

    public function whatsappLink(string $message = ''): string
    {
        $phone = \App\Support\Phone::forWhatsapp($this->whatsapp);
        $q = $message ? '?text=' . rawurlencode($message) : '';
        return "https://wa.me/{$phone}{$q}";
    }

    public function telLink(): string
    {
        return \App\Support\Phone::forTel($this->phone ?? $this->whatsapp);
    }
}
