<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadAlertVote extends Model
{
    protected $fillable = ['road_alert_id', 'ip_hash', 'kind'];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(RoadAlert::class, 'road_alert_id');
    }
}
