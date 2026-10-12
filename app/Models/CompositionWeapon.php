<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompositionWeapon extends Model
{
    protected $fillable = ['composition_id', 'weapon_name', 'weapon_icon', 'sort_order'];

    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }
}
