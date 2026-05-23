<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'business_id', 'user_id', 'code', 'customer_name', 'customer_phone', 'customer_address',
        'subtotal', 'delivery_fee', 'total', 'status', 'items', 'notes', 'placed_at',
    ];

    protected $casts = [
        'items'     => 'array',
        'placed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->code)) {
                $order->code = self::generateCode();
            }
        });
    }

    /**
     * Random, readable order code. Skips ambiguous characters (0/O, 1/I/L).
     * Format: BNH-XXXXXX  →  ≈ 1B combinations.
     */
    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // 31 chars
        do {
            $code = 'BNH-';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'new'        => 'تم استلام طلبك',
            'preparing'  => 'جاري تحضير الطلب',
            'completed'  => 'تم تسليم الطلب',
            'cancelled'  => 'الطلب ملغي',
            default      => $this->status,
        };
    }
}
