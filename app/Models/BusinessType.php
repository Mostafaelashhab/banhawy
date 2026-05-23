<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessType extends Model
{
    protected $fillable = ['slug', 'name_ar', 'icon', 'description_ar', 'sort'];

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
