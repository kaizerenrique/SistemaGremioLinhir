<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EspecialidadSemanal extends Model
{
    protected $table = 'especialidades_semanales';
    
    protected $fillable = [
        'personaje_id',
        'semana_inicio',
        'tipo',
        'valor_inicio',
        'valor_fin',
    ];

    protected $casts = [
        'semana_inicio' => 'date',
        'valor_inicio'  => 'integer',
        'valor_fin'     => 'integer',
    ];

    /**
     * Relación con el personaje
     */
    public function personaje(): BelongsTo
    {
        return $this->belongsTo(Personaje::class);
    }

    /**
     * Obtener la ganancia de la semana
     */
    public function getGananciaAttribute(): int
    {
        return $this->valor_fin - $this->valor_inicio;
    }
}
