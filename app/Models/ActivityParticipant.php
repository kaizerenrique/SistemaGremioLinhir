<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityParticipant extends Model
{
    protected $fillable = [
        'activity_id', 'discord_user_id', 'user_name',
        'personaje_id', 'participation_percent', 'share',
    ];

    protected $casts = [
        'participation_percent' => 'decimal:2',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function personaje(): BelongsTo
    {
        return $this->belongsTo(Personaje::class);
    }
}
