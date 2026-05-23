<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessReport extends Model
{
    protected $fillable = [
        'business_id', 'user_id',
        'reason', 'details',
        'reporter_phone', 'reporter_email',
        'ip_hash',
        'status', 'admin_note', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const REASONS = [
        'wrong_info'    => 'معلومات غلط (تليفون، عنوان، مواعيد)',
        'closed'        => 'النشاط مقفول نهائياً',
        'inappropriate' => 'صورة غير لائقة',
        'offensive'     => 'محتوى مسيء أو سب',
        'duplicate'     => 'متجر مكرر',
        'other'         => 'سبب آخر',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
