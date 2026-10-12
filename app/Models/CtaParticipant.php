<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtaParticipant extends Model
{
    protected $fillable = [
        'cta_id', 'discord_user_id', 'user_name', 'personaje_id',
        'composition_weapon_id', 'status', 'participation_percent',
    ];

    protected $casts = [
        'participation_percent' => 'decimal:2',
    ];

    public function cta(): BelongsTo
    {
        return $this->belongsTo(Cta::class);
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(CompositionWeapon::class, 'composition_weapon_id');
    }

    public function personaje(): BelongsTo
    {
        return $this->belongsTo(Personaje::class);
    }
}
