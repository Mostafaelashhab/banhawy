<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'tagline_ar', 'price_monthly', 'is_featured', 'features', 'sort'];

    protected $casts = [
        'features'    => 'array',
        'is_featured' => 'bool',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
