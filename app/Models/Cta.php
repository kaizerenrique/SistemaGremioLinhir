<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cta extends Model
{
    protected $fillable = [
        'composition_id', 'title', 'location',
        'visibility', 'loot_type', 
        'start_time', 'end_time', 
        'reminder_at', 'reminder_minutes', 'reminder_sent',
        'announce_at', 'announced', 
        'status', 'paid', 'pro_guild', 'image_url',
        'discord_message_id', 'discord_channel_id', 'discord_voice_channel_id', 
        'discord_event_id', 'discord_role_id',
        'created_by_discord_id', 'created_by_name',
    ];

    protected $casts = [
        'start_time'      => 'datetime',
        'end_time'        => 'datetime',
        'reminder_at'     => 'datetime',
        'announce_at'     => 'datetime',
        'reminder_sent'   => 'boolean',
        'announced'       => 'boolean',
        'paid'            => 'boolean',
        'pro_guild'       => 'boolean',
    ];

    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CtaParticipant::class);
    }

    public function confirmedParticipants(): HasMany
    {
        return $this->participants()->where('status', 'confirmed');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** Diccionario {composition_weapon_id => user_name} de armas ocupadas. */
    public function weaponOccupancy(): array
    {
        return $this->confirmedParticipants()
            ->whereNotNull('composition_weapon_id')
            ->pluck('user_name', 'composition_weapon_id')
            ->toArray();
    }
}
