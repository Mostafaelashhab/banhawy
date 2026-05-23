<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id', 'endpoint', 'p256dh', 'auth', 'user_agent', 'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shape expected by minishlink/web-push.
     */
    public function toWebPush(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys'     => ['p256dh' => $this->p256dh, 'auth' => $this->auth],
        ];
    }
}
