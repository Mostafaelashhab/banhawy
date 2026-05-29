<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostItem extends Model
{
    protected $fillable = [
        'user_id', 'kind', 'title', 'category', 'description',
        'location', 'lat', 'lng', 'happened_on',
        'image', 'reward',
        'contact_name', 'contact_phone',
        'status', 'resolved_at',
    ];

    protected $casts = [
        'happened_on' => 'date',
        'resolved_at' => 'datetime',
        'lat'         => 'decimal:7',
        'lng'         => 'decimal:7',
    ];

    public const KINDS = [
        'lost'  => 'ضاع مني',
        'found' => 'لقيته',
    ];

    public const CATEGORIES = [
        'documents'   => 'أوراق وبطاقات',
        'electronics' => 'موبايل وإلكترونيات',
        'jewelry'     => 'مجوهرات وساعات',
        'keys'        => 'مفاتيح',
        'bag'         => 'شنطة',
        'wallet'      => 'محفظة',
        'pet'         => 'حيوان أليف',
        'kid'         => 'طفل / شخص',
        'other'       => 'أخرى',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
