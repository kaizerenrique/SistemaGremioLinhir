<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'cta_id', 'name', 'start_time', 'end_time',
        'liquid_silver', 'items_value',
        'guild_tax_rate', 'management_tax_rate',
        'guild_tax_amount', 'management_tax_amount', 'total_distributed',
        'pro_guild', 'registered_by_discord_id', 'registered_by_name',
    ];

    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'pro_guild'    => 'boolean',
        'guild_tax_rate'        => 'decimal:2',
        'management_tax_rate'   => 'decimal:2',
    ];

    public function cta(): BelongsTo
    {
        return $this->belongsTo(Cta::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }
}
