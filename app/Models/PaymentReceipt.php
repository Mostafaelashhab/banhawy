<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const METHOD_INSTAPAY      = 'instapay';
    public const METHOD_VODAFONE_CASH = 'vodafone_cash';

    public const PAYMENT_PHONE        = '01022345504';

    protected $fillable = [
        'business_id', 'user_id', 'plan_id',
        'billing_cycle', 'amount', 'method',
        'receipt_path', 'reference_number',
        'status', 'admin_note',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function business(): BelongsTo  { return $this->belongsTo(Business::class); }
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo      { return $this->belongsTo(Plan::class); }
    public function reviewer(): BelongsTo  { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }

    public function methodLabel(): string
    {
        return match ($this->method) {
            self::METHOD_INSTAPAY      => 'إنستاباي',
            self::METHOD_VODAFONE_CASH => 'فودافون كاش',
            default => $this->method,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'بانتظار المراجعة',
            self::STATUS_APPROVED => 'مفعّل ✓',
            self::STATUS_REJECTED => 'مرفوض',
            default => $this->status,
        };
    }

    public function cycleLabel(): string
    {
        return $this->billing_cycle === 'yearly' ? 'سنوي' : 'شهري';
    }
}
