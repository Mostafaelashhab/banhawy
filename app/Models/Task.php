<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id', 'title', 'category', 'description',
        'location', 'lat', 'lng',
        'budget', 'urgency',
        'contact_name', 'contact_phone', 'contact_whatsapp',
        'status', 'closed_at',
    ];

    protected $casts = [
        'lat'        => 'decimal:7',
        'lng'        => 'decimal:7',
        'closed_at'  => 'datetime',
    ];

    public const CATEGORIES = [
        'cleaning'  => 'نظافة',
        'delivery'  => 'توصيل',
        'repair'    => 'صيانة وإصلاح',
        'tutoring'  => 'دروس خصوصية',
        'moving'    => 'نقل أثاث',
        'shopping'  => 'شراء وقضاء حوايج',
        'kids'      => 'رعاية أطفال',
        'elder'     => 'رعاية مسنين',
        'tech'      => 'حلول تقنية',
        'other'     => 'أخرى',
    ];

    public const URGENCIES = [
        'low'    => 'مش مستعجل',
        'normal' => 'عادي',
        'urgent' => 'مستعجل',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
