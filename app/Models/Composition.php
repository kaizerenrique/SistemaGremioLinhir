<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Composition extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function weapons(): HasMany
    {
        return $this->hasMany(CompositionWeapon::class)->orderBy('sort_order');
    }

    public function ctas(): HasMany
    {
        return $this->hasMany(Cta::class);
    }
}
