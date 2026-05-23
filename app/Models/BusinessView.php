<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessView extends Model
{
    public $timestamps = false;
    protected $fillable = ['business_id', 'ip_hash', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
