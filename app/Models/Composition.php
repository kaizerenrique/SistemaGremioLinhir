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

    /**
     * Armas ACTIVAS de la composición.
     *
     * Filtra por active=true para que el editor y las CTAs nuevas
     * solo vean armas vigentes. Armas retiradas (active=false) siguen
     * en BD para preservar el histórico de CTAs pasadas.
     */
    public function weapons(): HasMany
    {
        return $this->hasMany(CompositionWeapon::class)
            ->where('active', true)
            ->orderBy('sort_order');
    }

    /**
     * Todas las armas (activas e inactivas). Uso histórico/auditoría.
     */
    public function allWeapons(): HasMany
    {
        return $this->hasMany(CompositionWeapon::class)
            ->orderBy('sort_order');
    }

    public function ctas(): HasMany
    {
        return $this->hasMany(Cta::class);
    }
}
