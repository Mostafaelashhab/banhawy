<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'business_id', 'product_category_id',
        'name', 'description', 'price', 'image',
        'is_available', 'is_featured', 'sort',
    ];

    protected $casts = [
        'is_available' => 'bool',
        'is_featured'  => 'bool',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
