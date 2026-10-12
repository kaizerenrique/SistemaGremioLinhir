<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'personaje_id', 'discord_user_id', 'user_name', 'amount',
        'status', 'reviewed_by_discord_id', 'reviewed_by_name',
        'reviewed_at', 'notes', 'banco_gremial_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function personaje(): BelongsTo
    {
        return $this->belongsTo(Personaje::class);
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(BancoGremial::class, 'banco_gremial_id');
    }
}
