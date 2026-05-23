<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'business_id', 'customer_name', 'customer_phone',
        'service', 'booked_at', 'party_size', 'status', 'notes',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
